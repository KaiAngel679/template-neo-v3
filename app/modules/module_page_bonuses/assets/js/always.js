$('#takeBonus').on('click', function() {
    $.post(location.href, { hide_modal: true }, function() {
        window.location.href = '/bonuses/';
    });
});

$('#refuseBonus').on('click', function() {
    $.post(location.href, { hide_modal: true }, function() {
        $('.popup_modal').removeClass('visible');
    });
});