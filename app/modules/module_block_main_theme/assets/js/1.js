$(document).ready(function () {
    $("#openThemeEditor").on("click", function () {
        $("#themeSide").addClass("opened");
    });

    $("#closeThemeEditor").on("click", function () {
        $("#themeSide").removeClass("opened");
    });

    $(document).on("keyup", function (e) {
        if (e.key === "Escape") {
            $("#themeSide").removeClass("opened");
        }
    });
    $(document).on('change', 'input[name="theme_colors"]', function () {
        const rawThemeVal = String($(this).val() || '');
        const themeKey = (rawThemeVal.match(/(\d+)$/) || [])[1] || rawThemeVal.replace(/^theme-/, '');
        const detectedType = $(this).data('type') || 'default';
        if (!themeKey) return;
        openDialog({
            title: get_translate_module_phrase('module_block_main_theme', '_apply_theme'),
            message: get_translate_module_phrase('module_block_main_theme', '_apply_theme_message'),
            confirmText: get_translate_module_phrase('module_block_main_theme', '_apply_theme_confirm'),
            cancelText: get_translate_module_phrase('module_block_main_theme', '_cancel'),
            onConfirm: () => {
                $.ajax({
                    url: location.href,
                    method: 'POST',
                    data: { theme_change: true, theme: themeKey, type: detectedType },
                    dataType: 'json'
                }).done(function (resp) {
                    if (resp && resp.status === 'success' && resp.colors) {
                        for (const k in resp.colors) {
                            if (!Object.prototype.hasOwnProperty.call(resp.colors, k)) continue;
                            document.documentElement.style.setProperty(k, resp.colors[k]);
                        }
                    }
                    location.reload();
                });
            }
        });
    });

    $("#resetTheme").on("click", function () {
        openDialog({
            title: $(this).data("title"),
            message: $(this).data("text"),
            confirmText: $(this).data("confirm"),
            cancelText: $(this).data("cancel"),
            onConfirm: () => {
                $.ajax({
                    url: location.href,
                    method: 'POST',
                    data: { theme_reset: true },
                    dataType: 'json'
                }).done(function (resp) {
                    noty(resp.text, resp.status);
                    location.reload();
                })
            }
        });
    });
    $("#saveTheme").on("click", function (e) {
        e.preventDefault();
        let form;
        const activeTab = document.querySelector('[role="tab"][aria-selected="true"]');
        if (activeTab) {
            const panelId = activeTab.getAttribute('aria-controls');
            if (panelId) {
                const panel = document.getElementById(panelId);
                if (panel) {
                    form = panel.matches('form') ? panel : panel.querySelector('form');
                }
            }
        }
        if (!form) form = document.querySelector('#tabpanel-1');
        form_Data = new FormData(form);
        if (form.id === 'tabpanel-1') {
            form_Data.append('save_theme', '1');
        } else if (form.id === 'tabpanel-2') {
            form_Data.append('save_background', '1');
        }
        $.ajax({
            url: location.href,
            method: 'POST',
            data: form_Data,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (resp) {
            noty(resp.text, resp.status);
            if (resp.update_background) {
                (function () {
                    const raw = resp.update_background;
                    const obj = raw && typeof raw === 'object' && !Array.isArray(raw);
                    const bg = obj ? raw : {
                        type: resp.type,
                        gradients: resp.gradients,
                        image: resp.image,
                        'gradients-stars': resp['gradients-stars']
                    };
                    const type = String(bg.type || '');
                    const $b = $('body');
                    const clearGradients = () => $b.removeClass((_, c) => (c.match(/\btheme__gradient-\S+/g) || []).join(' '));
                    const setImage = (url) => $b.css(url ? {
                        'background-image': "url('" + url + "')",
                        'background-size': 'cover',
                        'background-attachment': 'fixed',
                        'background-repeat': 'no-repeat'
                    } : { 'background-image': '' });
                    const setGradient = (g) => { if (g) $b.addClass('theme__gradient-' + g); };
                    const starId = 'starfield';
                    const removeStars = () => $('#' + starId).remove();
                    const ensureStars = (r = 0) => {
                        let el = document.getElementById(starId);
                        if (!el) {
                            el = document.createElement('canvas');
                            el.id = starId;
                            document.body.prepend(el);
                        }
                        if (typeof window.stars === 'function') window.stars(el);
                        else if (r < 10) setTimeout(() => ensureStars(r + 1), 50);
                    };
                    clearGradients();
                    switch (type) {
                        case '2':
                            setImage(bg.image);
                            removeStars();
                            break;
                        case '3':
                            setImage();
                            setGradient(bg.gradients);
                            removeStars();
                            break;
                        case '4':
                            setImage();
                            ensureStars();
                            break;
                        case '5':
                            setImage();
                            setGradient(bg['gradients-stars'] || bg.gradients);
                            ensureStars();
                            break;
                        default:
                            setImage();
                            removeStars();
                    }
                })();
            }
        });
    });
    $("#savePalette").on("click", function (e) {
        e.preventDefault();
        form = new FormData(document.querySelector('#tabpanel-1'));
        form.append('save_palette', '1');
        $.ajax({
            url: location.href,
            method: 'POST',
            data: form,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (resp) {
            noty(resp.text, resp.status);
        });
    });
    $(".theme__delete").on("click", function (e) {
        e.preventDefault();
        const theme = this.dataset.theme;
        if (!theme) return;
        openDialog({
            title: get_translate_module_phrase('module_block_main_theme', '_delete_theme'),
            message: get_translate_module_phrase('module_block_main_theme', '_delete_theme_message'),
            confirmText: get_translate_module_phrase('module_block_main_theme', '_delete'),
            cancelText: get_translate_module_phrase('module_block_main_theme', '_cancel'),
            onConfirm: () => {
                $.ajax({
                    url: location.href,
                    method: 'POST',
                    data: { theme_delete: true, theme },
                    dataType: 'json'
                }).done(function (resp) {
                    noty(resp.text, resp.status);
                    closest.element('.themes__theme').remove();
                })
            }
        });
    });
});

const SIMPLE_HEX = ['text-default', 'text-default-invert', 'text-custom', 'text-secondary', 'bg', 'card', 'tooltip', 'bg-modal', 'input-form', 'btn-disabled', 'button', 'button-hover', 'stars'];
const VISIBLE_TEXT = new Set(['span', 'money', 'money-bg', 'input-form', 'text-default-invert', 'text-default', 'text-secondary', 'text-custom', 'star', 'star_gradient']);

const PENDING = new Map();
let RAF = 0;

function flushColors() {
    PENDING.forEach((c, el) => applyColorInternal(el, c));
    PENDING.clear();
    RAF = 0;
}

function scheduleColor(el, color) {
    PENDING.set(el, color);
    if (!RAF) RAF = requestAnimationFrame(flushColors);
}

function applyColorInternal(el, color) {
    if (!color) return;
    const name = el.dataset.colorName;

    el.style.backgroundColor = color.string('hex');
    const form = el.closest('form') || document.getElementById('tabpanel-1');
    if (form && form.id === 'tabpanel-1') {
        let hidden = form.querySelector(`input[type="hidden"][name="theme_colors[${name}]"]`);
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `theme_colors[${name}]`;
            form.appendChild(hidden);
        }
        hidden.value = color.string('hex');
    } else if (form && form.id === 'tabpanel-2') {
        let hidden = form.querySelector(`input[type="hidden"][name="${name}_color"]`);
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `${name}_color`;
            form.appendChild(hidden);
        }
        hidden.value = color.string('hex');
    }

    const root = document.documentElement.style;
    if (name === 'span') {
        const rgb = color.string('hex');
        root.setProperty('--span', rgb);
        root.setProperty('--span-10', color.clone().alpha(0.1).string('hex'));
        root.setProperty('--span-low', color.clone().alpha(0.05).string('hex'));
        root.setProperty('--span-middle', color.clone().alpha(0.25).string('hex'));
        root.setProperty('--span-half', color.clone().alpha(0.5).string('hex'));
    } else if (name === 'money') {
        root.setProperty('--money', color.string('hex'));
        root.setProperty('--money-bg', color.clone().alpha(0.10).string('hex'));
    } else if (SIMPLE_HEX.includes(name)) {
        root.setProperty(`--${name}`, color.string('hex'));
    }

    let display = el.querySelector('.color');
    if (!display) {
        display = document.createElement('div');
        display.className = 'color';
        el.appendChild(display);
    }
    if (VISIBLE_TEXT.has(name)) {
        display.className = 'color';
        display.style.color = color;
    } else {
        display.className = 'color bad-visibility-text';
        display.style.color = '';
    }
    display.textContent = color.string('hex');
}

function applyColor(el, color) {
    scheduleColor(el, color);
}

document.querySelectorAll('#palette .cell').forEach(el => {
    el.addEventListener('click', () => {
        new ColorPicker(el, {
            submitMode: 'instant',
            headless: true,
            formats: false,
            showClearButton: true,
            enableAlpha: true,
            color: el.style.backgroundColor
        }).on('pick', c => applyColor(el, c)).prompt();
    });
});


$(document).on('change', '.custom-file-input', function () {
    const fileInput = this;
    const file = fileInput.files[0];
    const infoBlock = $(fileInput).closest('.file-upload-container').find('.file-upload-info');

    if (file) {
        const size = (file.size / 1024).toFixed(1) + ' KB';
        const name = file.name.length > 40 ? file.name.substring(0, 37) + '...' : file.name;

        infoBlock.text(`${name} (${size})`).show();
    } else {
        infoBlock.text('Файл не выбран').show();
    }
});
