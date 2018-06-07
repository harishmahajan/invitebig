<?php require_once("php/core.php");?>

<!DOCTYPE HTML>
<html lang="en">
<head>
	
	<meta name="viewport" content="width=<?php if (strpos($_SERVER['REQUEST_URI'],"/print") > 0) echo "1000"; else echo "device-width, initial-scale=1.0"; ?>">
	<meta charset="UTF-8">
	
	<!-- <link href='https://fonts.googleapis.com/css?family=Roboto:400,700,400italic' rel='stylesheet' type='text/css'>
	<link href="/assets/font-awesome-4.3.0/css/font-awesome.min.css?_=1455331660" rel="stylesheet" type="text/css">
	<link href="/assets/css/bootstrap.min.css?_=1455331660" rel="stylesheet" type="text/css">
	<link href="/assets/css/style.css?_=1455331660" rel="stylesheet" type="text/css">
	<link href="/assets/css/style-responsive.css?_=1455331660" rel="stylesheet" type="text/css">
	<link href="/assets/smoothness/jquery-ui-1-cleaned.css?_=1455331660" rel="stylesheet" type="text/css">
	<link href="/inc/jQuery-File-Upload-master/css/all.css?_=1455331660" rel="stylesheet" type="text/css">
	<link href="/inc/slick-1.5.9/my-slick.css?_=1455331660" rel="stylesheet" type="text/css">
	
	<link href='https://fonts.googleapis.com/css?family=Open+Sans:300,600,700' rel='stylesheet' type='text/css'>
	
	<script src="/inc/js/jquery2.min.js?_=1455331660" type="text/javascript"></script>
	<script src="/inc/js/moment+tz.min.js?_=1455331660" type="text/javascript"></script>
	<script src="/inc/js/invitebig.js?_=1455331660" type="text/javascript"></script>
	<script src="/inc/js/jquery-ui-partial.min.js?_=1455331660" type="text/javascript"></script>
	<script src="/inc/js/bootstrap.min.js?_=1455331660" type="text/javascript"></script>
	<script src="/inc/js/sumoselect.js?_=1455331660" type="text/javascript"></script>
	<script src="/inc/slick-1.5.9/slick.min.js?_=1455331660" type="text/javascript"></script>
	 -->
	<!-- /reserve only -->
	<!-- <link href="/assets/css/bootstrap-nav-wizard.css?_=1455331660" rel="stylesheet" type="text/css">
	 -->

	 	<link href='https://fonts.googleapis.com/css?family=Roboto:400,700,400italic' rel='stylesheet' type='text/css'>
	<link href="/assets/font-awesome-4.3.0/css/font-awesome.min.css?_=1455331660" rel="stylesheet" type="text/css">
	<link href="/assets/css/bootstrap.min.css?_=1455331660" rel="stylesheet" type="text/css">
	<link href="/assets/css/style.css?_=1455331660" rel="stylesheet" type="text/css">
	<link href="/assets/css/style-responsive.css?_=1455331660" rel="stylesheet" type="text/css">
	<link href="/assets/smoothness/jquery-ui-1-cleaned.css?_=1455331660" rel="stylesheet" type="text/css">
	<link href="/inc/jQuery-File-Upload-master/css/all.css?_=1455331660" rel="stylesheet" type="text/css">
	<link href="/inc/slick-1.5.9/my-slick.css?_=1455331660" rel="stylesheet" type="text/css">
	
	<link href='https://fonts.googleapis.com/css?family=Open+Sans:300,600,700' rel='stylesheet' type='text/css'>
	
	<script src="/inc/js/jquery2.min.js?_=1455331660" type="text/javascript"></script>
	<script src="/inc/js/moment+tz.min.js?_=1455331660" type="text/javascript"></script>
	<script src="/inc/js/invitebig.js?_=1455331660" type="text/javascript"></script>
	<script src="/inc/js/jquery-ui-partial.min.js?_=1455331660" type="text/javascript"></script>
	<script src="/inc/js/bootstrap.min.js?_=1455331660" type="text/javascript"></script>
	<script src="/inc/js/sumoselect.js?_=1455331660" type="text/javascript"></script>
	<script src="/inc/slick-1.5.9/slick.min.js?_=1455331660" type="text/javascript"></script>
	
	<!-- /reserve only -->
	<link href="/assets/css/bootstrap-nav-wizard.css?_=1455331660" rel="stylesheet" type="text/css">
	
	<script>
	    // alert("1");
		localStorage.setItem("auth","<?php echo $_SESSION['auth']; ?>");
		localStorage.setItem("email","<?php echo (isset($_SESSION['email'])?$_SESSION['email']:''); ?>");
		localStorage.setItem("firstname","<?php echo (isset($_SESSION['firstname'])?$_SESSION['firstname']:'') ?>");
		localStorage.setItem("lastname","<?php echo (isset($_SESSION['lastname'])?$_SESSION['lastname']:'') ?>");
		localStorage.setItem("phone","<?php echo (isset($_SESSION['phone'])?$_SESSION['phone']:'') ?>");
		localStorage.setItem("birthdate","<?php echo (isset($_SESSION['phone'])?$_SESSION['birthdate']:'') ?>");
		localStorage.setItem("timezone","<?php echo (isset($_SESSION['phone'])?$_SESSION['timezone']:'') ?>");
		localStorage.setItem("venueRights","<?php echo (isset($_SESSION['venueRights'])?str_replace('"','\"',json_encode($_SESSION['venueRights'])):''); ?>");
		localStorage.setItem("cacheTimestamp","?_=1455331660");
		
		var resizeQueue = [];
		$(document).ajaxStart(TriggerAjaxLoad);
		$(document).ajaxStop(TriggerAjaxUnload);
		
		setInterval(function(){if (jQuery.active == 0) $("#ajaxOverlay").hide();}, 2000);
		
		console.log("LOADED INDEX",(moment().format('x')));
		if (window.location.href.indexOf('?iframe=true') > 0)
			sessionStorage.setItem("iframe","true");
		else sessionStorage.removeItem("iframe");
		
		if (window.location.href.indexOf('&displayHeader=true') > 0)
		{
			sessionStorage.setItem("displayHeader","true");
		}
		else 
		{
			sessionStorage.setItem("displayHeader","false");
			//sessionStorage.removeItem("displayHeader");
		}
		//sessionStorage.setItem("flag","true");			
		// $(document).ready(function(){
		// 	$.ajax({
		// 	type: "GET",
		// 	url: "iframeheaderloads.php",
		// 	success: function(output){
		// 		var value = $.trim(output);
		// 		//console.log(value);
					
		// 		if(value=='show')
		// 		{
		// 			if (window.location.href.indexOf('&displayHeader=true') > 0)
		// 				sessionStorage.setItem("displayHeader","true");
		// 			else sessionStorage.removeItem("displayHeader");
		// 		}
		//     	else
		//     	{
		// 				if (window.location.href.indexOf('&displayHeader=false') > 0)
		// 						sessionStorage.setItem("displayHeader","false");
		// 				else sessionStorage.removeItem("displayHeader");		    		
		// 	    }
		// 		}
		// 	});	

		// }); 


		
	

		$(function()
		{
			LoadPartial(window.location.pathname,null,false);
			
			setInterval(responsiveView,200);
			setInterval(AuthPing,1800000);
		});
		
		$(window).on('load', qResize);
		$(window).on('resize', qResize);
		$(document).on('click',ClickHandler);
		
		var blockPopstateEvent = document.readyState!="complete";
		window.addEventListener("load", function() {
			// The timeout ensures that popstate-events will be unblocked right
			// after the load event occured, but not in the same event-loop cycle.
			setTimeout(function(){ blockPopstateEvent = false; }, 0);
		}, false);
		window.addEventListener("popstate", function(evt) {
			if (blockPopstateEvent && document.readyState=="complete") {
				evt.preventDefault();
				evt.stopImmediatePropagation();
			}
		}, false);
		
		$(window).on('popstate', function(event) {
			LoadPartial(window.location.pathname,null,false);
		});
		
		// this makes script tags that are injected by LoadPartial() load from cache
		$.ajaxPrefilter(function( options, originalOptions, jqXHR )
		{
			if ( options.dataType == 'script' || originalOptions.dataType == 'script' ) {
				options.cache = true;
			}
		});
		
		window.onbeforeunload = BeforeLeaving;
    </script>
	
	<?php 
	   	// if($_SERVER['QUERY_STRING']) {
	   	// 	echo $_SESSION['token'];
	   	// 	exit();
	   	// 	if(isset($_SESSION['myrequest'])) {
	   	// 	//if($_SESSION['myrequest']) {
		   // 		//session_start();
		   // 		print_r($_SESSION['myrequest']);

	    // 		// echo "test";
	    // 		exit();
	    // 	}
     //    }
		if ($_SERVER['SERVER_NAME'] == "www.invitebig.com") echo '
	<script type="text/javascript">
		window.heap=window.heap||[],heap.load=function(t,e){window.heap.appid=t,window.heap.config=e;var a=document.createElement("script");a.type="text/javascript",a.async=!0,a.src=("https:"===document.location.protocol?"https:":"https:")+"//cdn.heapanalytics.com/js/heap-"+t+".js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(a,n);for(var o=function(t){return function(){heap.push([t].concat(Array.prototype.slice.call(arguments,0)))}},p=["clearEventProperties","identify","setEventProperties","track","unsetEventProperty"],c=0;c<p.length;c++)heap[p[c]]=o(p[c])};
		heap.load("500633542");
    </script>
	
	<script type="text/javascript">
		window.__wtw_lucky_site_id = 50688;

		(function() {
		var wa = document.createElement("script"); wa.type = "text/javascript"; wa.async = true;
		wa.src = ("https:" == document.location.protocol ? "https://ssl" : "http://cdn") + ".luckyorange.com/w.js";
		var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(wa, s);
		})();
	</script>
	';
	?>
	
	<script
		src="//d2wy8f7a9ursnm.cloudfront.net/bugsnag-2.min.js"
		data-apikey="a5ea0b735b80dad57748f0dfe4c75e40">
	</script>

	<!-- <script src="//cdn.optimizely.com/js/3209371425.js"></script> -->
	
	<!--[if lt IE 9]>
	<script src="https://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	
</head>
<body>
	<?php 
		if ($_SERVER['SERVER_NAME'] == "www.invitebig.com") echo '
	<!-- Google Tag Manager -->
	<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-W5KTVM"
	height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({"gtm.start":
	new Date().getTime(),event:"gtm.js"});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!="dataLayer"?"&l="+l:"";j.async=true;j.src=
	"//www.googletagmanager.com/gtm.js?id="+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,"script","dataLayer","GTM-W5KTVM");</script>
	<!-- End Google Tag Manager -->
	';
	?>
	
	<?php 
		if ($_SERVER['SERVER_NAME'] == "www.invitebig.com") echo '
		<!-- Begin Inspectlet Embed Code -->
		<script type="text/javascript" id="inspectletjs">
		window.__insp = window.__insp || [];
		__insp.push(["wid", 762194073]);
		(function() {
		function ldinsp(){if(typeof window.__inspld != "undefined") return; window.__inspld = 1; var insp = document.createElement("script"); insp.type = "text/javascript"; insp.async = true; insp.id = "inspsync"; insp.src = ("https:" == document.location.protocol ? "https" : "http") + "://cdn.inspectlet.com/inspectlet.js"; var x = document.getElementsByTagName("script")[0]; x.parentNode.insertBefore(insp, x); };
		setTimeout(ldinsp, 500); document.readyState != "complete" ? (window.attachEvent ? window.attachEvent("onload", ldinsp) : window.addEventListener("load", ldinsp, false)) : ldinsp();
		})();
		</script>
		<!-- End Inspectlet Embed Code -->
		';
	?>
	
	<div class='mainoverlay' id='mainOverlay'></div>
	<div class='ajaxOverlayInvis' id='ajaxOverlayInvis'></div>
	<div class='ajaxOverlay' id='ajaxOverlay'><div class='loadingimage'></div></div>
	
	<div class='modal in' id='mainModal' data-keyboard='true' data-backdrop='static'>
		<div class='modal-dialog'>
			<div class='modal-content'>
				<div class='modal-header' style='margin:5px;padding:0 15px 0 15px'>
					<button type='button' class='close' data-dismiss='modal' aria-label='Close' style='margin:5px 0 0 15px'><span aria-hidden='true'>&times;</span></button>
					<div id='mainModalHeader'></div>
				</div>
				<div class='modal-body' id='mainModalBody'></div>
				<div class='modal-footer' id='mainModalFooter'>
					<a href='#' class='btn btn-success' id='mainModalAcceptBtn' ></a>
					<a href='#' class='btn btn-default' id='mainModalCloseBtn' data-dismiss='modal'></a>
				</div>
			</div>
		</div>
	</div>
	<script>
		if (typeof BugSnag !== "undefined") 
			Bugsnag.releaseStage = "production";
		
		$("#mainModalAcceptBtn").click(function(event) { $("#mainModal").modal("hide"); });
		$("#mainModalClose").click(function(event) { $("#mainModal").modal("hide"); });
		$("#mainModalCloseBtn").click(function(event) { $("#mainModal").modal("hide"); });
		
	</script>	
	
	<div id='header' style='display:none'>
		<div class='navbar navbar-static-top' role='navigation'>
			<div class='navbar-header' style='width:100%'>
				<a id='imgLogo' href='/'><img class='logo-navbar' src='/assets/img/Invite_BIG_Final_Logo_01_sm.png' alt='InviteBIG'></a>
				<ul class='nav navbar-nav navbar-right pull-right' style='margin-right:10px'>
					<?php 

					
						if (isset($_SESSION['booking']) && $URL_ARGS[0] != "book") 
							echo "<li class='navbutton'><a class='savedbooking' data-partial=true href='/reserve/book-an-event-at-".$_SESSION['booking']['url']."'>Return To<br>Booking</a></li>";
						
						if (isset($_SESSION['email']) && ($_SESSION['siteRole'] > 1 || count($_SESSION['venueRights']) > 0))
							echo "";
						//else echo "<li class='navbutton'><a data-partial=true href='/venues'><i class='fa fa-search fa-2'></i><p>Find Venues</p><div class='clearfix'></div></a></li>";
										
						if (isset($_SESSION['email'])) 
						{
							echo "<li class='dropdown'>
									<a href='#' class='dropdown-toggle' data-toggle='dropdown'>
										<div class='profileChoice' data-account='me'><img class='miniProfilePic' src='".$_SESSION['profilePic']."'><p>".$_SESSION['firstname']." ".$_SESSION['lastname']."<b class='caret'></b></div></p>
									</a>							
									<ul id='headerUser' class='dropdown-menu'>
										<li><a data-partial=true href='/dashboard'>Go To Dashboard</a></li>";
							if ($_SESSION['siteRole'] > 1)
								echo
										"<li class='divider'></li>
										<li><a data-partial=true href='/create-venue'>Create New Venue</a></li>";
							echo
										"<!-- <li><a data-partial=true href='/events'>Search for events</a></li>
										<li class='divider'></li>
										<li><a data-partial=true href='/create_event'>Create New Event</a></li> -->
										<li class='divider'></li>
										<li><a data-partial=true href='/logout'>Logout</a></li>
									</ul>
								</li>";
							if ($_SESSION['siteRole'] >= 999)
								echo "<li class='navbutton'><a href='/admin'><i class='fa fa-cogs fa-2'></i><p>Admin</p><div class='clearfix'></div></a></li>";
						} else {
							echo "<li><a href='/login'><span class='login'>LOG IN</span></a>";
						}
					?>
				</ul>
			</div>
		</div>
	</div>
	<div class='clearfix'></div>
	
	<div id='bodyWrapper'>
		<div id='headerPad'></div>
		<div class='clearfix'></div>

		<noscript>
			<div class='alert alert-block col-md-11'>
				<div class='alert-heading'>Warning!</div>
				<p>You need to have <a href='https://en.wikipedia.org/wiki/JavaScript' target='_blank'>JavaScript</a> enabled to use this site.</p>
			</div>
		</noscript>

		<!--[if lt IE 10]>
			<div class='alert alert-block alert-info text-center' style='margin:20px'>You are using an <strong>outdated</strong> browser that is not compatible with InviteBIG. Please <strong><a href="https://browsehappy.com/">upgrade your browser</a></strong> to improve your experience.</div>
		<![endif]-->

		<div class='clearfix'></div>
		<div id='bodyContent'></div>
		<div class='clearfix'></div>
		<div id='footerPad'></div>
		<div class='clearfix'></div>
	</div>
	
	<div id='footer' style='display:none'>
		<div class='footerlinkpan'>
			<div class='footer-spacer'></div>
			<ul>
				<li><a data-partial=true href='/terms'>Terms of Service</a></li>
				<li><a data-partial=true href='/privacy'>Privacy Policy</a></li>
				<li><a data-partial=true href='/help'>Contact</a></li>
			</ul>
		</div>
		<div class='copyrightpan'>
			<div class='footer-spacer'></div>
			Copyright 2013-2016 InviteBig, Inc. All right reserved.
		</div>
	</div>
	<div class='clearfix'></div>

	<script>
		if (window.location.href.indexOf("?iframe=true") < 0){
			$("#header").show();
			$("#headerPad").show();
			$("#footer").show();
			$("#footerPad").show();
		}
		
		$(function(){
			setTimeout(WarmCache,5000);
		});

		function reloadpageonApprove() {
		  //  location.reload();
		}
	</script>
	<?php //date_default_timezone_set("America/Los_Angeles"); ?>
</body>
</html>
