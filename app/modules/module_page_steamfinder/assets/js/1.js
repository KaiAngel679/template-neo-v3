function sendRequest(body) {
    return $.ajax({
        url: location.href,
        type: 'POST',
        data: body,
        dataType: 'json'
    });
}

function checkAvatar(checkavatar, steamid) {
    if (checkavatar === 1) {
        avatar.push(steamid);
    }
    RenderingAvatar();
}

var isLoading = false;
$("#searchInfo").click(function (e) {
    e.preventDefault();
    if (isLoading) {
        return false;
    }
    if ($("#userSteam").val().trim() === "") {
        return false;
    }
    isLoading = true;
    $("#searchInfo").prop("disabled", true);
    $(".sf__search, .sf__title").addClass("up");
    $('#app').html('');
    $('#app').append('<div class="col-md-4"><div class="sf__loader sf__loader-1"></div></div><div class="col-md-8"><div class="sf__loader sf__loader-1"></div></div><div class="col-md-6"><div class="sf__loader sf__loader-2"></div></div><div class="col-md-6"><div class="sf__loader sf__loader-2"></div></div>');
    sendRequest({ search: true, steamid: $("#userSteam").val() })
        .done(function (result) {
            if (result.status != 'error') {
                $('#app').html('');
                $('#app').append(result.steam);
                checkAvatar(result.checkAvatar, result.steamid);
                $('#app').append(result.faceit);
                $('#app').append(result.servers);
                $('#app').append(result.cs2stats);
                $('#app').append(result.games);
            } else {
                $(".sf__search, .sf__title").removeClass("up");
                $('#app').html('');
                noty(result.text, result.status);
            }
        })
        .always(function () {
            isLoading = false;
            $("#searchInfo").prop("disabled", false);
        });
});

$("#userSteam").on("input", function () {
    if ($(this).val().trim() === "") {
        $(".sf__search, .sf__title").removeClass("up");
        $('#app').html('');
    }
});