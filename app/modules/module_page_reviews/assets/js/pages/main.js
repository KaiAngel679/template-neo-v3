(function ($) {
    'use strict';

    var $list = $('#rvReviewsList');
    if (!$list.length) {
        return;
    }

    var isAuth = $list.attr('data-is-auth') === '1';
    var viewerAvatar = $list.attr('data-viewer-avatar') || '';
    var hasCriteria = $list.attr('data-has-criteria') === '1';
    var banComment = $list.attr('data-ban-comment') === '1';
    var banCommentReason = String($list.attr('data-ban-comment-reason') || '');
    var banReply = $list.attr('data-ban-reply') === '1';
    var banReplyReason = String($list.attr('data-ban-reply-reason') || '');
    var banVote = $list.attr('data-ban-vote') === '1';
    var banVoteReason = String($list.attr('data-ban-vote-reason') || '');
    var state = {
        search: '',
        stars: [],
        sort: 'date',
        page: 1,
        limit: 20,
        loading: false,
        total: 0
    };

    var activeMenu = null;
    var activeTrigger = null;
    var searchTimer = null;
    var pendingDeepLink = parseRvDeepLink();

    function parseRvDeepLink() {
        var params = new URLSearchParams(window.location.search || '');
        var reviewId = parseInt(params.get('review') || '0', 10) || 0;
        if (reviewId <= 0) {
            return null;
        }
        var commentId = parseInt(params.get('comment') || '0', 10) || 0;
        return {
            reviewId: reviewId,
            commentId: commentId > 0 ? commentId : 0
        };
    }

    function buildRvDeepLink(reviewId, commentId) {
        var url = window.location.origin + '/reviews/?review=' + reviewId;
        if (commentId) {
            url += '&comment=' + commentId;
        }
        return url;
    }

    function clearRvDeepLinkUrl() {
        try {
            var url = new URL(window.location.href);
            if (!url.searchParams.has('review') && !url.searchParams.has('comment')) {
                return;
            }
            url.searchParams.delete('review');
            url.searchParams.delete('comment');
            var next = url.pathname + (url.searchParams.toString() ? '?' + url.searchParams.toString() : '') + url.hash;
            window.history.replaceState({}, '', next);
        } catch (e) {
        }
    }

    function copyRvLink(text) {
        function ok() {
            rvToast('success', rvPhrase('_rv_linkCopied', 'Ссылка скопирована'));
        }
        function fail() {
            rvToast('error', rvPhrase('_rv_copyFailed', 'Не удалось скопировать'));
        }

        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            navigator.clipboard.writeText(text).then(ok).catch(function () {
                fallbackCopy(text) ? ok() : fail();
            });
            return;
        }
        fallbackCopy(text) ? ok() : fail();
    }

    function fallbackCopy(text) {
        var $tmp = $('<textarea readonly></textarea>').css({
            position: 'fixed',
            left: '-9999px',
            top: '0'
        }).val(text).appendTo('body');
        $tmp[0].select();
        var done = false;
        try {
            done = document.execCommand('copy');
        } catch (e) {
            done = false;
        }
        $tmp.remove();
        return done;
    }

    function closeRvMenu() {
        if (activeMenu) {
            var $menu = $(activeMenu);
            activeMenu = null;
            $menu.fadeOut(150, function () {
                $(this).remove();
            });
        }
        if (activeTrigger) {
            $(activeTrigger).removeAttr('data-menu-open').data('menu-open', false);
            activeTrigger = null;
        }
    }

    function positionRvMenu() {
        if (!activeMenu || !activeTrigger) return;

        var $menu = $(activeMenu);
        var btnRect = activeTrigger.getBoundingClientRect();
        var menuWidth = $menu.outerWidth() || 160;
        var menuHeight = $menu.outerHeight() || 40;
        var winW = window.innerWidth;
        var winH = window.innerHeight;

        var top = btnRect.bottom + 10;
        var left = btnRect.right - menuWidth;

        if (left < 8) left = 8;
        if (left + menuWidth > winW - 8) left = winW - menuWidth - 8;
        if (top + menuHeight > winH - 8) {
            top = btnRect.top - menuHeight - 10;
        }
        if (top < 8) top = 8;

        $menu.css({ top: top, left: left });
    }

    function openRvMenu(trigger) {
        closeRvMenu();

        var canEdit = trigger.getAttribute('data-can-edit') === '1';
        var html =
            '<button type="button" data-action="copy_link">' +
                '<svg><use href="/resources/img/sprite.svg#copy"></use></svg> ' +
                rvEscapeHtml(rvPhrase('_rv_copyLink', 'Копировать ссылку')) +
            '</button>';

        if (canEdit) {
            html +=
                '<button type="button" data-action="edit">' +
                    '<svg><use href="/resources/img/sprite.svg#edit-pen"></use></svg> ' +
                    rvEscapeHtml(rvPhrase('_rv_edit', 'Изменить')) +
                '</button>' +
                '<button type="button" class="button-delete" data-action="delete">' +
                    '<svg><use href="/resources/img/sprite.svg#trash"></use></svg> ' +
                    rvEscapeHtml(rvPhrase('_rv_delete', 'Удалить')) +
                '</button>';
        }

        var $menu = $('<div class="at__action-list visible"></div>').html(html).hide();
        $('body').append($menu);

        activeMenu = $menu[0];
        activeTrigger = trigger;
        $(trigger).attr('data-menu-open', '1').data('menu-open', true);

        $menu.css({ visibility: 'hidden', display: 'flex' });
        positionRvMenu();
        $menu.css('visibility', 'visible').hide().fadeIn(150);
    }

    function collectFilters() {
        state.search = String($('#rvSearch').val() || '').trim();
        state.stars = [];
        $('input[name="rv-star"]:checked').each(function () {
            state.stars.push(parseInt($(this).val(), 10));
        });
        state.sort = String($('input[name="rv-sort"]:checked').val() || 'date');
    }

    function updateSummary(summary) {
        if (!summary) return;

        var avg = Number(summary.avg || 0).toFixed(1);
        var total = Number(summary.total || 0);
        $('.rv__score').first().text(avg);
        $('.rv__summary .rv__stars').first().css('--rv-rating', avg).attr(
            'aria-label',
            rvPhrase('_rv_ratingAria', 'Рейтинг %avg% из 5').replace('%avg%', avg)
        );
        $('.rv__summary-count').first().text(
            rvPhrase('_rv_reviewsCount', '%count% отзывов').replace(
                '%count%',
                String(total).replace(/\B(?=(\d{3})+(?!\d))/g, ' ')
            )
        );

        var dist = summary.distribution || {};
        var base = Math.max(1, total);
        $('#rvDist li').each(function () {
            var star = parseInt($(this).find('.rv__dist-label').text(), 10);
            var cnt = Number(dist[star] || 0);
            var pct = Math.round((cnt * 1000) / base) / 10;
            $(this).find('.rv__dist-bar > span').css('width', pct + '%').attr('data-count', cnt);
            $('#rvStar' + star).closest('label').find('em').text(cnt);
        });

        var attrs = summary.attrs || [];
        var $wrap = $('#rvAttrs');
        if ($wrap.length) {
            if (!attrs.length) {
                $wrap.empty().addClass('is-empty').attr('hidden', true);
            } else {
                $wrap.html(attrs.map(function (attr) {
                    return '' +
                        '<div class="rv__attr" data-attr="' + rvEscapeHtml(String(attr.id != null ? attr.id : '')) + '">' +
                            '<div class="rv__attr-score">' + Number(attr.avg || 0).toFixed(1) + '</div>' +
                            '<div class="rv__attr-name">' + rvEscapeHtml(attr.name || '') + '</div>' +
                        '</div>';
                }).join('')).removeClass('is-empty').removeAttr('hidden');
            }
        }
    }

    function voteButtonsHtml(score, myVote, kind, id) {
        var disabled = banVote ? ' disabled' : '';
        var bannedClass = banVote ? ' is-banned' : '';
        return '' +
            '<div class="rv__votes' + (kind === 'comment' ? ' rv__votes--sm' : '') + bannedClass + '" data-vote-kind="' + kind + '" data-vote-id="' + id + '">' +
                '<button class="rv__vote-btn' + (myVote === 1 ? ' is-active' : '') + '" type="button" data-vote="1"' + disabled + ' aria-label="' + rvEscapeHtml(rvPhrase('_rv_like', 'Нравится')) + '">' +
                    '<svg><use href="/resources/img/sprite.svg#like"></use></svg>' +
                '</button>' +
                '<span class="rv__vote-score ' + rvScoreClass(score) + '">' + rvEscapeHtml(rvFormatScore(score)) + '</span>' +
                '<button class="rv__vote-btn rv__vote-btn--down' + (myVote === -1 ? ' is-active' : '') + '" type="button" data-vote="-1"' + disabled + ' aria-label="' + rvEscapeHtml(rvPhrase('_rv_dislike', 'Не нравится')) + '">' +
                    '<svg><use href="/resources/img/sprite.svg#like"></use></svg>' +
                '</button>' +
            '</div>';
    }

    function rvPhrase(key, fallback) {
        if (typeof get_translate_module_phrase === 'function') {
            var value = get_translate_module_phrase('module_page_reviews', key);
            if (value && value !== key) {
                return value;
            }
        }
        return fallback || key;
    }

    function banMessage(kind) {
        if (kind === 'reply') {
            return banReplyReason
                ? rvPhrase('_rv_bannedReplyReason', 'Бан на ответы: %reason%').replace('%reason%', banReplyReason)
                : rvPhrase('_rv_bannedReply', 'Вы в бане и не можете отвечать');
        }
        if (kind === 'vote') {
            return banVoteReason
                ? rvPhrase('_rv_bannedVoteReason', 'Бан на голоса: %reason%').replace('%reason%', banVoteReason)
                : rvPhrase('_rv_bannedVote', 'Вы в бане и не можете голосовать');
        }
        return banCommentReason
            ? rvPhrase('_rv_bannedCommentReason', 'Бан на комментарии: %reason%').replace('%reason%', banCommentReason)
            : rvPhrase('_rv_bannedComment', 'Вы в бане и не можете комментировать');
    }

    function renderBadges(badges) {
        if (!badges) {
            return '';
        }
        var parts = [];
        if (badges.site_admin) {
            parts.push('<span class="rv__badge rv__badge--site"><span class="rv__badge-dot"></span>' +
                rvEscapeHtml(rvPhrase('_rv_badgeSiteAdmin', 'Админ сайта')) + '</span>');
        }
        if (badges.admin) {
            var adminLabel = badges.admin_group ? String(badges.admin_group) : rvPhrase('_rv_badgeAdmin', 'Админ');
            parts.push('<span class="rv__badge rv__badge--admin"><span class="rv__badge-dot"></span>' +
                adminLabel + '</span>');
        }
        if (badges.vip) {
            var vipLabel = badges.vip_group ? String(badges.vip_group) : rvPhrase('_rv_badgeVip', 'VIP');
            parts.push('<span class="rv__badge rv__badge--vip"><span class="rv__badge-dot"></span>' +
                vipLabel + '</span>');
        }
        if (!parts.length) {
            return '';
        }
        return '<div class="rv__badges">' + parts.join('') + '</div>';
    }

    function renderUser(user, opts) {
        opts = opts || {};
        var sid = String(user.steamid || '');
        var name = user.name || 'Unnamed';
        var avatarSrc = user.avatar || '';
        var sizeClass = opts.sm ? ' rv__user--sm' : '';
        var timeHtml = opts.time
            ? '<time>' + rvEscapeHtml(rvFormatDateTime(opts.time)) + '</time>'
            : '';

        return '' +
            '<a class="rv__user' + sizeClass + '" href="/profiles/' + rvEscapeHtml(sid) + '/?search=1" target="_blank">' +
                '<img id="avatar" avatarid="' + rvEscapeHtml(sid) + '" src="' + rvEscapeHtml(avatarSrc) + '" alt="">' +
                '<span class="rv__user-info">' +
                    '<span class="rv__user-name" id="name" nameid="' + rvEscapeHtml(sid) + '">' + name + '</span>' +
                    renderBadges(user.badges) +
                    timeHtml +
                '</span>' +
            '</a>';
    }

    function renderComment(comment, canReply, reviewId) {
        reviewId = parseInt(reviewId, 10) || 0;
        var replies = comment.replies || [];
        var repliesHtml = '';
        if (replies.length) {
            repliesHtml = '<div class="rv__replies is-collapsed" hidden data-rv-replies>' +
                replies.map(function (r) { return renderComment(r, false, reviewId); }).join('') +
                '</div>';
        }

        var menu =
            '<button class="rv__menu-btn" type="button" aria-label="' + rvEscapeHtml(rvPhrase('_rv_menu', 'Меню')) + '"' +
                ' data-menu-kind="comment"' +
                ' data-review-id="' + reviewId + '"' +
                ' data-comment-id="' + comment.id + '"' +
                ' data-can-edit="' + (comment.can_edit ? '1' : '0') + '">' +
                '<svg><use href="/resources/img/sprite.svg#dots-vertical"></use></svg>' +
            '</button>';

        var editText = comment.text_edit != null ? comment.text_edit : comment.text;

        return '' +
            '<div class="rv__comment" id="comment-' + comment.id + '" data-comment-id="' + comment.id + '">' +
                '<div class="rv__comment-head">' +
                    renderUser(comment, { sm: true, time: comment.created_at }) +
                    menu +
                '</div>' +
                '<div class="rv__comment-text" data-comment-text>' + rvEscapeHtml(comment.text) + '</div>' +
                '<div class="rv__composer rv__composer--edit" hidden data-rv-edit-form>' +
                    '<div class="rv__composer-field">' +
                        '<textarea rows="2" maxlength="2000">' + rvEscapeHtml(editText || '') + '</textarea>' +
                        '<div class="rv__composer-bar">' +
                            '<button type="button" data-cancel-edit-comment>' + rvEscapeHtml(rvPhrase('_rv_cancel', 'Отмена')) + '</button>' +
                            '<button class="active" type="button" data-save-edit-comment>' + rvEscapeHtml(rvPhrase('_rv_save', 'Сохранить')) + '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="rv__comment-actions">' +
                    voteButtonsHtml(comment.score, comment.my_vote, 'comment', comment.id) +
                    (canReply && !banReply ? '<button class="rv__reply-btn" type="button" data-reply-to="' + comment.id + '">' + rvEscapeHtml(rvPhrase('_rv_reply', 'Ответить')) + '</button>' : '') +
                    (replies.length ? '<button class="rv__replies-toggle" type="button" data-rv-replies-toggle aria-expanded="false">' + rvEscapeHtml(rvPhrase('_rv_showReplies', 'Показать ответы (%count%)').replace('%count%', String(replies.length))) + '</button>' : '') +
                '</div>' +
                (canReply && !banReply
                    ? '<div class="rv__composer rv__composer--reply" hidden data-rv-reply-form data-parent-id="' + comment.id + '">' +
                        '<div class="rv__composer-field">' +
                            '<textarea rows="2" maxlength="2000" placeholder="' + rvEscapeHtml(rvPhrase('_rv_replyPlaceholder', 'Написать ответ (10–2000)')) + '"></textarea>' +
                            '<div class="rv__composer-bar"><button class="active" type="button" data-send-reply>' + rvEscapeHtml(rvPhrase('_rv_send', 'Отправить')) + '</button></div>' +
                        '</div>' +
                      '</div>'
                    : '') +
                repliesHtml +
            '</div>';
    }

    function renderReview(review) {
        var serverTag = review.server_id === -1
            ? rvPhrase('_rv_allServers', 'Все сервера')
            : rvPhrase('_rv_serverTag', 'Сервер: %name%').replace('%name%', review.server_name || '');
        var gameTag = review.game
            ? rvPhrase('_rv_gameTag', 'Игра: %name%').replace('%name%', review.game)
            : '';
        var menu =
            '<button class="rv__menu-btn" type="button" aria-label="' + rvEscapeHtml(rvPhrase('_rv_menu', 'Меню')) + '"' +
                ' data-menu-kind="review"' +
                ' data-review-id="' + review.id + '"' +
                ' data-can-edit="' + (review.can_edit ? '1' : '0') + '">' +
                '<svg><use href="/resources/img/sprite.svg#dots-vertical"></use></svg>' +
            '</button>';

        return '' +
            '<article class="rv__review card" id="review-' + review.id + '" data-review-id="' + review.id + '">' +
                '<div class="rv__review-head">' +
                    renderUser(review) +
                    '<div class="rv__review-meta">' +
                        '<time>' + rvEscapeHtml(rvFormatDate(review.created_at)) + '</time>' +
                        menu +
                    '</div>' +
                '</div>' +
                '<div class="rv__review-rating">' +
                    '<span>' + rvEscapeHtml(rvPhrase('_rv_overall', 'Общая оценка:')) + '</span>' +
                    rvStarsHtml(review.overall, 'rv__stars--sm') +
                    '<span class="rv__review-overall-value">(' + Number(review.overall || 0).toFixed(1).replace('.', ',') + ')</span>' +
                '</div>' +
                '<div class="rv__tags">' +
                    '<span class="rv__tag">' + rvEscapeHtml(serverTag) + '</span>' +
                    (gameTag ? '<span class="rv__tag">' + rvEscapeHtml(gameTag) + '</span>' : '') +
                    '<span class="rv__tag">' + rvEscapeHtml(rvPhrase('_rv_hoursOnProject', 'Часов на проекте: %hours% ч.').replace('%hours%', rvFormatHours(review.hours))) + '</span>' +
                '</div>' +
                '<div class="rv__review-body">' +
                    (review.pros ? '<div class="rv__field"><strong>' + rvEscapeHtml(rvPhrase('_rv_pros', 'Достоинства')) + '</strong><p>' + rvEscapeHtml(review.pros) + '</p></div>' : '') +
                    (review.cons ? '<div class="rv__field"><strong>' + rvEscapeHtml(rvPhrase('_rv_cons', 'Недостатки')) + '</strong><p>' + rvEscapeHtml(review.cons) + '</p></div>' : '') +
                    (review.comment ? '<div class="rv__field"><strong>' + rvEscapeHtml(rvPhrase('_rv_comment', 'Комментарий')) + '</strong><p>' + rvEscapeHtml(review.comment) + '</p></div>' : '') +
                '</div>' +
                '<div class="rv__review-foot">' +
                    '<button class="rv__comments-toggle" type="button" data-rv-comments-toggle aria-expanded="false">' +
                        rvEscapeHtml(rvPhrase('_rv_commentsCount', 'Комментарии (%count%)').replace('%count%', String(review.comments_count || 0))) +
                        '<svg><use href="/resources/img/sprite.svg#chevron-down"></use></svg>' +
                    '</button>' +
                    voteButtonsHtml(review.score, review.my_vote, 'review', review.id) +
                '</div>' +
                '<div class="rv__comments" data-rv-comments hidden>' +
                    (isAuth
                        ? (banComment
                            ? '<div class="rv__composer rv__composer--main rv__composer--banned">' +
                                '<div class="rv__composer-title">' + rvEscapeHtml(banMessage('comment')) + '</div>' +
                              '</div>'
                            : '<div class="rv__composer rv__composer--main">' +
                                '<div class="rv__composer-title">' + rvEscapeHtml(rvPhrase('_rv_leaveComment', 'Оставить комментарий')) + '</div>' +
                                '<div class="rv__composer-body">' +
                                    '<img class="rv__composer-avatar" src="' + rvEscapeHtml(viewerAvatar) + '" alt="">' +
                                    '<div class="rv__composer-field">' +
                                        '<textarea rows="2" maxlength="2000" placeholder="' + rvEscapeHtml(rvPhrase('_rv_commentPlaceholderShort', 'Что думаете об отзыве? (10–2000)')) + '"></textarea>' +
                                        '<div class="rv__composer-bar"><button class="active" type="button" data-send-comment>' + rvEscapeHtml(rvPhrase('_rv_send', 'Отправить')) + '</button></div>' +
                                    '</div>' +
                                '</div>' +
                              '</div>')
                        : '') +
                    '<div class="rv__comments-list" data-comments-list></div>' +
                '</div>' +
            '</article>';
    }

    function loadReviews(page) {
        if (state.loading) return;

        page = page == null ? state.page : parseInt(page, 10) || 1;
        if (page < 1) {
            page = 1;
        }

        collectFilters();
        state.loading = true;
        state.page = page;

        $('#rvReviewsEmpty').prop('hidden', true).hide();
        $list.html(renderReviewsSkeletonList(10));
        $('#rvReviewsPagination').html('');

        sendRequest({
            get_reviews: true,
            search: state.search,
            stars: state.stars,
            sort: state.sort,
            limit: state.limit,
            offset: (page - 1) * state.limit
        }).done(function (res) {
            if (!res || res.status !== 'success') {
                $list.html('');
                rvToast('error', (res && res.message) || rvPhrase('_rv_loadReviewsFailed', 'Не удалось загрузить отзывы'));
                return;
            }

            updateSummary(res.summary);
            state.total = Number(res.total || 0);
            var totalPages = Math.max(1, Math.ceil(state.total / state.limit) || 1);
            if (state.total > 0 && page > totalPages) {
                setTimeout(function () {
                    loadReviews(totalPages);
                }, 0);
                return;
            }

            var reviews = res.reviews || [];
            var $empty = $('#rvReviewsEmpty');

            if (!reviews.length) {
                $list.html('');
                $empty.prop('hidden', false).show();
                $('#rvReviewsPagination').html('');
            } else {
                $empty.prop('hidden', true).hide();
                $list.html(reviews.map(renderReview).join(''));
                finishReviewsRender(reviews);
                if (typeof renderPagination === 'function') {
                    $('#rvReviewsPagination').html(renderPagination(page, totalPages));
                } else {
                    $('#rvReviewsPagination').html('');
                }
            }
        }).fail(function () {
            $list.html('');
            $('#rvReviewsPagination').html('');
            rvToast('error', rvPhrase('_rv_networkError', 'Ошибка сети'));
        }).always(function () {
            state.loading = false;
            applyPendingDeepLink();
        });
    }

    function flashTarget($el) {
        if (!$el || !$el.length) {
            return;
        }
        $el.addClass('is-target');
        setTimeout(function () {
            $el.removeClass('is-target');
        }, 2200);
        var node = $el[0];
        if (node && typeof node.scrollIntoView === 'function') {
            node.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function openCommentsPanel($review) {
        var $panel = $review.find('[data-rv-comments]');
        var $toggle = $review.find('[data-rv-comments-toggle]');
        if ($panel.length && $panel.prop('hidden')) {
            $panel.prop('hidden', false);
            $toggle.attr('aria-expanded', 'true').addClass('is-open');
        }
        if (!$review.data('thread-loaded')) {
            loadThread($review);
            $review.data('thread-loaded', true);
        }
    }

    function focusCommentInReview($review, commentId) {
        openCommentsPanel($review);

        function tryFocus(attempt) {
            var $comment = $review.find('.rv__comment[data-comment-id="' + commentId + '"]');
            if ($comment.length) {
                var $replies = $comment.closest('[data-rv-replies]');
                if ($replies.length && $replies.prop('hidden')) {
                    $replies.prop('hidden', false).removeClass('is-collapsed');
                    var $toggle = $replies.siblings('.rv__comment-actions').find('[data-rv-replies-toggle]');
                    if ($toggle.length) {
                        $toggle.attr('aria-expanded', 'true').text(rvPhrase('_rv_hideRepliesShort', 'Скрыть ответы'));
                    }
                }
                $comment.parents('[data-rv-replies]').each(function () {
                    var $box = $(this);
                    if ($box.prop('hidden')) {
                        $box.prop('hidden', false).removeClass('is-collapsed');
                        $box.closest('.rv__comment').find('> .rv__comment-actions [data-rv-replies-toggle]')
                            .attr('aria-expanded', 'true');
                    }
                });
                flashTarget($comment);
                return;
            }
            if (attempt < 20) {
                setTimeout(function () {
                    tryFocus(attempt + 1);
                }, 150);
            } else {
                flashTarget($review);
            }
        }

        tryFocus(0);
    }

    function focusReviewTarget(reviewId, commentId) {
        var $review = $list.find('.rv__review[data-review-id="' + reviewId + '"]');
        if (!$review.length) {
            return false;
        }
        if (commentId) {
            focusCommentInReview($review, commentId);
        } else {
            flashTarget($review);
        }
        return true;
    }

    function applyPendingDeepLink() {
        if (!pendingDeepLink || !pendingDeepLink.reviewId || pendingDeepLink.resolving || state.loading) {
            return;
        }

        if (focusReviewTarget(pendingDeepLink.reviewId, pendingDeepLink.commentId)) {
            pendingDeepLink = null;
            clearRvDeepLinkUrl();
            return;
        }

        pendingDeepLink.resolving = true;
        var target = pendingDeepLink;

        sendRequest({
            get_review_view: true,
            id: target.reviewId,
            sort: state.sort || 'date',
            limit: state.limit,
            search: state.search || '',
            stars: state.stars || []
        }).done(function (res) {
            if (!pendingDeepLink || pendingDeepLink.reviewId !== target.reviewId) {
                return;
            }
            pendingDeepLink.resolving = false;

            if (!res || res.status !== 'success' || !res.review) {
                pendingDeepLink = null;
                clearRvDeepLinkUrl();
                rvToast('error', (res && res.message) || rvPhrase('_rv_reviewNotFound', 'Отзыв не найден'));
                return;
            }

            var page = Math.max(1, parseInt(res.page, 10) || 1);
            if (page !== state.page) {
                loadReviews(page);
                return;
            }

            if (!$list.find('.rv__review[data-review-id="' + res.review.id + '"]').length) {
                $('#rvReviewsEmpty').prop('hidden', true).hide();
                $list.prepend(renderReview(res.review));
                finishReviewsRender([res.review]);
            }

            focusReviewTarget(target.reviewId, target.commentId);
            pendingDeepLink = null;
            clearRvDeepLinkUrl();
        }).fail(function () {
            if (pendingDeepLink && pendingDeepLink.reviewId === target.reviewId) {
                pendingDeepLink.resolving = false;
                pendingDeepLink = null;
            }
            clearRvDeepLinkUrl();
            rvToast('error', rvPhrase('_rv_openReviewFailed', 'Не удалось открыть отзыв'));
        });
    }

    function collectAttrs(modalId, namePrefix) {
        var attrs = {};
        var ok = true;
        var groups = $('#' + modalId + ' .rv__modal-attrs .rv__rate');
        if (!groups.length) {
            return null;
        }

        groups.each(function () {
            var $checked = $(this).find('input:checked');
            if (!$checked.length) {
                ok = false;
                return false;
            }
            var name = String($checked.attr('name') || '');
            var match = name.match(new RegExp('^' + namePrefix.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\[(.+)]$'));
            if (!match) {
                ok = false;
                return false;
            }
            attrs[match[1]] = parseInt($checked.val(), 10);
        });

        return ok ? attrs : null;
    }

    function collectOverall(modalId) {
        var $checked = $('#' + modalId + ' [data-rv-overall-rate] input:checked');
        if (!$checked.length) {
            return null;
        }
        var value = parseInt($checked.val(), 10);
        return value >= 1 && value <= 5 ? value : null;
    }

    function setOverall(modalId, value) {
        var $group = $('#' + modalId + ' [data-rv-overall-rate]');
        $group.find('input[type="radio"]').prop('checked', false);
        value = Math.round(Number(value) || 0);
        if (value >= 1 && value <= 5) {
            $group.find('input[value="' + value + '"]').prop('checked', true);
        }
    }

    function selectedServerId(name) {
        var $checked = $('input[name="' + name + '"]:checked');
        return $checked.length ? parseInt($checked.val(), 10) : -1;
    }

    function setServerRadio(name, serverId) {
        var $input = $('input[name="' + name + '"][value="' + serverId + '"]');
        if ($input.length) {
            $input.prop('checked', true).trigger('change');
        }
    }

    function setAttrs(modalId, namePrefix, map) {
        $('#' + modalId + ' .rv__modal-attrs input[type="radio"]').prop('checked', false);
        if (!map) {
            $('#' + modalId + ' .rv__modal-attrs input[type="radio"]').first().trigger('change');
            return;
        }
        Object.keys(map).forEach(function (key) {
            var val = map[key];
            $('#' + modalId + ' input[name="' + namePrefix + '[' + key + ']"][value="' + val + '"]')
                .prop('checked', true)
                .trigger('change');
        });
    }

    function collectOpenReplyIds($review) {
        var ids = [];
        $review.find('[data-rv-replies-toggle][aria-expanded="true"]').each(function () {
            var id = $(this).closest('.rv__comment').attr('data-comment-id');
            if (id) {
                ids.push(String(id));
            }
        });
        return ids;
    }

    function expandRepliesByIds($review, ids) {
        var seen = {};
        (ids || []).forEach(function (id) {
            id = String(id || '');
            if (!id || seen[id]) {
                return;
            }
            seen[id] = true;

            var $comment = $review.find('.rv__comment[data-comment-id="' + id + '"]').first();
            if (!$comment.length) {
                return;
            }
            var $replies = $comment.children('[data-rv-replies]');
            if (!$replies.length) {
                return;
            }
            var count = $replies.children('.rv__comment').length;
            $replies.prop('hidden', false).removeClass('is-collapsed');
            $comment.children('.rv__comment-actions').find('[data-rv-replies-toggle]')
                .attr('aria-expanded', 'true')
                .text(rvPhrase('_rv_hideReplies', 'Скрыть ответы (%count%)').replace('%count%', String(count)));
        });
    }

    function loadThread($review, opts) {
        opts = opts || {};
        var id = parseInt($review.attr('data-review-id'), 10);
        var $wrap = $review.find('[data-comments-list]');
        var keepOpen = collectOpenReplyIds($review);
        if (opts.expandParentId) {
            keepOpen.push(String(opts.expandParentId));
        }
        $wrap.html('<div class="rv__comments-loading">' + rvEscapeHtml(rvPhrase('_rv_loading', 'Загрузка...')) + '</div>');

        sendRequest({ get_review_thread: true, review_id: id }).done(function (res) {
            if (!res || res.status !== 'success') {
                $wrap.html('<div class="rv__comments-empty">' + rvEscapeHtml(rvPhrase('_rv_commentsLoadFailed', 'Не удалось загрузить комментарии')) + '</div>');
                return;
            }
            var comments = res.comments || [];
            if (!comments.length) {
                $wrap.html('<div class="rv__comments-empty">' + rvEscapeHtml(rvPhrase('_rv_noComments', 'Пока нет комментариев')) + '</div>');
                return;
            }
            $wrap.html(comments.map(function (c) { return renderComment(c, true, id); }).join(''));
            finishReviewsRender(comments);
            expandRepliesByIds($review, keepOpen);
            $review.find('.rv__comments-toggle').contents().filter(function () {
                return this.nodeType === 3;
            }).first().replaceWith(rvPhrase('_rv_commentsCount', 'Комментарии (%count%)').replace('%count%', String(res.total || comments.length)));
        }).fail(function () {
            $wrap.html('<div class="rv__comments-empty">' + rvEscapeHtml(rvPhrase('_rv_networkError', 'Ошибка сети')) + '</div>');
        });
    }

    function fillEditModal(review) {
        $('#rvEditId').val(review.id);
        setServerRadio('rv-edit-server', review.server_id);
        if (hasCriteria) {
            setAttrs('editReview', 'edit_attrs', review.attrs_map || {});
        } else {
            setOverall('editReview', review.overall);
        }
        $('#rvEditPros').val(review.pros || '');
        $('#rvEditCons').val(review.cons || '');
        $('#rvEditComment').val(review.comment || '');
        openPopupModal('editReview');
    }

    $('#rvSearch').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { loadReviews(1); }, 300);
    });
    $(document).on('change', 'input[name="rv-star"], input[name="rv-sort"]', function () {
        loadReviews(1);
    });
    $('#rvResetFilters').on('click', function () {
        $('#rvSearch').val('');
        $('input[name="rv-star"]').prop('checked', false);
        $('#rvSortDate').prop('checked', true);
        loadReviews(1);
    });

    $(document).on('click', '#rvReviewsPagination .button_pagination[data-page]', function (e) {
        e.preventDefault();
        var page = parseInt($(this).data('page'), 10) || 1;
        loadReviews(page);
        var top = $list.offset();
        if (top) {
            $('html, body').stop(true).animate({ scrollTop: Math.max(0, top.top - 80) }, 200);
        }
    });

    function handleRvMenuAction(action) {
        if (!activeTrigger || !action) return;

        var kind = activeTrigger.getAttribute('data-menu-kind') || 'review';
        var reviewId = parseInt(activeTrigger.getAttribute('data-review-id'), 10) || 0;
        var commentId = parseInt(activeTrigger.getAttribute('data-comment-id'), 10) || 0;
        var $comment = $(activeTrigger).closest('.rv__comment');
        var $review = $(activeTrigger).closest('.rv__review');

        closeRvMenu();

        if (!reviewId && $review.length) {
            reviewId = parseInt($review.attr('data-review-id'), 10) || 0;
        }

        if (action === 'copy_link' && reviewId) {
            copyRvLink(buildRvDeepLink(reviewId, kind === 'comment' ? commentId : 0));
            return;
        }

        if (kind === 'comment') {
            if (action === 'edit' && commentId && $comment.length) {
                $comment.find('[data-comment-text]').prop('hidden', true);
                $comment.find('[data-rv-edit-form]').prop('hidden', false);
                $comment.find('[data-rv-edit-form] textarea').trigger('focus');
            }
            if (action === 'delete' && commentId) {
                openDialog({
                    message: rvPhrase('_rv_deleteCommentConfirm', 'Удалить комментарий? Ответы к нему тоже будут удалены.'),
                    confirmText: (typeof phrase === 'function' ? phrase('_Delete_Action') : null) || rvPhrase('_rv_delete', 'Удалить'),
                    cancelText: rvPhrase('_rv_no', 'Нет'),
                    onConfirm: function () {
                        sendRequest({ delete_comment: true, id: commentId }).done(function (res) {
                            if (!res || res.status !== 'success') {
                                rvToast('error', (res && res.message) || rvPhrase('_rv_deleteFailed', 'Не удалось удалить'));
                                return;
                            }
                            rvToast('success', res.message || rvPhrase('_rv_deleted', 'Удалено'));
                            if ($review.length) {
                                $review.data('thread-loaded', false);
                                loadThread($review);
                                $review.data('thread-loaded', true);
                                if (typeof res.comments_count === 'number') {
                                    $review.find('.rv__comments-toggle').contents().filter(function () {
                                        return this.nodeType === 3;
                                    }).first().replaceWith(rvPhrase('_rv_commentsCount', 'Комментарии (%count%)').replace('%count%', String(res.comments_count)));
                                }
                            }
                        });
                    }
                });
            }
            return;
        }

        if (action === 'edit' && reviewId) {
            sendRequest({ get_review: true, id: reviewId }).done(function (res) {
                if (!res || res.status !== 'success') {
                    rvToast('error', (res && res.message) || rvPhrase('_rv_openReviewFailed', 'Не удалось открыть отзыв'));
                    return;
                }
                fillEditModal(res.review);
            });
        }

        if (action === 'delete' && reviewId) {
            openDialog({
                message: rvPhrase('_rv_deleteReviewConfirm', 'Удалить отзыв?'),
                confirmText: (typeof phrase === 'function' ? phrase('_Delete_Action') : null) || rvPhrase('_rv_delete', 'Удалить'),
                cancelText: rvPhrase('_rv_no', 'Нет'),
                onConfirm: function () {
                    sendRequest({ delete_review: true, id: reviewId }).done(function (res) {
                        if (!res || res.status !== 'success') {
                            rvToast('error', (res && res.message) || rvPhrase('_rv_deleteFailed', 'Не удалось удалить'));
                            return;
                        }
                        rvToast('success', res.message || rvPhrase('_rv_deleted', 'Удалено'));
                        loadReviews();
                    });
                }
            });
        }
    }

    $(document).on('click', '.rv__menu-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if ($(this).attr('data-menu-open')) {
            closeRvMenu();
        } else {
            openRvMenu(this);
        }
    });

    $(document).on('click', '.at__action-list button[data-action]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        handleRvMenuAction($(this).attr('data-action'));
    });

    $(document).on('click', function () {
        closeRvMenu();
    });

    $list.on('click', '[data-cancel-edit-comment]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $comment = $(this).closest('.rv__comment');
        $comment.find('[data-rv-edit-form]').prop('hidden', true);
        $comment.find('[data-comment-text]').prop('hidden', false);
    });

    $list.on('click', '[data-save-edit-comment]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (!isAuth) {
            location.href = '?auth=login';
            return;
        }
        var $btn = $(this);
        var $comment = $btn.closest('.rv__comment');
        var id = parseInt($comment.attr('data-comment-id'), 10);
        var text = String($comment.find('[data-rv-edit-form] textarea').val() || '').trim();
        var textError = rvValidateRequiredText(text, rvPhrase('_rv_comment', 'Комментарий'));
        if (textError) {
            rvToast('error', textError);
            return;
        }

        sendRequestWithButton($btn, {
            update_comment: true,
            id: id,
            text: text
        }).done(function (res) {
            if (!res || res.status !== 'success') {
                rvToast('error', (res && res.message) || rvPhrase('_rv_saveFailed', 'Не удалось сохранить'));
                return;
            }
            var nextText = (res.comment && res.comment.text) ? res.comment.text : text;
            var nextEdit = (res.comment && res.comment.text_edit != null) ? res.comment.text_edit : text;
            $comment.find('[data-comment-text]').text(nextText).prop('hidden', false);
            $comment.find('[data-rv-edit-form] textarea').val(nextEdit);
            $comment.find('[data-rv-edit-form]').prop('hidden', true);
            rvToast('success', res.message || rvPhrase('_rv_saved', 'Сохранено'));
        });
    });

    $(window).on('scroll resize', function () {
        if (activeMenu && activeTrigger) positionRvMenu();
    });
    if (typeof window._rvScrollCapture === 'undefined') {
        window._rvScrollCapture = true;
        document.addEventListener('scroll', function () {
            if (activeMenu && activeTrigger) positionRvMenu();
        }, true);
    }
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') closeRvMenu();
    });
    $(document).on('click', '.at__action-list', function (e) {
        e.stopPropagation();
    });

    $list.on('click', '[data-vote]', function () {
        if (!isAuth) {
            location.href = '?auth=login';
            return;
        }
        if (banVote) {
            rvToast('error', banMessage('vote'));
            return;
        }
        var $wrap = $(this).closest('[data-vote-kind]');
        var kind = $wrap.attr('data-vote-kind');
        var id = parseInt($wrap.attr('data-vote-id'), 10);
        var value = parseInt($(this).attr('data-vote'), 10);
        var body = kind === 'comment'
            ? { vote_comment: true, id: id, value: value }
            : { vote_review: true, id: id, value: value };

        sendRequest(body).done(function (res) {
            if (!res || res.status !== 'success') {
                rvToast('error', (res && res.message) || rvPhrase('_rv_voteRejected', 'Голос не принят'));
                return;
            }
            $wrap.find('.rv__vote-score').attr('class', 'rv__vote-score ' + rvScoreClass(res.score)).text(rvFormatScore(res.score));
            $wrap.find('[data-vote]').removeClass('is-active');
            if (res.my_vote === 1 || res.my_vote === -1) {
                $wrap.find('[data-vote="' + res.my_vote + '"]').addClass('is-active');
            }
        });
    });

    $list.on('click', '[data-rv-comments-toggle]', function () {
        var $btn = $(this);
        var $review = $btn.closest('.rv__review');
        var $box = $review.find('[data-rv-comments]');
        var open = $btn.attr('aria-expanded') === 'true';
        if (open) {
            $box.prop('hidden', true);
            $btn.attr('aria-expanded', 'false');
            return;
        }
        $box.prop('hidden', false);
        $btn.attr('aria-expanded', 'true');
        if (!$review.data('thread-loaded')) {
            loadThread($review);
            $review.data('thread-loaded', true);
        }
    });

    $list.on('click', '[data-rv-replies-toggle]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $btn = $(this);
        var $comment = $btn.closest('.rv__comment');
        var $replies = $comment.children('[data-rv-replies]');
        if (!$replies.length) {
            $replies = $comment.find('> [data-rv-replies]');
        }
        if (!$replies.length) return;

        var open = $btn.attr('aria-expanded') !== 'false';
        $replies.prop('hidden', open).toggleClass('is-collapsed', open);
        $btn.attr('aria-expanded', open ? 'false' : 'true');
        var count = $replies.children('.rv__comment').length;
        $btn.text(
            rvPhrase(
                open ? '_rv_showReplies' : '_rv_hideReplies',
                open ? 'Показать ответы (%count%)' : 'Скрыть ответы (%count%)'
            ).replace('%count%', String(count))
        );
    });

    $list.on('click', '[data-reply-to]', function () {
        if (!isAuth) {
            location.href = '?auth=login';
            return;
        }
        if (banReply) {
            rvToast('error', banMessage('reply'));
            return;
        }
        var $comment = $(this).closest('.rv__comment');
        var $form = $comment.children('[data-rv-reply-form]').first();
        var open = $form.prop('hidden');

        $list.find('[data-rv-reply-form]').prop('hidden', true);
        $form.prop('hidden', !open);
        if (open) {
            $form.find('textarea').trigger('focus');
        }
    });

    $list.on('click', '[data-send-comment]', function () {
        if (!isAuth) {
            location.href = '?auth=login';
            return;
        }
        if (banComment) {
            rvToast('error', banMessage('comment'));
            return;
        }
        var $review = $(this).closest('.rv__review');
        var $ta = $(this).closest('.rv__composer').find('textarea');
        var text = String($ta.val() || '').trim();
        var textError = rvValidateRequiredText(text, rvPhrase('_rv_comment', 'Комментарий'));
        if (textError) {
            rvToast('error', textError);
            return;
        }

        sendRequestWithButton($(this), {
            create_comment: true,
            review_id: parseInt($review.attr('data-review-id'), 10),
            text: text
        }).done(function (res) {
            if (!res || res.status !== 'success') {
                rvToast('error', (res && res.message) || rvPhrase('_rv_sendFailed', 'Не удалось отправить'));
                return;
            }
            $ta.val('');
            $review.data('thread-loaded', false);
            loadThread($review);
            $review.data('thread-loaded', true);
        });
    });

    $list.on('click', '[data-send-reply]', function () {
        if (!isAuth) {
            location.href = '?auth=login';
            return;
        }
        if (banReply) {
            rvToast('error', banMessage('reply'));
            return;
        }
        var $form = $(this).closest('[data-rv-reply-form]');
        var $review = $(this).closest('.rv__review');
        var $ta = $form.find('textarea');
        var parentId = parseInt($form.attr('data-parent-id'), 10) || 0;
        var text = String($ta.val() || '').trim();
        var textError = rvValidateRequiredText(text, rvPhrase('_rv_comment', 'Комментарий'));
        if (textError) {
            rvToast('error', textError);
            return;
        }

        sendRequestWithButton($(this), {
            create_comment: true,
            review_id: parseInt($review.attr('data-review-id'), 10),
            parent_id: parentId,
            text: text
        }).done(function (res) {
            if (!res || res.status !== 'success') {
                rvToast('error', (res && res.message) || rvPhrase('_rv_sendFailed', 'Не удалось отправить'));
                return;
            }
            $ta.val('');
            $form.prop('hidden', true);
            $review.data('thread-loaded', false);
            loadThread($review, { expandParentId: parentId });
            $review.data('thread-loaded', true);
        });
    });

    $('#rvSubmitReview').on('click', function () {
        if (!isAuth) {
            location.href = '?auth=login';
            return;
        }

        var attrs = {};
        var overall = null;
        if (hasCriteria) {
            attrs = collectAttrs('addReview', 'attrs');
            if (!attrs) {
                rvToast('error', rvPhrase('_rv_rateAllCriteria', 'Оцените все критерии'));
                return;
            }
        } else {
            overall = collectOverall('addReview');
            if (!overall) {
                rvToast('error', rvPhrase('_rv_setOverall', 'Поставьте общую оценку'));
                return;
            }
        }

        var pros = String($('#rvPros').val() || '');
        var cons = String($('#rvCons').val() || '');
        var comment = String($('#rvComment').val() || '');
        var textsError = rvValidateReviewTexts(pros, cons, comment);
        if (textsError) {
            rvToast('error', textsError);
            return;
        }

        var payload = {
            create_review: true,
            server_id: selectedServerId('rv-server'),
            pros: pros,
            cons: cons,
            comment: comment,
            attrs: attrs
        };
        if (overall !== null) {
            payload.overall = overall;
        }

        sendRequestWithButton($(this), payload).done(function (res) {
            if (!res || res.status !== 'success') {
                rvToast('error', (res && res.message) || rvPhrase('_rv_saveFailed', 'Не удалось сохранить'));
                return;
            }
            rvToast('success', res.message || rvPhrase('_rv_reviewAdded', 'Отзыв добавлен'));
            if (res.reward && res.reward.given) {
                var rewardText = (typeof get_translate_module_phrase === 'function')
                    ? get_translate_module_phrase('module_page_reviews', '_rv_rewardReceived')
                    : 'Reward +%amount%';
                rvToast('success', String(rewardText).replace('%amount%', String(res.reward.amount || 0)));
            }
            closePopupModal('addReview');
            $('#rvPros, #rvCons, #rvComment').val('');
            if (hasCriteria) {
                setAttrs('addReview', 'attrs', null);
            } else {
                setOverall('addReview', 0);
            }
            setServerRadio('rv-server', -1);
            loadReviews(1);
        });
    });

    $('#rvSaveReview').on('click', function () {
        var attrs = {};
        var overall = null;
        if (hasCriteria) {
            attrs = collectAttrs('editReview', 'edit_attrs');
            if (!attrs) {
                rvToast('error', rvPhrase('_rv_rateAllCriteria', 'Оцените все критерии'));
                return;
            }
        } else {
            overall = collectOverall('editReview');
            if (!overall) {
                rvToast('error', rvPhrase('_rv_setOverall', 'Поставьте общую оценку'));
                return;
            }
        }

        var pros = String($('#rvEditPros').val() || '');
        var cons = String($('#rvEditCons').val() || '');
        var comment = String($('#rvEditComment').val() || '');
        var textsError = rvValidateReviewTexts(pros, cons, comment);
        if (textsError) {
            rvToast('error', textsError);
            return;
        }

        var payload = {
            update_review: true,
            id: parseInt($('#rvEditId').val(), 10),
            server_id: selectedServerId('rv-edit-server'),
            pros: pros,
            cons: cons,
            comment: comment,
            attrs: attrs
        };
        if (overall !== null) {
            payload.overall = overall;
        }

        sendRequestWithButton($(this), payload).done(function (res) {
            if (!res || res.status !== 'success') {
                rvToast('error', (res && res.message) || rvPhrase('_rv_saveFailed', 'Не удалось сохранить'));
                return;
            }
            rvToast('success', res.message || rvPhrase('_rv_reviewUpdated', 'Отзыв обновлён'));
            closePopupModal('editReview');
            loadReviews();
        });
    });

    $('#searchRvServer').on('input', function () {
        var q = String($(this).val() || '').toLowerCase();
        $('#rvServerList > li').each(function () {
            var text = $(this).text().toLowerCase();
            $(this).toggle(!q || text.indexOf(q) !== -1);
        });
    });
    $('#searchRvEditServer').on('input', function () {
        var q = String($(this).val() || '').toLowerCase();
        $('#rvEditServerList > li').each(function () {
            var text = $(this).text().toLowerCase();
            $(this).toggle(!q || text.indexOf(q) !== -1);
        });
    });

    loadReviews(1);
    if (hasCriteria) {
        bindRvOverall('addReview', 'rvOverallValue', 'rvOverallStars');
        bindRvOverall('editReview', 'rvEditOverallValue', 'rvEditOverallStars');
    }
})(jQuery);
