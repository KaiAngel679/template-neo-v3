function noty(text, type) {
  switch (type) {
    case "success":
      iziToast.success({
        title: get_translate_phrase('_successIziToast'),
        titleColor: "var(--span)",
        titleSize: "16",
        message: text,
        backgroundColor: "var(--input-form)",
        messageColor: "var(--text-custom)",
        progressBar: false,
        messageSize: "12",
        position: "topRight",
        close: false,
        closeOnEscape: true,
        closeOnClick: true,
        displayMode: 0,
        layout: "2",
        transitionIn: "fadeInUp",
        transitionOut: "fadeOutRight",
        transitionInMobile: "fadeInUp",
        transitionOutMobile: "fadeOutDown",
        timeout: 2000,
      });
      break;
    case "error":
      iziToast.error({
        title: get_translate_phrase('_errorIziToast'),
        titleColor: "#ff4d4d",
        titleSize: "16",
        message: text,
        backgroundColor: "var(--input-form)",
        messageColor: "var(--text-custom)",
        progressBar: false,
        messageSize: "12",
        position: "topRight",
        close: false,
        closeOnEscape: true,
        closeOnClick: true,
        displayMode: 0,
        layout: "2",
        transitionIn: "fadeInDown",
        transitionOut: "fadeOutRight",
        transitionInMobile: "fadeInUp",
        transitionOutMobile: "fadeOutDown",
        timeout: 2000,
      });
      break;
    case "info":
      iziToast.info({
        title: get_translate_phrase('_infoIziToast'),
        titleColor: "var(--span)",
        titleSize: "16",
        message: text,
        backgroundColor: "var(--input-form)",
        messageColor: "var(--text-custom)",
        progressBar: false,
        messageSize: "12",
        position: "topRight",
        close: false,
        closeOnEscape: true,
        closeOnClick: true,
        displayMode: 0,
        layout: "2",
        transitionIn: "fadeInDown",
        transitionOut: "fadeOutRight",
        transitionInMobile: "fadeInUp",
        transitionOutMobile: "fadeOutDown",
        timeout: 2000,
      });
      break;
    case "warning":
      iziToast.warning({
        title: get_translate_phrase('_warningIziToast'),
        titleColor: "#d97706",
        titleSize: "16",
        message: text,
        backgroundColor: "var(--input-form)",
        messageColor: "var(--text-custom)",
        progressBar: false,
        messageSize: "12",
        position: "topRight",
        close: false,
        closeOnEscape: true,
        closeOnClick: true,
        displayMode: 0,
        layout: "2",
        transitionIn: "fadeInDown",
        transitionOut: "fadeOutRight",
        transitionInMobile: "fadeInUp",
        transitionOutMobile: "fadeOutDown",
        timeout: 2000,
      });
      break;
  }
}

tippy('.social_button[data-tippy-content]', {
  placement: 'bottom',
  arrow: false,
  animation: 'shift-away',
  theme: 'neo',
  allowHTML: true,
  appendTo: () => document.body,
  popperOptions: {
    strategy: 'fixed'
  }
});

tippy('.modal-card__body-player-info svg[data-tippy-content], .svg-table[data-tippy-content], .vip-svg[data-tippy-content]', {
  placement: 'top',
  arrow: false,
  animation: 'shift-away',
  theme: 'neo',
  allowHTML: true,
  appendTo: (reference) => reference.closest('.modal-card') || document.body,
  popperOptions: {
    strategy: 'fixed'
  }
});

tippy('[data-tippy-content]:not(.social_button):not(.svg-table):not(.vip-svg):not(.modal-card__body-player-info svg):not(.sc__skins-stickers-keychains):not(.sc__collection-skin)', {
  placement: 'right',
  arrow: false,
  animation: 'shift-away',
  theme: 'neo',
  allowHTML: true,
  appendTo: () => document.body,
  popperOptions: {
    strategy: 'absolute'
  }
});

tippy('.sidebar .sidebal__item, .sidebar .sidebar__submenu', {
  placement: 'right',
  arrow: false,
  animation: 'shift-away',
  theme: 'neo',
  allowHTML: false,
  appendTo: () => document.body,
  popperOptions: { strategy: 'fixed' },
  delay: [100, 0],
  offset: [0, 8],
  content(reference) {
    const text = reference.querySelector('.sidebar__item-text')?.textContent?.trim() || '';
    return text;
  },
  onShow(instance) {
    const isOpened = document.querySelector('.sidebar')?.classList.contains('opened');
    const text = instance.reference.querySelector('.sidebar__item-text')?.textContent?.trim();
    if (isOpened || !text) return false;
  }
});

let scrollTimeout;
window.addEventListener('scroll', function () {
  if (window.tippy && typeof tippy.hideAll === 'function') {
    tippy.hideAll({ duration: 0 });
  }
}, { passive: true, capture: true });


$(document).ready(function () {
  function toggleModalActive($target) {
    if ($(".modalActive").length > 0 && $target.hasClass("modalActive")) {
      $(".modalActive").removeClass("modalActive");
    } else {
      $(".modalActive").removeClass("modalActive");
      $target.addClass("modalActive");
    }
  }

  $("#profMenuOpen").click(function () {
    toggleModalActive($(".user__menu"));
  });

  $("#notifyOpen").click(function () {
    toggleModalActive($(".notification-wrapper"));
  });

  $("#openLanguage").click(function () {
    toggleModalActive($(".language__modal"));
  });

  $(document).mouseup(function (e) {
    if ($(".notification-wrapper").hasClass("modalActive")) {
      let popup = $(".notification-wrapper");
      if (e.target != popup[0] && popup.has(e.target).length === 0) {
        $(".notification-wrapper").animate(
          {
            opacity: "0",
          },
          100,
          "swing",
          function () {
            $(this).removeClass("modalActive");
          }
        );
      }
    }
  });

  $(document).mouseup(function (e) {
    if ($(".user__menu").hasClass("modalActive")) {
      let popup = $(".user__menu");
      if (e.target != popup[0] && popup.has(e.target).length === 0) {
        $(".user__menu").animate(
          {
            opacity: "0",
          },
          100,
          "swing",
          function () {
            $(this).removeClass("modalActive");
          }
        );
      }
    }
  });

  $(document).mouseup(function (e) {
    if ($(".language__modal").hasClass("modalActive")) {
      let popup = $(".language__modal");
      if (e.target != popup[0] && popup.has(e.target).length === 0) {
        $(".language__modal").animate(
          {
            opacity: "0",
          },
          100,
          "swing",
          function () {
            $(this).removeClass("modalActive");
          }
        );
      }
    }
  });
});

$(".header_burger").click(function (event) {
  $(".header_burger,.nav_header_menu").toggleClass("mmactive");
  $("body,html").toggleClass("modal__opened");
});

$(document).ready(function () {
  $(".dropbtn").click(function (event) {
    event.stopPropagation();
    var dropdownBlock = $(this).siblings(".dropdown_block");
    $(".dropdown_block").not(dropdownBlock).removeClass("dropdown_show");
    dropdownBlock.toggleClass("dropdown_show");
  });

  $(document).click(function (event) {
    if (!$(event.target).closest(".dropbtn, .dropdown_block").length) {
      $(".dropdown_block").removeClass("dropdown_show");
    }
  });
});

function decrementValue(button) {
  var input = button.nextElementSibling;
  input.stepDown();
}

function incrementValue(button) {
  var input = button.previousElementSibling;
  input.stepUp();
}

$(document).on("click", "[data-openmodal]", function (e) {
  let modalId = $(e.currentTarget).data("openmodal");
  let modal = $("#" + modalId);

  modal.addClass("visible");
  $("body,html").addClass("modal__opened");
  $(".tabbar_mobile").css("display", "none");
});

function closeModal(modal) {
  let type = modal.data("modal-type");
  let modalId = modal.attr("id");

  modal.removeClass("visible");
  $("body,html").removeClass("modal__opened");
  $(".tabbar_mobile").css("display", "flex");

  if (type === 2 || type === "2") {
    $("#" + modalId).html("");
  }
}

$(document).on("click", ".popup_modal_close", function () {
  let modal = $(this).closest(".popup_modal");
  closeModal(modal);
});

$(document).on("click", ".popup_modal_content", function (event) {
  event.stopPropagation();
});

$(document).on("mousedown", ".popup_modal", function (event) {
  $(this).data("allow-close", $(event.target).hasClass("popup_modal"));
});

$(document).on("mouseup", ".popup_modal", function (event) {
  let modal = $(this);

  if (modal.data("allow-close") && $(event.target).hasClass("popup_modal")) {
    closeModal(modal);
  }

  modal.removeData("allow-close");
});

$(document).ready(function () {
  const $counter = $('#counter');
  const $counterWrapper = $('#counterWrapper');
  let timeoutId = null;

  if ($counter.length === 0 || $counterWrapper.length === 0) {
    return;
  }

  $counter.on('mouseenter', function () {
    if (timeoutId) {
      clearTimeout(timeoutId);
      timeoutId = null;
    }
    $counterWrapper.addClass('visible');
  });

  $counter.on('mouseleave', function () {
    timeoutId = setTimeout(function () {
      if (!$counterWrapper.is(':hover')) {
        $counterWrapper.removeClass('visible');
      }
    }, 1000);
  });

  $counterWrapper.on('mouseenter', function () {
    if (timeoutId) {
      clearTimeout(timeoutId);
      timeoutId = null;
    }
  });

  $counterWrapper.on('mouseleave', function () {
    $counterWrapper.removeClass('visible');
  });
});

function updateAdaptiveSelectText($wrapper) {
  const $dropdown = $wrapper.find('.adaptive-select__dropdown-list');
  const $textSpan = $wrapper.find('.adaptive-select__span_text');
  const $inputs = $dropdown.find('input');
  if (!$inputs.length) return;

  const defaultText = $textSpan.data('default-text');
  if (!defaultText) {
    $textSpan.data('default-text', $textSpan.text().trim());
  }

  const noText = $wrapper.is('[data-no-text]');
  const changeIcon = $wrapper.is('[data-change-icon]');

  const $textInputs = $dropdown.find('input[type="number"], input[type="text"]');
  const textInputValue = $textInputs.filter(function() { return $(this).val().trim() !== ''; }).first().val();

  if (textInputValue) {
    if (!noText) $textSpan.text(textInputValue);
    return;
  }

  const $radioCheckbox = $dropdown.find('input[type="radio"], input[type="checkbox"]');
  if (!$radioCheckbox.length) return;

  const type = $radioCheckbox.first().attr('type');
  if (type === 'radio') {
    const $checkedLabel = $dropdown.find('input[type="radio"]:checked').closest('label');
    const selectedLabel = $checkedLabel.find('.adaptive-select__label-text').text();

    if (!noText) {
      $textSpan.text(selectedLabel || $textSpan.data('default-text'));
    }

    if (changeIcon) {
      const $firstIcon = $wrapper.find('.adaptive-select__fist-icon');
      if ($checkedLabel.length) {
        const $itemIcon = $checkedLabel.find('.adaptive-select__icon');
        if ($itemIcon.length) {
          $firstIcon.html($itemIcon.html());
        } else {
          const defaultIcon = $firstIcon.data('default-icon');
          if (defaultIcon) $firstIcon.html(defaultIcon);
        }
      } else {
        const defaultIcon = $firstIcon.data('default-icon');
        if (defaultIcon) $firstIcon.html(defaultIcon);
      }
    }
  } else if (type === 'checkbox') {
    const $checked = $dropdown.find('input[type="checkbox"]:checked');
    const selectedLabels = $checked
      .map(function () {
        return $(this).closest('label').find('.adaptive-select__label-text').text();
      })
      .get();
    if (!noText) {
      $textSpan.text(selectedLabels.length ? selectedLabels.join(', ') : $textSpan.data('default-text'));
    }

    if (changeIcon) {
      const $firstIcon = $wrapper.find('.adaptive-select__fist-icon');
      const defaultIcon = $firstIcon.data('default-icon');
      if ($checked.length === 1) {
        const $itemIcon = $checked.first().closest('label').find('.adaptive-select__icon');
        if ($itemIcon.length) {
          $firstIcon.html($itemIcon.html());
        } else if (defaultIcon) {
          $firstIcon.html(defaultIcon);
        }
      } else {
        if (defaultIcon) $firstIcon.html(defaultIcon);
      }
    }
  }
}
$(document).on('click', '[open-select]', function (e) {
  e.stopPropagation();
  const selectId = $(this).attr('open-select');
  const select = $(`#${selectId}`);
  if (select.length) {
    $('.adaptive-select__dropdown-list.visible').not(select).removeClass('visible');
    const isOpening = !select.hasClass('visible');
    select.toggleClass('visible');
    
    if (isOpening) {
      const $searchInput = select.find('input[type="search"]');
      if ($searchInput.length) {
        $searchInput.val('');
        select.find('li').css('display', '');
        select.find('.no-data').remove();
      }
    }
  }
});
$(document).on('click', function () {
  $('.adaptive-select__dropdown-list.visible').removeClass('visible');
});
$(document).on('click', '.adaptive-select__dropdown-list', function (e) {
  e.stopPropagation();
});
$(document).on('change', '.adaptive-select__dropdown-list input[type="radio"], .adaptive-select__dropdown-list input[type="checkbox"]', function () {
  const $wrapper = $(this).closest('.adaptive-select-wrapper');
  
  $wrapper.find('.adaptive-select__dropdown-list input[type="number"], .adaptive-select__dropdown-list input[type="search"], .adaptive-select__dropdown-list input[type="text"]').val('');
  
  updateAdaptiveSelectText($wrapper);

  if ($(this).attr('type') === 'radio') {
    $wrapper.find('.adaptive-select__dropdown-list').removeClass('visible');
  }
});

$(document).on('input', '.adaptive-select__dropdown-list input[type="number"], .adaptive-select__dropdown-list input[type="search"], .adaptive-select__dropdown-list input[type="text"]', function () {
  const $input = $(this);
  const $wrapper = $input.closest('.adaptive-select-wrapper');
  const $dropdown = $wrapper.find('.adaptive-select__dropdown-list');
  const inputType = $input.attr('type');
  const inputValue = $input.val().trim();

  $dropdown.find('input[type="radio"], input[type="checkbox"]').prop('checked', false);

  if (inputType === 'search') {
    const searchText = inputValue.toLowerCase();
    let visibleCount = 0;
    
    $dropdown.find('li').each(function () {
      const $li = $(this);
      const labelText = $li.find('.adaptive-select__label-text').text().toLowerCase();
      
      if (searchText === '' || labelText.includes(searchText)) {
        $li.show();
        visibleCount++;
      } else {
        $li.hide();
      }
    });
    
    $dropdown.find('.no-data').remove();
    
    if (visibleCount === 0 && searchText !== '') {
      $dropdown.append('<div class="no-data">' + get_translate_phrase('_nothingFound') + '</div>');
    }
  }

  updateAdaptiveSelectText($wrapper);
});
function initAdaptiveSelects(context = document) {
  $(context).find('.adaptive-select-wrapper').each(function () {
    const $textSpan = $(this).find('.adaptive-select__span_text');
    if (!$textSpan.data('default-text')) {
      $textSpan.data('default-text', $textSpan.text().trim());
    }
    if ($(this).is('[data-change-icon]')) {
      const $firstIcon = $(this).find('.adaptive-select__fist-icon');
      if (!$firstIcon.data('default-icon')) {
        $firstIcon.data('default-icon', $firstIcon.html());
      }
    }
    updateAdaptiveSelectText($(this));
  });
}
initAdaptiveSelects();


function renderPagination(page, pages) {
  pages = Number.isFinite(+pages) ? Math.max(0, Math.trunc(pages)) : 0;
  page = Number.isFinite(+page) ? Math.trunc(page) : 1;
  if (pages <= 1) return "";

  page = Math.min(Math.max(page, 1), pages);

  let startPage = 1;
  if (pages > 5) {
    if (page <= 3) {
      startPage = 1;
    } else if (page >= pages - 2) {
      startPage = pages - 4;
    } else {
      startPage = page - 2;
    }
  }

  const parts = [];
  parts.push('<div class="pagination">');

  if (startPage > 1) {
    parts.push(
      '<a class="button_pagination" data-page="1">' +
      '<svg><use href="/resources/img/sprite.svg#double-chevrone-left"></use></svg>' +
      '</a>'
    );
  }

  if (page > 1) {
    parts.push(
      `<a class="button_pagination" data-page="${page - 1}">` +
      '<svg><use href="/resources/img/sprite.svg#single-chevrone-left"></use></svg>' +
      '</a>'
    );
  }

  for (let i = startPage; i < startPage + 5 && i <= pages; i++) {
    const active = i === page ? " active" : "";
    parts.push(
      `<a class="button_pagination${active}" data-page="${i}">${i}</a>`
    );
  }

  if (page < pages) {
    parts.push(
      `<a class="button_pagination" data-page="${page + 1}">` +
      '<svg><use href="/resources/img/sprite.svg#single-chevrone-right"></use></svg>' +
      '</a>'
    );
  }

  if (startPage + 4 < pages) {
    parts.push(
      `<a class="button_pagination" data-page="${pages}">` +
      '<svg><use href="/resources/img/sprite.svg#double-chevrone-right"></use></svg>' +
      '</a>'
    );
  }

  parts.push("</div>");
  return parts.join("");
}

$(document).on('click', '.number-minus, .number-plus', function(e) {
  e.preventDefault();
  const $button = $(this);
  const $input = $button.hasClass('number-minus') ? $button.next('input[type="number"]') : $button.prev('input[type="number"]');
  
  if ($input.length) {
    if ($button.hasClass('number-minus')) {
      $input[0].stepDown();
    } else {
      $input[0].stepUp();
    }
    
    $input.trigger('change');
    $input.trigger('input');
    
    const $wrapper = $input.closest('.adaptive-select-wrapper');
    if ($wrapper.length) {
      updateAdaptiveSelectText($wrapper);
    }
  }
});


$(function () {
  const sidebar = $('.sidebar');
  const key = 'sidebarOpened';

  const savedOpened = localStorage.getItem(key);
  if (savedOpened === 'true') {
    sidebar.addClass('opened');
  } else if (savedOpened === 'false') {
    sidebar.removeClass('opened');
  }

  $('.sidebar__toggle').on('click', function () {
    sidebar.toggleClass('opened');
    localStorage.setItem(key, sidebar.hasClass('opened'));

    $('.sidebar__submenu').attr('aria-expanded', 'false').removeClass('expanded');
    $('.sidebar__sublist:visible').stop(true, true).slideUp(200);
    const $panels = $('.sidebar__flyout');
    $panels.removeClass('visible');
    setTimeout(() => $panels.remove(), 200);

    if (window.tippy && typeof tippy.hideAll === 'function') {
      tippy.hideAll({ duration: 0 });
    }

    if (sidebar.hasClass('opened') && typeof window.applyStoredSidebarExpansions === 'function') {
      window.applyStoredSidebarExpansions();
    }
  });
});

$(function () {
  $('.sidebar__submenu').attr('aria-expanded', 'false').removeClass('expanded');

  const SUBMENU_STORAGE_KEY = 'sidebarExpandedItems';

  function getSubmenuKey($submenu) {
    return ($submenu.find('.sidebar__item-text').text().trim() || '').toLowerCase();
  }

  function saveExpandedIds(ids) {
    const unique = Array.from(new Set(ids.filter(Boolean)));
    localStorage.setItem(SUBMENU_STORAGE_KEY, JSON.stringify(unique));
  }

  function applyStoredExpansions() {
    const isOpened = $('.sidebar').hasClass('opened');

    const keys = [];
    $('.sidebar__sublist .active').each(function () {
      const $sublist = $(this).closest('.sidebar__sublist');
      const $submenu = $sublist.closest('li').children('.sidebar__submenu');
      const key = getSubmenuKey($submenu);
      if (key && !keys.includes(key)) keys.push(key);
    });

    saveExpandedIds(keys);

    if (isOpened) {
      keys.forEach(function (key) {
        const $submenu = $('.sidebar__submenu').filter(function () {
          return getSubmenuKey($(this)) === key;
        }).first();
        if ($submenu.length) {
          const $sublist = $submenu.closest('li').children('.sidebar__sublist');
          $submenu.attr('aria-expanded', 'true').addClass('expanded');
          $sublist.stop(true, true).show();
        }
      });
    }
  }

  window.applyStoredSidebarExpansions = applyStoredExpansions;
  applyStoredExpansions();

  function openSidebarFlyout($trigger, $sublist) {
    const sidebarWidth = $('.sidebar').outerWidth() || 0;
    const left = sidebarWidth + 10;
    const trigTop = $trigger.offset().top || 0;

    const $panel = $('<div class="sidebar__flyout"></div>')
      .css({ left, top: trigTop, opacity: 0, visibility: 'hidden' });

    const $list = $sublist.clone()
      .removeClass('sidebar__sublist')
      .addClass('sidebar__flyout-list')
      .show();

    $panel.append($list);
    $('body').append($panel);

    const ph = $panel.outerHeight();
    const maxTop = window.innerHeight - ph - 10;
    $panel.css({ top: Math.max(10, Math.min(trigTop, maxTop)) });

    requestAnimationFrame(() => {
      $panel.addClass('visible');
    });
  }

  function closeFlyouts() {
    const $panels = $('.sidebar__flyout');
    $panels.removeClass('visible');
    setTimeout(() => $panels.remove(), 200);
    $('.sidebar__submenu[aria-expanded="true"]').attr('aria-expanded', 'false');
    $('.sidebar__submenu.expanded').removeClass('expanded');
  }

  $(document).on('click', '.sidebar__submenu', function (e) {
    e.preventDefault();

    const $trigger = $(this);
    const $item = $trigger.closest('li');
    const $sublist = $item.children('.sidebar__sublist');
    if (!$sublist.length) return;

    const isSidebarOpened = $('.sidebar').hasClass('opened');

    if (!isSidebarOpened) {
      const wasOpen = $trigger.attr('aria-expanded') === 'true';
      $('.sidebar__submenu').not($trigger).attr('aria-expanded', 'false').removeClass('expanded');
      closeFlyouts();

      if (wasOpen) {
        $trigger.attr('aria-expanded', 'false').removeClass('expanded');
        return;
      }

      $trigger.attr('aria-expanded', 'true').addClass('expanded');
      openSidebarFlyout($trigger, $sublist);
      return;
    }

    const isOpen = $trigger.attr('aria-expanded') === 'true';

    $('.sidebar__submenu').not($trigger).attr('aria-expanded', 'false').removeClass('expanded').each(function () {
      const $otherList = $(this).closest('li').children('.sidebar__sublist');
      if ($otherList.is(':visible')) {
        $otherList.stop(true, true).slideUp(200);
      }
    });
    closeFlyouts();

    if (isOpen) {
      $sublist.stop(true, true).slideUp(200);
      $trigger.attr('aria-expanded', 'false').removeClass('expanded');
    } else {
      $sublist.stop(true, true).slideDown(200);
      $trigger.attr('aria-expanded', 'true').addClass('expanded');
    }
  });

  $(document).on('mousedown', function (e) {
    if (!$('.sidebar').hasClass('opened')) {
      if (!$(e.target).closest('.sidebar__flyout, .sidebar__submenu').length) {
        closeFlyouts();
      }
    }
  });
  $(window).on('resize', function () {
    if (!$('.sidebar').hasClass('opened')) {
      closeFlyouts();
    }
  });
});

(function ($) {
  const io = new IntersectionObserver(entries =>
    entries.forEach(e => {
      if (!e.isIntersecting) return;
      const el = e.target, $el = $(el);

      if (el.tagName === "IMG") {
        $el.wrap('<span class="skeleton--default" style="display:inline-block;width:100%;height:100%;border-radius:inherit;"></span>');
        $el.one('load error', function () {
          $el.unwrap();
        });
        el.src = $el.data("src");
      }
      else if (el.tagName === "VIDEO") {
        $el.wrap('<span class="skeleton--default" style="display:block;width:100%;height:100%;border-radius:inherit;"></span>');
        const s = $el.find("source");
        s.attr("src", s.data("src"));
        el.load();
        $el.one('canplay error', function () {
          $el.unwrap();
        });
      }
      else if (el.tagName === "PICTURE") {
        $el.wrap('<span class="skeleton--default" style="display:inline-block;width:100%;height:100%;border-radius:inherit;"></span>');
        $el.find("source").each((_, s) =>
          s.srcset = $(s).data("srcset")
        );
        const img = $el.find("img")[0];
        $(img).one('load error', function () {
          $el.unwrap();
        });
        img.src = $(img).data("src");
      }

      $el.removeClass("lazy")
        .removeAttr("data-src")
        .removeAttr("data-srcset");
      io.unobserve(el);
    }),
    { rootMargin: "200px 0px" }
  );
  const observe = nodes =>
    $(nodes).find(".lazy").addBack(".lazy")
      .each((_, el) => io.observe(el));
  $(function () {
    observe(document);
    new MutationObserver(m =>
      m.forEach(x => observe(x.addedNodes))
    ).observe(document.body, {
      childList: true,
      subtree: true
    });
  });
})(jQuery);