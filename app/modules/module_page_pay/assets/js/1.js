$("#checkbox").click(function () {
  if ($("#checkbox").is(":checked")) {
    $("#paybutton").prop("disabled", false);
    $("#paybutton").removeClass("btn_disabled");
  } else {
    $("#paybutton").prop("disabled", true);
    $("#paybutton").addClass("btn_disabled");
  }
});

document.querySelectorAll(".preset-amount").forEach(function (button) {
  button.addEventListener("click", function () {
    if (!this.classList.contains("delete-amount")) {
      document.querySelectorAll(".preset-amount").forEach(function (btn) {
        btn.classList.remove("active");
      });
      this.classList.add("active");
      document.querySelector('input[name="amount"]').value =
        this.getAttribute("data-amount");
    }
  });
});

document.getElementById("clearButton").onclick = function (e) {
  document.querySelectorAll(".preset-amount").forEach(function (btn) {
    btn.classList.remove("active");
  });
  document.querySelector('input[name="amount"]').value = "";
};