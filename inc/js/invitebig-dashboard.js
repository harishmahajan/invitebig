function RouteDashboardRequest() {
    if (!localStorage.getItem("email")) $("#sidebar").hide(), $("#dashboardContainer").empty().append("<div class='panel' style='margin:30px'><div class='panel-body' style='padding:60px 20px 70px 20px'><div style='text-align:center'><h1>You must be logged in to view your dashboard</h1></div></div></div>");
    else {
        var e = "overview",
            t = "me";
        localStorage.getItem("lastDashboardPage") && (e = localStorage.getItem("lastDashboardPage")), localStorage.getItem("lastDashboardAccount") && (t = localStorage.getItem("lastDashboardAccount")), location.hash.length > 0 && ($("#dashboard-page li").removeClass("active"), e = location.hash.replace("#", "").toLowerCase()), e && ($("#dashboard-page li").removeClass("active"), $("#dashboard-page li").each(function() {
            $(this).attr("data-url") == e && $(this).addClass("active")
        }), localStorage.setItem("lastDashboardPage", e)), t && localStorage.setItem("activeProfile", t), localStorage.getItem("lastDashboardAccount") == "me" && (localStorage.getItem("lastDashboardPage") == "subscription" || localStorage.getItem("lastDashboardPage") == "sales" || localStorage.getItem("lastDashboardPage") == "integrations") && (localStorage.setItem("lastDashboardPage", "overview"), $("#dashboard-page li").removeClass("active"), $("#dash-overview").addClass("active")), GetDashboardPane()
    }
}

function GetDashboardPane() {
    var e = new $.Deferred,
        t = !1;
    if (localStorage.getItem("venueRights") && localStorage.getItem("activeProfile")) {
        var n = $.parseJSON(localStorage.getItem("venueRights"));
        n || (window.location.href = "/expired");
        for (var r = 0; r < n.length; r++) n[r]["venueid"] == localStorage.getItem("activeProfile") && (n[r].role & 16 ? $("#dash-profile").show() : $("#dash-profile").hide(), n[r].role & 16 ? $("#dash-subscription").show() : $("#dash-subscription").hide(), n[r].role & 16 || n[r].role & 4 ? $("#dash-messages").show() : $("#dash-messages").hide(), n[r].role & 16 || n[r].role & 4 || n[r].role & 2 ? $("#dash-bookings").show() : $("#dash-bookings").hide(), n[r].role & 16 || n[r].role & 4 || n[r].role & 2 ? $("#dash-overview").show() : $("#dash-overview").hide(), n[r].role & 16 || n[r].role & 4 || n[r].role & 2 ? $("#dash-calendar").show() : $("#dash-calendar").hide(), n[r].role & 16 || n[r].role & 8 ? $("#dash-sales").show() : $("#dash-sales").hide(), n[r].role & 16 ? $("#dash-integrations").show() : $("#dash-integrations").hide(), n[r]["subscription_status"] == "canceled" && (t = !0));
        localStorage.getItem("activeProfile") == "me" && ($("#dash-sales").hide(), $("#dash-integrations").hide())
    }
    var i = !1;
    localStorage.getItem("lastDashboardAccount") && localStorage.getItem("lastDashboardAccount") != "me" && (i = !0), localStorage.getItem("lastDashboardPage") || localStorage.setItem("lastDashboardPage", "overview");
    var s = localStorage.getItem("lastDashboardPage") + ".html";
    return i ? t && localStorage.getItem("lastDashboardPage") != "subscription" ? s = "dashboard/venue-locked.html" : s = "dashboard/venue-" + s : s = "dashboard/user-" + s, s == "dashboard/venue-profile.html" && (s = "venue-creator.html"), localStorage.getItem("lastDashboardAccount") == "me" && (localStorage.getItem("lastDashboardPage") == "subscription" || localStorage.getItem("lastDashboardPage") == "sales" || localStorage.getItem("lastDashboardPage") == "integrations") && (s = "user-overview.html", localStorage.setItem("lastDashboardPage", "overview")), $("#dashboardContainer").html().length > 0 && (typeof window.history.pushState != "undefined" ? window.history.pushState(!0, null, "#" + localStorage.getItem("lastDashboardPage")) : window.location.href = "#" + localStorage.getItem("lastDashboardPage")), LoadPartial(s, "dashboardContainer").done(function() {
        var t = !0,
            n = "";
        if (localStorage.getItem("activeProfile") == "me") n = localStorage.getItem("firstname") + " " + localStorage.getItem("lastname"), t = !1, $("#dash-profile img").attr("src", "/assets/img/menu-icon-02.png"), $("#dash-profile span").empty().append("PROFILE");
        else {
            $("#dash-profile img").attr("src", "/assets/img/menu-icon-08.png"), $("#dash-profile span").empty().append("CONFIGURATION");
            var r = "",
                i = $.parseJSON(localStorage.getItem("venueRights"));
            i || (window.location.href = "/expired");
            for (var s = 0; s < i.length; s++)
                if (i[s]["venueid"] == localStorage.getItem("activeProfile")) {
                    n = i[s].venueName, r = i[s].url;
                    break
                }
        }
        $("#dashboardHeader").remove();
        if ($("i.venueLocked").length == 0) switch ($("#dashboard-page li.active:first").attr("data-url")) {
            case "overview":
                $("#dashboardContainer").prepend("<div id='dashboardHeader'><h2>Dashboard</h2><h4>Important information at a glance</h4></div>");
                break;
            case "profile":
                t ? $("#dashboardContainer").prepend("<div id='dashboardHeader'><h2>Venue Configuration</h2><h4>The more information you fill out here, the more useful the booking experience will be to your customers</h4></div>") : $("#dashboardContainer").prepend("<div id='dashboardHeader'><h2>User Profile</h2><h4>Provide your personal information below</h4></div>");
                break;
            case "bookings":
                $("#dashboardContainer").prepend("<div id='dashboardHeader'><h2>Bookings</h2><h4>A complete list of all your bookings</h4></div>");
                break;
            case "messages":
                $("#dashboardContainer").prepend("<div id='dashboardHeader'><h2>Messages</h2><h4>Communicate with " + (t ? "your customers" : "venues") + "</h4></div>");
                break;
            case "calendar":
                $("#dashboardContainer").prepend("<div id='dashboardHeader'><h2>Calendar</h2><h4>A calendar view of your bookings</h4></div>");
                break;
            case "sales":
                $("#dashboardContainer").prepend("<div id='dashboardHeader'><h2>Sales Reports</h2><h4>Generate sales reports to better understand your business</h4></div>");
                break;
            case "subscription":
                $("#dashboardContainer").prepend("<div id='dashboardHeader'><h2>InviteBIG Subscription</h2><h4>Manage your InviteBIG subscription</h4></div>");
                break;
            case "integrations":
                $("#dashboardContainer").prepend("<div id='dashboardHeader'><h2>InviteBIG Integrations</h2><h4>Integrate your InviteBIG account with your own website or other third-party services</h4></div>")
        }
        GetVenueNotifications(), e.resolve()
    }), e.promise()
}

function GetMyBookings(e) {
    e ? e = 2 : e = 1, $("table.table-bookings").find("tbody").empty();
    var t = {
        method: "fGetUserBookings",
        onlypending: e
    };
    Post(t).then(function(e) {
        if (e["result"] == "success") {
            bookings = e.data;
            for (var t = 0; t < bookings.length; t++) {
                var n = new Date,
                    r = "" + Math.ceil((bookings[t].stop - bookings[t].start) / 3600 * 100) / 100 + " hrs",
                    i = "";
                bookings[t]["isnew"] == 1 && (i = " style='font-weight:bold;background-color:#EFE'");
                var s = "<tr data-booking='" + SanitizeAttr(bookings[t].id) + "' " + i + "><td>" + bookings[t].venue + "</td><td class='no-display-mob'>" + bookings[t].headcount + "</td><td class='no-display-tab'>" + FormatDateTimeTz(parseInt(bookings[t].start), "MMMM D, YYYY", bookings[t].timezone) + "</td><td class='no-display-mob'>" + r + "</td><td>" + FormatDollars(bookings[t].total, bookings[t].currency) + "</td><td class='no-display-mob'>" + bookings[t].status + "</td><td>";
                switch (bookings[t].status) {
                    case "Pending Deposit":
                    case "Pending Approval":
                    case "Pending Payment":
                    case "Past Due":
                        s += "<button class='btn btn-xs btn-success' name='buttonPayBooking'>Pay</button> ";
                    case "Paid":
                        bookings[t].stop > n.getTime() / 1e3 && (s += "<button class='btn btn-xs btn-danger' name='buttonCancelBooking'>Cancel</button>");
                    default:
                        s += "</td>"
                }
                s += "</tr>", $("table.table-bookings").find("tbody").append(s)
            }
            ReBindBookingsTable($("table.table-bookings")), bookings.length == 0 && $("#table.table-bookings").append("<tr><td colspan=7 class='none-found'>No bookings found</td></tr>")
        }
    })
}

function GetVenueBookings(e) {
    e ? e = 2 : e = 1, $("#venueBookingsPendingTable").find("tbody").empty(), $("#venueBookingsConfirmedTable").find("tbody").empty(), $("#venueBookingsCompletedTable").find("tbody").empty();
    var t = {
        method: "fGetVenueBookings",
        venueid: localStorage.getItem("activeProfile"),
        onlypending: e
    };
    Post(t).then(function(e) {
        if (e["result"] == "success") {
            bookings = e.data;
            var t = new Date,
                n = 0,
                r = 0,
                i = 0,
                s = 0;
            console.log("bookings", bookings);
            for (var o = 0; o < bookings.length; o++) {
                var u = "" + Math.ceil((bookings[o].stop - bookings[o].start) / 3600 * 100) / 100 + " hrs";
                bookings[o].multiple === !0 && (u = "Multiple");
                var a = "";
                bookings[o]["isnew"] == 1 && (a += " new"), bookings[o].doublebooked === !0 && (a += " conflict"), bookings[o].pastDue === !0 && (a += " pastDue"), bookings[o].retainable === !0 && (a += " retainable");
                //var f = "<tr data-booking='" + SanitizeAttr(bookings[o].id) + "' class='" + a + "'><td>" + bookings[o].user + "</td><td class='no-display-mob'>" + bookings[o].headcount + "</td><td class='no-display-tab'>" + FormatDateTimeTz(parseInt(bookings[o].start), "MMMM D, YYYY", bookings[o].timezone) + "</td><td class='no-display-tab'>" + FormatDateTimeTz(parseInt(bookings[o].stop), "MMMM D, YYYY", bookings[o].timezone) + "</td><td class='no-display-mob'>" + u + "</td><td>" + FormatDollars(bookings[o].total, bookings[o].currency) + "</td><td>" + bookings[o].status + "</td>";
                var f = "<tr data-booking='" + SanitizeAttr(bookings[o].id) + "' data-resid='" + SanitizeAttr(bookings[o].resourceids) + "' class='" + a + "'><td>" + bookings[o].user + "</td><td class='no-display-mob'>" + bookings[o].headcount + "</td><td class='no-display-tab'>" + FormatDateTimeTz(parseInt(bookings[o].start), "MMMM D, YYYY", bookings[o].timezone) + "</td><td class='no-display-tab'>" + FormatDateTimeTz(parseInt(bookings[o].stop), "MMMM D, YYYY", bookings[o].timezone) + "</td><td class='no-display-mob'>" + u + "</td><td>" + FormatDollars(bookings[o].total, bookings[o].currency) + "</td><td>" + bookings[o].status + "</td>";
                bookings[o]["status"].indexOf("Pending Approval") == 0 ? (n += 1, f += "<td><button class='btn btn-xs btn-success' name='buttonApproveBooking'>Approve</button> ", f += "<button class='btn btn-xs btn-danger' name='buttonDenyBooking'>Deny</button></td></tr>", $("#venueBookingsPendingTable").find("tbody").append(f)) : bookings[o]["status"].indexOf("Pending Deposit") == 0 || bookings[o]["status"].indexOf("Pending Payment") == 0 || bookings[o]["status"].indexOf("Past Due") == 0 ? (r += 1, f += "<td><button class='btn btn-xs btn-success' name='buttonPayBooking'>Pay</button> <button class='btn btn-xs btn-danger' name='buttonCancelBooking'>Cancel</button></td></tr>", $("#venueBookingsPendingPaymentTable").find("tbody").append(f)) : bookings[o].stop > t.getTime() / 1e3 && (bookings[o]["status"].indexOf("Pending Payment") == 0 || bookings[o]["status"].indexOf("Paid") == 0|| bookings[o]["status"].indexOf("Imported") == 0) ? (i += 1, f += "<td><button class='btn btn-xs btn-danger' name='buttonCancelBooking'>Cancel</button></td></tr>", $("#venueBookingsConfirmedTable").find("tbody").append(f)) : (s += 1, f += "</tr>", $("#venueBookingsCompletedTable").find("tbody").append(f))
            }
            n == 0 && $("#venueBookingsPendingTable").append("<tr><td colspan=8 class='none-found'>No bookings found</td></tr>"), r == 0 && $("#venueBookingsPendingPaymentTable").append("<tr><td colspan=8 class='none-found'>No bookings found</td></tr>"), i == 0 && $("#venueBookingsConfirmedTable").append("<tr><td colspan=8 class='none-found'>No bookings found</td></tr>"), s == 0 && $("#venueBookingsCompletedTable").append("<tr><td colspan=8 class='none-found'>No bookings found</td></tr>"), ReBindBookingsTable($("#venueBookingsPendingTable")), ReBindBookingsTable($("#venueBookingsPendingPaymentTable")), ReBindBookingsTable($("#venueBookingsConfirmedTable")), ReBindBookingsTable($("#venueBookingsCompletedTable"))
        }
    })
}

function ReBindBookingsTable(e) {
    e.find("tr td").off("click").click(function(e) {
        if ($(this).hasClass("none-found")) return;
        var t = $(this).closest("tr").attr("data-booking");
        var rsid=$(this).closest("tr").attr("data-resid");
        $("#mainModalHeader").empty().append("Booking Details<div class='pull-right' style='font-size:12px'><a href='/booking/" + t + "'>Full Screen & Print</a></div>"), $("#mainModalAcceptBtn").empty().append("OK").css({
            display: "none"
        }), $("#mainModalCloseBtn").empty().append("OK").css({
            display: "inline"
        }), LoadPartial("dashboard/booking-details.html", "mainModalBody").then(function() {
            PopulateBookingDetails(t), ActivateBookingAccordion(), $("div.bookingDetailsSectionBody.panel-body").css({
                "border-top-left-radius": "0",
                "border-top-right-radius": "0"
            }), $("#mainModal").modal("show"), $(".close").off("click").click(function(e) {
                $("#mainModalBody").empty(), $("#mainModal").modal("hide")
            }), $("#mainModalCloseBtn").off("click").click(function(e) {
                $("#mainModalBody").empty(), $("#mainModal").modal("hide")
            })
        })
    }), e.find("[name=buttonCancelBooking]").off("click").click(function(e) {
        e.preventDefault(), $(this).parents("td").off("click"), localStorage.getItem("activeProfile") == "me" ? CancelBookingUser($(this)) : CancelBookingVenue($(this))
    }), e.find("[name=buttonPayBooking]").off("click").click(function(e) {
        e.preventDefault(), $(this).parents("td").off("click"), LoadPartial("/booking/" + $(this).parents("tr").first().attr("data-booking") + "/pay")
    }), e.find("[name=buttonApproveBooking]").off("click").click(function(e) {
        e.preventDefault(), $(this).parents("td").off("click");
        var t = $(this).parents("tr").first(),
            n = t.attr("data-booking"),
            r = t.hasClass("conflict");
        var rsid=$(this).closest("tr").attr("data-resid");
        $("#mainModalHeader").empty().append("Are you sure?"), $("#mainModalAcceptBtn").empty().append("Approve").css({display: "inline"}), 
        $("#mainModalCloseBtn").empty().append("Not Yet").css({display: "inline"}),
        $("#mainModalBody").empty().append("Are you sure you want to APPROVE " + $(this).parents("tr").first().find("td").first().text() + "'s booking?" + (r ? "<br><br>Note: there is another booking request that conflicts with this one, if you approve this booking you will not be able to approve the conflicting booking(s)." : "")), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            var r = {
                method: "fUpdateBookingStatus",
                bookingid: n,
                rsid: rsid,
                venueid: localStorage.getItem("activeProfile"),
                action: "approve",
                message: "approved",
                isVenue: 1
            };
            Post(r).then(function(e) {
                if (e["result"] == "success") {
                    var r = $("tr[data-booking=" + n + "]").first().clone(!0, !0);
                    r.find("td:nth-last-child(2)").empty().append("Pending Payment"), $("tr[data-booking=" + n + "]").first().remove(), r.find("td").last().empty().prepend("<small><small><em>Refresh page to change this</em></small></small>"), $("#venueBookingsConfirmedTable").find("tbody").append(r), $("#mainModalBody").empty(), $("#mainModal").modal("hide")
                } else e["result"] == "not available" ? ($("#mainModalBody").empty().append("<div class='alert alert-danger'>This booking cannot be approved, some of the resources requested by this booking are no longer available.</div>"), $("#mainModalAcceptBtn").css({
                    display: "none"
                }), $("#mainModalCloseBtn").empty().append("Close"), t.find("[name=buttonApproveBooking]").hide()) : ($("#mainModalAcceptBtn").css({
                    display: "none"
                }), $("#mainModalCloseBtn").empty().append("Close"), $("#mainModalBody").empty().append("<div class='alert alert-danger'>" + e.result + "</div>"))
                reloadpageonApprove();
            // var r = {
            //     method: "fAutoConflictCancelled",
            //     bookingid: n,
            //     rsid: rsid,
            //     venueid: localStorage.getItem("activeProfile"),
            //     resourcesids : localStorage.getItem("resourcesIds"),
            //     isVenue: 1
            // };
            // Post(r).then(function(e) {
            //     console.log("Conflict Slot Remove");
            // })                
            })
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    }), e.find("[name=buttonDenyBooking]").off("click").click(function(e) {
        e.preventDefault(), $(this).parents("td").off("click");
        var t = $(this).parents("tr").first().attr("data-booking");
        $("#mainModalHeader").empty().append("Are you sure?"), $("#mainModalAcceptBtn").empty().append("Deny").css({display: "inline"}), 
        $("#mainModalCloseBtn").empty().append("Not Yet").css({
            display: "inline"
        }), $("#mainModalBody").empty().append("Are you sure you want to DENY " + $(this).parents("tr").first().find("td").first().text() + "'s booking?<BR><BR><textarea class='form-control' style='width:90%;height:200px' id='reservationDenyReason' placeholder='Please provide a reason for denying this booking...'></textarea>"), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            if ($("#reservationDenyReason").val().length < 3) $("#reservationDenyReason").before("<div class='alert alert-danger'>Please provide a reason for denying this booking</div>");
            else {
                var n = {
                    method: "fUpdateBookingStatus",
                    bookingid: t,
                    venueid: localStorage.getItem("activeProfile"),
                    action: "deny",
                    message: $("#reservationDenyReason").val(),
                    isVenue: 1
                };
                Post(n).then(function(e) {
                    e["result"] == "success" ? ($("tr[data-booking=" + t + "]").first().remove(), $("#mainModalBody").empty(), $("#mainModal").modal("hide")) : ($("#mainModalAcceptBtn").css({
                        display: "none"
                    }), $("#mainModalCloseBtn").empty().append("Close"), $("#mainModalBody").empty().append("<div class='alert alert-danger'>" + e.result + "</div>"))
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    }), e.find("[name=btnRefund]").off("click").click(function(e) {
        e.preventDefault(), $(this).parents("td").off("click");
        var t = $(this).parents("tr").first(),
            n = t.attr("data-booking");
        $("#mainModalHeader").empty().append("Are you sure you want to mark this as refunded?"), $("#mainModalAcceptBtn").empty().append("Refunded").css({
            display: "inline"
        }), $("#mainModalCloseBtn").empty().append("Not Refunded").css({
            display: "inline"
        }), $("#mainModalBody").empty().append("Are you sure you want to mark " + $(this).parents("tr").first().find("td").first().text() + "'s booking as fully refunded?<br><br>This booking had payments made offline.  You will need to issue this refund manually, InviteBIG cannot automatically refund offline payments."), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            var r = {
                method: "fUpdateBookingStatus",
                bookingid: n,
                venueid: localStorage.getItem("activeProfile"),
                action: "refunded",
                message: "",
                isVenue: 1
            };
            Post(r).then(function(e) {
                e["result"] == "success" ? (t.remove(), $("#mainModalBody").empty(), $("#mainModal").modal("hide")) : ($("#mainModalAcceptBtn").css({
                    display: "none"
                }), $("#mainModalCloseBtn").empty().append("Close"), $("#mainModalBody").empty().append("<div class='alert alert-danger'>" + e.result + "</div>"))
            })
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    })
}

function GetMyMessages(e) {
    $("#tableMessages").find("tbody").empty();
    var t = {
        method: "fGetMyMessages",
        onlynew: e
    };
    Post(t).then(function(t) {
        if (t["result"] == "success") {
            messages = t.data;
            for (var n = 0; n < messages.length; n++) {
                if (e && !messages[n].isnew) continue;
                var r = "";
                messages[n].bookingid && (r = " data-bookingid='" + SanitizeAttr(messages[n].bookingid) + "'");
                var i = "";
                messages[n].isnew && (i = " style='font-weight:bold;background-color:#EFE'");
                var s = "<tr data-convoid='" + SanitizeAttr(messages[n].convoid) + "'" + r + i + "><td><input type='checkbox' name='checkboxSelected'></td>" + "<td>" + messages[n].from + "</td><td>" + messages[n].subject + "</td>" + "<td class='no-display-mob'>" + FormatDate(parseInt(messages[n].time), "MMMM D, YYYY h:mma z") + "</td></tr>";
                $("#tableMessages").find("tbody").append(s)
            }
            ReBindMessagesTable(), messages.length == 0 && $("#tableMessages").append("<tr><td colspan=4 class='none-found'>No messages found</td></tr>")
        }
    })
}

function GetVenueMessages(e) {
    $("#tableMessages").find("tbody").empty();
    var t = {
        method: "fGetVenueMessages",
        venueid: localStorage.getItem("activeProfile"),
        onlynew: e
    };
    Post(t).then(function(t) {
        if (t["result"] == "success") {
            messages = t.data;
            for (var n = 0; n < messages.length; n++) {
                if (e && !messages[n].isnew) continue;
                var r = "";
                messages[n].bookingid && (r = " data-bookingid='" + SanitizeAttr(messages[n].bookingid) + "'");
                var i = "";
                messages[n].isnew && (i = " style='font-weight:bold;background-color:#EFE'");
                var s = "<tr data-convoid='" + SanitizeAttr(messages[n].convoid) + "'" + r + i + "><td><input type='checkbox' name='checkboxSelected'></td>" + "<td>" + messages[n].from + "</td><td>" + messages[n].subject + "</td>" + "<td class='no-display-mob'>" + FormatDate(parseInt(messages[n].time), "MMMM D, YYYY h:mma z") + "</td></tr>";
                $("#tableMessages").find("tbody").append(s)
            }
            ReBindMessagesTable(), messages.length == 0 && $("#tableMessages").append("<tr><td colspan=4 class='none-found'>No messages found</td></tr>")
        }
    })
}

function ReBindMessagesTable() {
    $("#tableMessages tr td").off("click").click(function(e) {
        if ($(this).hasClass("none-found")) return;
        $(this).closest("tr").css({
            "font-weight": "normal",
            "background-color": "inherit"
        });
        var t = $(this).closest("tr").attr("data-convoid"),
            n = $(this).closest("tr").attr("data-bookingid");
        $("#mainModalHeader").empty().append($(this).closest("tr").find("td:nth-last-child(2)").text()), $("#mainModalAcceptBtn").empty().append("OK").css({
            display: "none"
        }), $("#mainModalCloseBtn").empty().append("OK").css({
            display: "inline"
        }), LoadPartial("dashboard/message-details.html", "mainModalBody").done(function() {
            PopulateMessageDetails(t), $("#mainModal").modal("show"), $("#mainModalCloseBtn").off("click").click(function(e) {
                $("#mainModalBody").empty(), $("#mainModal").modal("hide")
            })
        })
    }), $("#tableMessages [name=checkboxSelected]").each(function() {
        $(this).parents("td").first().off("click")
    }), $("#btnDeleteMessages").off("click").on("click", function(e) {
        e.preventDefault(), ClickBtnDeleteMessages()
    })
}

function CreateNewMessage() {
    $("#mainModalHeader").empty().append("New Conversation"), $("#mainModalAcceptBtn").empty().append("Send").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), LoadPartial("dashboard/message-new.html", "mainModalBody").done(function() {
        localStorage.getItem("activeProfile") == "me" ? PopulateMyNewMessageTo() : PopulateVenueNewMessageTo(), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            var t = [],
                n = $("#SelectNewMessageToList option:selected");
            n.val() == 0 && t.push("Please select the venue you would like to send a message to"), $("#textNewMessageTitle").val().length < 1 && t.push("Please specify the subject of your message"), $("#textNewMessage").val().length < 3 && t.push("Please type the message you would like to send");
            if (t.length > 0) {
                $("#messageError").empty().append("<ul>");
                for (var r = 0; r < t.length; r++) $("#messageError").append("<li>" + t[r] + "</li>");
                $("#messageError").append("</ul>"), $("#messageError").css({
                    display: "inline-block"
                }), $("#mainModal").animate({
                    scrollTop: 0
                })
            } else {
                var i = {
                        venueid: n.attr("value"),
                        title: $("#textNewMessageTitle").val(),
                        message: $("#textNewMessage").val()
                    },
                    s = {
                        method: "fNewMessage",
                        venueid: n.attr("value"),
                        title: $("#textNewMessageTitle").val(),
                        message: $("#textNewMessage").val()
                    };
                Post(s).then(function(e) {
                    if (e["result"] == "success") {
                        var t = e.data;
                        t.id && $("#tableMessages").find("tbody").first().prepend("<tr data-convoid='" + SanitizeAttr(t.id) + "'><td><input type='checkbox' name='checkboxSelected'></td>" + "<td>Me</td><td>" + s.title + "</td>" + "<td>Just Now</td></tr>"), ReBindMessagesTable(), $("#tableMessages").parents("div").first().find("div.alert").remove()
                    } else $("#mainModalBody").prepend("<div class='alert alert-danger'>" + e.result + "</div>");
                    $("#mainModalBody").empty(), $("#mainModal").modal("hide")
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    })
}

function PopulateMyNewMessageTo() {
    var e = {
        method: "fGetVenueList"
    };
    Post(e).then(function(e) {
        if (e["result"] == "success") {
            var t = e.data;
            for (var n = 0; n < t.length; n++) $("#SelectNewMessageToList").append($("<option></option>", {
                value: t[n].id,
                text: t[n].name
            }))
        }
        $("#SelectNewMessageToList").SumoSelect()
    })
}

function PopulateVenueNewMessageTo() {
    var e = {
        method: "fGetUserList",
        venueid: localStorage.getItem("activeProfile")
    };
    Post(e).then(function(e) {
        if (e["result"] == "success") {
            var t = e.data;
            for (var n = 0; n < t.length; n++) $("#SelectNewMessageToList").append($("<option></option>", {
                value: t[n].id,
                text: t[n].name
            }))
        }
        $("#SelectNewMessageToList").SumoSelect()
    })
}

function PopulateMessageDetails(e) {
    var t = {
        method: "fLoadMessage",
        messageid: e
    };
    Post(t).then(function(t) {
        if (t["result"] != "success") $("#messageError").css({
            display: "inline-block"
        }), $("#messageError").empty().append(t.result);
        else {
            $msg = t.data, $msgs = $msg.messages;
            for (var n = 0; n < $msgs.length; n++) style = "", $msgs[n]["isvenue"] == 1 && (style = " class='venuemessage'"), $("#tableMessageDetails").find("tbody").first().append("<tr " + style + "><td>" + $msgs[n].from + "</td><td>" + $msgs[n].message + "</td><td>" + FormatDate(parseInt($msgs[n].time), "MMMM D, YYYY h:mma z") + "</td></tr>");
            $("#buttonNewMessage").off("click").click(function(t) {
                t.preventDefault();
                if ($("#textNewMessage").val().length > 0) {
                    var n = {
                            convoid: parseInt(e),
                            text: $("#textNewMessage").val()
                        },
                        r = {
                            method: "fSendMessage",
                            convoid: parseInt(e),
                            text: $("#textNewMessage").val()
                        };
                    Post(r).then(function(e) {
                        e["result"] == "success" && $("#tableMessageDetails").find("tbody").first().prepend("<tr style='background-color:#DEF'><td>Me</td><td>" + $("#textNewMessage").val() + "</td><td>Just Now</td></tr>")
                    })
                }
            })
        }
    })
}

function ClickBtnDeleteMessages() {
    $("[name=checkboxSelected]:checked").length > 0 && ($("#mainModalHeader").empty().append("Are you sure you want to delete messages?"), $("#mainModalAcceptBtn").empty().append("Delete").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty().append("Are you sure you want to delete the " + $("[name=checkboxSelected]:checked").length + " selected messages?"), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
        $("[name=checkboxSelected]:checked").each(function() {
            var e = $(this);
            if ($(this).parents("tr").first().attr("data-booking") > 0) $(this).parents("tr").remove();
            else {
                var t = {
                    method: "fDeleteMessage",
                    convoid: $(this).parents("tr").first().attr("data-convoid")
                };
                Post(t).then(function(t) {
                    t["result"] == "success" && e.parents("tr").remove()
                })
            }
        }), $("#mainModalBody").empty(), $("#mainModal").modal("hide")
    }), $("#mainModalCloseBtn").off("click").click(function(e) {
        $("#mainModalBody").empty(), $("#mainModal").modal("hide")
    }))
}

function GetUserUpcomingEvents(e, t) {
    var n = {
        method: "fGetUserUpcomingEvents",
        date: e
    };
    Post(n).then(function(e) {
        if (e["result"] == "success") {
            $("#overviewUpcomingEvents").find("tbody").first().empty();
            var n = e.data;
            for (var r = 0; r < n.length; r++) {
                var i = FormatDollars(n[r].cost, n[r].currency),
                    s = FormatDate(n[r].start, "MMMM D, YYYY"),
                    o = FormatDate(n[r].stop, "MMMM D, YYYY"),
                    u = s + " " + FormatTime(n[r].start),
                    a = o + " " + FormatTime(n[r].stop),
                    f = "<tr data-booking='" + SanitizeAttr(n[r].id) + "'><td>" + u + "</td><td>" + n[r].name + "</td></tr>";
                t || (f = "<tr data-booking='" + SanitizeAttr(n[r].id) + "'><td>" + u + "</td><td>" + a + "</td><td>" + n[r].name + "</td><td>" + i + "</td></tr>"), $("#overviewUpcomingEvents").find("tbody").first().append(f)
            }
            n.length == 0 && $("#overviewUpcomingEvents").append("<tr><td colspan=4 class='none-found'>No bookings found</td></tr>"), ReBindBookingsTable($("#overviewUpcomingEvents"))
        }
    })
}

function GetVenueUpcomingEvents(e, t) {
    var n = {
        method: "fGetVenueUpcomingEvents",
        venueid: localStorage.getItem("activeProfile"),
        date: e
    };
    Post(n).then(function(e) {
        if (e["result"] == "success") {
            $("#overviewUpcomingEvents").find("tbody").first().empty();
            var n = e.data;
            for (var r = 0; r < n.length; r++) {
                var i = FormatDollars(n[r].cost, n[r].currency),
                    s = FormatDate(n[r].start, "MMMM D, YYYY"),
                    o = FormatDate(n[r].stop, "MMMM D, YYYY"),
                    u = s + " " + FormatTime(n[r].start),
                    a = o + " " + FormatTime(n[r].stop),
                    f = "<tr data-booking='" + SanitizeAttr(n[r].id) + "'><td>" + u + "</td><td>" + n[r].name + "</td></tr>";
                t || (f = "<tr data-booking='" + SanitizeAttr(n[r].id) + "'><td>" + u + "</td><td>" + a + "</td><td>" + n[r].name + "</td><td>" + i + "</td></tr>"), $("#overviewUpcomingEvents").find("tbody").first().append(f)
            }
            n.length == 0 && $("#overviewUpcomingEvents").append("<tr><td colspan=4 class='none-found'>No bookings found</td></tr>"), ReBindBookingsTable($("#overviewUpcomingEvents"))
        }
    })
}

function CancelBookingUser(e) {
    var t = e.parents("tr").first().attr("data-booking"),
        n = null,
        r = "",
        i = {
            method: "fCalculateRefund",
            bookingid: t
        };
    Post(i).then(function(i) {
        if (i["result"] == "success") {
            n = i.data, console.log("refund", n);
            if (n["already_started"] == 1) r = "This booking has already begun, you will not get any money back if you cancel now.";
            else if (!n["approved"] == 1) r = "This booking was never approved by the venue so you will get all of your money back.";
            else {
                var s = n.refund_amount,
                    o = 0,
                    u = 0,
                    a = 0,
                    f = 0;
                for (var l = 0; l < n.resources.length; l++) o += n.resources[l].fee;
                for (var l = 0; l < n.addons.length; l++) u += n.addons[l].fee;
                for (var l = 0; l < n.personnel.length; l++) f += n.personnel[l].fee;
                for (var l = 0; l < n.menus.length; l++) a += n.menus[l].fee;
                n.due > n.deposit && (r = "<h5>Fees unable to be refunded if you cancel now:</h5><div style='margin-left:30px'>Booked Resources: " + FormatDollars(o, n.currency) + "<br>" + (u > 0 ? "Booked Addons: " + FormatDollars(u, n.currency) + "<br>" : "") + (a > 0 ? "Booked Menus: " + FormatDollars(a, n.currency) + "<br>" : "") + (f > 0 ? "Booked Personnel: " + FormatDollars(f, n.currency) + "<br>" : "") + (n.bookingfee > 0 ? "Fees: " + FormatDollars(n.bookingfee, n.currency) + "<br>" : "") + "Taxes: " + FormatDollars(n.taxes, n.currency) + "<br><br>"), r += "Amount Paid: " + FormatDollars(n.paid, n.currency) + "<br>", n.due <= n.deposit && (r += "Deposit Held: " + FormatDollars(n.deposit, n.currency) + "<br>"), r += "<br><B>Total Refund: " + FormatDollars(s, n.currency) + "</b></div>"
            }
        } else r = i.result;
        $("#mainModalHeader").empty().append("Are you sure you want to cancel?"), $("#mainModalAcceptBtn").empty().append("Cancel Booking").css({
            display: "inline"
        }), $("#mainModalCloseBtn").empty().append("Not Yet").css({
            display: "inline"
        }), $("#mainModalBody").empty().append("Are you sure you want to CANCEL this booking for " + e.parents("tr").first().find("td").first().text() + "? " + (n.approved ? "Any deposit you paid is non-refundable and the venue's refund policy (described on your invoice) dictates how much you can be refunded." : "") + "<BR><BR><div id='refundDetails' class='alert'>" + r + "</div><textarea class='form-control' style='width:90%;height:200px' id='reservationCancelReason' placeholder='Please provide a reason for cancelling this booking...'></textarea>"), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            if ($("#reservationCancelReason").val().length < 3) $("#reservationCancelReason").before("<div class='alert alert-danger'>Please provide a reason for cancelling this booking</div>");
            else {
                var n = {
                    method: "fUpdateBookingStatus",
                    bookingid: t,
                    venueid: 0,
                    action: "cancel",
                    message: $("#reservationCancelReason").val(),
                    isVenue: 0
                };
                Post(n).then(function(e) {
                    e["result"] == "success" ? ($("tr[data-booking=" + t + "]").first().find("td:last").empty(), $("tr[data-booking=" + t + "]").first().find("td:nth-last-child(2)").empty().append("Cancelled by User"), $("#mainModalBody").empty(), $("#mainModal").modal("hide")) : $("#mainModalBody").prepend("<div class='alert alert-danger'>" + e.result + "</div>")
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    })
}

function CancelBookingVenue(e) {
    var t = e.parents("tr").first(),
        n = t.attr("data-booking");
    $("#mainModalHeader").empty().append("Are you sure you want to cancel?"), $("#mainModalAcceptBtn").empty().append("Cancel Booking").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Not Yet").css({
        display: "inline"
    }), $("#mainModalBody").empty().append((t.hasClass("pastDue") && t.hasClass("retainable") ? "Payment for this booking is <b>past due</b>. You may retain any deposit paid for this booking, or you may give a full refund to the customer.<br><br> <input id='retain' type='checkbox' checked=true> Retain <span id='retainAmount'></span> in deposit / cancellation fees" : e.parents("tr").first().find("td").first().text() + " will be refunded in full (including deposit) if you cancel this booking.") + "<br><br><textarea class='form-control' style='width:90%;height:200px' id='reservationCancelReason' placeholder='Please provide a reason for cancelling this booking...'></textarea>"), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
        if ($("#reservationCancelReason").val().length < 3) $("#reservationCancelReason").before("<div class='alert alert-danger'>Please provide a reason for cancelling this booking</div>");
        else {
            var r = {
                method: "fUpdateBookingStatus",
                bookingid: n,
                venueid: localStorage.getItem("activeProfile"),
                action: $("#retain:checked").length > 0 ? "cancel_keep" : "cancel",
                message: $("#reservationCancelReason").val(),
                isVenue: 1
            };
            Post(r).then(function(e) {
                e["result"] == "success" ? t.remove() : $("#mainModalBody").prepend("<div class='alert alert-danger'>" + e.result + "</div>"), $("#mainModalBody").empty(), $("#mainModal").modal("hide")
            })
        }
    }), $("#mainModalCloseBtn").off("click").click(function(e) {
        $("#mainModalBody").empty(), $("#mainModal").modal("hide")
    });
    if (t.hasClass("pastDue")) {
        var r = {
            method: "fCalculateRefund",
            bookingid: n
        };
        Post(r).then(function(e) {
            if (e["result"] == "success") {
                var t = e.data,
                    n = parseFloat(t.paid) - parseFloat(t.refund_amount);
                n < 0 && (n = 0), $("#retainAmount").text(FormatDollars(n, t.currency))
            }
        })
    }
}

function GetVenuePendingRefunds() {
    var e = {
        method: "fGetVenueRefunds",
        venueid: localStorage.getItem("activeProfile")
    };
    Post(e).then(function(e) {
        if (e["result"] == "success") {
            var t = "<legend>Pending Refunds</legend><table class='table table-striped table-hover table-condensed table-bookings dtable' id='venueRefundsPendingTable'><thead><tr><th>Organizer</th><th class='no-display-tab'>Start Date</th><th class='no-display-tab'>Stop Date</th><th class='no-display-mob'>Duration</th><th>Total</th><th>Refund Amount</th><th>Action</th></tr></thead><tbody>";
            r = e.data;
            for (var n = 0; n < r.length; n++) {
                var i = "" + Math.ceil((r[n].stop - r[n].start) / 3600 * 100) / 100 + " hrs";
                t += "<tr data-booking='" + SanitizeAttr(r[n].id) + "'><td>" + r[n].user + "</td><td class='no-display-tab'>" + FormatDate(parseInt(r[n].start), "MMMM D, YYYY h:mma z") + "</td><td class='no-display-tab'>" + FormatDate(parseInt(r[n].stop), "MMMM D, YYYY h:mma z") + "</td><td class='no-display-mob'>" + i + "</td><td>" + FormatDollars(r[n].total, r[n].currency) + "</td><td>" + FormatDollars(-1 * r[n].refund, r[n].currency) + "</td><td><button name='btnRefund' class='btn btn-xs btn-primary'>Refund</button></td></tr>"
            }
            t += "</tbody></table><br>", r.length > 0 && $("#tableMessages").parent().prepend(t)
        }
        ReBindBookingsTable($("#venueRefundsPendingTable"))
    })
}

function GetVenueNotifications() {
    var e = {
        method: "fGetVenueNotifications",
        venueid: localStorage.getItem("activeProfile")
    };
    Post(e).then(function(e) {
        if (e["result"] == "success") {
            $(".venueAlert").remove();
            for (var t = 0; t < e.data.length; t++) typeof e.data[t].message != "undefined" && typeof e.data[t]["class"] != "undefined" ? $("#main-content").prepend("<div class='venueAlert alert " + e.data[t]["class"] + "'>" + e.data[t].message + "</div>") : typeof e["data"][t]["guide"] != "undefined" && ($(".venue-guide-widget").length == 0 ? ($("#dashboardContainer").before("<div class='venue-guide-widget'></div>"), $(".venue-guide-widget").guideWidget(e.data[t].guide)) : $(".venue-guide-widget")[0].guideWidget.LoadGuide())
        }
    })
}

function GenerateSalesReport(e, t) {
    var n = {
        method: "fGenerateSalesReport",
        venueid: localStorage.getItem("activeProfile"),
        start: e,
        stop: t
    };
    Post(n).then(function(e) {
        if (e["result"] == "success") {
            results = e.data;
            var t = 0,
                n = [],
                r = 0,
                i = 0,
                s = 0;
            for (var o = 0; o < results.length; o++) {
                var u = "<tr data-booking='" + SanitizeAttr(results[o].bookingID) + "'><td>" + FormatDateTimeTz(parseInt(results[o].date), "MMMM D, YYYY", results[o].timezone) + "</td>" + "<td>" + results[o].customer + "</td><td>" + "" + Math.ceil(results[o].duration / 3600 * 100) / 100 + " hrs</td>" + "<td>" + FormatDollars(results[o].total, results[o].currency) + "</td><td>" + FormatDollars(results[o].income, results[o].currency) + "</td>" + "<td>" + results[o].status + "</td></tr>";
                $("table.table-sales tbody").append(u), t++, $.inArray(results[o].customer, n) < 0 && n.push(results[o].customer), r += results[o].duration, i += results[o].total, s += results[o].income
            }
            results.length > 0 ? ($("div.salesHeader div.col-md-4").empty().append("Statistics for period<br>" + $("#salesStartDate").val() + " - " + $("#salesStopDate").val()), $("div.salesHeader div.col-md-2:nth(0)").empty().append("" + t + " Bookings"), $("div.salesHeader div.col-md-2:nth(1)").empty().append("" + n.length + " Customers"), $("div.salesHeader div.col-md-2:nth(2)").empty().append("" + FormatDollars(i, results[0].currency) + " Potential"), $("div.salesHeader div.col-md-2:nth(3)").empty().append("" + FormatDollars(s, results[0].currency) + " Actual")) : $("table.table-sales").after("<div class='alert' style='text-align:center;margin:-10px auto 20px auto'><B>No results found for specified dates</B></div>")
        } else $("table.table-sales").after("<div class='alert alert-error' style='text-align:center;margin:-10px auto 20px auto'>" + e.result + "</div>");
        ReBindBookingsTable($("table.table-sales")), $("table.table-sales tr.totals td").off("click")
    })
};