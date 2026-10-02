let info, players, update_message = false;
function UpdateServers() {
    $.ajax({
        type: 'POST',
        url: domain + "app/modules/module_block_main_servers/includes/js_controller.php",
        data: ({ servers: true }),
        dataType: 'json',
        global: false,
        success: function (data) {
            let minplayers = 0;
            let maxplayers = 0;
            if (update_message) {
                if (data.message) {
                    noty(data.message, 'success');
                    delete data.message;
                }
                update_message = false;
            }
            for (let i = 0; i < data.servers.length; i++) {
                info = data.servers[i];
                players = data.servers[i]["players"];
                minplayers += info['Players'];
                maxplayers += info['MaxPlayers'];

                const serverNameElement = document.getElementById('server-name-' + i);
                if (serverNameElement) serverNameElement.innerHTML = info['HostName'];

                const serverMapImageElement = document.getElementById('server-map-image-' + i);
                const serverMapImageModalElement = document.getElementById('server-map-image-modal-' + i);
                if (serverMapImageElement) serverMapImageElement.setAttribute("src", "/storage/cache/img/maps/" + data.servers[i]['Mod'] + "/" + info['Map_image'] + ".webp");
                if (serverMapImageModalElement) serverMapImageModalElement.setAttribute("src", "/storage/cache/img/maps/" + data.servers[i]['Mod'] + "/" + info['Map_image'] + ".webp");

                const serverPlayersElement = document.getElementById('server-players-' + i);
                const serverPlayersModalElement = document.getElementById('server-players-modal-' + i);
                if (serverPlayersElement) serverPlayersElement.innerHTML = info['Players'] + "/" + info['MaxPlayers'];
                if (serverPlayersModalElement) serverPlayersModalElement.innerHTML = info['Players'] + "/" + info['MaxPlayers'];

                const serverMapElement = document.getElementById('server-map-' + i);
                if (serverMapElement) serverMapElement.innerHTML = info['Map'];

                const copyBtnElement = document.getElementById('copy_btn_' + i);
                const copyBtnSecondElement = document.getElementById('copy_btnsecond_' + i);
                if (copyBtnElement) copyBtnElement.setAttribute("data-clipboard-text", 'connect ' + info['ip']);
                if (copyBtnSecondElement) copyBtnSecondElement.setAttribute("data-clipboard-text", 'connect ' + info['ip']);

                const serverModeElement = document.getElementById('server-mode-' + i);
                if (serverModeElement) serverModeElement.setAttribute("data-mode", info['GameMode']);

                const serverCityElement = document.getElementById('server-city-' + i);
                if (serverCityElement) serverCityElement.innerHTML = info['City'];

                const serverBageElement = document.getElementById('server-bage-' + i);
                if (serverBageElement) serverBageElement.innerHTML = info['Bage'];

                const serverCountryElement = document.getElementById('server-country-' + i);
                if (serverCountryElement) {
                    if (info['Country'].length > 0) {
                        serverCountryElement.innerHTML = '<img src="storage/cache/img/icons/custom/flags/' + info['Country'] + '.svg" alt="">';
                    } else {
                        serverCountryElement.innerHTML = '';
                    }
                }

                const connectServerElement = document.getElementById('connect_server_' + i);
                if (connectServerElement) connectServerElement.setAttribute("onclick", `location.href = 'steam://run/${info["Mod"]}//+connect ${info["ip"]}'`);

                let currentpercent = info["Players"] / info["MaxPlayers"];
                let progressElement = document.getElementById("progess-formula-" + i);
                if (progressElement) {
                    progressElement.style.setProperty("width", 100 * info['Players'] / info['MaxPlayers'] + "%", "important");
                    if (currentpercent <= 0.5)
                        progressElement.style.background = "var(--green)";
                    else if (currentpercent <= 0.8)
                        progressElement.style.background = "var(--orange)";
                    else if (currentpercent <= 1)
                        progressElement.style.background = "var(--red)";
                }

                let map_name = info['Map'];
                let clipped_map_name = map_name.substring(map_name.search('_') + 1, map_name.length);
                const serverMapTwoElement = document.getElementById('server-maptwo-' + i);
                const po = document.getElementById('players_online_' + i);
                if (serverMapTwoElement) serverMapTwoElement.innerHTML = clipped_map_name;

                if (players) {
                    if (players.length > 0) {
                        if (po) {
                            po.innerHTML = "";
                            for (var i2 = 0; i2 < players.length; i2++) {
                                var str = '<li class="hover_mon">' +
                                    '<span class="mon_player_name">' + players[i2]['Name'].replace(/[\u00A0-\u9999<>\&]/g, function (i) {
                                        return '&#' + i.charCodeAt(0) + ';';
                                    }) + '</span>' +
                                    '<span class="non_mob">' + players[i2]['Frags'] + '</span>' +
                                    '<span>' + players[i2]['TimeF'] + '</span>' +
                                    '</li>';
                                po.insertAdjacentHTML('beforeend', str);
                            }
                        }
                    } else {
                        if (po) {
                            po.innerHTML = "";
                        }
                        const btnConnectElements = document.querySelectorAll('.btn_connect_' + i);
                        btnConnectElements.forEach(btn => {
                            btn.onclick = null;
                            btn.href = `steam://run/${info["ip"]}//+connect ` + info['ip'];
                        });
                        const strConnectElements = document.querySelectorAll('.str_connect_' + i);
                        strConnectElements.forEach(str => {
                            str.onclick = () => { document.location = `steam://run/${info["ip"]}//+connect ` + info['ip']; };
                        });
                    }
                }
                if (connectServerElement) connectServerElement.setAttribute("href", `steam://run/${info["ip"]}//+connect ` + info['ip']);
            }
        }
    });
}
UpdateServers();

function get_players_data(i) {
    var modal = document.getElementById('server-players-online-' + i);
    if (modal) $(modal).addClass('modal_show');
}

function close_modal(i) {
    var modal = document.getElementById('server-players-online-' + i);
    if (modal) $(modal).removeClass('modal_show');
    $('body, html').removeClass('hidescroll');
}

window.addEventListener("click", (e) => {
    if (e.target === document.querySelector(".modal_show")) {
        $(".modal_show").removeClass("modal_show");
        $('body, html').removeClass('hidescroll');
    }
});

setInterval(UpdateServers, 30000);
const updateservers = document.getElementById('updateservers');
let isTimerActive = false;
updateservers.addEventListener('click', function () {
    if (isTimerActive) {
        return;
    }
    const originalContent = updateservers.innerHTML;
    updateservers.innerHTML = '<div class="update_element"><svg class="spinner" viewBox="0 0 50 50"><circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle></svg></div>';
    updateservers.disabled = true;
    isTimerActive = true;
    setTimeout(function () {
        updateservers.innerHTML = originalContent;
        updateservers.disabled = false;
        isTimerActive = false;
        update_message = true;
        UpdateServers();
    }, 2000);
});

let copyip = new ClipboardJS('.copybtn3');
copyip.on('success', function (e) {
    noty(get_translate_phrase('_copied'), 'success');
    e.clearSelection();
});

const modeButtons = document.querySelectorAll('.mode');
const serverBlocks = document.querySelectorAll('.server_block');

modeButtons.forEach(button => {
    button.addEventListener('click', function () {
        modeButtons.forEach(btn => {
            btn.classList.remove('chips_active');
        });
        this.classList.add('chips_active');

        const selectedMode = this.getAttribute('data-mode');
        serverBlocks.forEach(block => {
            const blockMode = block.getAttribute('data-mode');
            if (selectedMode === 'Все' || selectedMode === blockMode) {
                $(block).show();
            } else {
                $(block).hide();
            }
        });
    });
});