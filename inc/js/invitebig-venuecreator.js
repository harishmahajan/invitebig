function RebindContractUpload()
{
	$("form.contractupload").each(function()
	{
		$("form.contractupload span.fileinput-button").show();
		$(this).fileupload(
		{
			url: "/inc/jQuery-File-Upload-master/server/php/index.php",
			sequentialUploads: true,
			limitMultiFileUploads: 1,
			maxNumberOfFiles: 1,
			maxFileSize: 8388608,
			acceptFileTypes: /(\.|\/)(doc|pdf)$/i,
			done: function (e, data) {
				if (data && data.result && data.result.files && data.result.files.length > 0)
				{
					$("#cFiles").empty();
					$("#cFiles").append("<div class='input-group'><span class='input-group-addon' style='background:#fefefe'><a href='" + SanitizeAttr(data.result.files[0].url) + "'>" + data.result.files[0].url + "</a></span><button class='btn btn-default' name='delContract'><i class='glyphicon glyphicon-trash'></i></button></div>");
					$("form.contractupload span.fileinput-button").hide();
					$("#cFiles [name='delContract']").off("click").click(function(event)
					{
						event.preventDefault();
						$("#cFiles").empty();
						RebindContractUpload();
					});
				}
			},
		});
	});
}

function ValidateQuestion()
{
	var err = 0;
	
	if ($("#selQuestionResources option:selected").length < 1 && $("#selQuestionAddons option:selected").length < 1 &&
		$("#selQuestionMenus option:selected").length < 1 && $("#selQuestionPersonnel option:selected").length < 1)
	{
		$("#errQuestion").append("<br><br>This question must apply to a resource, addon, menu, or personnel.");
		err = 1;
	}
	
	switch ($("#selQuestionType option:selected").val())
	{
		case "text":
		case "checkbox":
			if ($("#txtQuestionText").val().length < 1)
			{
				$("#errQuestion").append("<br><br>Question field cannot be blank.");
				err = 1;
			}
			break;
		
		case "radio":
			if ($("input[name='txtQuestionAnswer']").length < 2)
			{
				$("#errQuestion").append("<br><br>There must be at least two answers to choose from.");
				err = 1;
			}
			
			var t = 0;
			$("input[name='txtQuestionAnswer']").each(function()
			{
				if ($(this).val().length < 1)
					t++;
			});
			if (t > 0)
			{
				$("#errQuestion").append("<br><br>Answers cannot be blank.");
				err = 1;
			}
			break;
			
		case "select":
			if ($("input[name='txtQuestionAnswer']").length < 2)
			{
				$("#errQuestion").append("<br><br>There must be at least two answers to choose from.");
				err = 1;
			}
			
			var t = 0;
			$("input[name='txtQuestionAnswer']").each(function()
			{
				if ($(this).val().length < 1)
					t++;
			});
			if (t > 0)
			{
				$("#errQuestion").append("<br><br>Answers cannot be blank.");
				err = 1;
			}
			break;
	}
	
	if (err > 0)
	{
		$("#errQuestion").css({"display":"inline-block"});
		return false;
	}
	
	return true;
}

function RebindQuestionnaireControls()
{
	$("#aQuestionAnswerAdd").off("click").click(function(event)
	{
		event.preventDefault();
		$(this).before("<input name='txtQuestionAnswer' class='form-control' style='width:200px;display:inline-block;margin-top:5px' placeholder='Specify the option...'/> <a href='#' name='aQuestionAnswerDel'>Delete Answer</a><br>");
		RebindQuestionnaireControls();
	});
	
	$("a[name='aQuestionAnswerDel']").off("click").click(function(event)
	{
		event.preventDefault();
		$(this).prev("input").remove();
		$(this).next("br").remove();
		$(this).remove();
	});
	
	$("i[name='btnQuestionUp']").off("click").click(function(event)
	{
		event.preventDefault();
		var r = $(this).parents("tr").first();
		var p = r.prev("tr");
		if (p.length > 0)
			p.before(r);
	});
	
	$("i[name='btnQuestionDown']").off("click").click(function(event)
	{
		event.preventDefault();
		var r = $(this).parents("tr").first();
		var n = r.next("tr");
		if (n.length > 0)
			n.after(r);
	});
	
	$("i[name='btnQuestionDel']").off("click").click(function(event)
	{
		event.preventDefault();
		var r = $(this).parents("tr").first();
		
		$("#mainModalHeader").empty().append("Delete?");
		$("#mainModalAcceptBtn").empty().append("Delete").css({"display":"inline"});
		$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
		$("#mainModalBody").empty().append("Are you sure you want to delete this question?<br><br>\"" + r.find("td").first().text() + "\"");
		$("#mainModal").modal("show");
		$("#mainModalAcceptBtn").off("click").click(function(event) 
		{
			event.preventDefault();
			r.remove();
			$("#mainModalBody").empty();$("#mainModal").modal("hide");
		});
		$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide"); });	
	});
	
	$("#tblQuestions tbody tr td").off("click").click(function(event)
	{
		event.preventDefault();
		PopulateQuestionData($(this).parents("tr").first().attr("data-details"));
		$("#divNewQuestionForm legend").empty().append("Edit Question");
		$("#divNewQuestionForm").css({"display":"block"});
		$("html, body").animate({ scrollTop: $("#divNewQuestionForm").offset().top });
	});
	
	$("#tblQuestions tbody tr").each(function()
	{
		$(this).find("td:last").off("click");
	});
}

function InsertQuestion($info)
{
	var row = $("<tr></tr>");
	var data;
	
	if (!$info)
	{
		data = {
			id: $("#txtQuestionID").val(),
			type: $("#selQuestionType option:selected").val(),
			req: $("input[name='chkQuestionReq']:checked").val(),
			text: $("#txtQuestionText").val(),
			choices: [],
			appliesto: {addons:[],menus:[],personnel:[],resources:[]}
		};
		
		if (!data['id'] || data['id'].length < 1)
			data['id'] = "_"+Math.floor((Math.random() * 1000000) + 1);
			
		$("input[name='txtQuestionAnswer']").each(function()
		{
			data['choices'].push($(this).val());
		});
		$("#selQuestionResources option:selected").each(function()
		{
			data['appliesto']['resources'].push($(this).attr("data-id"));
		});
		$("#selQuestionAddons option:selected").each(function()
		{
			data['appliesto']['addons'].push($(this).attr("data-id"));
		});
		$("#selQuestionMenus option:selected").each(function()
		{
			data['appliesto']['menus'].push($(this).attr("data-id"));
		});
		$("#selQuestionPersonnel option:selected").each(function()
		{
			data['appliesto']['personnel'].push($(this).attr("data-id"));
		});
		
		row.append("<td>"+$("#txtQuestionText").val()+"</td><td>"+$("#selQuestionType option:selected").text()+"<td>"+$("input[name='chkQuestionReq']:checked").val()+"</td><td><i name='btnQuestionUp' class='glyphicon glyphicon-chevron-up'></i><i name='btnQuestionDown' class='glyphicon glyphicon-chevron-down'></i><i name='btnQuestionDel' class='glyphicon glyphicon-trash'></i></td>");
	}
	else
	{
		data = {
			id: $info['id'],
			type: $info['type'],
			req: $info['req'],
			text: $info['text'],
			choices: $info['choices'],
			appliesto: $info['appliesto']
		};
		
		var ty = "";
		switch ($info['type'])
		{
			case "text":
				ty = "Text";
				break;
			case "checkbox":
				ty = "Checkbox";
				break;
			case "radio":
				ty = "Multiple Choice";
				break;
			case "select":
				ty = "Dropdown";
				break;
		}
		
		row.append("<td>"+$info['text']+"</td><td>"+ty+"<td>"+$info['req']+"</td><td><i name='btnQuestionUp' class='glyphicon glyphicon-chevron-up'></i><i name='btnQuestionDown' class='glyphicon glyphicon-chevron-down'></i><i name='btnQuestionDel' class='glyphicon glyphicon-trash'></i></td>");
	}
	
	var details = JSON.stringify(data);
	row.attr("data-details",details);
	
	var matched = 0;
	$("#tblQuestions tbody tr").each(function()
	{
		var d = $.parseJSON($(this).attr("data-details"));
		if (d['id'] == data['id'])
		{
			matched = 1;
			
			var newq = $.parseJSON(JSON.stringify(data));
			var old = $.parseJSON($(this).attr("data-details"));
			delete old['req'];
			delete newq['req'];
			delete old['appliesto'];
			delete newq['appliesto'];
			
			if (JSON.stringify(old) != JSON.stringify(newq))
			{
				data['id'] = "_"+Math.floor((Math.random() * 1000000) + 1);
				details = JSON.stringify(data);
				row.attr("data-details",details);
		
				$(this).after(row);
				$(this).remove();
				return false;
			} else {
				$(this).attr("data-details",details);
				return false;
			}
		}
	});
	
	if (matched == 0)
		$("#tblQuestions tbody").append(row);
	
	RebindQuestionnaireControls();
	
	// if id starts with _ then it is a new question
}

function PopulateQuestionData(details)
{
	$("#btnNewQuestion").trigger("click");
	
	var d = $.parseJSON(details);
	
	$("#txtQuestionID").val(d['id']);
	
	for (var i = 0; i < d['appliesto']['resources'].length; i++)
	{
		$("#selQuestionResources option[data-id='" + SanitizeAttr(d['appliesto']['resources'][i]) + "']").prop("selected",true);
	}
		
	for (var i = 0; i < d['appliesto']['addons'].length; i++)
	{
		$("#selQuestionAddons option[data-id='" + SanitizeAttr(d['appliesto']['addons'][i]) + "']").prop("selected",true);
	}
		
	for (var i = 0; i < d['appliesto']['menus'].length; i++)
	{
		$("#selQuestionMenus option[data-id='" + SanitizeAttr(d['appliesto']['menus'][i]) + "']").prop("selected",true);
	}
		
	for (var i = 0; i < d['appliesto']['personnel'].length; i++)
	{
		$("#selQuestionPersonnel option[data-id='" + SanitizeAttr(d['appliesto']['personnel'][i]) + "']").prop("selected",true);
	}
	
	$("#divNewQuestionForm select").each(function(){
		if (typeof $(this)[0].sumo !== "undefined")
			$(this)[0].sumo.reload();
		else $(this).SumoSelect();
	});
	
	if (d['req'] == "no")
		$("#divNewQuestionForm input[name='chkQuestionReq']:last").prop("checked",true);
		
	$("#selQuestionType option").each(function()
	{
		if ($(this).attr("value") == d['type'])
			$(this).prop("selected",true);
	});
	$("#selQuestionType").change();
	
	$("#txtQuestionText").val(d['text']);
	
	if (d['type'] == "radio" || d['type'] == "select")
	{
		$("input[name='txtQuestionAnswer']").each(function()
		{
			$(this).next("a").remove();
			$(this).next("br").remove();
			$(this).remove();
		});
			
		for (var i = 0; i < d['choices'].length; i++)
		{
			var opt = $("<input name='txtQuestionAnswer' class='form-control' style='width:200px;display:inline-block;margin-top:5px' placeholder='Specify the option...'/>");
			opt.val(d['choices'][i]);
			$("#aQuestionAnswerAdd").before(opt);
			$("#aQuestionAnswerAdd").before(" <a href='#' name='aQuestionAnswerDel'>Delete Answer</a><br>");
		}
		RebindQuestionnaireControls();
	}
}

function ReBindCreatorControls()
{	
	$("[name=buttonAddResource]").off("click").click(function(event){ event.preventDefault(); ClickAddResource($(this)); });
	
	$("[name=buttonAddMenu]").off("click").click(function(event){ event.preventDefault(); ClickAddMenu($(this)); });
	
	$("[name=buttonAddMenuItem]").off("click").click(function(event){ event.preventDefault(); ClickAddMenuItem($(this)); });
	
	$("[name=buttonAddPersonnel]").off("click").click(function(event){ event.preventDefault(); ClickAddPersonnel($(this)); });
	
	$("[name=buttonCreateAddon]").off("click").click(function(event){ event.preventDefault(); ClickCreateAddon($(this)); });
	
	$("[name=buttonCreateDeposit]").off("click").click(function(event){ event.preventDefault(); ClickCreateDeposit($(this)); });
	
	$("[name=buttonCreateRefund]").off("click").click(function(event){ event.preventDefault(); ClickCreateRefund($(this)); });
	
	$("[name=buttonDelete]").off("click").click(function(event){ event.preventDefault(); ClickDelete($(this)); });
	
	$("[name=buttonClone]").off("click").click(function(event){ event.preventDefault(); ClickClone($(this)); });
	
	$("[name=buttonEdit]").off("click").click(function(event){ event.preventDefault(); ClickEdit($(this)); });
	
	$("#venueSubmit").off("click").click(function(event)
	{
		event.preventDefault(); 
		/*
		// Disabling CC requirement during registration
		if (window.location.href.indexOf("create-venue") > 0 && $("#venuecreator:visible").length > 0)
		{
			var errors = ValidateVenueProfile();
			if (errors.length > 0)
			{
				$("#mainModalHeader").empty().append("Invalid venue profile information provided");
				$("#mainModalAcceptBtn").empty().append("OK").css({"display":"none"});
				$("#mainModalCloseBtn").empty().append("OK").css({"display":"inline"});
				$("#mainModalBody").empty().append("<div class='alert alert-danger'><ul></ul></div>");
				for (var i = 0; i < errors.length; i++)
				{
					$("#mainModalBody").find("ul").first().append("<li>" + errors[i] + "</li>");
				}
				$("#mainModal").modal("show");
				 
				$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
			}
			else
			{
				$("#planbilling").show();
				$("#venuecreator").hide();
				$("#bodyContent h4").first().text("Choose your subscription plan and payment method");
				$('html, body').animate({ scrollTop: 0 });
			}
		}
		else */ ClickSaveConfig($(this));
	});
	
	$("[name=venueApprove]").off("click").click(function(event){ event.preventDefault(); ClickRequestReview($(this)); });

	$("[name=buttonPictureUp]").off("click").click(function(event)
	{
		event.preventDefault();
		var $pic = $(this).parents("div.pic").first();
		$pic.prev("div.pic").before($pic);
		ColorVenueLogo();
	});
	
	$("[name=buttonPictureDown]").off("click").click(function(event)
	{
		event.preventDefault();
		var $pic = $(this).parents("div.pic").first();
		$pic.next("div.pic").after($pic);
		ColorVenueLogo();
	});
	
	$("[name=buttonPictureDel]").off("click").click(function(event) { event.preventDefault(); $(this).parents("div.pic").first().remove(); ColorVenueLogo(); });
	
	$("[name=fileupload]").each(function()
	{
		if (!$(this).attr("data-init"))
		{
			$(this).fileupload(
			{
				url: "/inc/jQuery-File-Upload-master/server/php/index.php",
				sequentialUploads: true,
				//formData: {script: true},
				limitMultiFileUploads: 4,
				maxFileSize: 8388608
			});
			
			$(this).on('fileuploaddone', function (e, data) 
			{
				if ($(this).find("div.pictures").find("div.pic").length < $(this).find("div.pictures").first().attr("data-limit") &&
					data && data.result && data.result.files && data.result.files.length > 0)
					InsertPicture($(this).find("div.pictures").first(),data.result.files[0].url);
			});
        }
        $(this).attr("data-init","1");
	});
	
	ColorResources();
	
	ColorVenueLogo();
	
	$("div.mgmtuser button.btnMgmtRightsDelete").off("click").click(function(event)
	{
		event.preventDefault();
		$(this).parents("div.mgmtuser").first().remove();
	});
	
	$("div.rPolicyDetail button.btnrPolicyDetailDelete").off("click").click(function(event)
	{
		event.preventDefault();
		$(this).parents("div.rPolicyDetail").first().remove();
	});
	
	$("div.rNewPolicyDetail").off("click").click(function(event)
	{
		event.preventDefault();
		$(this).before("<div class='rPolicyDetail'>\
					<input type='text' class='txtRefundFee' placeholder='00' value=''/><b>&#37; fee</b> if cancelled\
					<button class='btn btn-xs pull-right btnrPolicyDetailDelete'><i class='glyphicon glyphicon-trash'></i></button><br>\
					<div class='clearfix'></div>\
					<input type='text' class='txtRefundDays' placeholder='00' value=''/> days or less before the booking start date\
				</div>");
	});
	
	$(document).on('drop dragover', function (e) {e.preventDefault();
});
}

function ClickRequestReview()
{
	ClickSaveConfig();
	var data = {method:'fRequestVenueReview',venueid:localStorage.getItem("activeProfile")};
	Post(data).then(function($data)
	{
		if ($data['result'] == "success") 
		{
			$("#mainModalHeader").empty().append("Review Pending");
			$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
			$("#mainModalCloseBtn").empty().append("OK").css({"display":"none"});
			$("#mainModalBody").empty().append("Your venue has been submitted for review, we will notify you when our review is complete and we will contact you with any questions we may have.");
			$("#mainModal").modal("show");
			 
			$("#mainModalAcceptBtn").off("click").click(function(event) { event.preventDefault(); $("#mainModal").modal("hide"); }).click(function(event) 
			{ 
				LoadPartial("/dashboard");
			});					
		} 
		else
		{
			$("#mainModalHeader").empty().append("Error");
			$("#mainModalAcceptBtn").empty().append("OK").css({"display":"none"});
			$("#mainModalCloseBtn").empty().append("OK").css({"display":"inline"});
			$("#mainModalBody").empty().append("Failed to submit this venue for review.");
			$("#mainModal").modal("show");
			 	
			$("#mainModalAcceptBtn").off("click").click(function(event) { event.preventDefault(); $("#mainModal").modal("hide"); });
		} 
		
	});
}

function EncodeVenueConfig()
{
	if ($("#venueBusinessName").length < 1)
		return "";
	
	var $data = {config:[],deposits:[],refunds:[],addons:[],resources:[],food:[],personnel:[],promos:[],questions:[]};
	
	var $config = {
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
		salesTax: parseFloat($("#venueSalesTax").val())/100,
		currency: $("#venueCurrency").val(),
		contract: "",
		subscription: []
	};
	
	if ($("#billToken").length > 0)
	{
		$config['subscription'] = {
			token: $("#billToken").val(),
			plan: $("#billPlan option:selected").attr("val"),
			email: $("#billEmail").val().trim()
		};
	}
	
	if ($("#cFiles a").length > 0)
	{
		var url = $("#cFiles a").attr("href");
		url = (url.indexOf("/content/") > 0 ? url.split("/content/")[1] : url);
		$config['contract'] = url;
	}
		
	$("#selVenueFeatures option:selected").each(function()
	{
		$config['features'].push($(this).attr("value"));
	});
	
	$config['functionality'] = {
		menus:$("#venueCreatorDetailsPane label.btn.active input[name='toggleFD']").val(),
		personnel:$("#venueCreatorDetailsPane label.btn.active input[name='toggleP']").val(),
		questions:$("#venueCreatorDetailsPane label.btn.active input[name='toggleQ']").val(),
		promos:$("#venueCreatorDetailsPane label.btn.active input[name='togglePC']").val(),
		publicFileUploads:$("#venueCreatorDetailsPane label.btn.active input[name='toggleFU']").val(),
		gratuity:$("#venueCreatorDetailsPane label.btn.active input[name='toggleGratuity']").val(),
		entireVenue:$("#venueCreatorDetailsPane label.btn.active input[name='toggleEntireVenue']").val()
	};
	
	$("#venueContacts option").each(function()
	{
		$config['contacts'].push({name:$(this).data("name"),
			title:$(this).data("title"),
			email:$(this).data("email"),
			phone:$(this).data("phone"),
			comments:$(this).data("comments")});
	});
	
	$("div.mgmtuser").each(function()
	{
		
		var r = "";
		if ($(this).find("input[name='chkRightsViewBooks']").prop("checked"))
			r += "1,";
		if ($(this).find("input[name='chkRightsManageBooks']").prop("checked"))
			r += "2,";
		if ($(this).find("input[name='chkRightsViewFinancials']").prop("checked"))
			r += "3,";
		if ($(this).find("input[name='chkRightsManageVenue']").prop("checked"))
			r += "4,";
			
		r = r.substring(0,r.lastIndexOf(','));
		
		var e = 0;
		if ($(this).find("input[name='chkRightsEmails']").prop("checked") == true)
			e = 1;
		
		$config['rights'].push({
			name:	$(this).find("input.txtRightsEmail").first().val(),
			roles:	r,
			receiveEmails:	e
		});
	});
	
	var $questions = [];
	$("#tblQuestions tbody tr").each(function()
	{
		var q = $.parseJSON($(this).attr("data-details"));
		$questions.push(q);
	});
	
	var $promos = [];
	$("#selectPromoCodes option").each(function()
	{
		var start = null;
		var stop = null;
		
		if ($(this).attr("data-start") > 0)
		{
			start = $(this).attr("data-start");
        }
        if ($(this).attr("data-stop") > 0)
		{
			stop = $(this).attr("data-stop");
        }
        var $promo = {
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
			start: start,
			stop: stop,
			hours: $(this).data("data-hours")
		};
		
		$promos.push($promo);
	});
	
	var $deposits = [];
	$("div.deposit").each(function()
	{
		var $policy = {
			name: $(this).data("name").trim(), 
			id: $(this).attr("data-id"), 
			threshold: $(this).attr("data-threshold"),
			perc: $(this).attr("data-perc"),
			amount: $(this).attr("data-amount"),
			full: $(this).attr("data-full")};
		$deposits.push($policy);					
	});
	
	var $refunds = [];
	$("div.refund").each(function()
	{
		var $policy = {
			name: $(this).data("name").trim(), 
			id: $(this).attr("data-id"), 
			policy: $(this).attr("data-policy")};
		$refunds.push($policy);					
	});
	
	var $addons = [];
	$("div.addon").each(function()
	{
		var pics = ($(this).data("pictures") ? $.parseJSON($(this).data("pictures")) : []);
		for (var i = 0; i < pics.length; i++)
		{
			if (pics[i]['url'].indexOf("/content/") > 0)
				pics[i]['url'] = pics[i]['url'].split('/content/')[1];
        }
        var $a = {
			name: $(this).data("name").trim(), 
			id: $(this).attr("data-id"), 
			description: $(this).data("description").trim(),
			type: $(this).attr("data-type"),
			price: $(this).attr("data-price"),
			pictures: pics,
			minimum: $(this).attr("data-minimum"),
			maximum: $(this).attr("data-maximum"),
			deliverable: $(this).attr("data-deliverable"),
			hours: $(this).data("data-hours"),
			deposit: $(this).data("deposit"),
			refund: $(this).data("refund")};
		$addons.push($a);					
	});
	
	var $personnel = [];
	$("div.creatorcategory.personnel").each(function()
	{
		var $a = {
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
		
		$personnel.push($a);					
	});
	var $space = $("div.space").first();
	var $resources = [];
	if ($space.length > 0)
		$resources.push(ResourceToArray($space));
	
	var $food = MenusToArray();
	
	$data.config = $config;
	$data.promos = $promos;
	$data.deposits = $deposits;
	$data.refunds = $refunds;
	$data.addons = $addons;
	$data.resources = $resources;
	$data.food = $food;
	$data.personnel = $personnel;
	$data.questions = $questions;
	
	return $data;
}

function ClickSaveConfig()
{
	var errors = ValidateVenueProfile();
	if (errors.length > 0)
	{
		$("#mainModalHeader").empty().append("Invalid venue profile information provided");
		$("#mainModalAcceptBtn").empty().append("OK").css({"display":"none"});
		$("#mainModalCloseBtn").empty().append("OK").css({"display":"inline"});
		$("#mainModalBody").empty().append("<div class='alert alert-danger'><ul></ul></div>");
		for (var i = 0; i < errors.length; i++)
		{
			$("#mainModalBody").find("ul").first().append("<li>" + errors[i] + "</li>");
        }
        $("#mainModal").modal("show");
		 
		$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
	}
	else
	{
		localStorage.removeItem("tempVenueConfig");
		var $data = EncodeVenueConfig();
		
		console.log("save data:",$data);
		$("#ajaxOverlay").show();
		var data = {method:'fSaveVenue',data:$data};
		Post(data).then(function($data)
		{console.log("save response:",$data);
			if ($data['result'] == "success") 
			{
				$("#mainModalHeader").empty().append("Venue Configuration Saved");
				$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
				$("#mainModalCloseBtn").empty().append("OK").css({"display":"none"});
				$("#mainModalBody").empty().append("You have successfully saved this venue configuration.  You can make changes to this venue profile at any time via your dashboard, we're taking you there now.");
				$("#mainModal").modal("show");
				 
				$("#mainModalAcceptBtn").off("click").click(function(event) { $("#mainModal").modal("hide"); }).click(function(event) 
				{ 
					if (window.location.href.indexOf("/admin") > 0)
						$("#adminConfigList").trigger('change');
					else if (window.location.href.indexOf("/dashboard") < 0)
						AuthPing().then(function()
						{
							localStorage.setItem("lastDashboardAccount",$data['id']);
							localStorage.setItem("lastDashboardPage","profile");
							window.location.replace("/dashboard");
						});
					else
					{
						localStorage.setItem("lastDashboardAccount",$data['id']);
						//localStorage.setItem("lastDashboardPage","profile");
						GetDashboardPane();
					}
				});					
			} 
			else
			{
				$("#mainModalHeader").empty().append("Failed to save venue configuration");
				$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
				$("#mainModalCloseBtn").empty().append("OK").css({"display":"none"});
				$("#mainModalBody").empty().append($data['result']);
				$("#mainModal").modal("show");				 	
				$("#mainModalAcceptBtn").off("click").click(function(event) { event.preventDefault(); $("#mainModal").modal("hide"); });
			} 
		});
	}

}

function LoadVenueConfig($id)
{		
	var data = {
		method:'fLoadVenueConfig',
		venueid: $id
	};
	Post(data).then(function($data)
	{console.log("fLoadVenueConfig response",$data);
	
		if ($data['result'] != "success")
			$("div.venuecreator").empty().append("<div class='alert alert-danger'>" + $data['result'] + "</div>");
		else
		{
			var $json = $data['data'];
			$("#venueid").val($json['config']['id']);
			$("#shorturl").val($json['config']['shorturl']);
			$("#overviewShortURL").attr("href","/venue/"+$json['config']['shorturl']);
			$("#overviewShortURL").text("/venue/"+$json['config']['shorturl']);
			$("#overviewBookURL").attr("href","/reserve/book-an-event-at-"+$json['config']['shorturl']);
			$("#overviewBookURL").text("/reserve/book-an-event-at-"+$json['config']['shorturl']);
			
			$("#venueName").val($json['config']['name']);
			$("#venueDescription").val($json['config']['description']);
			$("#venueBanner").val($json['config']['banner']);
			$("#venuePictures").val($json['config']['pictures']);
			$("#venueBusinessName").val($json['config']['business_name']);
			$("#venueBusinessEIN").val($json['config']['ein']);
			$("#venueFullAddress").val($json['config']['address']);
			$("#venueAddress").val($json['config']['address']);
			$("#venueCity").val($json['config']['city']);
			$("#venueState").val($json['config']['state']);
			$("#venueZip").val($json['config']['zip']);
			$("#venueCountry").val($json['config']['country']);
			$("#venueLatitude").val($json['config']['latitude']);
			$("#venueLongitude").val($json['config']['longitude']);
			$("#venuePhone").val($json['config']['phone']);
			$("#venueWebsite").val($json['config']['website']);
			$("#venueFacebook").val($json['config']['facebook']);
			$("#venueTwitter").val($json['config']['twitter']);
			$("#venueType").find("option[value='" + SanitizeAttr($json['config']['type']) +"']").prop("selected",true);
			$("#venuePromos").val($json['config']['promos']);
			$("#venueTimezone").val($json['config']['timezone']);
			$("#venueSalesTax").val($json['config']['salesTax'] * 100);
			$("#venueCurrency").find("option[value='" + SanitizeAttr($json['config']['currency']) +"']").prop("selected",true);
			$("#venueVisibility").find("option[value='" + SanitizeAttr($json['config']['visibility']) +"']").prop("selected",true);
			$("#venueVisibility")[0].sumo.reload();
			$("#venueType")[0].sumo.reload();
			$("#venueCurrency")[0].sumo.reload();

			if ($json['config']['status'] == "active")
				$("a[href='#venueCreatorApprovePane']").hide();
			if ($json['config']['status'] == "pending_review")
				$("#venueCreatorApprovePane").empty().append("This venue has already been submitted for approval and we are still reviewing it.");
			
			if ($json['config']['functionality']['menus'] != 1)
			{
				$("#venueCreatorSteps").find(":contains('Food & Drink')").hide();
				$("input[name='toggleFD'][value=0]").trigger("click");
			}
			else $("input[name='toggleFD'][value=1]").trigger("click");
			
			if ($json['config']['functionality']['personnel'] != 1)
			{
				$("#venueCreatorSteps").find(":contains('Personnel')").hide();
				$("input[name='toggleP'][value=0]").trigger("click");
			}
			else $("input[name='toggleP'][value=1]").trigger("click");
			
			if ($json['config']['functionality']['questions'] != 1)
			{
				$("#venueCreatorSteps").find(":contains('Questionnaire')").hide();
				$("input[name='toggleQ'][value=0]").trigger("click");
			}
			else $("input[name='toggleQ'][value=1]").trigger("click");
			
			if ($json['config']['functionality']['promos'] != 1)
			{
				$("#venueCreatorSteps").find(":contains('Promo Codes')").hide();
				$("input[name='togglePC'][value=0]").trigger("click");
			}
			else $("input[name='togglePC'][value=1]").trigger("click");
			
			if ($json['config']['functionality']['publicFileUploads'] != 1)
			{
				$("input[name='toggleFU'][value=0]").trigger("click");
			}
			else $("input[name='toggleFU'][value=1]").trigger("click");
			
			if ($json['config']['functionality']['gratuity'] != 1)
			{
				$("input[name='toggleGratuity'][value=0]").trigger("click");
			}
			else $("input[name='toggleGratuity'][value=1]").trigger("click");
			
			if ($json['config']['functionality']['entireVenue'] != 1)
			{
				$("input[name='toggleEntireVenue'][value=0]").trigger("click");
			}
			else $("input[name='toggleEntireVenue'][value=1]").trigger("click");
			
			if ($json['config']['contract'].length > 0)
			{	
				$("#cFiles").empty();
				$("#cFiles").append("<div class='input-group'><span class='input-group-addon' style='background:#fefefe'><a href='" + SanitizeAttr($json['config']['contract']) + "'>" + $json['config']['contract'] + "</a></span><button class='btn btn-default' name='delContract'><i class='glyphicon glyphicon-trash'></i></button></div>");
				$("form.contractupload span.fileinput-button").hide();
				$("#cFiles [name='delContract']").off("click").click(function(event)
				{
					event.preventDefault();
					$("#cFiles").empty();
					RebindContractUpload();
				});
			}
			
			var length = $json['config']['features'].length;
			for (var i = 0; i < length; i++)
			{
				$("#selVenueFeatures").find("option[value='" + SanitizeAttr($json['config']['features'][i]) +"']").prop("selected",true);
			}
			$("#selVenueFeatures")[0].sumo.reload();
			
			var length = $json['config']['contacts'].length;
			for (var i = 0; i < length; i++)
			{
				InsertContact($json['config']['contacts'][i]['name'],$json['config']['contacts'][i]);
			}
			
			var length = $json['config']['rights'].length;
			for (var i = 0; i < length; i++)
			{
				InsertRights($json['config']['rights'][i]['name'],$json['config']['rights'][i]);
			}
	
			$("div.deposit_table").empty();
			var length = $json['deposits'].length;
			for (var i = 0; i < length; i++)
			{
				InsertDeposit($json['deposits'][i]['name'], $json['deposits'][i]);
			}
			
			length = $json['promos'].length;
			for (var i = 0; i < length; i++)
			{	
				InsertPromo($json['promos'][i]['name'], $json['promos'][i]);
			}
			
			$("div.refund_table").empty();
			length = $json['refunds'].length;
			for (var i = 0; i < length; i++)
			{
				InsertRefund($json['refunds'][i]['name'], $json['refunds'][i]);
			}
	
			length = $json['addons'].length;
			for (var i = 0; i < length; i++)
			{
				InsertAddon($json['addons'][i]['name'], $json['addons'][i]);
			}
			
			length = $json['resources'].length;
			for (var i = 0; i < length; i++)
			{
				var $resource = CreateResource($json['resources'][i]['name'], $json['resources'][i]['type'], $json['resources'][i]);
				$(".resource_table").append($resource);
			}
	
			length = $json['relationships'].length;
			for (var i = 0; i < length; i++)
			{
				SetResourceRelationship($json['relationships'][i]['child'],$json['relationships'][i]['parent'],$json['relationships'][i]['relation']);
			}
			
			$(".resource_table").find(".resource").each(function(){$(this).parent("div").find("div:first").after($(this))});
			
			length = $json['food']['menus'].length;
			for (var i = 0; i < length; i++)
			{
				var $food = CreateMenu($json['food']['menus'][i]['name'], $json['food']['menus'][i]);
				$(".menu_table").append($food);
			}
	
			length = $json['food']['items'].length;
			for (var i = 0; i < length; i++)
			{
				var $food = CreateMenuItem($json['food']['items'][i]['name'], $json['food']['items'][i]);
				$(".menu_table [data-id='" + SanitizeAttr($json['food']['items'][i]['menuid']) +"']").append($food);
			}
			
			length = $json['personnel'].length;
			for (var i = 0; i < length; i++)
			{
				var $personnel = CreatePersonnel($json['personnel'][i]['name'], $json['personnel'][i]);
				$(".personnel_table").append($personnel);
			}
			
			length = $json['questions'].length;
			for (var i = 0; i < length; i++)
			{
				InsertQuestion($json['questions'][i]);
			}
			
			length = $json['config']['pictures'].length;
			for (var i = 0; i < length; i++)
			{
				InsertPicture($("#venuePictures"),$json['config']['pictures'][i]['url'], $json['config']['pictures'][i]['caption']);
			}
			
			ReBindCreatorControls();
			
			if ($json['resources'].length > 0)
				$("#addvenuespace").remove();
			
			localStorage.setItem("tempVenueConfig",JSON.stringify(EncodeVenueConfig()));
        }
        ReBindCreatorControls();
	});
}

function ResourceToArray($div)
{
	if ($($div).length < 1)
		return;
		
	var $cnt = $($div).children("div.space,div.resource").length;
	var $children = [];
	
	if ($cnt > 0)
		$($div).children("div.space,div.resource").each(function()
		{
			$children.push(ResourceToArray($(this)));
		});
	
	var pics = ($($div).data("pictures") ? $.parseJSON($($div).data("pictures")) : []);
	for (var i = 0; i < pics.length; i++)
	{
		if (pics[i]['url'].indexOf("/content/") > 0)
			pics[i]['url'] = pics[i]['url'].split('/content/')[1];
    }
    var $resource = {
			name: $($div).data("name"), 
			id: $($div).attr("data-id"), 
			description: $($div).data("description"),
			type: $($div).attr("data-type"),
			pictures: pics,
			capacity: $($div).attr("data-capacity"),
			seats: $($div).attr("data-seats"),
			cleanupcost: $($div).attr("data-cleanupcost"),
			cleanup: $($div).attr("data-cleanup"),
			duration: $($div).attr("data-duration"),
			increment: $($div).attr("data-increment"),
			lead: $($div).attr("data-lead"),
			autoapprove: $($div).attr("data-autoapprove"),
			over21: $($div).attr("data-over21"),
			linked: $($div).attr("data-linked"),
			rate: $($div).attr("data-rate"),
			deposit: $($div).data("deposit"),
			refund: $($div).data("refund"),
			timeslots: $($div).attr("data-timeslots"),
			hours: $($div).data("data-hours"),
			rates: $($div).data("data-rates"),
			slots: $($div).data("data-slots"),
			addons: ($($div).data("addons") ? $.parseJSON($($div).data("addons")) : null),
			children: $children};
	return $resource;
}

function MenusToArray()
{
	var $arr = {menus:[],items:[]};
	var $menus = [];
	var $items = [];
	
	$("div.menu").each(function()
	{
		var $menu = {
			name: $(this).data("name"),
			id: $(this).attr("data-id"),
			description: $(this).data("description"),
			deposit: $(this).data("deposit"),
			refund: $(this).data("refund"),
			hours: $(this).data("data-hours")
		};
		$menus.push($menu);
	});
	
	$("div.menuitem").each(function()
	{
		var pics = ($(this).data("pictures") ? $.parseJSON($(this).data("pictures")) : [{url:"",caption:""}]);
		
		for (var i = 0; i < pics.length; i++)
		{
			if (pics[i]['url'].indexOf("/content/") > 0)
				pics[i]['url'] = pics[i]['url'].split('/content/')[1];
        }
        var $item = {
			name: $(this).data("name"),
			id: $(this).attr("data-id"),
			type: $(this).attr("data-type"),
			description: $(this).data("description"),
			pictures: pics,
			price: $(this).attr("data-price"),
			min: $(this).attr("data-min"),
			max: $(this).attr("data-max"),
			menu: $(this).parents("div.menu").first().data("name")
		};
		$items.push($item);
	});
	
	$arr.menus = $menus;
	$arr.items = $items;
	return $arr;
}

function PicturesToArray($div)
{
	var arr = [];
	var cnt = 0;
	
	$div.find("div.pic").each(function()
	{
		var data = {
			placement: cnt,
			url: ($(this).find("img").first().attr("src").indexOf("/content/") > 0 ? $(this).find("img").first().attr("src").split("/content/")[1] : $(this).find("img").first().attr("src")),
			caption: $(this).find("[name=caption]").first().val()
			};
		
		arr.push(data);
		cnt++;
	});
	
	//if (arr.length == 0)
	//	arr.push({placement:0,url:null,caption:null});
		
	return arr;
}

function ClickDelete($button)
{
	var $ok = true;
	var $div = $button.closest("div.creatorcategory");
	
	if ($div.attr("data-id"))
	{
		var $type = "";
		if ($div.hasClass("space") || $div.hasClass("resource")) $type = "resource";
		if ($div.hasClass("deposit")) $type = "deposit";
		if ($div.hasClass("refund")) $type = "refund";
		if ($div.hasClass("addon")) $type = "addon";
		
		if ($type == "resource")
		{
			var $ids = [];
			$ids.push($($div).attr("data-id"));
			$($div).find("div.space,div.resource").each(function(){$ids.push($(this).attr("data-id"));});
			var data = {method:'fCheckIfDeleteOK', ids:$ids};
			Post(data).then(function($data)
			{
				if ($data['result'] != "success") 
					$ok = false;
			});
		}
    }
    if ($ok)
	{
		$("#mainModalHeader").empty().append("Delete?");
		$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
		$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
		$("#mainModalBody").empty().append("Are you sure you want to delete \"" + $div.data("name") + "\"?  Deleting this will not affect bookings that have already been made.");
		$("#mainModal").modal("show");
		 
		$("#mainModalAcceptBtn").off("click").click(function(event) { $("#mainModal").modal("hide"); }).click(function(event) 
		{
			event.preventDefault();
			$div.remove();
			$("#mainModalBody").empty();
			ReBindCreatorControls();
		});
		$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
	}
	else
	{
		$("#mainModalHeader").empty().append("Failed to delete");
		$("#mainModalAcceptBtn").empty().append("OK").css({"display":"none"});
		$("#mainModalCloseBtn").empty().append("OK").css({"display":"inline"});
		$("#mainModalBody").empty().append("You cannot delete \"" + $div.data("name") + "\" because there are active reservations that depend it.");
		$("#mainModal").modal("show");
		 
		$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
	}
}

function ClickClone($button)
{
	var $div = $button.closest("div.creatorcategory");
	
	$("#mainModalHeader").empty().append("Clone how many times?");
	$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
	$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
	$("#mainModalBody").empty().append("<form class='form-horizontal' action='#'><div class='form-group'><label class='col-xs-3 control-label'>How many copies of \"" + $div.data("name") + 
		"\"?</label><div class='col-xs-9'><input type='text' class='form-control' id='textCloneCopies' placeholder='#'></div></div></form>");
	$("#mainModal").modal("show");
	 
	$("#mainModalAcceptBtn").off("click").click(function(event) { $("#mainModal").modal("hide"); }).click(function(event) 
	{
		event.preventDefault();
		var count = $("#textCloneCopies").val();
		if (!isNaN(count))
		{
			for (var i = 0; i < count; i++)
			{
				var $clone = $div.clone(true,true);
				$clone.attr("data-id","");
				$clone.data("name",$clone.data("name") + " (copy " + (i+1) + ")");
				$clone.find("div[name='resourceName']").text($clone.data("name"));
				$div.before($clone);
			}
		}
		$("#mainModalBody").empty();
		ReBindCreatorControls();
	});
	$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
}

function CreateResource($name, $type, $info)
{
	var $div = "<div class='creatorcategory " + (($type == "1" || $type == "2") ? "space" : "resource") + "'>" + (($type == "1" || $type == "2") ? "" : "<small>") + "<div class='row'>\
					<div class='col-xs-6'><div name='resourceName' style='display:inline;padding-right:10px'>" + $name + "</div>\
						<small class='editBar'>\
							<a name='buttonEdit' href='#'>Edit</a> | \
							<a name='buttonClone' href='#'>Clone</a> | \
							<a name='buttonDelete' href='#'>Delete</a>\
						</small>\
					</div><div class='col-xs-2' name='resourceRate'></div>\
					<div class='col-xs-2 display-mob'></div>\
					<div class='col-xs-2 no-display-mob' name='resourceMin'></div>\
				" + (($type == "1" || $type == "2") ? "\
				<div class='col-xs-2'><button name='buttonAddResource' class='btn btn-xs btn-primary pull-right' style='margin:auto 8px auto 10px'>+<div class='no-display-mob' style='display:inline'> Resource</div></button></div></div>\
				<div class='clearfix'></div>\
				<div class='addnewspace'>\
					<button name='buttonAddResource' class='btn btn-xs btn-default'>Add Space</button>\
				</div>" : "</small>") + "</div>";
		
	$div = EncodeResourceData($($div), $info);
	return $div;

}

function CreateMenu($name, $info)
{
	var $div = "<div class='creatorcategory menu formpan'><p style='float:left;margin:auto'>" + $name + "\
			</p><small class='editBar'>\
				<a name='buttonEdit' href='#' style='margin-left:10px'>Edit</a> | \
				<a name='buttonDelete' href='#'>Delete</a>\
			<button name='buttonAddMenuItem' class='btn btn-xs btn-primary pull-right' style='margin:auto auto auto 10px'>+ Item</button>\</small>\
			<div class='clearfix'></div>\
			</div>";
		
	$div = EncodeMenuData($($div), $info);
	return $div;

}

function CreateMenuItem($name, $info)
{
	var $div = "<div class='creatorcategory menuitem'><small><div class='row'>\
				<div class='col-xs-4'><div name='menuItemName' style='display:inline;padding-right:10px'>" + $name + "</div>\
					<small class='editBar'>\
						<a name='buttonEdit' href='#'>Edit</a> | \
						<a name='buttonDelete' href='#'>Delete</a>\
					</small>\
				</div><div class='col-xs-2' name='menuItemPrice'></div>\
				<div class='col-xs-2' name='menuItemMin'></div><div class='col-xs-2' name='menuItemMax'></div></div>\
			</small></div>";
		
	$div = EncodeMenuItemData($($div), $info);				
	return $div;

}

function CreatePersonnel($name, $info)
{
	var $div = "<div class='creatorcategory personnel'><div class='row'>\
				<div class='col-xs-4'><div name='personnelName' style='display:inline;padding-right:10px'>" + $name + "</div>\
					<small class='editBar'>\
						<a name='buttonEdit' href='#'>Edit</a> | \
						<a name='buttonDelete' href='#'>Delete</a>\
					</small>\
				</div><div class='col-xs-2' name='personnelPrice'></div>\
				<div class='col-xs-2' name='personnelMin'></div><div class='col-xs-2' name='personnelMax'></div></div>\
			</div>";
		
	$div = EncodePersonnelData($($div), $info);				
	return $div;

}

function InsertDeposit($name, $info)
{
	var $div = "<div class='creatorcategory deposit'><div class='row'>\
					<div class='col-xs-6'><div name='depositName' style='display:inline;padding-right:10px'>" + $name + "</div>\
						<small class='editBar'>\
							<a name='buttonEdit' href='#'>Edit</a> | \
							<a name='buttonClone' href='#'>Clone</a> | \
							<a name='buttonDelete' href='#'>Delete</a>\
						</small>\
					</div><div class='col-xs-2' name='depositPerc'></div>\
					<div class='col-xs-2' name='depositDue'></div></div>\
				</div>";
		
	$div = EncodeDepositData($($div), $info);
	$(".deposit_table").append($($div));
	ReBindCreatorControls();
}

function InsertRefund($name, $info)
{
	var $div = "<div class='creatorcategory refund'><div class='row'>\
					<div class='col-xs-8'><div name='refundName' style='display:inline;padding-right:10px'>" + $name + "</div>\
						<small class='editBar'>\
							<a name='buttonEdit' href='#'>Edit</a> | \
							<a name='buttonClone' href='#'>Clone</a> | \
							<a name='buttonDelete' href='#'>Delete</a>\
						</small>\
					</div>\</div>\
				</div>";
		
	$div = EncodeRefundData($($div), $info);
	$(".refund_table").append($($div));
	ReBindCreatorControls();
}

function InsertAddon($name, $info)
{
	var $div = "<div class='creatorcategory addon'><div class='row'>\
					<div class='col-xs-4'><div name='addOnName' style='display:inline;padding-right:10px'>" + $name + "</div>\
						<small class='editBar'>\
							<a name='buttonEdit' href='#'>Edit</a> | \
							<a name='buttonClone' href='#'>Clone</a> | \
							<a name='buttonDelete' href='#'>Delete</a>\
						</small>\
					</div><div class='col-xs-2' name='addOnPrice'></div>\
					<div class='col-xs-2' name='addOnMin'></div>\
					<div class='col-xs-2' name='addOnMax'></div></div>\
				</div>";
		
	$div = EncodeAddonData($($div), $info);
	$(".addon_table").append($($div));
	ReBindCreatorControls();
}

function InsertContact($name, $info)
{
	var $opt = $("<option></option>",{text:$name});
	
	$opt = EncodeContactData($opt, $info);					
	$("#venueContacts").append($opt);
}

function InsertRights($name, $info)
{
	var $div = $("<div class='mgmtuser'>\
							<b>Email:</b><input type='text' class='txtRightsEmail' placeholder='User&#39;s email address' value=''/>\
							<button class='btn btn-xs pull-right btnMgmtRightsDelete'><i class='glyphicon glyphicon-trash'></i></button><br>\
							<div class='clearfix'></div>\
							<div class='row'>\
								<div class='col-xs-6'>\
									<input type='checkbox' name='chkRightsViewBooks'/> View Books<br>\
									<input type='checkbox' name='chkRightsManageBooks'/> Manage Books\
									<input type='checkbox' name='chkRightsEmails'/> Receive Emails\
								</div>\
								<div class='col-xs-6'>\
									<input type='checkbox' name='chkRightsViewFinancials'/> View Financials<br>\
									<input type='checkbox' name='chkRightsManageVenue'/> Manage Venue \
								</div>\
							</div>\
						</div>");
	
	$div.find("input.txtRightsEmail").val($info['name']);
	for (var i=0; i<$info['roles'].length; i++)
	{
		switch ($info['roles'][i])
		{
			case 1:
				$div.find("input[name='chkRightsViewBooks']").prop("checked",true);
				break;
			case 2:
				$div.find("input[name='chkRightsManageBooks']").prop("checked",true);
				break;
			case 3:
				$div.find("input[name='chkRightsViewFinancials']").prop("checked",true);
				break;
			case 4:
				$div.find("input[name='chkRightsManageVenue']").prop("checked",true);
				break;
        }
    }
	
	if ($info['receiveEmails'] == 1)
		$div.find("input[name='chkRightsEmails']").prop("checked",true);
	
    $("#rowMgmtUsers").prepend($div);
}

function InsertPicture($dest_div, $url, $caption)
{
	if (!$url)
	{
		console.log("Error uploading picture!",$url);
		return;
	}
	//alert( SanitizeAttr(($url.indexOf("http") < 0 ? "" : "") ) + $url);
	//<img src='" + SanitizeAttr(($url.indexOf("http") < 0 ? "/assets/content/" : "") ) + $url + "'>
	var $div = "<div class='pic'>\
					<img src='"+ $url + "'>\
					<textarea name='caption' class='form-control pic-caption' placeholder='Caption...'>" + ($caption ? $caption : "") + "</textarea>\
					<div class='input-group' style='margin-left:25%'>\
						<button name='buttonPictureUp' class='btn btn-default'><i class='glyphicon glyphicon-chevron-up'></i></button>\
						<button name='buttonPictureDel' class='btn btn-default'><i class='glyphicon glyphicon-trash'></i></button>\
						<button name='buttonPictureDown' class='btn btn-default'><i class='glyphicon glyphicon-chevron-down'></i></button>\
					</div>\
				</div>";
		
	$dest_div.append($($div));
	ReBindCreatorControls();
}

function SetResourceRelationship($child, $parent, $relation)
{
	if ($relation.indexOf("_linked") > 0)
	{
		//alert(SanitizeAttr($child));
		$(".resource_table div[data-id='" + SanitizeAttr($child) +"']").attr("data-linked","true");
	}
	
	if ($relation.indexOf("inside_") >= 0)
	{
		//alert(SanitizeAttr($child));
		$(".resource_table div[data-id='" + SanitizeAttr($child) +"']").appendTo($(".resource_table div[data-id='" + SanitizeAttr($parent) +"']"));
	}
	
	$(".addnewspace").each(function(){$(this).appendTo($(this).parent("div.creatorcategory"))});
}

function ValidatePromoCode()
{
	var error = [];
	
	if ($("#txtPromoCode").val().length < 3 || $("#txtPromoCode").val().length > 20)
		error.push("The promo code must be between 3 and 20 characters");
	/*
	// value attribute is no longer used, now it's .data("name")
	if ($("#selectPromoCodes option[value='" + SanitizeAttr($("#txtPromoCode").val()) +"']:not(:selected)").length > 0)
		error.push("This promo code already exists");
	*/
	
	if (/^[0-9.$%]*$/.test($("#txtPromoAmount").val()) == false)
		error.push("The promo amount can only contain numbers and decimals");
	//if (/^[0-9.$]*$/.test($("#txtPromoThreshold").val()) == false)
	//	error.push("The minimum invoice requirement field can only contain numbers and decimals");
	if (/^[0-9]*$/.test($("#txtPromoPerUser").val()) == false)
		error.push("The per-user uses field can only contain numbers");
	if (/^[0-9.]*$/.test($("#txtPromoQuantity").val()) == false)
		error.push("The number of invoices field can only contain numbers and decimals");
	if ($("#selPromoResources option:checked").length < 1)
		error.push("You must choose at least one resource for this promo code to apply to");
	if ($("#fgHours tbody tr").length < 1)
		error.push("You must specify at least one timeslot for which this promo code is available");
	if ($("#txtPromoStart").val().length < 6)
		error.push("You must specify a start date after which this promo code becomes available");
	if ($("#txtPromoStop").val().length < 6)
		error.push("You must specify a stop date on which this promo code becomes unavailable");
	return error;
}

function EncodePromoData($opt, $info)
{	
	if (!$info)
	{
		$opt.attr("data-id", $("#txtPromoId").val());
		$opt.data("name", $("#txtPromoCode").val());
		$opt.text($("#txtPromoCode").val());
		$opt.data("description", $("#txtPromoDescription").val());
		$opt.attr("data-discounttype", $("#selPromoType option:selected").attr("value"));
		$opt.attr("data-discountamount", $("#txtPromoAmount").val().replace(/[^0-9.]/g,""));
		$opt.attr("data-discountthreshold", $("#txtPromoThreshold").val().replace(/[^0-9.]/g,""));
		$opt.attr("data-start", FormatDate($("#txtPromoStart").val(),"X"));
		$opt.attr("data-stop", FormatDate($("#txtPromoStop").val(),"X"));
		$opt.attr("data-peruser", ($("#chkPromoPerUserUnlim").prop("checked") == true ? "unlim":$("#txtPromoPerUser").val()));
		$opt.attr("data-quantity", ($("#chkPromoQuantityUnlim").prop("checked") == true ? "unlim":$("#txtPromoQuantity").val()));
		$opt.attr("data-applic",parseInt($("[name='radioGrp1']:checked").val()));
		$opt.attr("data-expires", parseInt($("#txtPromoExpVal").val()) || 0);
		if ($("#radioPromoEntireInvoice").prop("checked") == true)
			$opt.attr("data-entire",1);
		if ($("#radioPromoResourceOnly").prop("checked") == true)
			$opt.attr("data-entire",0);
		var res = [];
		$("#selPromoResources option:selected").each(function(){ res.push(parseInt($(this).attr("data-id"))); });
		$opt.attr("data-resources", JSON.stringify(res));
		$opt.attr("data-combinable", ($("#chkPromoCombinable").prop("checked") == true ? 1 : 0));
		$opt.attr("data-auto", ($("#chkPromoAuto").prop("checked") == true ? 1 : 0));
		$opt.attr("data-status", $("#selPromoStatus option:selected").attr("value"));
		$opt.data("data-hours", $("#fgHours .timeslot-widget").tsWidget("save"));
		
		if ($("[name='radioExpType'][value='before']:checked").length > 0)
			$opt.attr("data-expires",parseInt($opt.attr("data-expires")) * -1);
	}
	else
	{
		$opt.attr("data-id", $info['id']);
		$opt.data("name", $info['name']);
		$opt.text($info['name']);
		$opt.data("description", $info['description']);
		$opt.attr("data-discounttype", $info['discount_type']);
		$opt.attr("data-discountamount", $info['discount_amount']);
		$opt.attr("data-discountthreshold", $info['discount_threshold']);
		$opt.attr("data-expires", $info['expires']);
		$opt.data("data-hours", $info['hours']);
		$opt.attr("data-start", $info['start']);
		$opt.attr("data-stop", $info['stop']);
		$opt.attr("data-peruser", $info['peruser']);
		$opt.attr("data-quantity", $info['quantity']);
		$opt.attr("data-applic", $info['applic']);
		$opt.attr("data-entire", $info['entireinvoice']);
		$opt.attr("data-resources", JSON.stringify($info['resources']));
		$opt.attr("data-combinable", $info['combinable']);
		$opt.attr("data-auto", $info['auto']);
		$opt.attr("data-status", $info['status']);
	}
	
	if ($opt.attr("data-status") != "active")
		$opt.css({"color":"grey"});
	
	return $opt;
}

function PopulatePromoData($opt)
{
	$("#txtPromoId").val($opt.attr("data-id"));
	$("#txtPromoCode").val($opt.data("name"));
	$("#selPromoType option[value=" + $opt.attr("data-discounttype") + "]").prop("selected",true);
	$("#txtPromoDescription").val($opt.data("description"));
	$("#txtPromoAmount").val($opt.attr("data-discountamount"));
	$("#txtPromoAmount").trigger("blur");
	$("#txtPromoThreshold").val($opt.attr("data-discountthreshold"));
	$("#txtPromoThreshold").trigger("blur");
	$("#txtPromoStart").val(FormatDate($opt.attr("data-start"),"MMMM D, YYYY"));
	$("#txtPromoStop").val(FormatDate($opt.attr("data-stop"),"MMMM D, YYYY"));
	$("#fgHours .timeslot-widget").tsWidget("restore",$opt.data("data-hours"));
	
	if (parseInt($opt.attr("data-expires")) > 0)
	{
		$("[name='radioExpType'][value='after']").trigger('click');
		$("#txtPromoExpVal").val($opt.attr("data-expires"));
	}
	else if (parseInt($opt.attr("data-expires")) < 0)
	{
		$("[name='radioExpType'][value='before']").trigger('click');
		$("#txtPromoExpVal").val(Math.abs($opt.attr("data-expires")));
	}
	else $("[name='radioExpType'][value='never']").trigger('click');
	
    if ($opt.attr("data-peruser") == "unlim")
		$("#chkPromoPerUserUnlim").prop("checked",true);
	else 
	{
		$("#chkPromoPerUserUnlim").prop("checked",false);
		$("#txtPromoPerUser").val($opt.attr("data-peruser"));
    }
    if ($opt.attr("data-quantity") == "unlim")
		$("#chkPromoQuantityUnlim").prop("checked",true);
	else 
	{
		$("#chkPromoQuantityUnlim").prop("checked",false);
		$("#txtPromoQuantity").val($opt.attr("data-quantity"));
    }
	
    switch ($opt.attr("data-applic"))
	{
		case "3":
			$("#radioPromoAllSelected").prop("checked",true);
			break;
		case "2":
			$("#radioPromoAnySelectedI").prop("checked",true);
			break;
		default:
			$("#radioPromoAnySelected").prop("checked",true);
			break;
    }
	
	switch ($opt.attr("data-entire"))
	{
		case "1":
			$("#radioPromoEntireInvoice").prop("checked",true);
			break;
		default:
			$("#radioPromoResourceOnly").prop("checked",true);
			break;
    }
	
    if ($opt.attr("data-combinable") == 1)
		$("#chkPromoCombinable").prop("checked",true);
	else $("#chkPromoCombinable").prop("checked",false);
	
	if ($opt.attr("data-auto") == 1)
		$("#chkPromoAuto").prop("checked",true);
	else $("#chkPromoAuto").prop("checked",false);
	
	$("#selPromoStatus option[value='" + SanitizeAttr($opt.attr("data-status")) + "']").prop("selected","selected");
	
	var res = $.parseJSON($opt.attr("data-resources"));
	for (var i = 0; i < res.length; i++)
	{
		$("#selPromoResources option[data-id=" + res[i] + "]").prop("selected","selected");
    }
    if (res.length == 0)
		$("#selPromoResources option[value=Any]").prop("selected","selected");
	
	$("#divPromoCodeDetails select").each(function(){
		if (typeof $(this)[0].sumo !== "undefined")
			$(this)[0].sumo.reload();
		else $(this).SumoSelect();
	});
}

function PopulatePromoResourceList()
{
	$("div.creatorcategory.resource,div.creatorcategory.space").each(function()
	{
		if ($(this).attr("data-id"))
			$("#selPromoResources").append("<option data-id='" + SanitizeAttr($(this).attr("data-id")) + "'>" + $(this).data("name") + "</option>");
	});
}

function PopulateQuestionResourceList()
{
	$("#selQuestionResources option").remove();
	$("#selQuestionResources").append("<option data-id='0'>Any</option>");
	$("div.creatorcategory.resource,div.creatorcategory.space").each(function()
	{
		if ($(this).attr("data-id"))
			$("#selQuestionResources").append("<option data-id='" + SanitizeAttr($(this).attr("data-id")) + "'>" + $(this).data("name") + "</option>");
	});
	
	$("#selQuestionAddons option").remove();
	$("#selQuestionAddons").append("<option data-id='0'>Any</option>");
	$("div.creatorcategory.addon").each(function()
	{
		if ($(this).attr("data-id"))
			$("#selQuestionAddons").append("<option data-id='" + SanitizeAttr($(this).attr("data-id")) + "'>" + $(this).data("name") + "</option>");
	});
	
	$("#selQuestionMenus option").remove();
	$("#selQuestionMenus").append("<option data-id='0'>Any</option>");
	$("div.creatorcategory.menu").each(function()
	{
		if ($(this).attr("data-id"))
			$("#selQuestionMenus").append("<option data-id='" + SanitizeAttr($(this).attr("data-id")) + "'>" + $(this).data("name") + "</option>");
	});
	
	$("#selQuestionPersonnel option").remove();
	$("#selQuestionPersonnel").append("<option data-id='0'>Any</option>");
	$("div.creatorcategory.personnel").each(function()
	{
		if ($(this).attr("data-id"))
			$("#selQuestionPersonnel").append("<option data-id='" + SanitizeAttr($(this).attr("data-id")) + "'>" + $(this).data("name") + "</option>");
	});
	
	$("#divNewQuestionForm select").each(function(){
		if (typeof $(this)[0].sumo !== "undefined")
			$(this)[0].sumo.reload();
		else $(this).SumoSelect();
	});
}

function InsertPromo($name, $promo)
{
	var opt = $("<option />");
	opt.data("name",$name);
	opt.text($name);
	EncodePromoData(opt, $promo);
	$("#selectPromoCodes").prepend(opt);
	$("#selectPromoCodes").attr("size",$("#selectPromoCodes option").length+1);
}

function ValidateVenueProfile()
{
	var error = [];
			
	if ($("#venueName").val().length < 3 || $("#venueName").val().length > 128)
		error.push("Venue Name must be between 3 and 128 characters long");
	if ($("#venueDescription").val().length < 3)
		error.push("Venue Description must be provided");
	if ($("#venueBusinessName").val().length < 3 || $("#venueBusinessName").val().length > 128)
		error.push("Venue Business Name must be at between 3 and 128 characters long");
	if ($("#venueAddress").val().length < 3 || $("#venueAddress").val().length > 128)
		error.push("Please specify a valid venue address");
	if ($("#venueCity").val().length < 3 || $("#venueCity").val().length > 128)
		error.push("Please specify a valid city");
	if	($("#venueCountry").val().length < 2 || $("#venueCountry").val().length > 32)
		error.push("Please specify a valid country");
	if ($("#venuePhone").val().length < 10 || $("#venueName").val().length > 50)
		error.push("Venue Phone number must be at least 10 characters long");
	if ($("#venueWebsite").val().length > 0 && $("#venueWebsite").val().indexOf("https://") < 0 && $("#venueWebsite").val().indexOf("http://") < 0)
		error.push("Venue Website must be the full URL (including http://)");
	if ($("#venueWebsite").val().length > 0 && $("#venueWebsite").val().indexOf("https://") < 0 && $("#venueWebsite").val().indexOf("http://") < 0)
		error.push("Venue Website must be the full URL (including http://)");
	if ($("#venueFacebook").val().length > 0 && $("#venueFacebook").val().indexOf("https://") < 0 && $("#venueWebsite").val().indexOf("http://") < 0)
		error.push("Venue Facebook page must be the full URL (including http://)");
	if ($("#venueTwitter").val().length > 0 && $("#venueTwitter").val().indexOf("https://") < 0 && $("#venueWebsite").val().indexOf("http://") < 0)
		error.push("Venue Twitter page must be the full URL (including http://)");
	if ($("#venueTimezone").val().length < 3)
	{
		error.push("Could not determine the timezone for the specified address");
		$("#venueTimezone").parents(".form-group").first().show();
	}
	if (/^[0-9.]*$/.test($("#venueSalesTax").val()) == false)
		error.push("Sales Tax Rate field can only contain whole numbers, like '10' for 10%");
	if ($("#venuePictures").find("div.pic").length < 3)
		error.push("You must upload at least three pictures for your venue profile: a logo and a profile header image, and at least one more image of your venue");
	
	/*
	if ($("#billToken").length > 0 || window.location.href.indexOf("/create-venue") > 0)
	{
		if ($("#billToken").val().length < 10)
			error.push("Your credit card could not be processed, refresh the page and try again. If you need help send an email to support@invitebig.com.");
		if ($("#billPlan option:selected").length < 1)
			error.push("You did not choose a subscription plan, refresh the page and try again. If you need help send an email to support@invitebig.com.");
	}
	*/
	
	return error;
}

function ValidateResource()
{
	var error = [];
	
	$("#newResourceRate").val($("#newResourceRate").val().replace(/[$,]/g,""));
	$("#newResourceCleanupCost").val($("#newResourceCleanupCost").val().replace(/[$,]/g,""));
	
	if ($("#newResourceName").val().length < 3 || $("#newResourceName").val().length > 128)
		error.push("Resource Name must be between 3 and 128 characters long");
	$("div.resource,div.space").each(function(){ if ($(this).data("name") == $("#newResourceName").val() && ($(this).attr("data-id") && $(this).attr("data-id") != $("#newResourceID").val())) {
		error.push("This Resource Name is already in use, please choose another name"); } });
	if ($("#newResourceDescription").val().length < 3 || $("#newResourceDescription").val().length > 2048)
		error.push("Resource Description must be between 3 and 2048 characters long");
	if (/^[0-9.]*$/.test($("#newResourceCleanupCost").val()) == false)
		error.push("Cleanup Cost can only contain numbers and a period");
	if ($("#newResourceCleanupCost").val() == "" || parseFloat($("#newResourceCleanupCost").val()) < 0 || parseFloat($("#newResourceCleanupCost").val()) > 100000)
		error.push("Cleanup Cost must be between " + FormatDollars(0) + " and " + FormatDollars(100000));
	if (/^[0-9]*$/.test($("#newResourceCapacity").val()) == false)
		error.push("Max Occupancy can only contain numbers");
	if ($("#newResourceCapacity").val() == "" || parseInt($("#newResourceCapacity").val()) < 1 || parseInt($("#newResourceCapacity").val()) > 1000000)
		error.push("Max Occupancy must be between 1 and 1000000");
	if (/^[0-9]*$/.test($("#newResourceSeats").val()) == false)
		error.push("Seats can only contain numbers");
	if ($("#newResourceSeats").val() == "" || parseInt($("#newResourceSeats").val()) < 0 || parseInt($("#newResourceSeats").val()) > 1000000)
		error.push("Seats must be between 0 and 1000000");
	if (/^[0-9]*$/.test($("#newResourceCleanupTime").val()) == false)
		error.push("Cleanup Time can only contain whole numbers");
	if (/^[0-9]*$/.test($("#newResourceLeadTime").val()) == false)
		error.push("Lead Time can only contain whole numbers");
	if ($("#newResourceLeadTime").val() == "" || parseInt($("#newResourceLeadTime").val()) < 0 || parseInt($("#newResourceLeadTime").val()) > 259200)
		error.push("Lead Time must be between 0 and 259200");
	
	if ($("#billingHourly:checked").length > 0)
	{
		if (/^[0-9.]*$/.test($("#newResourceRate").val()) == false)
			error.push("Default rate can only contain numbers and a period");
		if ($("#newResourceRate").val() == "" || parseFloat($("#newResourceRate").val()) < 0 || parseFloat($("#newResourceRate").val()) > 100000)
			error.push("Default rate must be between " + FormatDollars(0) + " and " + FormatDollars(100000));
		if ($("#fgHours .timeslot-widget tbody tr").length < 1)
			error.push("You must specify the hours of operation");
		if (parseInt($("#newResourceMinDuration").val()) < 0 || parseInt($("#newResourceMinDuration").val()) > 10080)
			error.push("Minimum Duration must be between 0 minutes and 10080 minutes");
		if (parseInt($("#newResourceIncrement").val()) < 15 || parseInt($("#newResourceIncrement").val()) > 1440)
			error.push("Increment must be between 15 minutes and 1440 minutes");
	}
	
	if ($("#billingTimeslot:checked").length > 0)
	{
		if ($("#fgTimeslots .timeslot-widget tbody tr").length < 1)
			error.push("You must specify at least one bookable timeslot");
	}
	
	if (!$(".timeslot-widget:visible").tsWidget("validate"))
		error.push("Invalid timeslot definition");
	
	return error;
}

function EncodeResourceData($div, $info)
{	
	if (!$info)
	{
		$div.first("div").data("name", $("#newResourceName").val());
		$div.first("div").attr("data-id", $("#newResourceID").val());
		$div.first("div").data("description", $("#newResourceDescription").val());
		$div.first("div").attr("data-type", $("#newResourceType").val());
		$div.first("div").data("pictures", JSON.stringify(PicturesToArray($("#newResourcePictures"))));
		$div.first("div").attr("data-capacity", $("#newResourceCapacity").val());
		$div.first("div").attr("data-seats", $("#newResourceSeats").val());
		$div.first("div").attr("data-cleanupcost", $("#newResourceCleanupCost").val());
		$div.first("div").attr("data-cleanup", $("#newResourceCleanupTime").val());
		$div.first("div").attr("data-duration", $("#newResourceMinDuration").val());
		$div.first("div").attr("data-increment", $("#newResourceIncrement").val());
		$div.first("div").attr("data-lead", $("#newResourceLeadTime").val());
		$div.first("div").attr("data-autoapprove", ($("#newResourceAutoApprove").is(":checked") ? "true" : "false"));
		$div.first("div").attr("data-over21", ($("#newResourceOver21").is(":checked") ? "true" : "false"));
		$div.first("div").attr("data-linked", ($("#newResourceLinked").is(":checked") ? "true" : "false"));
		$div.first("div").attr("data-rate", $("#newResourceRate").val());
		$div.first("div").data("deposit", $("#selectDepositList option:selected").attr("value"));
		$div.first("div").data("refund", $("#selectRefundList option:selected").attr("value"));
		$div.first("div").attr("data-timeslots", ($("#billingHourly:checked").length > 0 ? 0 : 1));
		$div.first("div").data("data-hours", $("#fgHours .timeslot-widget").tsWidget("save"));
		$div.first("div").data("data-rates", $("#fgSpecialRates .timeslot-widget").tsWidget("save"));
		$div.first("div").data("data-slots", $("#fgTimeslots .timeslot-widget").tsWidget("save"));
		$div.first("div").data("addons", JSON.stringify(BuildAddonArray()));
		
		if ($("#billingHourly:checked").length == 0)
		{
			var min = 99999999;
			var rate = 99999999;
			var slots = $div.first("div").data("data-slots");
			for (var i=0; i<slots.length; i++)
			{
				var diff = slots[i]['stop'] - slots[i]['start'];
				// show the timeslot rate, not hourly rate
				//var r = slots[i]['rate'] / (diff / 60);
				var r = slots[i]['rate'];
				
				if (diff < min)
					min = diff;
				
				if (r < rate)
					rate = r;
			}
			
			$div.first("div").attr("data-duration",min);
			$div.first("div").attr("data-rate",rate);
		}
		
		$div.find("[name=resourceName]").first().empty().append($("#newResourceName").val());
		$div.find("[name=resourceRate]").first().empty().append("Rate: "+FormatDollars($("#newResourceRate").val()) + "/hr");
		$div.find("[name=resourceMin]").first().empty().append("Min: "+$("#newResourceMinDuration").val() + " mins");
	}
	else
	{
		$div.first("div").data("name", $info['name']);
		$div.first("div").attr("data-id", $info['id']);
		$div.first("div").data("description", $info['description']);
		$div.first("div").attr("data-type", $info['type']);
		$div.first("div").data("pictures", JSON.stringify($info['pictures']));
		$div.first("div").attr("data-capacity", $info['capacity']);
		$div.first("div").attr("data-seats", $info['seats']);
		$div.first("div").attr("data-cleanupcost", $info['cleanupcost']);
		$div.first("div").attr("data-cleanup", $info['cleanup']);
		$div.first("div").attr("data-duration", $info['duration']);
		$div.first("div").attr("data-increment", $info['increment']);
		$div.first("div").attr("data-lead", $info['lead']);
		$div.first("div").attr("data-autoapprove", ($info['autoapprove'] == "1" ? "true" : "false"));
		$div.first("div").attr("data-over21", ($info['over21'] == "1" ? "true" : "false"));
		$div.first("div").attr("data-linked", ($info['linked'] == "1" ? "true" : "false"));
		$div.first("div").attr("data-rate", $info['rate']);
		$div.first("div").data("deposit", $info['deposit']);
		$div.first("div").data("refund", $info['refund']);
		$div.first("div").attr("data-timeslots", $info['timeslots']);
		$div.first("div").data("data-hours", $info['hours']);
		$div.first("div").data("data-rates", $info['rates']);
		$div.first("div").data("data-slots", $info['slots']);
		$div.first("div").data("addons", $info['addons']);
		$div.find("[name=resourceName]").first().empty().append($info['name']);
		$div.find("[name=resourceRate]").first().empty().append("Rate: "+ FormatDollars($info['rate']) + "/hr");
		$div.find("[name=resourceMin]").first().empty().append("Min: "+ $info['duration'] + " mins");
	}
	
		return $div;
}

function ValidateMenu()
{
	var error = [];
	
	if ($("#newMenuName").val().length < 3 || $("#newMenuName").val().length > 128)
		error.push("Menu Name must be at between 3 and 128 characters long");
	$("div.menu").each(function(){ if ($(this).data("name") == $("#newMenuName").val() && ($(this).attr("data-id") && $(this).attr("data-id") != $("#newMenuID").val())) {
		error.push("This Menu Name is already in use, please choose another name"); } });
	if ($("#newMenuDescription").val().length < 3 || $("#newMenuDescription").val().length > 2048)
		error.push("Menu Description must be between 3 and 2048 characters long");
	if ($("#selectRefundList option:selected").length != 1)
		error.push("One refund policy must be assigned");
	if ($("#selectDepositList option:selected").length != 1)
		error.push("One deposit policy must be assigned");
	
	if ($("#fgHours .timeslot-widget tbody tr").length < 1)
			error.push("You must specify the availability");
	if (!$(".timeslot-widget:visible").tsWidget("validate"))
		error.push("Invalid timeslot definition");
	
	return error;
}

function EncodeMenuData($div, $info)
{
	if (!$info)
	{
		$div.first("div").data("name", $("#newMenuName").val());
		$div.first("div").attr("data-id", $("#newMenuID").val());
		$div.first("div").data("description", $("#newMenuDescription").val());
		$div.first("div").data("deposit", $("#selectDepositList option:selected").attr("value"));
		$div.first("div").data("refund", $("#selectRefundList option:selected").attr("value"));
		$div.first("div").data("data-hours", $("#fgHours .timeslot-widget").tsWidget("save"));
		$div.find("p:first").empty().append($("#newMenuName").val());
	}
	else
	{
		$div.first("div").data("name", $info['name']);
		$div.first("div").attr("data-id", $info['id']);
		$div.first("div").data("description", $info['description']);
		$div.first("div").data("deposit", $info['deposit']);
		$div.first("div").data("refund", $info['refund']);
		$div.first("div").data("data-hours", $info['hours']);
		$div.find("p:first").empty().append($info['name']);
	}
	
	return $div;
}

function ValidateMenuItem()
{
	var error = [];
	
	$("#newMenuItemPrice").val($("#newMenuItemPrice").val().replace(/[$,]/g,""));
	
	if ($("#newMenuItemName").val().length < 3 || $("#newMenuItemName").val().length > 128)
		error.push("Name must be at between 3 and 128 characters long");
	$("div.menuitem").each(function(){ if ($(this).data("name") == $("#newMenuItemName").val() && ($(this).attr("data-id") && $(this).attr("data-id") != $("#newMenuItemID").val())) {
		error.push("This Item Name is already in use, please choose another name"); } });
	if ($("#newMenuItemDescription").val().length < 3 || $("#newMenuItemDescription").val().length > 2048)
		error.push("Item Description must be between 3 and 2048 characters long");
	if (/^[0-9.]*$/.test($("#newMenuItemPrice").val()) == false)
		error.push("Price can only contain numbers and a period");
	if (/^[0-9]*$/.test($("#newMenuItemMin").val()) == false)
		error.push("Minimum order can only contain numbers");
	if (/^[0-9]*$/.test($("#newMenuItemMax").val()) == false)
		error.push("Maximum order can only contain numbers");
	if ($("#newMenuItemMin").val() == "" || parseInt($("#newMenuItemMin").val()) < 0 || parseInt($("#newMenuItemMin").val()) > 1000000)
		error.push("Minimum order must be between 0 and 1000");
	if ($("#newMenuItemMax").val() == "" || parseInt($("#newMenuItemMax").val()) < 1 || parseInt($("#newMenuItemMax").val()) > 1000000)
		error.push("Maximum order must be between 1 and 1000");
	if ($("#newMenuItemPrice").val() == "" || parseInt($("#newMenuItemPrice").val()) < 0 || parseInt($("#newMenuItemPrice").val()) > 1000000)
		error.push("Price per item must be between " + FormatDollars(0) + " and " + FormatDollars(10000));
		
	return error;
}

function EncodeMenuItemData($div, $info)
{	
	if (!$info)
	{
		$div.first("div").data("name", $("#newMenuItemName").val());
		$div.first("div").attr("data-id", $("#newMenuItemID").val());
		$div.first("div").attr("data-type", $("#newMenuItemType").val());
		$div.first("div").data("description", $("#newMenuItemDescription").val());
		$div.first("div").data("pictures", JSON.stringify(PicturesToArray($("#newMenuItemPictures"))));
		$div.first("div").attr("data-price", $("#newMenuItemPrice").val());
		$div.first("div").attr("data-min", $("#newMenuItemMin").val());
		$div.first("div").attr("data-max", $("#newMenuItemMax").val());
		$div.find("[name=menuItemName]").first().empty().append($("#newMenuItemName").val());
		$div.find("[name=menuItemPrice]").first().empty().append("Price: "+FormatDollars($("#newMenuItemPrice").val()));
		$div.find("[name=menuItemMin]").first().empty().append("Min: "+$("#newMenuItemMin").val());
		$div.find("[name=menuItemMax]").first().empty().append("Max: "+$("#newMenuItemMax").val());
	}
	else
	{
		$div.first("div").data("name", $info['name']);
		$div.first("div").attr("data-id", $info['id']);
		$div.first("div").attr("data-type", $info['type']);
		$div.first("div").data("description", $info['description']);
		$div.first("div").data("pictures", JSON.stringify($info['pictures']));
		$div.first("div").attr("data-price", $info['price']);
		$div.first("div").attr("data-min", $info['min']);
		$div.first("div").attr("data-max", $info['max']);
		$div.find("[name=menuItemName]").first().empty().append($info['name']);
		$div.find("[name=menuItemPrice]").first().empty().append("Price: "+FormatDollars($info['price']));
		$div.find("[name=menuItemMin]").first().empty().append("Min: "+$info['min']);
		$div.find("[name=menuItemMax]").first().empty().append("Max: "+$info['max']);
	}
	
		return $div;
}

function ValidatePersonnel()
{
	var error = [];
	
	$("#newPersonnelPrice").val($("#newPersonnelPrice").val().replace(/[$,]/g,""));
	
	if ($("#newPersonnelName").val().length < 3 || $("#newPersonnelName").val().length > 128)
		error.push("Personnel Name must be at between 3 and 128 characters long");
	$("div.personnel").each(function(){ if ($(this).data("name") == $("#newPersonnelName").val() && ($(this).attr("data-id") && $(this).attr("data-id") != $("#newPersonnelID").val())) {
		error.push("This Personnel Name is already in use, please choose another name"); } });
	if ($("#newPersonnelDescription").val().length < 3 || $("#newPersonnelDescription").val().length > 2048)
		error.push("Personnel Description must be between 3 and 2048 characters long");
	if (/^[0-9.]*$/.test($("#newPersonnelPrice").val()) == false)
		error.push("Price can only contain numbers and a period");
	if (/^[0-9]*$/.test($("#newPersonnelMin").val()) == false)
		error.push("Minimum order can only contain numbers");
	if (/^[0-9]*$/.test($("#newPersonnelMax").val()) == false)
		error.push("Maximum order can only contain numbers");
	if ($("#newPersonnelMin").val() == "" || parseInt($("#newPersonnelMin").val()) < 0 || parseInt($("#newPersonnelMin").val()) > 1000000)
		error.push("Minimum order must be between 0 and 1000");
	if ($("#newPersonnelMax").val() == "" || parseInt($("#newPersonnelMax").val()) < 1 || parseInt($("#newPersonnelMax").val()) > 1000000)
		error.push("Maximum order must be between 1 and 1000");
	if ($("#newPersonnelPrice").val() == "" || parseInt($("#newPersonnelPrice").val()) < 0 || parseInt($("#newPersonnelPrice").val()) > 1000000)
		error.push("Price per item must be between " + FormatDollars(0) + " and " + FormatDollars(10000));
		
	if ($("#fgHours .timeslot-widget tbody tr").length < 1)
			error.push("You must specify the availability");
	if (!$(".timeslot-widget:visible").tsWidget("validate"))
		error.push("Invalid timeslot definition");
	
	return error;
}

function EncodePersonnelData($div, $info)
{	
	if (!$info)
	{
		$div.first("div").data("name", $("#newPersonnelName").val());
		$div.first("div").attr("data-id", $("#newPersonnelID").val());
		$div.first("div").data("description", $("#newPersonnelDescription").val());
		$div.first("div").attr("data-price", $("#newPersonnelPrice").val());
		$div.first("div").attr("data-min", $("#newPersonnelMin").val());
		$div.first("div").attr("data-max", $("#newPersonnelMax").val());
		$div.first("div").attr("data-req", $("#newPersonnelReq").val());
		$div.first("div").data("data-hours", $("#fgHours .timeslot-widget").tsWidget("save"));
		$div.first("div").data("deposit", $("#selectDepositList option:selected").attr("value"));
		$div.first("div").data("refund", $("#selectRefundList option:selected").attr("value"));
		$div.find("[name=personnelName]").first().empty().append($("#newPersonnelName").val());
		$div.find("[name=personnelPrice]").first().empty().append("Price: "+FormatDollars($("#newPersonnelPrice").val()));
		$div.find("[name=personnelMin]").first().empty().append("Min: "+$("#newPersonnelMin").val());
		$div.find("[name=personnelMax]").first().empty().append("Max: "+$("#newPersonnelMax").val());
		
		var res = [];
		$("#selPersonnelResources option:selected").each(function()
		{
			res.push($(this).attr("data-id"));
		});
		if (res.length == 0) res.push("0");
		
		$div.first("div").attr("data-resources", JSON.stringify(res));
	}
	else
	{
		$div.first("div").data("name", $info['name']);
		$div.first("div").attr("data-id", $info['id']);
		$div.first("div").data("description", $info['description']);
		$div.first("div").attr("data-price", $info['price']);
		$div.first("div").attr("data-min", $info['min']);
		$div.first("div").attr("data-max", $info['max']);
		$div.first("div").attr("data-req", $info['req']);
		$div.first("div").data("data-hours", $info['hours']);
		$div.first("div").data("deposit", $info['deposit']);
		$div.first("div").data("refund", $info['refund']);
		$div.find("[name=personnelName]").first().empty().append($info['name']);
		$div.find("[name=personnelPrice]").first().empty().append("Price: "+FormatDollars($info['price']));
		$div.find("[name=personnelMin]").first().empty().append("Min: "+$info['min']);
		$div.find("[name=personnelMax]").first().empty().append("Max: "+$info['max']);
		
		$div.first("div").attr("data-resources", $info['resources']);
	}
	
		return $div;
}

function ValidateAddon()
{
	var error = [];
	
	$("#newAddonPrice").val($("#newAddonPrice").val().replace(/[$,]/g,""));
	
	if ($("#newAddonName").val().length < 3 || $("#newAddonName").val().length > 128)
		error.push("Addon Name must be at between 3 and 128 characters long");
	$("div.addon").each(function(){ if ($(this).data("name") == $("#newAddonName").val() && ($(this).attr("data-id") && $(this).attr("data-id") != $("#newAddonID").val())) {
		error.push("This Addon Name is already in use, please choose another name"); } });
	if ($("#newAddonDescription").val().length < 3 || $("#newAddonDescription").val().length > 2048)
		error.push("Addon Description must be between 3 and 2048 characters long");
	if (/^[0-9.]*$/.test($("#newAddonPrice").val()) == false)
		error.push("Price can only contain numbers and a period");
	if (/^[0-9]*$/.test($("#newAddonMinimum").val()) == false)
		error.push("Minimum order can only contain numbers");
	if (/^[0-9]*$/.test($("#newAddonMaximum").val()) == false)
		error.push("Maximum order can only contain numbers");
	if ($("#selectRefundList option:selected").length != 1)
		error.push("One refund policy must be assigned");
	if ($("#selectDepositList option:selected").length != 1)
		error.push("One deposit policy must be assigned");
	if ($("#newAddonMinimum").val() == "" || parseInt($("#newAddonMinimum").val()) < 0 || parseInt($("#newAddonMinimum").val()) > 1000000)
		error.push("Minimum order must be between 0 and 1000");
	if ($("#newAddonMaximum").val() == "" || parseInt($("#newAddonMaximum").val()) < 1 || parseInt($("#newAddonMaximum").val()) > 1000000)
		error.push("Maximum order must be between 1 and 1000");
	if ($("#newAddonPrice").val() == "" || parseFloat($("#newAddonPrice").val()) < 0 || parseFloat($("#newAddonPrice").val()) > 1000000)
		error.push("Price per order must be between " + FormatDollars(0) + " and " + FormatDollars(10000));
	
	if ($("#fgHours .timeslot-widget tbody tr").length < 1)
			error.push("You must specify the availability");
	if (!$(".timeslot-widget:visible").tsWidget("validate"))
		error.push("Invalid timeslot definition");
	
	return error;
}

function EncodeAddonData($div, $info)
{	
	if (!$info)
	{
		$div.first("div").data("name", $("#newAddonName").val());
		$div.first("div").attr("data-id", $("#newAddonID").val());
		$div.first("div").data("description", $("#newAddonDescription").val());
		$div.first("div").attr("data-type", $("#newAddonType").val());
		$div.first("div").data("pictures", JSON.stringify(PicturesToArray($("#newAddonPictures"))));
		$div.first("div").attr("data-minimum", $("#newAddonMinimum").val());
		$div.first("div").attr("data-maximum", $("#newAddonMaximum").val());
		$div.first("div").attr("data-deliverable", ($("#newAddonDeliver").prop("checked") ? 1 : 0));
		$div.first("div").attr("data-price", $("#newAddonPrice").val());
		$div.first("div").data("deposit", $("#selectDepositList option:selected").attr("value"));
		$div.first("div").data("refund", $("#selectRefundList option:selected").attr("value"));
		$div.first("div").data("data-hours", $("#fgHours .timeslot-widget").tsWidget("save"));
		$div.find("[name=addOnName]").first().empty().append($("#newAddonName").val());
		$div.find("[name=addOnPrice]").first().empty().append("Price: "+ FormatDollars($("#newAddonPrice").val()));
		$div.find("[name=addOnMin]").first().empty().append("Min: "+ $("#newAddonMinimum").val());
		$div.find("[name=addOnMax]").first().empty().append("Max: "+ $("#newAddonMaximum").val());
	}
	else
	{
		$div.first("div").data("name", $info['name']);
		$div.first("div").attr("data-id", $info['id']);
		$div.first("div").data("description", $info['description']);
		$div.first("div").attr("data-type", $info['type']);
		$div.first("div").data("pictures", JSON.stringify($info['pictures']));
		$div.first("div").attr("data-minimum", $info['minimum']);
		$div.first("div").attr("data-maximum", $info['maximum']);
		$div.first("div").attr("data-deliverable", $info['deliverable']);
		$div.first("div").attr("data-price", $info['price']);
		$div.first("div").data("deposit", $info['deposit']);
		$div.first("div").data("refund", $info['refund']);
		$div.first("div").data("data-hours", $info['hours']);
		$div.find("[name=addOnName]").first().empty().append($info['name']);
		$div.find("[name=addOnPrice]").first().empty().append("Price: "+FormatDollars($info['price']));
		$div.find("[name=addOnMin]").first().empty().append("Min: "+$info['minimum']);
		$div.find("[name=addOnMax]").first().empty().append("Max: "+$info['maximum']);
	}
	
	return $div;
}

function ValidateDeposit()
{
	var error = [];
	
	$("#newDepositThreshold").val($("#newDepositThreshold").val().replace(/[$,]/g,""));
	$("#newDepositPerc").val($("#newDepositPerc").val().replace(/%/g,""));
	
	if ($("#newDepositName").val().length < 3 || $("#newDepositName").val().length > 128)
		error.push("Policy Name must be at between 3 and 128 characters long");
	$("div.deposit").each(function(){ if ($(this).data("name") == $("#newDepositName").val() && ($(this).attr("data-id") && $(this).attr("data-id") != $("#newDepositID").val())) {
		error.push("This Policy Name is already in use, please choose another name"); } });
	if (/^[0-9.]*$/.test($("#newDepositThreshold").val()) == false)
		error.push("'Applies if over' threshold can only contain numbers and a period");
	if (/^[0-9]*$/.test($("#newDepositPerc").val()) == false)
		error.push("Deposit percentage must be a whole number");
	if (/^[0-9]*$/.test($("#newDepositFull").val()) == false)
		error.push("Full Payment Due can only contain numbers");
	if ($("#newDepositFull").val() == "" || parseInt($("#newDepositFull").val()) < 1 || parseInt($("#newDepositFull").val()) > 365)
		error.push("Full Payment Due must be between 1 and 365");
	if ($("#newDepositThreshold").val() == "" || parseFloat($("#newDepositThreshold").val()) < 0.00 || parseFloat($("#newDepositThreshold").val()) > 10000)
		error.push("'Applies if over' threshold must be between " + FormatDollars(0) + " and " + FormatDollars(10000));
	if ($("#newDepositPerc").val() == "" || parseInt($("#newDepositPerc").val()) < 0 || parseInt($("#newDepositPerc").val()) > 100)
		error.push("Deposit percentage must be between 0 and 100");
		
	return error;
}

function EncodeDepositData($div, $info)
{
	if (!$info)
	{
		$div.first("div").data("name", $("#newDepositName").val());
		$div.first("div").attr("data-id", $("#newDepositID").val());
		$div.first("div").attr("data-threshold", $("#newDepositThreshold").val());
		$div.first("div").attr("data-perc", $("#newDepositPerc").val());
		$div.first("div").attr("data-amount", $("#newDepositAmount").val());
		$div.first("div").attr("data-full", $("#newDepositFull").val());
		$div.find("[name=depositName]").first().empty().append($("#newDepositName").val());
		$div.find("[name=depositPerc]").first().empty().append("Amount: " + FormatDollars($("#newDepositAmount").val()) + " + " + $("#newDepositPerc").val() + "%");
	}
	else
	{
		$div.first("div").data("name", $info['name']);
		$div.first("div").attr("data-id", $info['id']);
		$div.first("div").attr("data-threshold", $info['threshold']);
		$div.first("div").attr("data-perc", $info['perc']);
		$div.first("div").attr("data-amount", $info['amount']);
		$div.first("div").attr("data-full", $info['full']);
		$div.find("[name=depositName]").first().empty().append($info['name']);
		$div.find("[name=depositPerc]").first().empty().append("Amount: " + FormatDollars($info['amount']) + " + " + $info['perc'] + "%");
	}
	
	return $div;
}

function ValidateRefund()
{
	var error = [];
	
	if ($("#newRefundName").val().length < 3 || $("#newRefundName").val().length > 128)
		error.push("Policy Name must be at between 3 and 128 characters long");
	$("div.refund").each(function(){ if ($(this).data("name") == $("#newRefundName").val() && ($(this).attr("data-id") && $(this).attr("data-id") != $("#newRefundID").val())) {
		error.push("This Policy Name is already in use, please choose another name"); } });
	if (/^[0-9]*$/.test($("input.txtRefundFee").val()) == false || $("input.txtRefundFee").val() > 100 || $("input.txtRefundFee").val() < 0 || $("input.txtRefundFee").val().length < 1)
		error.push("Refund Fee percentage must be a whole number between 0 and 100");
	if (/^[0-9]*$/.test($("input.txtRefundDays").val()) == false || $("input.txtRefundDays").val() > 365 || $("input.txtRefundDays").val() < 0 || $("input.txtRefundDays").val().length < 1)
		error.push("Refund Days must be a whole number between 0 and 365");
	
	return error;
}

function EncodeRefundData($div, $info)
{
	if (!$info)
	{
		$div.first("div").data("name", $("#newRefundName").val());
		$div.first("div").attr("data-id", $("#newRefundID").val());
		var pol = [];
		$("div.rPolicyDetail").each(function(){pol.push({days:$(this).find("input.txtRefundDays").first().val(),fee:$(this).find("input.txtRefundFee").first().val()});});
		pol.sort(function(a,b){return b['days']-a['days'];});
		
		$div.first("div").attr("data-policy", JSON.stringify(pol));
		$div.find("[name=refundName]").first().empty().append($("#newRefundName").val());
	}
	else
	{
		$div.first("div").data("name", $info['name']);
		$div.first("div").attr("data-id", $info['id']);
		$div.first("div").attr("data-policy", $info['policy']);
		$div.find("[name=refundName]").first().empty().append($info['name']);
	}
	
	return $div;
}

function EncodeContactData($opt, $info)
{
	if (!$info)
	{
		$opt.data("name", $("#newContactName").val());
		$opt.data("title", $("#newContactTitle").val());
		$opt.data("email", $("#newContactEmail").val());
		$opt.data("phone", $("#newContactPhone").val());
		$opt.data("comments", $("#newContactComments").val());
		$opt.prop("label",$("#newContactName").val());
	}
	else
	{
		$opt.data("name", $info['name']);
		$opt.data("title", $info['title']);
		$opt.data("email", $info['email']);
		$opt.data("phone", $info['phone']);
		$opt.data("comments", $info['comments']);
		$opt.prop("label", $info['name']);
	}
	
	return $opt;
}

function PopulateResourceData($div)
{
	$("#newResourceName").val($div.first("div").data("name"));
	$("#newResourceID").val($div.first("div").attr("data-id"));
	$("#newResourceDescription").val($div.first("div").data("description"));
	$("#newResourceType").find("option[value='" + SanitizeAttr($div.first("div").attr("data-type")) +"']").prop("selected",true);
	$("#newResourcePictures").data("pictures",($div.first("div").data("pictures")));
	$("#newResourceCapacity").val($div.first("div").attr("data-capacity"));
	$("#newResourceSeats").val($div.first("div").attr("data-seats"));
	$("#newResourceCleanupCost").val($div.first("div").attr("data-cleanupcost"));
	$("#newResourceCleanupTime").val($div.first("div").attr("data-cleanup"));
	$("#newResourceMinDuration").val($div.first("div").attr("data-duration"));
	$("#newResourceIncrement").val($div.first("div").attr("data-increment"));
	$("#newResourceLeadTime").val($div.first("div").attr("data-lead"));
	$("#newResourceRate").val($div.first("div").attr("data-rate"));
	($div.first("div").attr("data-over21") == "true" ? $("#newResourceOver21").prop("checked",true) : $("#newResourceOver21").prop("checked",false));
	($div.first("div").attr("data-autoapprove") == "true" ? $("#newResourceAutoApprove").prop("checked",true) : $("#newResourceAutoApprove").prop("checked",false));
	($div.first("div").attr("data-linked") == "true" ? $("#newResourceLinked").prop("checked",true) : $("#newResourceLinked").prop("checked",false));
	($div.first("div").attr("data-timeslots") == 1 ? $("#billingTimeslot").trigger('click') : $("#billingHourly").trigger('click'));
	$("#selectDepositList").find("option[value='" + SanitizeAttr($div.first("div").data("deposit")) +"']").prop("selected",true);
	$("#selectRefundList").find("option[value='" + SanitizeAttr($div.first("div").data("refund")) +"']").prop("selected",true);
	$("#newResourceType")[0].sumo.reload();
	$("#selectRefundList")[0].sumo.reload();
	$("#selectDepositList")[0].sumo.reload();
	
	$("#fgHours .timeslot-widget").tsWidget("restore",$div.first("div").data("data-hours"));
	$("#fgSpecialRates .timeslot-widget").tsWidget("restore",$div.first("div").data("data-rates"));
	$("#fgTimeslots .timeslot-widget").tsWidget("restore",$div.first("div").data("data-slots"));
	
	PopulateAddonList();
	PopulateAddonSelection($div);
	
	var arr;
	if ($("#newResourcePictures").data("pictures"))
		arr = $.parseJSON($("#newResourcePictures").data("pictures"));
	else arr = [];
	
	for (var i = 0; i < arr.length; i++)
	{
		InsertPicture($("#newResourcePictures"),arr[i].url,arr[i].caption);
    }
    for (var i = $("#newResourcePictures").find("div.pic").length; i > $("#newResourcePictures").attr("data-limit"); i--)
		$("#newResourcePictures").find("div.pic").last().remove();
		
	var $parents = "";
	$div.parents("div.space").each(function(){$parents += $(this).data("name") + " <B>></B> ";});
	$parents += $div.data("name");
	$("#newResourceBreadcrumbs").empty().append("<em>"+$parents+"</em>");
	$("#newResourceBreadcrumbs").css({"display":"block"});	
	if ($div.first("div").attr("data-id"))
	{
		var data = {method:'fCheckIfEditOK', resourceid:$div.attr("data-id")};
		Post(data).then(function($data)
		{
			if ($data['result'] != "success") 
			{
				// alert that there are bookings for this resource
				
				//$("#newResourceName").attr("disabled","disabled");
				//$("#newResourceDescription").attr("disabled","disabled");
				//$("#newResourceType").attr("disabled","disabled");
				//$("#newResourceCapacity").attr("disabled","disabled");
				//$("#newResourceSeats").attr("disabled","disabled");
				//$("#newResourceCleanupCost").attr("disabled","disabled");
				//$("#newResourceCleanupTime").attr("disabled","disabled");
				//$("#newResourceOver21").attr("disabled","disabled");
				//$("#newResourceLinked").attr("disabled","disabled");
				//$("#newResourceLocked").css({"display":"block"});
			} 
		});
    }
}

function PopulateMenuData($div)
{
	$("#newMenuName").val($div.first("div").data("name"));
	$("#newMenuID").val($div.first("div").attr("data-id"));
	$("#newMenuDescription").val($div.first("div").data("description"));
	$("#selectDepositList").find("option[value='" + SanitizeAttr($div.first("div").data("deposit")) +"']").prop("selected",true);
	$("#selectRefundList").find("option[value='" + SanitizeAttr($div.first("div").data("refund")) +"']").prop("selected",true);
	$("#fgHours .timeslot-widget").tsWidget("restore",$div.first("div").data("data-hours"));
	$("#selectRefundList")[0].sumo.reload();
	$("#selectDepositList")[0].sumo.reload();
}

function PopulateMenuItemData($div)
{
	$("#newMenuItemName").val($div.first("div").data("name"));
	$("#newMenuItemID").val($div.first("div").attr("data-id"));
	$("#newMenuItemType").find("option[value='" + SanitizeAttr($div.first("div").attr("data-type")) +"']").prop("selected",true);
	$("#newMenuItemDescription").val($div.first("div").data("description"));
	$("#newMenuItemPictures").data("pictures",($div.first("div").data("pictures")));
	$("#newMenuItemPrice").val($div.first("div").attr("data-price"));
	$("#newMenuItemMin").val($div.first("div").attr("data-min"));
	$("#newMenuItemMax").val($div.first("div").attr("data-max"));
	$("#newMenuItemType")[0].sumo.reload();
	
	var arr;
	if ($("#newMenuItemPictures").data("pictures"))
		arr = $.parseJSON($("#newMenuItemPictures").data("pictures"));
	else arr = [];
	
	for (var i = 0; i < arr.length; i++)
	{
		InsertPicture($("#newMenuItemPictures"),arr[i].url,arr[i].caption);
    }
    for (var i = $("#newMenuItemPictures").find("div.pic").length; i > $("#newMenuItemPictures").attr("data-limit"); i--)
		$("#newMenuItemPictures").find("div.pic").last().remove();
		
	var $parents = "";
	$div.parents("div.menu").each(function(){$parents += $(this).data("name") + " <B>></B> ";});
	$parents += $div.data("name");
	$("#newMenuItemBreadcrumbs").empty().append("<em>"+$parents+"</em>");
	$("#newMenuItemBreadcrumbs").css({"display":"block"});

}

function PopulatePersonnelData($div)
{
	$("#newPersonnelName").val($div.first("div").data("name"));
	$("#newPersonnelID").val($div.first("div").attr("data-id"));
	$("#newPersonnelDescription").val($div.first("div").data("description"));
	$("#newPersonnelPrice").val($div.first("div").attr("data-price"));
	$("#newPersonnelMin").val($div.first("div").attr("data-min"));
	$("#newPersonnelMax").val($div.first("div").attr("data-max"));
	$("#newPersonnelReq").val($div.first("div").attr("data-req"));
	$("#selectDepositList").find("option[value='" + SanitizeAttr($div.first("div").data("deposit")) +"']").prop("selected",true);
	$("#selectRefundList").find("option[value='" + SanitizeAttr($div.first("div").data("refund")) +"']").prop("selected",true);
	$("#fgHours .timeslot-widget").tsWidget("restore",$div.first("div").data("data-hours"));
	$("#selectRefundList")[0].sumo.reload();
	$("#selectDepositList")[0].sumo.reload();
	
	var res = $.parseJSON($div.first("div").attr("data-resources"));
	for (var i = 0; i < res.length; i++)
	{
		$("#selPersonnelResources option[data-id='" + SanitizeAttr(res[i]) + "']").prop("selected",true);
	}			
	
	if (typeof $("#selPersonnelResources")[0].sumo !== "undefined")
		$("#selPersonnelResources")[0].sumo.reload();
	else $("#selPersonnelResources").SumoSelect();
}

function PopulateAddonData($div)
{
	$("#newAddonName").val($div.first("div").data("name"));
	$("#newAddonID").val($div.first("div").attr("data-id"));
	$("#newAddonDescription").val($div.first("div").data("description"));
	$("#newAddonType").find("option[value='" + SanitizeAttr($div.first("div").attr("data-type")) +"']").prop("selected",true);
	$("#newAddonPictures").data("pictures",($div.first("div").data("pictures")));
	$("#newAddonMinimum").val($div.first("div").attr("data-minimum"));
	$("#newAddonMaximum").val($div.first("div").attr("data-maximum"));
	$("#newAddonDeliver").prop("checked",($div.first("div").attr("data-deliverable") == 1 ? true : false));
	$("#newAddonPrice").val($div.first("div").attr("data-price"));
	$("#selectDepositList").find("option[value='" + SanitizeAttr($div.first("div").data("deposit")) +"']").prop("selected",true);
	$("#selectRefundList").find("option[value='" + SanitizeAttr($div.first("div").data("refund")) +"']").prop("selected",true);
	$("#fgHours .timeslot-widget").tsWidget("restore",$div.first("div").data("data-hours"));
	$("#newAddonType")[0].sumo.reload();
	$("#selectRefundList")[0].sumo.reload();
	$("#selectDepositList")[0].sumo.reload();
	
	var arr;
	if ($("#newAddonPictures").data("pictures"))
		arr = $.parseJSON($("#newAddonPictures").data("pictures"));
	else arr = [];
	
	for (var i = 0; i < arr.length; i++)
	{
		InsertPicture($("#newAddonPictures"),arr[i].url,arr[i].caption);
    }
    for (var i = $("#newAddonPictures").find("div.pic").length; i > $("#newAddonPictures").attr("data-limit"); i--)
		$("#newAddonPictures").find("div.pic").last().remove();
		
	if ($div.first("div").attr("data-id"))
	{
		//$("#newAddonName").attr("disabled","disabled");
		//$("#newAddonDescription").attr("disabled","disabled");
		//$("#newAddonType").attr("disabled","disabled");
		//$("#newAddonLocked").css({"display":"block"});
    }
}

function PopulateDepositData($div)
{
	$("#newDepositName").val($div.first("div").data("name"));
	$("#newDepositID").val($div.first("div").attr("data-id"));
	$("#newDepositThreshold").val($div.first("div").attr("data-threshold"));
	$("#newDepositPerc").val($div.first("div").attr("data-perc"));
	$("#newDepositAmount").val($div.first("div").attr("data-amount"));
	$("#newDepositFull").val($div.first("div").attr("data-full"));
}

function PopulateRefundData($div)
{
	$("#newRefundName").val($div.first("div").data("name"));
	$("#newRefundID").val($div.first("div").attr("data-id"));
	var arr = $.parseJSON($div.first("div").attr("data-policy"));
	for (var i = 0; i < arr.length; i++)
	{
		$("#rPolicyList").prepend("<div class='rPolicyDetail'>\
					<input type='text' class='txtRefundFee' placeholder='00' value='" + SanitizeAttr(arr[i]['fee']) +"'/><b>&#37; fee</b> if cancelled\
					<button class='btn btn-xs pull-right btnrPolicyDetailDelete'><i class='glyphicon glyphicon-trash'></i></button><br>\
					<div class='clearfix'></div>\
					<input type='text' class='txtRefundDays' placeholder='00' value='" + SanitizeAttr(arr[i]['days']) +"'/> days or less before the booking start date\
				</div>");
    }
}

function PopulateContactData($opt)
{
	$("#newContactName").val($opt.data("name"));
	$("#newContactTitle").val($opt.data("title"));
	$("#newContactEmail").val($opt.data("email"));
	$("#newContactPhone").val($opt.data("phone"));
	$("#newContactComments").val($opt.data("comments"));
}

function PopulateVenueTypes($select)
{
	var deferred = new $.Deferred();
	var data = {method:'fGetVenueTypes'};
	Post(data).then(function($data)
	{
		if ($data['result'] == "success")
		{
			for (var i=0; i<$data['data'].length; i++)
			{
				$select.append($("<option></option>",{value:$data['data'][i]['id'],text:$data['data'][i]['name']}));
			}
		}
		$select.SumoSelect();
		deferred.resolve();
	});
	
	return deferred.promise();
}

function PopulateVenueStyles($select)
{
	var deferred = new $.Deferred();
	var data = {method:'fGetVenueStyles'};
	Post(data).then(function($data)
	{
		if ($data['result'] == "success")
		{
			for (var i=0; i<$data['data'].length; i++)
			{
				$select.append($("<option></option>",{value:$data['data'][i]['id'],text:$data['data'][i]['name']}));
			}
		}
		$select.SumoSelect({placeholder:"Select Features...", okCancelInMulti:true});
		deferred.resolve();
	});
	
	return deferred.promise();
}

function PopulateVenueFeatures($select)
{
	var deferred = new $.Deferred();
	var data = {method:'fGetVenueFeatures'};
	Post(data).then(function($data)
	{
		if ($data['result'] == "success")
		{
			$select.find("option").remove();
			for (var i=0; i<$data['data'].length; i++)
			{
				$select.append($("<option></option>",{value:$data['data'][i]['id'],text:$data['data'][i]['name']}));
			}
		}
		$select.SumoSelect({placeholder:"Select Features...", okCancelInMulti:true});
		deferred.resolve();
	});
	
	return deferred.promise();
}

function PopulateResourceTypes($select)
{
	var deferred = new $.Deferred();
	var data = {method:'fGetResourceTypes'};
	Post(data).then(function($data)
	{
		if ($data['result'] == "success")
		{
			for (var i=0; i<$data['data'].length; i++)
			{
				$select.append($("<option></option>",{value:$data['data'][i]['id'],text:$data['data'][i]['name']}));
			}
		}
		
		if (typeof $select[0].sumo !== "undefined")
			$select[0].sumo.reload();
		else $select.SumoSelect();
		
		deferred.resolve();
	});
	
	return deferred.promise();
}

function PopulateAddonTypes($select)
{
	var deferred = new $.Deferred();
	var data = {method:'fGetAddonTypes'};
	Post(data).then(function($data)
	{
		if ($data['result'] == "success")
		{
			for (var i=0; i<$data['data'].length; i++)
			{
				$select.append($("<option></option>",{value:$data['data'][i]['id'],text:$data['data'][i]['name']}));
			}
		}
		
		if (typeof $select[0].sumo !== "undefined")
			$select[0].sumo.reload();
		else $select.SumoSelect();
		
		deferred.resolve();
	});
	
	return deferred.promise();
}

function PopulateMenuItemTypes()
{
	var deferred = new $.Deferred();
	var data = {method:'fGetMenuItemTypes'};
	Post(data).then(function($data)
	{
		if ($data['result'] == "success")
		{
			for (var i=0; i<$data['data'].length; i++)
			{
				$("#newMenuItemType").append($("<option></option>",{value:$data['data'][i]['id'],text:$data['data'][i]['name']}));
			}
		}
		
		if (typeof $("#newMenuItemType")[0].sumo !== "undefined")
			$("#newMenuItemType")[0].sumo.reload();
		else $("#newMenuItemType").SumoSelect();
		
		deferred.resolve();
	});
	
	return deferred.promise();
}

function PopulateDepositList()
{
	$("div.deposit").each(function()
	{
		$("#selectDepositList").append($("<option></option>",{value:$(this).data("name"),text:$(this).data("name")}));
	});
	
	if (typeof $("#selectDepositList")[0].sumo !== "undefined")
		$("#selectDepositList")[0].sumo.reload();
	else $("#selectDepositList").SumoSelect();
}

function PopulateRefundList()
{
	$("div.refund").each(function()
	{
		$("#selectRefundList").append($("<option></option>",{value:$(this).data("name"),text:$(this).data("name")}));
	});
	
	if (typeof $("#selectRefundList")[0].sumo !== "undefined")
		$("#selectRefundList")[0].sumo.reload();
	else $("#selectRefundList").SumoSelect();
}

function PopulateAddonList()
{
	$("div.addon").each(function()
	{
		$("#selAddonsAvailable").append($("<option></option>",{value:$(this).data("name"),text:$(this).data("name")}));
	});
	
	if (typeof $("#selAddonsAvailable")[0].sumo !== "undefined")
		$("#selAddonsAvailable")[0].sumo.reload();
	else $("#selAddonsAvailable").SumoSelect({okCancelInMulti: true, selectAll: true});
}

function BuildAddonArray()
{
	var arr = [];
	$("#selAddonsAvailable option:selected").each(function()
	{
		arr.push($(this).attr("value"));
	});
	
	return arr;
}

function PopulateAddonSelection($div)
{
	if (!$div.data("addons"))
		return;
		
	var data = $.parseJSON($div.data("addons"));
	
	for (var i = 0; i < data.length; i++)
	{
		$("#selAddonsAvailable option[value='" + String(data[i]) + "']").prop("selected",true);
    }
	
    if (typeof $("#selAddonsAvailable")[0].sumo !== "undefined")
		$("#selAddonsAvailable")[0].sumo.reload();
	else $("#selAddonsAvailable").SumoSelect({okCancelInMulti: true, selectAll: true});
}

function ColorResources($cnt,$div)
{
	var colors = ["none","#c5c8d3","#cadee6","#d3e6ca","#e0e6ca","#e6daca","#e6caca"];
	
	if (!$cnt)
		$cnt = 0;
	if ($cnt > 6)
		$cnt = 1;
		
	if (!$div)
		$div = $("div.resource_table").first();
	else $div.css({"background-color":colors[$cnt]});
	
	$div.children("div.creatorcategory.space").each(function(){ColorResources($cnt+1,$(this));});

}

function ColorVenueLogo()
{
	$("#venuePictures div.pic").each(function(){ $(this).css({"border":"none"}); $(this).find("[name=venueLogo]").remove();});
	$("#venuePictures div.pic").each(function(){ $(this).css({"border":"none"}); $(this).find("[name=venueHeader]").remove();});
	
	$("#venuePictures div.pic").first().prepend("<div style='margin-left:4px;color:green' name='venueLogo'><small><b>Logo</b></small></div>").css({"border":"3px solid green"});
	$("#venuePictures div.pic:nth-child(2n)").first().prepend("<div style='margin-left:4px;color:orange' name='venueHeader'><small><b>Profile Header</b></small></div>").css({"border":"3px solid orange"});
}

function CreatePromoDetailsPage()
{
	var deferred = new $.Deferred();
	
	$("#divPromoCodeDetails").empty();
	LoadPartial("/venue-creator/promo.html","divPromoCodeDetails").done(function()
	{
		PopulatePromoResourceList();
		
		$("#txtPromoPerUser").on('blur',function(event){ $(this).val($(this).val().replace(/[^0-9.]/g,"")); if ($(this).val().length > 0) $("#chkPromoPerUserUnlim").prop("checked",false); else $("#chkPromoPerUserUnlim").prop("checked",true);});
		$("#txtPromoQuantity").on('blur',function(event){ $(this).val($(this).val().replace(/[^0-9.]/g,"")); if ($(this).val().length > 0) $("#chkPromoQuantityUnlim").prop("checked",false); else $("#chkPromoQuantityUnlim").prop("checked",true);});
		$("#txtPromoThreshold").on('blur',function(event){ $(this).val($(this).val().replace(/[^0-9.]/g,"")); if ($(this).val().length < 1) $(this).val("0"); $(this).val(FormatDollars($(this).val())); });
		
		$("#txtPromoAmount").on('blur',function(event)
		{
			$(this).val($(this).val().replace(/[^0-9.]/g,""));
			if ($("#selPromoType option[value=dollar]:selected").length > 0)
				$(this).val(FormatDollars($(this).val()));
		});
		
		$("#txtPromoAmount").blur(function(event)
		{
			if ($("#selPromoType option[value=percent]:selected").length > 0)
				$(this).val($(this).val().replace(/[^0-9.]/g,"") + "%");
			else $(this).val(FormatDollars($(this).val().replace(/[^0-9.]/g,"")));
		});
		
		$("#txtPromoStart").datepicker(
		{
			inline: true,
			changeMonth: true,
			changeYear: true,
			yearRange: "2013:+5",
			dateFormat: 'MM d, yy'
		});
		
		$("#txtPromoStop").datepicker(
		{
			inline: true,
			changeMonth: true,
			changeYear: true,
			yearRange: "2013:+5",
			dateFormat: 'MM d, yy'
		});
		
		$("input[name='radioExpType']:radio").change(function(event)
		{
			$("div.promoExpText").remove();
			$("#txtPromoExpVal").datepicker("destroy");
			
			switch ($(this).val())
			{
				case "never":
					$("#expValue").hide();
					break;
				case "after":
					$("#expValue").show();
					$("#expValue input").width("100px").after("<div class='promoExpText'>minutes after the booking is created</div>");
					$("#txtPromoExpVal").val("");
					break;
				case "before":
					$("#expValue").show();
					$("#expValue input").width("100px").after("<div class='promoExpText'>minutes before the event</div>");
					$("#txtPromoExpVal").val("");
					break;
			};
		});
	
		$("#btnSavePromoCode").off("click").click(function(event)
		{
			event.preventDefault();
			var errors = ValidatePromoCode();
			if (errors.length > 0)
			{	
				var str = "<div class='alert alert-danger'><ul>";
				for (var i = 0; i < errors.length; i++)
					str += "<li>" + errors[i] + "</li>";
				str += "</ul></div>";
				
				$(this).parents("form").first().find("div.alert-danger").remove();
				$(this).parents("form").first().prepend(str);
			}
			else
			{
				if ($("#selectPromoCodes option:selected").length > 0)
				{
					EncodePromoData($("#selectPromoCodes option:selected"));
				}
				else
				{
					var opt = $("<option/>");
					opt.data("name",$("#txtPromoCode").val());
					opt.text($("#txtPromoCode").val());
					EncodePromoData(opt);
					$("#selectPromoCodes").prepend(opt);
					$("#selectPromoCodes").attr("size",$("#selectPromoCodes option").length+1);
				}
				$("#divPromoCodeDetails").empty();
				$("#selectPromoCodes option:selected").prop("selected",false);
			}
			$("html, body").animate({ scrollTop: $("div.alert.alert-danger").first().offset().top });
		});
		
		deferred.resolve();
	});
	
	return deferred.promise();
}

var myVar;

	function getCalendarlist() {
	    myVar = setInterval(checkCalendarlist, 3000);
	}


	function checkCalendarlist() {
		//alert(localStorage.getItem("sync_resouceid"));
		var rids ={"rids":localStorage.getItem("sync_resouceid")};
		var myurl = window.location.href;
		if(myurl.indexOf("bookings#profile")>0)
			var urls="../callist.php";
		else
			var urls="callist.php";
		$.ajax({
		        url:urls,
		        type:'POST',
		        data:rids,
		        success:function(result){
					if(result!='' && result != null)
		        	{
		        		var res=JSON.parse(result);
			        	var listdata='';
			        	console.log(res);
		    	    	for(var index in res.name){
						//console.log(res.name[index]);
						//console.log(res.cid[index]);
							if(res.temp[index]==1)
							{
								listdata += '<option selected="selected" value='+res.cid[index]+'>';
								listdata += res.name[index];
								listdata += '</option>';
							}
							else
							{
								listdata += '<option value='+res.cid[index]+'>';
								listdata += res.name[index];
								listdata += '</option>';
							}
						}
					$("#authorize-url").css('display','none');
					$("#cal").css("display","block");
					$("#calendarList").html('');
					$("#calendarList").append(listdata);      		
	        		stopCalanderlist();
		        	}
		    }
		  });
	}
	function stopCalanderlist() {
	    clearInterval(myVar);
	} 
	

function ClickEdit($button)
{
	var $div = $button.closest("div.creatorcategory");
	getCalendarlist();
	//alert($div.attr("data-id"));
	$('#calendarList').css({"display":"none !important"});
	localStorage.removeItem("sync_resouceid");
	localStorage.setItem("sync_resouceid",$div.attr("data-id"));
	if ($div.hasClass("addon"))
	{
		$("#mainModalHeader").empty().append("Edit \"" + $div.data("name") + "\"");
		$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
		$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
		$("#mainModalBody").empty();
		LoadPartial("/venue-creator/addon.html","mainModalBody").done(function()
		{
			PopulateDepositList();
			PopulateRefundList();
			PopulateAddonTypes($("#newAddonType")).then(function()
			{
				PopulateAddonData($($div));
				ReBindCreatorControls();
			});
			$("#mainModal").modal("show");
			 
			$("#mainModalAcceptBtn").off("click").click(function(event) 
			{
				event.preventDefault();
				var error = ValidateAddon();
				if (error.length < 1)
				{
					EncodeAddonData($($div));
					$("#mainModalBody").empty();
					$("#mainModal").modal("hide"); 
					
				}
				else
				{
					$("#newAddonError").empty().append("<ul>");
					for (var i = 0; i < error.length; i++)
					{
						$("#newAddonError").append("<li>" + error[i] + "</li>");
					}
					$("#newAddonError").append("</ul>");
					$("#newAddonError").css({"display":"block"});
					$("#mainModal").animate({ scrollTop: 0 });
				}
			});
			$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
		});
	}
	else if ($div.hasClass("deposit"))
	{
		$("#mainModalHeader").empty().append("Edit \"" + $div.data("name") + "\" Policy");
		$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
		$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
		$("#mainModalBody").empty();
		LoadPartial("/venue-creator/deposit.html","mainModalBody").done(function()
		{
			PopulateDepositData($($div));
			$("#mainModal").modal("show");
			 
			$("#mainModalAcceptBtn").off("click").click(function(event) 
			{
				event.preventDefault();
				var error = ValidateDeposit();
				if (error.length < 1)
				{
					EncodeDepositData($($div));
					$("#mainModalBody").empty();
					ReBindCreatorControls();
					$("#mainModal").modal("hide"); 
					
				}
				else
				{
					$("#newDepositError").empty().append("<ul>");
					for (var i = 0; i < error.length; i++)
					{
						$("#newDepositError").append("<li>" + error[i] + "</li>");
					}
					$("#newDepositError").append("</ul>");
					$("#newDepositError").css({"display":"block"});
					$("#mainModal").animate({ scrollTop: 0 });
				}
			});
			$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
		});
	}
	else if ($div.hasClass("refund"))
	{
		$("#mainModalHeader").empty().append("Edit \"" + $div.data("name") + "\" Policy");
		$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
		$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
		$("#mainModalBody").empty();
		LoadPartial("/venue-creator/refund.html","mainModalBody").done(function()
		{
			PopulateRefundData($($div));
			ReBindCreatorControls();
			$("#mainModal").modal("show");
			 
			$("#mainModalAcceptBtn").off("click").click(function(event) 
			{
				var error = ValidateRefund();
				if (error.length < 1)
				{
					EncodeRefundData($($div));
					$("#mainModalBody").empty();
					ReBindCreatorControls();
					$("#mainModal").modal("hide"); 
					
				}
				else
				{
					$("#newRefundError").empty().append("<ul>");
					for (var i = 0; i < error.length; i++)
					{
						$("#newRefundError").append("<li>" + error[i] + "</li>");
					}
					$("#newRefundError").append("</ul>");
					$("#newRefundError").css({"display":"block"});
					$("#mainModal").animate({ scrollTop: 0 });
				}
			});
			$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
		});
	}
	else if ($div.hasClass("space") || $div.hasClass("resource"))
	{
		$("#mainModalHeader").empty().append("Edit \"" + $div.data("name") + "\"");
		$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
		$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
		$("#mainModalBody").empty();
		LoadPartial("/venue-creator/resource.html","mainModalBody").done(function()
		{
			var parent = $div.parents("div.creatorcategory").first().data("name");
			$("#mainModalBody").find(":contains('Included with space'):last").empty().append("Included with <small>\""+parent+"\"</small>");
			if (!parent) $("#mainModalBody").find(":contains('Included with '):last").parent("div").first().hide();
			PopulateDepositList();
			PopulateRefundList();
			PopulateResourceTypes($("#newResourceType")).then(function()
			{
				PopulateResourceData($($div));
				ReBindCreatorControls();
			});
			$("#mainModal").modal("show");
			 
			$("#mainModalAcceptBtn").off("click").click(function(event) 
			{
				event.preventDefault();
				var error = ValidateResource();
				if (error.length < 1)
				{
					EncodeResourceData($($div));
					$("#mainModalBody").empty();
					ReBindCreatorControls();
					$("#mainModal").modal("hide"); 
					
				}
				else
				{
					$("#newResourceError").empty().append("<ul>");
					for (var i = 0; i < error.length; i++)
					{
						$("#newResourceError").append("<li>" + error[i] + "</li>");
					}
					$("#newResourceError").append("</ul>");
					$("#newResourceError").css({"display":"block"});
					$("#mainModal").animate({ scrollTop: 0 });
				}
			});
			$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
		});
	}
	else if ($div.hasClass("menu"))
	{
		$("#mainModalHeader").empty().append("Edit \"" + $div.data("name") + "\"");
		$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
		$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
		$("#mainModalBody").empty();
		LoadPartial("/venue-creator/menu.html","mainModalBody").done(function()
		{
			PopulateDepositList();
			PopulateRefundList();
			PopulateMenuData($($div));
			ReBindCreatorControls();

			$("#mainModal").modal("show");
			 
			$("#mainModalAcceptBtn").off("click").click(function(event)
			{
				event.preventDefault();
				var error = ValidateMenu();
				if (error.length < 1)
				{
					EncodeMenuData($div);
					$("#mainModalBody").empty();
					ReBindCreatorControls();
					$("#mainModal").modal("hide"); 
				}
				else
				{
					var str = "<ul>";

					for (var i = 0; i < error.length; i++)
					{
						str += "<li>" + error[i] + "</li>";
					}
					str += "</ul>";
					$("#newMenuError").append(str);
					$("#newMenuError").css({"display":"block"});
					$("#mainModal").animate({ scrollTop: 0 });
				}
			});
			$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
		});
	}
	else if ($div.hasClass("menuitem"))
	{
		$("#mainModalHeader").empty().append("Edit \"" + $div.data("name") + "\"");
		$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
		$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
		$("#mainModalBody").empty();
		LoadPartial("/venue-creator/menuitem.html","mainModalBody").done(function()
		{
			PopulateMenuItemTypes().then(function()
			{
				PopulateMenuItemData($($div));
				ReBindCreatorControls();
			});
			$("#mainModal").modal("show");
			 
			$("#mainModalAcceptBtn").off("click").click(function(event) 
			{
				event.preventDefault();
				var error = ValidateMenuItem();
				if (error.length < 1)
				{
					EncodeMenuItemData($($div));
					$("#mainModalBody").empty();
					ReBindCreatorControls();
					$("#mainModal").modal("hide"); 
					
				}
				else
				{
					$("#newMenuItemError").empty().append("<ul>");
					for (var i = 0; i < error.length; i++)
					{
						$("#newMenuItemError").append("<li>" + error[i] + "</li>");
					}
					$("#newMenuItemError").append("</ul>");
					$("#newMenuItemError").css({"display":"block"});
					$("#mainModal").animate({ scrollTop: 0 });
				}
			});
			$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
		});
	}
	else if ($div.hasClass("personnel"))
	{
		$("#mainModalHeader").empty().append("Edit \"" + $div.data("name") + "\"");
		$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
		$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
		$("#mainModalBody").empty();
		LoadPartial("/venue-creator/personnel.html","mainModalBody").done(function()
		{
			PopulateDepositList();
			PopulateRefundList();
			$("#selPersonnelResources").append("<option data-id='0'>Any</option>");
			$("div.creatorcategory.resource,div.creatorcategory.space").each(function()
			{
				if ($(this).attr("data-id"))
					$("#selPersonnelResources").append("<option data-id='" + SanitizeAttr($(this).attr("data-id")) + "'>" + $(this).data("name") + "</option>");
			});
			
			PopulatePersonnelData($($div));
			ReBindCreatorControls();
			$("#mainModal").modal("show");
			 
			$("#mainModalAcceptBtn").off("click").click(function(event) 
			{
				event.preventDefault();
				var error = ValidatePersonnel();
				if (error.length < 1)
				{
					EncodePersonnelData($($div));
					$("#mainModalBody").empty();
					ReBindCreatorControls();
					$("#mainModal").modal("hide"); 
					
				}
				else
				{
					$("#newPersonnelError").empty().append("<ul>");
					for (var i = 0; i < error.length; i++)
					{
						$("#newPersonnelError").append("<li>" + error[i] + "</li>");
					}
					$("#newPersonnelError").append("</ul>");
					$("#newPersonnelError").css({"display":"block"});
					$("#mainModal").animate({ scrollTop: 0 });
				}
			});
			$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
		});
	}
}

function ClickCreateDeposit($button)
{
	$("#mainModalHeader").empty().append("Add a Deposit Policy");
	$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
	$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
	$("#mainModalBody").empty();
	LoadPartial("/venue-creator/deposit.html","mainModalBody").done(function()
	{
		$("#mainModal").modal("show");
		 
		$("#mainModalAcceptBtn").off("click").click(function(event) 
		{
			event.preventDefault();
			var error = ValidateDeposit();
			if (error.length < 1)
			{
				InsertDeposit($("#newDepositName").val());
				$("#mainModalBody").empty();
				$("#mainModal").modal("hide"); 
				
			}
			else
			{
				$("#newDepositError").empty().append("<ul>");
				for (var i = 0; i < error.length; i++)
				{
					$("#newDepositError").append("<li>" + error[i] + "</li>");
				}
				$("#newDepositError").append("</ul>");
				$("#newDepositError").css({"display":"block"});
				$("#mainModal").animate({ scrollTop: 0 });
			}
		});
		$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
	});
}

function ClickCreateRefund($button)
{
	$("#mainModalHeader").empty().append("Add a Refund Policy");
	$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
	$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
	$("#mainModalBody").empty();
	LoadPartial("/venue-creator/refund.html","mainModalBody").done(function()
	{
		ReBindCreatorControls();
		$("#mainModal").modal("show");
		 
		$("#mainModalAcceptBtn").off("click").click(function(event) 
		{
			event.preventDefault();
			var error = ValidateRefund();
			if (error.length < 1)
			{
				InsertRefund($("#newRefundName").val());
				$("#mainModalBody").empty();
				$("#mainModal").modal("hide"); 
				
			}
			else
			{
				$("#newRefundError").empty().append("<ul>");
				for (var i = 0; i < error.length; i++)
				{
					$("#newRefundError").append("<li>" + error[i] + "</li>");
				}
				$("#newRefundError").append("</ul>");
				$("#newRefundError").css({"display":"block"});
				$("#mainModal").animate({ scrollTop: 0 });
			}
		});
		$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
	});
}

function ClickCreateAddon($button)
{
	$("#mainModalHeader").empty().append("Create an Addon");
	$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
	$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
	$("#mainModalBody").empty();
	LoadPartial("/venue-creator/addon.html","mainModalBody").done(function()
	{
		PopulateAddonTypes($("#newAddonType"));
		PopulateDepositList();
		PopulateRefundList();
		ReBindCreatorControls();
		$("#mainModal").modal("show");
		 
		$("#mainModalAcceptBtn").off("click").click(function(event) 
		{
			event.preventDefault();
			var error = ValidateAddon();
			if (error.length < 1)
			{
				InsertAddon($("#newAddonName").val());
				$("#mainModalBody").empty();
				$("#mainModal").modal("hide"); 
				
			}
			else
			{
				$("#newAddonError").empty().append("<ul>");
				for (var i = 0; i < error.length; i++)
				{
					$("#newAddonError").append("<li>" + error[i] + "</li>");
				}
				$("#newAddonError").append("</ul>");
				$("#newAddonError").css({"display":"block"});
				$("#mainModal").animate({ scrollTop: 0 });
			}
		});
		$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
	});
}

function ClickAddResource($button)
{
	var $div = $button.parents("div.creatorcategory").first();
	
	$("#mainModalHeader").empty().append("Add a resource");
	$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
	$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
	$("#mainModalBody").empty();
	LoadPartial("/venue-creator/resource.html","mainModalBody").done(function()
	{
		var parent = $div.data("name");
		$("#mainModalBody").find(":contains('Included with space'):last").empty().append("Included with <small>\""+parent+"\"</small>");
		if (!parent) $("#mainModalBody").find(":contains('Included with '):last").parent("div").first().hide();
		PopulateDepositList();
		PopulateRefundList();
		PopulateAddonList();
		
		$("#fgHours .timeslot-widget").tsWidget("restore",$div.data("data-hours"));
		$("#fgTimeslots .timeslot-widget").tsWidget("restore",$div.data("data-slots"));
		
		if ($div.attr("id") == "addvenuespace")
		{
			$("#newResourceName").val($("#venueName").val() + " (Entire Venue)");
			$("#newResourceDescription").val($("#venueDescription").val());
			$("#newResourceType").append($("<option>",{value:"1",text:"space"}));
		}
		else PopulateResourceTypes($("#newResourceType"));
		ReBindCreatorControls();
		$("#mainModal").modal("show");
		 
		$("#mainModalAcceptBtn").off("click").click(function(event) 
		{
			event.preventDefault();
			var error = ValidateResource();
			if (error.length < 1)
			{
				var $div2 = CreateResource($("#newResourceName").val(),$("#newResourceType").val());
				if ($div.attr("id") == "addvenuespace")
				{
					$div.after($($div2));
				}
				else 
				{
					if ($div2.hasClass("space"))
						$div.find("div.addnewspace").last().before($($div2));
					else $div.find("div.clearfix").first().before($($div2));
				}
				$("#addvenuespace").remove();
				$("#mainModalBody").empty();
				ReBindCreatorControls();
				$("#mainModal").modal("hide"); 
				
			}
			else
			{
				$("#newResourceError").empty().append("<ul>");
				for (var i = 0; i < error.length; i++)
				{
					$("#newResourceError").append("<li>" + error[i] + "</li>");
				}
				$("#newResourceError").append("</ul>");
				$("#newResourceError").css({"display":"block"});
				$("#mainModal").animate({ scrollTop: 0 });
			}
		});
		$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
	});
}

function ClickAddMenu($button)
{
	$("#mainModalHeader").empty().append("Add New Menu");
	$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
	$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
	$("#mainModalBody").empty();
	LoadPartial("/venue-creator/menu.html","mainModalBody").done(function()
	{
		PopulateDepositList();
		PopulateRefundList();
		ReBindCreatorControls();
		$("#mainModal").modal("show");
		 
		$("#mainModalAcceptBtn").off("click").click(function(event) 
		{
			event.preventDefault();
			var error = ValidateMenu();
			if (error.length < 1)
			{
				$div = CreateMenu($("#newMenuName").val());
				$("div.menu_table").append($($div));
				$("#mainModalBody").empty();
				ReBindCreatorControls();
				$("#mainModal").modal("hide"); 
				
			}
			else
			{
				$("#newMenuError").empty().append("<ul>");
				for (var i = 0; i < error.length; i++)
				{
					$("#newMenuError").append("<li>" + error[i] + "</li>");
				}
				$("#newMenuError").append("</ul>");
				$("#newMenuError").css({"display":"block"});
				$("#mainModal").animate({ scrollTop: 0 });
			}
		});
		$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
	});
}

function ClickAddMenuItem($button)
{
	$("#mainModalHeader").empty().append("Add menu item");
	$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
	$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
	$("#mainModalBody").empty();
	LoadPartial("/venue-creator/menuitem.html","mainModalBody").done(function()
	{
		PopulateMenuItemTypes();
		ReBindCreatorControls();
		$("#mainModal").modal("show");
		 
		$("#mainModalAcceptBtn").off("click").click(function(event) 
		{
			event.preventDefault();
			var error = ValidateMenuItem();
			if (error.length < 1)
			{
				$div = CreateMenuItem($("#newMenuItemName").val());
				$button.after($($div));
				$("#mainModalBody").empty();
				ReBindCreatorControls();
				$("#mainModal").modal("hide"); 
				
			}
			else
			{
				$("#newMenuItemError").empty().append("<ul>");
				for (var i = 0; i < error.length; i++)
				{
					$("#newMenuItemError").append("<li>" + error[i] + "</li>");
				}
				$("#newMenuItemError").append("</ul>");
				$("#newMenuItemError").css({"display":"block"});
				$("#mainModal").animate({ scrollTop: 0 });
			}
		});
		$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
	});
}

function ClickAddPersonnel($button)
{
	$("#mainModalHeader").empty().append("Add New Personnel");
	$("#mainModalAcceptBtn").empty().append("OK").css({"display":"inline"});
	$("#mainModalCloseBtn").empty().append("Cancel").css({"display":"inline"});
	$("#mainModalBody").empty();
	LoadPartial("/venue-creator/personnel.html","mainModalBody").done(function()
	{
		PopulateDepositList();
		PopulateRefundList();
		$("#selPersonnelResources option").remove();
		$("#selPersonnelResources").append("<option data-id='0'>Any</option>");
		$("div.creatorcategory.resource,div.creatorcategory.space").each(function()
		{
			if ($(this).attr("data-id"))
				$("#selPersonnelResources").append("<option data-id='" + SanitizeAttr($(this).attr("data-id")) + "'>" + $(this).data("name") + "</option>");
		});
		
		ReBindCreatorControls();
		$("#mainModal").modal("show");
		 
		$("#mainModalAcceptBtn").off("click").click(function(event) 
		{
			event.preventDefault();
			var error = ValidatePersonnel();
			if (error.length < 1)
			{
				$div = CreatePersonnel($("#newPersonnelName").val());
				$("div.personnel_table").append($($div));
				$("#mainModalBody").empty();
				ReBindCreatorControls();
				$("#mainModal").modal("hide"); 
				
			}
			else
			{
				$("#newPersonnelError").empty().append("<ul>");
				for (var i = 0; i < error.length; i++)
				{
					$("#newPersonnelError").append("<li>" + error[i] + "</li>");
				}
				$("#newPersonnelError").append("</ul>");
				$("#newPersonnelError").css({"display":"block"});
				$("#mainModal").animate({ scrollTop: 0 });
			}
		});
		$("#mainModalCloseBtn").off("click").click(function(event){$("#mainModalBody").empty();$("#mainModal").modal("hide");});
	});
}