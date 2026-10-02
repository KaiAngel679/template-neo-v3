;(function($) {
  var units = {
      en: ['days', 'hours', 'minutes', 'seconds'],
      ru: ['дней', 'часов', 'минут', 'секунд'],
      sec: [86400, 3600, 60, 1]
    },
    defaults = {
      etType: 1,
      etDate: '0',
      etTitleText: '',
      etTitleSize: 14,
      etShowSign: 'EN',
      etSep: ':',
      etFontFamily: 'Arial',
      etTextColor: 'black',
      etPaddingTB: 0,
      etPaddingLR: 0,
      etBackground: 'transparent',
      etBorderSize: 0,
      etBorderRadius: 0,
      etBorderColor: 'transparent',
      etShadow: '',
      etLastUnit: 2,
      etNumberFontFamily: 'Arial',
      etNumberSize: 32,
      etNumberColor: 'black',
      etNumberPaddingTB: 0,
      etNumberPaddingLR: 0,
      etNumberBackground: 'transparent',
      etNumberBorderSize: 0,
      etNumberBorderRadius: 0,
      etNumberBorderColor: 'transparent',
      etNumberShadow: ''
    };

  $.fn.eTimer = function(options) {
    var config = $.extend({}, defaults, options);

    return this.each(function() {
      var element = $(this),
        date = config.etDate,
        dayNum = 2;

      element.date = function() {
        var now = new Date();
        if (config.etType == 1) {
          date = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
        } else if (config.etType == 2) {
          var day = now.getDay();
          if (day == 0) day = 7;
          date = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 8 - day);
        } else if (config.etType == 3) {
          date = new Date(now.getFullYear(), now.getMonth() + 1, 1);
        } else {
          date = date.split('.');
          date = new Date(date[2], date[1], date[0], date[3], date[4]);
          if (Math.floor((date - now) / units.sec[0] / 1000) >= 100) dayNum = 3;
        }
      };

      element.layout = function() {
        var unit,
          elClass = element.attr('class').split(' ')[0];
        element.html('').addClass('eTimer').append('<div class="etTitle">' + config.etTitleText + '</div>');
        $.each(units.en, function(i) {
          if (i < config.etLastUnit) {
            unit = $('<div class="etUnit et' + this + '"></div>').appendTo(element).append('<div class="etNumber">0</div>').append('<div class="etNumber">0</div>').after('<div class="etSep">' + config.etSep + '</div>');
            if (i == 0 && dayNum == 3) unit.append('<div class="etNumber">0</div>');
            if (config.etShowSign === 'RU'){
              unit.append('<div class="etSign">' + units.ru[i] + '</div>');
            }
            else {
              unit.append('<div class="etSign">' + units.en[i].toLowerCase() + '</div>');
            }
          }
        });
        element.append('<style type="text/css">.' + elClass + ' {display: inline-block; line-height: normal; font-family: ' + config.etFontFamily + '; color: ' + config.etTextColor + '; padding: ' + config.etPaddingTB + 'px ' + config.etPaddingLR + 'px; background: ' + config.etBackground + '; border: ' + config.etBorderSize + 'px solid ' + config.etBorderColor + '; -webkit-border-radius: ' + config.etBorderRadius + 'px; -moz-border-radius: ' + config.etBorderRadius + 'px; border-radius: ' + config.etBorderRadius + 'px; -webkit-box-shadow: ' + config.etShadow + '; -moz-box-shadow: ' + config.etShadow + '; box-shadow: ' + config.etShadow + ';} .' + elClass + ' .etTitle {margin-bottom: 10px; font-size: ' + config.etTitleSize + 'px;} .' + elClass + ' .etUnit {display: inline-block;} .' + elClass + ' .etUnit .etNumber {display: inline-block; margin: 1px; text-align: center; font-family: ' + config.etNumberFontFamily + '; font-size: ' + config.etNumberSize + 'px; color: ' + config.etNumberColor + '; padding: ' + config.etNumberPaddingTB + 'px ' + config.etNumberPaddingLR + 'px; background: ' + config.etNumberBackground + '; border: ' + config.etNumberBorderSize + 'px solid ' + config.etNumberBorderColor + '; -webkit-border-radius: ' + config.etNumberBorderRadius + 'px; -moz-border-radius: ' + config.etNumberBorderRadius + 'px; border-radius: ' + config.etNumberBorderRadius + 'px; -webkit-box-shadow: ' + config.etNumberShadow + '; -moz-box-shadow: ' + config.etNumberShadow + '; box-shadow: ' + config.etNumberShadow + ';} .' + elClass + ' .etUnit .etSign {text-align: center; font-size: ' + (+config.etNumberSize / 1.5) + 'px;} .' + elClass + ' .etSep {display: inline-block; vertical-align: top; font-size: ' + config.etNumberSize + 'px; padding: ' + (+config.etNumberPaddingTB + +config.etNumberBorderSize) + 'px 5px;} .' + elClass + ' .etSep:last-of-type {display: none;}</style>').append('<style type="text/css">.' + elClass + ' .etUnit .etNumber {width: ' + $('.etNumber:visible').eq(0).css('width') + ';}</style>');
      };

      element.tick = function() {
        var timeLeft = Math.floor((date - new Date()) / 1000),
          unit;
        if (timeLeft < 0) clearInterval(element.data('interval'));
        else {
          $.each(units.en, function(i) {
            if (i < config.etLastUnit) {
              unit = Math.floor(timeLeft / units.sec[i]);
              timeLeft -= unit * units.sec[i];
              if (i == 0 && dayNum == 3) {
                element.find('.et' + this).find('.etNumber').eq(0).text(Math.floor(unit / 100) % 10);
                element.find('.et' + this).find('.etNumber').eq(1).text(Math.floor(unit / 10) % 10);
                element.find('.et' + this).find('.etNumber').eq(2).text(unit % 10);
                if ((Math.floor(unit / 100) % 10) == 0) {
                  dayNum = 2;
                  element.find('.et' + this).find('.etNumber').eq(0).remove();
                }
              } else {
                element.find('.et' + this).find('.etNumber').eq(0).text(Math.floor(unit / 10) % 10);
                element.find('.et' + this).find('.etNumber').eq(1).text(unit % 10);
              }
            }
          });
        }
      };

      clearInterval(element.data('interval'));
      element.date();
      element.layout();
      element.tick();
      element.data('interval', setInterval(function() {
        element.tick()
      }, 1000));
    });
  };
})(window);