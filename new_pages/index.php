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
        <link rel="stylesheet" href="/new_pages/static/css/style.css">        
        <title>Event and Venue Management Software | InviteBIG</title>
		<meta name="description" content="Venue management software. Event management software. InviteBIG provides a suite of online tools for venues to achieve a state of organized Zen."/>
		<script src="/inc/js/invitebig-dashboard.js?_=1455331660" type="text/javascript"></script>
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
        <div class="page-wrapper">
          

            <section class="header-17-sub text-center bg-image">
                <div class="background">&nbsp;</div>
            	    <div class="container">
            	        <div class="row">
            	            <div class="col-xs-offset-1 col-sm-11 bg-height">
            	                <div class="hero-unit">
                                    <h1>End-To-End Event Booking For Your Venue</h1>
                                    <p class="lead">Let customers book your venue online and your team manage scheduling, bookkeeping and communications all in one place.</p>
                                    <br />
                                    <a class="btn btn-clear btn-huge btn-primary" href="/register/venue">Start Your Free Trial</a>
                                    <br />
                                    <a class="hero-link" href="https://invitebig.appointlet.com/" target="_blank">Or Request A Demo</a>
            	                </div>
            	            </div>
            	        </div>
                    </div>
                </div>
            </section>

            <section id="home-features-customer" class="home-features bg-gray">
                <div class="container">
                    
                    <h2 class="textcenter">For Your Customers</h2>
                    <p class="textcenter lead">Delight your customers by removing unnecessary obstacles to booking your venue.</p>

                    <div class="img textcenter">
                        <img src="/new_pages/startup/common-files/img/screenshots/home_calendar.png" alt="event booking process">
                    </div>

                    <br />
                    <br />
                    <br />

                    <div class="row clearfix">
                        <div class="col-sm-4 feature-item">
                            <div class="feature-img">
                                <img src="/new_pages/startup/common-files/icons/icon_blue_1.png" alt="" />
                            </div>
                            <div class="feature-text">
                                <h4>Self-Serve Booking</h4>
                                <p>Allow your customers to book your venue online, through your website, 24/7.</p>
                            </div>
                        </div>
                        <div class="col-sm-4 feature-item">
                            <div class="feature-img">
                                <img src="/new_pages/startup/common-files/icons/icon_blue_2.png" alt="" />
                            </div>
                            <div class="feature-text">
                                <h4>Easy Online Payments</h4>
                                <p>Simple, secure, online payments using credit or debit cards and automated payment reminders.</p>
                            </div>
                        </div>
                        <div class="col-sm-4 feature-item">
                            <div class="feature-img">
                                <img src="/new_pages/startup/common-files/icons/icon_blue_3.png" alt="" />
                            </div>
                            <div class="feature-text">
                                <h4>Book Everything Online</h4>
                                <p>Customers can book multiple rooms, amentities and request services like catering for their events, online.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="home-features-team" class="home-features">
                <div class="container">
                    
                    <h2 class="textcenter">For Your Team</h2>
                    <p class="textcenter lead">Everything you need to keep track of bookings, sales, and communications without dropping the ball.</p>

                    <div class="img textcenter">
                        <img src="/new_pages/startup/common-files/img/content/features_payments.jpg" width="780" height="418" alt="">
                    </div>
                    <br />
                    <br />
                    <br />

                    <div class="row clearfix">
                        <div class="col-sm-4 feature-item">
                            <div class="feature-img">
                                <img src="/new_pages/startup/common-files/icons/icon_calendar.png" alt="" />
                            </div>
                            <div class="feature-text">
                                <h4>Real-time Scheduling</h4>
                                <p>Keep track of bookings and availability with an automatically updated event calendar.</p>
                            </div>
                        </div>
                        <div class="col-sm-4 feature-item">
                            <div class="feature-img">
                                <img src="/new_pages/startup/common-files/icons/icon_chart.png" alt="" />
                            </div>
                            <div class="feature-text">
                                <h4>Accurate Bookkeeping</h4>
                                <p>Get paid on time and avoid double bookings and errors with our sales and inventory tracking tools.</p>
                            </div>
                        </div>
                        <div class="col-sm-4 feature-item">
                            <div class="feature-img">
                                <img src="/new_pages/startup/common-files/icons/icon_speech.png" alt="" />
                            </div>
                            <div class="feature-text">
                                <h4>Shared Communication</h4>
                                <p>Get on the same page with a shared event calendar and centralized customer messaging.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="home-features bg-gray">
                <div class="container">

                    <h2 class="textcenter">For Your Business</h2>
                    <p class="textcenter lead">Make your venue shine with an improved online presence, automated customer guidance, and superior customer service.</p>

                    <div class="img textcenter">
                        <img src="/new_pages/startup/common-files/img/content/booking_sample.jpg" width="780" height="294" alt="">
                    </div>
                    <br />
                    <br />
                    <br />

                    <div class="row clearfix">
                        <div class="col-sm-4 feature-item">
                            <div class="feature-img">
                                <img src="/new_pages/startup/common-files/icons/icon_blue_1.png" alt="" />
                            </div>
                            <div class="feature-text">
                                <h4>Keep Your Branding</h4>
                                <p>Integrate online event booking into your website to generate more sales and strengthen your brand.</p>
                            </div>
                        </div>
                        <div class="col-sm-4 feature-item">
                            <div class="feature-img">
                                <img src="/new_pages/startup/common-files/icons/icon_blue_2.png" alt="" />
                            </div>
                            <div class="feature-text">
                                <h4>Improve Ranking</h4>
                                <p>Become more discoverable on all major search engines with your optimized venue profile on InviteBIG.</p>
                            </div>
                        </div>
                        <div class="col-sm-4 feature-item">
                            <div class="feature-img">
                                <img src="/new_pages/startup/common-files/icons/icon_blue_3.png" alt="" />
                            </div>
                            <div class="feature-text">
                                <h4>Free Support</h4>
                                <p>Not only do we offer free setup for your venue, but we also provide support at no cost.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="home-details" class="home-features">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-8 col-sm-offset-2 textcenter">
                          <h2>Even More Benefits</h2>
                          <p class="lead">Our software focuses on solving all your event booking needs.</p>
                        </div>
                    </div>
                    <div class="row clearfix">
                        <div class="col-sm-4 feature-item icon2">
                            <div class="feature-img">
                            </div>
                            <div class="feature-text">
                                <h4>Keep your Privacy</h4>
                                <p>Set privacy settings to limit who can view, manage and even book your venue.</p>
                            </div>
                        </div>
                        <div class="col-sm-4 feature-item icon3">
                            <div class="feature-img">
                            </div>
                            <div class="feature-text">
                                <h4>Always Accessible</h4>
                                <p>Access your venue’s booking information from any device, at any time.</p>
                            </div>
                        </div>
                        <div class="col-sm-4 feature-item icon4">
                            <div class="feature-img">
                            </div>
                            <div class="feature-text">
                                <h4>Secure Payments</h4>
                                <p>We use the most trusted credit card processing services with a 4.9% transaction fee.</p>
                            </div>
                        </div>
                        <div class="col-sm-4 feature-item icon5">
                            <div class="feature-img">
                            </div>
                            <div class="feature-text">
                                <h4>You In Control</h4>
                                <p>Approve, deny and edit incoming bookings and maintain your schedules.</p>
                            </div>
                        </div>
                        <div class="col-sm-4 feature-item icon7">
                            <div class="feature-img">
                            </div>
                            <div class="feature-text">
                                <h4>Stay Notified</h4>
                                <p>Receive instant alerts to resolve pending bookings, booking conflict and respond to customer requests.</p>
                            </div>
                        </div>
                        <div class="col-sm-4 feature-item icon9">
                            <div class="feature-img">
                            </div>
                            <div class="feature-text">
                                <h4>Customize Everything</h4>
                                <p>Create a fully customized and company branded booking experience for your staff and customer needs.</p>
                            </div>
                        </div>

                        <div class="col-sm-4 feature-item icon-calculator">
                            <div class="feature-img">
                            </div>
                            <div class="feature-text">
                                <h4>Lose The Calculator</h4>
                                <p>Our system will crunch the numbers for the total cost of each event proposal.</p>
                            </div>
                        </div>
                        <div class="col-sm-4 feature-item icon6">
                            <div class="feature-img">
                            </div>
                            <div class="feature-text">
                                <h4>Your Personal Assistant</h4>
                                <p>Guide your customers with automated email invoices, payment reminders and status updates.</p>
                            </div>
                        </div>
                        <div class="col-sm-4 feature-item icon-tag">
                            <div class="feature-img">
                            </div>
                            <div class="feature-text">
                                <h4>Deals Done Better</h4>
                                <p>Manage your own promo codes and discounts to encourage bookings during non-peak hour.</p>
                            </div>
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
			<script src="/new_pages/startup/flat-ui/js/bootstrap.min.js"></script>
            <script src="/new_pages/startup/common-files/js/startup-kit.js"></script>
        </div>
    </div></div>
    </body>
</html>
