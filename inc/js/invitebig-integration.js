jQuery(function() 
{
	jQuery("a.InviteBIG_Button").each(function()
	{					
		jQuery(this).off("click").on('click',function(event)
		{
			event.preventDefault();
			jQuery(this).after("<div id='InviteBIG_IFrame_Overlay' style='z-index:9998;position:fixed;display:block;top:0;bottom:0;left:0;right:0;background-color:rgba(0,0,0,0.6)'><div id='InviteBIG_IFrame_Popup' style='width:80%;min-width:765px;height:97%;z-index:9999;position:relative;margin:10px auto;display:block'><iframe id='InviteBIG_IFrame' src='" + jQuery(this).attr("href") + "' style='width:100%;height:100%;border: 1px solid rgba(193, 200, 202, 0.6);border-radius: 5px;-webkit-box-shadow: 0 0px 13px rgba(0, 0, 0, 0.1);-moz-box-shadow: 0 0px 13px rgba(0, 0, 0, 0.1);box-shadow: 0 0px 13px rgba(0, 0, 0, 0.1)'></iframe></div></div>");
			//jQuery("#InviteBIG_IFrame").load(function(){ jQuery(this).contents().find("body").css("overflow","hidden"); });
			//var timeout = setInterval(function(){jQuery('#InviteBIG_IFrame').each(function(){ jQuery(this).parents("div").first().height(jQuery(this).contents().outerHeight()); jQuery(this).parents("div").first().width(jQuery(this).contents().outerWidth() + 2); });},1000);
			
			jQuery("#InviteBIG_IFrame_Overlay").off("click").click(function(event)
			{
				jQuery("#InviteBIG_IFrame_Popup").remove();
				jQuery(this).remove();
				//clearTimeout(timeout);
			});
		});
	});
});