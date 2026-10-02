(function ($) {
    'use strict';

    function t(key) {
        if (typeof get_translate_module_phrase === 'function') {
            return get_translate_module_phrase('module_page_reviews', key);
        }
        return key;
    }

    function phrase(key) {
        if (typeof get_translate_phrase === 'function') {
            return get_translate_phrase(key);
        }
        return key;
    }

    function collectDisplay(prefix) {
        var display = {};
        $('[id^="' + prefix + '"]').each(function () {
            var lang = this.id.replace(prefix, '').toLowerCase();
            var value = String($(this).val() || '').trim();
            if (lang && value !== '') {
                display[lang] = value;
            }
        });
        return display;
    }

    function fillDisplay(prefix, map) {
        map = map || {};
        $('[id^="' + prefix + '"]').each(function () {
            var lang = this.id.replace(prefix, '').toLowerCase();
            $(this).val(map[lang] || '');
        });
    }

    function syncMinHoursDisabled() {
        var on = $('#rvMinHoursEnabled').is(':checked');
        $('#rvMinHours').prop('disabled', !on);
    }

    function syncBlacklistDisabled() {
        var on = $('#rvBlacklistEnabled').is(':checked');
        var mode = String($('input[name="rv-blacklist-mode"]:checked').val() || 'censor');
        var styleOn = on && mode === 'censor';

        $('#rvBlacklistWords').prop('disabled', !on);
        $('input[name="rv-blacklist-mode"]').prop('disabled', !on);
        $('#rvBlacklistModeWrap').toggleClass('is-disabled', !on);

        $('input[name="rv-blacklist-style"]').prop('disabled', !styleOn);
        $('#rvBlacklistStyleWrap').toggleClass('is-disabled', !styleOn);
    }

    function saveGeneralSettings() {
        if (!$('#rvMinHoursEnabled').length && !$('#rvRewardEnabled').length && !$('#rvBlacklistEnabled').length && !$('#rvDiscordWebhookUrl').length) {
            return;
        }

        sendRequest({
            save_settings: true,
            reward_enabled: $('#rvRewardEnabled').is(':checked') ? 1 : 0,
            reward_amount: parseInt(String($('#rvRewardAmount').val() || '0'), 10) || 0,
            min_hours_enabled: $('#rvMinHoursEnabled').is(':checked') ? 1 : 0,
            min_hours: parseInt(String($('#rvMinHours').val() || '0'), 10) || 0,
            fields_mode: String($('input[name="rv-fields-mode"]:checked').val() || 'all'),
            server_select_enabled: $('#rvServerSelectEnabled').is(':checked') ? 1 : 0,
            list_columns: parseInt(String($('input[name="rv-list-columns"]:checked').val() || '1'), 10) === 2 ? 2 : 1,
            blacklist_enabled: $('#rvBlacklistEnabled').is(':checked') ? 1 : 0,
            blacklist_words: String($('#rvBlacklistWords').val() || ''),
            blacklist_mode: String($('input[name="rv-blacklist-mode"]:checked').val() || 'censor'),
            blacklist_style: String($('input[name="rv-blacklist-style"]:checked').val() || 'hearts'),
            rating_icon: String($('input[name="rv-rating-icon"]:checked').val() || 'star-fill'),
            discord_webhook_url: String($('#rvDiscordWebhookUrl').val() || '').trim(),
            discord_webhook_image: String($('#rvDiscordWebhookImage').val() || '').trim(),
            discord_webhook_color: String($('#rvDiscordWebhookColor').val() || '#5865F2').trim()
        }).done(function (result) {
            if (!result || result.status !== 'success') {
                noty((result && result.message) || 'error', 'error');
            }
        });
    }

    $('#rvMinHoursEnabled').on('change', function () {
        syncMinHoursDisabled();
        saveGeneralSettings();
    });
    $('#rvMinHours').on('change', saveGeneralSettings);
    $('#rvRewardEnabled').on('change', saveGeneralSettings);
    $('#rvRewardAmount').on('change', saveGeneralSettings);
    $(document).on('change', 'input[name="rv-fields-mode"]', saveGeneralSettings);
    $('#rvServerSelectEnabled').on('change', saveGeneralSettings);
    $(document).on('change', 'input[name="rv-list-columns"]', saveGeneralSettings);
    $('#rvBlacklistEnabled').on('change', function () {
        syncBlacklistDisabled();
        saveGeneralSettings();
    });
    $('#rvBlacklistWords').on('change', saveGeneralSettings);
    $(document).on('change', 'input[name="rv-blacklist-mode"]', function () {
        syncBlacklistDisabled();
        saveGeneralSettings();
    });
    $(document).on('change', 'input[name="rv-blacklist-style"]', saveGeneralSettings);
    $(document).on('change', 'input[name="rv-rating-icon"]', saveGeneralSettings);
    $('#rvDiscordWebhookUrl, #rvDiscordWebhookImage, #rvDiscordWebhookColor').on('change', saveGeneralSettings);
    $('#rvShowDiscordWebhookPass').on('click', function () {
        var $input = $('#rvDiscordWebhookUrl');
        $input.attr('type', $input.attr('type') === 'password' ? 'text' : 'password');
    });
    syncMinHoursDisabled();
    syncBlacklistDisabled();

    $('#rvMigrateLegacy').on('click', function () {
        var $btn = $(this);

        function doMigrate() {
            sendRequestWithButton($btn, {
                migrate_legacy_reviews: true
            }).done(function (res) {
                if (!res || res.status !== 'success') {
                    noty((res && res.message) || 'error', 'error');
                    return;
                }
                noty(
                    t('_rv_migrationSuccess')
                        .replace('%imported%', String(res.imported || 0))
                        .replace('%skipped%', String(res.skipped || 0))
                        .replace('%likes%', String(res.likes || 0)),
                    'success'
                );
            });
        }

        if (typeof openDialog === 'function') {
            openDialog({
                message: t('_rv_migrationConfirm'),
                confirmText: t('_rv_migrationRun'),
                cancelText: t('_rv_no'),
                onConfirm: doMigrate
            });
            return;
        }

        if (window.confirm(t('_rv_migrationConfirm'))) {
            doMigrate();
        }
    });

    var $tbody = $('#rvCriteriaTableBody');
    if ($tbody.length) {
        function escHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function criterionAttr(item) {
            return escHtml(JSON.stringify({
                id: Number(item.id) || 0,
                name: String(item.name || ''),
                name_raw: item.name_raw && typeof item.name_raw === 'object' ? item.name_raw : {}
            }));
        }

        function renderCriterionRow(item) {
            return '' +
                '<tr data-criterion-id="' + (Number(item.id) || 0) + '" data-criterion="' + criterionAttr(item) + '">' +
                    '<td>' + escHtml(item.name || '') + '</td>' +
                    '<td><div class="action-buttons">' +
                        '<button type="button" class="button-icon rv-btn-criterion-edit" data-openmodal="editCriterionReviews">' +
                            '<svg><use href="/resources/img/sprite.svg#edit-pen"></use></svg>' +
                        '</button>' +
                        '<button type="button" class="button-icon button-delete rv-btn-criterion-delete">' +
                            '<svg><use href="/resources/img/sprite.svg#trash"></use></svg>' +
                        '</button>' +
                    '</div></td>' +
                '</tr>';
        }

        $('#rvCriterionAdd').on('click', function () {
            var display = collectDisplay('rvCriterionNameAdd');
            if (!Object.keys(display).length) {
                noty(t('_rv_enterName'), 'error');
                return;
            }

            sendRequestWithButton($(this), {
                create_criterion: true,
                display: display
            }).done(function (res) {
                if (!res || res.status !== 'success' || !res.criterion) {
                    noty((res && res.message) || 'error', 'error');
                    return;
                }
                $('[id^="rvCriterionNameAdd"]').val('');
                $tbody.append(renderCriterionRow(res.criterion));
            });
        });

        $(document).on('click', '.rv-btn-criterion-edit', function () {
            var $row = $(this).closest('tr');
            var raw = $row.attr('data-criterion');
            if (!raw) {
                return;
            }

            var item;
            try {
                item = JSON.parse(raw);
            } catch (e) {
                return;
            }

            $('#editCriterionId').val(item.id || $row.attr('data-criterion-id'));
            fillDisplay('rvCriterionNameEdit', item.name_raw || {});
        });

        $('#rvCriterionSaveEdit').on('click', function () {
            var id = parseInt($('#editCriterionId').val(), 10) || 0;
            var display = collectDisplay('rvCriterionNameEdit');
            if (!id || !Object.keys(display).length) {
                noty(t('_rv_enterName'), 'error');
                return;
            }

            var $row = $tbody.find('tr[data-criterion-id="' + id + '"]');

            sendRequestWithButton($(this), {
                update_criterion: true,
                id: id,
                display: display
            }).done(function (res) {
                if (!res || res.status !== 'success' || !res.criterion) {
                    noty((res && res.message) || 'error', 'error');
                    return;
                }
                noty(res.message || 'ok', 'success');
                $row.replaceWith(renderCriterionRow(res.criterion));
                if (typeof closePopupModal === 'function') {
                    closePopupModal('editCriterionReviews');
                }
            });
        });

        $(document).on('click', '.rv-btn-criterion-delete', function () {
            var $row = $(this).closest('tr');
            var id = parseInt($row.attr('data-criterion-id'), 10);

            function doDelete() {
                sendRequest({
                    delete_criterion: true,
                    id: id
                }).done(function (res) {
                    if (!res || res.status !== 'success') {
                        noty((res && res.message) || 'error', 'error');
                        return;
                    }
                    $row.remove();
                });
            }

            if (typeof openDialog === 'function') {
                openDialog({
                    message: t('_rv_deleteCriterionConfirm'),
                    confirmText: phrase('_Delete_Action'),
                    cancelText: t('_rv_no'),
                    onConfirm: doDelete
                });
                return;
            }

            if (window.confirm(t('_rv_deleteCriterionConfirm'))) {
                doDelete();
            }
        });
    }

    var $bansBody = $('#rvBansTableBody');
    if ($bansBody.length) {
        var scopeLabels = {
            all: t('_rv_banScopeAll'),
            review: t('_rv_banScopeReview'),
            comment: t('_rv_banScopeComment'),
            reply: t('_rv_banScopeReply'),
            vote: t('_rv_banScopeVote')
        };

        function escBan(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function renderBans(bans) {
            bans = bans || [];
            if (!bans.length) {
                $bansBody.empty();
                return;
            }

            $bansBody.html(bans.map(function (ban) {
                var scope = String(ban.scope || 'all');
                return '' +
                    '<tr data-ban-id="' + escBan(ban.id || '') + '">' +
                        '<td>' + escBan(ban.steamid || '—') + '</td>' +
                        '<td>' + escBan(ban.ip || '—') + '</td>' +
                        '<td>' + escBan(scopeLabels[scope] || scope) + '</td>' +
                        '<td>' + escBan(ban.reason || '—') + '</td>' +
                        '<td><div class="action-buttons">' +
                            '<button type="button" class="button-icon button-delete rv-btn-ban-delete">' +
                                '<svg><use href="/resources/img/sprite.svg#trash"></use></svg>' +
                            '</button>' +
                        '</div></td>' +
                    '</tr>';
            }).join(''));
        }

        $('#rvBanDeleteContent').on('change', function () {
            sendRequest({
                save_ban_options: true,
                ban_delete_content: $(this).is(':checked') ? 1 : 0
            }).done(function (res) {
                if (!res || res.status !== 'success') {
                    noty((res && res.message) || 'error', 'error');
                }
            });
        });

        $('#rvBanAdd').on('click', function () {
            sendRequestWithButton($(this), {
                create_ban: true,
                steamid: String($('#rvBanSteam').val() || '').trim(),
                ip: String($('#rvBanIp').val() || '').trim(),
                scope: String($('input[name="rv-ban-scope"]:checked').val() || 'all'),
                reason: String($('#rvBanReason').val() || '').trim()
            }).done(function (res) {
                if (!res || res.status !== 'success') {
                    noty((res && res.message) || 'error', 'error');
                    return;
                }
                $('#rvBanSteam, #rvBanIp, #rvBanReason').val('');
                $('#rvBanScope-all').prop('checked', true).trigger('change');
                renderBans(res.bans || []);
            }).fail(function () {
                noty(t('_rv_networkError'), 'error');
            });
        });

        $(document).on('click', '.rv-btn-ban-delete', function () {
            var id = String($(this).closest('tr').attr('data-ban-id') || '');
            if (!id) {
                return;
            }

            function doDeleteBan() {
                sendRequest({
                    delete_ban: true,
                    id: id
                }).done(function (res) {
                    if (!res || res.status !== 'success') {
                        noty((res && res.message) || 'error', 'error');
                        return;
                    }
                    noty(res.message || 'ok', 'success');
                    renderBans(res.bans || []);
                });
            }

            if (typeof openDialog === 'function') {
                openDialog({
                    message: t('_rv_banDeleteConfirm'),
                    confirmText: phrase('_Delete_Action'),
                    cancelText: t('_rv_no'),
                    onConfirm: doDeleteBan
                });
                return;
            }

            doDeleteBan();
        });
    }
})(jQuery);
