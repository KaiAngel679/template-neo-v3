$(function () {
    const c = document.getElementById("starfield");
    if(!c) return;
    const ctx = c.getContext("2d");
    const dpr = window.devicePixelRatio || 1;

    let w, h;
    let stars = [];
    const COUNT = 200;

    function resize() {
        w = window.innerWidth;
        h = window.innerHeight;

        c.width = w * dpr;
        c.height = h * dpr;
        c.style.width = w + "px";
        c.style.height = h + "px";
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.scale(dpr, dpr);

        stars = [];
        const speedFactor = parseFloat(getComputedStyle(document.documentElement).getPropertyValue("--star-speed")) || 1;
        for (let i = 0; i < COUNT; i++) {
            stars.push({
                x: Math.random() * w,
                y: Math.random() * h,
                size: Math.random() * 1.5 + 0.5,
                speed: (Math.random() * 1.1 + 0.3) * speedFactor
            });
        }
    }

    function draw() {
        ctx.clearRect(0, 0, w, h);

        const color = getComputedStyle(document.documentElement).getPropertyValue("--stars").trim() || "#fff";

        stars.forEach(s => {
            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.arc(s.x, s.y, s.size, 0, Math.PI * 2);
            ctx.fill();

            s.y -= s.speed;
            if (s.y < -s.size) {
                s.y = h + s.size;
                s.x = Math.random() * w;
            }
        });

        requestAnimationFrame(draw);
    }

    $(window).on("resize", resize);
    resize();
    draw();
});