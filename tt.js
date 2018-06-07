function RebindContractUpload() {
    $("form.contractupload").each(function() {
        $("form.contractupload span.fileinput-button").show(), $(this).fileupload({
            url: "/inc/jQuery-File-Upload-master/server/php/index.php",
            sequentialUploads: !0,
            limitMultiFileUploads: 1,
            maxNumberOfFiles: 1,
            maxFileSize: 8388608,
            acceptFileTypes: /(\.|\/)(doc|pdf)$/i,
            done: function(e, t) {
                t && t.result && t.result.files && t.result.files.length > 0 && ($("#cFiles").empty(), $("#cFiles").append("<div class='input-group'><span class='input-group-addon' style='background:#fefefe'><a href='" + SanitizeAttr(t.result.files[0].url) + "'>" + t.result.files[0].url + "</a></span><button class='btn btn-default' name='delContract'><i class='glyphicon glyphicon-trash'></i></button></div>"), $("form.contractupload span.fileinput-button").hide(), $("#cFiles [name='delContract']").off("click").click(function(e) {
                    e.preventDefault(), $("#cFiles").empty(), RebindContractUpload()
                }))
            }
        })
    })
}

function ValidateQuestion() {
    var e = 0;
    $("#selQuestionResources option:selected").length < 1 && $("#selQuestionAddons option:selected").length < 1 && $("#selQuestionMenus option:selected").length < 1 && $("#selQuestionPersonnel option:selected").length < 1 && ($("#errQuestion").append("<br><br>This question must apply to a resource, addon, menu, or personnel."), e = 1);
    switch ($("#selQuestionType option:selected").val()) {
        case "text":
        case "checkbox":
            $("#txtQuestionText").val().length < 1 && ($("#errQuestion").append("<br><br>Question field cannot be blank."), e = 1);
            break;
        case "radio":
            $("input[name='txtQuestionAnswer']").length < 2 && ($("#errQuestion").append("<br><br>There must be at least two answers to choose from."), e = 1);
            var t = 0;
            $("input[name='txtQuestionAnswer']").each(function() {
                $(this).val().length < 1 && t++
            }), t > 0 && ($("#errQuestion").append("<br><br>Answers cannot be blank."), e = 1);
            break;
        case "select":
            $("input[name='txtQuestionAnswer']").length < 2 && ($("#errQuestion").append("<br><br>There must be at least two answers to choose from."), e = 1);
            var t = 0;
            $("input[name='txtQuestionAnswer']").each(function() {
                $(this).val().length < 1 && t++
            }), t > 0 && ($("#errQuestion").append("<br><br>Answers cannot be blank."), e = 1)
    }
    return e > 0 ? ($("#errQuestion").css({
        display: "inline-block"
    }), !1) : !0
}

function RebindQuestionnaireControls() {
    $("#aQuestionAnswerAdd").off("click").click(function(e) {
        e.preventDefault(), $(this).before("<input name='txtQuestionAnswer' class='form-control' style='width:200px;display:inline-block;margin-top:5px' placeholder='Specify the option...'/> <a href='#' name='aQuestionAnswerDel'>Delete Answer</a><br>"), RebindQuestionnaireControls()
    }), $("a[name='aQuestionAnswerDel']").off("click").click(function(e) {
        e.preventDefault(), $(this).prev("input").remove(), $(this).next("br").remove(), $(this).remove()
    }), $("i[name='btnQuestionUp']").off("click").click(function(e) {
        e.preventDefault();
        var t = $(this).parents("tr").first(),
            n = t.prev("tr");
        n.length > 0 && n.before(t)
    }), $("i[name='btnQuestionDown']").off("click").click(function(e) {
        e.preventDefault();
        var t = $(this).parents("tr").first(),
            n = t.next("tr");
        n.length > 0 && n.after(t)
    }), $("i[name='btnQuestionDel']").off("click").click(function(e) {
        e.preventDefault();
        var t = $(this).parents("tr").first();
        $("#mainModalHeader").empty().append("Delete?"), $("#mainModalAcceptBtn").empty().append("Delete").css({
            display: "inline"
        }), $("#mainModalCloseBtn").empty().append("Cancel").css({
            display: "inline"
        }), $("#mainModalBody").empty().append('Are you sure you want to delete this question?<br><br>"' + t.find("td").first().text() + '"'), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            e.preventDefault(), t.remove(), $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    }), $("#tblQuestions tbody tr td").off("click").click(function(e) {
        e.preventDefault(), PopulateQuestionData($(this).parents("tr").first().attr("data-details")), $("#divNewQuestionForm legend").empty().append("Edit Question"), $("#divNewQuestionForm").css({
            display: "block"
        }), $("html, body").animate({
            scrollTop: $("#divNewQuestionForm").offset().top
        })
    }), $("#tblQuestions tbody tr").each(function() {
        $(this).find("td:last").off("click")
    })
}

function InsertQuestion(e) {
    var t = $("<tr></tr>"),
        n;
    if (!e) {
        n = {
            id: $("#txtQuestionID").val(),
            type: $("#selQuestionType option:selected").val(),
            req: $("input[name='chkQuestionReq']:checked").val(),
            text: $("#txtQuestionText").val(),
            choices: [],
            appliesto: {
                addons: [],
                menus: [],
                personnel: [],
                resources: []
            }
        };
        if (!n.id || n.id.length < 1) n.id = "_" + Math.floor(Math.random() * 1e6 + 1);
        $("input[name='txtQuestionAnswer']").each(function() {
            n.choices.push($(this).val())
        }), $("#selQuestionResources option:selected").each(function() {
            n.appliesto.resources.push($(this).attr("data-id"))
        }), $("#selQuestionAddons option:selected").each(function() {
            n.appliesto.addons.push($(this).attr("data-id"))
        }), $("#selQuestionMenus option:selected").each(function() {
            n.appliesto.menus.push($(this).attr("data-id"))
        }), $("#selQuestionPersonnel option:selected").each(function() {
            n.appliesto.personnel.push($(this).attr("data-id"))
        }), t.append("<td>" + $("#txtQuestionText").val() + "</td><td>" + $("#selQuestionType option:selected").text() + "<td>" + $("input[name='chkQuestionReq']:checked").val() + "</td><td><i name='btnQuestionUp' class='glyphicon glyphicon-chevron-up'></i><i name='btnQuestionDown' class='glyphicon glyphicon-chevron-down'></i><i name='btnQuestionDel' class='glyphicon glyphicon-trash'></i></td>")
    } else {
        n = {
            id: e.id,
            type: e.type,
            req: e.req,
            text: e.text,
            choices: e.choices,
            appliesto: e.appliesto
        };
        var r = "";
        switch (e.type) {
            case "text":
                r = "Text";
                break;
            case "checkbox":
                r = "Checkbox";
                break;
            case "radio":
                r = "Multiple Choice";
                break;
            case "select":
                r = "Dropdown"
        }
        t.append("<td>" + e.text + "</td><td>" + r + "<td>" + e.req + "</td><td><i name='btnQuestionUp' class='glyphicon glyphicon-chevron-up'></i><i name='btnQuestionDown' class='glyphicon glyphicon-chevron-down'></i><i name='btnQuestionDel' class='glyphicon glyphicon-trash'></i></td>")
    }
    var i = JSON.stringify(n);
    t.attr("data-details", i);
    var s = 0;
    $("#tblQuestions tbody tr").each(function() {
        var e = $.parseJSON($(this).attr("data-details"));
        if (e["id"] == n["id"]) {
            s = 1;
            var r = $.parseJSON(JSON.stringify(n)),
                o = $.parseJSON($(this).attr("data-details"));
            return delete o.req, delete r.req, delete o.appliesto, delete r.appliesto, JSON.stringify(o) != JSON.stringify(r) ? (n.id = "_" + Math.floor(Math.random() * 1e6 + 1), i = JSON.stringify(n), t.attr("data-details", i), $(this).after(t), $(this).remove(), !1) : ($(this).attr("data-details", i), !1)
        }
    }), s == 0 && $("#tblQuestions tbody").append(t), RebindQuestionnaireControls()
}

function PopulateQuestionData(e) {
    $("#btnNewQuestion").trigger("click");
    var t = $.parseJSON(e);
    $("#txtQuestionID").val(t.id);
    for (var n = 0; n < t.appliesto.resources.length; n++) $("#selQuestionResources option[data-id='" + SanitizeAttr(t.appliesto.resources[n]) + "']").prop("selected", !0);
    for (var n = 0; n < t.appliesto.addons.length; n++) $("#selQuestionAddons option[data-id='" + SanitizeAttr(t.appliesto.addons[n]) + "']").prop("selected", !0);
    for (var n = 0; n < t.appliesto.menus.length; n++) $("#selQuestionMenus option[data-id='" + SanitizeAttr(t.appliesto.menus[n]) + "']").prop("selected", !0);
    for (var n = 0; n < t.appliesto.personnel.length; n++) $("#selQuestionPersonnel option[data-id='" + SanitizeAttr(t.appliesto.personnel[n]) + "']").prop("selected", !0);
    $("#divNewQuestionForm select").each(function() {
        typeof $(this)[0].sumo != "undefined" ? $(this)[0].sumo.reload() : $(this).SumoSelect()
    }), t["req"] == "no" && $("#divNewQuestionForm input[name='chkQuestionReq']:last").prop("checked", !0), $("#selQuestionType option").each(function() {
        $(this).attr("value") == t["type"] && $(this).prop("selected", !0)
    }), $("#selQuestionType").change(), $("#txtQuestionText").val(t.text);
    if (t["type"] == "radio" || t["type"] == "select") {
        $("input[name='txtQuestionAnswer']").each(function() {
            $(this).next("a").remove(), $(this).next("br").remove(), $(this).remove()
        });
        for (var n = 0; n < t.choices.length; n++) {
            var r = $("<input name='txtQuestionAnswer' class='form-control' style='width:200px;display:inline-block;margin-top:5px' placeholder='Specify the option...'/>");
            r.val(t.choices[n]), $("#aQuestionAnswerAdd").before(r), $("#aQuestionAnswerAdd").before(" <a href='#' name='aQuestionAnswerDel'>Delete Answer</a><br>")
        }
        RebindQuestionnaireControls()
    }
}

function ReBindCreatorControls() {
    $("[name=buttonAddResource]").off("click").click(function(e) {
        e.preventDefault(), ClickAddResource($(this))
    }), $("[name=buttonAddMenu]").off("click").click(function(e) {
        e.preventDefault(), ClickAddMenu($(this))
    }), $("[name=buttonAddMenuItem]").off("click").click(function(e) {
        e.preventDefault(), ClickAddMenuItem($(this))
    }), $("[name=buttonAddPersonnel]").off("click").click(function(e) {
        e.preventDefault(), ClickAddPersonnel($(this))
    }), $("[name=buttonCreateAddon]").off("click").click(function(e) {
        e.preventDefault(), ClickCreateAddon($(this))
    }), $("[name=buttonCreateDeposit]").off("click").click(function(e) {
        e.preventDefault(), ClickCreateDeposit($(this))
    }), $("[name=buttonCreateRefund]").off("click").click(function(e) {
        e.preventDefault(), ClickCreateRefund($(this))
    }), $("[name=buttonDelete]").off("click").click(function(e) {
        e.preventDefault(), ClickDelete($(this))
    }), $("[name=buttonClone]").off("click").click(function(e) {
        e.preventDefault(), ClickClone($(this))
    }), $("[name=buttonEdit]").off("click").click(function(e) {
        e.preventDefault(), ClickEdit($(this))
    }), $("#venueSubmit").off("click").click(function(e) {
        e.preventDefault(), ClickSaveConfig($(this))
    }), $("[name=venueApprove]").off("click").click(function(e) {
        e.preventDefault(), ClickRequestReview($(this))
    }), $("[name=buttonPictureUp]").off("click").click(function(e) {
        e.preventDefault();
        var t = $(this).parents("div.pic").first();
        t.prev("div.pic").before(t), ColorVenueLogo()
    }), $("[name=buttonPictureDown]").off("click").click(function(e) {
        e.preventDefault();
        var t = $(this).parents("div.pic").first();
        t.next("div.pic").after(t), ColorVenueLogo()
    }), $("[name=buttonPictureDel]").off("click").click(function(e) {
        e.preventDefault(), $(this).parents("div.pic").first().remove(), ColorVenueLogo()
    }), $("[name=fileupload]").each(function() {
        $(this).attr("data-init") || ($(this).fileupload({
            url: "/inc/jQuery-File-Upload-master/server/php/index.php",
            sequentialUploads: !0,
            limitMultiFileUploads: 4,
            maxFileSize: 8388608
        }), $(this).on("fileuploaddone", function(e, t) {
            $(this).find("div.pictures").find("div.pic").length < $(this).find("div.pictures").first().attr("data-limit") && t && t.result && t.result.files && t.result.files.length > 0 && InsertPicture($(this).find("div.pictures").first(), t.result.files[0].url)
        })), $(this).attr("data-init", "1")
    }), ColorResources(), ColorVenueLogo(), $("div.mgmtuser button.btnMgmtRightsDelete").off("click").click(function(e) {
        e.preventDefault(), $(this).parents("div.mgmtuser").first().remove()
    }), $("div.rPolicyDetail button.btnrPolicyDetailDelete").off("click").click(function(e) {
        e.preventDefault(), $(this).parents("div.rPolicyDetail").first().remove()
    }), $("div.rNewPolicyDetail").off("click").click(function(e) {
        e.preventDefault(), $(this).before("<div class='rPolicyDetail'>					<input type='text' class='txtRefundFee' placeholder='00' value=''/><b>&#37; fee</b> if cancelled					<button class='btn btn-xs pull-right btnrPolicyDetailDelete'><i class='glyphicon glyphicon-trash'></i></button><br>					<div class='clearfix'></div>					<input type='text' class='txtRefundDays' placeholder='00' value=''/> days or less before the booking start date				</div>")
    }), $(document).on("drop dragover", function(e) {
        e.preventDefault()
    })
}

function ClickRequestReview() {
    ClickSaveConfig();
    var e = {
        method: "fRequestVenueReview",
        venueid: localStorage.getItem("activeProfile")
    };
    Post(e).then(function(e) {
        e["result"] == "success" ? ($("#mainModalHeader").empty().append("Review Pending"), $("#mainModalAcceptBtn").empty().append("OK").css({
            display: "inline"
        }), $("#mainModalCloseBtn").empty().append("OK").css({
            display: "none"
        }), $("#mainModalBody").empty().append("Your venue has been submitted for review, we will notify you when our review is complete and we will contact you with any questions we may have."), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            e.preventDefault(), $("#mainModal").modal("hide")
        }).click(function(e) {
            LoadPartial("/dashboard")
        })) : ($("#mainModalHeader").empty().append("Error"), $("#mainModalAcceptBtn").empty().append("OK").css({
            display: "none"
        }), $("#mainModalCloseBtn").empty().append("OK").css({
            display: "inline"
        }), $("#mainModalBody").empty().append("Failed to submit this venue for review."), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            e.preventDefault(), $("#mainModal").modal("hide")
        }))
    })
}

function EncodeVenueConfig() {
    if ($("#venueBusinessName").length < 1) return "";
    var e = {
            config: [],
            deposits: [],
            refunds: [],
            addons: [],
            resources: [],
            food: [],
            personnel: [],
            promos: [],
            questions: []
        },
        t = {
            name: $("#venueName").val().trim(),
            id: $("#venueid").val(),
            description: $("#venueDescription").val().trim(),
            banner: $("#venueBanner").val().trim(),
            pictures: PicturesToArray($("#venuePictures")),
            business: $("#venueBusinessName").val().trim(),
            ein: $("#venueBusinessEIN").val().trim(),
            address: $("#venueAddress").val().trim(),
            city: $("#venueCity").val().trim(),
            state: $("#venueState").val().trim(),
            zip: $("#venueZip").val().trim(),
            country: $("#venueCountry").val().trim(),
            latitude: $("#venueLatitude").val(),
            longitude: $("#venueLongitude").val(),
            phone: $("#venuePhone").val().trim(),
            website: $("#venueWebsite").val().trim(),
            facebook: $("#venueFacebook").val().trim(),
            twitter: $("#venueTwitter").val().trim(),
            type: $("#venueType").val(),
            style: $("#venueStyle").val(),
            features: [],
            functionality: [],
            contacts: [],
            rights: [],
            timezone: $("#venueTimezone").val(),
            visibility: $("#venueVisibility").val(),
            salesTax: parseFloat($("#venueSalesTax").val()) / 100,
            currency: $("#venueCurrency").val(),
            contract: "",
            subscription: []
        };
    $("#billToken").length > 0 && (t.subscription = {
        token: $("#billToken").val(),
        plan: $("#billPlan option:selected").attr("val"),
        email: $("#billEmail").val().trim()
    });
    if ($("#cFiles a").length > 0) {
        var n = $("#cFiles a").attr("href");
        n = n.indexOf("/content/") > 0 ? n.split("/content/")[1] : n, t.contract = n
    }
    $("#selVenueFeatures option:selected").each(function() {
        t.features.push($(this).attr("value"))
    }), t.functionality = {
        menus: $("#venueCreatorDetailsPane label.btn.active input[name='toggleFD']").val(),
        personnel: $("#venueCreatorDetailsPane label.btn.active input[name='toggleP']").val(),
        questions: $("#venueCreatorDetailsPane label.btn.active input[name='toggleQ']").val(),
        promos: $("#venueCreatorDetailsPane label.btn.active input[name='togglePC']").val(),
        publicFileUploads: $("#venueCreatorDetailsPane label.btn.active input[name='toggleFU']").val(),
        gratuity: $("#venueCreatorDetailsPane label.btn.active input[name='toggleGratuity']").val(),
        entireVenue: $("#venueCreatorDetailsPane label.btn.active input[name='toggleEntireVenue']").val()
    }, $("#venueContacts option").each(function() {
        t.contacts.push({
            name: $(this).data("name"),
            title: $(this).data("title"),
            email: $(this).data("email"),
            phone: $(this).data("phone"),
            comments: $(this).data("comments")
        })
    }), $("div.mgmtuser").each(function() {
        var e = "";
        $(this).find("input[name='chkRightsViewBooks']").prop("checked") && (e += "1,"), $(this).find("input[name='chkRightsManageBooks']").prop("checked") && (e += "2,"), $(this).find("input[name='chkRightsViewFinancials']").prop("checked") && (e += "3,"), $(this).find("input[name='chkRightsManageVenue']").prop("checked") && (e += "4,"), e = e.substring(0, e.lastIndexOf(","));
        var n = 0;
        $(this).find("input[name='chkRightsEmails']").prop("checked") == 1 && (n = 1), t.rights.push({
            name: $(this).find("input.txtRightsEmail").first().val(),
            roles: e,
            receiveEmails: n
        })
    });
    var r = [];
    $("#tblQuestions tbody tr").each(function() {
        var e = $.parseJSON($(this).attr("data-details"));
        r.push(e)
    });
    var i = [];
    $("#selectPromoCodes option").each(function() {
        var e = null,
            t = null;
        $(this).attr("data-start") > 0 && (e = $(this).attr("data-start")), $(this).attr("data-stop") > 0 && (t = $(this).attr("data-stop"));
        var n = {
            id: $(this).attr("data-id"),
            name: $(this).data("name").trim(),
            description: $(this).data("description").trim(),
            discounttype: $(this).attr("data-discounttype"),
            discountamount: $(this).attr("data-discountamount"),
            discountthreshold: $(this).attr("data-discountthreshold"),
            expires: $(this).attr("data-expires"),
            peruser: $(this).attr("data-peruser"),
            quantity: $(this).attr("data-quantity"),
            applic: $(this).attr("data-applic"),
            entireinvoice: $(this).attr("data-entire"),
            resources: $.parseJSON($(this).attr("data-resources")),
            combinable: $(this).attr("data-combinable"),
            auto: $(this).attr("data-auto"),
            status: $(this).attr("data-status"),
            start: e,
            stop: t,
            hours: $(this).data("data-hours")
        };
        i.push(n)
    });
    var s = [];
    $("div.deposit").each(function() {
        var e = {
            name: $(this).data("name").trim(),
            id: $(this).attr("data-id"),
            threshold: $(this).attr("data-threshold"),
            perc: $(this).attr("data-perc"),
            amount: $(this).attr("data-amount"),
            full: $(this).attr("data-full")
        };
        s.push(e)
    });
    var o = [];
    $("div.refund").each(function() {
        var e = {
            name: $(this).data("name").trim(),
            id: $(this).attr("data-id"),
            policy: $(this).attr("data-policy")
        };
        o.push(e)
    });
    var u = [];
    $("div.addon").each(function() {
        var e = $(this).data("pictures") ? $.parseJSON($(this).data("pictures")) : [];
        for (var t = 0; t < e.length; t++) e[t].url.indexOf("/content/") > 0 && (e[t].url = e[t].url.split("/content/")[1]);
        var n = {
            name: $(this).data("name").trim(),
            id: $(this).attr("data-id"),
            description: $(this).data("description").trim(),
            type: $(this).attr("data-type"),
            price: $(this).attr("data-price"),
            pictures: e,
            minimum: $(this).attr("data-minimum"),
            maximum: $(this).attr("data-maximum"),
            deliverable: $(this).attr("data-deliverable"),
            hours: $(this).data("data-hours"),
            deposit: $(this).data("deposit"),
            refund: $(this).data("refund")
        };
        u.push(n)
    });
    var a = [];
    $("div.creatorcategory.personnel").each(function() {
        var e = {
            name: $(this).data("name").trim(),
            id: $(this).attr("data-id"),
            description: $(this).data("description").trim(),
            price: $(this).attr("data-price"),
            min: $(this).attr("data-min"),
            max: $(this).attr("data-max"),
            req: $(this).attr("data-req"),
            hours: $(this).data("data-hours"),
            deposit: $(this).data("deposit"),
            refund: $(this).data("refund"),
            resources: $.parseJSON($(this).attr("data-resources"))
        };
        a.push(e)
    });
    var f = $("div.space").first(),
        l = [];
    f.length > 0 && l.push(ResourceToArray(f));
    var c = MenusToArray();
    return e.config = t, e.promos = i, e.deposits = s, e.refunds = o, e.addons = u, e.resources = l, e.food = c, e.personnel = a, e.questions = r, e
}

function ClickSaveConfig() {
    var e = ValidateVenueProfile();
    if (e.length > 0) {
        $("#mainModalHeader").empty().append("Invalid venue profile information provided"), $("#mainModalAcceptBtn").empty().append("OK").css({
            display: "none"
        }), $("#mainModalCloseBtn").empty().append("OK").css({
            display: "inline"
        }), $("#mainModalBody").empty().append("<div class='alert alert-danger'><ul></ul></div>");
        for (var t = 0; t < e.length; t++) $("#mainModalBody").find("ul").first().append("<li>" + e[t] + "</li>");
        $("#mainModal").modal("show"), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    } else {
        localStorage.removeItem("tempVenueConfig");
        var n = EncodeVenueConfig();
        console.log("save data:", n), $("#ajaxOverlay").show();
        var r = {
            method: "fSaveVenue",
            data: n
        };
        Post(r).then(function(e) {
            console.log("save response:", e), e["result"] == "success" ? ($("#mainModalHeader").empty().append("Venue Configuration Saved"), $("#mainModalAcceptBtn").empty().append("OK").css({
                display: "inline"
            }), $("#mainModalCloseBtn").empty().append("OK").css({
                display: "none"
            }), $("#mainModalBody").empty().append("You have successfully saved this venue configuration.  You can make changes to this venue profile at any time via your dashboard, we're taking you there now."), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
                $("#mainModal").modal("hide")
            }).click(function(t) {
                window.location.href.indexOf("/admin") > 0 ? $("#adminConfigList").trigger("change") : window.location.href.indexOf("/dashboard") < 0 ? AuthPing().then(function() {
                    localStorage.setItem("lastDashboardAccount", e.id), localStorage.setItem("lastDashboardPage", "profile"), window.location.replace("/dashboard")
                }) : (localStorage.setItem("lastDashboardAccount", e.id), GetDashboardPane())
            })) : ($("#mainModalHeader").empty().append("Failed to save venue configuration"), $("#mainModalAcceptBtn").empty().append("OK").css({
                display: "inline"
            }), $("#mainModalCloseBtn").empty().append("OK").css({
                display: "none"
            }), $("#mainModalBody").empty().append(e.result), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
                e.preventDefault(), $("#mainModal").modal("hide")
            }))
        })
    }
}

function LoadVenueConfig(e) {
    var t = {
        method: "fLoadVenueConfig",
        venueid: e
    };
    Post(t).then(function(e) {
        console.log("fLoadVenueConfig response", e);
        if (e["result"] != "success") $("div.venuecreator").empty().append("<div class='alert alert-danger'>" + e.result + "</div>");
        else {
            var t = e.data;
            $("#venueid").val(t.config.id), $("#shorturl").val(t.config.shorturl), $("#overviewShortURL").attr("href", "/venue/" + t.config.shorturl), $("#overviewShortURL").text("/venue/" + t.config.shorturl), $("#overviewBookURL").attr("href", "/reserve/book-an-event-at-" + t.config.shorturl), $("#overviewBookURL").text("/reserve/book-an-event-at-" + t.config.shorturl), $("#venueName").val(t.config.name), $("#venueDescription").val(t.config.description), $("#venueBanner").val(t.config.banner), $("#venuePictures").val(t.config.pictures), $("#venueBusinessName").val(t.config.business_name), $("#venueBusinessEIN").val(t.config.ein), $("#venueFullAddress").val(t.config.address), $("#venueAddress").val(t.config.address), $("#venueCity").val(t.config.city), $("#venueState").val(t.config.state), $("#venueZip").val(t.config.zip), $("#venueCountry").val(t.config.country), $("#venueLatitude").val(t.config.latitude), $("#venueLongitude").val(t.config.longitude), $("#venuePhone").val(t.config.phone), $("#venueWebsite").val(t.config.website), $("#venueFacebook").val(t.config.facebook), $("#venueTwitter").val(t.config.twitter), $("#venueType").find("option[value='" + SanitizeAttr(t.config.type) + "']").prop("selected", !0), $("#venuePromos").val(t.config.promos), $("#venueTimezone").val(t.config.timezone), $("#venueSalesTax").val(t.config.salesTax * 100), $("#venueCurrency").find("option[value='" + SanitizeAttr(t.config.currency) + "']").prop("selected", !0), $("#venueVisibility").find("option[value='" + SanitizeAttr(t.config.visibility) + "']").prop("selected", !0), $("#venueVisibility")[0].sumo.reload(), $("#venueType")[0].sumo.reload(), $("#venueCurrency")[0].sumo.reload(), t["config"]["status"] == "active" && $("a[href='#venueCreatorApprovePane']").hide(), t["config"]["status"] == "pending_review" && $("#venueCreatorApprovePane").empty().append("This venue has already been submitted for approval and we are still reviewing it."), t["config"]["functionality"]["menus"] != 1 ? ($("#venueCreatorSteps").find(":contains('Food & Drink')").hide(), $("input[name='toggleFD'][value=0]").trigger("click")) : $("input[name='toggleFD'][value=1]").trigger("click"), t["config"]["functionality"]["personnel"] != 1 ? ($("#venueCreatorSteps").find(":contains('Personnel')").hide(), $("input[name='toggleP'][value=0]").trigger("click")) : $("input[name='toggleP'][value=1]").trigger("click"), t["config"]["functionality"]["questions"] != 1 ? ($("#venueCreatorSteps").find(":contains('Questionnaire')").hide(), $("input[name='toggleQ'][value=0]").trigger("click")) : $("input[name='toggleQ'][value=1]").trigger("click"), t["config"]["functionality"]["promos"] != 1 ? ($("#venueCreatorSteps").find(":contains('Promo Codes')").hide(), $("input[name='togglePC'][value=0]").trigger("click")) : $("input[name='togglePC'][value=1]").trigger("click"), t["config"]["functionality"]["publicFileUploads"] != 1 ? $("input[name='toggleFU'][value=0]").trigger("click") : $("input[name='toggleFU'][value=1]").trigger("click"), t["config"]["functionality"]["gratuity"] != 1 ? $("input[name='toggleGratuity'][value=0]").trigger("click") : $("input[name='toggleGratuity'][value=1]").trigger("click"), t["config"]["functionality"]["entireVenue"] != 1 ? $("input[name='toggleEntireVenue'][value=0]").trigger("click") : $("input[name='toggleEntireVenue'][value=1]").trigger("click"), t.config.contract.length > 0 && ($("#cFiles").empty(), $("#cFiles").append("<div class='input-group'><span class='input-group-addon' style='background:#fefefe'><a href='" + SanitizeAttr(t.config.contract) + "'>" + t.config.contract + "</a></span><button class='btn btn-default' name='delContract'><i class='glyphicon glyphicon-trash'></i></button></div>"), $("form.contractupload span.fileinput-button").hide(), $("#cFiles [name='delContract']").off("click").click(function(e) {
                e.preventDefault(), $("#cFiles").empty(), RebindContractUpload()
            }));
            var n = t.config.features.length;
            for (var r = 0; r < n; r++) $("#selVenueFeatures").find("option[value='" + SanitizeAttr(t.config.features[r]) + "']").prop("selected", !0);
            $("#selVenueFeatures")[0].sumo.reload();
            var n = t.config.contacts.length;
            for (var r = 0; r < n; r++) InsertContact(t.config.contacts[r].name, t.config.contacts[r]);
            var n = t.config.rights.length;
            for (var r = 0; r < n; r++) InsertRights(t.config.rights[r].name, t.config.rights[r]);
            $("div.deposit_table").empty();
            var n = t.deposits.length;
            for (var r = 0; r < n; r++) InsertDeposit(t.deposits[r].name, t.deposits[r]);
            n = t.promos.length;
            for (var r = 0; r < n; r++) InsertPromo(t.promos[r].name, t.promos[r]);
            $("div.refund_table").empty(), n = t.refunds.length;
            for (var r = 0; r < n; r++) InsertRefund(t.refunds[r].name, t.refunds[r]);
            n = t.addons.length;
            for (var r = 0; r < n; r++) InsertAddon(t.addons[r].name, t.addons[r]);
            n = t.resources.length;
            for (var r = 0; r < n; r++) {
                var i = CreateResource(t.resources[r].name, t.resources[r].type, t.resources[r]);
                $(".resource_table").append(i)
            }
            n = t.relationships.length;
            for (var r = 0; r < n; r++) SetResourceRelationship(t.relationships[r].child, t.relationships[r].parent, t.relationships[r].relation);
            $(".resource_table").find(".resource").each(function() {
                $(this).parent("div").find("div:first").after($(this))
            }), n = t.food.menus.length;
            for (var r = 0; r < n; r++) {
                var s = CreateMenu(t.food.menus[r].name, t.food.menus[r]);
                $(".menu_table").append(s)
            }
            n = t.food.items.length;
            for (var r = 0; r < n; r++) {
                var s = CreateMenuItem(t.food.items[r].name, t.food.items[r]);
                $(".menu_table [data-id='" + SanitizeAttr(t.food.items[r].menuid) + "']").append(s)
            }
            n = t.personnel.length;
            for (var r = 0; r < n; r++) {
                var o = CreatePersonnel(t.personnel[r].name, t.personnel[r]);
                $(".personnel_table").append(o)
            }
            n = t.questions.length;
            for (var r = 0; r < n; r++) InsertQuestion(t.questions[r]);
            n = t.config.pictures.length;
            for (var r = 0; r < n; r++) InsertPicture($("#venuePictures"), t.config.pictures[r].url, t.config.pictures[r].caption);
            ReBindCreatorControls(), t.resources.length > 0 && $("#addvenuespace").remove(), localStorage.setItem("tempVenueConfig", JSON.stringify(EncodeVenueConfig()))
        }
        ReBindCreatorControls()
    })
}

function ResourceToArray(e) {
    if ($(e).length < 1) return;
    var t = $(e).children("div.space,div.resource").length,
        n = [];
    t > 0 && $(e).children("div.space,div.resource").each(function() {
        n.push(ResourceToArray($(this)))
    });
    var r = $(e).data("pictures") ? $.parseJSON($(e).data("pictures")) : [];
    for (var i = 0; i < r.length; i++) r[i].url.indexOf("/content/") > 0 && (r[i].url = r[i].url.split("/content/")[1]);
    var s = {
        name: $(e).data("name"),
        id: $(e).attr("data-id"),
        description: $(e).data("description"),
        type: $(e).attr("data-type"),
        pictures: r,
        capacity: $(e).attr("data-capacity"),
        seats: $(e).attr("data-seats"),
        cleanupcost: $(e).attr("data-cleanupcost"),
        cleanup: $(e).attr("data-cleanup"),
        duration: $(e).attr("data-duration"),
        increment: $(e).attr("data-increment"),
        lead: $(e).attr("data-lead"),
        autoapprove: $(e).attr("data-autoapprove"),
        over21: $(e).attr("data-over21"),
        linked: $(e).attr("data-linked"),
        rate: $(e).attr("data-rate"),
        deposit: $(e).data("deposit"),
        refund: $(e).data("refund"),
        timeslots: $(e).attr("data-timeslots"),
        hours: $(e).data("data-hours"),
        rates: $(e).data("data-rates"),
        slots: $(e).data("data-slots"),
        addons: $(e).data("addons") ? $.parseJSON($(e).data("addons")) : null,
        children: n
    };
    return s
}

function MenusToArray() {
    var e = {
            menus: [],
            items: []
        },
        t = [],
        n = [];
    return $("div.menu").each(function() {
        var e = {
            name: $(this).data("name"),
            id: $(this).attr("data-id"),
            description: $(this).data("description"),
            deposit: $(this).data("deposit"),
            refund: $(this).data("refund"),
            hours: $(this).data("data-hours")
        };
        t.push(e)
    }), $("div.menuitem").each(function() {
        var e = $(this).data("pictures") ? $.parseJSON($(this).data("pictures")) : [{
            url: "",
            caption: ""
        }];
        for (var t = 0; t < e.length; t++) e[t].url.indexOf("/content/") > 0 && (e[t].url = e[t].url.split("/content/")[1]);
        var r = {
            name: $(this).data("name"),
            id: $(this).attr("data-id"),
            type: $(this).attr("data-type"),
            description: $(this).data("description"),
            pictures: e,
            price: $(this).attr("data-price"),
            min: $(this).attr("data-min"),
            max: $(this).attr("data-max"),
            menu: $(this).parents("div.menu").first().data("name")
        };
        n.push(r)
    }), e.menus = t, e.items = n, e
}

function PicturesToArray(e) {
    var t = [],
        n = 0;
    return e.find("div.pic").each(function() {
        var e = {
            placement: n,
            url: $(this).find("img").first().attr("src").indexOf("/content/") > 0 ? $(this).find("img").first().attr("src").split("/content/")[1] : $(this).find("img").first().attr("src"),
            caption: $(this).find("[name=caption]").first().val()
        };
        t.push(e), n++
    }), t
}

function ClickDelete(e) {
    var t = !0,
        n = e.closest("div.creatorcategory");
    if (n.attr("data-id")) {
        var r = "";
        if (n.hasClass("space") || n.hasClass("resource")) r = "resource";
        n.hasClass("deposit") && (r = "deposit"), n.hasClass("refund") && (r = "refund"), n.hasClass("addon") && (r = "addon");
        if (r == "resource") {
            var i = [];
            i.push($(n).attr("data-id")), $(n).find("div.space,div.resource").each(function() {
                i.push($(this).attr("data-id"))
            });
            var s = {
                method: "fCheckIfDeleteOK",
                ids: i
            };
            Post(s).then(function(e) {
                e["result"] != "success" && (t = !1)
            })
        }
    }
    t ? ($("#mainModalHeader").empty().append("Delete?"), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty().append('Are you sure you want to delete "' + n.data("name") + '"?  Deleting this will not affect bookings that have already been made.'), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
        $("#mainModal").modal("hide")
    }).click(function(e) {
        e.preventDefault(), n.remove(), $("#mainModalBody").empty(), ReBindCreatorControls()
    }), $("#mainModalCloseBtn").off("click").click(function(e) {
        $("#mainModalBody").empty(), $("#mainModal").modal("hide")
    })) : ($("#mainModalHeader").empty().append("Failed to delete"), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "none"
    }), $("#mainModalCloseBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalBody").empty().append('You cannot delete "' + n.data("name") + '" because there are active reservations that depend it.'), $("#mainModal").modal("show"), $("#mainModalCloseBtn").off("click").click(function(e) {
        $("#mainModalBody").empty(), $("#mainModal").modal("hide")
    }))
}

function ClickClone(e) {
    var t = e.closest("div.creatorcategory");
    $("#mainModalHeader").empty().append("Clone how many times?"), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty().append("<form class='form-horizontal' action='#'><div class='form-group'><label class='col-xs-3 control-label'>How many copies of \"" + t.data("name") + "\"?</label><div class='col-xs-9'><input type='text' class='form-control' id='textCloneCopies' placeholder='#'></div></div></form>"), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
        $("#mainModal").modal("hide")
    }).click(function(e) {
        e.preventDefault();
        var n = $("#textCloneCopies").val();
        if (!isNaN(n))
            for (var r = 0; r < n; r++) {
                var i = t.clone(!0, !0);
                i.attr("data-id", ""), i.data("name", i.data("name") + " (copy " + (r + 1) + ")"), i.find("div[name='resourceName']").text(i.data("name")), t.before(i)
            }
        $("#mainModalBody").empty(), ReBindCreatorControls()
    }), $("#mainModalCloseBtn").off("click").click(function(e) {
        $("#mainModalBody").empty(), $("#mainModal").modal("hide")
    })
}

function CreateResource(e, t, n) {
    var r = "<div class='creatorcategory " + (t == "1" || t == "2" ? "space" : "resource") + "'>" + (t == "1" || t == "2" ? "" : "<small>") + "<div class='row'>					<div class='col-xs-6'><div name='resourceName' style='display:inline;padding-right:10px'>" + e + "</div>						<small class='editBar'>							<a name='buttonEdit' href='#'>Edit</a> | 							<a name='buttonClone' href='#'>Clone</a> | 							<a name='buttonDelete' href='#'>Delete</a>						</small>					</div><div class='col-xs-2' name='resourceRate'></div>					<div class='col-xs-2 display-mob'></div>					<div class='col-xs-2 no-display-mob' name='resourceMin'></div>				" + (t == "1" || t == "2" ? "				<div class='col-xs-2'><button name='buttonAddResource' class='btn btn-xs btn-primary pull-right' style='margin:auto 8px auto 10px'>+<div class='no-display-mob' style='display:inline'> Resource</div></button></div></div>				<div class='clearfix'></div>				<div class='addnewspace'>					<button name='buttonAddResource' class='btn btn-xs btn-default'>Add Space</button>				</div>" : "</small>") + "</div>";
    return r = EncodeResourceData($(r), n), r
}

function CreateMenu(e, t) {
    var n = "<div class='creatorcategory menu formpan'><p style='float:left;margin:auto'>" +
        e + "			</p><small class='editBar'>				<a name='buttonEdit' href='#' style='margin-left:10px'>Edit</a> | 				<a name='buttonDelete' href='#'>Delete</a>			<button name='buttonAddMenuItem' class='btn btn-xs btn-primary pull-right' style='margin:auto auto auto 10px'>+ Item</button></small>			<div class='clearfix'></div>			</div>";
    return n = EncodeMenuData($(n), t), n
}

function CreateMenuItem(e, t) {
    var n = "<div class='creatorcategory menuitem'><small><div class='row'>				<div class='col-xs-4'><div name='menuItemName' style='display:inline;padding-right:10px'>" + e + "</div>					<small class='editBar'>						<a name='buttonEdit' href='#'>Edit</a> | 						<a name='buttonDelete' href='#'>Delete</a>					</small>				</div><div class='col-xs-2' name='menuItemPrice'></div>				<div class='col-xs-2' name='menuItemMin'></div><div class='col-xs-2' name='menuItemMax'></div></div>			</small></div>";
    return n = EncodeMenuItemData($(n), t), n
}

function CreatePersonnel(e, t) {
    var n = "<div class='creatorcategory personnel'><div class='row'>				<div class='col-xs-4'><div name='personnelName' style='display:inline;padding-right:10px'>" + e + "</div>					<small class='editBar'>						<a name='buttonEdit' href='#'>Edit</a> | 						<a name='buttonDelete' href='#'>Delete</a>					</small>				</div><div class='col-xs-2' name='personnelPrice'></div>				<div class='col-xs-2' name='personnelMin'></div><div class='col-xs-2' name='personnelMax'></div></div>			</div>";
    return n = EncodePersonnelData($(n), t), n
}

function InsertDeposit(e, t) {
    var n = "<div class='creatorcategory deposit'><div class='row'>					<div class='col-xs-6'><div name='depositName' style='display:inline;padding-right:10px'>" + e + "</div>						<small class='editBar'>							<a name='buttonEdit' href='#'>Edit</a> | 							<a name='buttonClone' href='#'>Clone</a> | 							<a name='buttonDelete' href='#'>Delete</a>						</small>					</div><div class='col-xs-2' name='depositPerc'></div>					<div class='col-xs-2' name='depositDue'></div></div>				</div>";
    n = EncodeDepositData($(n), t), $(".deposit_table").append($(n)), ReBindCreatorControls()
}

function InsertRefund(e, t) {
    var n = "<div class='creatorcategory refund'><div class='row'>					<div class='col-xs-8'><div name='refundName' style='display:inline;padding-right:10px'>" + e + "</div>						<small class='editBar'>							<a name='buttonEdit' href='#'>Edit</a> | 							<a name='buttonClone' href='#'>Clone</a> | 							<a name='buttonDelete' href='#'>Delete</a>						</small>					</div></div>				</div>";
    n = EncodeRefundData($(n), t), $(".refund_table").append($(n)), ReBindCreatorControls()
}

function InsertAddon(e, t) {
    var n = "<div class='creatorcategory addon'><div class='row'>					<div class='col-xs-4'><div name='addOnName' style='display:inline;padding-right:10px'>" + e + "</div>						<small class='editBar'>							<a name='buttonEdit' href='#'>Edit</a> | 							<a name='buttonClone' href='#'>Clone</a> | 							<a name='buttonDelete' href='#'>Delete</a>						</small>					</div><div class='col-xs-2' name='addOnPrice'></div>					<div class='col-xs-2' name='addOnMin'></div>					<div class='col-xs-2' name='addOnMax'></div></div>				</div>";
    n = EncodeAddonData($(n), t), $(".addon_table").append($(n)), ReBindCreatorControls()
}

function InsertContact(e, t) {
    var n = $("<option></option>", {
        text: e
    });
    n = EncodeContactData(n, t), $("#venueContacts").append(n)
}

function InsertRights(e, t) {
    var n = $("<div class='mgmtuser'>							<b>Email:</b><input type='text' class='txtRightsEmail' placeholder='User&#39;s email address' value=''/>							<button class='btn btn-xs pull-right btnMgmtRightsDelete'><i class='glyphicon glyphicon-trash'></i></button><br>							<div class='clearfix'></div>							<div class='row'>								<div class='col-xs-6'>									<input type='checkbox' name='chkRightsViewBooks'/> View Books<br>									<input type='checkbox' name='chkRightsManageBooks'/> Manage Books									<input type='checkbox' name='chkRightsEmails'/> Receive Emails								</div>								<div class='col-xs-6'>									<input type='checkbox' name='chkRightsViewFinancials'/> View Financials<br>									<input type='checkbox' name='chkRightsManageVenue'/> Manage Venue 								</div>							</div>						</div>");
    n.find("input.txtRightsEmail").val(t.name);
    for (var r = 0; r < t.roles.length; r++) switch (t.roles[r]) {
        case 1:
            n.find("input[name='chkRightsViewBooks']").prop("checked", !0);
            break;
        case 2:
            n.find("input[name='chkRightsManageBooks']").prop("checked", !0);
            break;
        case 3:
            n.find("input[name='chkRightsViewFinancials']").prop("checked", !0);
            break;
        case 4:
            n.find("input[name='chkRightsManageVenue']").prop("checked", !0)
    }
    t["receiveEmails"] == 1 && n.find("input[name='chkRightsEmails']").prop("checked", !0), $("#rowMgmtUsers").prepend(n)
}

function InsertPicture(e, t, n) {
    if (!t) {
        console.log("Error uploading picture!", t);
        return
    }
    var r = "<div class='pic'>					<img src='" + SanitizeAttr(t.indexOf("http") < 0 ? "https://static.invitebig.com/assets/content/" : "") + t + "'>					<textarea name='caption' class='form-control pic-caption' placeholder='Caption...'>" + (n ? n : "") + "</textarea>					<div class='input-group' style='margin-left:25%'>						<button name='buttonPictureUp' class='btn btn-default'><i class='glyphicon glyphicon-chevron-up'></i></button>						<button name='buttonPictureDel' class='btn btn-default'><i class='glyphicon glyphicon-trash'></i></button>						<button name='buttonPictureDown' class='btn btn-default'><i class='glyphicon glyphicon-chevron-down'></i></button>					</div>				</div>";
    e.append($(r)), ReBindCreatorControls()
}

function SetResourceRelationship(e, t, n) {
    n.indexOf("_linked") > 0 && $(".resource_table div[data-id='" + SanitizeAttr(e) + "']").attr("data-linked", "true"), n.indexOf("inside_") >= 0 && $(".resource_table div[data-id='" + SanitizeAttr(e) + "']").appendTo($(".resource_table div[data-id='" + SanitizeAttr(t) + "']")), $(".addnewspace").each(function() {
        $(this).appendTo($(this).parent("div.creatorcategory"))
    })
}

function ValidatePromoCode() {
    var e = [];
    return ($("#txtPromoCode").val().length < 3 || $("#txtPromoCode").val().length > 20) && e.push("The promo code must be between 3 and 20 characters"), /^[0-9.$%]*$/.test($("#txtPromoAmount").val()) == 0 && e.push("The promo amount can only contain numbers and decimals"), /^[0-9]*$/.test($("#txtPromoPerUser").val()) == 0 && e.push("The per-user uses field can only contain numbers"), /^[0-9.]*$/.test($("#txtPromoQuantity").val()) == 0 && e.push("The number of invoices field can only contain numbers and decimals"), $("#selPromoResources option:checked").length < 1 && e.push("You must choose at least one resource for this promo code to apply to"), $("#fgHours tbody tr").length < 1 && e.push("You must specify at least one timeslot for which this promo code is available"), $("#txtPromoStart").val().length < 6 && e.push("You must specify a start date after which this promo code becomes available"), $("#txtPromoStop").val().length < 6 && e.push("You must specify a stop date on which this promo code becomes unavailable"), e
}

function EncodePromoData(e, t) {
    if (!t) {
        e.attr("data-id", $("#txtPromoId").val()), e.data("name", $("#txtPromoCode").val()), e.text($("#txtPromoCode").val()), e.data("description", $("#txtPromoDescription").val()), e.attr("data-discounttype", $("#selPromoType option:selected").attr("value")), e.attr("data-discountamount", $("#txtPromoAmount").val().replace(/[^0-9.]/g, "")), e.attr("data-discountthreshold", $("#txtPromoThreshold").val().replace(/[^0-9.]/g, "")), e.attr("data-start", FormatDate($("#txtPromoStart").val(), "X")), e.attr("data-stop", FormatDate($("#txtPromoStop").val(), "X")), e.attr("data-peruser", $("#chkPromoPerUserUnlim").prop("checked") == 1 ? "unlim" : $("#txtPromoPerUser").val()), e.attr("data-quantity", $("#chkPromoQuantityUnlim").prop("checked") == 1 ? "unlim" : $("#txtPromoQuantity").val()), e.attr("data-applic", parseInt($("[name='radioGrp1']:checked").val())), e.attr("data-expires", parseInt($("#txtPromoExpVal").val()) || 0), $("#radioPromoEntireInvoice").prop("checked") == 1 && e.attr("data-entire", 1), $("#radioPromoResourceOnly").prop("checked") == 1 && e.attr("data-entire", 0);
        var n = [];
        $("#selPromoResources option:selected").each(function() {
            n.push(parseInt($(this).attr("data-id")))
        }), e.attr("data-resources", JSON.stringify(n)), e.attr("data-combinable", $("#chkPromoCombinable").prop("checked") == 1 ? 1 : 0), e.attr("data-auto", $("#chkPromoAuto").prop("checked") == 1 ? 1 : 0), e.attr("data-status", $("#selPromoStatus option:selected").attr("value")), e.data("data-hours", $("#fgHours .timeslot-widget").tsWidget("save")), $("[name='radioExpType'][value='before']:checked").length > 0 && e.attr("data-expires", parseInt(e.attr("data-expires")) * -1)
    } else e.attr("data-id", t.id), e.data("name", t.name), e.text(t.name), e.data("description", t.description), e.attr("data-discounttype", t.discount_type), e.attr("data-discountamount", t.discount_amount), e.attr("data-discountthreshold", t.discount_threshold), e.attr("data-expires", t.expires), e.data("data-hours", t.hours), e.attr("data-start", t.start), e.attr("data-stop", t.stop), e.attr("data-peruser", t.peruser), e.attr("data-quantity", t.quantity), e.attr("data-applic", t.applic), e.attr("data-entire", t.entireinvoice), e.attr("data-resources", JSON.stringify(t.resources)), e.attr("data-combinable", t.combinable), e.attr("data-auto", t.auto), e.attr("data-status", t.status);
    return e.attr("data-status") != "active" && e.css({
        color: "grey"
    }), e
}

function PopulatePromoData(e) {
    $("#txtPromoId").val(e.attr("data-id")), $("#txtPromoCode").val(e.data("name")), $("#selPromoType option[value=" + e.attr("data-discounttype") + "]").prop("selected", !0), $("#txtPromoDescription").val(e.data("description")), $("#txtPromoAmount").val(e.attr("data-discountamount")), $("#txtPromoAmount").trigger("blur"), $("#txtPromoThreshold").val(e.attr("data-discountthreshold")), $("#txtPromoThreshold").trigger("blur"), $("#txtPromoStart").val(FormatDate(e.attr("data-start"), "MMMM D, YYYY")), $("#txtPromoStop").val(FormatDate(e.attr("data-stop"), "MMMM D, YYYY")), $("#fgHours .timeslot-widget").tsWidget("restore", e.data("data-hours")), parseInt(e.attr("data-expires")) > 0 ? ($("[name='radioExpType'][value='after']").trigger("click"), $("#txtPromoExpVal").val(e.attr("data-expires"))) : parseInt(e.attr("data-expires")) < 0 ? ($("[name='radioExpType'][value='before']").trigger("click"), $("#txtPromoExpVal").val(Math.abs(e.attr("data-expires")))) : $("[name='radioExpType'][value='never']").trigger("click"), e.attr("data-peruser") == "unlim" ? $("#chkPromoPerUserUnlim").prop("checked", !0) : ($("#chkPromoPerUserUnlim").prop("checked", !1), $("#txtPromoPerUser").val(e.attr("data-peruser"))), e.attr("data-quantity") == "unlim" ? $("#chkPromoQuantityUnlim").prop("checked", !0) : ($("#chkPromoQuantityUnlim").prop("checked", !1), $("#txtPromoQuantity").val(e.attr("data-quantity")));
    switch (e.attr("data-applic")) {
        case "3":
            $("#radioPromoAllSelected").prop("checked", !0);
            break;
        case "2":
            $("#radioPromoAnySelectedI").prop("checked", !0);
            break;
        default:
            $("#radioPromoAnySelected").prop("checked", !0)
    }
    switch (e.attr("data-entire")) {
        case "1":
            $("#radioPromoEntireInvoice").prop("checked", !0);
            break;
        default:
            $("#radioPromoResourceOnly").prop("checked", !0)
    }
    e.attr("data-combinable") == 1 ? $("#chkPromoCombinable").prop("checked", !0) : $("#chkPromoCombinable").prop("checked", !1), e.attr("data-auto") == 1 ? $("#chkPromoAuto").prop("checked", !0) : $("#chkPromoAuto").prop("checked", !1), $("#selPromoStatus option[value='" + SanitizeAttr(e.attr("data-status")) + "']").prop("selected", "selected");
    var t = $.parseJSON(e.attr("data-resources"));
    for (var n = 0; n < t.length; n++) $("#selPromoResources option[data-id=" + t[n] + "]").prop("selected", "selected");
    t.length == 0 && $("#selPromoResources option[value=Any]").prop("selected", "selected"), $("#divPromoCodeDetails select").each(function() {
        typeof $(this)[0].sumo != "undefined" ? $(this)[0].sumo.reload() : $(this).SumoSelect()
    })
}

function PopulatePromoResourceList() {
    $("div.creatorcategory.resource,div.creatorcategory.space").each(function() {
        $(this).attr("data-id") && $("#selPromoResources").append("<option data-id='" + SanitizeAttr($(this).attr("data-id")) + "'>" + $(this).data("name") + "</option>")
    })
}

function PopulateQuestionResourceList() {
    $("#selQuestionResources option").remove(), $("#selQuestionResources").append("<option data-id='0'>Any</option>"), $("div.creatorcategory.resource,div.creatorcategory.space").each(function() {
        $(this).attr("data-id") && $("#selQuestionResources").append("<option data-id='" + SanitizeAttr($(this).attr("data-id")) + "'>" + $(this).data("name") + "</option>")
    }), $("#selQuestionAddons option").remove(), $("#selQuestionAddons").append("<option data-id='0'>Any</option>"), $("div.creatorcategory.addon").each(function() {
        $(this).attr("data-id") && $("#selQuestionAddons").append("<option data-id='" + SanitizeAttr($(this).attr("data-id")) + "'>" + $(this).data("name") + "</option>")
    }), $("#selQuestionMenus option").remove(), $("#selQuestionMenus").append("<option data-id='0'>Any</option>"), $("div.creatorcategory.menu").each(function() {
        $(this).attr("data-id") && $("#selQuestionMenus").append("<option data-id='" + SanitizeAttr($(this).attr("data-id")) + "'>" + $(this).data("name") + "</option>")
    }), $("#selQuestionPersonnel option").remove(), $("#selQuestionPersonnel").append("<option data-id='0'>Any</option>"), $("div.creatorcategory.personnel").each(function() {
        $(this).attr("data-id") && $("#selQuestionPersonnel").append("<option data-id='" + SanitizeAttr($(this).attr("data-id")) + "'>" + $(this).data("name") + "</option>")
    }), $("#divNewQuestionForm select").each(function() {
        typeof $(this)[0].sumo != "undefined" ? $(this)[0].sumo.reload() : $(this).SumoSelect()
    })
}

function InsertPromo(e, t) {
    var n = $("<option />");
    n.data("name", e), n.text(e), EncodePromoData(n, t), $("#selectPromoCodes").prepend(n), $("#selectPromoCodes").attr("size", $("#selectPromoCodes option").length + 1)
}

function ValidateVenueProfile() {
    var e = [];
    return ($("#venueName").val().length < 3 || $("#venueName").val().length > 128) && e.push("Venue Name must be between 3 and 128 characters long"), $("#venueDescription").val().length < 3 && e.push("Venue Description must be provided"), ($("#venueBusinessName").val().length < 3 || $("#venueBusinessName").val().length > 128) && e.push("Venue Business Name must be at between 3 and 128 characters long"), ($("#venueAddress").val().length < 3 || $("#venueAddress").val().length > 128) && e.push("Please specify a valid venue address"), ($("#venueCity").val().length < 3 || $("#venueCity").val().length > 128) && e.push("Please specify a valid city"), ($("#venueCountry").val().length < 2 || $("#venueCountry").val().length > 32) && e.push("Please specify a valid country"), ($("#venuePhone").val().length < 10 || $("#venueName").val().length > 50) && e.push("Venue Phone number must be at least 10 characters long"), $("#venueWebsite").val().length > 0 && $("#venueWebsite").val().indexOf("https://") < 0 && $("#venueWebsite").val().indexOf("http://") < 0 && e.push("Venue Website must be the full URL (including http://)"), $("#venueWebsite").val().length > 0 && $("#venueWebsite").val().indexOf("https://") < 0 && $("#venueWebsite").val().indexOf("http://") < 0 && e.push("Venue Website must be the full URL (including http://)"), $("#venueFacebook").val().length > 0 && $("#venueFacebook").val().indexOf("https://") < 0 && $("#venueWebsite").val().indexOf("http://") < 0 && e.push("Venue Facebook page must be the full URL (including http://)"), $("#venueTwitter").val().length > 0 && $("#venueTwitter").val().indexOf("https://") < 0 && $("#venueWebsite").val().indexOf("http://") < 0 && e.push("Venue Twitter page must be the full URL (including http://)"), $("#venueTimezone").val().length < 3 && (e.push("Could not determine the timezone for the specified address"), $("#venueTimezone").parents(".form-group").first().show()), /^[0-9.]*$/.test($("#venueSalesTax").val()) == 0 && e.push("Sales Tax Rate field can only contain whole numbers, like '10' for 10%"), $("#venuePictures").find("div.pic").length < 3 && e.push("You must upload at least three pictures for your venue profile: a logo and a profile header image, and at least one more image of your venue"), e
}

function ValidateResource() {
    var e = [];
    return $("#newResourceRate").val($("#newResourceRate").val().replace(/[$,]/g, "")), $("#newResourceCleanupCost").val($("#newResourceCleanupCost").val().replace(/[$,]/g, "")), ($("#newResourceName").val().length < 3 || $("#newResourceName").val().length > 128) && e.push("Resource Name must be between 3 and 128 characters long"), $("div.resource,div.space").each(function() {
        $(this).data("name") == $("#newResourceName").val() && $(this).attr("data-id") && $(this).attr("data-id") != $("#newResourceID").val() && e.push("This Resource Name is already in use, please choose another name")
    }), ($("#newResourceDescription").val().length < 3 || $("#newResourceDescription").val().length > 2048) && e.push("Resource Description must be between 3 and 2048 characters long"), /^[0-9.]*$/.test($("#newResourceCleanupCost").val()) == 0 && e.push("Cleanup Cost can only contain numbers and a period"), ($("#newResourceCleanupCost").val() == "" || parseFloat($("#newResourceCleanupCost").val()) < 0 || parseFloat($("#newResourceCleanupCost").val()) > 1e5) && e.push("Cleanup Cost must be between " + FormatDollars(0) + " and " + FormatDollars(1e5)), /^[0-9]*$/.test($("#newResourceCapacity").val()) == 0 && e.push("Max Occupancy can only contain numbers"), ($("#newResourceCapacity").val() == "" || parseInt($("#newResourceCapacity").val()) < 1 || parseInt($("#newResourceCapacity").val()) > 1e6) && e.push("Max Occupancy must be between 1 and 1000000"), /^[0-9]*$/.test($("#newResourceSeats").val()) == 0 && e.push("Seats can only contain numbers"), ($("#newResourceSeats").val() == "" || parseInt($("#newResourceSeats").val()) < 0 || parseInt($("#newResourceSeats").val()) > 1e6) && e.push("Seats must be between 0 and 1000000"), /^[0-9]*$/.test($("#newResourceCleanupTime").val()) == 0 && e.push("Cleanup Time can only contain whole numbers"), /^[0-9]*$/.test($("#newResourceLeadTime").val()) == 0 && e.push("Lead Time can only contain whole numbers"), ($("#newResourceLeadTime").val() == "" || parseInt($("#newResourceLeadTime").val()) < 0 || parseInt($("#newResourceLeadTime").val()) > 259200) && e.push("Lead Time must be between 0 and 259200"), $("#billingHourly:checked").length > 0 && (/^[0-9.]*$/.test($("#newResourceRate").val()) == 0 && e.push("Default rate can only contain numbers and a period"), ($("#newResourceRate").val() == "" || parseFloat($("#newResourceRate").val()) < 0 || parseFloat($("#newResourceRate").val()) > 1e5) && e.push("Default rate must be between " + FormatDollars(0) + " and " + FormatDollars(1e5)), $("#fgHours .timeslot-widget tbody tr").length < 1 && e.push("You must specify the hours of operation"), (parseInt($("#newResourceMinDuration").val()) < 0 || parseInt($("#newResourceMinDuration").val()) > 10080) && e.push("Minimum Duration must be between 0 minutes and 10080 minutes"), (parseInt($("#newResourceIncrement").val()) < 15 || parseInt($("#newResourceIncrement").val()) > 1440) && e.push("Increment must be between 15 minutes and 1440 minutes")), $("#billingTimeslot:checked").length > 0 && $("#fgTimeslots .timeslot-widget tbody tr").length < 1 && e.push("You must specify at least one bookable timeslot"), $(".timeslot-widget:visible").tsWidget("validate") || e.push("Invalid timeslot definition"), e
}

function EncodeResourceData(e, t) {
    if (!t) {
        e.first("div").data("name", $("#newResourceName").val()), e.first("div").attr("data-id", $("#newResourceID").val()), e.first("div").data("description", $("#newResourceDescription").val()), e.first("div").attr("data-type", $("#newResourceType").val()), e.first("div").data("pictures", JSON.stringify(PicturesToArray($("#newResourcePictures")))), e.first("div").attr("data-capacity", $("#newResourceCapacity").val()), e.first("div").attr("data-seats", $("#newResourceSeats").val()), e.first("div").attr("data-cleanupcost", $("#newResourceCleanupCost").val()), e.first("div").attr("data-cleanup", $("#newResourceCleanupTime").val()), e.first("div").attr("data-duration", $("#newResourceMinDuration").val()), e.first("div").attr("data-increment", $("#newResourceIncrement").val()), e.first("div").attr("data-lead", $("#newResourceLeadTime").val()), e.first("div").attr("data-autoapprove", $("#newResourceAutoApprove").is(":checked") ? "true" : "false"), e.first("div").attr("data-over21", $("#newResourceOver21").is(":checked") ? "true" : "false"), e.first("div").attr("data-linked", $("#newResourceLinked").is(":checked") ? "true" : "false"), e.first("div").attr("data-rate", $("#newResourceRate").val()), e.first("div").data("deposit", $("#selectDepositList option:selected").attr("value")), e.first("div").data("refund", $("#selectRefundList option:selected").attr("value")), e.first("div").attr("data-timeslots", $("#billingHourly:checked").length > 0 ? 0 : 1), e.first("div").data("data-hours", $("#fgHours .timeslot-widget").tsWidget("save")), e.first("div").data("data-rates", $("#fgSpecialRates .timeslot-widget").tsWidget("save")), e.first("div").data("data-slots", $("#fgTimeslots .timeslot-widget").tsWidget("save")), e.first("div").data("addons", JSON.stringify(BuildAddonArray()));
        if ($("#billingHourly:checked").length == 0) {
            var n = 99999999,
                r = 99999999,
                i = e.first("div").data("data-slots");
            for (var s = 0; s < i.length; s++) {
                var o = i[s].stop - i[s].start,
                    u = i[s].rate;
                o < n && (n = o), u < r && (r = u)
            }
            e.first("div").attr("data-duration", n), e.first("div").attr("data-rate", r)
        }
        e.find("[name=resourceName]").first().empty().append($("#newResourceName").val()), e.find("[name=resourceRate]").first().empty().append("Rate: " + FormatDollars($("#newResourceRate").val()) + "/hr"), e.find("[name=resourceMin]").first().empty().append("Min: " + $("#newResourceMinDuration").val() + " mins")
    } else e.first("div").data("name", t.name), e.first("div").attr("data-id", t.id), e.first("div").data("description", t.description), e.first("div").attr("data-type", t.type), e.first("div").data("pictures", JSON.stringify(t.pictures)), e.first("div").attr("data-capacity", t.capacity), e.first("div").attr("data-seats", t.seats), e.first("div").attr("data-cleanupcost", t.cleanupcost), e.first("div").attr("data-cleanup", t.cleanup), e.first("div").attr("data-duration", t.duration), e.first("div").attr("data-increment", t.increment), e.first("div").attr("data-lead", t.lead), e.first("div").attr("data-autoapprove", t["autoapprove"] == "1" ? "true" : "false"), e.first("div").attr("data-over21", t["over21"] == "1" ? "true" : "false"), e.first("div").attr("data-linked", t["linked"] == "1" ? "true" : "false"), e.first("div").attr("data-rate", t.rate), e.first("div").data("deposit", t.deposit), e.first("div").data("refund", t.refund), e.first("div").attr("data-timeslots", t.timeslots), e.first("div").data("data-hours", t.hours), e.first("div").data("data-rates", t.rates), e.first("div").data("data-slots", t.slots), e.first("div").data("addons", t.addons), e.find("[name=resourceName]").first().empty().append(t.name), e.find("[name=resourceRate]").first().empty().append("Rate: " + FormatDollars(t.rate) + "/hr"), e.find("[name=resourceMin]").first().empty().append("Min: " + t.duration + " mins");
    return e
}

function ValidateMenu() {
    var e = [];
    return ($("#newMenuName").val().length < 3 || $("#newMenuName").val().length > 128) && e.push("Menu Name must be at between 3 and 128 characters long"), $("div.menu").each(function() {
        $(this).data("name") == $("#newMenuName").val() && $(this).attr("data-id") && $(this).attr("data-id") != $("#newMenuID").val() && e.push("This Menu Name is already in use, please choose another name")
    }), ($("#newMenuDescription").val().length < 3 || $("#newMenuDescription").val().length > 2048) && e.push("Menu Description must be between 3 and 2048 characters long"), $("#selectRefundList option:selected").length != 1 && e.push("One refund policy must be assigned"), $("#selectDepositList option:selected").length != 1 && e.push("One deposit policy must be assigned"), $("#fgHours .timeslot-widget tbody tr").length < 1 && e.push("You must specify the availability"), $(".timeslot-widget:visible").tsWidget("validate") || e.push("Invalid timeslot definition"), e
}

function EncodeMenuData(e, t) {
    return t ? (e.first("div").data("name", t.name), e.first("div").attr("data-id", t.id), e.first("div").data("description", t.description), e.first("div").data("deposit", t.deposit), e.first("div").data("refund", t.refund), e.first("div").data("data-hours", t.hours), e.find("p:first").empty().append(t.name)) : (e.first("div").data("name", $("#newMenuName").val()), e.first("div").attr("data-id", $("#newMenuID").val()), e.first("div").data("description", $("#newMenuDescription").val()), e.first("div").data("deposit", $("#selectDepositList option:selected").attr("value")), e.first("div").data("refund", $("#selectRefundList option:selected").attr("value")), e.first("div").data("data-hours", $("#fgHours .timeslot-widget").tsWidget("save")), e.find("p:first").empty().append($("#newMenuName").val())), e
}

function ValidateMenuItem() {
    var e = [];
    return $("#newMenuItemPrice").val($("#newMenuItemPrice").val().replace(/[$,]/g, "")), ($("#newMenuItemName").val().length < 3 || $("#newMenuItemName").val().length > 128) && e.push("Name must be at between 3 and 128 characters long"), $("div.menuitem").each(function() {
        $(this).data("name") == $("#newMenuItemName").val() && $(this).attr("data-id") && $(this).attr("data-id") != $("#newMenuItemID").val() && e.push("This Item Name is already in use, please choose another name")
    }), ($("#newMenuItemDescription").val().length < 3 || $("#newMenuItemDescription").val().length > 2048) && e.push("Item Description must be between 3 and 2048 characters long"), /^[0-9.]*$/.test($("#newMenuItemPrice").val()) == 0 && e.push("Price can only contain numbers and a period"), /^[0-9]*$/.test($("#newMenuItemMin").val()) == 0 && e.push("Minimum order can only contain numbers"), /^[0-9]*$/.test($("#newMenuItemMax").val()) == 0 && e.push("Maximum order can only contain numbers"), ($("#newMenuItemMin").val() == "" || parseInt($("#newMenuItemMin").val()) < 0 || parseInt($("#newMenuItemMin").val()) > 1e6) && e.push("Minimum order must be between 0 and 1000"), ($("#newMenuItemMax").val() == "" || parseInt($("#newMenuItemMax").val()) < 1 || parseInt($("#newMenuItemMax").val()) > 1e6) && e.push("Maximum order must be between 1 and 1000"), ($("#newMenuItemPrice").val() == "" || parseInt($("#newMenuItemPrice").val()) < 0 || parseInt($("#newMenuItemPrice").val()) > 1e6) && e.push("Price per item must be between " + FormatDollars(0) + " and " + FormatDollars(1e4)), e
}

function EncodeMenuItemData(e, t) {
    return t ? (e.first("div").data("name", t.name), e.first("div").attr("data-id", t.id), e.first("div").attr("data-type", t.type), e.first("div").data("description", t.description), e.first("div").data("pictures", JSON.stringify(t.pictures)), e.first("div").attr("data-price", t.price), e.first("div").attr("data-min", t.min), e.first("div").attr("data-max", t.max), e.find("[name=menuItemName]").first().empty().append(t.name), e.find("[name=menuItemPrice]").first().empty().append("Price: " + FormatDollars(t.price)), e.find("[name=menuItemMin]").first().empty().append("Min: " + t.min), e.find("[name=menuItemMax]").first().empty().append("Max: " + t.max)) : (e.first("div").data("name", $("#newMenuItemName").val()), e.first("div").attr("data-id", $("#newMenuItemID").val()), e.first("div").attr("data-type", $("#newMenuItemType").val()), e.first("div").data("description", $("#newMenuItemDescription").val()), e.first("div").data("pictures", JSON.stringify(PicturesToArray($("#newMenuItemPictures")))), e.first("div").attr("data-price", $("#newMenuItemPrice").val()), e.first("div").attr("data-min", $("#newMenuItemMin").val()), e.first("div").attr("data-max", $("#newMenuItemMax").val()), e.find("[name=menuItemName]").first().empty().append($("#newMenuItemName").val()), e.find("[name=menuItemPrice]").first().empty().append("Price: " + FormatDollars($("#newMenuItemPrice").val())), e.find("[name=menuItemMin]").first().empty().append("Min: " + $("#newMenuItemMin").val()), e.find("[name=menuItemMax]").first().empty().append("Max: " + $("#newMenuItemMax").val())), e
}

function ValidatePersonnel() {
    var e = [];
    return $("#newPersonnelPrice").val($("#newPersonnelPrice").val().replace(/[$,]/g, "")), ($("#newPersonnelName").val().length < 3 || $("#newPersonnelName").val().length > 128) && e.push("Personnel Name must be at between 3 and 128 characters long"), $("div.personnel").each(function() {
        $(this).data("name") == $("#newPersonnelName").val() && $(this).attr("data-id") && $(this).attr("data-id") != $("#newPersonnelID").val() && e.push("This Personnel Name is already in use, please choose another name")
    }), ($("#newPersonnelDescription").val().length < 3 || $("#newPersonnelDescription").val().length > 2048) && e.push("Personnel Description must be between 3 and 2048 characters long"), /^[0-9.]*$/.test($("#newPersonnelPrice").val()) == 0 && e.push("Price can only contain numbers and a period"), /^[0-9]*$/.test($("#newPersonnelMin").val()) == 0 && e.push("Minimum order can only contain numbers"), /^[0-9]*$/.test($("#newPersonnelMax").val()) == 0 && e.push("Maximum order can only contain numbers"), ($("#newPersonnelMin").val() == "" || parseInt($("#newPersonnelMin").val()) < 0 || parseInt($("#newPersonnelMin").val()) > 1e6) && e.push("Minimum order must be between 0 and 1000"), ($("#newPersonnelMax").val() == "" || parseInt($("#newPersonnelMax").val()) < 1 || parseInt($("#newPersonnelMax").val()) > 1e6) && e.push("Maximum order must be between 1 and 1000"), ($("#newPersonnelPrice").val() == "" || parseInt($("#newPersonnelPrice").val()) < 0 || parseInt($("#newPersonnelPrice").val()) > 1e6) && e.push("Price per item must be between " + FormatDollars(0) + " and " + FormatDollars(1e4)), $("#fgHours .timeslot-widget tbody tr").length < 1 && e.push("You must specify the availability"), $(".timeslot-widget:visible").tsWidget("validate") || e.push("Invalid timeslot definition"), e
}

function EncodePersonnelData(e, t) {
    if (!t) {
        e.first("div").data("name", $("#newPersonnelName").val()), e.first("div").attr("data-id", $("#newPersonnelID").val()), e.first("div").data("description", $("#newPersonnelDescription").val()), e.first("div").attr("data-price", $("#newPersonnelPrice").val()), e.first("div").attr("data-min", $("#newPersonnelMin").val()), e.first("div").attr("data-max", $("#newPersonnelMax").val()), e.first("div").attr("data-req", $("#newPersonnelReq").val()), e.first("div").data("data-hours", $("#fgHours .timeslot-widget").tsWidget("save")), e.first("div").data("deposit", $("#selectDepositList option:selected").attr("value")), e.first("div").data("refund", $("#selectRefundList option:selected").attr("value")), e.find("[name=personnelName]").first().empty().append($("#newPersonnelName").val()), e.find("[name=personnelPrice]").first().empty().append("Price: " + FormatDollars($("#newPersonnelPrice").val())), e.find("[name=personnelMin]").first().empty().append("Min: " + $("#newPersonnelMin").val()), e.find("[name=personnelMax]").first().empty().append("Max: " + $("#newPersonnelMax").val());
        var n = [];
        $("#selPersonnelResources option:selected").each(function() {
            n.push($(this).attr("data-id"))
        }), n.length == 0 && n.push("0"), e.first("div").attr("data-resources", JSON.stringify(n))
    } else e.first("div").data("name", t.name), e.first("div").attr("data-id", t.id), e.first("div").data("description", t.description), e.first("div").attr("data-price", t.price), e.first("div").attr("data-min", t.min), e.first("div").attr("data-max", t.max), e.first("div").attr("data-req", t.req), e.first("div").data("data-hours", t.hours), e.first("div").data("deposit", t.deposit), e.first("div").data("refund", t.refund), e.find("[name=personnelName]").first().empty().append(t.name), e.find("[name=personnelPrice]").first().empty().append("Price: " + FormatDollars(t.price)), e.find("[name=personnelMin]").first().empty().append("Min: " + t.min), e.find("[name=personnelMax]").first().empty().append("Max: " + t.max), e.first("div").attr("data-resources", t.resources);
    return e
}

function ValidateAddon() {
    var e = [];
    return $("#newAddonPrice").val($("#newAddonPrice").val().replace(/[$,]/g, "")), ($("#newAddonName").val().length < 3 || $("#newAddonName").val().length > 128) && e.push("Addon Name must be at between 3 and 128 characters long"), $("div.addon").each(function() {
        $(this).data("name") == $("#newAddonName").val() && $(this).attr("data-id") && $(this).attr("data-id") != $("#newAddonID").val() && e.push("This Addon Name is already in use, please choose another name")
    }), ($("#newAddonDescription").val().length < 3 || $("#newAddonDescription").val().length > 2048) && e.push("Addon Description must be between 3 and 2048 characters long"), /^[0-9.]*$/.test($("#newAddonPrice").val()) == 0 && e.push("Price can only contain numbers and a period"), /^[0-9]*$/.test($("#newAddonMinimum").val()) == 0 && e.push("Minimum order can only contain numbers"), /^[0-9]*$/.test($("#newAddonMaximum").val()) == 0 && e.push("Maximum order can only contain numbers"), $("#selectRefundList option:selected").length != 1 && e.push("One refund policy must be assigned"), $("#selectDepositList option:selected").length != 1 && e.push("One deposit policy must be assigned"), ($("#newAddonMinimum").val() == "" || parseInt($("#newAddonMinimum").val()) < 0 || parseInt($("#newAddonMinimum").val()) > 1e6) && e.push("Minimum order must be between 0 and 1000"), ($("#newAddonMaximum").val() == "" || parseInt($("#newAddonMaximum").val()) < 1 || parseInt($("#newAddonMaximum").val()) > 1e6) && e.push("Maximum order must be between 1 and 1000"), ($("#newAddonPrice").val() == "" || parseFloat($("#newAddonPrice").val()) < 0 || parseFloat($("#newAddonPrice").val()) > 1e6) && e.push("Price per order must be between " + FormatDollars(0) + " and " + FormatDollars(1e4)), $("#fgHours .timeslot-widget tbody tr").length < 1 && e.push("You must specify the availability"), $(".timeslot-widget:visible").tsWidget("validate") || e.push("Invalid timeslot definition"), e
}

function EncodeAddonData(e, t) {
    return t ? (e.first("div").data("name", t.name), e.first("div").attr("data-id", t.id), e.first("div").data("description", t.description), e.first("div").attr("data-type", t.type), e.first("div").data("pictures", JSON.stringify(t.pictures)), e.first("div").attr("data-minimum", t.minimum), e.first("div").attr("data-maximum", t.maximum), e.first("div").attr("data-deliverable", t.deliverable), e.first("div").attr("data-price", t.price), e.first("div").data("deposit", t.deposit), e.first("div").data("refund", t.refund), e.first("div").data("data-hours", t.hours), e.find("[name=addOnName]").first().empty().append(t.name), e.find("[name=addOnPrice]").first().empty().append("Price: " + FormatDollars(t.price)), e.find("[name=addOnMin]").first().empty().append("Min: " + t.minimum), e.find("[name=addOnMax]").first().empty().append("Max: " + t.maximum)) : (e.first("div").data("name", $("#newAddonName").val()), e.first("div").attr("data-id", $("#newAddonID").val()), e.first("div").data("description", $("#newAddonDescription").val()), e.first("div").attr("data-type", $("#newAddonType").val()), e.first("div").data("pictures", JSON.stringify(PicturesToArray($("#newAddonPictures")))), e.first("div").attr("data-minimum", $("#newAddonMinimum").val()), e.first("div").attr("data-maximum", $("#newAddonMaximum").val()), e.first("div").attr("data-deliverable", $("#newAddonDeliver").prop("checked") ? 1 : 0), e.first("div").attr("data-price", $("#newAddonPrice").val()), e.first("div").data("deposit", $("#selectDepositList option:selected").attr("value")), e.first("div").data("refund", $("#selectRefundList option:selected").attr("value")), e.first("div").data("data-hours", $("#fgHours .timeslot-widget").tsWidget("save")), e.find("[name=addOnName]").first().empty().append($("#newAddonName").val()), e.find("[name=addOnPrice]").first().empty().append("Price: " + FormatDollars($("#newAddonPrice").val())), e.find("[name=addOnMin]").first().empty().append("Min: " + $("#newAddonMinimum").val()), e.find("[name=addOnMax]").first().empty().append("Max: " + $("#newAddonMaximum").val())), e
}

function ValidateDeposit() {
    var e = [];
    return $("#newDepositThreshold").val($("#newDepositThreshold").val().replace(/[$,]/g, "")), $("#newDepositPerc").val($("#newDepositPerc").val().replace(/%/g, "")), ($("#newDepositName").val().length < 3 || $("#newDepositName").val().length > 128) && e.push("Policy Name must be at between 3 and 128 characters long"), $("div.deposit").each(function() {
        $(this).data("name") == $("#newDepositName").val() && $(this).attr("data-id") && $(this).attr("data-id") != $("#newDepositID").val() && e.push("This Policy Name is already in use, please choose another name")
    }), /^[0-9.]*$/.test($("#newDepositThreshold").val()) == 0 && e.push("'Applies if over' threshold can only contain numbers and a period"), /^[0-9]*$/.test($("#newDepositPerc").val()) == 0 && e.push("Deposit percentage must be a whole number"), /^[0-9]*$/.test($("#newDepositFull").val()) == 0 && e.push("Full Payment Due can only contain numbers"), ($("#newDepositFull").val() == "" || parseInt($("#newDepositFull").val()) < 1 || parseInt($("#newDepositFull").val()) > 365) && e.push("Full Payment Due must be between 1 and 365"), ($("#newDepositThreshold").val() == "" || parseFloat($("#newDepositThreshold").val()) < 0 || parseFloat($("#newDepositThreshold").val()) > 1e4) && e.push("'Applies if over' threshold must be between " + FormatDollars(0) + " and " + FormatDollars(1e4)), ($("#newDepositPerc").val() == "" || parseInt($("#newDepositPerc").val()) < 0 || parseInt($("#newDepositPerc").val()) > 100) && e.push("Deposit percentage must be between 0 and 100"), e
}

function EncodeDepositData(e, t) {
    return t ? (e.first("div").data("name", t.name), e.first("div").attr("data-id", t.id), e.first("div").attr("data-threshold", t.threshold), e.first("div").attr("data-perc", t.perc), e.first("div").attr("data-amount", t.amount), e.first("div").attr("data-full", t.full), e.find("[name=depositName]").first().empty().append(t.name), e.find("[name=depositPerc]").first().empty().append("Amount: " + FormatDollars(t.amount) + " + " + t.perc + "%")) : (e.first("div").data("name", $("#newDepositName").val()), e.first("div").attr("data-id", $("#newDepositID").val()), e.first("div").attr("data-threshold", $("#newDepositThreshold").val()), e.first("div").attr("data-perc", $("#newDepositPerc").val()), e.first("div").attr("data-amount", $("#newDepositAmount").val()), e.first("div").attr("data-full", $("#newDepositFull").val()), e.find("[name=depositName]").first().empty().append($("#newDepositName").val()), e.find("[name=depositPerc]").first().empty().append("Amount: " + FormatDollars($("#newDepositAmount").val()) + " + " + $("#newDepositPerc").val() + "%")), e
}

function ValidateRefund() {
    var e = [];
    return ($("#newRefundName").val().length < 3 || $("#newRefundName").val().length > 128) && e.push("Policy Name must be at between 3 and 128 characters long"), $("div.refund").each(function() {
        $(this).data("name") == $("#newRefundName").val() && $(this).attr("data-id") && $(this).attr("data-id") != $("#newRefundID").val() && e.push("This Policy Name is already in use, please choose another name")
    }), (/^[0-9]*$/.test($("input.txtRefundFee").val()) == 0 || $("input.txtRefundFee").val() > 100 || $("input.txtRefundFee").val() < 0 || $("input.txtRefundFee").val().length < 1) && e.push("Refund Fee percentage must be a whole number between 0 and 100"), (/^[0-9]*$/.test($("input.txtRefundDays").val()) == 0 || $("input.txtRefundDays").val() > 365 || $("input.txtRefundDays").val() < 0 || $("input.txtRefundDays").val().length < 1) && e.push("Refund Days must be a whole number between 0 and 365"), e
}

function EncodeRefundData(e, t) {
    if (!t) {
        e.first("div").data("name", $("#newRefundName").val()), e.first("div").attr("data-id", $("#newRefundID").val());
        var n = [];
        $("div.rPolicyDetail").each(function() {
            n.push({
                days: $(this).find("input.txtRefundDays").first().val(),
                fee: $(this).find("input.txtRefundFee").first().val()
            })
        }), n.sort(function(e, t) {
            return t.days - e.days
        }), e.first("div").attr("data-policy", JSON.stringify(n)), e.find("[name=refundName]").first().empty().append($("#newRefundName").val())
    } else e.first("div").data("name", t.name), e.first("div").attr("data-id", t.id), e.first("div").attr("data-policy", t.policy), e.find("[name=refundName]").first().empty().append(t.name);
    return e
}

function EncodeContactData(e, t) {
    return t ? (e.data("name", t.name), e.data("title", t.title), e.data("email", t.email), e.data("phone", t.phone), e.data("comments", t.comments), e.prop("label", t.name)) : (e.data("name", $("#newContactName").val()), e.data("title", $("#newContactTitle").val()), e.data("email", $("#newContactEmail").val()), e.data("phone", $("#newContactPhone").val()), e.data("comments", $("#newContactComments").val()), e.prop("label", $("#newContactName").val())), e
}

function PopulateResourceData(e) {
    $("#newResourceName").val(e.first("div").data("name")), $("#newResourceID").val(e.first("div").attr("data-id")), $("#newResourceDescription").val(e.first("div").data("description")), $("#newResourceType").find("option[value='" + SanitizeAttr(e.first("div").attr("data-type")) + "']").prop("selected", !0), $("#newResourcePictures").data("pictures", e.first("div").data("pictures")), $("#newResourceCapacity").val(e.first("div").attr("data-capacity")), $("#newResourceSeats").val(e.first("div").attr("data-seats")), $("#newResourceCleanupCost").val(e.first("div").attr("data-cleanupcost")), $("#newResourceCleanupTime").val(e.first("div").attr("data-cleanup")), $("#newResourceMinDuration").val(e.first("div").attr("data-duration")), $("#newResourceIncrement").val(e.first("div").attr("data-increment")), $("#newResourceLeadTime").val(e.first("div").attr("data-lead")), $("#newResourceRate").val(e.first("div").attr("data-rate")), e.first("div").attr("data-over21") == "true" ? $("#newResourceOver21").prop("checked", !0) : $("#newResourceOver21").prop("checked", !1), e.first("div").attr("data-autoapprove") == "true" ? $("#newResourceAutoApprove").prop("checked", !0) : $("#newResourceAutoApprove").prop("checked", !1), e.first("div").attr("data-linked") == "true" ? $("#newResourceLinked").prop("checked", !0) : $("#newResourceLinked").prop("checked", !1), e.first("div").attr("data-timeslots") == 1 ? $("#billingTimeslot").trigger("click") : $("#billingHourly").trigger("click"), $("#selectDepositList").find("option[value='" + SanitizeAttr(e.first("div").data("deposit")) + "']").prop("selected", !0), $("#selectRefundList").find("option[value='" + SanitizeAttr(e.first("div").data("refund")) + "']").prop("selected", !0), $("#newResourceType")[0].sumo.reload(), $("#selectRefundList")[0].sumo.reload(), $("#selectDepositList")[0].sumo.reload(), $("#fgHours .timeslot-widget").tsWidget("restore", e.first("div").data("data-hours")), $("#fgSpecialRates .timeslot-widget").tsWidget("restore", e.first("div").data("data-rates")), $("#fgTimeslots .timeslot-widget").tsWidget("restore", e.first("div").data("data-slots")), PopulateAddonList(), PopulateAddonSelection(e);
    var t;
    $("#newResourcePictures").data("pictures") ? t = $.parseJSON($("#newResourcePictures").data("pictures")) : t = [];
    for (var n = 0; n < t.length; n++) InsertPicture($("#newResourcePictures"), t[n].url, t[n].caption);
    for (var n = $("#newResourcePictures").find("div.pic").length; n > $("#newResourcePictures").attr("data-limit"); n--) $("#newResourcePictures").find("div.pic").last().remove();
    var r = "";
    e.parents("div.space").each(function() {
        r += $(this).data("name") + " <B>></B> "
    }), r += e.data("name"), $("#newResourceBreadcrumbs").empty().append("<em>" + r + "</em>"), $("#newResourceBreadcrumbs").css({
        display: "block"
    });
    if (e.first("div").attr("data-id")) {
        var i = {
            method: "fCheckIfEditOK",
            resourceid: e.attr("data-id")
        };
        Post(i).then(function(e) {
            e["result"] != "success"
        })
    }
}

function PopulateMenuData(e) {
    $("#newMenuName").val(e.first("div").data("name")), $("#newMenuID").val(e.first("div").attr("data-id")), $("#newMenuDescription").val(e.first("div").data("description")), $("#selectDepositList").find("option[value='" + SanitizeAttr(e.first("div").data("deposit")) + "']").prop("selected", !0), $("#selectRefundList").find("option[value='" + SanitizeAttr(e.first("div").data("refund")) + "']").prop("selected", !0), $("#fgHours .timeslot-widget").tsWidget("restore", e.first("div").data("data-hours")), $("#selectRefundList")[0].sumo.reload(), $("#selectDepositList")[0].sumo.reload()
}

function PopulateMenuItemData(e) {
    $("#newMenuItemName").val(e.first("div").data("name")), $("#newMenuItemID").val(e.first("div").attr("data-id")), $("#newMenuItemType").find("option[value='" + SanitizeAttr(e.first("div").attr("data-type")) + "']").prop("selected", !0), $("#newMenuItemDescription").val(e.first("div").data("description")), $("#newMenuItemPictures").data("pictures", e.first("div").data("pictures")), $("#newMenuItemPrice").val(e.first("div").attr("data-price")), $("#newMenuItemMin").val(e.first("div").attr("data-min")), $("#newMenuItemMax").val(e.first("div").attr("data-max")), $("#newMenuItemType")[0].sumo.reload();
    var t;
    $("#newMenuItemPictures").data("pictures") ? t = $.parseJSON($("#newMenuItemPictures").data("pictures")) : t = [];
    for (var n = 0; n < t.length; n++) InsertPicture($("#newMenuItemPictures"), t[n].url, t[n].caption);
    for (var n = $("#newMenuItemPictures").find("div.pic").length; n > $("#newMenuItemPictures").attr("data-limit"); n--) $("#newMenuItemPictures").find("div.pic").last().remove();
    var r = "";
    e.parents("div.menu").each(function() {
        r += $(this).data("name") + " <B>></B> "
    }), r += e.data("name"), $("#newMenuItemBreadcrumbs").empty().append("<em>" + r + "</em>"), $("#newMenuItemBreadcrumbs").css({
        display: "block"
    })
}

function PopulatePersonnelData(e) {
    $("#newPersonnelName").val(e.first("div").data("name")), $("#newPersonnelID").val(e.first("div").attr("data-id")), $("#newPersonnelDescription").val(e.first("div").data("description")), $("#newPersonnelPrice").val(e.first("div").attr("data-price")), $("#newPersonnelMin").val(e.first("div").attr("data-min")), $("#newPersonnelMax").val(e.first("div").attr("data-max")), $("#newPersonnelReq").val(e.first("div").attr("data-req")), $("#selectDepositList").find("option[value='" + SanitizeAttr(e.first("div").data("deposit")) + "']").prop("selected", !0), $("#selectRefundList").find("option[value='" + SanitizeAttr(e.first("div").data("refund")) + "']").prop("selected", !0), $("#fgHours .timeslot-widget").tsWidget("restore", e.first("div").data("data-hours")), $("#selectRefundList")[0].sumo.reload(), $("#selectDepositList")[0].sumo.reload();
    var t = $.parseJSON(e.first("div").attr("data-resources"));
    for (var n = 0; n < t.length; n++) $("#selPersonnelResources option[data-id='" + SanitizeAttr(t[n]) + "']").prop("selected", !0);
    typeof $("#selPersonnelResources")[0].sumo != "undefined" ? $("#selPersonnelResources")[0].sumo.reload() : $("#selPersonnelResources").SumoSelect()
}

function PopulateAddonData(e) {
    $("#newAddonName").val(e.first("div").data("name")), $("#newAddonID").val(e.first("div").attr("data-id")), $("#newAddonDescription").val(e.first("div").data("description")), $("#newAddonType").find("option[value='" + SanitizeAttr(e.first("div").attr("data-type")) + "']").prop("selected", !0), $("#newAddonPictures").data("pictures", e.first("div").data("pictures")), $("#newAddonMinimum").val(e.first("div").attr("data-minimum")), $("#newAddonMaximum").val(e.first("div").attr("data-maximum")), $("#newAddonDeliver").prop("checked", e.first("div").attr("data-deliverable") == 1 ? !0 : !1), $("#newAddonPrice").val(e.first("div").attr("data-price")), $("#selectDepositList").find("option[value='" + SanitizeAttr(e.first("div").data("deposit")) + "']").prop("selected", !0), $("#selectRefundList").find("option[value='" + SanitizeAttr(e.first("div").data("refund")) + "']").prop("selected", !0), $("#fgHours .timeslot-widget").tsWidget("restore", e.first("div").data("data-hours")), $("#newAddonType")[0].sumo.reload(), $("#selectRefundList")[0].sumo.reload(), $("#selectDepositList")[0].sumo.reload();
    var t;
    $("#newAddonPictures").data("pictures") ? t = $.parseJSON($("#newAddonPictures").data("pictures")) : t = [];
    for (var n = 0; n < t.length; n++) InsertPicture($("#newAddonPictures"), t[n].url, t[n].caption);
    for (var n = $("#newAddonPictures").find("div.pic").length; n > $("#newAddonPictures").attr("data-limit"); n--) $("#newAddonPictures").find("div.pic").last().remove();
    e.first("div").attr("data-id")
}

function PopulateDepositData(e) {
    $("#newDepositName").val(e.first("div").data("name")), $("#newDepositID").val(e.first("div").attr("data-id")), $("#newDepositThreshold").val(e.first("div").attr("data-threshold")), $("#newDepositPerc").val(e.first("div").attr("data-perc")), $("#newDepositAmount").val(e.first("div").attr("data-amount")), $("#newDepositFull").val(e.first("div").attr("data-full"))
}

function PopulateRefundData(e) {
    $("#newRefundName").val(e.first("div").data("name")), $("#newRefundID").val(e.first("div").attr("data-id"));
    var t = $.parseJSON(e.first("div").attr("data-policy"));
    for (var n = 0; n < t.length; n++) $("#rPolicyList").prepend("<div class='rPolicyDetail'>					<input type='text' class='txtRefundFee' placeholder='00' value='" + SanitizeAttr(t[n].fee) + "'/><b>&#37; fee</b> if cancelled					<button class='btn btn-xs pull-right btnrPolicyDetailDelete'><i class='glyphicon glyphicon-trash'></i></button><br>					<div class='clearfix'></div>					<input type='text' class='txtRefundDays' placeholder='00' value='" + SanitizeAttr(t[n].days) + "'/> days or less before the booking start date				</div>")
}

function PopulateContactData(e) {
    $("#newContactName").val(e.data("name")), $("#newContactTitle").val(e.data("title")), $("#newContactEmail").val(e.data("email")), $("#newContactPhone").val(e.data("phone")), $("#newContactComments").val(e.data("comments"))
}

function PopulateVenueTypes(e) {
    var t = new $.Deferred,
        n = {
            method: "fGetVenueTypes"
        };
    return Post(n).then(function(n) {
        if (n["result"] == "success")
            for (var r = 0; r < n.data.length; r++) e.append($("<option></option>", {
                value: n.data[r].id,
                text: n.data[r].name
            }));
        e.SumoSelect(), t.resolve()
    }), t.promise()
}

function PopulateVenueStyles(e) {
    var t = new $.Deferred,
        n = {
            method: "fGetVenueStyles"
        };
    return Post(n).then(function(n) {
        if (n["result"] == "success")
            for (var r = 0; r < n.data.length; r++) e.append($("<option></option>", {
                value: n.data[r].id,
                text: n.data[r].name
            }));
        e.SumoSelect({
            placeholder: "Select Features...",
            okCancelInMulti: !0
        }), t.resolve()
    }), t.promise()
}

function PopulateVenueFeatures(e) {
    var t = new $.Deferred,
        n = {
            method: "fGetVenueFeatures"
        };
    return Post(n).then(function(n) {
        if (n["result"] == "success") {
            e.find("option").remove();
            for (var r = 0; r < n.data.length; r++) e.append($("<option></option>", {
                value: n.data[r].id,
                text: n.data[r].name
            }))
        }
        e.SumoSelect({
            placeholder: "Select Features...",
            okCancelInMulti: !0
        }), t.resolve()
    }), t.promise()
}

function PopulateResourceTypes(e) {
    var t = new $.Deferred,
        n = {
            method: "fGetResourceTypes"
        };
    return Post(n).then(function(n) {
        if (n["result"] == "success")
            for (var r = 0; r < n.data.length; r++) e.append($("<option></option>", {
                value: n.data[r].id,
                text: n.data[r].name
            }));
        typeof e[0].sumo != "undefined" ? e[0].sumo.reload() : e.SumoSelect(), t.resolve()
    }), t.promise()
}

function PopulateAddonTypes(e) {
    var t = new $.Deferred,
        n = {
            method: "fGetAddonTypes"
        };
    return Post(n).then(function(n) {
        if (n["result"] == "success")
            for (var r = 0; r < n.data.length; r++) e.append($("<option></option>", {
                value: n.data[r].id,
                text: n.data[r].name
            }));
        typeof e[0].sumo != "undefined" ? e[0].sumo.reload() : e.SumoSelect(), t.resolve()
    }), t.promise()
}

function PopulateMenuItemTypes() {
    var e = new $.Deferred,
        t = {
            method: "fGetMenuItemTypes"
        };
    return Post(t).then(function(t) {
        if (t["result"] == "success")
            for (var n = 0; n < t.data.length; n++) $("#newMenuItemType").append($("<option></option>", {
                value: t.data[n].id,
                text: t.data[n].name
            }));
        typeof $("#newMenuItemType")[0].sumo != "undefined" ? $("#newMenuItemType")[0].sumo.reload() : $("#newMenuItemType").SumoSelect(), e.resolve()
    }), e.promise()
}

function PopulateDepositList() {
    $("div.deposit").each(function() {
        $("#selectDepositList").append($("<option></option>", {
            value: $(this).data("name"),
            text: $(this).data("name")
        }))
    }), typeof $("#selectDepositList")[0].sumo != "undefined" ? $("#selectDepositList")[0].sumo.reload() : $("#selectDepositList").SumoSelect()
}

function PopulateRefundList() {
    $("div.refund").each(function() {
        $("#selectRefundList").append($("<option></option>", {
            value: $(this).data("name"),
            text: $(this).data("name")
        }))
    }), typeof $("#selectRefundList")[0].sumo != "undefined" ? $("#selectRefundList")[0].sumo.reload() : $("#selectRefundList").SumoSelect()
}

function PopulateAddonList() {
    $("div.addon").each(function() {
        $("#selAddonsAvailable").append($("<option></option>", {
            value: $(this).data("name"),
            text: $(this).data("name")
        }))
    }), typeof $("#selAddonsAvailable")[0].sumo != "undefined" ? $("#selAddonsAvailable")[0].sumo.reload() : $("#selAddonsAvailable").SumoSelect({
        okCancelInMulti: !0,
        selectAll: !0
    })
}

function BuildAddonArray() {
    var e = [];
    return $("#selAddonsAvailable option:selected").each(function() {
        e.push($(this).attr("value"))
    }), e
}

function PopulateAddonSelection(e) {
    if (!e.data("addons")) return;
    var t = $.parseJSON(e.data("addons"));
    for (var n = 0; n < t.length; n++) $("#selAddonsAvailable option[value='" + String(t[n]) + "']").prop("selected", !0);
    typeof $("#selAddonsAvailable")[0].sumo != "undefined" ? $("#selAddonsAvailable")[0].sumo.reload() : $("#selAddonsAvailable").SumoSelect({
        okCancelInMulti: !0,
        selectAll: !0
    })
}

function ColorResources(e, t) {
    var n = ["none", "#c5c8d3", "#cadee6", "#d3e6ca", "#e0e6ca", "#e6daca", "#e6caca"];
    e || (e = 0), e > 6 && (e = 1), t ? t.css({
        "background-color": n[e]
    }) : t = $("div.resource_table").first(), t.children("div.creatorcategory.space").each(function() {
        ColorResources(e + 1, $(this))
    })
}

function ColorVenueLogo() {
    $("#venuePictures div.pic").each(function() {
        $(this).css({
            border: "none"
        }), $(this).find("[name=venueLogo]").remove()
    }), $("#venuePictures div.pic").each(function() {
        $(this).css({
            border: "none"
        }), $(this).find("[name=venueHeader]").remove()
    }), $("#venuePictures div.pic").first().prepend("<div style='margin-left:4px;color:green' name='venueLogo'><small><b>Logo</b></small></div>").css({
        border: "3px solid green"
    }), $("#venuePictures div.pic:nth-child(2n)").first().prepend("<div style='margin-left:4px;color:orange' name='venueHeader'><small><b>Profile Header</b></small></div>").css({
        border: "3px solid orange"
    })
}

function CreatePromoDetailsPage() {
    var e = new $.Deferred;
    return $("#divPromoCodeDetails").empty(), LoadPartial("/venue-creator/promo.html", "divPromoCodeDetails").done(function() {
        PopulatePromoResourceList(), $("#txtPromoPerUser").on("blur", function(e) {
            $(this).val($(this).val().replace(/[^0-9.]/g, "")), $(this).val().length > 0 ? $("#chkPromoPerUserUnlim").prop("checked", !1) : $("#chkPromoPerUserUnlim").prop("checked", !0)
        }), $("#txtPromoQuantity").on("blur", function(e) {
            $(this).val($(this).val().replace(/[^0-9.]/g, "")), $(this).val().length > 0 ? $("#chkPromoQuantityUnlim").prop("checked", !1) : $("#chkPromoQuantityUnlim").prop("checked", !0)
        }), $("#txtPromoThreshold").on("blur", function(e) {
            $(this).val($(this).val().replace(/[^0-9.]/g, "")), $(this).val().length < 1 && $(this).val("0"), $(this).val(FormatDollars($(this).val()))
        }), $("#txtPromoAmount").on("blur", function(e) {
            $(this).val($(this).val().replace(/[^0-9.]/g, "")), $("#selPromoType option[value=dollar]:selected").length > 0 && $(this).val(FormatDollars($(this).val()))
        }), $("#txtPromoAmount").blur(function(e) {
            $("#selPromoType option[value=percent]:selected").length > 0 ? $(this).val($(this).val().replace(/[^0-9.]/g, "") + "%") : $(this).val(FormatDollars($(this).val().replace(/[^0-9.]/g, "")))
        }), $("#txtPromoStart").datepicker({
            inline: !0,
            changeMonth: !0,
            changeYear: !0,
            yearRange: "2013:+5",
            dateFormat: "MM d, yy"
        }), $("#txtPromoStop").datepicker({
            inline: !0,
            changeMonth: !0,
            changeYear: !0,
            yearRange: "2013:+5",
            dateFormat: "MM d, yy"
        }), $("input[name='radioExpType']:radio").change(function(e) {
            $("div.promoExpText").remove(), $("#txtPromoExpVal").datepicker("destroy");
            switch ($(this).val()) {
                case "never":
                    $("#expValue").hide();
                    break;
                case "after":
                    $("#expValue").show(), $("#expValue input").width("100px").after("<div class='promoExpText'>minutes after the booking is created</div>"), $("#txtPromoExpVal").val("");
                    break;
                case "before":
                    $("#expValue").show(), $("#expValue input").width("100px").after("<div class='promoExpText'>minutes before the event</div>"), $("#txtPromoExpVal").val("")
            }
        }), $("#btnSavePromoCode").off("click").click(function(e) {
            e.preventDefault();
            var t = ValidatePromoCode();
            if (t.length > 0) {
                var n = "<div class='alert alert-danger'><ul>";
                for (var r = 0; r < t.length; r++) n += "<li>" + t[r] + "</li>";
                n += "</ul></div>", $(this).parents("form").first().find("div.alert-danger").remove(), $(this).parents("form").first().prepend(n)
            } else {
                if ($("#selectPromoCodes option:selected").length > 0) EncodePromoData($("#selectPromoCodes option:selected"));
                else {
                    var i = $("<option/>");
                    i.data("name", $("#txtPromoCode").val()), i.text($("#txtPromoCode").val()), EncodePromoData(i), $("#selectPromoCodes").prepend(i), $("#selectPromoCodes").attr("size", $("#selectPromoCodes option").length + 1)
                }
                $("#divPromoCodeDetails").empty(), $("#selectPromoCodes option:selected").prop("selected", !1)
            }
            $("html, body").animate({
                scrollTop: $("div.alert.alert-danger").first().offset().top
            })
        }), e.resolve()
    }), e.promise()
}

function ClickEdit(e) {
    var t = e.closest("div.creatorcategory");
    t.hasClass("addon") ? ($("#mainModalHeader").empty().append('Edit "' + t.data("name") + '"'), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty(), LoadPartial("/venue-creator/addon.html", "mainModalBody").done(function() {
        PopulateDepositList(), PopulateRefundList(), PopulateAddonTypes($("#newAddonType")).then(function() {
            PopulateAddonData($(t)), ReBindCreatorControls()
        }), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            e.preventDefault();
            var n = ValidateAddon();
            if (n.length < 1) EncodeAddonData($(t)), $("#mainModalBody").empty(), $("#mainModal").modal("hide");
            else {
                $("#newAddonError").empty().append("<ul>");
                for (var r = 0; r < n.length; r++) $("#newAddonError").append("<li>" + n[r] + "</li>");
                $("#newAddonError").append("</ul>"), $("#newAddonError").css({
                    display: "block"
                }), $("#mainModal").animate({
                    scrollTop: 0
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    })) : t.hasClass("deposit") ? ($("#mainModalHeader").empty().append('Edit "' + t.data("name") + '" Policy'), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty(), LoadPartial("/venue-creator/deposit.html", "mainModalBody").done(function() {
        PopulateDepositData($(t)), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            e.preventDefault();
            var n = ValidateDeposit();
            if (n.length < 1) EncodeDepositData($(t)), $("#mainModalBody").empty(), ReBindCreatorControls(), $("#mainModal").modal("hide");
            else {
                $("#newDepositError").empty().append("<ul>");
                for (var r = 0; r < n.length; r++) $("#newDepositError").append("<li>" + n[r] + "</li>");
                $("#newDepositError").append("</ul>"), $("#newDepositError").css({
                    display: "block"
                }), $("#mainModal").animate({
                    scrollTop: 0
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    })) : t.hasClass("refund") ? ($("#mainModalHeader").empty().append('Edit "' + t.data("name") + '" Policy'), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty(), LoadPartial("/venue-creator/refund.html", "mainModalBody").done(function() {
        PopulateRefundData($(t)), ReBindCreatorControls(), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            var n = ValidateRefund();
            if (n.length < 1) EncodeRefundData($(t)), $("#mainModalBody").empty(), ReBindCreatorControls(), $("#mainModal").modal("hide");
            else {
                $("#newRefundError").empty().append("<ul>");
                for (var r = 0; r < n.length; r++) $("#newRefundError").append("<li>" + n[r] + "</li>");
                $("#newRefundError").append("</ul>"), $("#newRefundError").css({
                    display: "block"
                }), $("#mainModal").animate({
                    scrollTop: 0
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    })) : t.hasClass("space") || t.hasClass("resource") ? ($("#mainModalHeader").empty().append('Edit "' + t.data("name") + '"'), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty(), LoadPartial("/venue-creator/resource.html", "mainModalBody").done(function() {
        var e = t.parents("div.creatorcategory").first().data("name");
        $("#mainModalBody").find(":contains('Included with space'):last").empty().append('Included with <small>"' + e + '"</small>'), e || $("#mainModalBody").find(":contains('Included with '):last").parent("div").first().hide(), PopulateDepositList(), PopulateRefundList(), PopulateResourceTypes($("#newResourceType")).then(function() {
            PopulateResourceData($(t)), ReBindCreatorControls()
        }), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            e.preventDefault();
            var n = ValidateResource();
            if (n.length < 1) EncodeResourceData($(t)), $("#mainModalBody").empty(), ReBindCreatorControls(), $("#mainModal").modal("hide");
            else {
                $("#newResourceError").empty().append("<ul>");
                for (var r = 0; r < n.length; r++) $("#newResourceError").append("<li>" + n[r] + "</li>");
                $("#newResourceError").append("</ul>"), $("#newResourceError").css({
                    display: "block"
                }), $("#mainModal").animate({
                    scrollTop: 0
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    })) : t.hasClass("menu") ? ($("#mainModalHeader").empty().append('Edit "' + t.data("name") + '"'), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty(), LoadPartial("/venue-creator/menu.html", "mainModalBody").done(function() {
        PopulateDepositList(), PopulateRefundList(), PopulateMenuData($(t)), ReBindCreatorControls(), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            e.preventDefault();
            var n = ValidateMenu();
            if (n.length < 1) EncodeMenuData(t), $("#mainModalBody").empty(), ReBindCreatorControls(), $("#mainModal").modal("hide");
            else {
                var r = "<ul>";
                for (var i = 0; i < n.length; i++) r += "<li>" + n[i] + "</li>";
                r += "</ul>", $("#newMenuError").append(r), $("#newMenuError").css({
                    display: "block"
                }), $("#mainModal").animate({
                    scrollTop: 0
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    })) : t.hasClass("menuitem") ? ($("#mainModalHeader").empty().append('Edit "' + t.data("name") + '"'), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty(), LoadPartial("/venue-creator/menuitem.html", "mainModalBody").done(function() {
        PopulateMenuItemTypes().then(function() {
            PopulateMenuItemData($(t)), ReBindCreatorControls()
        }), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            e.preventDefault();
            var n = ValidateMenuItem();
            if (n.length < 1) EncodeMenuItemData($(t)), $("#mainModalBody").empty(), ReBindCreatorControls(), $("#mainModal").modal("hide");
            else {
                $("#newMenuItemError").empty().append("<ul>");
                for (var r = 0; r < n.length; r++) $("#newMenuItemError").append("<li>" + n[r] + "</li>");
                $("#newMenuItemError").append("</ul>"), $("#newMenuItemError").css({
                    display: "block"
                }), $("#mainModal").animate({
                    scrollTop: 0
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    })) : t.hasClass("personnel") && ($("#mainModalHeader").empty().append('Edit "' + t.data("name") + '"'), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty(), LoadPartial("/venue-creator/personnel.html", "mainModalBody").done(function() {
        PopulateDepositList(), PopulateRefundList(), $("#selPersonnelResources").append("<option data-id='0'>Any</option>"), $("div.creatorcategory.resource,div.creatorcategory.space").each(function() {
            $(this).attr("data-id") && $("#selPersonnelResources").append("<option data-id='" + SanitizeAttr($(this).attr("data-id")) + "'>" + $(this).data("name") + "</option>")
        }), PopulatePersonnelData($(t)), ReBindCreatorControls(), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            e.preventDefault();
            var n = ValidatePersonnel();
            if (n.length < 1) EncodePersonnelData($(t)), $("#mainModalBody").empty(), ReBindCreatorControls(), $("#mainModal").modal("hide");
            else {
                $("#newPersonnelError").empty().append("<ul>");
                for (var r = 0; r < n.length; r++) $("#newPersonnelError").append("<li>" + n[r] + "</li>");
                $("#newPersonnelError").append("</ul>"), $("#newPersonnelError").css({
                    display: "block"
                }), $("#mainModal").animate({
                    scrollTop: 0
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    }))
}

function ClickCreateDeposit(e) {
    $("#mainModalHeader").empty().append("Add a Deposit Policy"), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty(), LoadPartial("/venue-creator/deposit.html", "mainModalBody").done(function() {
        $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            e.preventDefault();
            var t = ValidateDeposit();
            if (t.length < 1) InsertDeposit($("#newDepositName").val()), $("#mainModalBody").empty(), $("#mainModal").modal("hide");
            else {
                $("#newDepositError").empty().append("<ul>");
                for (var n = 0; n < t.length; n++) $("#newDepositError").append("<li>" + t[n] + "</li>");
                $("#newDepositError").append("</ul>"), $("#newDepositError").css({
                    display: "block"
                }), $("#mainModal").animate({
                    scrollTop: 0
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    })
}

function ClickCreateRefund(e) {
    $("#mainModalHeader").empty().append("Add a Refund Policy"), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty(), LoadPartial("/venue-creator/refund.html", "mainModalBody").done(function() {
        ReBindCreatorControls(), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            e.preventDefault();
            var t = ValidateRefund();
            if (t.length < 1) InsertRefund($("#newRefundName").val()), $("#mainModalBody").empty(), $("#mainModal").modal("hide");
            else {
                $("#newRefundError").empty().append("<ul>");
                for (var n = 0; n < t.length; n++) $("#newRefundError").append("<li>" + t[n] + "</li>");
                $("#newRefundError").append("</ul>"), $("#newRefundError").css({
                    display: "block"
                }), $("#mainModal").animate({
                    scrollTop: 0
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    })
}

function ClickCreateAddon(e) {
    $("#mainModalHeader").empty().append("Create an Addon"), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty(), LoadPartial("/venue-creator/addon.html", "mainModalBody").done(function() {
        PopulateAddonTypes($("#newAddonType")), PopulateDepositList(), PopulateRefundList(), ReBindCreatorControls(), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            e.preventDefault();
            var t = ValidateAddon();
            if (t.length < 1) InsertAddon($("#newAddonName").val()), $("#mainModalBody").empty(), $("#mainModal").modal("hide");
            else {
                $("#newAddonError").empty().append("<ul>");
                for (var n = 0; n < t.length; n++) $("#newAddonError").append("<li>" + t[n] + "</li>");
                $("#newAddonError").append("</ul>"), $("#newAddonError").css({
                    display: "block"
                }), $("#mainModal").animate({
                    scrollTop: 0
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    })
}

function ClickAddResource(e) {
    var t = e.parents("div.creatorcategory").first();
    $("#mainModalHeader").empty().append("Add a resource"), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty(), LoadPartial("/venue-creator/resource.html", "mainModalBody").done(function() {
        var e = t.data("name");
        $("#mainModalBody").find(":contains('Included with space'):last").empty().append('Included with <small>"' + e + '"</small>'), e || $("#mainModalBody").find(":contains('Included with '):last").parent("div").first().hide(), PopulateDepositList(), PopulateRefundList(), PopulateAddonList(), $("#fgHours .timeslot-widget").tsWidget("restore", t.data("data-hours")), $("#fgTimeslots .timeslot-widget").tsWidget("restore", t.data("data-slots")), t.attr("id") == "addvenuespace" ? ($("#newResourceName").val($("#venueName").val() + " (Entire Venue)"), $("#newResourceDescription").val($("#venueDescription").val()), $("#newResourceType").append($("<option>", {
            value: "1",
            text: "space"
        }))) : PopulateResourceTypes($("#newResourceType")), ReBindCreatorControls(), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            e.preventDefault();
            var n = ValidateResource();
            if (n.length < 1) {
                var r = CreateResource($("#newResourceName").val(), $("#newResourceType").val());
                t.attr("id") == "addvenuespace" ? t.after($(r)) : r.hasClass("space") ? t.find("div.addnewspace").last().before($(r)) : t.find("div.clearfix").first().before($(r)), $("#addvenuespace").remove(), $("#mainModalBody").empty(), ReBindCreatorControls(), $("#mainModal").modal("hide")
            } else {
                $("#newResourceError").empty().append("<ul>");
                for (var i = 0; i < n.length; i++) $("#newResourceError").append("<li>" + n[i] + "</li>");
                $("#newResourceError").append("</ul>"), $("#newResourceError").css({
                    display: "block"
                }), $("#mainModal").animate({
                    scrollTop: 0
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    })
}

function ClickAddMenu(e) {
    $("#mainModalHeader").empty().append("Add New Menu"), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty(), LoadPartial("/venue-creator/menu.html", "mainModalBody").done(function() {
        PopulateDepositList(), PopulateRefundList(), ReBindCreatorControls(), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            e.preventDefault();
            var t = ValidateMenu();
            if (t.length < 1) $div = CreateMenu($("#newMenuName").val()), $("div.menu_table").append($($div)), $("#mainModalBody").empty(), ReBindCreatorControls(), $("#mainModal").modal("hide");
            else {
                $("#newMenuError").empty().append("<ul>");
                for (var n = 0; n < t.length; n++) $("#newMenuError").append("<li>" + t[n] + "</li>");
                $("#newMenuError").append("</ul>"), $("#newMenuError").css({
                    display: "block"
                }), $("#mainModal").animate({
                    scrollTop: 0
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    })
}

function ClickAddMenuItem(e) {
    $("#mainModalHeader").empty().append("Add menu item"), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty(), LoadPartial("/venue-creator/menuitem.html", "mainModalBody").done(function() {
        PopulateMenuItemTypes(), ReBindCreatorControls(), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(t) {
            t.preventDefault();
            var n = ValidateMenuItem();
            if (n.length < 1) $div = CreateMenuItem($("#newMenuItemName").val()), e.after($($div)), $("#mainModalBody").empty(), ReBindCreatorControls(), $("#mainModal").modal("hide");
            else {
                $("#newMenuItemError").empty().append("<ul>");
                for (var r = 0; r < n.length; r++) $("#newMenuItemError").append("<li>" + n[r] + "</li>");
                $("#newMenuItemError").append("</ul>"), $("#newMenuItemError").css({
                    display: "block"
                }), $("#mainModal").animate({
                    scrollTop: 0
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    })
}

function ClickAddPersonnel(e) {
    $("#mainModalHeader").empty().append("Add New Personnel"), $("#mainModalAcceptBtn").empty().append("OK").css({
        display: "inline"
    }), $("#mainModalCloseBtn").empty().append("Cancel").css({
        display: "inline"
    }), $("#mainModalBody").empty(), LoadPartial("/venue-creator/personnel.html", "mainModalBody").done(function() {
        PopulateDepositList(), PopulateRefundList(), $("#selPersonnelResources option").remove(), $("#selPersonnelResources").append("<option data-id='0'>Any</option>"), $("div.creatorcategory.resource,div.creatorcategory.space").each(function() {
            $(this).attr("data-id") && $("#selPersonnelResources").append("<option data-id='" + SanitizeAttr($(this).attr("data-id")) + "'>" + $(this).data("name") + "</option>")
        }), ReBindCreatorControls(), $("#mainModal").modal("show"), $("#mainModalAcceptBtn").off("click").click(function(e) {
            e.preventDefault();
            var t = ValidatePersonnel();
            if (t.length < 1) $div = CreatePersonnel($("#newPersonnelName").val()), $("div.personnel_table").append($($div)), $("#mainModalBody").empty(), ReBindCreatorControls(), $("#mainModal").modal("hide");
            else {
                $("#newPersonnelError").empty().append("<ul>");
                for (var n = 0; n < t.length; n++) $("#newPersonnelError").append("<li>" + t[n] + "</li>");
                $("#newPersonnelError").append("</ul>"), $("#newPersonnelError").css({
                    display: "block"
                }), $("#mainModal").animate({
                    scrollTop: 0
                })
            }
        }), $("#mainModalCloseBtn").off("click").click(function(e) {
            $("#mainModalBody").empty(), $("#mainModal").modal("hide")
        })
    })
};