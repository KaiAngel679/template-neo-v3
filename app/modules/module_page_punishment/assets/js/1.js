let limit = 20;
const pagepunishment = localStorage.getItem('pagepunishment') || 'bans';

function setupChoosingServers() {
  const options = document.querySelectorAll('input[name="sample"]');
  options.forEach(function (option) {
    option.addEventListener('change', function () {
      let selectedOption = document.querySelector('input[name="sample"]:checked').value;
      localStorage.setItem('pagepunishment', selectedOption);
    });
  });
  document.getElementById(pagepunishment).checked = true;
  if (pagepunishment == 'bans' || pagepunishment == 'comms') {
    $('#punishments_list').show();
    $('#punishments_title').show();
    $('#punishment_pagination').show();
    $('#admins_list').hide();
    $('#admins_title').hide();
    $('#admins_pagination').hide();
  } else {
    $('#punishments_list').hide();
    $('#punishments_title').hide();
    $('#punishment_pagination').hide();
    $('#admins_list').show();
    $('#admins_title').show();
    $('#admins_pagination').show();
  }
}

setupChoosingServers();

$(document).ready(function () {
  $(".hide_stats").click(function () {
    $(".punish_stats_list")
      .css({ height: $(".punish_stats_list").height() })
      .animate({ height: 0 }, 100);
    $(".hide_stats").hide();
    $(".show_stats").show();
  });

  $(".show_stats").click(function () {
    $(".punish_stats_list").animate(
      { height: $(".punish_stats_list")[0].scrollHeight },
      100
    );
    $(".hide_stats").show();
    $(".show_stats").hide();
  });

  function moveSelection() {
    const $checkedOption = $(".segmented-control .option input:checked").closest(".option");
    const position = $checkedOption.position().left;
    const width = $checkedOption.outerWidth();

    $(".segmented-control .selection").css({
      transform: `translateX(${position}px)`,
      width: `${width}px`
    });
  }

  moveSelection();
  $(".segmented-control").on("change", moveSelection);

  function checkAndRenderAvatar(check_getavatar, sid) {
    if (check_getavatar === 1) {
      avatar.push(sid);
    }
  }

  function loadDataFromDatabase(type, page = 1) {
    $.ajax({
      type: 'post',
      url: location.href,
      data: { list: true, type: type, page: page },
      dataType: 'json',
      success: function (data) {
        if (type == 'bans' || type == 'comms') {
          $('#search').html('').append(data['search']);
          $('#punishment_content').html('');
          $('#punishment_pagination').html('');
          for (let i = 0; i < data['html'].length; i++) {
            $('#punishment_content').append(data['html'][i]['html_list']);
            checkAndRenderAvatar(data['html'][i]['CheckAvatar'], data['html'][i]['sid']);
          }
          const pagination = $('#punishment_pagination');
          pagination.html(data['pagination']);
          pagination.off('click').on('click', 'a[data-page]', function () {
            const newPage = parseInt($(this).data('page'));
            if (!isNaN(newPage)) {
              loadDataFromDatabase(type, newPage);
            }
          });
          RenderingAvatar();
        } else if (type == 'admins') {
          $('#search').html('').append(data['search']);
          $('#admins_content').html('');
          $('#admins_pagination').html('');

          for (let i = 0; i < data['html'].length; i++) {
            $('#admins_content').append(data['html'][i]['html_list']);
            checkAndRenderAvatar(data['html'][i]['CheckAvatar'], data['html'][i]['sid']);
          }
          const pagination = $('#admins_pagination');
          pagination.html(data['pagination']);
          pagination.off('click').on('click', 'a[data-page]', function () {
            const newPage = parseInt($(this).data('page'));
            if (!isNaN(newPage)) {
              loadDataFromDatabase(type, newPage);
            }
          });
          RenderingAvatar();
          $(document)
            .on('mouseenter', '.punishmen-admins__card', function () {
              const video = $(this).find('.back_video')[0];
              if (video) {
                video.play();
              }
            })
            .on('mouseleave', '.punishmen-admins__card', function () {
              const video = $(this).find('.back_video')[0];
              if (video) {
                video.pause();
              }
            });

        }
      }
    });
  }

  loadDataFromDatabase(pagepunishment);

  $(document).on('click', '#load_next', function () {
    const pagepunishment = localStorage.getItem('pagepunishment') || 'bans';
    loadDataFromDatabase(pagepunishment);
  });

  $('input[type="radio"][name="sample"]').change(function () {
    const pagepunishment = localStorage.getItem('pagepunishment') || 'bans';
    loadDataFromDatabase(pagepunishment);
    if (pagepunishment == 'bans' || pagepunishment == 'comms') {
      const ListContent = $('#punishment_content');
      ListContent.scrollTop(0);
      const searchBanValue = $('#search_ban').val();
      const searchMuteValue = $('#search_mute').val();
      if (!searchBanValue && !searchMuteValue) {
        $('#punishment_list_search').hide();
        $("#punishment_content").show();
      }
      $('#search_ban').val('');
      $('#search_mute').val('');
      $('#punishment_list_search').hide();
      $("#punishment_content").show();

      $('#punishments_list').show();
      $('#punishments_title').show();
      $('#punishment_pagination').show();
      $('#admins_list').hide();
      $('#admins_title').hide();
      $('#admins_pagination').hide();
    } else {
      const ListContent = $('#admins_content');
      ListContent.scrollTop(0);
      const searchAdminValue = $('#search_admin').val();
      if (!searchAdminValue) {
        $('#admins_list_search').hide();
        $("#admins_content").show();
      }
      $('#search_admin').val('');
      $('#admins_list_search').hide();
      $('#punishment_list_search').hide();
      $("#admins_content").show();
      $("#punishment_content").hide();

      $('#punishments_list').hide();
      $('#punishments_title').hide();
      $('#punishment_pagination').hide();
      $('#admins_list').show();
      $('#admins_title').show();
      $('#admins_pagination').show();
    }
  });

  $(document).on('click', '.modal_open', function () {
    const type = $(this).attr('page');
    const id = $(this).attr('id');
    $.ajax({
      type: 'post',
      url: location.href,
      data: { modal: true, id: id, type: type },
      dataType: 'json',
      success: function (data) {
        if (type == 'bans' || type == 'comms') {
          $('#punishModal').addClass("visible");
          $('#punishModal .punish_body').remove();
          $('#punishModal .popup_modal_content').append(data['html_modal']);
        } else {
          $('#adminModal').addClass("visible");
          $('#adminModal .punish_body').remove();
          $('#adminModal .popup_modal_content').append(data['html_modal']);
        }
      }
    });
  });

  $(document).on('click', '.btn_unban', function () {
    const $btn = $(this);
    const idpunish = $btn.attr('idpunish');
    const page = $btn.attr('page');
    const type = $btn.attr('type');
    const sid = $btn.attr('sid') ?? null;
    $btn.prop('disabled', true);

    $.ajax({
      type: 'post',
      url: location.href,
      data: { btn_unban: true, idpunish, page, type, sid },
      dataType: 'json',
      global: false,
      success: function (data) {
        noty(data.text, data.status);
        if (data.status === "success") {
          setTimeout(function () { location.reload(); }, 1000);
        } else {
          $btn.prop('disabled', false);
        }
      }
    });
  });

  function delay(fn, ms) {
    let timer = 0;
    return function (...args) {
      clearTimeout(timer);
      timer = setTimeout(fn.bind(this, ...args), ms || 0);
    }
  }

  $(document).on('keyup', '#search_ban', delay(function (e) {
    let ban = this.value;
    SearchAjax(ban);
  }, 500));

  $(document).on('keyup', '#search_mute', delay(function (e) {
    let mute = this.value;
    SearchAjax("", mute);
  }, 500));

  $(document).on('keyup', '#search_admin', delay(function (e) {
    let admin = this.value;
    SearchAjax("", "", admin);
  }, 500));

  function SearchAjax(ban = "", mute = "", admin = "") {
    const pagepunishment = localStorage.getItem('pagepunishment') || 'bans';
    if (ban.length >= 1 || mute.length >= 1 || admin.length >= 1) {
      $.ajax({
        type: "POST",
        url: location.href,
        dataType: "json",
        data: "search_ban=" + ban + "&search_mute=" + mute + "&search_admin=" + admin,
        success: function (res) {
          if (pagepunishment == 'bans' || pagepunishment == 'comms') {
            $('#punishment_list_search').html('');
            try {
              if (res.html.length > 0) {
                for (i = 0; i < res.html.length; i++) {
                  $('#punishment_list_search').append(res.html[i].search_html);
                  checkAndRenderAvatar(res.html[i].CheckAvatar, res.html[i].sid);
                }
                $('#punishment_list_search').show();
                $("#punishment_content").hide();
                $("#punishment_pagination").hide();
              } else {
                $('#punishment_list_search').hide();
                $("#punishment_content").hide();
                $("#punishment_pagination").hide();
              }
            } catch (e) {
              $('#punishment_list_search').hide();
              $("#punishment_content").hide();
              $("#punishment_pagination").hide();
            }
          } else if (pagepunishment == 'admins') {
            $('#admins_list_search').html('');
            try {
              if (res.html.length > 0) {
                for (i = 0; i < res.html.length; i++) {
                  $('#admins_list_search').append(res.html[i].search_html);
                  checkAndRenderAvatar(res.html[i].CheckAvatar, res.html[i].sid);
                }
                $('#admins_list_search').show();
                $("#admins_content").hide();
                $("#admins_pagination").hide();
              } else {
                $('#admins_list_search').hide();
                $("#admins_content").hide();
                $("#admins_pagination").hide();
              }
            } catch (e) {
              $('#admins_list_search').hide();
              $("#admins_content").hide();
              $("#admins_pagination").hide();
            }
          }
        }
      });
      return false;
    } else {
      if (pagepunishment == 'bans' || pagepunishment == 'comms') {
        $('#punishment_list_search').hide();
        $("#punishment_content").show();
        $("#punishment_pagination").show();
      } else if (pagepunishment == 'admins') {
        $('#admins_list_search').hide();
        $("#admins_content").show();
        $("#admins_pagination").show();
      }
    }
    return false;
  }
  $(document).on('click', '#punishmentInstallTable', function () {
    $.ajax({
      type: 'post',
      url: location.href,
      data: { installTable: true },
      dataType: 'json',
      global: false,
      success: function (data) {
        if (data.status == "success") {
          noty(data.text, data.status)
        } else {
          noty(data.text, data.status)
        }
      },
    });
  });
});

$(document).on('click', '#adminRating', function () {
  const btn = $(this), steamid = btn.data('steam'), type = btn.data('type');
  $.post(location.href, { admin_vote: true, steamid, type }, function (data) {
    if (data.status === "success") {
      const match = btn.html().match(/(\d+)\s*$/);
      if (match) {
        let count = parseInt(match[1]);
        count = data.action === 'added' ? count + 1 : Math.max(count - 1, 0);
        btn.html(btn.html().replace(/(\d+)\s*$/, count));
      }
      btn.toggleClass('voted', data.action === 'added');
    }
    noty(data.text, data.status);
  }, 'json');
});
