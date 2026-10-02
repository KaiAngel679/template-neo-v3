$(document).ready(function () {
  $("#contact_info").click(function () {
    $("#contactModal").fadeIn();
  });

  $(".contact_modal-content").click(function (event) {
    event.stopPropagation(); 
  });

  $("#contactModal").click(function (event) {
    if ($(event.target).hasClass("contact_modal")) {
      $("#contactModal").fadeOut();
    }
  });

  $(".contact_close").click(function () {
    $("#contactModal").fadeOut();
  });
});