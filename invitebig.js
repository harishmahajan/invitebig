function qResize() {
    resizeQueue.push(!0)
}

function responsiveView() {
    if (resizeQueue.length > 1) {
        var e = $(window).width(),
            t = $(window).height();
        if ($("div.featurette").length > 0) {
            if (e < 635) $("div.featurette div.spacer").hide(), $("div.featurette img,div.featurette video").each(function() {
                $(this).next("h2").length == 0 && $(this).parent().prepend($(this))
            });
            else {
                var n = !1;
                $("div.spacer").each(function() {
                    $(this).next("img").length > 0 && (n = !0)
                }), n || location.reload()
            }
            $("div.featurette video").each(function() {
                $(this).width() < 300 ? ($(this).parent().prepend($(this)), $(this).parent().children().each(function() {
                    $(this).css({
                        width: "100%"
                    })
                }), $(this).parent().find("div.spacer").hide(), $(this).css({
                    width: "100%",
                    "margin-bottom": "20px"
                })) : $(this).width() > 500 && $(window).width() > 976 && location.reload()
            })
        }
        $(".panel-heading .nav li").length > 0 && $(".panel-heading .nav").each(function() {
            var e = 0,
                t = !1;
            $(this).removeClass("small-tabs"), $(this).find("li a:visible").height("auto"), $(this).find("li").css({
                "float": "left"
            });
            var n = $(this).find("li").first().offset().top;
            $(this).find("li a:visible").each(function() {
                $(this).outerHeight() > e && (e = $(this).outerHeight()), $(this).offset().top - n > 5 && (t = !0)
            }), e > 75 && (t = !0), t ? ($(this).addClass("small-tabs"), $(this).find("li").css({
                "float": "none"
            })) : $(this).find("li a:visible").css({
                height: e + "px"
            })
        });
        if (window.location.href.indexOf("/dashboard") > 0) {
            var r = !1;
            e <= 940 && (r = !0), t < 670 && (r = !0), r ? ($("div.sidebar-toggle-box").length < 1 && ($("#imgLogo").prepend("<div class='sidebar-toggle-box'><div data-original-title='Toggle Navigation' data-placement='right' class='fa fa-reorderr fa-3'></div></div>"), $("#sidebar").hide(), $("div.sidebar-toggle-box").off("click").on("click", function(e) {
                e.preventDefault(), $("#sidebar").is(":visible") === !0 ? $("#sidebar").hide() : $("#sidebar").show()
            })), $("#sidebar").addClass("small-sidebar"), $("#sidebar > ul").css({
                "margin-top": "0px"
            }), $("img.logo-sidebar").hide(), $("#main-content").css({
                "margin-left": "0px"
            }), $("div.footer-spacer").hide()) : ($("#sidebar").removeClass("small-sidebar"), $("#sidebar").css("margin-top", -1 * $("#header").height() - 1), $("div.sidebar-toggle-box").remove(), $("img.logo-sidebar").show(), $("#sidebar").show(), $("#main-content").css({
                "margin-left": "200px"
            }), $("div.footer-spacer").css("display", "inline-block"))
        }
        sessionStorage.getItem("iframe") == "true" && window.location.href.indexOf("/dashboard") < 0 ? ($("#header").hide(), $("#headerPad").hide(), $("#footer").hide(), $("#footerPad").hide()) : ($("#header").show(), $("#headerPad").show(), $("#footer").show(), $("#footerPad").show());
        //sessionStorage.getItem("displayHeader") == "true" && window.location.href.indexOf("/dashboard") < 0 ? ($(".bookHeading").show(),$(".bookHeader").show()) : ($(".bookHeading").hide(),$(".bookHeader").hide());
        //sessionStorage.getItem("displayHeader") == "true" && window.location.href.indexOf("/dashboard") < 0 ? ($(".bookHeading").css("display","block !important"),$(".bookHeader").css("display","block !important")) : ($(".bookHeading").css("display","none !important"),$(".bookHeader").css("display","none !important"));
        if(sessionStorage.getItem("displayHeader") == "true" && window.location.href.indexOf("/dashboard") < 0)
        {
            //alert("true");
                        $("div.bookHeading").show();
                        $("div.bookHeader").show();
                        //$("#bookHeaders").show();
        }
        else
        {
            //alert("false");
                        //$("#bookHeaders").hide();
                        $("div.bookHeading").hide();
                        $("div.bookHeader").hide();
        }
        //sessionStorage.getItem("flag") == "true" ? ($(".bookHeading").show(),$(".bookHeader").show()) : ($(".bookHeading").hide(),$(".bookHeader").hide());

        var i = $("#header"),
            s = $("#footer");
        i.removeClass("narrow"), s.removeClass("narrow"), i.outerHeight() > 110 && $("#header").addClass("narrow"), s.outerHeight() > 110 && $("#footer").addClass("narrow"), $("#bodyWrapper").css({
            "margin-bottom": "-" + s.outerHeight() + "px"
        }), $("#headerPad").height(i.outerHeight()), $("#footerPad").height(s.outerHeight()), s.outerHeight() > 110 ? ($("div.copyrightpan").css({
            margin: "0"
        }), $("div.footerlinkpan").css({
            margin: "0"
        }), $("div.copyrightpan").css({
            "float": "none"
        }), $("div.footerlinkpan").css({
            "float": "none"
        }), window.location.href.indexOf("/dashboard") > 0 && $("div.small-sidebar").length < 1 && $("div.footerlinkpan div.footer-spacer").css({
            display: "inline-block"
        })) : ($("div.copyrightpan").css({
            "float": "right"
        }), $("div.footerlinkpan").css({
            "float": "left"
        }), $("div.copyrightpan").css({
            "margin-right": "5%"
        }), $("div.footerlinkpan").css({
            "margin-left": "5%"
        })), window.location.href.indexOf("/venue") > 0 && (e < 464 ? ($("#venueAddons td img").each(function() {
            $(this).next("div.media-body").prepend($(this))
        }), $("#venueMenus td img").each(function() {
            $(this).next("div.media-body").prepend($(this))
        })) : $("#venueAddons div.media-body img").length > 0 && location.reload()), resizeQueue = [], setTimeout(qResize, 1e3)
    }
}

function TriggerAjaxLoad() {
    $("#ajaxOverlayInvis").show(), localStorage.getItem("ajaxLoading") || (localStorage.setItem("ajaxLoading", Date.now()), setTimeout(TriggerAjaxShow, 1500)), $("#ajaxOverlay").css("display") == "none" && $("#ajaxOverlay").css("opacity", 0)
}

function TriggerAjaxShow() {
    jQuery.active > 0 && localStorage.getItem("ajaxLoading") < Date.now() - 1e3 && $("#ajaxOverlay").show().animate({
        opacity: 100
    }, 1e4)
}

function TriggerAjaxUnload() {
    jQuery.active == 0 && ($("#ajaxOverlayInvis").hide(), $("#ajaxOverlay").hide(), $("#ajaxOverlay").finish(), localStorage.removeItem("ajaxLoading"))
}

function SetTitleAndMeta() {
    $("head link[rel='canonical']").remove();
    var e = "Online Event Booking and Venue Management Software | InviteBIG",
        t = "Are you planning an event? Do you manage a venue? Our event booking, venue booking and venue management software is here to help.";
    if (window.location.href.indexOf("venue-management-software") > 0) e = "Event and Venue Management Software | InviteBIG", t = "Venue management software. Event management software. InviteBIG provides a suite of online tools for venues to achieve a state of organized Zen.";
    else if (window.location.href.indexOf("online-booking-system") > 0) e = "Online Booking System For Your Venue | InviteBIG", t = "InviteBIG's online booking system will delight your customers and save you time. Our event management software will revolutionize your venue's reservations.";
    else if (window.location.href.indexOf("online-event-booking") > 0) e = "Online Event Booking Without the Hassle | InviteBIG", t = "Online event booking is complex and time-consuming. Accessible 24 hours a day, 7 days a week, our online software will guide you step by step.";
    else if (window.location.href.indexOf("/register/venue") > 0) e = "New Venue Registration | InviteBIG", t = "Register for InviteBIG.com today!";
    else if (window.location.href.indexOf("/register") > 0) e = "New User Registration | InviteBIG", t = "Register for InviteBIG.com today!";
    else if (window.location.href.indexOf("/create-venue") > 0) e = "New Venue Registration | InviteBIG", t = "Register for InviteBIG.com today!";
    else if (window.location.href.indexOf("/terms") > 0) e = 'InviteBIG Terms of Service ("Agreement")', t = 'InviteBIG Terms of Service ("Agreement")';
    else if (window.location.href.indexOf("/privacy") > 0) e = "InviteBIG's Privacy Policy", t = "InviteBIG's Privacy Policy";
    else if (window.location.href.indexOf("/help") > 0) e = "InviteBIG Help and Support", t = "InviteBIG Help and Support";
    else if (window.location.href.indexOf("/forgot") > 0) e = "Forgot Your Password? | InviteBIG", t = "InviteBIG Password Reset";
    else if (window.location.href.indexOf("/request-a-demo") > 0) e = "Request an InviteBIG demonstration | InviteBIG", t = "Schedule a demonstration of InviteBIG's venue management software solution!";
    else if (window.location.href.indexOf("/reserve") > 0) {
        e = "Book an Event", t = "Find the perfect time to create your reservation";
        if (window.location.href.indexOf("/book-an-event-at") > 0) {
            var n = window.location.href.split("?")[0].split("book-an-event-at-")[1].replace(/\-/g, " ").replace(/\w\S*/g, function(e) {
                return e.charAt(0).toUpperCase() + e.substr(1).toLowerCase()
            });
            t += " for " + n, e += " at " + n
        }
        e += " | InviteBIG";
        var r = window.location.href;
        r = r.split("?")[0], r = "/reserve" + r.split("/reserve")[1], $("head").append("<link rel='canonical' href='" + SanitizeAttr(r) + "'>")
    } else if (window.location.href.indexOf("/venues") > 0) {
        e = "Search For Venues", t = "Search through our directory of venues to discover a facility that is perfect for your event. Book space, amenities and catering for any meeting, celebration, team building event, and much more.";
        var i = new Array;
        window.location.href.indexOf("/venues/") > 0 && (i = window.location.href.split("/venues/")[1].split("?")[0].split("/"));
        if (i.length > 0) {
            for (var s = 0; s < i.length; s++) i[s] = i[s].replace(/\-/g, " ").replace(/\w\S*/g, function(e) {
                return e.charAt(0).toUpperCase() + e.substr(1).toLowerCase()
            });
            i[0] = i[0].replace(/\s([^\s]*)$/, ", $1"), i[0].indexOf(", ") > 0 && (i[0] = i[0].split(", ")[0] + ", " + i[0].split(", ")[1].toUpperCase()), i[0] == "All" && (i[0] = "All Cities"), i.length > 3 && i[2] == "Type" ? (t = "The Best Venues in " + i[0] + " at InviteBIG.com. Search through our directory of " + i[1] + " with " + i[3] + " to discover a facility that is perfect for your event. Book space, amenities and catering for any meeting, celebration, team building event, and much more.", e = i[1] + " - " + i[0] + " - " + i[3]) : i.length > 2 && i[2].length > 0 ? (t = "The Best Venues in " + i[0] + " at InviteBIG.com. Book your event at " + i[2] + ".", e = i[2] + " - " + i[1] + " - " + i[0]) : i.length > 1 && i[1].length > 0 ? (t = "The Best Venues in " + i[0] + " at InviteBIG.com. Search through our directory of " + i[1] + " to discover a facility that is perfect for your event. Book space, amenities and catering for any meeting, celebration, team building event, and much more.", e = i[1] + " - " + i[0]) : i.length > 0 && i[0].length > 0 && (t = "The Best Venues in " + i[0] + " at InviteBIG.com. Search through our directory of venues to discover a facility that is perfect for your event. Book space, amenities and catering for any meeting, celebration, team building event, and much more.", e = i[0] + " - Venues")
        }
        var r = window.location.href;
        r = r.split("?")[0], r = "/venues" + r.split("/venues")[1], i.length == 3 && (r = "/venue/" + i[2].toLowerCase().replace(/ /g, "-")), $("head").append("<link rel='canonical' href='" + SanitizeAttr(r) + "'>"), e = e.replace(/\-/g, "|") + " | InviteBIG"
    } else window.location.href.indexOf("/venue") > 0 || window.location.href.indexOf("/booking") > 0 && (e = "Booking Details | InviteBIG", t = "Booking Details");
    $("title").remove(), $("meta[name=description]").remove(), $("head").append("<title>" + e + "</title>"), $("head").append("<meta name='description' content=\"" + t + '">')
}

function AuthPing() {
    var e = new $.Deferred,
        t = {
            method: "fAuthPing"
        };
    return Post(t).then(function(t) {
        if (t["result"] != "success" || localStorage.getItem("email") && localStorage.getItem("email").length > 1 && !t.rights || localStorage.getItem("venueRights") && !t.rights || window.location.href.indexOf("/dashboard") > 0 && !localStorage.getItem("email")) window.location.href = "/expired";
        t.rights && localStorage.setItem("venueRights", JSON.stringify(t.rights)), localStorage.getItem("cacheTimestamp") || localStorage.setItem("cacheTimestamp", t.cacheTimestamp), localStorage.getItem("cacheTimestamp") != t["cacheTimestamp"] && (localStorage.setItem("cacheTimestamp", t.cacheTimestamp), localStorage.removeItem("cacheWarm"), localStorage.setItem("forceReload", "true")), e.resolve()
    }), e.promise()
}

function Post(e) {
    var t = new $.Deferred;
    return e.auth = localStorage.getItem("auth"), $.ajax({
        type: "POST",
        contentType: "application/x-www-form-urlencoded",
        url: "/action.php",
        data: {
            request: JSON.stringify(e)
        },
        async: !0
    }).done(function(n) {
        var r = !0;
        try {
            r = $.parseJSON(n), localStorage.setItem("auth", r.auth)
        } catch (i) {
            console.log("Post() error: ", i), r = $.parseJSON('{"result":"There was an error processing your request, please refresh the page"}')
        }
        t.resolve(r, e)
    }), t.promise()
}

function PostFiles(e) {
    var t = new $.Deferred;
    return e.auth = localStorage.getItem("auth"), $.ajax({
        type: "POST",
        contentType: !1,
        processData: !1,
        url: "/action.php",
        data: e,
        async: !0
    }).done(function(e) {
        var n = !0;
        try {
            n = $.parseJSON(e), localStorage.setItem("auth", n.auth)
        } catch (r) {
            console.log("PostFiles() error: ", r), n = $.parseJSON('{"result":"There was an error processing your request, please refresh the page"}')
        }
        t.resolve(n)
    }), t.promise()
}

function ConfirmLeaving(e) {
    var t = new $.Deferred;
    return $("#mainModalHeader").empty().append("Save your changes?"), $("#mainModalAcceptBtn").empty().append("Save").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Don't Save").css({
        display: "inline"
    }), $("#mainModalBody").empty().append(e + "<br>Would you like to save your changes?"), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
        $("#mainModalBody").empty(), $("#mainModal").modal("hide"), t.resolve("yes")
    }), $("#mainModalCloseBtn").off("click").click(function(e) {
        $("#mainModalBody").empty(), $("#mainModal").modal("hide"), t.resolve("no")
    }), t.promise()
}

function LoadPartial(e, t, n) {
    var r = new $.Deferred;
    typeof n == "undefined" || n ? n = !0 : n = !1;
    var i = !1;
    if (t != "mainModalBody" && t != "divPromoCodeDetails") {
        var s = BeforeLeaving();
        s && (i = s)
    }
    if (i) {
        var o = ConfirmLeaving(i);
        $.when(o).then(function(i) {
            i == "yes" ? (ClickSaveConfig(), r.fail()) : LoadPartialThread(r, e, t, n)
        })
    } else LoadPartialThread(r, e, t, n);
    return r.promise()
}

function LoadPartialThread(e, t, n, r) {
    function f(e) {
        e.indexOf("<html") >= 0 || e == "404" ? ($("head").append('<meta name="prerender-status-code" content="404">'), e = a) : $("meta[name='prerender-status-code']").remove(), typeof datepicker != "undefined" && $("#" + n + " .hasDatepicker").datepicker("destroy"), $("#" + n).empty().append(e), n == "divPromoCodeDetails" ? null : n == "mainModalBody" ? $("#mainModal").animate({
            scrollTop: 0
        }) : $("html, body").animate({
            scrollTop: 0
        }), $(window).trigger("resize")
    }
    n || (n = "bodyContent");
    var i = t;
    i.indexOf("/") !== 0 && (i = "/" + i), i == "/" && (i = "/homepage"), i.charAt(i.length - 1) == "/" && (i = i.substring(0, i.length - 1));
    if (i.indexOf(".html") == i.length - 5 && i.length - 5 >= 0) i = i.substring(0, i.length - 5);
    else try {
        i = i.split("/")[1].split("?")[0].split("#")[0]
    } catch (s) {}
    if (i == "venues") {
        var s = t.split("/");
        s.length == 5 && s[4].length > 0 && (i = "venue")
    }
    i = "/partials/" + i + ".html?_=1455331660", i = i.replace("//", "/"), n == "bodyContent" && r && $("#bodyContent").html() && $("#bodyContent").html().length > 0 && (typeof window.history.pushState != "undefined" ? window.history.pushState(!0, null, t) : window.location.href = t), typeof booking == "object" && booking != null && typeof booking.url == "string" && window.location.href.indexOf("/reserve/") < 0 && $("a.savedbooking").length < 1 && $("ul.nav.navbar-nav").prepend("<li class='navbutton'><a class='savedbooking' data-partial=true href='/reserve/book-an-event-at-" + booking.url + "'>Return To<br>Booking</a></li>"), window.location.href.indexOf("/reserve/") > 0 && $("a.savedbooking").parents("li").first().remove(), $(".navDashHeading").remove();
    if (window.location.href.indexOf("/dashboard") > 0 && localStorage.getItem("activeProfile") != "me") {
        var o = $.parseJSON(localStorage.getItem("venueRights"));
        if (o)
            for (var u = 0; u < o.length; u++)
                if (o[u]["venueid"] == localStorage.getItem("activeProfile")) {
                    $("#header ul.nav").before("<div class='navDashHeading'><h3>" + o[u].venueName + "</h3>" + "<a href='/venue/" + o[u].url + "'>Venue Profile</a></div>");
                    break
                }
    }
    SetTitleAndMeta();
    var a = "<div class='container'><div class='panel' style='margin:30px'><div class='panel-body' style='padding:60px 20px 70px 20px'><div style='text-align:center'><h1>Error 404, Not Found</h1>The page you are looking for does not exist.  We suggest that you return to the <a data-partial=true href='/'>homepage</a> to find what you are looking for.</div></div></div></div>";
    return i == "/partials/404.html?_=1455331660" ? (f("404"), $("#" + n).loadingDone(), e.resolve()) : $.ajax({
        type: "GET",
        contentType: "application/x-www-form-urlencoded",
        url: i,
        async: !0
    }).done(function(t) {
        f(t), e.resolve()
    }).fail(function() {
        f("404"), e.fail()
    }), e.promise()
}

function ClickHandler(e) {
    var t = $(e.target);
    t.parent().prop("tagName") == "A" && t.parent().attr("data-partial") && (t = t.parent());
    var n = !1;
    localStorage.getItem("forceReload") == "true" && (localStorage.removeItem("forceReload"), n = !0), t.prop("tagName") == "A" && t.attr("data-partial") && !n ? (e.preventDefault(), LoadPartial(t.attr("href"))) : t.prop("tagName") == "A" && t.attr("data-partialTab") && t.attr("data-target") ? (e.preventDefault(), LoadPartial(t.attr("href"), t.attr("data-target"))) : (t.attr("id") == "mainModalCloseBtn" || t.attr("id") == "mainModalAcceptBtn") && e.preventDefault()
}

function BeforeLeaving() {
    if (typeof EncodeVenueConfig == "function" && localStorage.getItem("tempVenueConfig") && $("#venueBusinessName").length > 0) {
        var e = JSON.stringify(EncodeVenueConfig());
        if (e != localStorage.getItem("tempVenueConfig")) return "You have changed this venue configuration, if you leave this page without saving it then your changes will be lost."
    }
    localStorage.removeItem("tempVenueConfig");
    return
}

function WarmCache() {
    if (!localStorage.getItem("cacheWarm")) {
        var e = ["/partials/venues.html?_=1455331660", "/partials/request-a-demo-success.html?_=1455331660", "/partials/homepage.html?_=1455331660", "/partials/booking.html?_=1455331660", "/partials/request-a-demo.html?_=1455331660", "/partials/logout.html?_=1455331660", "/partials/verify.html?_=1455331660", "/partials/reserve/reserve_info.html?_=1455331660", "/partials/reserve/reserve_personnel.html?_=1455331660", "/partials/reserve/reserve_addon.html?_=1455331660", "/partials/reserve/reserve_order.html?_=1455331660", "/partials/reserve/reserve_avail.html?_=1455331660", "/partials/reserve/reserve_food.html?_=1455331660", "/partials/register.html?_=1455331660", "/partials/help.html?_=1455331660", "/partials/create-venue.html?_=1455331660", "/partials/register-success.html?_=1455331660", "/partials/forgot.html?_=1455331660", "/partials/online-event-booking.html?_=1455331660", "/partials/dashboard.html?_=1455331660", "/partials/terms.html?_=1455331660", "/partials/online-booking-system.html?_=1455331660", "/partials/venue-management-software.html?_=1455331660", "/partials/expired.html?_=1455331660", "/partials/admin.html?_=1455331660", "/partials/venue-creator.html?_=1455331660", "/partials/login.html?_=1455331660", "/partials/venue.html?_=1455331660", "/partials/dashboard/venue-calendar.html?_=1455331660", "/partials/dashboard/venue-overview.html?_=1455331660", "/partials/dashboard/user-bookings.html?_=1455331660", "/partials/dashboard/message-details.html?_=1455331660", "/partials/dashboard/user-calendar.html?_=1455331660", "/partials/dashboard/user-profile.html?_=1455331660", "/partials/dashboard/venue-messages.html?_=1455331660", "/partials/dashboard/venue-subscription-plans.html?_=1455331660", "/partials/dashboard/venue-locked.html?_=1455331660", "/partials/dashboard/venue-bookings.html?_=1455331660", "/partials/dashboard/venue-subscription.html?_=1455331660", "/partials/dashboard/message-new.html?_=1455331660", "/partials/dashboard/user-overview.html?_=1455331660", "/partials/dashboard/venue-integrations.html?_=1455331660", "/partials/dashboard/venue-sales.html?_=1455331660", "/partials/dashboard/user-messages.html?_=1455331660", "/partials/dashboard/booking-details.html?_=1455331660", "/partials/venue-creator/resource.html?_=1455331660", "/partials/venue-creator/menuitem.html?_=1455331660", "/partials/venue-creator/personnel.html?_=1455331660", "/partials/venue-creator/addon.html?_=1455331660", "/partials/venue-creator/refund.html?_=1455331660", "/partials/venue-creator/deposit.html?_=1455331660", "/partials/venue-creator/promo.html?_=1455331660", "/partials/venue-creator/contact.html?_=1455331660", "/partials/venue-creator/menu.html?_=1455331660", "/partials/privacy.html?_=1455331660", "/partials/reserve.html?_=1455331660"];
        for (var t = 0; t < e.length; t++) $.ajax({
            type: "GET",
            url: e[t],
            async: !0
        }).done(function(e) {});
        localStorage.setItem("cacheWarm", "true")
    }
}

function CalcMinute(e) {
    if (/([0-9]{1,2}):([0-9]{2}) (pm|am)/i.test(e) != 1) return 0;
    var t = parseInt(e.split(":")[0]),
        n = parseInt(e.split(":")[1].replace("pm", "").replace("am", "").replace(" ", ""));
    return t < 12 && e.indexOf("pm") > 0 && (t += 12), t == 12 && e.indexOf("am") > 0 && (t = 0), t * 60 + n
}

function FormatInterval(e) {
    var t = Math.ceil(e / 60),
        n = t % 60,
        r = parseInt((t - n) / 60);
    return "" + r + "h " + n + "m"
}

function FormatDate(e, t, n) {
    if (!e || e.length < 1) return "";
    n = typeof n == "string" ? n : "", n == "" && $("#timezone").length > 0 && $("#timezone").val().length > 0 ? n = $("#timezone").val() : $("#bookTimezone").length > 0 && $("#bookTimezone").val().length > 0 && (n = $("#bookTimezone").val());
    var r = moment.tz(new Date, n);
    if ($.isNumeric(e)) var r = moment.tz(new Date(e * 1e3), n);
    else if (e.length > 0) var r = moment.tz(new Date(e), n);
    else console.log("time.length <= 0!", e);
    return r.format(t)
}

function FormatDateTime(e, t, n) {
    return FormatDate(e, t, n) + " " + FormatTime(e, null, n)
}

function FormatDateTimeTz(e, t, n) {
    return typeof e != "number" && (e = moment(new Date(e)).format("X")), FormatDate(e, t, n) + " " + FormatTime(e, null, n) + " " + moment.tz.zone(n).abbr(e * 1e3)
}

function FormatTime(e, t, n) {
    return FormatDate(e, t && t.length > 0 ? t : "h:mma", n)
}

function FormatDollars(e, t) {
    t || (typeof booking == "array" && booking.currency && (t = booking.currency), $(".availwidget").length > 0 && $(".availwidget").attr("data-currency") && (t = $(".availwidget").attr("data-currency")), $("#venueCurrency").length > 0 && $("#venueCurrency").val().length > 0 && (t = $("#venueCurrency").val())), t || (t = ""), t = t.toUpperCase(), e || (e = 0), e = String(e).replace(/[^0-9.\-]/g, "");
    var n = parseInt(parseFloat(e) * 100) / 100;
    n = "" + n, n.indexOf(".") < 0 && (n += ".00");
    while (n.split(".")[1].length < 2) n += "0";
    n.indexOf("$") < 0 && (n = "$" + n), n = n.replace("$-", "-$");
    switch (t) {
        case "AUD":
            n = n.replace("$", "A$");
            break;
        case "CAD":
            n = n.replace("$", "C$");
            break;
        case "CHF":
            n = "CHF " + n.replace("$", "").replace("-CHF ", "CHF -");
            break;
        case "EUR":
            n = n.replace("$", "&#8364;");
            break;
        case "GBP":
            n = n.replace("$", "&#163;");
            break;
        case "HKD":
            n = n.replace("$", "HK$");
            break;
        case "INR":
            n = n.replace("$", "&#8377;");
            break;
        case "JPY":
            n = n.replace("$", "&#165;");
            break;
        case "MXN":
            n = n.replace("$", "Mex$");
            break;
        case "NOK":
            n = n.replace("$", "") + " kr";
            break;
        case "NZD":
            n += " NZD";
            break;
        case "RUB":
            n = n.replace("$", "&#8381;");
            break;
        case "USD":
        default:
    }
    return n
}

function FormatDescription(e) {
    if (!e || typeof e != "string" || e.length < 0) return e;
    e = e.replace(/(?:\r\n|\r|\n)/g, "<br>");
    var t = $("<div>" + e + "</div>"),
        n = ["A", "B", "BR", "EM", "DIV", "LI", "OL", "P", "SPAN", "UL", "FONT", "H2", "H3", "H4", "H5", "H6", "HR", "LEGEND", "S", "SMALL", "STRIKE", "STRONG", "TABLE", "THEAD", "TBODY", "TFOOT", "TD", "TH", "TR", "U"],
        r = ["class", "href", "style"];
    return t.find("*").each(function() {
        var e = $(this).get(0);
        if (n.indexOf(e.nodeName) >= 0) {
            for (var t = 0; t < e.attributes.length; t++)
                if (r.indexOf(e.attributes[t].name.toLowerCase()) < 0) {
                    e.removeAttribute(e.attributes[t].name), t = -1;
                    continue
                }
            e.nodeName == "A" && e.setAttribute("target", "_blank")
        } else $(this).remove()
    }), t.get(0).innerHTML
}

function FormatShort(e) {
    return !e || typeof e != "string" || e.length < 0 ? e : (e = $("<div>" + e.replace(/<br>/g, " ") + "</div>").text(), e)
}

function SanitizeAttr(e) {
    return String(e).replace("'", "&apos;").replace('"', "&quot;")
}
jQuery.fn.loading = function() {
    this.prepend("<div class='fnLoading' style='height:" + this.height() + "px;width:" + this.width() + "px'><div class='ajaxOverlayInvis' style='display:block'></div><div class='ajaxOverlay' style='display:block;position:absolute'><div class='loadingimage' style='display:block'></div></div></div>")
}, jQuery.fn.loadingDone = function() {
    this.find("div.fnLoading").first().remove()
}, jQuery.fn.tsWidget = function(e, t) {
    function l(e) {
        var t = "<tr><td><div class='tsDay'>Su<input type='checkbox' name='Su' data-offset='0'/></div><div class='tsDay'>M<input type='checkbox' name='M' data-offset='1440'/></div><div class='tsDay'>T<input type='checkbox' name='T' data-offset='2880'/></div><div class='tsDay'>W<input type='checkbox' name='W' data-offset='4320'/></div><div class='tsDay'>Th<input type='checkbox' name='Th' data-offset='5760'/></div><div class='tsDay'>F<input type='checkbox' name='F' data-offset='7200'/></div><div class='tsDay'>S<input type='checkbox' name='S' data-offset='8640'/></div></td><td><input type='text' class='form-control tsStart timepicker' placeholder='&nbsp;&nbsp;&nbsp;(click)'/></td><td><input type='text' class='form-control tsStop timepicker' placeholder='&nbsp;&nbsp;&nbsp;(click)'/></td>";
        e.hasClass("timeslot-widget-rate") && (t += "<td><input type='text' class='form-control tsRate' onchange='this.value=FormatDollars(this.value)' value='" + FormatDollars(0) + "'/></td>"), e.hasClass("timeslot-widget-combinable") && (t += "<td><input type='checkbox' class='tsCombine' alt='Can be combined with an adjacent timeslot' checked/> <small>Combinable</small></td>"), t += "<td><button class='btn btn-danger btn-xs tsDel'><i class='fa fa-trash-o'></i></button></td></tr>";
        var n = $(t);
        return n.find("button.tsDel").on("click", function(e) {
            e.preventDefault(), $(this).parents("tr").first().remove()
        }), n.find(".timepicker").timepicker(), n
    }

    function c(e) {
        var t = [],
            n = CalcMinute(e.find(".tsStart").val()),
            r = CalcMinute(e.find(".tsStop").val());
        return r <= n && (r += 1440), e.find(".tsDay input:checked").each(function() {
            var i = {
                start: parseInt($(this).attr("data-offset")) + n,
                stop: parseInt($(this).attr("data-offset")) + r
            };
            e.find(".tsRate").length > 0 && (i.rate = parseFloat(e.find(".tsRate").val().replace(/[^0-9.]/g, ""))), e.find(".tsCombine").length > 0 && (i.combinable = e.find(".tsCombine:checked").length), t.push(i)
        }), t
    }
    var n = $(this);
    typeof e == "undefined" && (n.find("tfoot button.tsAdd").off("click").on("click", function(e) {
        e.preventDefault();
        var t = $(this).parents("table.timeslot-widget").first();
        t.find("tbody").append(l(t))
    }), n.find("tfoot button.ts24x7").off("click").on("click", function(e) {
        e.preventDefault();
        var t = $(this).parents("table.timeslot-widget").first(),
            n = l(t);
        n.find(".tsStart").val("12:00 am"), n.find(".tsStop").val("12:00 am"), n.find(".tsDay input").prop("checked", !0), t.find("tbody").append(n)
    }));
    if (e == "validate") {
        var r = !0;
        return n.find("tbody tr.invalid").removeClass("invalid"), n.find("tbody tr .invalid-message").remove(), n.find("tbody tr").each(function() {
            var e = "";
            $(this).find(".tsDay input:checked").length < 1 && (e += "You must specify the day(s) of the week.<br>"), /([0-9]{1,2}):([0-9]{2}) (pm|am)/i.test($(this).find(".tsStart").val()) != 1 && (e += "Invalid start time specified.<br>"), /([0-9]{1,2}):([0-9]{2}) (pm|am)/i.test($(this).find(".tsStop").val()) != 1 && (e += "Invalid end time specified.<br>"), $(this).find(".tsRate").length > 0 && $(this).find(".tsRate").val().length < 1 && (e += "Invalid rate/cost specified.<br>");
            if (e.length == 0) {
                var t = $(this),
                    n = c(t),
                    i = !1;
                $(this).parents("tbody").first().find("tr").each(function() {
                    if ($(this).is(t)) return;
                    var e = c($(this));
                    for (var r = 0; r < n.length; r++)
                        for (var s = 0; s < e.length; s++)
                            if (e[s].start < n[r].stop && e[s].stop > n[r].start) return i = !0, !1
                }), i && (e += "This timeslot overlaps with another.<br>")
            }
            e.length > 0 && (e = e.slice(0, -4), $(this).addClass("invalid").find(".tsDay:last").after("<div class='invalid-message'>" + e + "</div>"), r = !1)
        }), r
    }
    if (e == "save") {
        var i = [];
        return n.find("tbody tr").each(function() {
            $.merge(i, c($(this)))
        }), i.sort(function(e, t) {
            return e.start % 1440 > t.start % 1440 ? 1 : -1
        }), i
    }
    if (e == "restore") {
        if (!t || t.length < 1) return "error";
        n.find("tbody tr").remove();
        var i = t;
        for (var s = 0; s < i.length; s++) {
            var o = Math.floor(parseInt(i[s].start) / 1440) * 1440,
                u = parseInt(i[s].start) % 1440 * 60,
                a = parseInt(i[s].stop) % 1440 * 60;
            u = FormatTime(978307200 + u, "hh:mm a", "UTC"), a = FormatTime(978307200 + a, "hh:mm a", "UTC");
            var f = !1;
            n.find("tbody tr").each(function() {
                var e = !1;
                $(this).find(".tsStart").val() == u && $(this).find(".tsStop").val() == a && (e = !0, $(this).find(".tsRate").length > 0 && $(this).find(".tsRate").val() != FormatDollars(i[s].rate) && (e = !1), $(this).find(".tsCombine").length > 0 && $(this).find(".tsCombine:checked").length != i[s].combinable && (e = !1));
                if (e) return f = $(this), !1
            }), f || (f = l(n), f.find(".tsStart").val(u), f.find(".tsStop").val(a), f.find(".tsRate").length > 0 && f.find(".tsRate").val(FormatDollars(i[s].rate)), f.find(".tsCombine").length > 0 && f.find(".tsCombine").prop("checked", i[s].combinable == 1 ? !0 : !1), n.append(f));
            while (o < parseInt(i[s].stop)) f.find(".tsDay input[data-offset='" + o + "']").prop("checked", !0), o += 1440
        }
    }
}, jQuery.fn.timepicker = function(e) {
    return $(this).each(function() {
        var t = $(this);
        return typeof e == "undefined" && t.on("click", function(e) {
            e.preventDefault();
            var n = "<div class='timepicker-widget'><div class='timepicker-left'><h4>Hour</h4><div class='timepicker-hours'><button value=1>1</button><button value=2>2</button><button value=3>3</button><button value=4>4</button><button value=5>5</button><button value=6>6</button><button value=7>7</button><button value=8>8</button><button value=9>9</button><button value=10>10</button><button value=11>11</button><button value=12>12</button><div class='timepicker-ampm'><button value='am'>AM</button><button value='pm'>PM</button></div></div></div><div class='timepicker-right'><h4>Minute</h4><div class='timepicker-minutes'><button value=0>00</button><button value=5>05</button><button value=10>10</button><button value=15>15</button><button value=20>20</button><button value=25>25</button><button value=30>30</button><button value=35>35</button><button value=40>40</button><button value=45>45</button><button value=50>50</button><button value=55>55</button><button class='timepicker-close'>Close</button></div></div></div>";
            n = $(n), n.find(".timepicker-hours button").on("click", function(e) {
                e.preventDefault();
                var n = t.val();
                if (/([0-9]{1,2}):([0-9]{2}) (pm|am)/i.test(n)) {
                    var r = n.split(":")[1];
                    t.val(("0" + $(this).val()).slice(-2) + ":" + r)
                } else t.val(("0" + $(this).val()).slice(-2) + ":00 am")
            }), n.find(".timepicker-minutes button").on("click", function(e) {
                e.preventDefault();
                var n = t.val();
                if (/([0-9]{1,2}):([0-9]{2}) (pm|am)/i.test(n)) {
                    var r = n.split(":")[0],
                        i = n.indexOf("pm") > 0 ? "pm" : "am";
                    t.val(r + ":" + ("0" + $(this).val()).slice(-2) + " " + i)
                } else t.val("12:" + ("0" + $(this).val()).slice(-2) + " am")
            }), n.find(".timepicker-ampm button").off("click").on("click", function(e) {
                e.preventDefault();
                var n = t.val();
                /([0-9]{1,2}):([0-9]{2}) (pm|am)/i.test(n) ? t.val(n.replace("am", $(this).val()).replace("pm", $(this).val())) : t.val("12:00 " + $(this).val())
            }), n.find(".timepicker-close").off("click").on("click", function(e) {
                e.preventDefault(), $(this).parents(".timepicker-widget").prev(".timepicker-overlay").remove(), $(this).parents(".timepicker-widget").first().remove(), t.css({
                    "border-color": ""
                })
            });
            var r = {
                "z-index": t.css("z-index"),
                top: t.get(0).offsetTop + t.outerHeight() + 2,
                left: t.get(0).offsetLeft
            };
            t.parent().get(0).nodeName == "TD" && (r.top += t.parent().get(0).offsetTop, r.left += t.parent().get(0).offsetLeft), r["z-index"] == "auto" && (r["z-index"] = 0), r["z-index"] = parseInt(r["z-index"]) + 10, n.css(r), t.after(n), t.css({
                "border-color": "#999"
            });
            var i = $("<div class='timepicker-overlay' style='z-index:" + (r["z-index"] - 1) + "'></div>");
            i.off("click").on("click", function(e) {
                $(this).parent().find(".timepicker-close").first().trigger("click")
            }), n.before(i)
        }), $(this)
    })
}, jQuery.fn.guideWidget = function(e) {
    return this.each(function() {
        function o() {
            var e = 0,
                n = 0;
            for (var i = 0; i < r.length; i++) e += r[i].taskWeight, r[i].taskDone > 0 && (n += r[i].taskWeight);
            var s = Math.floor(n * 100 / e),
                o = t.find(".progress-pie-chart");
            s = parseInt(s), deg = 360 * s / 100, s > 50 && o.addClass("gt-50"), t.find(".ppc-progress-fill").css("transform", "rotate(" + deg + "deg)"), t.find(".ppc-percents span").first().empty().append(s), t.find(".guide-widget-small .perc").empty().append(s)
        }

        function u(e) {
            n = e, t.empty(), t.addClass("guide-widget"), t.append("			<div class='guide-widget-small'></div>			<a href='#' class='guide-widget-close'>&#10005;</a>			<div class='container'>				<div class='guide-widget-chart-holder'>					<div class='progress-pie-chart'>						<div class='ppc-progress'>							<div class='ppc-progress-fill'></div>						</div>						<div class='ppc-percents'>							<div class='ppc-percents-wrapper'>								<span></span><span class='perc'>%</span>							</div>						</div>					</div>					<div class='guide-widget-under'>ACCOUNT SETUP</div>				</div>				<div class='guide-widget-messages'>					<h2></h2><p></p><a href='#' class='nextStep'>Next Step &raquo;</a>				</div>			</div>"), a()
        }

        function a() {
            var e = {
                method: "fGuideLoad",
                guideid: n
            };
            Post(e).then(function(e) {
                e["result"] == "success" && (r = $.parseJSON(e.guide), s = e.oneLiner, t.find(".guide-widget-small").html(s));
                var n = 0;
                for (var u = 0; u < r.length; u++) r[u].taskDone == 0 && n++;
                if (n == 0) {
                    t.remove();
                    return
                }
                f(i), o(), t.find(".guide-widget-messages a.nextStep").off("click").on("click", function(e) {
                    e.preventDefault();
                    var n = !1;
                    for (var s = i + 1; s < r.length; s++)
                        if (r[s].taskDone <= 0) {
                            f(s), n = !0;
                            break
                        }
                    if (!n)
                        for (var s = 0; s < r.length; s++)
                            if (r[s].taskDone <= 0) {
                                f(s), n = !0;
                                break
                            }
                    n || t.empty().append("<h2>Your venue profile looks complete!</h2><p>You can come back here at any time to make additional changes.</p>"), $(this).blur()
                }), t.find(".guide-widget-close").off("click").on("click", function(e) {
                    e.preventDefault(), t.find(".container").hide(), t.find(".guide-widget-close").hide(), t.find(".guide-widget-small").show(), t.css("padding", "10px")
                }), t.find(".guide-widget-close-small").off("click").on("click", function(e) {
                    e.preventDefault(), t.find(".container").show(), t.find(".guide-widget-close").show(), t.find(".guide-widget-small").hide(), t.css("padding", "30px 10px 30px 10px")
                })
            })
        }

        function f(e) {
            for (; e < r.length; e++)
                if (r[e].taskDone <= 0) break;
            if (e >= r.length)
                for (e = 0; e < r.length; e++)
                    if (r[e].taskDone <= 0) break;
            if (e >= r.length) {
                t.empty().append("<h2>Your basic venue configuration is complete!</h2><p>You can continue customizing your venue configuration now, or you may come back here at any time to make additional changes.</p>");
                return
            }
            t.css("display", "block"), t.find("h2").empty().append(r[e].taskTitle), t.find("p").empty().append(r[e].taskDesc), i = e;
            var u = 0;
            for (var a = 0; a < r.length; a++) r[a].taskDone <= 0 && u++;
            u <= 1 ? t.find(".guide-widget-messages a.nextStep").hide() : t.find(".guide-widget-messages a.nextStep").show(), r[e]["taskDone"] == -1 && (r[e].taskDone = 1), t.find(".guide-widget-messages a[data-target]").each(function() {
                $(this).on("click", function(e) {
                    function s(e) {
                        var t = $(e.attr("data-target")),
                            n = t.parents(".tab-pane").first();
                        t.hasClass("tab-pane") && (n = t), n.length > 0 && ($("a[href='#" + n[0].id + "']").trigger("click"), $("html, body").animate({
                            scrollTop: t.offset().top - 80
                        }), t.addClass("flash"), setTimeout(function() {
                            t.removeClass("flash")
                        }, 2500, t))
                    }
                    e.preventDefault();
                    var t = $(this),
                        n = t.attr("data-page");
                    if (n)
                        if (window.location.href.indexOf(n) > 0) s(t);
                        else if (n.indexOf("/reserve") >= 0) {
                        var r = $(".navDashHeading a").attr("href");
                        r = r.replace("/venue/", "/reserve/book-an-event-at-"), window.location = r
                    } else if (n.indexOf("/dashboard") >= 0 && window.location.href.indexOf("/dashboard") > 0 && n.split("#").length > 1) {
                        var i = n.split("#")[1];
                        localStorage.setItem("lastDashboardPage", i), $("#sidebar ul li").removeClass("active"), $("#sidebar ul li[data-url='" + i + "']").addClass("active"), GetDashboardPane().then(function() {
                            s(t)
                        })
                    } else window.location = n;
                    else s(t)
                })
            }), t.find(".guide-widget-messages a.markComplete").each(function() {
                $(this).on("click", function(e) {
                    e.preventDefault();
                    var u = $(this),
                        a = {
                            method: "fGuideMarkComplete",
                            guideid: n,
                            taskid: r[i].taskID
                        };
                    Post(a).then(function(e) {
                        e["result"] == "success" && (r = $.parseJSON(e.guide), s = e.oneLiner, t.find(".guide-widget-small").html(s), f(i), o())
                    })
                })
            })
        }
        var t = $(this),
            n = null,
            r = [],
            i = 0,
            s = "<a href='#' class='guide-widget-close-small'>Continue your guide</a>";
        if (this.guideWidget) return;
        this.guideWidget = {
            Init: u,
            LoadGuide: a,
            ShowTask: f,
            SetPercent: o
        }, this.guideWidget.Init(e)
    })
}, String.prototype.capitalize = function() {
    return this.toLowerCase().replace(/\b\w/g, function(e) {
        return e.toUpperCase()
    })
};