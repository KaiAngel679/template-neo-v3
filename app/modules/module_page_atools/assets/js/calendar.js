function formatAtCalendarDate(isoDate) {
    if (!isoDate) {
        return '';
    }

    var parts = isoDate.split('-');
    return parts.length === 3 ? parts[2] + '.' + parts[1] + '.' + parts[0] : isoDate;
}

function getCalendarDropdown($filterWrap) {
    return $filterWrap.find('.at__calendar-dropdown');
}

function isCalendarOpen($filterWrap) {
    return getCalendarDropdown($filterWrap).hasClass('visible');
}

function setCalendarOpen($filterWrap, open) {
    getCalendarDropdown($filterWrap).toggleClass('visible', !!open);
}

function closeAtAdaptiveSelects() {
    $('.adaptive-select__dropdown-list.visible').removeClass('visible');
}

function closeAtOpenCalendars(except) {
    var $except = except ? $(except) : $();

    $('.at__calendar-filter').not($except).each(function () {
        setCalendarOpen($(this), false);
    });
}

function getAtCalendarDateRange(calendarApi) {
    if (calendarApi && typeof calendarApi.getDateRange === 'function') {
        return calendarApi.getDateRange();
    }

    return {
        from: '',
        to: ''
    };
}

$(document).on('click', '[open-select]', function () {
    closeAtOpenCalendars();
});

function initAtRangeCalendar(options) {
    if (!window.VanillaCalendarPro || !options || !options.calendarSelector) {
        return null;
    }

    var Calendar = window.VanillaCalendarPro.Calendar;
    var $calendarEl = $(options.calendarSelector);

    if (!$calendarEl.length) {
        return null;
    }

    var $startInput = $(options.startInput);
    var $endInput = $(options.endInput);
    var $periodEl = options.periodSelector ? $(options.periodSelector) : $();
    var $filterWrap = options.filterSelector ? $(options.filterSelector) : $calendarEl.closest('.at__calendar-filter');
    var emptyLabel = options.emptyLabel || '';
    var onChange = options.onChange;
    var calendarInstance;

    if (!emptyLabel && $periodEl.length) {
        emptyLabel = $periodEl.text();
    }

    function clearCalendarSelection(triggerChange) {
        calendarInstance.set({ selectedDates: [] });
        updatePeriod([]);

        if (triggerChange && typeof onChange === 'function') {
            onChange($startInput.val(), $endInput.val());
        }
    }

    function updatePeriod(dates) {
        if (!dates || !dates.length) {
            $startInput.val('');
            $endInput.val('');
            if ($periodEl.length) {
                $periodEl.text(emptyLabel);
            }
            return;
        }

        $startInput.val(dates[0]);
        $endInput.val(dates[dates.length - 1]);

        if ($periodEl.length) {
            var text = formatAtCalendarDate($startInput.val());
            if ($endInput.val() && $endInput.val() !== $startInput.val()) {
                text += ' - ' + formatAtCalendarDate($endInput.val());
            }
            $periodEl.text(text);
        }
    }

    var locale = (typeof lang !== 'undefined' ? lang : 'RU').toLowerCase();

    calendarInstance = new Calendar(options.calendarSelector, {
        locale: locale,
        selectionDatesMode: 'multiple-ranged',
        selectedDates: [],
        onClickDate: function (self) {
            updatePeriod(self.context.selectedDates);
            if (typeof onChange === 'function') {
                onChange($startInput.val(), $endInput.val());
            }
        }
    });
    calendarInstance.init();

    $filterWrap.on('click mousedown', '.at__calendar-dropdown, .at__calendar-dropdown *', function (e) {
        e.stopPropagation();
    });

    $filterWrap.on('click mousedown', function (e) {
        if (options.triggerSelector && $(e.target).closest(options.triggerSelector).length) {
            return;
        }

        e.stopPropagation();
    });

    $filterWrap.on('click', '.at__calendar-dropdown-reset', function (e) {
        e.preventDefault();
        e.stopPropagation();
        clearCalendarSelection(true);
    });

    if (options.triggerSelector) {
        $(document).on('click', options.triggerSelector, function (e) {
            e.stopPropagation();
            var isOpening = !isCalendarOpen($filterWrap);
            closeAtOpenCalendars($filterWrap);
            setCalendarOpen($filterWrap, isOpening);
            if (isOpening) {
                closeAtAdaptiveSelects();
            }
        });
    }

    $(document).on('click mousedown', function (e) {
        if (!isCalendarOpen($filterWrap)) {
            return;
        }

        if ($(e.target).closest('.at__calendar-filter').is($filterWrap)) {
            return;
        }

        if ($(e.target).closest('[data-vc]').length && $calendarEl.has(e.target).length) {
            return;
        }

        setCalendarOpen($filterWrap, false);
    });

    return {
        getDateRange: function () {
            return {
                from: $startInput.val() || '',
                to: $endInput.val() || ''
            };
        },
        reset: function () {
            clearCalendarSelection(false);
        },
        clear: function () {
            clearCalendarSelection(true);
        },
        close: function () {
            setCalendarOpen($filterWrap, false);
        }
    };
}
