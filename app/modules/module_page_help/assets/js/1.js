
var catid = 0;
function Ajax(id, param1 = "", param2 = "") {
  switch (id) {
    case "add_content":
      param1 = editor.getHTML();
      param2 = "&" + $("#content_form").serialize();
      break;
    case "edit_content_btn":
      param1 = $("#help_block").attr("content_id");
      break;
    case "edit_content":
      param1 = editor.getHTML();
      param2 =
        $("#help_block").attr("content_id") +
        "&" +
        $("#content_form").serialize();
      break;
    case "del_content":
      param1 = $("#help_block").attr("content_id");
      break;
    case "add_category":
      param1 = "&" + $("#category_form").serialize();
      break;
    case "edit_category":
      param1 = $("#modal_category").attr("category_id");
      param2 = "&" + $("#category_form").serialize();
      break;
  }
  $.ajax({
    url: location.href,
    type: "post",
    data: $("#" + id).serialize() + "&button=" + id + "&param1=" + param1 + "&param2=" + param2,
    success: function (response) {
      let jsonData = JSON.parse(response);
      if (typeof jsonData.error === "undefined") {
        switch (id) {
          case "load":
            if (jsonData.buttons) {
              $("#help_buttons").html("");
              jsonData.buttons.forEach((element) => {
                $("#help_buttons").append(element);
              });
            }
            if (jsonData.category || jsonData.no_category) {
              $("#content_cat").empty();
              add_options("#content_cat", jsonData.no_category.id, jsonData.no_category.title);
              $.each(jsonData.category, function (index, category) {
                add_options("#content_cat", category.id, category.title);
              });
            }
            if (jsonData.content) {
              if(param1){
                $("#help_buttons *").removeClass("help_active_small");
                if(catid != jsonData.content.category){
                  $("#help_buttons *").removeClass("help_active");
                  $("#category_id_" + jsonData.content.category).addClass("help_active");
                }
                $("#content_" + jsonData.content.id).addClass("help_active");
                $("#content_btn_" + jsonData.content.id).addClass("help_active_small");
              }
              catid = jsonData.content.category;
              $("#help_block").attr("content_id", jsonData.content.id);
              $("#content").html(jsonData.content.content);
              $("#content_date").html(timeConverter(jsonData.content.created));
              setTimeout(function () {
                show_dropdown_list(jsonData.content.category);
              }, 500);
            }
            break;
          case "add_content":
            hide_modal("modal_editor");
            editor.setHTML("");
            Ajax("load");
            success(jsonData.success, 0);
            break;
          case "add_content_btn":
            $("#modal_editor_title").html(jsonData.title);
            $("#content_title").val("");
            $("#content_svg").val("");
            editor.setHTML("");
            show_modal("modal_editor");
            $("#modal_editor_btn").attr("onClick", "Ajax('add_content')");
            break;
          case "edit_content_btn":
            show_modal("modal_editor");
            $("#modal_editor_title").html(jsonData.title);
            $("#content_title").val(jsonData.content.title);
            $("#content_svg").val(jsonData.content.svg);
            $("#content_sort").val(jsonData.content.sort);
            $('#content_cat').val(jsonData.content.category);
            editor.setHTML(jsonData.content.content);
            $("#modal_editor_btn").attr("onClick", "Ajax('edit_content')");
            break;
          case "del_content":
            Ajax("load");
            success(jsonData.success, 0);
            break;
          case "edit_content":
            hide_modal("modal_editor");
            editor.setHTML("");
            Ajax("load", jsonData.id);
            success(jsonData.success, 0);
            break;
          case "add_category_btn":
            $("#modal_category_title").html(jsonData.title);
            $("#category_title").val("");
            $("#category_svg").val("");
            $("#category_sort").val("");
            show_modal("modal_category");
            $("#modal_category_btn").attr("onClick", "Ajax('add_category')");
            break;
          case "add_category":
            hide_modal("modal_category");
            Ajax("load");
            success(jsonData.success, 0);
            break;
          case "edit_category_btn":
            show_modal("modal_category");
            $("#modal_category").attr("category_id", jsonData.category.id);
            $("#modal_category_title").html(jsonData.title);
            $("#category_title").val(jsonData.category.title);
            $("#category_svg").val(jsonData.category.svg);
            $("#category_sort").val(jsonData.category.sort);
            $("#modal_category_btn").attr("onClick", "Ajax('edit_category')");
            break;
          case "edit_category":
            hide_modal("modal_category");
            Ajax("load");
            success(jsonData.success, 0);
            break;
          case "del_category":
            Ajax("load");
            success(jsonData.success, 0);
            break;
        }
      } else if (!(typeof jsonData.success === "undefined")) {
        success(jsonData.success);
      } else if (!(typeof jsonData.error === "undefined")) {
        error(jsonData.error);
      }
    },
  });
  return false;
}

function add_options(element, value, text) {
  $(element).append(
    $("<option>", {
      value: value,
      text: text,
    })
  );
}

function show_dropdown_list(id) {
  var Dropdown = $('.dropdown_list-' + id);
  if (Dropdown.is(":hidden")) {
    Dropdown.slideDown(250, function () {
      Dropdown.css("display", "flex");
    });
  };
}

function show_modal(id) {
  let modal = $("#" + id);
  modal.addClass("visible");
}

function hide_modal(id) {
  let modal = $("#" + id);
  modal.removeClass("visible");
}

function edit_category(data) {
  $(".shop_table_edit_server").attr("data-value", data.id);
  $("#cat_name_edit").val(data.name);
  $("#cat_svg_edit").val(data.svg);
  $("#cat_sort_edit").val(data.sort);
}

function error(data, timeout = 0) {
  noty(data, "error");
  if (timeout !== 0) {
    setTimeout(function () {
      window.location = window.location.href;
    }, timeout);
  }
}

function success(data, timeout = 2000) {
  noty(data, "success");
  if (timeout !== 0) {
    setTimeout(function () {
      window.location = window.location.href;
    }, timeout);
  }
}

function timeConverter(UNIX_timestamp) {
  var a = new Date(UNIX_timestamp * 1000);
  var months = [
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "May",
    "Jun",
    "Jul",
    "Aug",
    "Sep",
    "Oct",
    "Nov",
    "Dec",
  ];
  var year = a.getFullYear();
  var month = months[a.getMonth()];
  var date = a.getDate();
  var hour = a.getHours();
  var min = a.getMinutes();
  var sec = a.getSeconds();
  var time =
    date + " " + month + " " + year + " " + hour + ":" + min + ":" + sec;
  return time;
}

$(document).ready(function () {
  $(document).on('click', '.dropdown', function () {
    var catid = $(this).attr('id').match(/\d+/)[0]
    var helpDropdown = $('.dropdown_list-' + catid);
    if (helpDropdown.is(":visible")) {
      helpDropdown.slideUp(250, function () {
        helpDropdown.css("display", "none");
      });
    } else {
      helpDropdown.slideDown(250, function () {
        helpDropdown.css("display", "flex");
      });
    }
  });
  Ajax("load");
});