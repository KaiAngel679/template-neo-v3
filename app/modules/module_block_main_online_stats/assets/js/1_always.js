function onlineStats() {
    fetch(domain + "/app/modules/module_block_main_online_stats/includes/js_controller.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({ online_stats: true })
    })
    .then(response => response.json())
    .then(data => {
        const mods = data.server_mods || {};
        for (let mod in mods) {
            const el = document.getElementById(`mod_${mod}`);
            if (el) el.textContent = mods[mod];
        }

        const set = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        };

        set('site_olnine', data.site);
        set('servers_total', data.servers);
    })
}
onlineStats();
setInterval(onlineStats, 15000);