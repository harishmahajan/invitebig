<?php require_once("../php/core.php");?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black" />
        
        <link rel="stylesheet" href="/new_pages/startup/flat-ui/bootstrap/css/bootstrap.css">
        <!-- Using only with Flat-UI (free)-->
        <link rel="stylesheet" href="/new_pages/startup/common-files/css/icon-font.css">
        <!-- end -->
        <link rel="stylesheet" href="/new_pages/static/css/style.css">        
        
        <title>Contact Us | InviteBIG</title>
		<meta name="description" content="Use this form to contact us with any questions you have."/>
		
		<?php 
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
	
	
        <div class="page-wrapper bg-gray">
            <header class="header-17">
                <div class="container">
                    <div class="row">
                        <div class="navbar col-sm-12" role="navigation">
                            <div class="navbar-header">
                                <button type="button" class="navbar-toggle"></button>
                                <a class="brand" href="/">
                                    <img src="/new_pages/startup/common-files/img/header/logo.png" height="50" alt="">
                                </a>
                            </div>
                            <div class="collapse navbar-collapse pull-left">
                                <ul class="nav pull-left">
                                    <li><a href="/">HOME</a></li>
                                    <!--<li><a href="features.html">FEATURES</a></li>-->
                                    <li><a href="/pricing">PRICING</a></li>
                                    <li><a href="/contact">CONTACT</a></li>
                                </ul>
                                
                            </div>
                            <form id="nav-global" class="navbar-form pull-right">
								<p>
								<?php 
									if (isset($_SESSION['userid']))
										echo '<a href="/dashboard">GO TO DASHBOARD</a>';
									else echo '<a id="signin" href="/login">SIGN IN</a> &nbsp;&nbsp;&nbsp; <a href="/register/venue">START YOUR FREE TRIAL</a>';
								?>
								</p>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="header-background"></div>
            </header>
            <br /><br /><br />

            <section class="contact-text text-center">
                <div class="container">
                    <div class="row center-text">
                        <div class="col-sm-10 col-sm-offset-1">
                            <h2>Contact Us</h2>
                            <p class="lead muted">Reach out with any question, suggestion or concern.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="contacts-3">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-5">
                            <h3>Get In Touch</h3>
                            <p>We appreciate helping our customers and are happy to provide a variety of ways for you to reach us.</p>
                            <div class="links">
                                <a href="tel:(206) 747-8675"><span class="fui-phone"></span> +1 206 747 8675 (9AM - 5PM PST)</a><br>
                                <a href="mailto:support@invitebig.com"><span class="fui-mail"></span> SUPPORT@INVITEBIG.COM</a>
                            </div>
                            <h3>Our Home</h3>
                            <p>InviteBIG is proudly based in Seattle, Washington. </p>
                            <div class="map">
                                <!--map-->
                              <iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
                                        src="https://maps.google.com/?ie=UTF8&amp;t=m&amp;ll=47.61,-122.34&amp;spn=0.04554,0.072956&amp;z=7&amp;output=embed"></iframe>
                            </div>
                        </div>
                        <div class="col-sm-6 col-sm-offset-1">
                            <h3>Send A Message</h3>
                            <p>Please message us if you have any questions, feedback or need help. We will respond as soon as possible.</p>
                            <form>
                              <label class="h6">Name / Last Name</label>
                                <input id='contactName' type="text" class="form-control">
                                <label class="h6">E-mail</label>
                                <input id='contactEmail' type="text" class="form-control">
                                <label class="h6">Message</label>
                                <textarea id='contactMessage' rows="7" class="form-control"></textarea>
                                <button id='contactSubmit' type="submit" class="btn btn-primary"><span class="fui-mail"></span></button>
                            </form>
                        </div>
                    </div>
              </div>
            </section>
            <section id="form-signup-cta">
                <div class="container">
                    <div class="row">
                       <div class="col-sm-10 col-sm-offset-1 text-center">
                         <h2>Start Your 15 Day Free Trial</h2>
                         <p class="lead">Setup only takes a minute. Cancel anytime. No Credit Card Required.</p>
                         <a class="btn btn-clear btn-huge btn-primary" href="/register/venue">Start Your Free Trial</a>
                         <!-- <div class="signup-form">
                            <form>
                                <div class="clearfix">
                                    <input type="text" class="form-control" placeholder="Full Name">
                                    <input type="password" class="form-control" placeholder="Email">
                                    <input type="password" class="form-control" placeholder="Password">
                                    <button type="submit" class="btn btn-primary">Get Started</button>
                                </div>
                            </form>
                        </div> -->
                    </div>
                </div>
            </section>

            <footer class="footer-6 bg-white">
                <div class="container">
                    <div class="row">
                       
                        <nav>
                            <div class=" col-sm-offset-1 col-sm-2">
                                <h6>About</h6>
                                <ul>
                                    <li><a href="#">Blog (coming soon)</a></li>
                                    <li><a href="/contact">Contact</a></li>
                                </ul>
                            </div>
                            <div class="col-sm-2">
                                <h6>Product</h6>
                                <ul>
                                    <li><a href="/pricing">Pricing</a></li>
                                    <li><a href="/pricing#pricing-faq">FAQ</a></li>
                                </ul>
                            </div>
                            <div class="col-sm-2">
                                <h6>Legal Stuff</h6>
                                <ul>
                                    <li><a href="/privacy">Privacy</a></li>
                                    <li><a href="/terms">Terms of Service</a></li>
                                </ul>
                            </div>
                        </nav>
                      <div class="col-sm-4">
                            <h6>Subscribe</h6>
                            <p> Signup for our newsletter to learn about new features and useful tips for your venue.</p>
                            <p><a href="https://invitebig.wufoo.com/forms/subscribe-to-newsletter/" target="_blank">Signup for Newsletter</a></p>
                            <div>
                       	    <p>© InviteBIG, Inc.</p>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
              </div>
            </footer> 

            <!-- Placed at the end of the document so the pages load faster -->
			<script src="/inc/js/jquery2.min.js?_=1455331660" type="text/javascript"></script>
			<script src="/inc/js/moment+tz.min.js?_=1455331660" type="text/javascript"></script>
			<script src="/inc/js/invitebig.js?_=1455331660" type="text/javascript"></script>
            <script src="/new_pages/startup/flat-ui/js/bootstrap.min.js"></script>
            <script src="/new_pages/startup/common-files/js/startup-kit.js"></script>

			<script>
				localStorage.setItem("auth","<?php echo $_SESSION['auth']; ?>");
				
				function ValidateContactForm()
				{
					$("form div.alert").remove();
					$errString = "<div class='alert alert-danger'><b>Errors:</b><br><ul>";
					
					if ($("#contactEmail").val().length < 8 || $("#contactEmail").val().indexOf("@") < 0)
						$errString += "<li>Invalid email address</li>";
					
					if ($("#contactName").val().length < 3)
						$errString += "<li>Please provide your name</li>";
					
					if ($("#contactMessage").val().length < 3)
						$errString += "<li>Invalid message</li>";
					
					$errString += "</ul></div>";
					
					if ($errString.length > 70)
					{
						$("#contactSubmit").after($errString);
						//$("html, body").animate({ scrollTop: 0 });
						return false;
					}
					else return true;
				}

				$("#contactSubmit").on("click",function(event)
				{
					event.preventDefault();
					if (ValidateContactForm())
					{						
						var data = {
							method: 'fContact',
							email: $("#contactEmail").val(),
							subject: $("#contactName").val() + "'s Inquiry",
							message: $("#contactMessage").val()
						};
						Post(data).then(function($data)
						{
							if ($data['result'] == "success") 
							{
								if (typeof heap !== "undefined")
								{
									heap.identify({
										email: $("#contactEmail").val()
									});
								}
								
								if (typeof BugSnag !== "undefined")
								{
									BugSnag.user = {
										email: $("#contactEmail").val()
									};
								}
								
								if (typeof __insp !== "undefined")
								{
									__insp.push(['identify', $("#contactEmail").val()]);
								}
								
								if (typeof window.__wtw_custom_user_data === "undefined")
									window.__wtw_custom_user_data = {};
								window.__wtw_custom_user_data.email = $("#contactEmail").val();
								
								var d = $("form").parents("div").first();
								d.empty().append("<h3>Message Sent!</h3><p>Your message has been sent to the InviteBIG support staff, we will respond as quickly as we can.  Thank you for contacting InviteBIG.</p>");
								$("html, body").animate({ scrollTop: 0 });
							} else 
							{
								$("#contactSubmit").after("<div class='alert alert-danger'>" + $data['result'] + "</div>");
							}				
							
							//$("html, body").animate({ scrollTop: 0 });
						});
					}
				});
			</script>
        </div>
    </body>
</html>
