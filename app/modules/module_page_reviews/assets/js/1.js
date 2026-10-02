(function ($) {
    'use strict';

    var RV_JS_CACHE_SUFFIX = (function () {
        var suffix = '';
        $('script[src*="/module_page_reviews/assets/js/1.js"]').each(function () {
            var src = String($(this).attr('src') || '');
            var queryIndex = src.indexOf('?');
            if (queryIndex !== -1) {
                suffix = src.slice(queryIndex);
                return false;
            }
        });
        return suffix;
    })();

    function rvWithCacheBust(path) {
        if (!RV_JS_CACHE_SUFFIX) {
            return path;
        }

        return path + (path.indexOf('?') === -1 ? RV_JS_CACHE_SUFFIX : '&' + RV_JS_CACHE_SUFFIX.slice(1));
    }

    function rvLoadScript(src) {
        return $.getScript(rvWithCacheBust(src));
    }

    function rvLoadScriptsSequential(files, basePath) {
        return files.reduce(function (chain, file) {
            return chain.then(function () {
                return rvLoadScript(basePath + file);
            });
        }, $.Deferred().resolve().promise());
    }

    var RV_JS_BASE = '/app/modules/module_page_reviews/assets/js/';
    var path = location.pathname || '';
    var pageScripts = path.indexOf('/reviews/settings') !== -1
        ? ['utils.js', 'pages/settings.js']
        : ['utils.js', 'pages/main.js'];

    rvLoadScriptsSequential(pageScripts, RV_JS_BASE).fail(function () {
        console.error('Reviews JS load failed');
    });
})(jQuery);
