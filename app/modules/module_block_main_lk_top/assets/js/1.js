$(function () {
  var $buttons = $('.lk-top__chips-btn');
  var $loaders = $('#lk-top-loaders');
  var $content = $('#lk-top-content');
  var currentType = 'lktop7d';

  function loadTop(type) {
    $loaders.show();
    $content.hide();
    $buttons.removeClass('active');
    $buttons.filter('[data-show-id="' + type + '"]').addClass('active');
    $.ajax({
      url: location.href,
      method: 'POST',
      data: { getLkTop: true, type: type },
      dataType: 'json',
      global: false,
      success: function (response) {
        if (response.status === 'success' && response.data.length > 0) {
          $content.empty();
          response.data.forEach(player => {
            checkAndRenderAvatar(player.check_avatar, player.steam);
            $content.append(`
              <div class="lk-top__user-data ${player.pos_class}" onclick="window.location.href='/profiles/${player.steam}/?search=1'">
                <div class="lk-top__user-avatar">
                  <img class="lk-top__user-avatar-image" src="${player.avatar}" alt="" title="" id="avatar" avatarid="${player.steam}">
                </div>
                <div class="lk-top__user-details">
                  <span class="lk-top__place">${player.pos} ${player.pos_text}</span>
                  <span class="lk-top__user-name ${player.pos_class}" id="name" nameid="${player.steam}">${player.name}</span>
                </div>
                <span class="lk-top__place-hashtag ${player.pos_class}">#</span>
                <span class="lk-top__place-number ${player.pos_class}">${player.pos}</span>
              </div>
            `);
          });
          RenderingAvatar();
          $loaders.hide();
          $content.show();
        } else {
          $content.hide();
          $loaders.show();
        }
      }
    });
  }

  $buttons.on('click', function () {
    var type = $(this).data('show-id');
    if (type !== currentType) {
      currentType = type;
      loadTop(type);
    }
  });

  loadTop(currentType);
});

function checkAndRenderAvatar(check, steamid) {
  if (check === 1) {
    avatar.push(steamid);
  }
}