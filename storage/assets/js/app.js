function RenderingAvatar() {
  if (avatar.length === 0) return;

  const uniqueAvatar = Array.from(new Set(avatar));

  $.post("/app/includes/js_controller.php", {
    function: 'avatars',
    data: uniqueAvatar
  }, function (response) {
    if (!response || typeof response !== 'object') return;

    uniqueAvatar.forEach(id => {
      const data = response[id];
      if (!data) return;

      document.querySelectorAll(`[nameid="${id}"]`).forEach(el => {
        el.textContent = data.name || 'Unnamed';
      });

      document.querySelectorAll(`[avatarid="${id}"]`).forEach(el => {
        if (data.avatar) {
          el.setAttribute('src', data.avatar + '?t=' + Date.now());
        }
      });

      document.querySelectorAll(`[backgroundid="${id}"]`).forEach(container => {
        const isVideo = /\.webm(?:\?|$)/i.test(data.background);
        container.innerHTML = '';

        if (isVideo) {
          const video = document.createElement('video');
          video.setAttribute('playsinline', '');
          video.setAttribute('autoplay', '');
          video.setAttribute('muted', '');
          video.setAttribute('loop', '');

          const source = document.createElement('source');
          source.src = data.background + '?t=' + Date.now();
          source.type = 'video/webm';

          video.appendChild(source);
          container.appendChild(video);
        } else {
          const img = document.createElement('img');
          img.src = data.background + '?t=' + Date.now();
          img.alt = 'background';
          container.appendChild(img);
        }
      });
    });

    avatar = [];
  }, 'json');
}

RenderingAvatar();


function RenderingFaceit() {
  if (faceit.length === 0) return;
  const uniqueIds = Array.from(new Set(faceit));
  $.post("/app/includes/js_controller.php", {
    function: 'faceit',
    data: uniqueIds
  }, function (response) {
    if (!response || typeof response !== 'object') return;
    uniqueIds.forEach(id => {
      const stats = response[id];
      if (!stats || stats.status === 'error') return;

      document.querySelectorAll(`[faceit_nickname="${id}"]`).forEach(el => {
        el.textContent = stats.nickname || '-';
      });

      document.querySelectorAll(`[faceit_elo="${id}"]`).forEach(el => {
        el.textContent = stats.elo !== null ? stats.elo : '-';
      });

      document.querySelectorAll(`[faceit_level="${id}"]`).forEach(el => {
        el.textContent = stats.faceit_level !== null ? stats.faceit_level : '-';
      });

      document.querySelectorAll(`[faceit_level_img="${id}"]`).forEach(el => {
        if (stats.faceit_level_img) {
          el.setAttribute('src', stats.faceit_level_img);
        }
      });

      document.querySelectorAll(`[faceit_url="${id}"]`).forEach(el => {
        if (stats.url) {
          el.setAttribute('href', stats.url);
          el.style.display = '';
        }
      });

      document.querySelectorAll(`[faceit_kd="${id}"]`).forEach(el => {
        el.textContent = stats.kd_ratio !== null ? stats.kd_ratio : '-';
      });

      document.querySelectorAll(`[faceit_winrate="${id}"]`).forEach(el => {
        el.textContent = stats.win_rate !== null ? stats.win_rate + '%' : '-';
      });

      document.querySelectorAll(`[faceit_hs="${id}"]`).forEach(el => {
        el.textContent = stats.headshot_percent !== null ? stats.headshot_percent + '%' : '-';
      });

      document.querySelectorAll(`[faceit_matches="${id}"]`).forEach(el => {
        el.textContent = stats.total_matches !== null ? stats.total_matches : '-';
      });

      document.querySelectorAll(`[faceit_streak="${id}"]`).forEach(el => {
        el.textContent = stats.max_win_streak !== null ? stats.max_win_streak : '-';
      });

      document.querySelectorAll(`[faceit_last="${id}"]`).forEach(el => {
        el.textContent = Array.isArray(stats.last_5_matches) ? stats.last_5_matches.join(' ') : '-';
      });
    });
    faceit = [];
  }, 'json');
}

RenderingFaceit();

function set_options_data(data_id, change_data) {
  $.post(domain + "/app/includes/js_controller.php", {
    function: "set",
    option: data_id,
    change: change_data,
  });
  noty(get_translate_phrase('_Saved'), "success");
}

function set_options_data_select(name, value) {
  $.post(domain + "/app/includes/js_controller.php", {
    function: "set",
    option: name,
    data: value,
  });
  noty(get_translate_phrase('_Saved'), "success");
}

function SaveInStorage(key, value) {
  if (typeof Storage !== "undefined") {
    sessionStorage.setItem(key, value);
  }
}

function LoadFromStorage(key) {
  if (typeof Storage !== "undefined") {
    return sessionStorage.getItem(key);
  } else {
    return "";
  }
}
//Notifications -->
let notifications = {};
let soundPlayed = false;

function PlaySound(src) {
  let audio = new Audio(src);
  audio.play();
}

function main_notifications_refresh() {
  $.ajax({
    type: "POST",
    url: window.location.href,
    data: { entryid: 1 },
    success: function (result) {
      if (IsJsonString(result)) {
        let data = jQuery.parseJSON(result);
        SaveInStorage("notifications_count", data["count"]);
        let hasUnseenNotifications = false;

        if (data["count"] != 0) {
          $("#main_notifications_badge").html(data["count"]);
          $("#main_notifications_badge").css({
            display: "flex",
            "align-items": "center",
            "justify-content": "center",
          });
          $(".no_notify").remove();
          $("#main_notifications_all_del").css({
            "opacity": 1,
            "pointer-events": "auto"
          });

          data["notifications"].forEach(function (notification) {
            if (!notifications.hasOwnProperty(notification["id"])) {
              $("#main_notifications").prepend(notification["html"]);
              notifications[notification["id"]] = true;
              if (notification["seen"] == 0) {
                hasUnseenNotifications = true;
              }
            }
          });
          if (hasUnseenNotifications && !soundPlayed) {
            PlaySound(domain + "storage/assets/sounds/Knock.mp3");
            soundPlayed = true;
          }
        } else {
          $("#main_notifications").html(data["no_notifications"]);
          $("#main_notifications_badge").html(false);
          $("#main_notifications_badge").hide();
          $("#main_notifications_all_del").css({
            "opacity": 0,
            "pointer-events": "none"
          });
          soundPlayed = false;
        }
      }
    },
  });
}

function main_notifications_load() {
  let count_saved = LoadFromStorage("notifications_count");
  if ($.isNumeric(count_saved)) {
    main_notifications_refresh();
  }
  setInterval(main_notifications_refresh, 30000);
}

$(document).on('click', '#main_notifications_del', function () {
  let button = $(this);
  const id_del = button.attr('id_del');
  $.ajax({
    type: 'post',
    url: location.href,
    data: { main_notifications_del: true, id: id_del },
    dataType: 'json',
    global: false,
    success: function (data) {
      noty(data.text, data.status);
      button.closest(".notify_body").remove();
      main_notifications_refresh();
    },
  });
});

$(document).on('click', '#main_notifications_all_del', function () {
  $.ajax({
    type: 'post',
    url: location.href,
    data: { main_notifications_all_del: true },
    dataType: 'json',
    global: false,
    success: function (data) {
      noty(data.text, data.status);
      main_notifications_refresh();
    },
  });
});

main_notifications_load();
//<-- Notifications

function IsJsonString(str) {
  try {
    JSON.parse(str);
  } catch (e) {
    return false;
  }
  return true;
}

$(document).on("click", "#show_pass", function () {
  const eye = $(this);
  const input = eye.siblings("input");

  const isPassword = input.attr("type") === "password";
  input.attr("type", isPassword ? "text" : "password");
  return false;
});

$(document).ready(function () {
  new ClipboardJS('.copy-btn', {
    text: function (trigger) {
      noty(get_translate_phrase('_copied'), 'success');
      return $(trigger).attr('data-clipboard-text');
    }
  });
});

jscolor.presets.default = {
  alphaChannel: true,
  position: 'top',
  format: 'rgba',
  zIndex: 31000,
  previewPosition: 'right',
  backgroundColor: 'var(--card)',
  borderColor: 'var(--transparent-10-w)',
  borderRadius: 12,
  controlBorderColor: 'var(--grey)',
  height: 150,
  width: 300,
  padding: 20
}

// dialog
document.addEventListener("DOMContentLoaded", () => {
  const modalElement = document.getElementById("modal");
  const onAnimationEnd = () => {
    modalElement.close();
    modalElement.classList.remove("hide");
    modalElement.removeEventListener("animationend", onAnimationEnd);
  };

  const handleModalShow = () => {
    modalElement.showModal();
  };

  const handleModalClose = () => {
    modalElement.classList.add("hide");
    modalElement.addEventListener("animationend", onAnimationEnd);
  };

  let mouseDownTime = 0;
  let isDragging = false;

  modalElement.addEventListener('mousedown', () => {
    mouseDownTime = Date.now();
    isDragging = false;
  });

  modalElement.addEventListener('mousemove', () => {
    if (mouseDownTime !== 0) isDragging = true;
  });

  const handleModalClick = ({ currentTarget, target }) => {
    const isClickOnBackdrop = target === currentTarget;
    if (isClickOnBackdrop && !isDragging) {
      handleModalClose();
    }
    mouseDownTime = 0;
    isDragging = false;
  };

  modalElement.addEventListener("click", handleModalClick);
});

function openDialog({ title = 'Подтверждение действия', message = 'Удалить', confirmText = 'Да', cancelText = 'Нет', onConfirm = null, onCancel = null }) {
  const $modal = $('#modal');
  $('#dialog-header').text(title);
  $('#dialog-content').text(message);
  $('#confirm-btn').text(confirmText).off('.dialog').on('click.dialog', function () {
    if ($.isFunction(onConfirm)) onConfirm();
    $modal[0].close();
  });
  $('#cancel-btn').text(cancelText).off('.dialog').on('click.dialog', function () {
    if ($.isFunction(onCancel)) onCancel();
    $modal[0].close();
  });
  $modal.off('.dialog').on('close.dialog', function () {
    $modal[0].close();
  });
  $modal[0].showModal();
}
