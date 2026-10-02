$(document).ready(function () {
  const KassImg = document.getElementById("kass_img");
  const KassName = document.getElementById("kass_name");
  const CheckKass = document.querySelectorAll('input[name="gatewayPay"]');
  const elementToToggle = $("#skinnone");
  let promoResult = null;
  function updateKassInfo(radioButton) {
    const Name = radioButton.getAttribute("data-name");
    const Img = radioButton.getAttribute("value");
    KassImg.innerHTML = `<img src="/app/modules/module_page_pay/assets/gateways/${Img}.svg" alt="">`;
    KassName.textContent = getKassName(Name);
    if (Name === "SkinPay") {
      elementToToggle.slideUp(300);
    } else {
      elementToToggle.slideDown(300);
    }
    updatePromoResult();
  }
  function getKassName(name) {
    switch (name) {
      case "YooMoney":
        return "Карта, Юмани";
      case "FreeKassa":
        return "Скины, Крипта, Карта";
      case "AnyPay":
        return "СБП, Карта, Крипта";
      case "PayPalych":
        return "СБП, Карта, Крипта";
      case "CentApp":
        return "СБП, Карта, Крипта";
      case "Aaio":
        return "Скины, Крипта, Карта";
      case "SkinPay":
        return "Скины";
      case "Cshost":
        return "Карта";
      case "Lava":
        return "СБП";
      default:
        return "";
    }
  }
  function updatePromoResult() {
    const selectedRadioButton = document.querySelector(
      'input[name="gatewayPay"]:checked'
    );
    if (selectedRadioButton && promoResult) {
      const Name = selectedRadioButton.getAttribute("data-name");
      const resultHTML =
        Name === "SkinPay" ? promoResult.result_skin : promoResult.result;
      $("#promoresult").html(resultHTML);
    } else {
      $("#promoresult").html("");
    }
  }
  CheckKass.forEach(function (radioButton) {
    radioButton.addEventListener("change", function () {
      updateKassInfo(this);
    });
  });
  $(document).on(
    "input",
    '[name="promocode"],[name="amount"],[name="steam"]',
    function () {
      let promocode = $('[name="promocode"]').val();
      let amount = $('[name="amount"]').val();
      let steam = $('[name="steam"]').val();

      if (promocode && $.isNumeric(amount) && steam) {
        $.ajax({
          type: "POST",
          url: window.location.href,
          data: {
            promocode: promocode,
            amount: amount,
            steamid: steam,
          },
          cache: false,
          success: function (response) {
            if (response.trim()) {
              promoResult = jQuery.parseJSON(response.trim());
              if (promoResult.result || promoResult.result_skin) {
                updatePromoResult();
              } else {
                $("#promoresult").html(false);
              }
            }
          },
        });
      } else {
        $("#promoresult").html(false);
      }
    }
  );

  $(document).on("input", '[name="steam"]', function () {
    let steam = $('[name="steam"]');

    if (steam.val()) {
      $.ajax({
        type: "POST",
        url: window.location.href,
        data: "steamidload=" + steam.val(),
        cache: false,
        success: function (result) {
          if (result.trim()) {
            result = jQuery.parseJSON(result.trim());
            if (result.img) {
              $("#profile").html(
                '<div class="avabalance"><img alt="" title="" class="ava_no_auth" width="64" height="64" src="' +
                result.img +
                '"><small class="nick_no_auth">' +
                result.name +
                "</small>" +
                "</div>"
              );
            } else {
              $("#profile").html(false);
            }
          }
        },
      });
    } else {
      $("#profile").html(false);
    }
  });
  $("form").submit(function (event) {
    if ($(this).attr("data-default")) {
      let del = $(this).attr("data-get");
      event.preventDefault();
      let mess;
      $.ajax({
        type: $(this).attr("method"),
        url: window.location.href,
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData: false,
        success: function (result) {
          mess = jQuery.parseJSON(result.trim());
          if (mess.status) {
            if (mess.status == "success") {
              setTimeout(function () {
                if (del) {
                  removeParam(del);
                } else {
                  window.location.reload();
                }
              }, 4100);
            }
            noty(mess.text, mess.status);
          } else if (mess.location) {
            window.location.href = mess.location;
          } else {
            $("#resultForm").html(mess.text);
            document.getElementById("punsh").click();
          }
        },
      });
    }
  });

  document.addEventListener(
    "click",
    removeElem("col-md-6", "data-del", "delete")
  );

  document.getElementById("clearButton").onclick = function (e) {
    document.querySelectorAll(".preset-amount").forEach(function (btn) {
      btn.classList.remove("active");
    });
    document.querySelector('input[name="amount"]').value = "";
  };

  function updatePayButton() {
    const isChecked = $("#checkbox").is(":checked");
    $("#paybutton").prop("disabled", !isChecked);
  }

  $("#checkbox").click(updatePayButton);

  $(document).ready(updatePayButton);
});

function removeElem(delElem, attribute, attributeName) {
  if (!(delElem && attribute && attributeName)) return;
  return function (e) {
    let target = e.target;
    if (
      !(target.hasAttribute(attribute)
        ? target.getAttribute(attribute) === attributeName
          ? true
          : false
        : false)
    )
      return;
    removeParam(target.getAttribute("data-get"));
    let elem = target;
    while (target != this) {
      if (target.classList.contains(delElem)) {
        target.remove();
        return;
      }
      target = target.parentNode;
    }
    return;
  };
}

function removeParam(key) {
  let splitUrl = window.location.href.split("?"),
    rtn = splitUrl[0],
    param,
    params_arr = [],
    queryString = window.location.href.indexOf("?") !== -1 ? splitUrl[1] : "";
  if (queryString !== "") {
    params_arr = queryString.split("&");
    for (let i = params_arr.length - 1; i >= 0; i -= 1) {
      param = params_arr[i].split("=")[0];
      if (param === key) {
        params_arr.splice(i, 1);
      }
    }
    rtn = rtn + "?" + params_arr.join("&");
  }
  window.location.href = rtn;
}

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

$(document).ready(function () {
  $(".popup_copybtn").on("click", function () {
    let $promocodeInput = $('input[name="promocode"]');
    $promocodeInput.val($(this).attr("data-promocode"));
    $promocodeInput.trigger("input");

    let $this = $(this);
    let originalText = $this.html();

    let appliedText = $(
      '<span style="color: var(--green);">' + get_translate_module_phrase('module_page_pay','_promoEnable') +  '</span>'
    );

    $this.addClass("hidden");

    setTimeout(function () {
      $this.html(appliedText);
      $this.removeClass("hidden");

      setTimeout(function () {
        $this.addClass("hidden");

        setTimeout(function () {
          $this.html(originalText);
          $this.removeClass("hidden");
        }, 500);
      }, 1000);
    }, 500);
  });

  $("#popupClearPromo").on("click", function () {
    let $promocodeInput = $('input[name="promocode"]');
    $promocodeInput.val("");
    $promocodeInput.trigger("input");
  });
});

$(document).on("click", "#del_lk_user", function () {
  var result = confirm(get_translate_module_phrase('module_page_pay','_deletePLayer'));
  if (result) {
    let button = $(this);
    const id_del = button.attr("id_del");
    console.log(id_del);
    $.ajax({
      type: "post",
      url: location.href,
      data: { del_lk_user: true, id: id_del },
      dataType: "json",
      global: false,
      success: function (data) {
        if (data.status == "success") {
          noty(data.text, data.status);
        } else {
          noty(data.text, data.status);
        }
      },
    });
  }
});

$(document).on("click", "#del_check", function () {
  $.ajax({
    type: "post",
    url: location.href,
    data: { del_check: true },
    dataType: "json",
    global: false,
    success: function (data) {
      noty(data.text, data.status);
      setTimeout(function () {
        location.reload();
      }, 1000);
    },
  });
});