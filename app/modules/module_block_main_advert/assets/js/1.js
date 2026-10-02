$('#addBannerForm').on('submit', function (e) {
    e.preventDefault();
    let formData = new FormData(this);
    $.ajax({
        url: location.href,
        method: "POST",
        data: formData,
        dataType: 'json',
        global: false,
        processData: false,
        contentType: false,
        success: function (data) {
            if (data.status === 'success') {
                noty(data.text, data.status);
                setTimeout(() => location.reload(), 1000);
            } else {
                noty(data.text, data.status);
            }
        }
    });
});

$('[id^="editBannerForm"]').on('submit', function (e) {
    e.preventDefault();
    let formData = new FormData(this);
    $.ajax({
        url: location.href,
        method: "POST",
        data: formData,
        dataType: 'json',
        global: false,
        processData: false,
        contentType: false,
        success: function (data) {
            if (data.status === 'success') {
                noty(data.text, data.status);
                setTimeout(() => location.reload(), 1000);
            } else {
                noty(data.text, data.status);
            }
        }
    });
});

$(document).ready(function () {
    $('#file-input').on('change', function (e) {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (event) {
                $('#bannerPreview').attr('src', event.target.result).show();
                $('#file-info').text(file.name);
            };
            reader.readAsDataURL(file);
        } else {
            $('#bannerPreview').hide().attr('src', '#');
            $('#file-info').text(get_translate_module_phrase('module_block_main_advert', '_exampleTitle'));
        }
    });

    $('#generalText').on('input', function () {
        $('#generalTextPreview').text($(this).val());
    });

    $('#secondaryText').on('input', function () {
        $('#secondaryTextPreview').text($(this).val());
    });

    $('input[name="color_text"]').on('input', function () {
        const color = $(this).val();
        $('#generalTextPreview').css('color', color);
    });

    $('input[name="color_other_text"]').on('input', function () {
        const color = $(this).val();
        $('#secondaryTextPreview').css('color', color);
    });

    $('input[name="color_bg_start"], input[name="color_bg_end"]').on('input', function () {
        const startColor = $('input[name="color_bg_start"]').val();
        const endColor = $('input[name="color_bg_end"]').val();
        $('#infoBlockCard').css('background-image', `linear-gradient(to right, ${startColor}, ${endColor})`);
    });
});

$(document).ready(function () {
    function initBannerEdit(modal) {
        modal.find('input[name="url"]').off('input').on('input', function () {
            modal.find('#linkBanner').text($(this).val());
        });
        modal.find('input[name="text_main"]').off('input').on('input', function () {
            modal.find('#generalTextPreview').text($(this).val());
        });
        modal.find('input[name="text_other"]').off('input').on('input', function () {
            modal.find('#secondaryTextPreview').text($(this).val());
        });
        modal.find('input[name="color_text"]').off('input').on('input', function () {
            modal.find('#generalTextPreview').css('color', $(this).val());
        });
        modal.find('input[name="color_other_text"]').off('input').on('input', function () {
            modal.find('#secondaryTextPreview').css('color', $(this).val());
        });
        modal.find('input[name="color_bg_start"], input[name="color_bg_end"]').off('input').on('input', function () {
            const startColor = modal.find('input[name="color_bg_start"]').val();
            const endColor = modal.find('input[name="color_bg_end"]').val();
            modal.find('#infoBlockCard').css('background-image', `linear-gradient(to right, ${startColor}, ${endColor})`);
        });
    }

    $(document).on('change', 'input[type="file"][name="file"]', function (e) {
        const modal = $(this).closest('.popup_modal.visible');
        console.log('modal id:', modal.attr('id'));

        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (event) {
                modal.find('#bannerPreview').attr('src', event.target.result).show();
                modal.find('#file-info').text(file.name);
            };
            reader.readAsDataURL(file);
        } else {
            modal.find('#bannerPreview').hide().attr('src', '/app/modules/module_block_main_advert/assets/img/null-image.svg');
            modal.find('#file-info').text(get_translate_module_phrase('module_block_main_advert', '_fileNotImage'));
        }
    });

    $('[data-openmodal]').on('click', function () {
        const modalId = $(this).data('openmodal');
        const modal = $('#' + modalId);
        modal.addClass('visible');
        initBannerEdit(modal);
    });

    $('.popup_modal_close').on('click', function () {
        $(this).closest('.popup_modal').removeClass('visible');
    });
});

$(document).on('click', '#del_banner', function (e) {
    e.preventDefault();
    const bannerId = $(this).data('banner');
    openDialog({
        title: get_translate_module_phrase('module_block_main_advert', '_delete_banner'),
        message: get_translate_module_phrase('module_block_main_advert', '_confirm_text_delete_banner'),
        confirmText: get_translate_phrase('_Delete_Action'),
        cancelText: get_translate_phrase('_Close'),
        onConfirm: function () {
            $.ajax({
                url: location.href,
                method: 'POST',
                dataType: 'json',
                data: { del_banner: true, id_del: bannerId },
                success: function (data) {
                    if (data.status === 'success') {
                        noty(data.text, data.status);
                        $(`[data-bannerid="${bannerId}"]`).remove();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        noty(data.text, data.status);
                    }
                }
            });
        }
    });
});

if (typeof Sortable !== 'undefined') {
    new Sortable(document.getElementById('sortable-banners'), {
        filter: '.filtered',
        swapThreshold: 1,
        animation: 150,
        onEnd: function () {
            const order = [];
            $('#sortable-banners .info-block__card').not('.filtered').each(function () {
                const id = $(this).data('bannerid');
                if (id) order.push(id);
            });
            $.ajax({
                url: location.href,
                method: 'POST',
                data: { sortBanners: true, order: order },
                dataType: "json",
                global: false,
                success: function (data) {
                    noty(data.text, data.status);
                }
            });
        }
    });
}