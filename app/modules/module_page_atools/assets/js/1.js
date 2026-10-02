var AT_JS_CACHE_SUFFIX = (function () {
    var scripts = document.getElementsByTagName('script');
    var i;

    for (i = 0; i < scripts.length; i++) {
        var src = scripts[i].src || '';
        if (src.indexOf('/module_page_atools/assets/js/1.js') === -1) {
            continue;
        }
        var queryIndex = src.indexOf('?');
        if (queryIndex !== -1) {
            return src.slice(queryIndex);
        }
    }

    return '';
})();

function withCacheBust(path) {
    if (!AT_JS_CACHE_SUFFIX) {
        return path;
    }

    return path + (path.indexOf('?') === -1 ? AT_JS_CACHE_SUFFIX : '&' + AT_JS_CACHE_SUFFIX.slice(1));
}

function loadScript(src) {
    return new Promise(function (resolve, reject) {
        var script = document.createElement('script');
        script.src = withCacheBust(src);
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

function loadScriptsSequential(files, basePath) {
    return files.reduce(function (chain, file) {
        return chain.then(function () {
            return loadScript(basePath + file);
        });
    }, Promise.resolve());
}

var AT_JS_BASE = '/app/modules/module_page_atools/assets/js/';

var AT_CORE_SCRIPTS = [
    'utils.js',
    'default.js',
    'components/global-search-renders.js',
    'global-search.js',
    'components/default-renders.js'
];

var AT_CALENDAR_SCRIPTS = [
    'vendors/vanilla-calendar.js',
    'calendar.js'
];

var AT_PAGE_ROUTES = [
    {
        test: function (path) {
            return path.indexOf('/atools/admins') !== -1;
        },
        scripts: ['components/admins-renders.js', 'pages/admins.js']
    },
    {
        test: function (path) {
            return path.indexOf('/atools/punishments') !== -1;
        },
        scripts: AT_CALENDAR_SCRIPTS.concat(['components/punishments-renders.js', 'pages/punishments.js'])
    },
    {
        test: function (path) {
            return path.indexOf('/atools/checks') !== -1;
        },
        scripts: AT_CALENDAR_SCRIPTS.concat(['components/checks-renders.js', 'pages/checks.js'])
    },
    {
        test: function (path) {
            return path.indexOf('/atools/settings') !== -1;
        },
        scripts: ['pages/settings.js']
    },
    {
        test: function (path) {
            return path.indexOf('/atools/privileges') !== -1;
        },
        scripts: ['components/privileges-renders.js', 'pages/privileges.js']
    },
    {
        test: function (path) {
            return path.indexOf('/atools/logs') !== -1;
        },
        scripts: AT_CALENDAR_SCRIPTS.concat(['components/logs-renders.js', 'pages/logs.js'])
    },
    {
        test: function (path) {
            return path.indexOf('/atools/finances') !== -1;
        },
        scripts: ['components/finances-renders.js', 'pages/finances.js']
    },
    {
        test: function (path) {
            return path.indexOf('/atools/experience') !== -1;
        },
        scripts: ['components/experience-renders.js', 'pages/experience.js']
    },
    {
        test: function (path) {
            return /\/atools\/?$/.test(path) || path.indexOf('/atools/main') !== -1;
        },
        scripts: ['vendors/apexcharts.js', 'pages/main.js']
    }
];

function resolvePageScripts(pathname) {
    for (var i = 0; i < AT_PAGE_ROUTES.length; i++) {
        if (AT_PAGE_ROUTES[i].test(pathname)) {
            return AT_PAGE_ROUTES[i].scripts;
        }
    }
    return [];
}

loadScriptsSequential(AT_CORE_SCRIPTS, AT_JS_BASE)
    .then(function () {
        return loadScriptsSequential(resolvePageScripts(window.location.pathname), AT_JS_BASE);
    })
    .catch(function (err) {
        console.error('Failed to load module scripts:', err);
    });