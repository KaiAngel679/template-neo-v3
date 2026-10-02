function InfoStats() {
    $.ajax({
    type: 'POST',
    url: domain + "app/modules/module_block_main_statistics/includes/js_controller.php",
    data: ({ stats: true }),
    dataType: 'json',
    global: false,
    success: function (data) {
        $('#count').html('');
        $('#count24').html('');
        $('#count7d').html('');
        $('#counta').html('');
        $('#countb').html('');
        $('#countm').html('');
        $('#countv').html('');
        $('#count').append(data['CountPlayers']);
        $('#count24').append(data['CountPlayers24']);
        $('#count7d').append(data['CountPlayers7d']);
        $('#counta').append(data['CountAdmins']);
        $('#countb').append(data['CountBans']);
        $('#countm').append(data['CountMutes']);
        $('#countv').append(data['CountVip']);
    }
    });
}
InfoStats();
setInterval(InfoStats, 30000);