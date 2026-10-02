if ($("#nestable").length > 0) {
  !(function (d, h, p, l) {
    var a = "ontouchstart" in p,
      c = (function () {
        var t = p.createElement("div"),
          e = p.documentElement;
        if (!("pointerEvents" in t.style)) return !1;
        (t.style.pointerEvents = "auto"),
          (t.style.pointerEvents = "x"),
          e.appendChild(t);
        var s =
          h.getComputedStyle &&
          "auto" === h.getComputedStyle(t, "").pointerEvents;
        return e.removeChild(t), !!s;
      })(),
      s = {
        listNodeName: "ol",
        itemNodeName: "li",
        rootClass: "dd",
        listClass: "dd-list",
        itemClass: "dd-item",
        dragClass: "dd-dragel",
        handleClass: "dd-handle",
        placeClass: "dd-placeholder",
        noDragClass: "dd-nodrag",
        emptyClass: "dd-empty",
        group: 0,
        maxDepth: 5,
        threshold: 20,
      };

    function i(t, e) {
      (this.w = d(p)),
        (this.el = d(t)),
        (this.options = d.extend({}, s, e)),
        this.init();
    }

    (i.prototype = {
      init: function () {
        var s = this;
        s.reset(),
          s.el.data("nestable-group", this.options.group),
          (s.placeEl = d('<div class="' + s.options.placeClass + '"/>')),
          d.each(this.el.find(s.options.itemNodeName), function (t, e) {
            s.setParent(d(e));
          }),
          s.el.on("click", "button", function (t) {
            if (!s.dragEl) {
              var e = d(t.currentTarget);
              e.data("action"), e.parent(s.options.itemNodeName);
            }
          });
        var t = function (t) {
          var e = d(t.target);
          if (!e.hasClass(s.options.handleClass)) {
            if (e.closest("." + s.options.noDragClass).length) return;
            e = e.closest("." + s.options.handleClass);
          }
          e.length &&
            !s.dragEl &&
            ((s.isTouch = /^touch/.test(t.type)),
              (s.isTouch && 1 !== t.touches.length) ||
              (t.preventDefault(),
                s.dragStart(t.touches ? t.touches[0] : t)));
        },
          e = function (t) {
            s.dragEl && s.dragMove(t.touches ? t.touches[0] : t);
          },
          i = function (t) {
            s.dragEl &&
              (t.preventDefault(), s.dragStop(t.touches ? t.touches[0] : t));
          };
        a &&
          (s.el[0].addEventListener("touchstart", t, !1),
            h.addEventListener("touchmove", e, !1),
            h.addEventListener("touchend", i, !1),
            h.addEventListener("touchcancel", i, !1)),
          s.el.on("mousedown", t),
          s.w.on("mousemove", e),
          s.w.on("mouseup", i);
      },
      serialize: function () {
        var i = this;
        return (
          (step = function (t, e) {
            var s = [];
            return (
              t.children(i.options.itemNodeName).each(function () {
                var t = d(this),
                  e = d.extend({}, t.data());
                t.children(i.options.listNodeName);
                s.push(e);
              }),
              s
            );
          }),
          step(i.el.find(i.options.listNodeName).first(), 0)
        );
      },
      serialise: function () {
        return this.serialize();
      },
      reset: function () {
        (this.mouse = {
          offsetX: 0,
          offsetY: 0,
          startX: 0,
          startY: 0,
          lastX: 0,
          lastY: 0,
          nowX: 0,
          nowY: 0,
          distX: 0,
          distY: 0,
          dirAx: 0,
          dirX: 0,
          dirY: 0,
          lastDirX: 0,
          lastDirY: 0,
          distAxX: 0,
          distAxY: 0,
        }),
          (this.isTouch = !1),
          (this.moving = !1),
          (this.dragEl = null),
          (this.dragRootEl = null),
          (this.dragDepth = 0),
          (this.hasNewRoot = !1),
          (this.pointEl = null);
      },
      expandAll: function () {
        var t = this;
        t.el.find(t.options.itemNodeName).each(function () {
          t.expandItem(d(this));
        });
      },
      setParent: function (t) { },
      unsetParent: function (t) { },
      dragStart: function (t) {
        var e = this.mouse,
          s = d(t.target),
          i = s.closest(this.options.itemNodeName);
        this.placeEl.css("height", i.height()),
          (e.offsetX = t.offsetX !== l ? t.offsetX : t.pageX - s.offset().left),
          (e.offsetY = t.offsetY !== l ? t.offsetY : t.pageY - s.offset().top),
          (e.startX = e.lastX = t.pageX),
          (e.startY = e.lastY = t.pageY),
          (this.dragRootEl = this.el),
          (this.dragEl = d(p.createElement(this.options.listNodeName)).addClass(
            this.options.listClass + " " + this.options.dragClass
          )),
          this.dragEl.css("width", i.width()),
          i.after(this.placeEl),
          i[0].parentNode.removeChild(i[0]),
          i.appendTo(this.dragEl),
          d(p.body).append(this.dragEl),
          this.dragEl.css({
            left: t.pageX - e.offsetX,
            top: t.pageY - e.offsetY,
          });
        var a,
          o,
          n = this.dragEl.find(this.options.itemNodeName);
        for (a = 0; a < n.length; a++)
          (o = d(n[a]).parents(this.options.listNodeName).length) >
            this.dragDepth && (this.dragDepth = o);
      },
      dragStop: function (t) {
        var e = this.dragEl.children(this.options.itemNodeName).first();
        e[0].parentNode.removeChild(e[0]),
          this.placeEl.replaceWith(e),
          this.dragEl.remove(),
          this.el.trigger("change"),
          this.hasNewRoot && this.dragRootEl.trigger("change"),
          this.reset();
      },
      dragMove: function (t) {
        var e,
          s = this.options,
          i = this.mouse;
        this.dragEl.css({
          left: t.pageX - i.offsetX,
          top: t.pageY - i.offsetY,
        }),
          (i.lastX = i.nowX),
          (i.lastY = i.nowY),
          (i.nowX = t.pageX),
          (i.nowY = t.pageY),
          (i.distX = i.nowX - i.lastX),
          (i.distY = i.nowY - i.lastY),
          (i.lastDirX = i.dirX),
          (i.lastDirY = i.dirY),
          (i.dirX = 0 === i.distX ? 0 : 0 < i.distX ? 1 : -1),
          (i.dirY = 0 === i.distY ? 0 : 0 < i.distY ? 1 : -1);
        var a = Math.abs(i.distX) > Math.abs(i.distY) ? 1 : 0;
        if (!i.moving) return (i.dirAx = a), void (i.moving = !0);
        i.dirAx !== a
          ? ((i.distAxX = 0), (i.distAxY = 0))
          : ((i.distAxX += Math.abs(i.distX)),
            0 !== i.dirX && i.dirX !== i.lastDirX && (i.distAxX = 0),
            (i.distAxY += Math.abs(i.distY)),
            0 !== i.dirY && i.dirY !== i.lastDirY && (i.distAxY = 0)),
          (i.dirAx = a);
        var o = !1;
        if (
          (c || (this.dragEl[0].style.visibility = "hidden"),
            (this.pointEl = d(
              p.elementFromPoint(
                t.pageX - p.body.scrollLeft,
                t.pageY - (h.pageYOffset || p.documentElement.scrollTop)
              )
            )),
            c || (this.dragEl[0].style.visibility = "visible"),
            this.pointEl.hasClass(s.handleClass) &&
            (this.pointEl = this.pointEl.parent(s.itemNodeName)),
            this.pointEl.hasClass(s.emptyClass))
        )
          o = !0;
        else if (!this.pointEl.length || !this.pointEl.hasClass(s.itemClass))
          return;
        var n = this.pointEl.closest("." + s.rootClass),
          l = this.dragRootEl.data("nestable-id") !== n.data("nestable-id");
        if (!i.dirAx || l || o) {
          if (l && s.group !== n.data("nestable-group")) return;
          if (
            this.dragDepth - 1 + this.pointEl.parents(s.listNodeName).length >
            s.maxDepth
          )
            return;
          var r =
            t.pageY < this.pointEl.offset().top + this.pointEl.height() / 2;
          this.placeEl.parent(),
            o
              ? ((e = d(p.createElement(s.listNodeName)).addClass(
                s.listClass
              )).append(this.placeEl),
                this.pointEl.replaceWith(e))
              : r
                ? this.pointEl.before(this.placeEl)
                : this.pointEl.after(this.placeEl),
            this.dragRootEl.find(s.itemNodeName).length ||
            this.dragRootEl.append('<div class="' + s.emptyClass + '"/>'),
            l &&
            ((this.dragRootEl = n),
              (this.hasNewRoot = this.el[0] !== this.dragRootEl[0]));
        }
      },
    }),
      (d.fn.nestable = function (e) {
        var s = this;
        return (
          this.each(function () {
            var t = d(this).data("nestable");
            t
              ? "string" == typeof e &&
              "function" == typeof t[e] &&
              (s = t[e]())
              : (d(this).data("nestable", new i(this, e)),
                d(this).data("nestable-id", new Date().getTime()));
          }),
          s || this
        );
      });
  })(window.jQuery || window.Zepto, window, document),
    $(document).ready(function () {
      var t = function (t) {
        var e = t.length ? t : $(t.target),
          s = e.data("output");
        window.JSON
          ? s.val(window.JSON.stringify(e.nestable("serialize")))
          : s.val("JSON browser support required for this demo.");
      };
      $("#nestable").nestable({ group: 1 }).on("change", t),
        t($("#nestable").data("output", $("#nestable-output")));
    }),
    $(document).ready(function () {
      $("#load").hide(),
        $(".dd").on("change", function () {
          $("#load").show();
          var t = { data: $("#nestable-output").val() };
          $.ajax({
            type: "POST",
            url: window.location.href,
            data: t,
            cache: !1,
            success: function (t) {
              $("#load").hide();
            },
            error: function (t, e, s) { },
          });
        });
    });
}

function delete_server(element) {
  $.post(window.location.href, { del_server: element.closest("tr").id });
  noty(get_translate_module_phrase('module_page_adminpanel', '_serverDeleted'), "success");
  element.closest("tr").remove();
}

function action_db_delete_table(id, element) {
  $.post(window.location.href, { function: "delete", table: element });
  noty(get_translate_module_phrase('module_page_adminpanel', '_tableDeleted'), "success");
  id.closest("li").remove();
  $("li." + element).remove();
}

function action_db_delete_mod(id, element) {
  $.post(window.location.href, { function: "delete", table: element });
  noty(get_translate_module_phrase('module_page_adminpanel', '_modDeleted'), "success");
  id.closest("div").remove();
  $("div." + element).remove();
}

let doubleClickedCon = true;
function addConection() {
  if (doubleClickedCon) {
    doubleClickedCon = false;
    $.ajax({
      url: window.location.href,
      type: "post",
      data: $("#form-add-conection").serialize() + "&function=add_conection",
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (!(typeof jsonData.success === "undefined")) {
          noty(jsonData.success, "success");
          setTimeout(function () {
            window.location = window.location.href.replace(
              window.location.hash,
              "#"
            );
            location.reload(true);
          }, 2000);
        } else {
          setTimeout(function () {
            doubleClickedCon = true;
          }, 1000);
          noty(jsonData.error, "error");
        }
      },
    });
  }
}

$(document).on('submit', '#create_table, #create_table_neo3_7', function (e) {
  e.preventDefault();
  const $form = $(this);
  const formData = new FormData($form[0]);
  const param = $form.attr('id') === 'create_table_neo3_7' ? 'create_table_neo3_7' : 'create_table';
  formData.append(param, 'settings_modules');
  $.ajax({
    type: 'POST',
    url: location.href,
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (data) {
      if (data.status == 'success') {
        noty(data.text, data.status);
        if (data.location) {
          setTimeout(function () {
            window.location.href = data.location;
          }, 3000);
        }
      } else {
        noty(data.text, data.status);
      }
    }
  });
});

$(document).off('submit', '#form-add-conection').on('submit', '#form-add-conection', function (e) {
  e.preventDefault();
  addConection();
});

Admin("#form-edit-conection", {
  param: "form-edit-conection",
  success: function (data) {
    if (typeof noty === "function") {
      if (data.status == "success") {
        noty(data.text, data.status);
      } else {
        noty(data.text, data.status);
      }
    } else {
      note({ content: data.text, type: data.status, time: 3 });
    }
    if (data.status == "success")
      setTimeout(function () {
        window.location.href = "/adminpanel/?section=db";
      }, 3000);
  },
});

function changeConnection(mod) {
  document.getElementById("con_mod_name").innerHTML = get_translate_module_phrase('module_page_adminpanel', '_mod') + ": " + mod;
  document.getElementById("con_mod_id").value = mod;
  document
    .getElementById("add_conection_button")
    .setAttribute("href", "#add_connect");
  document
    .getElementById("custom_mod_wrapper")
    .setAttribute("style", "display: none;");
  document.getElementById("con_table_name").value = "";
  document
    .getElementById("rank_pack_connection")
    .setAttribute("style", "display: none;");

  if (mod == "custom") {
    document
      .getElementById("custom_mod_wrapper")
      .setAttribute("style", "display: block;");
  }
  let db_hide = document.querySelectorAll(".con_active");
  for (let i = 0; i < db_hide.length; i++) {
    db_hide[i].classList.remove("con_active");
  }
  let db_show = document.querySelectorAll(".con_" + mod);
  for (let i = 0; i < db_show.length; i++) {
    db_show[i].classList.add("con_active");
  }
  if (mod == "LevelsRanks") {
    document.getElementById("con_table_name").value = "lvl_base";
    document
      .getElementById("rank_pack_connection")
      .setAttribute("style", "display: block;");
  } else if (mod == "Vips") {
    document.getElementById("con_table_name").value = "vip_";
  } else if (mod == "IksAdmin") {
    document.getElementById("con_table_name").value = "iks_";
  } else if (mod == "AdminSystem") {
    document.getElementById("con_table_name").value = "as_";
  } else if (mod == "SourceBans") {
    document.getElementById("con_table_name").value = "sb_";
  } else if (mod == "lk") {
    document.getElementById("con_table_name").value = "lk";
  } else if (mod == "Reports") {
    document.getElementById("con_table_name").value = "rs_";
  }
}

function changeNameModule() {
  let val = document.querySelector('input[name="mod-select"]:checked')?.value || "";
  let db_show = document.querySelectorAll(
    ".con_" + document.getElementById("custom_mod_name").value
  );
  for (let i = 0; i < db_show.length; i++) {
    db_show[i].classList.add("con_active");
  }
  if (val == "custom") {
    document.getElementById("con_mod_name").innerHTML = "Mod: " + document.getElementById("custom_mod_name").value;
    document.getElementById("con_mod_id").value = document.getElementById("custom_mod_name").value;
  }
}

$(document).on("click", "#show_pass", function () {
  var input = $(this);
  if (input.getAttribute("type") == "password") {
    target.classList.add("view");
    input.setAttribute("type", "text");
  } else {
    target.classList.remove("view");
    input.setAttribute("type", "password");
  }
  return false;
});

function Admin(formSelect, options) {
  $(formSelect).on("submit", (e) => {
    e.preventDefault();
    const formData = new FormData($(e.currentTarget)[0]);
    formData.append(options.param, "settings_modules");
    $.ajax({
      type: "post",
      url: location.href,
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (data) {
        options.success(data);
      },
      error: function () {
        return false;
      },
    });
    return false;
  });
}

Admin("#options_one", {
  param: "options_one",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
    } else {
      noty(data.text, data.status);
    }
  },
});

Admin("#options_two", {
  param: "options_two",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
    } else {
      noty(data.text, data.status);
    }
  },
});

Admin("#settings_modules", {
  param: "settings_modules",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
    } else {
      noty(data.text, data.status);
    }
  },
});

$(document).on("click", "#baner_del", function () {
  let button = $(this);
  const id_del = button.attr("id_del");
  $.ajax({
    type: "post",
    url: location.href,
    data: { baner_del: true, id_del: id_del },
    dataType: "json",
    global: false,
    success: function (data) {
      if (data.status == "success") {
        noty(data.text, data.status);
        button.closest(".banner_content").remove();
      } else {
        noty(data.text, data.status);
      }
    },
  });
});

Admin("#create_table", {
  param: "create_table",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
    } else {
      noty(data.text, data.status);
    }
    if (data.status == "success")
      setTimeout(function () {
        window.location.href = mess.location;
      }, 3000);
  },
});

Admin("#admin_clear_stats", {
  param: "admin_clear_stats",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
    } else {
      noty(data.text, data.status);
    }
  },
});

Admin("#admin_clear_empty_players", {
  param: "admin_clear_empty_players",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
    } else {
      noty(data.text, data.status);
    }
  },
});

Admin("#hide_filter_form", {
  param: "hide_filter_form",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
    } else {
      noty(data.text, data.status);
    }
  },
});

Admin("#stretch_filter_form", {
  param: "stretch_filter_form",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
    } else {
      noty(data.text, data.status);
    }
  },
});

Admin("#hide_city_form", {
  param: "hide_city_form",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
    } else {
      noty(data.text, data.status);
    }
  },
});

Admin("#hide_country_form", {
  param: "hide_country_form",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
    } else {
      noty(data.text, data.status);
    }
  },
});

$(document).ready(function () {
  $("#stretch_filter").change(function () {
    $("#stretch_filter_form").submit();
  });
  $("#hide_filter").change(function () {
    $("#hide_filter_form").submit();
  });
  $("#hide_city").change(function () {
    $("#hide_city_form").submit();
  });
  $("#hide_country").change(function () {
    $("#hide_country_form").submit();
  });
  const fileInput = $("#file-input");
  const fileInfo = $("#file-info");

  fileInput.on("change", function (event) {
    const file = event.target.files[0];
    if (file) {
      fileInfo.text(`${file.name}`).show();
    }
  });
});

Admin("#all_del_logs", {
  param: "all_del_logs",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
      $(".btn_delete").closest(".btn_delete").remove();
      $(".lk_logs_wrap").closest(".lk_logs_wrap").remove();
    } else {
      noty(data.text, data.status);
    }
  },
});

Admin("#all_del_logs_lk", {
  param: "all_del_logs_lk",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
      $(".btn_delete").closest(".btn_delete").remove();
      $(".lk_logs_wrap").closest(".lk_logs_wrap").remove();
    } else {
      noty(data.text, data.status);
    }
  },
});

Admin("#settings_modules_core", {
  param: "settings_modules_core",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
    } else {
      noty(data.text, data.status);
    }
  },
});

Admin("#clear_modules_initialization", {
  param: "clear_modules_initialization",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
    } else {
      noty(data.text, data.status);
    }
  },
});

$(document).on("click", "#log_del_lk", function () {
  let button = $(this);
  const id_del = button.attr("id_del");
  $.ajax({
    type: "post",
    url: location.href,
    data: { log_del_lk: true, id: id_del },
    dataType: "json",
    global: false,
    success: function (data) {
      if (data.status == "success") {
        noty(data.text, data.status);
      } else {
        noty(data.text, data.status);
      }
      if (data.status === "success") {
        button.closest("div").remove();
      }
    },
  });
});

$(document).on("click", "#log_del", function () {
  let button = $(this);
  const id_del = button.attr("id_del");
  $.ajax({
    type: "post",
    url: location.href,
    data: { log_del: true, id: id_del },
    dataType: "json",
    global: false,
    success: function (data) {
      if (data.status == "success") {
        noty(data.text, data.status);
      } else {
        noty(data.text, data.status);
      }
      if (data.status === "success") {
        button.closest("div").remove();
      }
    },
  });
});

$(".user__roles-wrapper").on("click", ".users__roles-role", function () {
  const $list = $(this)
    .closest(".user__roles-wrapper")
    .next(".users__roles-list");
  const $svg = $(this).find("svg");

  if (!$list.hasClass("open-list")) {
    $(".users__roles-list").removeClass("open-list");
    $(".users__roles-role svg").css("transform", "rotate(0deg)");
  }

  $list.toggleClass("open-list");

  if ($list.hasClass("open-list")) {
    $svg.css("transform", "rotate(90deg)");
  } else {
    $svg.css("transform", "rotate(0deg)");
  }
});

Admin("#addRoleForm", {
  param: "addRoleForm",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
      setTimeout(function () {
        window.location.href = "/adminpanel/?section=users";
      }, 3000);
    } else {
      noty(data.text, data.status);
    }
  },
});

Admin("#addAdminForm", {
  param: "addAdminForm",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
      setTimeout(function () {
        window.location.href = "/adminpanel/?section=users";
      }, 3000);
    } else {
      noty(data.text, data.status);
    }
  },
});

Admin("#addUserRoleForm", {
  param: "addUserRoleForm",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
      setTimeout(function () {
        window.location.href = "/adminpanel/?section=users";
      }, 3000);
    } else {
      noty(data.text, data.status);
    }
  },
});

Admin("#addBlockForm", {
  param: "addBlockForm",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
      setTimeout(function () {
        window.location.href = "/adminpanel/?section=users";
      }, 3000);
    } else {
      noty(data.text, data.status);
    }
  },
});

$(document).on("click", "#del_role", function () {
  var result = confirm(get_translate_module_phrase('module_page_adminpanel', '_deleteRole'));

  if (result) {
    let button = $(this);
    const id_del = button.attr("id_del");
    $.ajax({
      type: "post",
      url: location.href,
      data: { del_role: true, id: id_del },
      dataType: "json",
      global: false,
      success: function (data) {
        if (data.status == "success") {
          noty(data.text, data.status);
        } else {
          noty(data.text, data.status);
        }
        if (data.status === "success") {
          document.getElementById("role-" + id_del).remove();
          document.getElementById("role-users-" + id_del).remove();
        }
      },
    });
  }
});

$(document).on("click", "#del_admin", function () {
  var result = confirm(get_translate_module_phrase('module_page_adminpanel', '_deleteAdmin'));

  if (result) {
    let button = $(this);
    const id_del = button.attr("id_del");
    $.ajax({
      type: "post",
      url: location.href,
      data: { del_admin: true, id: id_del },
      dataType: "json",
      global: false,
      success: function (data) {
        if (data.status == "success") {
          noty(data.text, data.status);
        } else {
          noty(data.text, data.status);
        }
        if (data.status === "success") {
          document.getElementById("admin-" + id_del).remove();
        }
      },
    });
  }
});

$(document).on("click", "#admin_clear_stats", function () {
  var result = confirm(get_translate_module_phrase('module_page_adminpanel', '_clearStats'));

  if (result) {
    $.ajax({
      type: "post",
      url: location.href,
      data: { admin_clear_stats: true },
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

$(document).on("click", "#admin_clear_empty_players", function () {
  var result = confirm(get_translate_module_phrase('module_page_adminpanel', '_clearEmpty'));

  if (result) {
    $.ajax({
      type: "post",
      url: location.href,
      data: { admin_clear_empty_players: true },
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

$(document).on("click", "#admin_clear_unactive_players", function () {
  var result = confirm(get_translate_module_phrase('module_page_adminpanel', '_clearInactive'));

  if (result) {
    $.ajax({
      type: "post",
      url: location.href,
      data: { admin_clear_unactive_players: true },
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

$(document).on("click", "#del_user_role", function () {
  var result = confirm(get_translate_module_phrase('module_page_adminpanel', '_deleteUserRole'));

  if (result) {
    let button = $(this);
    const user_del = button.attr("user_del");
    const role_del = button.attr("role_del");
    $.ajax({
      type: "post",
      url: location.href,
      data: { del_user_role: true, user_steam: user_del, role_id: role_del },
      dataType: "json",
      global: false,
      success: function (data) {
        if (data.status == "success") {
          noty(data.text, data.status);
        } else {
          noty(data.text, data.status);
        }
        if (data.status === "success") {
          document
            .getElementById("role-users-" + role_del + "-" + user_del)
            .remove();
        }
      },
    });
  }
});

$(document).on("click", "#del_block", function () {
  var result = confirm(get_translate_module_phrase('module_page_adminpanel', '_deleteBlocking'));

  if (result) {
    let button = $(this);
    const ban_id = button.attr("ban-id");
    $.ajax({
      type: "post",
      url: location.href,
      data: { del_block: true, block_id: ban_id },
      dataType: "json",
      global: false,
      success: function (data) {
        if (data.status == "success") {
          noty(data.text, data.status);
        } else {
          noty(data.text, data.status);
        }
        if (data.status === "success") {
          document.getElementById("block-" + ban_id).remove();
        }
      },
    });
  }
});
$(document).ready(function () {
  const $selectedText = $(".selected__text span");
  const $selectList = $(".select__list");
  const $selectItems = $(".select__item");
  const $allSummBlocks = $(".stats_all_cash > span").not(
    ".selected__text span"
  );

  $(".selected__text").on("click", function (event) {
    $selectList.toggleClass("menu-opened");
    event.stopPropagation();
  });

  $(document).on("click", function () {
    if ($selectList.hasClass("menu-opened")) {
      $selectList.removeClass("menu-opened");
    }
  });

  $selectItems.on("click", function () {
    const selectedId = $(this).data("id");
    $selectedText.text($(this).text());
    $allSummBlocks.hide();
    $(`#${selectedId}`).show();
    $selectList.removeClass("menu-opened");
  });
});

document.addEventListener("click", (event) => {
  const blurredElements = document.querySelectorAll(
    ".text-blurred, .text-not-blurred"
  );

  blurredElements.forEach((element) => {
    if (element.contains(event.target)) {
      element.classList.add("text-not-blurred");
      element.classList.remove("text-blurred");
    } else {
      element.classList.remove("text-not-blurred");
      element.classList.add("text-blurred");
    }
  });
});

function InfoOnline() {
  $.ajax({
    type: "POST",
    url: window.location.href,
    data: { admin_update_online: true },
    dataType: "json",
    global: false,
    success: function (data) {
      $("#admin_online_count").html(data.online_site);
      $("#admin_online_list").html(data.online_users);
    },
  });
}

InfoOnline();
setInterval(InfoOnline, 30000);

$(document).on("click", ".visit-users__header", function () {
  let card = $(this).closest('.visit-users__card');
  let currentHeight = Math.round(card.height());
  card.height(currentHeight <= 41 ? 176 : 37);
});

var nestedSortables = [].slice.call(document.querySelectorAll('.nested-sortable'));

for (var i = 0; i < nestedSortables.length; i++) {
  const container = nestedSortables[i];
  const containerId = (container && container.id) ? container.id : '';

  if (containerId === 'nested-nav' || containerId === 'subMenus') {
    new Sortable(container, {
      group: {
        name: 'nested-nav',
        pull: true,
        put: function (to, from, dragEl) {
          const isCategory = dragEl && dragEl.dataset && dragEl.dataset.type === 'category';
          const targetEl = to && (to.el || to);
          const hasClass = (el, cls) => !!(el && el.classList && typeof el.classList.contains === 'function') && el.classList.contains(cls);
          const isTargetSubmenus = hasClass(targetEl, 'navigation__menu-submenus') || (targetEl && targetEl.id === 'subMenus');
          const isTargetRoot = hasClass(targetEl, 'nested-sortable');
          if (isCategory) {
            const allow = isTargetRoot && !isTargetSubmenus;
            return allow;
          }
          const allow = isTargetSubmenus || isTargetRoot;
          return allow;
        }
      },
      animation: 150,
      handle: '.handle',
      ghostClass: 'grey-bg',
      fallbackOnBody: true,
      forceFallback: true,
      swapThreshold: 0.65,
      onMove: function (evt) {
        const dragged = evt.dragged;
        const to = evt.to;
        if (dragged.dataset.type === 'category') {
          if (to.classList.contains('navigation__menu-submenus')) {
            return false;
          }
        }
        return true;
      },
      onEnd: function (evt) {
        const item = evt.item;
        const newParent = item.parentElement;
        let isNowSubmenu = (newParent && (newParent.classList.contains('navigation__menu-submenus') || newParent.id === 'subMenus'));
        const isPoint = item && item.dataset && item.dataset.type === 'point';
        if (isPoint) {
          if (!isNowSubmenu && evt.originalEvent && typeof document.elementFromPoint === 'function') {
            const oe = evt.originalEvent;
            const x = oe.clientX;
            const y = oe.clientY;
            if (typeof x === 'number' && typeof y === 'number') {
              const hoveredEl = document.elementFromPoint(x, y);
              const category = hoveredEl && hoveredEl.closest ? hoveredEl.closest('[data-type="category"]') : null;
              if (category) {
                const sub = category.querySelector('#subMenus') || category.querySelector('.navigation__menu-submenus');
                if (sub) {
                  sub.appendChild(item);
                  isNowSubmenu = true;
                }
              }
            }
          }

          const existingSubmenu = item.querySelector('.navigation__menu-point-submenu');
          if (existingSubmenu) existingSubmenu.remove();
          if (isNowSubmenu) {
            const submenuIcon = document.createElement('div');
            submenuIcon.className = 'navigation__menu-point-submenu';
            submenuIcon.innerHTML = '<svg><use href="/resources/img/sprite.svg#arrow-b-r"></use></svg>';
            item.insertBefore(submenuIcon, item.firstChild);
          }
        }

        const rootContainer = document.getElementById('nested-nav');
        const menuJson = getMenuJson(rootContainer);
        $.ajax({
          url: location.href,
          type: 'POST',
          data: { changeSortNav: true, data_sort: JSON.stringify(menuJson), rootContainer: 'nested-nav' },
          dataType: "json",
          success: function (data) {
            if (data.success !== undefined) {
              noty(data.success, 'success');
            } else {
              noty(data.error || get_translate_module_phrase('module_page_adminpanel', '_sortingError'), 'error');
            }
          }
        });
      }
    });
    continue;
  }

  if (containerId === 'nested-userbar' || containerId === 'nested-footer') {
    new Sortable(container, {
      group: { name: containerId, pull: false, put: false },
      animation: 150,
      handle: '.handle',
      ghostClass: 'grey-bg',
      fallbackOnBody: true,
      forceFallback: true,
      swapThreshold: 0.65,
      onMove: function (evt) {
        const dragged = evt.dragged;
        const to = evt.to;
        if (dragged.dataset.type === 'category') {
          if (to.classList.contains('navigation__menu-submenus')) {
            return false;
          }
        }
        return true;
      },
      onEnd: function () {
        const rootContainer = document.getElementById(containerId);
        const order = getFlatMenuOrder(rootContainer);
        $.ajax({
          url: location.href,
          type: 'POST',
          data: { changeSortNav: true, data_sort: JSON.stringify(order), rootContainer: containerId },
          dataType: "json",
          success: function (data) {
            if (data.success !== undefined) {
              noty(data.success, 'success');
            } else {
              noty(data.error || get_translate_module_phrase('module_page_adminpanel', '_sortingError'), 'error');
            }
          }
        });
      }
    });
    continue;
  }

  new Sortable(container, {
    group: { name: containerId || 'nested', pull: false, put: false },
    animation: 150,
    handle: '.handle',
    ghostClass: 'grey-bg',
    fallbackOnBody: true,
    forceFallback: true,
    swapThreshold: 0.65,
  });
}

if ($('#sortable-table-server').length) {
  if (typeof Sortable !== 'undefined') {
    new Sortable(document.getElementById('sortable-table-server'), {
      animation: 150,
      handle: '.handle',
      ghostClass: 'grey-bg',
      fallbackOnBody: true,
      swapThreshold: 0.65,
      onEnd: function () {
        let order = [];
        let table = $('#sortable-table-server');
        table.find('tr').each(function (index) {
          let id = $(this).attr('id');
          if (id) {
            order.push({ id: id, position: index + 1 });
          }
        });

        $.ajax({
          url: location.href,
          type: 'POST',
          data: {
            changeSortServer: true,
            order: JSON.stringify(order)
          },
          dataType: "json",
          success: function (data) {
            if (data.status == 'success') {
              noty(data.text, 'success');
            } else {
              noty(data.text || get_translate_module_phrase('module_page_adminpanel', '_sortingError'), 'error');
            }
          }
        });
      }
    });
  }
}

function getFlatMenuOrder(container) {
  if (!container) return [];
  const order = [];
  for (const item of container.children) {
    const menuId = item.getAttribute('data-menu_id') || '';
    const id = parseInt(menuId, 10);
    if (!Number.isNaN(id) && id > 0) {
      order.push(id);
    }
  }
  return order;
}

function getMenuJson(container) {
  if (!container) return [];

  const result = [];

  for (const item of container.children) {
    const type = item.getAttribute('data-type') || '';
    const menuId = item.getAttribute('data-menu_id') || '';
    const pointId = item.getAttribute('data-point_id') || '';

    if (type === 'category') {
      const subMenus = item.querySelector('#subMenus');
      const children = subMenus ? getMenuJson(subMenus) : [];
      const node = { menu_id: menuId, type };
      if (children.length) node.children = children;
      result.push(node);
    } else {
      const parentCategory = item.closest('[data-type="category"]');
      const categoryId = parentCategory ? (parentCategory.getAttribute('data-menu_id') || '') : '';
      const idForPoint = pointId || menuId;
      result.push({ menu_id: idForPoint, point_id: pointId || null, category_id: categoryId || null, type });
    }
  }

  return result;
}

Admin("#add_point", {
  param: "add_point",
  success: function (data) {
    if (data.success !== undefined) {
      noty(data.success, 'success');
      setTimeout(function () {
        location.reload();
      }, 2000);
    } else {
      noty(data.error, 'error');
    }
  },
});

Admin("#add_category", {
  param: "add_category",
  success: function (data) {
    if (data.success !== undefined) {
      noty(data.success, 'success');
      setTimeout(function () {
        location.reload();
      }, 2000);
    } else {
      noty(data.error, 'error');
    }
  },
});

Admin("#add_userbar", {
  param: "add_userbar",
  success: function (data) {
    if (data.success !== undefined) {
      noty(data.success, 'success');
      setTimeout(function () {
        location.reload();
      }, 2000);
    } else {
      noty(data.error, 'error');
    }
  },
});

Admin("#add_footer", {
  param: "add_footer",
  success: function (data) {
    if (data.success !== undefined) {
      noty(data.success, 'success');
      setTimeout(function () {
        location.reload();
      }, 2000);
    } else {
      noty(data.error, 'error');
    }
  },
});

Admin("#edit_point", {
  param: "edit_point",
  success: function (data) {
    if (data.status == "success") {
      noty(data.text, data.status);
      setTimeout(function () {
        location.reload();
      }, 2000);
    } else {
      noty(data.text, data.status);
    }
  },
});

$(document).on("click", "#point_del, #footer_del, #userbar_del, #subpoint_del, #category_del", function () {
  let button = $(this);
  const id_del = button.data("del");
  const name = button.attr("id");
  switch (name) {
    case "footer_del":
      var dataParam = { menu_del: true, type: 'footer', id_del: id_del };
      break;
    case "userbar_del":
      var dataParam = { menu_del: true, type: 'userbar', id_del: id_del };
      break;
    case "subpoint_del":
      var dataParam = { menu_del: true, type: 'subpoint', id_del: id_del, id_point: button.data("point") };
      break;
    case "category_del":
    case "point_del":
    default:
      var dataParam = { menu_del: true, type: 'menu', id_del: id_del };
      break;
  }
  $.ajax({
    type: "post",
    url: location.href,
    data: dataParam,
    dataType: "json",
    success: function (data) {
      if (data.success !== undefined) {
        noty(data.success, 'success');
        button.closest(".navigation__menu-point, .navigation__menu-category").remove();
      } else {
        noty(data.error, 'error');
      }
    },
  });
});

$(document).on('click', '#edit_category, #edit_point, #edit_subpoint, #edit_footer, #edit_userbar', function () {
  const $btn = $(this);
  const id = $btn.data('menu');
  const point = $btn.data('point') ?? '';
  const btnId = $btn.attr('id');

  let type = 'point';
  switch (btnId) {
    case 'edit_category':
      type = 'category';
      break;
    case 'edit_subpoint':
      type = 'point';
      break;
    case 'edit_userbar':
      type = 'userbar';
      break;
    case 'edit_footer':
      type = 'footer';
      break;
    case 'edit_point':
      type = 'point';
      break;
    default:
      type = 'point';
      break;
  }

  let $modal;
  switch (type) {
    case 'category':
      $modal = $('#EditCategoryNav');
      break;
    case 'userbar':
      $modal = $('#editUserbarItem');
      break;
    case 'footer':
      $modal = $('#editFooterLink');
      break;
    case 'point':
    default:
      $modal = $('#EditPointNav');
      break;
  }

  $.ajax({
    url: location.href,
    type: 'POST',
    data: {
      loadMenuData: true,
      id: id,
      point: type === 'point' ? point : '',
      type: type
    },
    dataType: 'json',
    success: function (response) {
      if (response.success !== undefined) {
        $modal.find('input[name="title"]').val(response.title || '');
        if ($modal.find('input[name="svg"]').length) {
          $modal.find('input[name="svg"]').val(response.icon || '');
        }
        $modal.find('input[name="only_auth"]').prop('checked', !!response.only_auth);
        $modal.find('input[name="only_admin"]').prop('checked', !!response.only_admin);
        $modal.find('input[name="only_admin_site"]').prop('checked', !!response.only_admin_site);
        $modal.find('input[name="only_admin_server"]').prop('checked', !!response.only_admin_server);
        $modal.find('input[name="blank"]').prop('checked', !!response.blank);
        if ($modal.find('input[name="module"]').length && response.module) {
          $modal.find('input[name="module"][value="' + response.module + '"]').prop('checked', true);
          initAdaptiveSelects();
        }

        if ($modal.find('input[name="link"]').length) {
          $modal.find('input[name="link"]').val(response.link || '');
        }
        if ($modal.find('input[name="description"]').length) {
          $modal.find('input[name="description"]').val(response.description || '');
        }

        $modal.attr('data-edit-id', id);
        if (type === 'point') {
          $modal.attr('data-edit-point', point);
        } else {
          $modal.removeAttr('data-edit-point');
        }

        $modal.addClass('visible');
      } else {
        noty(response.error, 'error');
      }
    }
  });
});

$(document).on("submit", "#edit_point_form, #edit_category_form, #edit_footer_form, #edit_userbar_form", function (e) {
  e.preventDefault();

  const $form = $(this);
  const formId = $form.attr('id');

  const map = {
    edit_category_form: { type: 'category', modal: '#EditCategoryNav' },
    edit_point_form: { type: 'point', modal: '#EditPointNav' },
    edit_footer_form: { type: 'footer', modal: '#editFooterLink' },
    edit_userbar_form: { type: 'userbar', modal: '#editUserbarItem' }
  };

  const cfg = map[formId];
  if (!cfg) return;

  const $modal = $(cfg.modal);

  const data = {
    updateMenuItem: true,
    id: $modal.attr('data-edit-id'),
    type: cfg.type,
    title: ($form.find('input[name="title"]').val() || '').trim(),
    onlyAuth: $form.find('input[name="only_auth"]').prop('checked') ? 1 : 0,
    onlyAdmin: $form.find('input[name="only_admin"]').prop('checked') ? 1 : 0,
    onlyAdminSite: $form.find('input[name="only_admin_site"]').prop('checked') ? 1 : 0,
    onlyAdminServer: $form.find('input[name="only_admin_server"]').prop('checked') ? 1 : 0,
  };

  const $svg = $form.find('input[name="svg"]');
  if ($svg.length) {
    data.icon = ($svg.val() || '').trim();
  }

  const $link = $form.find('input[name="link"]');
  if ($link.length) {
    data.link = ($link.val() || '').trim();
  }

  const $desc = $form.find('input[name="description"]');
  if ($desc.length) {
    data.description = ($desc.val() || '').trim();
  }

  const $blank = $form.find('input[name="blank"]');
  if ($blank.length) {
    data.blank = $blank.prop('checked') ? 1 : 0;
  }

  if (cfg.type === 'point') {
    data.point = $modal.attr('data-edit-point') || '';
  }

  const $module = $form.find('input[name="module"]:checked');
  if ($module.length) {
    data.module = ($module.val() || '').trim();
  }

  $.ajax({
    type: 'post',
    url: location.href,
    data: data,
    dataType: 'json',
    global: false,
    success: function (response) {
      if (response.success !== undefined) {
        noty(response.success, 'success');
        $modal.removeClass('visible');
        location.reload();
      } else {
        noty(response.error || get_translate_phrase('_errorIziToast'), 'error');
      }
    }
  });
});

$(document).ready(function () {
  $('#searchIcon').on('input', function () {
    var searchText = $(this).val().toLowerCase().trim();
    var $allIcons = $('.dev__icons-icon');

    if (searchText === '') {
      $allIcons.removeClass('unsee');
    } else {
      $allIcons.each(function () {
        var $icon = $(this);
        var content = $icon.data('tippy-content').toLowerCase();

        if (content.includes(searchText)) {
          $icon.removeClass('unsee');
        } else {
          $icon.addClass('unsee');
        }
      });
    }
  });
});

$('#template_socials').on('submit', function (e) {
  e.preventDefault();
  $.ajax({
    url: location.href,
    type: 'POST',
    data: $(this).serialize() + '&edit_socials=1',
    dataType: "json",
    success: function (data) {
      noty(data.text, data.status);
    }
  });
});

$('#logo-settings').on('submit', function (e) {
  e.preventDefault();
  $.ajax({
    url: location.href,
    type: 'POST',
    data: $(this).serialize() + '&edit_info=1',
    dataType: "json",
    success: function (data) {
      noty(data.text, data.status);
    }
  });
});

$('#other-settings').on('submit', function (e) {
  e.preventDefault();
  $.ajax({
    url: location.href,
    type: 'POST',
    data: $(this).serialize() + '&edit_other=1',
    dataType: "json",
    success: function (data) {
      noty(data.text, data.status);
    }
  });
});

$('#delete-logo').on('click', function (e) {
  e.preventDefault();
  $.ajax({
    url: location.href,
    type: 'POST',
    data: { remove_logo: true },
    dataType: "json",
    success: function (data) {
      noty(data.text, data.status);
    }
  });
});

if (typeof FilePond !== 'undefined') {
  FilePond.registerPlugin(
    FilePondPluginImagePreview,
    FilePondPluginFileValidateSize,
    FilePondPluginFileValidateType
  );

  const filepondElement = document.querySelector('.filepond-single');

  if (filepondElement) {
    pond = FilePond.create(filepondElement, {
      allowMultiple: false,
      allowImagePreview: true,
      credits: false,
      maxFileSize: '50MB',

      acceptedFileTypes: [
        'image/svg+xml',
        'image/png',
        'image/jpeg',
        'image/gif',
        'image/webp'
      ],

      labelIdle: get_translate_module_phrase(
        'module_page_adminpanel',
        '_uploadLogo'
      ),

      server: {
        url: location.href,
        process: {
          method: 'POST',
          ondata: (formData) => {
            formData.append('send_filepond_file', 'true');
            return formData;
          },
          onload: (result) => {
            const res = JSON.parse(result);
            const filepondInput = document.getElementById('filepond-single');

            filepondInput.value = res.file;
            return res.file;
          },
          onabort: (file) => {
            const filepondInput = document.getElementById('filepond-single');
            if (filepondInput && filepondInput.value === file.filename) {
              filepondInput.value = '';
            }

            fetch(location.href, {
              method: 'DELETE',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ file: file.filename })
            }).catch(err => console.error('Abort file delete failed:', err));
          }
        },

        revert: (fileId, load) => {
          fetch(location.href, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ file: fileId })
          })
            .then(() => {
              const filepondInput = document.getElementById('filepond-single');
              if (filepondInput && filepondInput.value === fileId) {
                filepondInput.value = '';
              }
              load();
            })
            .catch(err => {
              console.error('Revert file delete failed:', err);
              load();
            });
        }
      }

    });
  }
}
