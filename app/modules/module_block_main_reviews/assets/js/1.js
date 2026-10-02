(function ($) {
    'use strict';

    var SCROLL_STEP = 0.85;
    var WHEEL_SPEED = 1.1;
    var WHEEL_EASE = 0.22;

    function updateEdges($scroller, $track) {
        var track = $track[0];
        if (!track) return;
        var max = track.scrollWidth - track.clientWidth;
        $scroller.toggleClass('is-start', track.scrollLeft <= 1);
        $scroller.toggleClass('is-end', track.scrollLeft >= max - 1 || max <= 1);
    }

    function bindWheel($track) {
        var track = $track[0];
        if (!track) return;

        var target = track.scrollLeft;
        var current = track.scrollLeft;
        var active = false;

        function normalize(e) {
            var oe = e.originalEvent || e;
            return oe.deltaY * (oe.deltaMode === 1 ? 16 : oe.deltaMode === 2 ? window.innerHeight : 1);
        }

        function tick() {
            var diff = target - current;
            if (Math.abs(diff) < 0.5) {
                track.scrollLeft = target;
                active = false;
                return;
            }
            current += diff * WHEEL_EASE;
            track.scrollLeft = current;
            requestAnimationFrame(tick);
        }

        $track.on('scroll', function () {
            if (!active) {
                target = current = track.scrollLeft;
            }
        });

        $track.on('wheel', function (e) {
            var oe = e.originalEvent || e;
            if (Math.abs(oe.deltaX) > Math.abs(oe.deltaY)) return;

            var max = track.scrollWidth - track.clientWidth;
            var delta = normalize(e);
            if (max <= 0 || (delta > 0 && target >= max - 0.5) || (delta < 0 && target <= 0)) return;

            e.preventDefault();
            target = Math.max(0, Math.min(max, target + delta * WHEEL_SPEED));
            if (!active) {
                active = true;
                current = track.scrollLeft;
                requestAnimationFrame(tick);
            }
        });
    }

    function initScroller($scroller) {
        var $track = $scroller.find('[data-mbr-track]').first();
        if (!$track.length) return;

        var refresh = function () {
            updateEdges($scroller, $track);
        };

        bindWheel($track);

        $track.on('mouseenter', '.mbr__card:last-child', function () {
            var track = $track[0];
            var max = track.scrollWidth - track.clientWidth;
            if (max > 0 && track.scrollLeft >= max - 14) {
                track.scrollTo({ left: max, behavior: 'smooth' });
            }
        });

        $scroller.on('click', '[data-mbr-nav]', function () {
            var dir = $(this).attr('data-mbr-nav') === 'next' ? 1 : -1;
            var track = $track[0];
            track.scrollBy({ left: dir * track.clientWidth * SCROLL_STEP, behavior: 'smooth' });
        });

        $track.on('scroll', refresh);
        $(window).on('resize', refresh);
        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(refresh).observe($track[0]);
        }

        refresh();
    }

    $(function () {
        $('[data-mbr-scroller]').each(function () {
            initScroller($(this));
        });
    });
})(jQuery);
