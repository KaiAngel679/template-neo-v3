let mainCharts = {};

function setMainChartSkeleton(elId, show) {
    var $el = $('#' + elId);
    if (!$el.length) {
        return;
    }
    $el.toggleClass('skeleton--default', !!show);
}

function loadMainChart(elId, chart, days) {
    days = days || 7;
    if (!$('#' + elId).length || typeof ApexCharts === 'undefined') {
        return;
    }

    setMainChartSkeleton(elId, true);

    sendRequest({
        get_main_charts: true,
        chart: chart,
        days: days
    }).done(function (result) {
        if (result.status !== 'success') {
            setMainChartSkeleton(elId, false);
            return;
        }

        const colors = chart === 'mutes' ? ['#7c6bff', '#5a83ff'] : ['#ff4560', '#00e396'];
        const options = {
            chart: {
                type: 'area',
                height: 260,
                toolbar: { show: false },
                fontFamily: 'inherit',
                foreColor: 'var(--text-secondary)'
            },
            stroke: { curve: 'smooth', width: 2 },
            fill: {
                type: 'gradient',
                gradient: { opacityFrom: 0.35, opacityTo: 0.05 }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: result.labels || [],
                labels: { style: { colors: 'var(--text-secondary)' } }
            },
            yaxis: {
                labels: {
                    style: { colors: 'var(--text-secondary)' },
                    formatter: function (value) {
                        return Math.round(value);
                    }
                }
            },
            grid: { borderColor: 'var(--transparent-10-w)', strokeDashArray: 4 },
            colors: colors,
            series: result.series || [],
            legend: { show: false },
            tooltip: { theme: 'dark' }
        };

        const finish = function () {
            setMainChartSkeleton(elId, false);
        };

        if (mainCharts[elId]) {
            mainCharts[elId].updateOptions({
                xaxis: { categories: options.xaxis.categories },
                series: options.series,
                colors: colors
            }).then(finish).catch(finish);
            return;
        }

        mainCharts[elId] = new ApexCharts(document.querySelector('#' + elId), options);
        mainCharts[elId].render().then(finish).catch(finish);
    }).fail(function () {
        setMainChartSkeleton(elId, false);
    });
}

function showMainTopAdmins(metric) {
    $('.at__top-admins-panel').css('display', 'none');
    $('#topAdminsList-' + metric).css('display', '');
}

$(function () {
    $('#bansChartTabs').on('click', '.filter', function () {
        const days = parseInt($(this).data('range'), 10) || 7;
        $('#bansChartTabs .filter').removeClass('active');
        $(this).addClass('active');
        loadMainChart('chartBans', 'bans', days);
    });

    $('#mutesChartTabs').on('click', '.filter', function () {
        const days = parseInt($(this).data('range'), 10) || 7;
        $('#mutesChartTabs .filter').removeClass('active');
        $(this).addClass('active');
        loadMainChart('chartMutes', 'mutes', days);
    });

    $('#topAdminsTabs').on('click', '.filter', function () {
        $('#topAdminsTabs .filter').removeClass('active');
        $(this).addClass('active');
        showMainTopAdmins($(this).data('metric'));
    });

    if ($('#chartBans').length) {
        loadMainChart('chartBans', 'bans', 7);
    }
    if ($('#chartMutes').length) {
        loadMainChart('chartMutes', 'mutes', 7);
    }
});