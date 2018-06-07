function ValidatePayment(e) {
    var t = [];
    if ($("#reservationPayAmount").val().length < 1) return t.push("You have not specified the payment amount you wish to make"), t;
    var n = $("#reservationPayAmount").val();
    return n = parseInt(parseFloat(n.replace(/[^0-9.]/g, "")) * 100) / 100, (!n || n <= 0) && t.push("Payment amount must be greater than 0"), n > parseFloat(e.cost) - parseFloat(e.paid) && t.push("You are trying to pay more than you owe"), t
}

function PopulatePaymentInfo(e) {
    var t = new $.Deferred,
        n = {
            method: "fLoadPaymentInfo",
            bookingid: e
        };
    return Post(n).then(function(e) {
        if (e["result"] != "success") $("div.reservationTitle").before("<div class='alert alert-danger'>" + e.result + "</div>"), $("#btnPDF").hide();
        else {
            $("#reservationError").css({
                display: "none"
            });
            var n = e.data;
            CreatePaymentPage(n)
        }
        t.resolve()
    }), t.promise()
}

function CreatePaymentPage(e) {
    var t = $("<div class='row no-padding-small'>"),
        n = $("<div class='col-sm-7 no-padding-small'><table class='table table-striped table-condensed table-payments'><thead><tr><th>Payment Date</th><th>Paid By</th><th>Amount</th><th>Status</th></thead><tbody></tbody></table></div>");
    if (e && e.payments)
        for (var r = 0; r < e.payments.length; r++) {
            var i = e.payments[r].status;
            switch (e.payments[r].status) {
                case "processed":
                    e.payments[r].last4.length < 1 && (i = "processed offline"), e.payments[r].method.length > 0 && (i += " (" + e.payments[r].method.replace(/^m_+/, "") + ")");
                    break;
                case "pending_refund":
                    i = "pending refund"
            }
            e.payments[r].last4.length > 0 && (i += " (..." + e.payments[r].last4 + ")"), n.find("tbody").first().append("<tr><td>" + FormatDateTime(parseInt(e.payments[r].time), "MMMM D, YYYY", e.timezone) + "</td><td>" + e.payments[r].name + "</td><td class='nowrap'>" + FormatDollars(e.payments[r].amount, e.currency) + "</td><td>" + i + "</td></tr>")
        }
    var s = Math.ceil(parseFloat(e.cost) * 100 - parseFloat(e.paid) * 100) / 100,
        o = Math.ceil(parseFloat(e.deposit) * 100 - parseFloat(e.paid) * 100) / 100;
    o < 0 && (o = 0);
    var u = (new Date).getTime() / 1e3,
        a = "",
        f = "";
    if (s > e.cost) {
        s = e.cost
    }
    parseInt(e.full) - u < 7200 && (a = " style='color:red' "), e["cost"] == e["deposit"] ? ($("h5:contains('In order to secure')").remove(), window.location.href.indexOf("/booking") > 0 && window.location.href.indexOf("/pay") > 0 && $(".bookHeading").append("<h5 style='text-align:center;margin:30px auto'>In order to secure your reservation time you must pay in full, and then the venue will review and confirm your reservation.</h5>")) : o > 0 && (f = " style='color:red' ", $("h5:contains('In order to secure')").remove(), window.location.href.indexOf("/booking") > 0 && window.location.href.indexOf("/pay") > 0 && $(".bookHeading").append("<h5 style='text-align:center;margin:30px auto'>In order to secure your reservation time you must make a deposit. After your deposit is paid the venue will review and confirm your reservation.</h5>"));
    var l = "<div id='reservationPayment'><button id='reservationPayNow' class='btn btn-lg btn-primary'>Pay Now</button>";
    e["imvenue"] == 1 && (l += "<button id='reservationRecordPayment' class='btn btn-lg btn-default'>Record Payment</button>"), l += "</div>", (e["status"].indexOf("Pending Deposit") == 0 || e["status"].indexOf("Pending Approval") == 0 || e["status"].indexOf("Pending Payment") == 0) && n.append(l);
    var c = "<div class='col-sm-5 no-padding-small'><table class='table table-striped table-condensed'><thead><th colspan='2'>Booking Payment Overview</th></thead><tr><td>Payments Received:</td><td>" + FormatDollars(e.paid, e.currency) + "</td></tr>";
    if (e["status"].indexOf("Pending Deposit") == 0 || e["status"].indexOf("Pending Payment") == 0 || e["status"].indexOf("Pending Approval") == 0) c += "<tr><td>Payments Remaining:</td><td>" + FormatDollars(s, e.currency) + "</td></tr>" + (o > 0 && parseFloat(e.cost) > parseFloat(e.deposit) && a.length < 1 ? "<tr><td>Deposit Remaining:</td><td>" + FormatDollars(o, e.currency) + "</td></tr>" : "") + "<tr" + a + "><td>Full Payment Due By:</td><td>" + (a.length > 0 ? "<b>Now</b>" : FormatDateTimeTz(parseInt(e.full), "MMMM D, YYYY", e.timezone)) + "</td></tr>";
    c += "</table><div class='clearfix' style='height:10px'></div></div>", t.append(c), t.append(n), t.append("<script src='https://checkout.stripe.com/v2/checkout.js' type='text/javascript'></script>"), $("#sectionPaymentDetails").empty().append(t), (!e.payments || e.payments.length < 1) && t.find(".table-payments").first().append("<tr><td colspan=10 style='text-align:center'>No payments have been made yet</tr></td>"), s <= 0 && (window.location.href.indexOf("/pay") > 0 && $("table.table-payments").after("<div class='alert alert-success text-center' style='margin-top:15px'>This reservation has been paid in full. You can view the complete reservation details in " + (localStorage.getItem("venueRights") ? "<a data-partial=true href='/dashboard/bookings'>your dashboard</a>" : "<a href='/login'>your dashboard</a>") + "</div>"), $("#reservationPayment").hide()), $("#reservationPayOffline").on("click", function(t) {
        t.preventDefault(), $("#reservationPayment div.alert").remove(), $("#reservationPayment").append("<div class='clearfix'></div><div class='offlinePaymentInstructions alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>You may contact the venue at <span style='white-space:nowrap'>" + e.phone + "</span> and reference Reservation #" + e.id + " to make alternate payment arrangements</div>")
    }), $("#reservationPayNow").off("click").click(function(t) {
        t.preventDefault(), $("#reservationPayment div.alert").remove(), $("#divPaymentAmount").remove(), $("#reservationPayment button").hide();
        var n = "<div class='clearfix'></div><div id='divPaymentAmount' class='panel' style='margin-top:5px;'><div class='panel-body' style='text-align:center'><label class='control-label' style='margin-right:10px'>Amount to pay: </label><input type='text' class='form-control' style='width:150px;display:inline-block' id='reservationPayAmount' value='" + FormatDollars(s, e.currency) + "'/><button id='reservationPayAmountNow' class='btn btn-sm btn-primary'>Pay</button><button id='reservationPayAmountCancel' class='btn btn-sm btn-default'>Cancel</button></div></div>";
        $("#reservationPayment").append(n), $("#reservationPayAmountCancel").on("click", function(e) {
            e.preventDefault(), $("#reservationPayment button").show(), $("#divPaymentAmount").remove(), $("#reservationPayment div.alert").remove()
        }), $("#reservationPayAmountNow").on("click", function(t) {
            t.preventDefault();
            var n = ValidatePayment(e);
            if (n.length > 0) {
                var r = $("<div class='alert alert-danger'></div>");
                for (var i = 0; i < n.length; i++) r.append(n[i] + "<br>");
                $("#divPaymentAmount panel-body:first").append(r)
            } else {
                var s = e.processingfee * 100,
                    o = parseFloat($("#reservationPayAmount").val().replace(/[^0-9.]/g, "")),
                    u = Date.now(),
                    a = confirm("There is a " + s + "% processing fee for credit card payments.  Do you accept this fee?");
                if (a == 1) {
                    var f = function(t) {
                        SubmitPayment(e.id, o, t.id), $("#reservationPayment button").show(), $("#divPaymentAmount").remove(), $("#divPaymentAmount div.alert").remove()
                    };
                    StripeCheckout.open({
                        key: "pk_live_bqrhEtEGEZ9pLTy5zS1gEBLY",
                        address: !1,
                        amount: parseInt(Math.ceil((1 + parseFloat(e.processingfee)) * o * 100)),
                        currency: e.currency,
                        name: "InviteBIG",
                        description: "InviteBIG booking payment",
                        panelLabel: "Pay",
                        token: f
                    })
                }
            }
        })
    }), $("#reservationRecordPayment").off("click").click(function(t) {
        t.preventDefault(), $("#reservationPayment div.alert").remove(), $("#reservationPayment button").hide();
        var n = "<div class='clearfix'></div><div id='divRecordPayment' class='panel' style='margin-top:5px'><div class='panel-body'><label class='control-label'>Payer&apos;s Name</label><input type='text' class='form-control' id='textPaymentName' placeholder='Name of the person making the payment'><label class='control-label'>Amount</label><input type='text' class='form-control' id='textPaymentAmount' placeholder='Amount paid'><label class='control-label'>Payment Method</label><select id='selPaymentMethod' class='form-control'><option value='cash'>Cash</option><option value='check'>Check</option><option value='credit'>Credit</option><option value='invoice'>Invoice</option><option value='other'>Other</option></select><br><button id='recordPayment' class='btn btn-sm btn-success' style='margin:5px'>Submit Payment</button> <button id='cancelRecordPayment' class='btn btn-sm btn-danger' style='margin:5px' >Cancel</button></div></div>";
        $(this).parent().append(n), $("#recordPayment").off("click").click(function(t) {
            t.preventDefault();
            var n = 0;
            $("#textPaymentAmount").val($("#textPaymentAmount").val().replace(/\$|,/g, "")), $("#textPaymentName").css({
                border: "none"
            }), $("#textPaymentAmount").css({
                border: "none"
            }), $("#textPaymentName").val().length < 2 && ($("#textPaymentName").css({
                border: "1px solid #F88"
            }), n++);
            if (/[^0-9.]/.test($("#textPaymentAmount").val()) == 1 || $("#textPaymentAmount").val().length < 1) $("#textPaymentAmount").css({
                border: "1px solid #F88"
            }), n++;
            if (n == 0) {
                $("#divRecordPayment").loading();
                var r = {
                    method: "fRecordPayment",
                    bookingid: e.id,
                    name: $("#textPaymentName").val(),
                    type: $("#selPaymentMethod option:selected").attr("value"),
                    amount: $("#textPaymentAmount").val(),
                    currency: e.currency
                };
                Post(r).then(function(t) {
                    t["result"] == "success" && PopulatePaymentInfo(e.id), $("#reservationPayment button").show(), $("#divRecordPayment").loadingDone()
                })
            }
        }), $("#cancelRecordPayment").off("click").click(function(e) {
            e.preventDefault(), $("#divRecordPayment").remove(), $("#reservationPayment button").show()
        })
    }), $("#reservationPayment").submit(function(e) {
        e.preventDefault()
    })
}

function SubmitPayment(e, t, n) {
    var r = {
        method: "fSubmitPayment",
        bookingid: e,
        token: n,
        amount: t
    };
    $("#sectionPaymentDetails").loading(), Post(r).then(function(e, t) {
        $("#sectionPaymentDetails").loadingDone(), e["result"] == "success" ? ReloadBookingDetails(t.bookingid) : $("table.table-payments tbody").append("<tr><td>Just Now</td><td></td><td>" + t.amount + "</td><td>Failed</td></tr>")
    })
}

function ActivateBookingAccordion() {
    $("div.bookingDetailsSectionBody").css({
        display: "none"
    }), $("div.bookingDetailsSectionHeader").append("<i class='glyphicon glyphicon-chevron-down pull-right' name='chevron' style='margin:2px 10px 0 0'></i>"), $("div.bookingDetailsSectionHeader").off("click").click(function(e) {
        e.preventDefault(), $(this).next("div.bookingDetailsSectionBody").css("display") == "none" ? ($(this).next("div.bookingDetailsSectionBody").css({
            display: "block"
        }), $(this).find("i.glyphicon").removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-up")) : ($(this).next("div.bookingDetailsSectionBody").css({
            display: "none"
        }), $(this).find("i.glyphicon").removeClass("glyphicon-chevron-up").addClass("glyphicon-chevron-down"))
    }), $("#sectionMessages").click(function(e) {
        var t = {
            method: "fMarkBookingMessagesRead",
            bookingid: $("#reservationTitle").attr("data-id")
        };
        Post(t).then(function(e) {
            e["result"] == "success" && $("table.table-bookings").find("tr[data-booking='" + SanitizeAttr(t.bookingid) + "']").css({
                "font-weight": "normal",
                "background-color": "initial"
            })
        })
    })
}

function PopulateBookingMessages(e) {
    var t = e.messages;
    $("#sectionMessageDetails").empty().append("<div class='panel'><div class='panel-body newMessage'>          <h4>New Message:</h4>           <textarea class='form-control' style='width:80%;height:50px;margin-bottom:5px;display:inline-block' id='textNewMessage' placeholder='Type your message here...'></textarea>         <button class='btn btn-sm btn-primary' id='buttonNewMessage' style='vertical-align:top'>Send Message</button>       </div></div>        <table id='tableMessageDetails' class='table table-condensed table-messagedetails'>         <thead><tr><th>From</th><th>Message</th><th>Time</th></tr></thead>          <tbody></tbody>     </table>");
    for (var n = 0; n < t.length; n++) style = "", t[n]["isvenue"] == 1 && (style = " class='venuemessage'"), t[n]["isnew"] == 1 && $("#sectionMessages span.badge").css("display", "inline-block"), $("#tableMessageDetails").find("tbody").first().append("<tr " + style + "><td>" + t[n].from + "</td><td>" + t[n].message + "</td><td>" + FormatDateTime(parseInt(t[n].time), "MMMM D, YYYY", e.timezone) + "</td></tr>");
    t.length == 0 && $("#tableMessageDetails").find("tbody").first().append("<tr><td colspan=10 style='text-align:center'>No messages have been received</td></tr>"), $("#buttonNewMessage").off("click").click(function(e) {
        e.preventDefault();
        if ($("#textNewMessage").val().length > 0) {
            var t = {
                method: "fSendBookingMessage",
                bookingid: $("#reservationTitle").attr("data-id"),
                text: $("#textNewMessage").val()
            };
            Post(t).then(function(e) {
                if (e["result"] != "success") {
                    var t = "<div class='alert alert-danger'><ul><li>" + e.result + "</li></ul></div>";
                    $("#buttonNewMessage").after(t)
                } else $("#tableMessageDetails").find("tbody").first().prepend("<tr style='background-color:#DEF'><td>Me</td><td>" + $("#textNewMessage").val() + "</td><td>Just Now</td></tr>")
            })
        }
    })
}

function PopulateBookingFiles(e) {
    var t = e.files,
        n = "";
    parseInt(e.userCanUpload) > 0 && (n += "<div id='bookingFileUpload' class='panel'><div class='panel-body'><h4>Upload a new file</h4><input type='file' id='bookingFile' style='margin-right:20px' multiple/>" + (e.imvenue ? "<div style='display:inline-block'><input type='checkbox' id='bookingFilePrivate'/> Private</div>" : "") + "<br><input type='text' id='bookingFileDesc' style='width:300px;margin-right:20px' placeholder='Description...'/><input type='submit' id='btnFileUpload' class='btn btn-sm btn-success' value='Upload'/></div></div>"), n += "<table class='table table-condensed table-striped'><thead><tr><th style='padding:0;margin:0'></th><th>Filename</th><th>Description</th><th>Added By</th><th>Date</th></tr></thead><tbody>";
    for (var r = 0; r < t.length; r++) n += "<tr data-fileid='" + SanitizeAttr(t[r].fileid) + "'><td style='padding:0;margin:0'>" + (t[r].canDel ? "<button class='btn btn-xs btn-danger btnDel'><i class='fa fa-trash'></i></button>" : "") + "</td><td><a target='_blank' download href='/assets/content/" + t[r].name + "'>" + t[r].name + "</a></td><td>" + t[r].desc + (t[r]["private"] ? " <small style='font-style:italics'>(venue only)</small>" : "") + "</td><td>" + t[r].user + "</td><td>" + FormatDate(t[r].date, "MMM D, YYYY") + "</td></tr>";
    t.length == 0 && (n += "<tr><td colspan=10 style='text-align:center'>No files have been attached</td></tr>"), n += "</tbody></table>", $("#sectionBookingFiles").empty().append(n);
    var t;
    $("#bookingFile").on("change", function(e) {
        t = e.target.files
    }), $("#btnFileUpload").on("click", function(n) {
        n.preventDefault();
        var r = new FormData;
        $("#bookingFileUpload div.alert-error").remove();
        for (var i = 0; i < t.length; i++) t[i].name.match(/.(jpg)|(gif)|(png)|(bmp)|(pdf)|(doc)|(docx)|(jpeg)|(rtf)|(txt)|(csv)|(xml)|(pps)|(ppt)|(pptx)$/) || $("#bookingFileUpload").append("<div class='alert alert-danger'>This file type is not allowed</div>"), r.append("files[]", t[i], t[i].name);
        var s = 0;
        $("#bookingFilePrivate").prop("checked") && (s = 1);
        var o = {
            method: "fUploadBookingFile",
            bookingid: e.id,
            desc: $("#bookingFileDesc").val(),
            priv: s,
            auth: localStorage.getItem("auth")
        };
        r.append("request", JSON.stringify(o)), PostFiles(r).then(function(e) {
            e.auth && localStorage.setItem("auth", e.auth);
            if (e["result"] != "success") {
                var t = "<div class='alert alert-danger'><ul><li>" + e.result + "</li></ul></div>";
                $("#bookingFileUpload").append(t)
            } else {
                $("#sectionBookingFiles table tbody tr").each(function() {
                    var e = !1;
                    $(this).find("td").each(function() {
                        $(this).text().indexOf("No files have been attached") >= 0 && (e = !0)
                    }), e == 1 && $(this).remove()
                });
                var n = e.data,
                    t = "";
                for (var r = 0; r < n.length; r++) t += "<tr><td style='padding:0;margin:0'><button class='btn btn-xs btn-danger btnDel'><i class='fa fa-trash'></i></button></td><td><a target='_blank' download href='/assets/content/" + n[r] + "'>" + n[r] + "</a></td><td>" + $("#bookingFileDesc").val() + (s ? " <small style='font-style:italics'>(venue only)</small>" : "") + "</td><td>me</td><td>Just now</td></tr>";
                $("#sectionBookingFiles table tbody").append(t), $("#bookingFileUpload input[type='file']").val("")
            }
        })
    }), $("#sectionBookingFiles").find("button.btnDel").on("click", function(e) {
        e.preventDefault();
        var t = $(this).parents("tr").first(),
            n = t.attr("data-fileid"),
            r = {
                method: "fDeleteBookingFile",
                file: n
            };
        Post(r).then(function(e) {
            if (e["result"] != "success") {
                var n = "<div class='alert alert-danger'><ul><li>" + e.result + "</li></ul></div>";
                $("#sectionBookingFiles").prepend(n)
            } else t.remove()
        })
    })
}

function PopulateBookingDetails(e, t) {
    var n = new $.Deferred,
        r = {
            method: "fLoadBooking",
            bookingid: e
        };
    return Post(r).then(function(e) {
        console.log("booking details data", e);
        if (e["result"] != "success") $("div.ReservationOrderError").length > 0 ? $("div.ReservationOrderError").empty().append(e.result) : ($("#reservationTitle").before("<div class='alert alert-danger'>" + e.result + "</div>"), $("#btnPDF").hide()), n.fail(), ActivateBookingAccordion();
        else {
            $("#ReservationOrderError").css({
                display: "none"
            });
            var r = CreateInvoice(e.data);
            PopulateBookingMessages(e.data), PopulateBookingFiles(e.data), PopulateQuestions(e.data), t == 1 && PopulateBookVenueData(e.data), $.when(r).then(function() {
                n.resolve()
            })
        }
    }), n.promise()
}

function ReloadBookingDetails(e) {
    var t = new $.Deferred,
        n = {
            method: "fLoadBooking",
            bookingid: e
        };
    return Post(n).then(function(e) {
        console.log("booking details data", e);
        if (e["result"] == "success") {
            var n = CreateInvoice(e.data);
            PopulateBookingMessages(e.data), PopulateBookingFiles(e.data), PopulateQuestions(e.data), $.when(n).then(function() {
                t.resolve()
            })
        }
    }), t.promise()
}

function PopulateQuestions(e) {
    e.info && ($("#reservationEventName").val(e.info.name).attr("disabled", "disabled"), $("#reservationDescription").val(e.info.description).attr("disabled", "disabled"), $("#reservationGroupSize").val(e.headcount).attr("disabled", "disabled"), $("#reservationComments").val(e.info.comments).attr("disabled", "disabled"), $("#reservationContactName").val(e.info.contact_name).attr("disabled", "disabled"), $("#reservationContactCompany").val(e.info.contact_company).attr("disabled", "disabled"), $("#reservationContactTitle").val(e.info.contact_title).attr("disabled", "disabled"), $("#reservationContactPhone").val(e.info.contact_phone).attr("disabled", "disabled"), $("#reservationContactEmail").val(e.info.contact_email).attr("disabled", "disabled"), $("#reservationContactWebsite").val(e.info.contact_website).attr("disabled", "disabled"));
    if (e.questions)
        for (var t = 0; t < e.questions.length; t++) {
            var n = e.questions[t].answer;
            n == "checked" && e["questions"][t]["type"] == "checkbox" && (n = "<i class='glyphicon glyphicon-ok'/>"), $("#sectionQuestions").next("div.bookingDetailsSectionBody").find("table tbody").append("<tr><td>" + e.questions[t].question + "</td><td>" + n + "</td></tr>")
        }
}

function CreateInvoice(e) {
    var t = [],
        n = e.address.split(","),
        r = "";
    for (var i = 0; i < n.length - 2; i++) r += n[i] + ", ";
    r = r.replace(/,([^,]*)$/g, "") + "<BR>";
    for (var i = n.length - 2; i < n.length; i++) r += n[i] + ", ";
    window.location.href.indexOf("/reserve/") < 0 && $("#reservationTitle").empty().attr("data-id", e.id).append("<h3>Reservation " + (e.id ? "#" + e.id : "") + " for " + e.name + "</h3>"), $("#reservationVenueName").empty().append(e.name), $("#reservationVenueAddress").empty().append(r.replace(/,([^,]*)$/g, "")), $("#reservationVenuePhone").empty().append(e.phone), $("#reservationVenueWebsite").empty().append(e.website), $("#sectionResources").parents("div.bookingDetailsSection").first().find("tbody").empty(), $("#sectionAddons").parents("div.bookingDetailsSection").first().find("tbody").empty(), $("#sectionMenus").parents("div.bookingDetailsSection").first().find("tbody").empty(), $("#sectionPersonnel").parents("div.bookingDetailsSection").first().find("tbody").empty(), $("#sectionPromos").parents("div.bookingDetailsSection").first().find("tbody").empty();
    var s = 0,
        o = 0,
        u = 0,
        a = 0,
        f = 0,
        l = 0,
        c = 0,
        h = 0,
        p = parseFloat(e.deposit),
        d = (new Date).getTime() / 1e3,
        v = d + 7200 - e.full;
    for (var i = 0; i < e.resources.length; i++) {
        var m = "",
            g = 0,
            y = e.resources[i].start,
            b = e.resources[i].stop;
            console.log("e_timezone:-",e.timezone);
        m = FormatDateTime(y, "MMMM D, YYYY", e.timezone) + " - " + FormatDateTimeTz(b, "MMMM D, YYYY", e.timezone), g += (b - y) / 60;
        var w = "<tr><td>" + e.resources[i].name + "</td><td>" + m + "</td>" + "<td>" + FormatDollars(e.resources[i].cost, e.currency) + "</td></tr>",
            E = $("#sectionResources").parents("div.bookingDetailsSection").first().find("tbody").first();
        E.length < 1 && (E = $("#sectionResources").find("tbody").first()), E.length > 0 && E.append(w), s += parseFloat(e.resources[i].cost), f += parseFloat(e.resources[i].cleanupcost), t.indexOf(e.resources[i].refund_policyid) < 0 && t.push(e.resources[i].refund_policyid);
        for (var S = 0; S < e.resources[i].addons.length; S++) {
            var x = "";
            e.resources[i].addons[S].deliverat && (x = FormatDateTimeTz(e.resources[i].addons[S].deliverat, "MMMM D, YYYY", e.timezone));
            var w = "<tr><td>" + e.resources[i].addons[S].name + "</td><td>" + e.resources[i].name + "</td><td>" + x + "</td>" + "<td>" + e.resources[i].addons[S].quantity + "</td>" + "<td>" + FormatDollars(e.resources[i].addons[S].price, e.currency) + "</td><td>" + FormatDollars(e.resources[i].addons[S].cost, e.currency) + "</td></tr>",
                E = $("#sectionAddons").parents("div.bookingDetailsSection").first().find("tbody").first();
            E.length < 1 && (E = $("#sectionAddons").find("tbody").first()), E.length > 0 && E.append(w), o += parseFloat(e.resources[i].addons[S].cost), t.indexOf(e.resources[i].addons[S].refund_policyid) < 0 && t.push(e.resources[i].addons[S].refund_policyid)
        }
    }
    var T = !0;
    $("#sectionAddons").find("tbody tr").each(function() {
        $(this).find("tr:nth(2)").text().length > 0 && (T = !1)
    }), T && ($("#sectionAddons th:nth(2)").remove(), $("#sectionAddons").find("tbody tr").each(function() {
        $(this).find("td:nth(2)").remove()
    }));
    for (var i = 0; i < e.menus.length; i++) {
        var x = "";
        e.menus[i].deliverat && (x = FormatDateTimeTz(e.menus[i].deliverat, "MMMM D, YYYY", e.timezone));
        for (var S = 0; S < e.menus[i].items.length; S++) {
            var w = "<tr><td>" + e.menus[i].items[S].name + "</td><td>" + x + "</td>" + "<td>" + e.menus[i].items[S].quantity + "</td>" + "<td>" + FormatDollars(e.menus[i].items[S].price, e.currency) + "</td><td>" + FormatDollars(e.menus[i].items[S].cost, e.currency) + "</td></tr>",
                E = $("#sectionMenus").parents("div.bookingDetailsSection").first().find("tbody").first();
            E.length < 1 && (E = $("#sectionMenus").find("tbody").first()), E.length > 0 && E.append(w)
        }
        u += parseFloat(e.menus[i].cost), t.indexOf(e.menus[i].refund_policyid) < 0 && t.push(e.menus[i].refund_policyid)
    }
    T = !0, $("#sectionMenus").find("tbody tr").each(function() {
        $(this).find("tr:nth(1)").text().length > 0 && (T = !1)
    }), T && ($("#sectionMenus th:nth(1)").remove(), $("#sectionMenus").find("tbody tr").each(function() {
        $(this).find("td:nth(1)").remove()
    }));
    for (var i = 0; i < e.personnel.length; i++) {
        var w = "<tr><td>" + e.personnel[i].name + "</td>" + "<td>" + e.personnel[i].quantity + "</td>" + "<td>" + FormatDollars(e.personnel[i].price, e.currency) + "/hr</td><td>" + FormatDollars(e.personnel[i].cost, e.currency) + "</td></tr>",
            E = $("#sectionPersonnel").parents("div.bookingDetailsSection").first().find("tbody").first();
        E.length < 1 && (E = $("#sectionPersonnel").find("tbody").first()), E.length > 0 && E.append(w), a += parseFloat(e.personnel[i].cost), t.indexOf(e.personnel[i].refund_policyid) < 0 && t.push(e.personnel[i].refund_policyid)
    }
    if (e.promos)
        for (var i = 0; i < e.promos.length; i++) {
            if (!e.promos[i].desc) continue;
            var w = "<tr><td>" + e.promos[i].name + "</td><td>" + FormatShort(FormatDescription(e.promos[i].desc)).substring(0, 300) + "</td><td>" + FormatDollars(e.promos[i].discountAmount, e.currency) + "</td></tr>",
                E = $("#sectionPromos").parents("div.bookingDetailsSection").first().find("tbody").first();
            E.length < 1 && (E = $("#sectionPromos").find("tbody").first()), E.length > 0 && E.append(w)
        }
    var N = new $.Deferred,
        C = {
            method: "fGetRefundPolicies",
            policies: t,
            fromBooking: window.location.href.indexOf("/booking") > 0 ? e.id : !1
        };
    Post(C).then(function(t) {
        if (t["result"] == "success") {
            var n = "<h4>Venue Booking Terms</h4><hr style='margin:10px auto 5px auto'><b>This booking is subject to venue approval and may be refused or cancelled at any time.</b><br><br>Venue approval will be granted or denied within 72 hours of the request. If this booking is denied or cancelled by the venue then any payments made will be refunded in full.  If this booking is cancelled by you then it will be subject to the refund policies described below.  Refund rates are based on the full invoice price, before any discounts or adjustments have been applied.<br><br>";
            for (var r = 0; r < t.data.length; r++) {
                n += "<b>Refund Policy " + t.data[r].id + "</b><br>";
                var i = $.parseJSON(t.data[r].policy);
                i.length > 0 && i[0]["fee"] != "0" && (n += "Cancelled " + i[0].days + " day(s) or more prior to the event date: 100% refunded.<br>");
                for (var s = 0; s < i.length; s++) n += "Cancelled within " + i[s].days + " day(s) of the event: " + i[s].fee + "% fee.<br>";
                n += "This policy applies to:<br><ul>";
                var o = [];
                for (var u = 0; u < e.resources.length; u++) {
                    e["resources"][u]["refund_policyid"] == t["data"][r]["id"] && o.indexOf(e["resources"][u]["name"]) == -1 && o.push(e.resources[u].name);
                    for (var a = 0; a < e.resources[u].addons.length; a++) e["resources"][u]["addons"][a]["refund_policyid"] == t["data"][r]["id"] && o.indexOf(e["resources"][u]["addons"][a]["name"]) == -1 && o.push(e.resources[u].addons[a].name)
                }
                for (var u = 0; u < e.menus.length; u++) e["menus"][u]["refund_policyid"] == t["data"][r]["id"] && o.indexOf(e["menus"][u]["name"]) == -1 && o.push(e.menus[u].name);
                for (var u = 0; u < e.personnel.length; u++) e["personnel"][u]["refund_policyid"] == t["data"][r]["id"] && o.indexOf(e["personnel"][u]["name"]) == -1 && o.push(e.personnel[u].name);
                for (var u = 0; u < o.length; u++) n += "<li>" + o[u] + "</li>";
                n += "</ul><br>"
            }
            n += "The fee percentage is applied to the total booking price.  " + (p ? "The deposit is non-refundable.  " : "") + "If you cancel this reservation then you agree to pay the greater of the deposit amount or the cancellation fee at the time of cancellation.<br><br>", window.location.href.indexOf("/reserve") < 0 && typeof e.contract == "string" && e.contract.length > 0 && (n += "This booking is also subject to the <a id='bookingContract' href='/assets/content/" + e.contract + "' target='_blank'>Venue Booking Contract</a>.<br><br>", $("#btnContract").css({
                display: "inline-block"
            })), $("#reservationPolicyList").empty().append(n)
        }
        N.resolve()
    }), $("#reservationTotalsResources").empty().append(FormatDollars(s, e.currency)), $("#reservationTotalsAddons").empty().append(FormatDollars(o, e.currency)), $("#reservationTotalsMenus").empty().append(FormatDollars(u, e.currency)), $("#reservationTotalsPersonnel").empty().append(FormatDollars(a, e.currency)), $("#reservationTotalsCleanup").empty().append(FormatDollars(e.cleanupfee, e.currency)), $("#reservationTotalsCleanup").empty().append(FormatDollars(e.cleanupfee, e.currency)), $("#reservationTotalsBookingFee").empty().append(FormatDollars(e.bookingfee, e.currency)), $("#reservationTotalsProcessingFee").empty().append(FormatDollars(e.processingfee, e.currency)), $("#reservationTotalsPromos").empty().append("-" + FormatDollars(e.promos_total, e.currency)), $("#reservationTotalsTaxes").empty().append(FormatDollars((parseFloat(e.full_tax) * 100 + parseFloat(e.bookingfee_tax) * 100) / 100, e.currency)), $("#reservationTotalsGrand").empty().append(FormatDollars(e.cost, e.currency)), $("#reservationTotalsDeposit").empty().append(FormatDollars(p, e.currency)), $("#reservationTotalsGratuity").empty().append(FormatDollars(e.gratuity, e.currency)), $("#reservationTotalsAdjustment").empty().append(FormatDollars(e.adjustment, e.currency)), $("#reservationTotalsDue").empty().append(v > 0 ? "<b>Now</b>" : FormatDateTimeTz(parseInt(e.full), "MMMM D, YYYY", e.timezone)), e["bookingfee"] == 0 && $("#reservationTotalsBookingFee").parents("tr").first().remove(), e["processingfee"] == 0 && $("#reservationTotalsProcessingFee").parents("tr").first().remove(), p == 0 && $("#reservationTotalsDeposit").parents("tr").first().remove(), o == 0 && $("#sectionAddons table tbody tr").length == 0 && ($("#reservationTotalsAddons").parents("tr").first().remove(), $("#sectionAddons").parents("div.bookingDetailsSection").first().remove(), $("#sectionAddons").remove()), u == 0 && $("#sectionMenus table tbody tr").length == 0 && ($("#reservationTotalsMenus").parents("tr").first().remove(), $("#sectionMenus").parents("div.bookingDetailsSection").first().remove(), $("#sectionMenus").remove()), a == 0 && $("#sectionPersonnel table tbody tr").length == 0 && ($("#reservationTotalsPersonnel").parents("tr").first().remove(), $("#sectionPersonnel").parents("div.bookingDetailsSection").first().remove(), $("#sectionPersonnel").remove()), e["gratuity"] == 0 && $("#reservationTotalsGratuity").parents("tr").first().remove(), e["adjustment"] == 0 && $("#reservationTotalsAdjustment").parents("tr").first().remove(), e["cleanupfee"] == 0 && $("#reservationTotalsCleanup").parents("tr").first().remove();
    if (!e.promos || e.promos.length < 1) $("#sectionPromos").parents("div.bookingDetailsSection").first().remove(), $("#sectionPromos").remove(), $("#reservationTotalsPromos").parents("tr").first().remove();
    $.when(N).done(function() {
        return e.id ? PopulatePaymentInfo(e.id) : (new $.Deferred).resolve()
    })
};