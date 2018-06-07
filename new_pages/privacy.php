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
        <title>Privacy Policy | InviteBIG</title>
		<meta name="description" content="Review InviteBIG's Privacy Policy"/>
		
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
        <div class="page-wrapper bg-gray">
        
			<div class='container' style='margin:30px auto 60px auto'>

				<h1 style='text-align:center;'>InviteBIG's Privacy Policy</h1>
				<p>Protecting your private information is our priority. This Statement of Privacy applies to the invitebig.com and InviteBIG, 
				Inc. and governs data collection and usage. For the purposes of this Privacy Policy, unless otherwise noted, all references 
				to InviteBIG, Inc. include invitebig.com and InviteBIG. By using the 
				InviteBIG website you consent to the data practices described in this statement.</p><br>
				<h3>Collection of your Personal Information</h3>
				<p>InviteBIG may collect personally identifiable information, such as your name. If you purchase products or 
				services through InviteBIG, we collect billing and credit card information. This information is used to complete the purchase 
				transaction, however InviteBIG does not store credit card numbers. InviteBIG may also collect anonymous demographic information, which is not unique to you, such as 
				your age, gender and household income. We may gather additional personal or non-personal information in the future.
				</p>
				<p>Information about your computer hardware and software may be automatically collected by InviteBIG. This information can 
				include: your IP address, browser type, domain names, access times and referring website addresses. This information is 
				used for the operation of the service, to maintain quality of the service, and to provide general statistics regarding 
				use of the InviteBIG website.
				</p>
				<p>Please keep in mind that if you directly disclose personally identifiable information or personally sensitive data through 
				InviteBIG's public message boards, this information may be collected and used by others.
				</p>
				<p>InviteBIG encourages you to review the privacy statements of websites you choose to link to from InviteBIG so that you can 
				understand how those websites collect, use and share your information. InviteBIG is not responsible for the privacy 
				statements or other content on websites outside of the InviteBIG website.
				</p><br>
				<h3>Use of your Personal Information</h3>
				<p>InviteBIG collects and uses your personal information to operate its website(s) and deliver the services you have requested.
				</p>
				<p>InviteBIG may also use your personally identifiable information to inform you of other products or services available from 
				InviteBIG and its affiliates. InviteBIG may also contact you via surveys to conduct research about your opinion of current 
				services or of potential new services that may be offered.
				</p><p>
				InviteBIG does not sell, rent or lease its customer lists to third parties.
				</p><p>
				InviteBIG may, from time to time, contact you on behalf of external business partners about a particular offering that may 
				be of interest to you. In those cases, your unique personally identifiable information (e-mail, name, address, telephone 
				number) is transferred to the third party. InviteBIG may share data with trusted partners to help perform statistical 
				analysis, send you email or postal mail, provide customer support, or arrange for deliveries. All such third parties are 
				prohibited from using your personal information except to provide these services to InviteBIG, and they are required to 
				maintain the confidentiality of your information.
				</p><p>
				InviteBIG may keep track of the websites and pages our users visit within InviteBIG, in order to determine what InviteBIG 
				services are the most popular. This data is used to deliver customized content and advertising within InviteBIG to customers 
				whose behavior indicates that they are interested in a particular subject area.
				</p><p>
				InviteBIG will disclose your personal information, without notice, only if required to do so by law or in the good faith 
				belief that such action is necessary to: (a) conform to the edicts of the law or comply with legal process served on 
				InviteBIG or the site; (b) protect and defend the rights or property of InviteBIG; and, (c) act under exigent circumstances 
				to protect the personal safety of users of InviteBIG, or the public.
				</p><br>
				<h3>Use of Cookies</h3>
				<p>The InviteBIG website may use "cookies" to help you personalize your online experience. A cookie is a text file that is placed 
				on your hard disk by a web page server. Cookies cannot be used to run programs or deliver viruses to your computer. Cookies 
				are uniquely assigned to you, and can only be read by a web server in the domain that issued the cookie to you.
				</p><p>
				One of the primary purposes of cookies is to provide a convenience feature to save you time. The purpose of a cookie is to 
				tell the Web server that you have returned to a specific page. For example, if you personalize InviteBIG pages, or register 
				with InviteBIG site or services, a cookie helps InviteBIG to recall your specific information on subsequent visits. This 
				simplifies the process of recording your personal information, such as billing addresses, shipping addresses, and so on. 
				When you return to the same InviteBIG website, the information you previously provided can be retrieved, so you can easily 
				use the InviteBIG features that you customized.
				</p><p>
				You have the ability to accept or decline cookies. Most Web browsers automatically accept cookies, but you can usually 
				modify your browser setting to decline cookies if you prefer. If you choose to decline cookies, you may not be able to 
				fully experience the interactive features of the InviteBIG services or websites you visit.
				</p><br>
				<h3>Security of your Personal Information</h3>
				<p>InviteBIG has been designed to secure your personal information from unauthorized access, use, or disclosure. When personal information 
				(such as a credit card number) is transmitted to other websites, it is protected through the use of encryption, such 
				as the Secure Sockets Layer (SSL) protocol.
				</p><br>
				<h3>Children Under Thirteen</h3>
				<p>InviteBIG does not knowingly collect personally identifiable information from children under the age of thirteen. If 
				you are under the age of thirteen, you must ask your parent or guardian for permission to use this website.
				</p><br>
				<h3>Opt-Out & Unsubscribe</h3>
				<p>We respect your privacy and give you an opportunity to opt-out of receiving announcements of certain information. 
				Users may opt-out of receiving any or all communications from InviteBIG by contacting us here:</p><br><ul>
				<li>Web page: /dashboard#profile</li>
				<li>Email: support@invitebig.com</li>
				</ul></p><br>
				<h3>Changes to this Statement</h3>
				<p>InviteBIG will occasionally update this Statement of Privacy to reflect company and customer feedback. InviteBIG 
				encourages you to periodically review this Statement to be informed of how InviteBIG is protecting your information.
				</p><br>
				<h3>Contact Information</h3>
				<p>InviteBIG welcomes your questions or comments regarding this Statement of Privacy. If you believe that InviteBIG has 
				not adhered to this Statement, please feel free to send us an email at support@invitebig.com.
				</p><br>
				<p>Effective as of January 01, 2014
				</p><br>
				
            </div>

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
    </body>
</html>
