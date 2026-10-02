! function(a) {
    var o = function(t) {
            var e = {
                    playCount: 0,
                    $rouletteTarget: null,
                    imageCount: null,
                    $images: null,
                    originalStopImageNumber: null,
                    totalWidth: null,
                    leftPosition: 0,
                    leftPositionStartPoint: 0,
                    soundPlayPoint: 95,
                    case_width: 190,
                    maxDistance: null,
                    slowDownStartDistance: 0,
                    isRunUp: !0,
                    isSlowdown: !1,
                    isStop: !1,
                    distance: 0,
                    stopDistance: 0,
                    runUpDistance: 1e4,
                    isIE: -1 < navigator.userAgent.toLowerCase().indexOf("msie")
                },
                n = a.extend({}, {
                    maxPlayCount: null,
                    speed: 14,
                    stopImageNumber: null,
                    rollCount: 0,
                    duration: 10,
                    stopCallback: function() {},
                    startCallback: function() {}
                }, t, e),
                i = function() {
                    var t, e, i;
                    n.isSlowdown || (n.isSlowdown = !0, n.slowDownStartDistance = n.distance, n.maxDistance = n.distance + 4 * n.totalWidth, n.maxDistance += n.itemWidth - n.leftPosition % n.itemWidth, null != n.stopImageNumber && (_rand_distance = -n.case_width / 2 + (t = 30, e = n.case_width - 30, i = t - .5 + Math.random() * (e - t + 1), i = Math.round(i)), n.maxDistance += (n.totalWidth - n.maxDistance % n.totalWidth + n.stopImageNumber * n.itemWidth) % n.totalWidth - n.leftPositionStartPoint + _rand_distance))
                },
                o = function() {
                    var t = n.speed;
                    if (n.isRunUp ? n.distance <= n.runUpDistance ? (t = n.distance / n.runUpDistance * n.speed) < 1 && (t += .5) : n.isRunUp = !1 : n.isSlowdown && (0 == n.stopDistance && (n.stopDistance = n.maxDistance - n.distance), t = (n.maxDistance - n.distance) / n.stopDistance * n.speed + .3), n.maxDistance && n.distance >= n.maxDistance) return n.isStop = !0, n.maxDistance = e.maxDistance, n.slowDownStartDistance = e.slowDownStartDistance, n.distance = e.distance, n.isRunUp = e.isRunUp, n.isSlowdown = e.isSlowdown, n.isStop = e.isStop, n.leftPositionStartPoint = n.leftPosition, n.stopDistance = n.stopDistance, void n.stopCallback(n.$rouletteTarget.find("div.subject-block").eq(n.stopImageNumber));
                    n.distance += t, n.leftPosition += t, n.leftPosition >= n.totalWidth + n.case_width && (n.leftPosition = n.leftPosition - n.totalWidth), n.soundPlayPoint += t, n.soundPlayPoint >= n.itemWidth && (n.soundPlayPoint = n.soundPlayPoint - n.itemWidth, play_case_sound("scroll")), n.isIE ? n.$rouletteTarget.css("left", "-" + n.leftPosition + "px") : n.$rouletteTarget.css("transform", "translateX(-" + n.leftPosition + "px)"), setTimeout(o, 1)
                };
            return {
                start: function() {
                    n.playCount++, n.maxPlayCount && n.playCount > n.maxPlayCount || (n.stopImageNumber = a.isNumeric(e.originalStopImageNumber) && 0 <= Number(e.originalStopImageNumber) ? Number(e.originalStopImageNumber) : Math.floor(Math.random() * n.imageCount), n.startCallback(), o(), setTimeout(function() {
                        i()
                    }, 1e3 * n.duration))
                },
                stop: function(t) {
                    if (!n.isSlowdown) {
                        if (t) {
                            var e = Number(t.stopImageNumber);
                            0 <= e && e <= n.imageCount - 1 && (n.stopImageNumber = t.stopImageNumber)
                        }
                        i()
                    }
                },
                init: function(t) {
                    e.originalStopImageNumber = n.stopImageNumber, n.$images || (n.$images = t.find("div.subject-block").remove(), n.imageCount = n.$images.length, n.itemWidth = n.case_width, n.totalWidth = n.imageCount * n.itemWidth, n.runUpDistance = 2 * n.itemWidth), n.leftPositionStartPoint = n.case_width, n.leftPosition = n.case_width, n.stopDistance = e.stopDistance, n.soundPlayPoint = e.soundPlayPoint, t.find("div").remove(), n.$rouletteTarget = a("<div>").css({
                        transform: "translateX(-" + n.leftPosition + "px)"
                    }).attr("class", "roulette-inner"), t.append(n.$rouletteTarget), n.$rouletteTarget.append(n.$images), n.$rouletteTarget.append(n.$images.clone()), t.show()
                },
                option: function(t) {
                    (n = a.extend(n, t)).speed = Number(n.speed), n.duration = Number(n.duration), n.duration = 1 < n.duration ? n.duration - 1 : 1, e.originalStopImageNumber = t.stopImageNumber
                }
            }
        },
        s = "roulette";
    a.fn[s] = function(i, n) {
        return this.each(function() {
            var t = a(this),
                e = t.data("plugin_" + s);
            e ? e[i] && e[i](n) : ((e = new o(i)).init(t, i), a(this).data("plugin_" + s, e))
        })
    }
}(jQuery), "off" == get_cookie("roulette_sound") ? (1 == $("#sound-point").length && $("#sound-point").addClass("sound-off"), cases_roulette_sound = 2) : (1 == $("#sound-point").length && $("#sound-point").addClass("sound-on"), cases_roulette_sound = 1);