$(document)
    .on('mouseenter', '.user_back', function () {
        const video = $(this).find('.back_video')[0];
        if (video) {
            video.play();
        }
    })
    .on('mouseleave', '.user_back', function () {
        const video = $(this).find('.back_video')[0];
        if (video) {
            video.pause();
        }
    });

$(function () {
    const update = function () {
        $.ajax({
            type: 'POST',
            url: domain + "app/modules/module_page_profiles/includes/js_controller.php",
            data: ({ online: info }),
            dataType: 'json',
            global: false,
            async: true,
            success: function (data) {
                var last_seen = document.getElementById('online_status');
                var last_seen_link = document.getElementById("connect_link");
                const tippyInstance = last_seen._tippy;
                last_seen.innerHTML = data['online'];
                if (data['ip']) {
                    last_seen_link.setAttribute("href", 'steam://run/730//+connect ' + data['ip'] + '');
                    last_seen.classList.add("button_playing_server");
                    if (tippyInstance) {
                        tippyInstance.destroy();
                    };
                    last_seen.classList.remove("lastconnect");
                } else {
                    last_seen.classList.remove("button_playing_server");
                    last_seen.classList.add("lastconnect");
                    last_seen_link.removeAttribute("href");
                    if (tippyInstance) {
                        tippyInstance.destroy();
                    };
                }
            }
        });
    };
    setInterval(update, 5000);
    update();
});


$('#options_one').on("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    $.ajax({
        type: "post",
        url: location.href,
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function (data) {
            noty(data.text, data.status);
            setTimeout(() => location.reload(), 1000);
        }
    });
});