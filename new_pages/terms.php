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
        <title>Terms and Conditions | InviteBIG</title>
		<meta name="description" content="By using InviteBIG you agree to our Terms and Conditions"/>
		
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
				<h1 style='text-align:center;'>
					InviteBIG Terms of Service ("Agreement")
				</h1>
				<p>
					This Agreement was last modified on January 27, 2016.
				</p>
				<p>
					Please read these Terms of Service ("Agreement", "Terms") carefully before using <a href="/"></a> ("the Site")
					operated by InviteBIG, Inc. ("InviteBIG", "us", "we", or "our"). These Terms constitute a legally binding agreement between you and InviteBIG. You are not
					authorized to use this Site or Service unless you fully agree to these Terms and you are able to enter into this legally binding contract. Additional terms
					may apply to other services you may access using the Site.
				</p>
				<p>
					By accessing or using the Site in any manner, including, but not limited to, visiting or browsing the Site or contributing content or other materials to
					the Site, you agree to be bound by these Terms of Service as well as our <a href="/privacy">Privacy Policy</a>. Capitalized terms
					are defined in this Agreement. Failure to use the Site in accordance with these Terms may subject you to civil and criminal penalties.
				</p>
				<p>
					OUR SERVICES COMPRISE AN ONLINE PLATFORM THROUGH WHICH VENUE AGENTS MAY CREATE AND MANAGE LISTINGS, VENUE AGENTS AND CUSTOMERS MAY MAKE BOOKINGS AND REVIEW
					VENUES. YOU UNDERSTAND AND AGREE THAT INVITEBIG'S SERVICES BRING VENUE AGENTS AND CUSTOMERS TOGETHER; HOWEVER, INVITEBIG IS NOT A PARTY TO ANY AGREEMENTS
					ENTERED INTO BETWEEN VENUE AGENTS AND CUSTOMERS, NOR IS INVITEBIG A REAL ESTATE BROKER, AGENT OR INSURER. INVITEBIG HAS NO CONTROL OVER THE CONDUCT OF
					VENUE AGENTS OR CUSTOMERS VIA THE SERVICES OR VENUES FEATURED ON THE SERVICES, AND DISCLAIMS ALL LIABILITY IN THIS REGARD.
				</p><br>
				<h3>Key Terminology</h3>
				</p>
				<p>
					<strong>"Booking"</strong> means a reservation for a Venue and its accommodations in advance which may require Venue Agent approval and a paid deposit.
				</p>
				<p>
					<strong>"Collective Content"</strong> means Customer Content and InviteBIG Content.
				</p>
				<p>
					<strong>"Content"</strong> means text, graphics, images, music, software (excluding the Application), audio, video, information or other materials.
				</p>
				<p>
					<strong>"Customer"</strong> means a person who completes the InviteBIG account registration process to create and manage a Booking as contemplated herein,
					as described under "Creating an account on the Site" below.
				</p>
				<p>
					<strong>"Customer Content"</strong> means all Content that a Customer posts, uploads, publishes, submits or transmits to be made available through the
					Site.
				</p>
				<p>
					<strong>"InviteBIG Content"</strong> means all Content that InviteBIG makes available through the Site, including any Content licensed from a third party,
					but excluding Customer Content.
				</p>
				<p>
					<strong>"Listing"</strong> means any Venue's information that is listed by a Venue Agent for Booking via our Services.
				</p>
				<p>
					<strong>"Off-Site"</strong> means not controlled by or directly connected to the Site or our Services.
				</p>
				<p>
					<strong>"Services"</strong> refers to our services, technologies and the Site collectively.
				</p>
				<p>
					<strong>"Tax" or "Taxes"</strong>
					mean any sales taxes, value added taxes (VAT), goods and services taxes (GST) and other similar municipal, state and federal indirect or other withholding
					and personal or corporate income taxes.
				</p>
				<p>
					<strong>"Venue"</strong>
					means a place and business entity where a Booking takes place.
				</p>
				<p>
					<strong>"Venue Agent"</strong>
					means a Customer who is granted Venue management rights by an existing Venue Agent or InviteBIG.
				</p>
				<p>
					<strong>"Venue Booking Terms"</strong> means any separate Terms and Conditions agreement that Customers must agree to that are provided by the Venue Agent
					at time of Booking.
				</p>
				<br><p>
					<h3>Agreeing to these Terms</h3>
				</p>
				<p>
					YOU ACKNOWLEDGE AND AGREE THAT, BY ACCESSING OR USING OUR SERVICES OR BY DOWNLOADING OR POSTING ANY CONTENT to, FROM OR ON THE SITE, YOU ARE INDICATING
					THAT YOU HAVE READ, AND THAT YOU UNDERSTAND AND AGREE TO BE BOUND BY THESE TERMS, WHETHER OR NOT YOU HAVE REGISTERED TO USE OUR SERVICES. IF YOU DO NOT
					AGREE TO THESE TERMS, THEN YOU HAVE NO RIGHT TO ACCESS OR USE THE SERVICES, OR COLLECTIVE CONTENT OR TO PARTICIPATE IN THE REFERRAL PROGRAM. If you accept
					or agree to these Terms on behalf of a company or other legal entity, you represent and warrant that you have the authority to bind that company or other
					legal entity to these Terms and, in such event, "you" and "your" will refer and apply to that company or other legal entity.
				</p>
				<p>
					Certain areas of the Site (and your access to or use of certain aspects of the Services or Collective Content) may have different terms and conditions
					posted or may require you to agree with and accept additional terms and conditions. If there is a conflict between these Terms and terms and conditions
					posted for a specific area of the Site or Collective Content, the latter terms and conditions will take precedence with respect to your use of or access to
					that area of the Site or Collective Content.
				</p>
				<br><p>
					<h3>Modification </h3>
				</p>
				<p>
					InviteBIG reserves the right, at its sole discretion, to modify the Site or Services or to modify these Terms, including the Service Fees, at any time and
					without prior notice. If we modify these Terms, we will post the modification on the Site or provide you with notice of the modification. We will also
					update the "Last Updated Date" at the top of these Terms. By continuing to access or use the Site after we have posted a modification on the Site or have
					provided you with notice of a modification, you are indicating that you agree to be bound by the modified Terms. If the modified Terms are not acceptable
					to you, your only recourse is to cease using the Site.
				</p>
				<br><p>
					<h3>General eligibility to use the site </h3>
				</p>
				<p>
					As a condition of your use of the SITE, you warrant that:
				</p>
				<ul>
					<li>
						you are at least 18 years of age;
					</li>
					<li>
						you possess the legal authority to create a binding legal obligation;
					</li>
					<li>
						you will use the Site in accordance with this Agreement;
					</li>
					<li>
						you will only use the Site to make legitimate Bookings for you or for another person for whom you are legally authorized to act;
					</li>
					<li>
						all information supplied by you on the Site is true, accurate, current and complete, and;
					</li>
					<li>
						you will safeguard your InviteBIG account information and will supervise and be completely responsible for all uses of your account including use by anyone other than you.
					</li>
				</ul>
				
				<p>
					We retain the right at our sole discretion to deny anyone access to the Site at any time and for any reason, including, but not limited to, violation of
					this Agreement or any applicable law, rule, or regulation.
				</p>
				<br><p>
					<h3>Venue agent eligibility: accurate information and representations.</h3>
				</p>
				<p>
					A Venue Agent may only use Our Service if the Venue Agents can form legally binding contracts under applicable law. If you are registering as a business
					entity, you represent that you have the authority to bind the entity to these Terms. Each Venue Agent represents and covenants that all information
					submitted to us and to the Site during such Venue Agent's registration with the Site will be true and correct. Each Venue Agent further agrees to promptly
					provide notice to the Site by updating their Venue Agent information, including any contact information previously submitted by the Venue Agent to the
					Site. Each Venue Agent agrees to promptly provide such proof of identification, legal formation, licensure, authority to act as a Venue Agent, space
					ownership, and any other information reasonably required in our sole discretion to operate the Site. Each Venue Agent further represents, warrants and
					covenants that: (i) it owns and/or has all necessary rights and authority to offer the space or activity listed by such Venue Agent; (ii) it will not
					wrongfully withhold a deposit in breach of the underlying agreement; (iii) it will accurately describe the subject space or activity and will not fail to
					disclose a material defect in, or material information about, any rental space or activity; (iv) it will not wrongfully deny access to the listed space or
					activity; (v) it will not fail to provide a refund when due in accordance with the underlying agreement; and (vi) it will comply with all applicable laws,
					rules and regulations in all transactions contemplated by these Terms, including its transactions and communications with other users of the Site.
				</p>
				<br><p>
					<h3>Creating an account on the Site </h3>
				</p>
				<p>
					In order to access certain features of the Site, and create and manage a Booking or a Listing, you must register to create an account ("InviteBIG
					Account"). You may register to join the Site directly via the Site or as described in this section.
				</p>
				<p>
					You can also register to join by logging into your account with certain third party social networking sites ("SNS") (including, but not limited to,
					Facebook) via the Site, as described below. Each such account is referred to herein as a "Third Party Account." As part of the functionality of the Site,
					you may link your InviteBIG Account with Third Party Accounts, by either: (i) providing your Third Party Account login information to InviteBIG through the
					Site; or (ii) allowing InviteBIG to access your Third Party Account, as is permitted under the applicable terms and conditions that govern your use of each
					Third Party Account. You represent that you are entitled to disclose your Third Party Account login information to InviteBIG and/or grant InviteBIG access
					to your Third Party Account (including, but not limited to, for use for the purposes described herein), without breach by you of any of the terms and
					conditions that govern your use of the applicable Third Party Account and without obligating InviteBIG to pay any fees or making InviteBIG subject to any
					usage limitations imposed by such third party service providers. By granting InviteBIG access to any Third Party Accounts, you understand that InviteBIG
					will access, make available and store (if applicable) any Content that you have provided to be stored in your Third Party Account ("SNS Content") so that
					it is available on and through the Site via your InviteBIG Account and InviteBIG Account profile page. Unless otherwise specified in these Terms, all SNS
					Content, if any, will be considered to be Customer Content for all purposes of these Terms. Depending on the Third Party Accounts you choose and subject to
					the privacy settings that you have set in such Third Party Accounts, personally identifiable information that you post to your Third Party Accounts will be
					available on and through your InviteBIG Account on the Site. Please note that if a Third Party Account or associated service becomes unavailable or
					InviteBIG's access to such Third Party Account is terminated by the third party service provider, then SNS Content will no longer be available on and
					through the Site. You have the ability to disable the connection between your InviteBIG Account and your Third Party Accounts, at any time, through your
					Third Party Service Provider. PLEASE NOTE THAT YOUR RELATIONSHIP WITH THE THIRD PARTY SERVICE PROVIDERS ASSOCIATED WITH YOUR THIRD PARTY ACCOUNTS IS
					GOVERNED SOLELY BY YOUR AGREEMENT(S) WITH SUCH THIRD PARTY SERVICE PROVIDERS. InviteBIG makes no effort to review any SNS Content for any purpose,
					including but not limited to, for accuracy, legality or non-infringement and InviteBIG is not responsible for any SNS Content.
				</p>
				<p>
					We will create your InviteBIG Account and your InviteBIG Account profile page for your use of the Site based upon the personal information you provide to
					us or that we obtain via an SNS as described above. You may not have more than one (1) active InviteBIG Account. You agree to provide accurate, current and
					complete information during the registration process and to update such information to keep it accurate, current and complete. InviteBIG reserves the right
					to suspend or terminate your InviteBIG Account and your access to the Site if you create more than one (1) InviteBIG Account or if any information provided
					during the registration process or thereafter proves to be inaccurate, not current or incomplete. You are responsible for safeguarding your password. You
					agree that you will not disclose your password to any third party and that you will take sole responsibility for any activities or actions under your
					InviteBIG Account, whether or not you have authorized such activities or actions. You will immediately notify InviteBIG of any unauthorized use of your
					InviteBIG Account.
				</p>
				<br><p>
					<h3>Bookings and Financial Terms</h3>
				</p>
				<p>
					The Venue Agents, not InviteBIG, are solely responsible for fulfilling bookings and making available any Venues and its accommodations reserved through the
					Services. It is the Venue Agent and not InviteBIG which determines the amounts due and payable by the Customer to the Venue Agent for and related to a
					Booking ("Venue Fees") and the possible fee range for booking a Venue provided in the Listing. A full description of the Venue Fees, Taxes, payment
					schedule, Deposit amount, and Booking Fees is made available to the Customer prior to all Bookings (the "Invoice"). InviteBIG may collect the full or
					partial amount of the Venue Fees on behalf of the Venue Agent prior to a Booking. The exact amount InviteBIG collects on behalf of the Venue is determined
					by the Venue Booking Terms stated by the Venue Agent for the respective Booking. InviteBIG may charge a processing fee for processing payments made through 
					the Site, and the Customer or Venue Agent that is making the payment will be prompted to agree to the fee prior to the payment being processed. ANY OFF-SITE PAYMENTS MADE BY THE CUSTOMER TO THE VENUE OR VENUE AGENT WILL
					NOT BE INCLUDED AS PART OF THESE TERMS.
				</p>
				<br><p>
					<h3>Subscription plan terms for a Venue:</h3>
					In consideration for providing the Services, each Venue registered on the Site is required to maintain an active subscription plan. Subscription plan pricing and 
					availability is subject to change at any time, with or without notice.<br><br>All subscription plans automatically renew at the end of the subscription period. 
					If the automated renewal payment fails then the Venue Agent will have 7 days to make the payment, otherwise the Services rendered for the Venue will be restricted.
					Subscription plans may be changed at any time by a Venue Agent. If a subscription plan is cancelled then Services will be rendered until the end of the current subscription 
					period and the subscription plan will not automatically renew. No refunds will be given for subscription plan changes or cancellations. If a subscription plan is changed  
					to a plan which costs less then the Venue will be credited a non-transferable account balance based on the proration of the plan being cancelled.<br><br>
					Some subscription plans may have a free trial period. For these plans a valid credit card must be provided before Services will be rendered, however no charges will 
					be made until the end of the trial period. Once the trial period ends the subscription plan will automatically renew and the credit card provided will be charged 
					according to the details of the subscription plan.
				</p>
				<br><p>
					<h3>Booking and financial terms for a Venue Agent:</h3>
					If you are a Venue Agent and a Booking is requested for your Venue via our Services, you will be required to either confirm or reject the booking within 72
					hours of when the booking is requested (as determined by InviteBIG in its sole discretion) or the booking request will be automatically cancelled. When a
					Booking is requested via the Site, we will share with you the first and last name of the Customer who has requested the Booking and we will allow you to
					send a direct message to the Customer via the Site. This is provided solely for you to plan out all the details for the Booking with the Customer before
					confirming or rejecting the booking. If you are unable to confirm or decide to reject a booking within such 72 hour period, any amounts collected by
					InviteBIG for the requested booking will be refunded to the applicable Customer's credit card and any pre-authorization of such credit card will be
					released. When you confirm a booking requested by a Customer, InviteBIG will send you an email via the Site confirming such booking.
				</p>
				<p>
					In consideration for providing the Services, InviteBIG may collect booking fees from Customers ("Booking Fees"). A Booking Fee is an additional fee that is
					charged to the Customer based upon a percentage of the Venue Fees for making a Booking. Where applicable, Taxes may also be charged in addition to the
					Booking Fee and Venue Fee (collectively, the "Total Fee"). Venue Agent will be responsible for collecting the remaining balance of the Total Fee, not
					collected by InviteBIG. Venue Fees collected by InviteBIG are paid to the Venue Agent via an agreed upon payment method, paid to the legal
					business name of the Venue within a commercially reasonable time after the applicable Booking has been completed and no dispute has been submitted by
					either the Venue Agent or Customer in regards to the specific booking transaction.
				</p>
				<p>
					A commercially reasonable time will take into account bank processing times for credit card payments and bank policies regarding availability of deposits,
					in addition to administrative processing time on the part of InviteBIG. Absent exceptional circumstances, Venue Fees will be paid to the Venue Agent within
					3 business days after the applicable Booking has been completed.
				</p>
				<br><p>
					<h3>Booking and financial terms for Customers:</h3>
					If you choose to make a Booking via our Services, InviteBIG will either collect: (i) a deposit as a percentage of the Venue Fees plus the Booking Fee (the
					"Deposit"); or (ii) the Total Fee, on behalf of the Venue Agent at the time of booking. The Invoice provided to you before making the Booking will indicate
					whether a Deposit or the Total Fee will be due at the time of booking. Where a Deposit is required, the remaining balance of the Total Fee will be
					collected by the Venue Agent on behalf of the Venue on the due date that the Venue Agent specified. InviteBIG will not be liable or responsible for any
					Off-Site payment made by the Customer to the Venue or Venue Agent.
				</p>
				<p>
					In connection with your booking, you will be asked to provide customary billing information such as name, billing address and credit card information
					either to InviteBIG or its third party payment processor. You agree to pay InviteBIG for any bookings made in connection with your Account in accordance
					with these Terms and you hereby authorize the collection of such amounts on behalf of the Venue Agent by charging the credit card provided as part of
					making a Booking, either directly by InviteBIG or indirectly, via a third party online payment processor. If you are directed to InviteBIG's third party
					payment processor, you may be subject to terms and conditions governing use of that third party's service and that third party's personal information
					collection practices. Please review such terms and conditions and privacy policy before using the services.
				</p>
				<p>
					InviteBIG reserves the right, in its sole discretion, to (i) obtain a pre-authorization via your credit card for the Deposit amount or the Total Fees
					associated with your booking or (ii) charge your credit card a nominal amount, not to exceed one dollar ($1), to verify your credit card. Once InviteBIG
					receives confirmation of your booking from the applicable Venue Agent, InviteBIG will collect the Deposit or Total Fees, as applicable, in accordance with
					the these Terms. All Deposits and Total Fees are payable in United States dollars. Once your booking transaction is complete, you will receive a
					confirmation email summarizing your booking and a copy of such confirmation will be available for review via your InviteBIG Account.
				</p>
				<br><p>
					<h3>Deposits</h3>
				</p>
				<p>
					Venue Agents may choose to require deposits on their Listings ("Deposits"). Each Listing will describe whether a Deposit is required for the applicable
					Booking. If a Deposit is included in a Listing for a Booking, InviteBIG will, in its capacity as the payment agent of the Venue, use its commercially
					reasonable efforts to obtain a pre-authorization of the Customer's credit card in the amount the Venue Agent determines for the Deposit.
				</p>
				<p>
					If, as a Customer, you fail to pay the full Deposit in accordance with the Venue Booking Terms disclosed in the Listing and available on the Booking's
					Invoice, then your Booking may be cancelled at the Venue's discretion and any partial Deposit paid will be refunded. If a Venue Agent does not accept and approve your
					Booking, any amount you paid for a Deposit will be refunded. However, if as a Customer, you cancel the booking after paying the deposit and it has been
					approved and accepted by the Venue Agent, the Booking Fee made through our Services is non-refundable. Any additional payments made through our Services
					may or may not be refundable depending upon the terms of the applicable refund policy disclosed in the Venue Booking Terms (as described under
					"Cancellations and Refunds," below). If the Venue Agent cancels the Booking then your Deposit and any additional payments made through our Services are
					refundable (as described under "Cancellations and Refunds" and the applicable refund policy).
				</p>
				<br><p>
					<h3>Booking Fees </h3>
				</p>
				<p>
					In consideration for providing the Services, InviteBIG may collect booking fees from Customers ("Booking Fees"). A Booking Fee is an additional fee that is
					charged to the Customer based upon a percentage of the Venue Fees for making a Booking. Where applicable, Taxes may also be charged in addition to the
					Booking Fee and Venue Fees (collectively, the "Total Fee").
				</p>
				<p>
					Except as otherwise provided herein, under "Cancellations and Refunds", or as required by applicable law Booking Fees are non-refundable.
				</p>
				<br><p>
					<h3>Venue Fees and payments</h3>
				</p>
				<p>
					Venue Fees are determined solely by the Venue Agent. Generally, Venue Fees based on a by hour or fixed cost (e.g. $100 per hour or $700 per item). The
					Venue Agent sets the price when registering its venue profile on the Site. The Venue Agents may make changes to the Venue Fees. However, a Customer will
					lock in the pricing displayed at the time of making a Booking on the Site.
				</p>
				<br><p>
					<h3>Cancellations and refunds </h3>
				</p>
				<p>
					If, as a Customer, you cancel your requested booking before the requested booking is confirmed by a Venue Agent, InviteBIG will cancel any
					pre-authorization to your credit card and/or refund any nominal amounts charged to your credit card in connection with the requested booking within a
					commercially reasonable time. If, as a Customer, you wish to cancel a confirmed booking made via the Site, either prior to or after your Booking start
					time, the refund policy of the Venue, provided by the Venue Agent, contained in the applicable Listing or Venue Booking Terms will apply to such
					cancellation. Our ability to refund the Venue Fees, Sales Tax, and other fees collected from you by our Services will depend upon the terms of the
					applicable refund policy. Details regarding refunds and cancellation policies will be presented to you at the time of payment and your acceptance is a
					condition of your Booking via the Site. If you are entitled to a refund, InviteBIG can only refund amounts paid through the Site. Under no circumstances
					will we be responsible to refund any fees paid Off-Site.
				</p>
				<p>
					If a Venue Agent cancels a confirmed booking made via our Services then InviteBIG will refund all fees our Services collected for such booking to the
					applicable Customer within a commercially reasonable time of the cancellation, provided that no payments are overdue for said booking. If payment is overdue 
					then the Venue Agent may be entitled to certain cancellation fees. Our ability to refund the Venue Fees, Sales Tax, and other fees collected from you by our Services will depend upon the terms of the
					applicable refund policy. Details regarding refunds and cancellation policies will be presented to you at the time of payment and your acceptance is a
					condition of your Booking via the Site. If you are entitled to a refund, InviteBIG can only refund amounts paid through
					the Site. Under no circumstances will we be responsible to refund any fees paid Off-Site. If a Venue Agent cancelled a
				confirmed booking and you, as a Customer, have not received an email or other communication from InviteBIG, please contact us at    <a href="mailto:support@invitebig.com">support@invitebig.com</a>.
				</p>
				<p>
					In the event of a dispute between a Customer and a Venue Agent as to the refundability of fees paid through our Site, InviteBIG in its sole discretion may
					either refund the fees or interplead any disputed funds into a court of competent jurisdiction.
				</p>
				<br><p>
					<h3>Insurance and Taxes</h3>
				</p>
				<p>
					InviteBIG does not carry any insurance for any Booking made or Venues featured on the Services. Accordingly, you agree that it is your sole responsibility
					to obtain any insurance as required by law or otherwise related to your booking of a Venue or your activities on the Services. You are also solely
					responsible for determining any tax requirements or obligations you may have related to any Booking made via the Services. InviteBIG cannot and does not
					offer tax advice to Customers. InviteBIG does not determine or collect taxes for remittance to applicable taxing authorities, except with respect to tax
					obligations on the amount we retain for our services. Remittance of all Taxes associated with any Booking, including but not limited to city. Business and
					Occupations Taxes, and local hospitality taxes, will be the responsibility of the Venue Agent. InviteBIG does not act as a co-vendor with the Venue Agent.
				</p>
				<br><p>
					<h3>Invitebig reservations</h3>
				</p>
				<p>
					By reading and acknowledging these Terms, you understand that InviteBIG is a service provider that facilitates Bookings by allowing Venue Agents to post
					Listings containing availability, location and pricing for venues and their accommodations and allowing Customers to make Bookings through the Services.
					InviteBIG provides payment processing and booking services and provides a forum to display scheduling, ratings, and reviews as an added service, but this
					does not make InviteBIG a party to any agreement made between a Customer and Venue Agent, including but not limited to a Booking agreement arising between
					a Customer and Venue Agent.
				</p>
				<p>
					When making a Booking on our Services, you authorize InviteBIG to book a Booking on your behalf. You understand that InviteBIG is not a co-vendor with the
					Venue or Venue Agent and that you may need to agree to a separate contract with the Venue Agent to complete the booking. By permitting InviteBIG to book
					the reservation you agree to payment of the Total Fees including the booking rate displayed on the Site and any associated fees and taxes. Further, if the
					Provider has opted to use InviteBIG payment services you agree that your credit card will be charged by InviteBIG for the Total Fees or Deposit.
				</p>
				<p>
					Reservations made on InviteBIG are for obtaining facilities on a temporary event basis only, and not as a real estate lease. At minimum, services
					associated with facilities must include light, electricity, and climate control. Other amenities (seating, tables, etc) may be provided by a Venue Agent as
					appropriate and as agreed to by the Customer.
				</p>
				<br><p>
					<h3>Venue agent agreements rules and regulations</h3>
				</p>
				<p>
					A separate agreement will apply to your Booking and/or purchase of facilities and related services from a Venue Agent (Booking Agreement and/or Venue
					Booking Terms). InviteBIG is not a party to any Booking Agreement or Venue Booking Terms. Nothing in any separate agreement between a Customer and a Venue
					Agent can modify InviteBIG's rights and obligations under this Agreement. The Booking Agreement and/or Venue Booking Terms will be presented to you at the
					time of payment and your acceptance is a condition of your reservation. Please read this separate agreement carefully. You agree to abide by the Venue
					Booking Terms imposed by any Venue Agent with whom you elect to deal, including, but not limited to, payment of all amounts when due and compliance with
					the supplier's rules and restrictions regarding availability and use of the facilities.
				</p>
				<p>
					You acknowledge that Venue Agents offering certain services and/or activities may require you to agree to their separate agreement prior to participating
					in the service and/or activity. You understand that any violation of any such supplier's terms, rules, or restrictions may result in cancellation of your
					booking(s), denied access to the applicable Venue and its accommodations, forfeiture of any monies paid for such booking(s), and/or debiting of your
					account for any costs incurred by InviteBIG as a result of such violation.
				</p>
				<br><p>
					<h3>Intellectual Property</h3>
				</p>
				<p>
					The Site and its original content, features and functionality are owned by InviteBIG, Inc. and are protected by international copyright, trademark, patent,
					trade secret and other intellectual property or proprietary rights laws.
				</p>
				<br><p>
					<h3>Content on InviteBIG</h3>
				</p>
				<p>
					"Venue Content" means all Content that a Venue Agent posts, uploads, publishes, submits or transmits to be made available through the Services, including,
					but not limited to, Listings.
				</p>
				<br><p>
					<h3>Customer Content:</h3>
					We permit Customers to post, upload, publish, submit or transmit Customer Content, including, but not limited to, reviews of Venues. By making available
					any Customer Content through the Services, you hereby grant to InviteBIG a worldwide, irrevocable, perpetual, non-exclusive, transferable, royalty-free
					license, with the right to sublicense, to use, copy, adapt, modify for formatting purposes, distribute, license, transfer, publicly display, publicly
					perform, transmit, stream, broadcast, access, view, and otherwise exploit such Customer Content in connection with the Services, including for promotion of
					the Services, directly or indirectly by intermediaries, and to authorize others to do the same.
				</p>
				<p>
					You agree that you are solely responsible for all your Customer Content. You represent and warrant that: (a) you own the Customer Content that you provide
					through the Services, and (b) you have all rights, licenses, consents and releases, express or implied, necessary to use the Services and to grant to
					InviteBIG the rights in the Customer Content you provide via the Services. You also represent and warrant that your use of, and your authorization of
					InviteBIG's use of, the Customer Content you provide via the Services will not infringe, misappropriate or violate a third party's intellectual property
					rights, or rights of publicity or privacy, or result in the violation of any applicable law or regulation.
				</p>
				<br><p>
					<h3>Termination</h3>
				</p>
				<p>
					We may terminate your access to the Site, without cause or notice, which may result in the forfeiture and destruction of all information associated with
					you. All provisions of this Agreement that by their nature should survive termination shall survive termination, including, without limitation, ownership
					provisions, warranty disclaimers, indemnity, and limitations of liability.
				</p>
				<br><p>
					<h3>Links to other sites</h3>
				</p>
				<p>
					Our Site may contain links to third-party sites that are not owned or controlled by InviteBIG, Inc.
				</p>
				<p>
					InviteBIG, Inc. has no control over, and assumes no responsibility for, the content, privacy policies, or practices of any third party sites or services.
					We strongly advise you to read the terms and conditions and privacy policy of any third-party site that you visit.
				</p>
				<br><p>
					<h3>Privacy policy</h3>
				</p>
				<p>
					InviteBIG believes in protecting your privacy. Please review our current <a href="/privacy">Privacy Policy</a>, which also governs your
					use of the Site.
				</p>
				<br><p>
					<h3>Ownership of intellectual property</h3>
				</p>
				<p>
					The contents of the Services, including all software, design, text, images, photographs, illustrations, audio and video material, artwork, graphic
					material, databases, proprietary information, downloadable products, and all copyrightable or otherwise legally protectable elements of the Services,
					including, without limitation, the selection, sequence and 'look and feel' and arrangement of items, and all trademarks, service marks and trade names
					(individually and/or collectively, "Material"), are, unless expressly disclaimed, the property of InviteBIG, its suppliers, its Venues, or its operational
					service providers and are legally protected, without limitation, under applicable U.S. Federal, State, and foreign laws, regulations and treaties. Unless
					the context clearly requires otherwise or we explicitly state in writing, the term SITE includes "Material" as well. The Services is to be used solely for
					your non-exclusive, non-assignable, non-transferable and limited personal use and for no other purposes.
				</p>
				<p>
					You must not alter, delete or conceal any copyright notice, trademark notice, or other notices contained on the Site, including notices on any Material you
					download, transmit, display, print or reproduce from the Site. You may not, nor may you allow any third party (whether or not for your benefit) to
					reproduce, modify, create derivative works from, display, perform, publish, distribute, disseminate, broadcast or circulate to any third party (including,
					without limitation, on or via a third party site), or otherwise use, any Material without the express prior written consent of the owner. Any unauthorized
					or prohibited use of any Material may subject you to civil liability, criminal prosecution, or both, under applicable federal, state and local laws. We
					require users to respect our copyrights, trademarks, and other intellectual property rights. We likewise respect the intellectual property of others. On
					notice, we will act expeditiously to remove content on the Site that infringes the copyright rights of others and will disable the access to the Site and
					its services of anyone who uses them to repeatedly to infringe the intellectual property rights of others.
				</p>
				<p>
					We take protection of intellectual property, both our own and others, very seriously. We therefore employ measures to prevent copyright infringement and to
					promptly end any infringement that might occur.
				</p>
				<p>
					The Digital Millennium Copyright Act of 1998, found at 17 U.S.C. &sect; 512 ("DMCA"), provides recourse for owners of copyrighted materials who believe that
					their rights under United States copyright law have been infringed upon on the Internet.
				</p>
				<p>
					Under the DMCA, the bona fide owner of copyrighted materials who has a good faith belief that their copyright has been infringed may contact not only the
					person or entity infringing on their copyright, but may also contact the designated agent of an Internet service provider to report alleged infringements
					of their protected works, when such alleged infringements appear on pages contained within the system of the Internet service provider ("ISP").
				</p>
				<p>
					InviteBIG is committed to complying with all United States laws, including United States copyright law. Upon receipt of a properly filed complaint under
					the DMCA, the owner will block access to the allegedly infringing material. The site owner will forward a copy of the notification of claimed copyright
					infringement to the alleged infringer. Anyone who believes in good faith that a notice of copyright infringement has wrongfully been filed against them may
					submit a Counter-notice to the site owner.
				</p>
				<br><p>
					<h3>Infringement notification procedures</h3>
				</p>
				<p>
					If you believe in good faith that materials hosted by InviteBIG infringe upon your copyright or other intellectual property rights, you may file a notice
					of infringement. To file a notice of infringement, you must provide a written communication that sets forth the items specified below. You will be liable
					for damages (including damages, costs, and attorneys' fees) if you materially misrepresent that the Site or Collective Content is infringing your
					intellectual property. Accordingly, if you are not sure whether certain material of yours is protected by copyright or other laws, we suggest that you
					first contact an attorney.
				</p>
				<p>
					To expedite the process please include the following information in your written notice of infringement:
				</p>
				<p>
					Identify in sufficient detail the copyrighted work you claim was infringed.
				</p>
				<p>
					Adequately identify the material you claim is infringing your copyrighted work, and information that will allow us to locate that material on the SITE.
					Please include the URL(s) (the location(s) of the page(s) that contain the allegedly infringing material and also include a description of the specific
					content when you claim that is infringing upon your copyright).
				</p>
				<p>
					Include an email address, telephone number and any other contact information reasonably sufficient to permit us to contact you;
				</p>
				<p>
					Include the following statement: "I swear, under penalty of perjury, that the information in the notification is accurate and that I am the copyright owner
					or I am authorized to act on behalf of the owner of an exclusive right that is allegedly infringed. I also affirm that as the copyright owner, I have a
					good faith belief that use of the material in the manner complained of is not authorized by me, my agent, or the law." [Please note that a recent court
					decision has indicated that your good faith belief should include a consideration of "Fair Use" as defined under U.S. copyright law. If you are not sure
					whether material available online infringes your copyright, we suggest that you first contact an attorney.]
				</p>
				<p>
					Include the signature of the copyright owner or a person authorized to act on behalf of the copyright owner.
				</p>
				<p>
					For further details on the information required for valid notification, see 17 U.S.C. &sect; 512(c)(3).
				</p>
				<p>
					InviteBIG, Inc.,
				</p>
				<p>
					Attn: Legal Department
				</p>
				<p>
					Email:support@InviteBIG.com
				</p>
				<p>
					We will review and respond to all notices that comply with the requirements above. Email is our preferred method of notification.
				</p>
				<br><p>
					<h3>No Endorsement</h3>
				</p>
				<p>
					InviteBIG does not endorse any Customer, Venue Agent, or any Venue featured on the Services. In addition, we do not attempt to confirm, and do not confirm,
					any Customer's or Venue Agent's purported identity. You are responsible for determining the identity and suitability of others whom you contact via the
					Services. We will not be responsible for any damage or harm resulting from your interactions with other Customers and Venue Agents of the Services. By
					using the Services, you agree that any legal remedy or liability that you seek to obtain for actions or omissions of other Customers and Venue Agents will
					be limited to a claim against the particular Customer or Venue Agent who caused you harm. You agree not to attempt to impose liability on, or seek any
					legal remedy from InviteBIG in this regard.
				</p>
				<br><p>
					<h3>Liability disclaimer</h3>
				</p>
				<p>
					THE INFORMATION, SOFTWARE, PRODUCTS, AND SERVICES PUBLISHED ON THIS SITE MAY INCLUDE INACCURACIES OR ERRORS, INCLUDING PRICING ERRORS. IN PARTICULAR,
					INVITEBIG AND ANY AFFILIATED, CO- BRANDED AND/OR LINKED SITE PARTNERS THROUGH WHOM WE PROVIDE SERVICE DO NOT GUARANTEE THE ACCURACY OF, AND DISCLAIM ALL
					LIABILITY FOR ANY ERRORS OR OTHER INACCURACIES RELATING TO THE INFORMATION AND DESCRIPTION OF THE VENUE, ROOM, SPACE OR SPOT AND OTHER MEETING SERVICES
					DISPLAYED ON THIS SITE (INCLUDING, WITHOUT LIMITATION, THE PRICING, PHOTOGRAPHS, LIST OF VENUE AMENITIES, GENERAL PRODUCT DESCRIPTIONS, ETC.), MUCH OF
					WHICH INFORMATION IS PROVIDED BY THE RESPECTIVE SUPPLIERS. IN ADDITION, InviteBIG EXPRESSLY RESERVES THE RIGHT TO CORRECT ANY PRICING ERRORS ON OUR SITE
					AND/OR ON PENDING RESERVATIONS MADE UNDER AN INCORRECT PRICE. IN SUCH EVENT, WE WILL OFFER YOU THE OPPORTUNITY TO KEEP YOUR PENDING RESERVATION AT THE
					CORRECT PRICE OR, AT YOUR OPTION, WE WILL CANCEL YOUR RESERVATION WITHOUT ANY InviteBIG CHANGE OR CANCELLATION FEES.
				</p>
				<p>
					VENUE RATINGS DISPLAYED ON THIS SITE ARE INTENDED AS ONLY GENERAL GUIDELINES, AND InviteBIG AND InviteBIG AFFILIATES DO NOT GUARANTEE THE ACCURACY OF THE
					RATINGS. InviteBIG MAKES NO GUARANTEES ABOUT THE AVAILABILITY OF SPECIFIC PRODUCTS AND SERVICES. InviteBIG MAY MAKE IMPROVEMENTS AND/OR CHANGES ON THIS
					SITE AT ANY TIME.
				</p>
				<p>
					INVITEBIG AND ITS RESPECTIVE VENUE SUPPLIERS MAKE NO REPRESENTATIONS ABOUT THE SUITABILITY OF THE INFORMATION, SOFTWARE, PRODUCTS, AND SERVICES CONTAINED
					ON THIS SITE FOR ANY PURPOSE, AND THE INCLUSION OR OFFERING OF ANY PRODUCTS OR SERVICES ON THIS SITE DOES NOT CONSTITUTE ANY ENDORSEMENT OR RECOMMENDATION
					OF SUCH PRODUCTS OR SERVICES BY InviteBIG OR ITS AFFILIATES. ALL SUCH INFORMATION, SOFTWARE, PRODUCTS, AND SERVICES ARE PROVIDED "AS IS" WITHOUT WARRANTY
					OF ANY KIND. InviteBIG AND ITS RESPECTIVE SUPPLIERS DISCLAIM ALL WARRANTIES AND CONDITIONS THAT THE SITE, ITS SERVERS OR ANY EMAIL SENT FROM INVITEBIG
					AND/OR ITS RESPECTIVE SUPPLIERS ARE FREE OF VIRUSES OR OTHER HARMFUL COMPONENTS. INVITEBIG AND ITS RESPECTIVE SUPPLIERS HEREBY DISCLAIM ALL WARRANTIES AND
					CONDITIONS WITH REGARD TO THIS INFORMATION, SOFTWARE, PRODUCTS, AND SERVICES, INCLUDING ALL IMPLIED WARRANTIES AND CONDITIONS OF MERCHANTABILITY, FITNESS
					FOR A PARTICULAR PURPOSE, TITLE, AND NONINFRINGEMENT.
				</p>
				<p>
					THE VENUE PROVIDERS SUPPLYING MEETING SPACE OR OTHER SERVICES ON THIS SITE ARE INDEPENDENT CONTRACTORS AND NOT AGENTS OR EMPLOYEES OF INVITEBIG. INVITEBIG
					IS NOT LIABLE FOR THE ACTS, ERRORS, OMISSIONS, REPRESENTATIONS, WARRANTIES, BREACHES OR NEGLIGENCE OF ANY SUCH PROVIDERS OR FOR ANY PERSONAL INJURIES,
					DEATH, PROPERTY DAMAGE, OR OTHER DAMAGES OR EXPENSES RESULTING THEREFROM. INVITEBIG AND ITS AFFILIATES HAVE NO LIABILITY AND WILL MAKE NO REFUND IN THE
					EVENT OF CANCELLATION, OVERBOOKING, STRIKE, FORCE MAJEURE OR OTHER CAUSES BEYOND THEIR DIRECT CONTROL, AND THEY HAVE NO RESPONSIBILITY FOR ANY ADDITIONAL
					EXPENSE OR OMISSIONS.
				</p>
				<p>
					SIMILARLY, THE CUSTOMERS USING THIS SITE ARE NOT AGENTS OR EMPLOYEES OF INVITEBIG, AND INVITEBIG IS NOT RESPONSIBLE OR LIABLE FOR THE ACTS, ERRORS,
					OMISSIONS, REPRESENTATIONS, WARRANTIES, BREACHES OR NEGLIGENCE OF ANY SUCH CUSTOMER OR FOR ANY PERSONAL INJURIES, DEATH, PROPERTY DAMAGE, OR OTHER DAMAGES
					OR EXPENSES RESULTING THEREFROM. INVITEBIG AND ITS AFFILIATES HAVE NO LIABILITY AND WILL MAKE NO REFUND IN THE EVENT OF CANCELLATION, OVERBOOKING, STRIKE,
					FORCE MAJEURE OR OTHER CAUSES BEYOND THEIR DIRECT CONTROL, AND THEY HAVE NO RESPONSIBILITY FOR ANY ADDITIONAL EXPENSE OR OMISSIONS OR TO COLLECT ANY
					ADDITIONAL FEES OR COSTS FROM ANY CUSTOMER EXCEPT AS OTHERWISE EXPRESSLY PROVIDED IN THIS AGREEMENT.
				</p>
				<p>
					IN NO EVENT SHALL INVITEBIG AND/OR ITS RESPECTIVE SUPPLIERS BE LIABLE FOR ANY DIRECT, INDIRECT, PUNITIVE, INCIDENTAL, SPECIAL, OR CONSEQUENTIAL DAMAGES
					ARISING OUT OF, OR IN ANY WAY CONNECTED WITH, YOUR ACCESS TO, DISPLAY OF OR USE OF THIS SITE OR WITH THE DELAY OR INABILITY TO ACCESS, DISPLAY OR USE THIS
					SITE (INCLUDING, BUT NOT LIMITED TO, YOUR RELIANCE UPON OPINIONS APPEARING ON THIS SITE; ANY COMPUTER VIRUSES, INFORMATION, SOFTWARE, LINKED SITES,
					PRODUCTS, AND SERVICES OBTAINED THROUGH THIS SITE; OR OTHERWISE ARISING OUT OF THE ACCESS TO, DISPLAY OF OR USE OF THIS SITE) WHETHER BASED ON A THEORY OF
					NEGLIGENCE, CONTRACT, TORT, STRICT LIABILITY, OR OTHERWISE, AND EVEN IF THE INVITEBIG.COM AND/OR THEIR RESPECTIVE SUPPLIERS HAVE BEEN ADVISED OF THE
					POSSIBILITY OF SUCH DAMAGES.
				</p>
				<p>
					If, despite the limitation above, InviteBIG or any of its respective suppliers is found liable for any loss or damage which arises out of or in any way
					connected with any of the occurrences described above, then InviteBIG and/or its respective suppliers liabilities will in no event exceed, in the
					aggregate, the greater of (a) the transaction fees assessed for your transactions on the Site, or (b) One-Hundred Dollars (US$100.00).
				</p>
				<p>
					The limitation of liability reflects the allocation of risk between the parties. The limitations specified in this section will survive and apply even if
					any limited remedy specified in these terms is found to have failed of its essential purpose. The limitations of liability provided in these terms inure to
					the benefit of InviteBIG and/or its respective suppliers.
				</p>
				<br><p>
					<h3>Indemnification</h3>
				</p>
				<p>
					You agree to indemnify and hold harmless InviteBIG, its affiliates, and any of its officers, directors, members, managers, employees and agents from and
					against any claims, causes of action, demands, recoveries, losses, damages, fines, penalties or other costs or expenses of any kind or nature including but
					not limited to reasonable legal and accounting fees, brought by third parties as a result of:
				</p>
				<ul>
					<li>
						your breach of this Agreement or the documents referenced herein;
					</li>
					<li>
						your violation of any law or the rights of a third party;
					</li>
					<li>
						your access to or use of the Services or Collective Content or your violation of these Terms;
					</li>
					<li>
						your Customer Content;
					</li>
					<li>
						information provided by you with regard to a Venue;
					</li>
					<li>
						your booking of a Venue ;
					</li>
					<li>
						your use of a Venue by you or your guests, invitees or visitors; or
					</li>
					<li>
						your interaction with any Customer or Venue Agent.
					</li>
				</ul>		
				<br><p>
					<h3>Binding Arbitration</h3>
				</p>
				<p>
					YOU AND INVITEBIG AGREE THAT ANY DISPUTE, CLAIM OR CONTROVERSY ARISING OUT OF OR RELATING TO THESE TERMS OR THE BREACH, TERMINATION, ENFORCEMENT,
					INTERPRETATION OR VALIDITY THEREOF, OR TO THE USE OF THE SERVICE OR USE OF THE SITE (COLLECTIVELY, "DISPUTES") WILL BE SETTLED BY BINDING ARBITRATION
					CONDUCTED IN KING COUNTY, IN THE STATE OF WASHINGTON, BY A SINGLE NEUTRAL ARBITRATOR, EXCEPT THAT EACH PARTY RETAINS THE RIGHT TO SEEK INJUNCTIVE OR OTHER
					EQUITABLE RELIEF IN A COURT OF COMPETENT JURISDICTION TO PREVENT THE ACTUAL OR THREATENED INFRINGEMENT, MISAPPROPRIATION OR VIOLATION OF A PARTY'S
					COPYRIGHTS, TRADEMARKS, TRADE SECRETS, PATENTS, OR OTHER INTELLECTUAL PROPERTY RIGHTS. YOU ACKNOWLEDGE AND AGREE THAT YOU AND INVITEBIG ARE EACH WAIVING
					THE RIGHT TO A TRIAL BY JURY OR TO PARTICIPATE AS A PLAINTIFF OR CLASS MEMBER IN ANY PURPORTED CLASS ACTION OR REPRESENTATIVE PROCEEDING. FURTHER, UNLESS
					BOTH YOU AND INVITEBIG OTHERWISE AGREE IN WRITING, THE ARBITRATOR MAY NOT CONSOLIDATE MORE THAN ONE PERSON'S CLAIMS, AND MAY NOT OTHERWISE PRESIDE OVER ANY
					FORM OF ANY CLASS OR REPRESENTATIVE PROCEEDING. IF THIS SPECIFIC PARAGRAPH IS HELD UNENFORCEABLE, THEN THE ENTIRETY OF THIS "BINDING ARBITRATION" SECTION
					WILL BE DEEMED VOID, AND IN THAT CASE ANY CAUSE OF ACTION YOU MAY HAVE HEREUNDER OR WITH RESPECT TO YOUR USE OF THE SITE OR SERVICE MUST BE COMMENCED BY
					FILING SUIT IN KING COUNTY, WASHINGTON, WITHIN ONE (1) YEAR AFTER THE INCIDENT UPON WHICH THE CLAIM OR CAUSE OF ACTION IS BASED FIRST OCCURRED. EXCEPT AS
					PROVIDED IN THE PRECEDING SENTENCE, THIS "BINDING ARBITRATION" SECTION WILL SURVIVE ANY TERMINATION OF THESE TERMS.
				</p>
				<p>
					YOU AND WE EACH ALSO AGREE THAT THESE TERMS AFFECT INTERSTATE COMMERCE SO THE FEDERAL ARBITRATION ACT APPLIES INCLUDING WITH RESPECT TO ANY QUESTION OF
					WHETHER A CLAIM OR DISPUTE IS SUBJECT TO ARBITRATION (DESPITE THE CHOICE OF GOVERNING LAW IN THIS SECTION). YOU AND WE EACH AGREE WE WILL ONLY PURSUE
					ARBITRATION ON AN INDIVIDUAL BASIS AND WILL NOT PURSUE ARBITRATION IN A CLASS, CONSOLIDATED, OR REPRESENTATIVE BASIS, REGARDLESS OF THE APPLICATION OF
					PROCEDURAL RULES BY ANY ARBITRATOR. IF ANY COURT OR ARBITRATOR HOLDS THAT THE CLASS ACTION WAIVER IS UNENFORCEABLE, THEN THE DISPUTE MUST BE BROUGHT IN A
					STATE OR FEDERAL COURT IN KING COUNTY, WASHINGTON. YOU AND WE EACH ALSO AGREE THAT FOR ARBITRATED CLAIMS THE ARBITRATOR'S AWARD WILL BE FINAL AND BINDING
					AND MAY BE ENTERED AS A JUDGMENT IN ANY COURT OF COMPETENT JURISDICTION.
				</p>
				<p>
					(a) Arbitration Rules and Governing Law. The arbitration will be administered by the American Arbitration Association ("AAA") in accordance with the
					Commercial Arbitration Rules and the Supplementary Procedures for Consumer Related Disputes (the "AAA Rules") then in effect, except as modified by this
					"Dispute Resolution" section. (The AAA Rules are available at <a href="http://www.adr.org/arb_med">www.adr.org/arb_med</a> or by calling the AAA at
					1-800-778-7879.) The Federal Arbitration Act will govern the interpretation and enforcement of this section.
				</p>
				<p>
					(b) Arbitration Process. A party who desires to initiate arbitration must provide the other party with a written Demand for Arbitration as specified in the
					AAA Rules. The arbitrator will be either a retired judge
					or an attorney licensed to practice law in the state of Washington and will be selected by the parties from the AAA's roster of consumer dispute
					arbitrators. If the parties are unable to agree upon an arbitrator within seven (7) days of delivery of the Demand for Arbitration, then the AAA will
					appoint the arbitrator in accordance with the AAA Rules.
				</p>
				<p>
					(c) Arbitration Procedure. If your claim does not exceed $10,000, then the arbitration will be conducted solely on the basis of documents you and InviteBIG
					submit to the arbitrator, unless you request a hearing or the arbitrator determines that a hearing is necessary. If your claim exceeds $10,000, your right
					to a hearing will be determined by the AAA Rules. Subject to the AAA Rules, the arbitrator will have the discretion to direct a reasonable exchange of
					information by the parties, consistent with the expedited nature of the arbitration.
				</p>
				<p>
					(d) Arbitrator's Decision. The arbitrator will render an award within the time frame specified in the AAA Rules. The arbitrator's decision will include the
					essential findings and conclusions upon which the arbitrator based the award. Judgment on the arbitration award may be entered in any court having
					jurisdiction thereof. The arbitrator's award damages must be consistent with the terms of the "Limitation of Liability" section above as to the types and
					the amounts of damages for which a party may be held liable. The arbitrator may award declaratory or injunctive relief only in favor of the claimant and
					only to the extent necessary to provide relief warranted by the claimant's individual claim. If you prevail in arbitration you will be entitled to an award
					of attorneys' fees and expenses, to the extent provided under applicable law.
				</p>
				<p>
					(e) Fees. Your responsibility to pay any AAA filing, administrative and arbitrator fees will be solely as set forth in the AAA Rules. However, if your
					claim for damages does not exceed $75,000, InviteBIG will pay all such fees unless the arbitrator finds that either the substance of your claim or the
					relief sought in your Demand for Arbitration was frivolous or was brought for an improper purpose (as measured by the standards set forth in Federal Rule
					of Civil Procedure 11(b)).
				</p>
				<p>
					(f) Changes. Notwithstanding InviteBIG's right to modify these Terms, if InviteBIG changes this "Dispute Resolution" section after the date you first
					accepted these Terms (or accepted any subsequent changes to these Terms), you may reject any such change by sending us written notice (including by email
					to legal@InviteBIG.com) within 30 days of the date such change became effective, as indicated in the "Last Updated Date" above or in the date of
					InviteBIG's email to you notifying you of such change. By rejecting any change, you are agreeing that you will arbitrate any Dispute between you and
					InviteBIG in accordance with the provisions of this "Binding Arbitration" section as of the date you first accepted these Terms (or accepted any subsequent
					changes to these Terms).
				</p>
				<br><p>
					<h3>Software available on the Site</h3>
				</p>
				<p>
					Any software that is made available to download from the Site or used in the operation of the Site ("Software") is the copyrighted work of InviteBIG and/or
					its respective suppliers. Your use of such Software is governed by the terms of the end user license agreement, if any, which accompanies, or is included
					with, the Software ("License Agreement"). You may not install or use any Software that is accompanied by or includes a License Agreement unless you first
					agree to the License Agreement terms. For any Software made available for download on the Site not accompanied by a License Agreement, we hereby grant to
					you, the user, a limited, personal, nontransferable license to use the Software for viewing and otherwise using the Site in accordance with these terms and
					conditions and for no other purpose.
				</p>
				<p>
					Please note that all Software, including, without limitation, all HTML &amp; CSS code, PHP, Javascript or AJAX, including databases and algorithms,
					contained in the Services is owned by InviteBIG and/or its respective suppliers, and is protected by copyright laws and international treaty provisions.
					Any reproduction or redistribution of the Software is expressly prohibited, and may result in severe civil and criminal penalties. Violators will be
					prosecuted to the maximum extent possible.
				</p>
				<p>
					WITHOUT LIMITING THE FOREGOING, COPYING OR REPRODUCTION OF THE SOFTWARE TO ANY OTHER SERVER OR LOCATION FOR FURTHER REPRODUCTION OR REDISTRIBUTION IS
					EXPRESSLY PROHIBITED. THE SOFTWARE IS WARRANTED, IF AT ALL, ONLY ACCORDING TO THE TERMS OF THE LICENSE AGREEMENT.
				</p>
				<br><p>
					<h3>Copyrights and trademark notices</h3>
				</p>
				<p>
					All contents of this Site are copyrighted &copy; 2013-2016 InviteBIG, Inc. All rights reserved. InviteBIG is not responsible for content on websites operated by
					parties other than InviteBIG. Other product and company names mentioned herein may be the trademarks of their respective owners.
				</p>
				<p>
					If you are aware of an infringement of our brand, please let us know by emailing us at trademark@InviteBIG.com. We only address messages concerning brand
					infringement at this email address.
				</p>
				<br><p>
					<h3>Governing law</h3>
				</p>
				<p>
					The Site is operated by a U.S. entity and this Agreement is governed by the laws of the State of Washington, USA. You hereby consent to the exclusive
					jurisdiction and venue of courts in King County, Washington, USA, in all disputes arising out of or relating to the use of the Site. Use of the Site is
					unauthorized in any jurisdiction that does not give effect to all provisions of these terms and conditions, including, without limitation, this paragraph.
				</p>
				<p>
					Our performance of this Agreement is subject to existing laws and legal process, and nothing contained in this Agreement limits our right to comply with
					law enforcement or other governmental or legal requests or requirements relating to your use of the Site or information provided to or gathered by us with
					respect to such use. To the extent allowed by applicable law, you agree that you will bring any claim or cause of action arising from or relating to your
					access or use of the Site within two (2) years from the date on which such claim or action arose or accrued or such claim or cause of action will be
					irrevocably waived.
				</p>
				<p>
					If any part of this Agreement is determined to be invalid or unenforceable pursuant to applicable law including, but not limited to, the warranty
					disclaimers and liability limitations set forth above, then the invalid or unenforceable provision will be deemed superseded by a valid, enforceable
					provision that most closely matches the intent of the original provision and Agreement shall continue in effect.
				</p>
				<br><p>
					<h3>Contact us</h3>
				</p>
				<p>
					If you have any questions about this Agreement, please contact us at support@invitebig.com.
				</p>
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
