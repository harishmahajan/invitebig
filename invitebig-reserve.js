function RouteReservationRequest(e) {
    typeof e == "undefined" && (e = window.location.href.split("/"), e = e[e.length - 1]);
    var t = "",
        n = window.location.href.split("/");
    for (var r = 0; r < n.length; r++) {
        t += n[r] + "/";
        if (n[r].indexOf("reserve") == 0) {
            t += e;
            break
        }
    }
    e = e.split("#")[0], $("#reservationContainer").html().length > 0 && (typeof window.history.pushState != "undefined" ? window.history.pushState(!0, null, t) : window.location.href = t);
    switch (e) {
        case "addons":
            LoadPartial("/reserve/reserve_addon.html", "reservationContainer", !1);
            break;
        case "food":
            LoadPartial("/reserve/reserve_food.html", "reservationContainer", !1);
            break;
        case "info":
            LoadPartial("/reserve/reserve_info.html", "reservationContainer", !1);
            break;
        case "order":
            LoadPartial("/reserve/reserve_order.html", "reservationContainer", !1);
            break;
        case "personnel":
            LoadPartial("/reserve/reserve_personnel.html", "reservationContainer", !1);
            break;
        default:
            LoadPartial("/reserve/reserve_avail.html", "reservationContainer", !1)
    }
}

function LoadVenueAvailability(e, t, n) {
       // alert("bookTotalsBar");
    $("#reservationContainer").loading(), e.attr("data-availability") && EncodeAvailData();
    var r = {
        method: "fLoadVenueAvailability",
        shorturl: t,
        filter: n
    };
    Post(r).then(function(r) {
        console.log("filter", n, "LoadVenueAvailability:", r);
        if (r["result"] != "success") LoadPartial("/404", null, !1);
        else {
            var i = r.data;
            if (i) {
                (!n || $("#bookTitle").text().length < 1) && PopulateBookVenueData(i), e.attr("data-availability", JSON.stringify(i)), e.attr("data-date", i.date), e.attr("data-timezone", i.timezone), e.attr("data-start", i.start), e.attr("data-stop", i.stop), e.attr("data-buffer", i.buffer), e.attr("data-currency", i.currency), e.attr("data-showGroupSizePopup", i.showGroupSizePopup), CreateAvailWidget(e), RemoveOldAvailData(), PopulateAvailData(), $("div.avail_res div.avail_res_hours").each(function() {
                    if ($(this).find("div:visible").length == 12) {
                        var e = parseInt($(this).parents("div.avail_res").first().attr("data-nextavailability"));
                        if (e && e != -1) {
                            var t = parseInt($("#firstAvailWidget").attr("data-start"));
                            while (t < e) t += 86400;
                            t -= 86400;
                            var n = window.location.href;
                            if (n.indexOf("?") > 0) {
                                var r = n.indexOf("date=");
                                if (r > 0) {
                                    var s = n.indexOf("&", r) + 1;
                                    s < r && (s = n.length), n = n.substr(0, r) + n.substr(s, n.length) + "&date=" + t
                                } else n = n + "&date=" + t
                            } else n += "?date=" + t;
                            e = "UNTIL <a href='" + SanitizeAttr(n) + "'>" + FormatDate(e, "M/DD", i.timezone) + "<i class='fa fa-arrow-right fa'></i></a>"
                        } else e = "";
                        $(this).after("<div class='noAvailability'>NO AVAILABILITY " + (e ? " " + e : "") + "</div>")
                    }
                });
                var s = $("div.avail_res_hours").last();
                $("div.noAvailability").each(function() {
                    $(this).css({
                        left: s.offset().left + (s.width() - $(this).width()) / 2 + "px"
                    })
                }), $("div.bookTotalsBar").last().offset().top > $(window).height() ? $("div.bookTotalsBar").first().show() : $("div.bookTotalsBar").first().hide()
            } else e.append("<h4>Error: failed to load data for " + t + "</h4>")
        }
        if ( $("div").hasClass( "avail_selected" ) ) {
             $('.bookTotalsBar').show();
        }
    else{
         $('.bookTotalsBar').hide();    
        }
        $("#reservationContainer").loadingDone()
    })
}

function PopulateBookVenueData(e) {
    console.log("PopulateBookVenueData", e);
    e.preventDefault();
    var t = String(e.logo);
    t.indexOf("url(") != 0 && (t = "url(" + t + ")"), $("div.bookHeader").css({
        "background-image": t
    }), $("#bookTitle").empty().append(e.name), $("#bookAddress").empty().append(e.address), $("#bookTitle,#bookAddress").attr("href", "/venue/" + e.url), $("#bookShortUrl").val(e.url), $("#bookTimezone").val(e.timezone), $("#venueCurrency").val(e.currency), $("#bookvenueid").val(e.venueid), $("#bookFunctionality").attr("data-functionality", JSON.stringify(e.functionality)), $("#bookContract").val(e.contract);
    if (e.types)
        for (var n = 0; n < e.types.length; n++) $("#bookFilterType").append($("<option></option>", {
            value: e.types[n].id,
            text: e.types[n].name
        }));
    $("#bookFilterType").length > 0 && typeof $("#bookFilterType")[0].sumo != "undefined" ? $("#bookFilterType")[0].sumo.reload() : $("#bookFilterType").SumoSelect({
        okCancelInMulti: !0,
        selectAll: !0
    }), window.location.href.indexOf("/pay") > 0 && $("div.bookHeader").css({
        display: "block"
    });
    if ($("div.bookHeading").html().length == 0 && localStorage.getItem("venueRights")) {
        var r = $.parseJSON(localStorage.getItem("venueRights"));
        for (var n = 0; n < r.length; n++)
            if (r[n]["venueid"] == e["venueid"] && window.location.href.indexOf("/booking") < 0) {
                $("div.bookHeader").css({
                    display: "none"
                }), $("div.bookHeading").append("<h3 style='text-align:center;margin-bottom:0'>Reservation for " + e.name + "</h3>"), e["visibility"] == "private" && window.location.href.indexOf("/pay") < 0 && $("div.bookHeading").append("<h5 style='text-align:center' class='denied'>Public users will be denied access to this page</h5>");
                break
            }
    }
}

function CreateAvailWidget(e) {
    var t = 150,
        n = (e.parents("div.container").first().width() - t - 5) * .92,
        r = Math.floor(n / 12),
        i = 6,
        s = t;
    if (e.attr("data-availability")) {
        e.find("div.availhours").first().empty();
        var o = $.parseJSON(e.attr("data-availability"));
        console.log("data", o), $("div.availwidget li.active").removeClass("active");
        var u = o.date;
        $("#bookFilterDate").val(FormatDate(u, "MMMM D, YYYY", o.timezone));
        if ($("a[data-date=" + u + "]").length < 1) {
            var a = moment.unix(u).subtract(3, "d").format("X");
            $("#page1").empty().append("<span>" + FormatDate(a, "dddd", o.timezone) + "</span>" + FormatDate(a, "MMMM D", o.timezone)).attr("data-date", a), a = moment.unix(u).subtract(2, "d").format("X"), $("#page2").empty().append("<span>" + FormatDate(a, "dddd", o.timezone) + "</span>" + FormatDate(a, "MMMM D", o.timezone)).attr("data-date", a), a = moment.unix(u).subtract(1, "d").format("X"), $("#page3").empty().append("<span>" + FormatDate(a, "dddd", o.timezone) + "</span>" + FormatDate(a, "MMMM D", o.timezone)).attr("data-date", a), a = moment.unix(u).format("X"), $("#page4").empty().append("<span>" + FormatDate(a, "dddd", o.timezone) + "</span>" + FormatDate(a, "MMMM D", o.timezone)).attr("data-date", a), $("#page4").parents("li").first().addClass("active"), a = moment.unix(u).add(1, "d").format("X"), $("#page5").empty().append("<span>" + FormatDate(a, "dddd", o.timezone) + "</span>" + FormatDate(a, "MMMM D", o.timezone)).attr("data-date", a), a = moment.unix(u).add(2, "d").format("X"), $("#page6").empty().append("<span>" + FormatDate(a, "dddd", o.timezone) + "</span>" + FormatDate(a, "MMMM D", o.timezone)).attr("data-date", a), a = moment.unix(u).add(3, "d").format("X"), $("#page7").empty().append("<span>" + FormatDate(a, "dddd", o.timezone) + "</span>" + FormatDate(a, "MMMM D", o.timezone)).attr("data-date", a)
        } else $("a[data-date=" + u + "]").parents("li").first().addClass("active");
        var f = "<div class='avail_hour_labels' style='width:INSERTRESWIDTHHERE'><div class='avail_res_name hoursname' style='cursor:auto;width:INSERTNAMEWIDTHHERE''><div class='avail_hour avail_timezone'>Time Zone (" + moment.tz(new Date(o.start * 1e3), o.timezone).format("z") + ")</div></div><div class='avail_res_hours'>",
            l = 0;
        for (var c = o.start; c < o.stop && c < o.start + 86400; c += 7200) {
            s += r + i;
            var h = FormatTime(c, "ha", o.timezone);
            h = "&nbsp;" + h;
            var p = "";
            n >= 850 && (p = FormatTime(c + 3600, "ha", o.timezone), n > 900 && n < 1e3 ? p = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" + p : n > 1e3 ? p = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" + p : p = "&nbsp;&nbsp;&nbsp;&nbsp;" + p);
            var d = "";
            l % 2 && (d = "color:rgb(144,144,144)"), f += "<div class='avail_hour' style='width:" + r + "px;" + d + "'>" + h + p + "</div>", l++
        }
        s -= i, f += "</div></div><div class='clearfix'></div>", f = f.replace("INSERTRESWIDTHHERE", String(s) + "px"), f = f.replace("INSERTNAMEWIDTHHERE", String(t) + "px"), e.find("div.availhours").first().append(f);
        if (o.books.length > 0) {
            var v = [0],
                m = 0;
            while (m < v.length) {
                var g = [];
                for (var y = 0; y < o.books.length; y++) o.books[y].parents.length > 0 && o["books"][y]["parents"][0] == o["books"][v[m]]["id"] && g.push(y);
                g.sort(function(e, t) {
                    return o.books[e].children.length < 1 && o.books[t].children.length < 1 || o.books[e].children.length > 1 && o.books[t].children.length > 1 ? parseFloat(o.books[t].rate) - parseFloat(o.books[e].rate) : o.books[e].children.length - o.books[t].children.length
                });
                for (var y = 0; y < g.length; y++) v.splice(m + 1 + y, 0, g[y]);
                m++
            }
            for (var y = 0; y < v.length; y++) v[y] = o.books[v[y]].id;
            o.books.sort(function(e, t) {
                var n = v.indexOf(e.id),
                    r = v.indexOf(t.id);
                return n < 0 ? 1 : r < 0 ? -1 : n - r
            })
        }
        for (var y = 0; y < o.books.length; y++) {
            if (o["books"][y]["hide"] == 1) continue;
            var b = "<div class='avail_res' style='width:" + s + "px' data-id='" + SanitizeAttr(o.books[y].id) + "' " + "data-timeslots='" + o.books[y].timeslots + "' " + "data-cleanup='" + SanitizeAttr(o.books[y].cleanup) + "' " + "data-increment='" + SanitizeAttr(o.books[y].increment) + "' " + "data-capacity='" + SanitizeAttr(o.books[y].capacity) + "' " + "data-rate='" + SanitizeAttr(o.books[y].rate) + "' " + "data-cleanupcost='" + SanitizeAttr(o.books[y].cleanupcost) + "' data-min='" + SanitizeAttr(o.books[y].minduration) + "' " + "data-children='" + SanitizeAttr(JSON.stringify(o.books[y].children)) + "' " + "data-lead='" + SanitizeAttr(o.books[y].lead) + "' " + (o.books[y].nextAvailability ? "data-nextavailability='" + SanitizeAttr(o.books[y].nextAvailability) + "'" : "") + "><div class='avail_res_name' style='width:" + t + "px'><span></span></div>" + "<div class='avail_res_hours'>",
                l = 0;
            for (var c = o.start; c < o.stop && c < o.start + 86400; c += 7200) b += "<div class='avail_busy' style='width: " + r + "px;", l % 2 && (b += "background-color:rgb(230,232,233)"), b += "'></div>", l++;
            b += "</div><div class='clearfix'></div></div>", b = $(b), b.data("name", JSON.stringify(o.books[y].name)), b.data("desc", JSON.stringify(o.books[y].description)), b.data("pictures", JSON.stringify(o.books[y].pictures)), b.find("span").text(o.books[y].name), b.find("span").append("<br><div class='avail-capacity'>" + o.books[y].capacity + " <i class='fa fa-user'></i></div>"), e.find("div.availhours").first().append(b), e.find("div.availhours").first().append("<div class='clearfix'></div>");
            for (var c = 0; c < o.books[y].schedule.length; c++) {
                if (o.books[y].schedule[c].start >= o.stop) continue;
                o["books"][y]["schedule"][c]["status"] == "open" && InsertTimeSlot("avail_available", b, o.books[y].schedule[c].start, o.books[y].schedule[c].stop, 0, !0)
            }
        }
        ConsolidateTimeSlots(), RebindTimeSlotClick(), $("div.avail_res_name").off("click").click(function(e) {
            e.preventDefault(), ShowResourcePopup($(this).parents("div.avail_res").first())
        }), e.find("div.bookTotalsBar").css({
            width: e.find("div.availhours").first().width()
        }), e.find("div.filtercontainer").css({
            width: e.find("div.availhours").first().width() + 2
        })
    }
}

function ResizeAvailWidget() {}

function BuildBookingArray() {
    var e = null;
    return e = {
        headcount: $("#groupSize").val().replace(/[^0-9]/g, ""),
        venueid: $("#bookvenueid").val(),
        timezone: $("#bookTimezone").val(),
        currency: $(".availwidget").attr("data-currency"),
        functionality: $.parseJSON($("#bookFunctionality").attr("data-functionality")),
        visibility: $("div.bookHeader:visible").length ? "public" : "private",
        name: $("#bookTitle").text(),
        address: $("#bookAddress").text(),
        phone: $("#bookPhone").text().replace(/[^0-9\.]+/g, ""),
        url: $("#bookShortUrl").val(),
        contract: $("#bookContract").val(),
        resources: [],
        logo: $("div.bookHeader").css("background-image"),
        menus: [],
        personnel: []
    }, ConsolidateAvailData(), $("div.availwidget").each(function() {
        var t = $.parseJSON($(this).attr("data-selections"));
        for (var n = 0; n < t.length; n++) {
            var r = {
                id: t[n].id,
                name: t[n].name,
                timezone: $("#bookTimezone").val(),
                cost: t[n].cost,
                start: t[n].start,
                stop: t[n].stop,
                addons: [],
                deposit_amount: 0,
                full_due: 0,
                refund_policyid: null
            };
            e.resources.push(r)
        }
    }), e.resources.sort(function(e, t) {
        return parseInt(e.start) - parseInt(t.start)
    }), e
}

function InsertBookingNav(e) {
    $("div.bookingNav").parents("div.container").first().remove();
    var t = "/reserve/",
        n = "<div class='container'><div class='bookingNav'><ul class='nav nav-wizard'><li><a data-partial=true href='" + SanitizeAttr(t) + "book-an-event-at-" + e.url + "'>Select Resources</a></li>" + "<li><a data-partial=true href='" + SanitizeAttr(t) + "addons'>Addons</a></li>";
    e["functionality"]["menus"] == 1 && (n += "<li><a data-partial=true href='" + SanitizeAttr(t) + "food'>Food & Drinks</a></li>"), e["functionality"]["personnel"] == 1 && (n += "<li><a data-partial=true href='" + SanitizeAttr(t) + "personnel'>Personnel</a></li>"), n += "<li><a data-partial=true href='" + SanitizeAttr(t) + "info'>Booking Information</a></li>" + "<li><a data-partial=true href='" + SanitizeAttr(t) + "order'>Confirm Reservation</a></li>" + "<li><a data-partial=true href='#'>Payment</a></li></ul></div></div>", $("div.bookingNav").remove(), $("#reservationContainer").prepend($(n)), window.location.pathname.indexOf(e.url) > 0 && $("div.bookingNav li:contains('Resources')").addClass("active"), window.location.pathname.indexOf("/addons") > 0 && $("div.bookingNav li:contains('Addons')").addClass("active"), window.location.pathname.indexOf("/food") > 0 && $("div.bookingNav li:contains('Food')").addClass("active"), window.location.pathname.indexOf("/personnel") > 0 && $("div.bookingNav li:contains('Personnel')").addClass("active"), window.location.pathname.indexOf("/info") > 0 && $("div.bookingNav li:contains('Information')").addClass("active"), window.location.pathname.indexOf("/order") > 0 && $("div.bookingNav li:contains('Confirm')").addClass("active")
}

function RemoveOldAvailData() {
    $("div.availwidget").each(function() {
        var e = [];
        $(this).attr("data-selections") && (e = $.parseJSON($(this).attr("data-selections")));
        var t = new Date,
            n = t.getTime() / 1e3;
        for (var r = 0; r < e.length; r++) e[r].start < n && (e[r].start = n, e[r].cost = -999999999), e[r].stop < n && (e.splice(r, 1), r--)
    })
}

function EncodeAvailData() {
    RemoveOldAvailData(), CalculateAvailCosts(), $("div.availwidget").each(function() {
        var e = [];
        $(this).attr("data-selections") && (e = $.parseJSON($(this).attr("data-selections")));
        var t = parseInt($("#firstAvailWidget").attr("data-start")),
            n = parseInt($("#firstAvailWidget").attr("data-stop")),
            r = parseInt($("#firstAvailWidget").attr("data-buffer"));
        for (var i = 0; i < e.length; i++)
            if (e[i].start < n && e[i].stop > t) {
                e.splice(i, 1), i--;
                continue
            }
        $(this).find("div.avail_selected").each(function() {
            var t = {
                id: parseInt($(this).parents("div.avail_res").first().attr("data-id")),
                name: $.parseJSON($(this).parents("div.avail_res").first().data("name")),
                start: parseInt($(this).attr("data-start")),
                stop: parseInt($(this).attr("data-stop")),
                cost: parseFloat($(this).attr("data-cost"))
            };
            e.push(t)
        }), $(this).attr("data-selections", JSON.stringify(e)), $.parseJSON($(this).attr("data-selections")).length > 0 && $("button.reservationMakeRes").css({
            display: "inline"
        })
    }), ConsolidateAvailData(), ShowAvailCosts()
}

function ConsolidateAvailData() {
    $("div.availwidget").each(function() {
        var e = [];
        $(this).attr("data-selections") && (e = $.parseJSON($(this).attr("data-selections")));
        var t = !0;
        while (t == 1) {
            t = !1;
            for (var n = 0; n < e.length; n++)
                for (var r = 0; r < e.length; r++) {
                    if (r == n) continue;
                    if (e[r]["id"] != e[n]["id"]) continue;
                    if (e[r]["start"] == e[n]["stop"]) {
                        e[n].stop = e[r].stop, e[n].cost += e[r].cost, console.log("i", e[n].start + " " + e[n].stop), e.splice(r, 1), r < n && n--, r--, t = !0;
                        continue
                    }
                    if (e[r]["start"] == e[n]["start"]) {
                        e[r].stop > e[n].stop && (e[n].stop = e[r].stop, e[n].cost = -999999999), e.splice(r, 1), r < n && n--, r--, t = !0;
                        continue
                    }
                    if (e[r]["stop"] == e[n]["stop"]) {
                        e[r].start < e[n].start && (e[n].start = e[r].start, e[n].cost = -999999999), e.splice(r, 1), r < n && n--, r--, t = !0;
                        continue
                    }
                }
        }
        $(this).attr("data-selections", JSON.stringify(e))
    })
}

function PaginationSelect() {
    $("div.availwidget").each(function() {
        $(this).find(".paginationSelected").remove();
        var e = [];
        $(this).attr("data-selections") && (e = $.parseJSON($(this).attr("data-selections")));
        for (var t = 0; t < e.length; t++) $(this).find("a[name='paginationGroup']").each(function() {
            var n = parseInt($(this).attr("data-date")),
                r = n + 86400;
            $(this).find(".paginationSelected").length == 0 && n < e[t].stop && r > e[t].start && $(this).append("<div class='paginationSelected'><i class='fa fa-circle'></i></div>")
        })
    })
}

function PopulateAvailData() {
    $("div.availwidget").each(function() {
        var e = [];
        $(this).attr("data-selections") && (e = $.parseJSON($(this).attr("data-selections"))), e.length > 0 && $("button.reservationMakeRes").css({
            display: "inline"
        });
        var t = parseInt($("#firstAvailWidget").attr("data-start")),
            n = parseInt($("#firstAvailWidget").attr("data-stop")),
            r = 0;
        for (var i = 0; i < e.length; i++) {
            r += parseFloat(e[i].cost);
            if (e[i].start < n && e[i].stop > t) {
                var s = e[i].start,
                    o = e[i].stop,
                    u = !1,
                    a = $("div.avail_res[data-id=" + e[i].id + "]").first();
                a.find("div.avail_available").each(function() {
                    parseInt($(this).attr("data-start")) <= o && parseInt($(this).attr("data-stop")) >= s && (u = !0)
                }), u == 1 && InsertTimeSlot("avail_selected", a, s, o, e[i].cost, !0)
            }
        }
        ConsolidateTimeSlots(), RedrawTimeSlots(), ShowAvailCosts()
    }), PaginationSelect()
}

function CheckMinTime() {
    EncodeAvailData(), ConsolidateAvailData();
    var e = "";
    return $("div.availwidget").each(function() {
        var t = [];
        $(this).attr("data-selections") && (t = $.parseJSON($(this).attr("data-selections")));
        for (var n = 0; n < t.length; n++)
            if (t[n]["timeslots"] == 0 && t[n].stop - t[n].start < parseInt($(this).find("div.avail_res[data-id=" + t[n].id + "]").attr("data-min")) * 60) {
                e = $.parseJSON($(this).find("div.avail_res[data-id=" + t[n].id + "]").data("name")) + " has a minimum reservation time of " + $(this).find("div.avail_res[data-id=" + t[n].id + "]").attr("data-min") + " minutes.  Please add more time before or after the selected timeslot of " + FormatDate(parseInt(t[n].start), "MMMM D, YYYY h:mma z") + " - " + FormatDate(parseInt(t[n].stop), "MMMM D, YYYY h:mma z");
                break
            }
    }), e
}

function ShowAvailCosts() {
    $("div.availwidget").each(function() {
        var e = [];
        $(this).attr("data-selections") && (e = $.parseJSON($(this).attr("data-selections")));
        var t = 0;
        for (var n = 0; n < e.length; n++) t += parseFloat(e[n].cost);
        t < 0 ? $("span.reservationTotal").empty().append("Proceed to see new price") : $("span.reservationTotal").empty().append("TOTAL: " + FormatDollars(t))
    })
}

function CalculateAvailCosts() {
    $("div.availwidget").each(function() {
        var e = $.parseJSON($(this).attr("data-availability")),
            t = 0;
        $(this).find("div.avail_res").each(function() {
            var t = 0,
                n = [],
                r = $(this).attr("data-timeslots"),
                i = $(this).attr("data-id");
            for (var s = 0; s < e.books.length; s++) e["books"][s]["id"] == i && (t = parseFloat(e.books[s].rate), n = e.books[s].rates);
            r == 1 && ($(this).find(".avail_available").each(function() {
                for (var e = 0; e < n.length; e++) $(this).find(".avail_text").empty().append("Available - " + FormatDollars(n[e].rate))
            }), $(this).find(".avail_selected").each(function() {
                for (var e = 0; e < n.length; e++) $(this).find(".avail_text").empty().append("Selected - " + FormatDollars(n[e].rate))
            })), $(this).find("div.avail_selected").each(function() {
                var e = 0,
                    i = [{
                        start: parseInt($(this).attr("data-start")),
                        stop: parseInt($(this).attr("data-stop")),
                        rate: -1
                    }];
                if (r == 0) {
                    for (var s = 0; s < i.length; s++) {
                        var o = !1;
                        for (var u = 0; u < n.length; u++)
                            if (n[u].start < i[s].stop && n[u].stop > i[s].start && n[u].rate > i[s].rate) {
                                var a = n[u].start,
                                    f = n[u].stop;
                                a < i[s].start && (a = i[s].start), f > i[s].stop && (f = i[s].stop), a == i[s]["start"] && f == i[s]["stop"] ? i[s].rate = n[u].rate : a == i[s]["start"] && f < i[s].stop ? (i.push({
                                    start: a,
                                    stop: f,
                                    rate: n[u].rate
                                }), i[s].start = f) : f == i[s]["stop"] && a > i[s].start ? (i.push({
                                    start: a,
                                    stop: i[s].stop,
                                    rate: n[u].rate
                                }), i[s].stop = a) : f < i[s].stop && a > i[s].start && (i.push({
                                    start: a,
                                    stop: f,
                                    rate: n[u].rate
                                }), i.push({
                                    start: f,
                                    stop: i[s].stop,
                                    rate: i[s].rate
                                }), i[s].stop = a), o = !0
                            }
                        o && (s = -1)
                    }
                    for (var s = 0; s < i.length; s++) i[s]["rate"] == -1 && (i[s].rate = t), e += parseFloat(i[s].rate) * ((i[s].stop - i[s].start) / 3600)
                } else
                    for (var u = 0; u < n.length; u++) n[u].start < i[0].stop && n[u].stop > i[0].start && (e += parseFloat(n[u].rate));
                $(this).attr("data-cost", e)
            })
        })
    })
}

function FormatTimeSlot(e) {
    var t = 0,
        n = 0,
        r = parseInt($("#firstAvailWidget").attr("data-start")),
        i = e.parents("div.avail_res_hours").first().find("div.avail_busy").first().outerWidth(),
        s = parseInt(e.parents("div.avail_res_hours").first().find("div.avail_busy").first().css("margin-right")),
        o = parseInt(e.attr("data-start")),
        u = parseInt(e.attr("data-stop"));
    e.find("i.glyphicon-chevron-right").remove(), u > parseInt($("#firstAvailWidget").attr("data-stop")) && e.append("<i class='glyphicon glyphicon-chevron-right pull-right avail_chevron'></i>"), o < r && (o = r), u > r + 86400 && (u = r + 86400);
    if (o >= r + 86400 || u <= r) {
        e.hide();
        return
    }
    e.show();
    var a = (u - o) / 7200,
        f = (o - r) / 7200,
        l = (u - r) / 7200,
        c = 0;
    for (var h = r; h < u; h += 7200) h > o && c++;
    var p = 0;
    for (var h = r + 7200; h <= o; h += 7200) h <= o && p++;
    t = a * i, t += s * c, l - Math.floor(l) > 0 && (t -= 1), n = f * i, n += s * p, e.css({
        width: String(t) + "px",
        "margin-left": String(n) + "px"
    });
    var d = FormatTime(parseInt(e.attr("data-start")), null, e.parents("div.availwidget").first().attr("data-timezone")),
        v = FormatTime(parseInt(e.attr("data-stop")), null, e.parents("div.availwidget").first().attr("data-timezone"));
    e.find("div.avail_time_string").empty().append(d + " - " + v)
}

function RedrawTimeSlots() {
    var e = $.parseJSON($("#firstAvailWidget").attr("data-availability"));
    $("div.avail_res").each(function() {
        var t = $(this),
            n = [];
        t.find("div.avail_selected").each(function() {
            n.push({
                clss: $(this).attr("class"),
                start: $(this).attr("data-start"),
                stop: $(this).attr("data-stop"),
                cost: $(this).attr("data-cost")
            }), $(this).remove()
        }), t.find("div.avail_available,div.avail_parent_selected").each(function() {
            $(this).remove()
        });
        for (var r = 0; r < e.books.length; r++) {
            if (e["books"][r]["id"] != t.attr("data-id")) continue;
            for (var i = 0; i < e.books[r].schedule.length; i++) {
                if (e.books[r].schedule[i].start >= e.stop) continue;
                e["books"][r]["schedule"][i]["status"] == "open" && InsertTimeSlot("avail_available", t, e.books[r].schedule[i].start, e.books[r].schedule[i].stop, 0, !0)
            }
        }
        for (var r = 0; r < n.length; r++) InsertTimeSlot(n[r].clss, t, n[r].start, n[r].stop, n[r].cost, !0);
        ConsolidateTimeSlots()
    }), $("div.avail_res").each(function() {
        var e = $(this);
        e.find("div.avail_selected").each(function() {
            var t = $(this),
                n = parseInt(t.attr("data-start")),
                r = parseInt(t.attr("data-stop")),
                i = $.parseJSON(e.attr("data-children"));
            for (var s = 0; s < i.length; s++) {
                var o = $("div.avail_res[data-id=" + i[s] + "]").first();
                o.length > 0 && InsertTimeSlot("avail_parent_selected", o, n, r, 0)
            }
        })
    }), CalculateAvailCosts()
}

function InsertTimeSlot(e, t, n, r, i, s) {
    var o = "Available";
    e.indexOf("avail_parent_selected") >= 0 && (o = "Included"), e.indexOf("avail_selected") >= 0 && (o = "Selected");
    var u = t.find("div.avail_busy").first(),
        a = $("<div class='" + e + "' data-start='" + SanitizeAttr(n) + "' data-stop='" + SanitizeAttr(r) + "' data-cost='" + SanitizeAttr(String(i)) + "'><div class='avail_text'>" + o + "</div><div class='avail_time_string'></div></div>");
    u.before(a), FormatTimeSlot(a), RebindTimeSlotClick(), s != 1 && ConsolidateTimeSlots()
}

function ConsolidateTimeSlots() {
    var e = !0,
        t = !1;
    while (e) e = !1, $("div.avail_res").each(function() {
        var n = $(this),
            r = !0;
        if (n.attr("data-timeslots") == "0") {
            r = !0;
            while (r) r = !1, n.find("div.avail_available").each(function() {
                var t = $(this),
                    i = parseInt(t.attr("data-start")),
                    s = parseInt(t.attr("data-stop"));
                n.find("div.avail_available").each(function() {
                    if ($(this).is(t)) return;
                    var n = $(this),
                        o = parseInt(n.attr("data-start")),
                        u = parseInt(n.attr("data-stop"));
                    if (o <= s && u > i) return t.attr("data-start", i < o ? i : o), t.attr("data-stop", s > u ? s : u), FormatTimeSlot(t), r = !0, e = !0, n.remove(), !1
                });
                if (r) return !1
            });
            r = !0;
            while (r) r = !1, n.find("div.avail_selected").each(function() {
                var t = $(this),
                    i = parseInt(t.attr("data-start")),
                    s = parseInt(t.attr("data-stop")),
                    o = parseInt(t.attr("data-cost"));
                n.find("div.avail_selected").each(function() {
                    if ($(this).is(t)) return;
                    var n = $(this),
                        u = parseInt(n.attr("data-start")),
                        a = parseInt(n.attr("data-stop")),
                        f = parseInt(n.attr("data-cost"));
                    if (u <= s && a > i) return t.attr("data-start", i < u ? i : u), t.attr("data-stop", s > a ? s : a), t.attr("data-cost", o + f), FormatTimeSlot(t), r = !0, e = !0, n.remove(), !1
                });
                if (r) return !1
            })
        }
        n.find("div.avail_selected,div.avail_parent_selected").each(function() {
            var t = $(this),
                r = parseInt(t.attr("data-start")),
                i = parseInt(t.attr("data-stop"));
            n.find("div.avail_available").each(function() {
                if ($(this).is(t)) return;
                var s = $(this),
                    o = parseInt(s.attr("data-start")),
                    u = parseInt(s.attr("data-stop"));
                i > o && r < u && (n.attr("data-timeslots") == "1" ? s.remove() : o >= r && u <= i ? s.remove() : o < r && u > r ? (s.attr("data-stop", r), FormatTimeSlot(s), u > i && InsertTimeSlot("avail_available", n, i, u, 0, !0)) : o < i && u > i && (s.attr("data-start", i), FormatTimeSlot(s), o < r && InsertTimeSlot("avail_available", n, o, r, 0, !0)), e = !0)
            })
        }), n.find("div.avail_parent_selected").each(function() {
            var r = $(this),
                i = parseInt(r.attr("data-start")),
                s = parseInt(r.attr("data-stop"));
            n.find("div.avail_selected").each(function() {
                if ($(this).is(r)) return;
                var n = $(this),
                    o = parseInt(n.attr("data-start")),
                    u = parseInt(n.attr("data-stop"));
                s > o && i < u && (n.remove(), t = !0, e = !0)
            })
        })
    });
    t && RedrawTimeSlots()
}

function RebindTimeSlotClick() {
    $("div.avail_available").off("click").click(function(e) {
        $(this).off("click");
        var t = $(this);
        $("#mainOverlay").css({
            display: "inline"
        }), $("#mainOverlay").off("click"), t.css({
            "z-index": "1001"
        });
        var n = e.pageX - 60;
        n < 0 && (n = 0), n + 330 > $(document).width() && (n = $(document).width() - 330), e.pageY + 200 > self.pageYOffset + $(window).height() && $("html, body").animate({
            scrollTop: e.pageY + 200 - $(window).height()
        });
        var r = t.parents("div.avail_res").first(),
            i = parseInt(r.attr("data-min")),
            s = parseInt(r.attr("data-increment")) * 60,
            o = $.parseJSON(r.data("name")),
            u = i * 60 > s ? FormatInterval(i * 60) : FormatInterval(s),
            a = "<div class='avail_popup arrow-up' style='left:" + n + "px'><div class='avail_popup_name text-center'>" + o + "</div><div id='popupError' class='avail_popup_error'>The minimum reservation time for this resource is " + u + "</div><b>From</b> <select id='popupFrom'></select>" + "&nbsp;&nbsp;<b>To</b> <select id='popupTo'></select><br><div class='clearfix' style='padding:4px'></div>" + "<div class='flex-between-center'>" + "<button id='popupCancel' class='btn btn-md btn-default'>CANCEL</button>" + "<button id='popupDelete' class='btn btn-md btn-danger' style='display:none;'>UNSELECT</button>" + "<button id='popupApply' class='btn btn-md btn-success'>APPLY</button></div>" + "</div>";
        t.before(a);
        var f = parseInt(t.attr("data-start")),
            l = parseInt(t.attr("data-stop"));
        r.attr("data-timeslots") == 1 && (s = l - f);
        var c = 0;
        r.find("div.avail_selected[data-start=" + l + "]").length > 0 && (c = parseInt(r.find("div.avail_selected[data-start=" + l + "]").first().attr("data-start")));
        var h = 0;
        r.find("div.avail_selected[data-stop=" + f + "]").length > 0 && (h = parseInt(r.find("div.avail_selected[data-stop=" + f + "]").first().attr("data-stop"))), console.log("next", c, "prev", h);
        for (var p = f; p <= l; p += s) {
            var d = FormatTime(p, null, $(this).parents("div.availwidget").first().attr("data-timezone")) + " " + FormatDate(p, "MM/DD", $(this).parents("div.availwidget").first().attr("data-timezone"));
            p < l && (p <= l - i * 60 || l - f < i * 60) && $("#popupFrom").append("<option value='" + SanitizeAttr(p) + "'>" + d + "</option>"), p > f && (p >= f + i * 60 || l - f < i * 60) && $("#popupTo").append("<option value='" + SanitizeAttr(p) + "' selected>" + d + "</option>")
        }
        $("#popupFrom").SumoSelect(), $("#popupTo").SumoSelect(), $("#popupFrom").change(function(e) {
            var t = parseInt($(this).val());
            parseInt($("#popupTo option:selected").val()) <= t && $("#popupTo option").filter(function() {
                return parseInt($(this).attr("value")) > t
            }).first().prop("selected", "selected"), $("#popupTo option").each(function() {
                parseInt($(this).val()) <= t ? $(this).prop("disabled", !0) : $(this).prop("disabled", !1)
            }), r.attr("data-timeslots") == 0 && (parseInt($("#popupTo option:selected").val()) - t < i * 60 && parseInt($("#popupTo option:selected").val()) != c && parseInt($("#popupFrom option:selected").val()) != h || $("#popupTo option").length < 1) ? ($("#popupError").css({
                display: "block"
            }), $("#popupApply").prop("disabled", !0)) : ($("#popupError").css({
                display: "none"
            }), $("#popupApply").prop("disabled", !1)), $(this)[0].sumo.reload()
        }), $("#popupTo").change(function(e) {
            r.attr("data-timeslots") == 0 && parseInt($("#popupTo option:selected").val()) - parseInt($("#popupFrom option:selected").val()) < i * 60 && parseInt($("#popupTo option:selected").val()) != c && parseInt($("#popupFrom option:selected").val()) != h ? ($("#popupError").css({
                display: "block"
            }), $("#popupApply").prop("disabled", !0)) : ($("#popupError").css({
                display: "none"
            }), $("#popupApply").prop("disabled", !1))
        }), $("#popupCancel").off("click").click(function(e) {
            e.preventDefault(), $("div.avail_popup").remove(), t.css({
                "z-index": "1"
            }), $("#mainOverlay").css({
                display: "none"
            }), RebindTimeSlotClick()
        }), $("#popupApply").off("click").click(function(e) {
            $(".bookTotalsBar").css("display", "block");
            e.preventDefault();
            var n = parseInt($("#popupFrom option:selected").val()),
                s = parseInt($("#popupTo option:selected").val());
            if (r.attr("data-timeslots") == 0 && s - n < i * 60 && parseInt($("#popupTo option:selected").val()) != c && parseInt($("#popupFrom option:selected").val()) != h) {
                $("#popupError").css({
                    display: "block"
                });
                return
            }
            $("#popupError").css({
                display: "none"
            }), InsertTimeSlot("avail_selected", t.parents("div.avail_res").first(), n, s, 0), RedrawTimeSlots(), $("div.avail_popup").remove(), t.css({
                "z-index": "1"
            }), $("#mainOverlay").css({
                display: "none"
            }), EncodeAvailData()
        }), $("#popupFrom").trigger("change")
    }), $("div.avail_selected").off("click").click(function(e) {
        $(this).off("click");
        var t = $(this);
        $("#mainOverlay").css({
            display: "inline"
        }), $("#mainOverlay").off("click"), t.css({
            "z-index": "1001"
        });
        var n = e.pageX - 60;
        n < 0 && (n = 0), n + 330 > $(document).innerWidth() && (n = $(document).innerWidth() - 330), e.pageY + 200 > self.pageYOffset + $(window).height() && $("html, body").animate({
            scrollTop: e.pageY + 200 - $(window).height()
        });
        var r = t.parents("div.avail_res").first(),
            i = parseInt(r.attr("data-min")),
            s = $.parseJSON(r.data("name")),
            o = parseInt(r.attr("data-increment")) * 60,
            u = i * 60 > o ? FormatInterval(i * 60) : FormatInterval(o),
            a = parseInt(t.attr("data-start")),
            f = parseInt(t.attr("data-stop")),
            l = a,
            c = f,
            h = t.parents("div.avail_res").first().find("div.avail_available[data-stop=" + a + "]"),
            p = t.parents("div.avail_res").first().find("div.avail_available[data-start=" + f + "]");
        h.length > 0 && h.hasClass("avail_available") && parseInt(h.attr("data-stop")) >= l && (l = parseInt(h.attr("data-start"))), p.length > 0 && p.hasClass("avail_available") && parseInt(p.attr("data-start")) <= c && (c = parseInt(p.attr("data-stop")));
        var d = "<div class='avail_popup arrow-up' style='left:" + n + "px'><div class='avail_popup_name text-center'>" + s + "</div><div id='popupError' class='avail_popup_error'>The minimum reservation time for this resource is " + u + "</div><b>From</b> <select id='popupFrom' class='form-control'></select>" + " <b>To</b> <select id='popupTo' class='form-control'></select><br><div class='clearfix' style='padding:4px'></div>" + "<div class='flex-between-center'>" + "<button id='popupCancel' class='btn btn-md btn-default'>CANCEL</button>" + "<button id='popupDelete' class='btn btn-md btn-danger'>UNSELECT</button>" + "<button id='popupApply' class='btn btn-md btn-success'>APPLY</button></div>" + "</div>";
        t.before(d), r.attr("data-timeslots") == 1 && (o = c - l);
        for (var v = l; v <= c; v += o) {
            var m = FormatTime(v, null, $(this).parents("div.availwidget").first().attr("data-timezone")) + " " + FormatDate(v, "MM/DD", $(this).parents("div.availwidget").first().attr("data-timezone"));
            v < c && (v <= c - i * 60 || c - l < i * 60) && $("#popupFrom").append("<option value='" + SanitizeAttr(v) + "'>" + m + "</option>"), v > l && (v >= l + i * 60 || c - l < i * 60) && $("#popupTo").append("<option value='" + SanitizeAttr(v) + "' selected>" + m + "</option>")
        }
        $("#popupFrom option[value=" + a + "]").prop("selected", "selected"), $("#popupTo option[value=" + f + "]").prop("selected", "selected"), $("#popupFrom").SumoSelect(), $("#popupTo").SumoSelect(), $("#popupFrom").trigger("change"), $("#popupFrom").change(function(e) {
            var t = parseInt($(this).val());
            parseInt($("#popupTo option:selected").val()) <= t && $("#popupTo option").filter(function() {
                return parseInt($(this).attr("value")) > t
            }).first().prop("selected", "selected"), $("#popupTo option").each(function() {
                parseInt($(this).val()) <= t ? $(this).prop("disabled", !0) : $(this).prop("disabled", !1)
            }), r.attr("data-timeslots") == 0 && parseInt($("#popupTo option:selected").val()) - t < i * 60 || $("#popupTo option").length < 1 ? ($("#popupError").css({
                display: "block"
            }), $("#popupApply").prop("disabled", !0)) : ($("#popupError").css({
                display: "none"
            }), $("#popupApply").prop("disabled", !1)), $(this)[0].sumo.reload()
        }), $("#popupTo").change(function(e) {
            parseInt($("#popupTo option:selected").val()) - parseInt($("#popupFrom option:selected").val()) < i * 60 ? ($("#popupError").css({
                display: "block"
            }), $("#popupApply").prop("disabled", !0)) : ($("#popupError").css({
                display: "none"
            }), $("#popupApply").prop("disabled", !1))
        }), $("#popupCancel").off("click").click(function(e) {
            e.preventDefault(), $("div.avail_popup").remove(), t.css({
                "z-index": "11"
            }), $("#mainOverlay").css({
                display: "none"
            }), RebindTimeSlotClick()
        }), $("#popupDelete").off("click").click(function(e) {
            //$(".bookTotalsBar").css("display", "none");
            e.preventDefault(), $("div.avail_popup").remove();
            var n = t.parents("div.avail_res").first();
            t.remove(), RedrawTimeSlots(n), $("#mainOverlay").css({
                display: "none"
            }), EncodeAvailData(), $("div.availwidget").first().attr("data-selections") == "[]" && ($("span.reservationTotal").empty(), $("button.reservationMakeRes").hide())
             $(".bookTotalsBar").css("display", "none");
        }), $("#popupApply").off("click").click(function(e) {
            e.preventDefault();
            var n = parseInt($("#popupFrom option:selected").val()),
                r = parseInt($("#popupTo option:selected").val());
            if (r - n < i * 60) {
                $("#popupError").css({
                    display: "block"
                });
                return
            }
            $("#popupError").css({
                display: "none"
            });
            var s = t.parents("div.avail_res").first();
            t.remove(), InsertTimeSlot("avail_selected", s, n, r, 0), RedrawTimeSlots(s), $("div.avail_popup").remove(), t.css({
                "z-index": "11"
            }), $("#mainOverlay").css({
                display: "none"
            }), EncodeAvailData()
        })
    })
}

function ShowResourcePopup(e) {
    $("#mainOverlay").css({
        display: "inline"
    });
    var t = "",
        n = "",
        r = "",
        i = "0.00",
        s = "0",
        o = "15",
        u = "15",
        a = "",
        f = 0;
    e.data("name") && (t = $.parseJSON(e.data("name"))), e.data("desc") && (n = $.parseJSON(e.data("desc"))), e.attr("data-rate") && (i = FormatDollars(parseFloat(e.attr("data-rate")))), e.attr("data-capacity") && (s = parseInt(e.attr("data-capacity"))), e.attr("data-min") && (o = parseInt(e.attr("data-min")) * 60), e.attr("data-increment") && (u = e.attr("data-increment")), e.attr("data-lead") && (a = parseInt(e.attr("data-lead")) * 60), e.attr("data-timeslots") && (f = e.attr("data-timeslots")), f == 0 && (i += "/hr"), i += "+";
    var l = ($(window).width() - 350) / 2 + 15;
    l < 0 && (l = 0);
    var c = "<div class='resource_popup' style='top:" + String(window.pageYOffset + 15) + "px;left:0'><div class='resource_popup_name'>" + t + "</div><div class='clearfix'></div>" + "<div id='carousel-resource' style='padding-bottom:66%;margin-bottom:10px'></div>" + "<div class='clearfix'></div><div class='resource_popup_details'><b>Capacity:</b> " + s + "<br><b>Price:</b> " + i + "<br><b>Minimum Reservation Time:</b> " + FormatInterval(o) + "<br>" + (f == 0 ? "<b>Reservation Time Increment:</b> " + FormatInterval(u) + " minutes<br>" : "") + "<b>Lead Time Requirement:</b> " + FormatInterval(a) + "</div>" + "<div class='clearfix'></div><div class='resource_popup_desc'>" + FormatDescription(n) + "</div><div class='clearfix'></div>" + "<button id='popupOk' class='btn btn-md btn-default' style='display:inline;margin:5px;width:100px;float:right'>Close</button><div class='clearfix'></div></div>";
    e.before(c);
    if (e.data("pictures") && e.data("pictures").length > 2) {
        var h = $.parseJSON(e.data("pictures"));
        for (var p = 0; p < h.length; p++) {
            var r = $("<div><img src='" + SanitizeAttr(h[p].url) + "'/></div>");
            r.find("img").attr("alt", h[p].caption), $("#carousel-resource").append(r)
        }
    }
    $("#carousel-resource").slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: !0,
        fade: !0,
        dots: !0,
        infinite: !0,
        speed: 500
    });
    if ($("div.resource_popup img").length == 0) {
        var l = ($(window).width() - $("div.resource_popup").outerWidth()) / 2 + 15;
        l < 0 && (l = 0), $("div.resource_popup").css({
            left: l + "px"
        })
    }
    $("div.resource_popup img").load(function() {
        var e = ($(window).width() - $("div.resource_popup").outerWidth()) / 2 + 15;
        e < 0 && (e = 0), $("div.resource_popup").css({
            left: e + "px"
        })
    }), $("#mainOverlay").off("click").click(function(e) {
        e.preventDefault(), $("div.resource_popup").remove(), $("#mainOverlay").css({
            display: "none"
        })
    }), $("#popupOk").off("click").click(function(e) {
        e.preventDefault(), $("div.resource_popup").remove(), $("#mainOverlay").css({
            display: "none"
        })
    })
}

function UpdateBookingArrayMenus(e) {
    e.menus = [], $(".reservationMenuItem").each(function() {
        var t = parseInt($(this).find("input.reservationMenuItemQuantity").first().val());
        if (t > 0) {
            var n = {
                    id: $(this).attr("data-id"),
                    quantity: t,
                    price: $(this).find(".reservationMenuItemPrice").first().text().replace(/[^0-9\.]+/g, "")
                },
                r = !1;
            for (var i = 0; i < e.menus.length; i++) $(this).attr("data-menuid") == e["menus"][i]["id"] && (e.menus[i].items.push(n), r = !0);
            if (!r) {
                var s = {
                    id: $(this).attr("data-menuid"),
                    deliverat: null,
                    items: [],
                    deposit_amount: 0,
                    full_due: 0,
                    refund_policyid: null
                };
                s.items.push(n), e.menus.push(s)
            }
        }
    })
}

function UpdateBookingArrayPersonnel(e) {
    e.personnel = [], $(".reservationperson").each(function() {
        var t = parseInt($(this).find("input.reservationPersonnelQuantity").first().val()),
            n = {
                id: $(this).attr("data-id"),
                deposit_amount: 0,
                full_due: 0,
                refund_policyid: null,
                quantity: t,
                price: $(this).find("div.reservationPersonnelPrice").first().text().replace(/[^0-9\.]+/g, "")
            };
        n.quantity > 0 && e.personnel.push(n)
    })
}

function UpdateBookingArrayinfo(e) {
    var t = {
        name: $("#reservationEventName").val(),
        description: $("#reservationDescription").val(),
        headcount: $("#reservationGroupSize").val(),
        comments: $("#reservationComments").val(),
        contact_name: $("#reservationContactName").val(),
        contact_company: $("#reservationContactCompany").val(),
        contact_title: $("#reservationContactTitle").val(),
        contact_phone: $("#reservationContactPhone").val(),
        contact_email: $("#reservationContactEmail").val(),
        contact_website: $("#reservationContactWebsite").val()
    };
    e.info = t, e.questions = [], $("div.question").each(function() {
        var t = {
            id: $(this).attr("data-id"),
            type: $(this).attr("data-type"),
            answer: ""
        };
        switch ($(this).attr("data-type")) {
            case "text":
                t.answer = $(this).find("textarea:first").val();
                break;
            case "check":
                t.answer = $(this).find("input[type='checkbox']:first").prop("checked") == 1 ? "checked" : "";
                break;
            case "radio":
                $(this).find("input[type='radio']:checked:first").length > 0 && (t.answer = $(this).find("input[type='radio']:checked:first").val());
                break;
            case "select":
                $(this).find("option:selected").length > 0 && (t.answer = $(this).find("option:selected").val())
        }
        e.questions.push(t)
    })
}

function CalculateReservationFoodSubTotal() {
    var e = 0;
    $(".reservationMenuItem").each(function() {
        var t = parseInt($(this).find("input.reservationMenuItemQuantity").first().val().replace(/[^0-9\.]+/g, "")),
            n = parseFloat($(this).find(".reservationMenuItemPrice").first().text().replace(/[^0-9\.]+/g, ""));
        t > 0 && n > 0 && (e += t * n), t > 0 && $(this).parents("div.reservationmenu").first().find("select.reservationMenuDeliverAt").parent("div").css({
            display: "inline-block"
        })
    }), $("div.reservationSubTotal").empty().append("Subtotal: " + FormatDollars(e)), $("div.reservationSubTotal").css("display", "block")
}

function ReBindReservationFoodControls() {
    $("input.reservationMenuItemQuantity").keyup(function(e) {
        var t = parseInt($(this).parents(".reservationMenuItem").first().attr("data-min")),
            n = parseInt($(this).parents(".reservationMenuItem").first().attr("data-max")),
            r = parseInt($(this).val());
        r < t || r > n ? $(this).css({
            "background-color": "#f2dede"
        }) : $(this).css({
            "background-color": ""
        }), CalculateReservationFoodSubTotal()
    }), CalculateReservationFoodSubTotal()
}

function AddReservationMenus(e, t) {
    for (var n = 0; n < e.length; n++)
        for (var r = 0; r < t.menus.length; r++)
            if (e[n]["id"] == t["menus"][r]["id"])
                for (var i = 0; i < e[n].items.length; i++)
                    for (var s = 0; s < t.menus[r].items.length; s++) e[n]["items"][i]["id"] == t["menus"][r]["items"][s]["id"] && e[n]["items"][i]["quantity"] == t["menus"][r]["items"][s]["quantity"];
    for (var n = 0; n < e.length; n++) {
        var o = "<legend class='menuHeader'><div class='menuName'>" + e[n].name + "</div><div class='menuDescription'>" + FormatDescription(e[n].description) + "</div></legend><table class='table table-striped table-condensed ptable'><thead><tr><th>Menu Item</th><th>Price</th><th>Order</th></tr></thead><tbody>";
        for (var r = 0; r < e[n].items.length; r++) {
            o += "<tr class='reservationMenuItem' data-menuid='" + SanitizeAttr(e[n].id) + "' data-id='" + SanitizeAttr(e[n].items[r].id) + "' data-min=' " + e[n].items[r].min + "' data-max=' " + e[n].items[r].max + "'><td>";
            if (e[n].items[r].picture.length > 0) {
                var u = $("<img class='profileTablePic' src='" + SanitizeAttr(e[n].items[r].picture) + "'/>");
                u.attr("alt", e[n].items[r].caption), o += u.get(0).outerHTML
            }
            o += "<div class='media-body'><h5 class='reservationMenuItemTitle'>" + e[n].items[r].name + "</h5><p>" + FormatDescription(e[n].items[r].description) + "</p></div></td>" + "<td class='nowrap'><div class='timebold reservationMenuItemPrice'>" + FormatDollars(e[n].items[r].price) + "</div></td>" + "<td><input class='form-control reservationMenuItemQuantity' type='text' style='width:50px;display:inline-block' value='" + SanitizeAttr(e[n].items[r].quantity ? e[n].items[r].quantity : 0) + "'><div class='reservationMenuItemMinMax'>Min: " + e[n].items[r].min + " Max: " + e[n].items[r].max + "</div></td></tr>"
        }
        o += "</tbody></table>", $(".reservationmenus").append(o)
    }
    for (var n = 0; n < e.length; n++)
        for (var r = 0; r < e[n].items.length; r++)
            if (e[n]["items"][r]["max"] == 1) {
                var a = $("tr[data-id='" + SanitizeAttr(e[n].items[r].id) + "'] input.reservationMenuItemQuantity");
                a.prop("type", "hidden");
                var f = $("<input name='chkAddon' type='checkbox' " + (a.val() > 0 ? " checked" : "") + (e[n]["items"][r]["min"] == "1" ? " disabled" : "") + ">");
                $("tr[data-id='" + SanitizeAttr(e[n].items[r].id) + "'] div.reservationMenuItemMinMax").remove(), a.after(f), f.on("click", function(e) {
                    $(this).prop("checked") ? $(this).parents("tr.reservationMenuItem").find("input.reservationMenuItemQuantity").val("1") : $(this).parents("tr.reservationMenuItem").find("input.reservationMenuItemQuantity").val("0"), CalculateReservationFoodSubTotal()
                })
            }
    ReBindReservationFoodControls()
}

function ReBindReservationPersonnelControls() {
    $("input.reservationPersonnelQuantity").keyup(function(e) {
        var t = parseInt($(this).parents(".reservationperson").first().attr("data-min")),
            n = parseInt($(this).parents(".reservationperson").first().attr("data-max")),
            r = parseInt($(this).val());
        r < t || r > n ? $(this).css({
            "background-color": "#f2dede"
        }) : $(this).css({
            "background-color": ""
        }), CalculateReservationPersonnelSubTotal()
    }), CalculateReservationPersonnelSubTotal()
}

function CalculateReservationPersonnelSubTotal() {
    var e = 0;
    $(".reservationperson").each(function() {
        var t = parseInt($(this).find("input.reservationPersonnelQuantity").first().val().replace(/[^0-9\.]+/g, "")),
            n = parseFloat($(this).find("div.reservationPersonnelPrice").first().text().replace(/[^0-9\.]+/g, ""));
        t > 0 && n > 0 && (e += t * n)
    }), $("div.reservationSubTotal").empty().append("Subtotal: " + FormatDollars(e)), $("div.reservationSubTotal").css("display", "block")
}

function AddReservationPersonnel(e, t) {
    console.log("booking", t);
    for (var n = 0; n < e.length; n++) {
        if (e[n].req > 0) {
            var r = Math.ceil(parseInt(t.headcount) / parseFloat(e[n].req)) * Math.ceil((parseInt(moment(new Date(e[n].hours_limits.stop * 1e3)).format("X")) - parseInt(moment(new Date(e[n].hours_limits.start * 1e3)).format("X"))) / 3600);
            r > e[n].min && (e[n].min = r)
        }
        var i = $("<tr class='reservationperson' data-id='" + SanitizeAttr(e[n].id) + "' data-min='" + SanitizeAttr(e[n].min) + "' data-max='" + SanitizeAttr(e[n].max) + "' data-req='" + SanitizeAttr(e[n].req) + "'>" + "<td><div class='media-body'><h5>" + e[n].name + "</h5><p>" + FormatDescription(e[n].description) + "</p></div></td>" + "<td><div class='timebold reservationPersonnelPrice'>" + FormatDollars(e[n].price) + "/hr</div></td>" + "<td><input class='form-control reservationPersonnelQuantity' type='text' style='width:50px;display:inline-block' value='0'><div class='reservationPersonnelMinMax'></div></td></tr>");
        e[n].req > 0 && i.find("div.timebold").append("<br><small>(required)</small>"), i.find("div.reservationPersonnelMinMax").empty().append("Min: " + e[n].min + (e[n].req > 0 ? "" : "  Max: " + e[n].max));
        var s = !1;
        for (var o = 0; o < t.personnel.length; o++) t["personnel"][o]["id"] == e[n]["id"] && (t.personnel[o].quantity < e[n].min && (t.personnel[o].quantity = e[n].min), i.find("input.reservationPersonnelQuantity").first().val(t.personnel[o].quantity), s = !0);
        !s && e[n].req > 0 && i.find("input.reservationPersonnelQuantity").first().val(e[n].min), $("div.reservationpersonnel table tbody").append(i)
    }
    ReBindReservationPersonnelControls()
}

function UpdateBookingArrayAddons(e) {
    for (var t = 0; t < e.resources.length; t++) {
        e.resources[t].addons = [];
        var n;
        $("div.reservationresource[data-id='" + SanitizeAttr(e.resources[t].id) + "']").each(function() {
            $.parseJSON($(this).attr("data-hours"))[0]["start"] == e["resources"][t]["start"] && (n = $(this))
        }), typeof n == "undefined" ? (console.log("spliced ", t), e.resources.splice(t, 1), t--) : n.find("tr.reservationresourceaddon").each(function() {
            var n = parseInt($(this).find("input.reservationAddonQuantity").first().val());
            if (n > 0) {
                var r = {
                    id: $(this).attr("data-id"),
                    quantity: n,
                    price: $(this).attr("data-price"),
                    deliverat: $(this).attr("data-deliverable") ? $(this).find("select.selectReservationAddonDeliverAt option:selected").val() : null,
                    cost: 0,
                    deposit_amount: 0,
                    full_due: 0,
                    refund_policyid: null
                };
                e.resources[t].addons.push(r)
            }
        })
    }
}

function ReBindReservationControls() {
    $("[name=reservationRemove]").off("click").on("click", function(e) {
        e.preventDefault();
        var t = $(this).parents("div.reservationresource").first();
        $("#mainModalHeader").empty().append("Delete?"), $("#mainModalAcceptBtn").empty().append("OK").css({
            display: "inline"
        }), $("#mainModalCloseBtn").empty().append("Cancel").css({
            display: "inline"
        }), $("#mainModalBody").empty().append('Are you sure you want to remove "' + t.find("[name=reservationResourceTitle]").first().text() + '"?'), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            $("#mainModal").modal("hide")
        }).click(function(e) {
            t.remove(), $("#mainModalBody").empty(), ReBindReservationControls()
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    }), $("input.reservationAddonQuantity").keyup(function(e) {
        var t = parseInt($(this).parents("tr.reservationresourceaddon").first().attr("data-min")),
            n = parseInt($(this).parents("tr.reservationresourceaddon").first().attr("data-max")),
            r = parseInt($(this).val());
        r < t || r > n ? $(this).css({
            "background-color": "#f2dede"
        }) : ($(this).css({
            "background-color": ""
        }), $(this).attr("data-quantity", r)), CalculateReservationSubTotal()
    }), CalculateReservationSubTotal()
}

function CalculateReservationSubTotal() {
    var e = 0;
    $("[name=reservationResourceCost]").each(function() {
        e += parseFloat($(this).text().replace(/[^0-9\.]+/g, ""))
    }), $("tr.reservationresourceaddon").each(function() {
        var t = parseInt($(this).find("input.reservationAddonQuantity").first().val().replace(/[^0-9\.]+/g, "")),
            n = parseFloat($(this).attr("data-price"));
        e += t * n, t > 0 && $(this).attr("data-deliverable") == 1 ? $(this).find("div.reservationAddonDeliverAt").css({
            display: "inline-block"
        }) : $(this).find("div.reservationAddonDeliverAt").css({
            display: "none"
        })
    }), $("div.reservationSubTotal").empty().append("Subtotal: " + FormatDollars(e)), $("div.reservationSubTotal").css("display", "block")
};