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
        
        <title>Pricing | InviteBIG</title>
		<meta name="description" content="Learn about InviteBIG's pricing"/>
		
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
            </header>


            <section class="top-text text-center">
                <div class="container">
                    <div class="hero-unit">
                      <h2>Product Pricing</h2>
                      <p class="lead muted">Simple pricing with no hidden costs.</p>
                    </div>
                </div>
            </section>

            <section class="price-4">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-6 col-sm-offset-3">
                            <a class="btn btn-huge" href="/register/venue"> <div class="span5 plan">
                                <div class="title">Standard Plan</div>
                                <div class="price"><span class="currency">$</span>40<span class="period">/Month</span><br>
                                  <p>Billed annually or </br> $49 month-to-month</p>
                            </div>
                            <div class="description">
                                <div class="description-box">
                                <span class="fui-location"></span> <strong>1 venue</strong> account
                                </div> 
                                <div class="description-box">
                                <span class="fui-user"></span> <strong>unlimited</strong> users
                                </div>
                                <div class="description-box">
                                <span class="fui-home"></span> <strong>unlimited</strong> spaces
                                </div>
                                <div class="description-box"><span class="fui-plus-inverted"></span> <strong>unlimited</strong> addons
                                </div>
                                <div class="description-box">
                                <span class="fui-calendar"></span> <strong>unlimited</strong> bookings
                                </div>
                                <div class="description-box">
                                <span class="fui-window"></span> <strong>white-label</strong> integration
                                </div>
                            </div>
                            <a class="btn btn-huge" href="/register/venue">Start Your Free Trial</a></div>
                        </div>
                        <!--
                        <div class="col-sm-6 col-sm-offset-1 description-detail">
                            <div class="row">
                                <div class="col-sm-6">
                                    <span class="fui-list"></span>
                                  <h6>Get Listed on the Web</h6>
                                    Without iconic symbols really finish your work - for clearly understand. </div>
                                <div class="col-sm-6">
                                    <span class="fui-calendar"></span>
                                  <h6>Automate Event Booking</h6>
                                    Easy to change and easy to create new elements with color swatches. </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <span class="fui-credit-card"></span>
                                  <h6>Accept Online Payments</h6>
                                    simple 4.9% transaction fee </div>
                                <div class="col-sm-6">
                                    <span class="fui-mic"></span>
                                  <h6>Improve Communication</h6>
                                    Easy to change and easy to create new elements with color swatches. </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                <p>Click here to <a href="">view a full list of features</a></p>   
                                </div>
                            </div>
                        </div>
                        -->
                    </div>
            </section>
            <section id="pricing-custom" class="content-5 bg-blue">
                <div class="container">
                    <div class="row">
                        <div style="text-align:right;padding-right:20px;" class="col-md-3 col-sm-2 hidden-xs"><i style="font-size:78px;" class="fui-new"></i>
                        </div>
                        <div class="col-md-8 col-sm-10">
                          <h2>Custom Requirements?</h2>
                          <p>Even though our software is fully customizable, no software is perfect. With this in mind, we offer venues that sign up for an annual subscription a chance to send us their requirements and we will do our best to incorporate them at no cost.</p>
                          <a class="btn btn-primary" href="/contact">Send us your Requirements</a>
                        </div>
                    </div>
                </div>
            </section>
                    <section id="pricing-faq" class="price-4">
                <div class="container">
                    <div class="row center-text">
                        <div class="col-sm-10 col-sm-offset-1">
                            <h2>Frequently Asked Questions</h2>
                            <p class="lead muted">Find answers to the most common questions.</p>
                        </div>
                    </div>
                    <br />
                    <br />
                    <div class="row features">
                        <div class="col-sm-6">
                          <h4>Q: What Comes with Unlimited Booking?</h4>
                          <p>Yes, it is true, we offer no limits on booking events through InviteBIG. Not only can your staff and customers make as many bookings desired, we are also allow bookings to include catering, amentities, event packages, and other services. Whatever your venue wants your customers to book online, InviteBIG can make it happen in most cases. </p>
                            <br />
                            <br />
                          <h4>Q: Can My Venue Still Use InviteBIG Without Accepting Payments Through the Website?</h4
                            ><p>Yes, InviteBIG allows venues to manually enter offline payments made by customers. Just by selecting the "Record Payment" option, your venue can easily keep track of payments taken / accepted outside of InviteBIG.com, without incuring a payment processing fee. </p>
                          <p>However, the benefit of using InviteBIG for online payments is it provide a high-level of security, accuracy and convience to your customer. InviteBIG can collect the online payment from your customers, either all upfront or on a payment schedule, handle currency exchanges and work seamlessly with just about every debit or credit card around the world. &nbsp; </p>
                          <br />
                            <br />
                            <h4>Q: Is There a Discount for More Than one Venue?</h4>
                            <p>Yes, we can offer most companies with multple venues a discount. Please contact us and let us know a bit about your venues and how we can help.</p>
                            <br />
                            <br />
                            <h4>Q: Why Does InviteBIG Offer Free Custom Development?</h4>
                            <p>The reason we don't charge for custom development is because we believe in building unique and lasting partnership with our customers. The best way we can do this is by developing new features for our customers need in exchange for their committment to use our service for at least a year. It's a win-win scenario. Not only will our product improve over time and be tailored to our customers specific needs, but InviteBIG will become intimately tied to our customers' success. </p>
                            <p><a href="/contact">Please reach out to us with your requirements</a> and we will go over them with you at your earliest convenience.</p>
                            <br />
                            <br />
                             </div>
                        <div class="col-sm-6">
                          <h4>Q: How Does my Venue get Paid?</h4>
                          <p><strong>All online payments made through InviteBIG, minus a processing fee, will be sent to your venue via Stripe (immediately)</strong>, wire transfer (5-7 business days), or by check (in 3-5 business days). International wire transfers may take longer to process.</p>
                         
                          <p>For example, if your customer books an event at your venue for $100, InviteBIG will charge your region's tax rate (e.g. 10%) and an additonal 4.9% processing fee. The customer will end up paying $115.39 in total of which $110 will be sent directly to your venue and the remaining $5.39 will go to InviteBIG for maintaining the website and covering any merchant fees. &nbsp; </p>
                          <br />
                            <br />
                            <h4>Q: What is the Payment Processing Fee?</h4>
                            <p>Our 4.9% transaction fee is added on top of the booking costs and is used to cover payment process fees, chargebacks and other merchant fees. Your customers only have to pay this fee when making an online payment through InviteBIG.</p>
                            <br />
                            <br />
                            <h4>Q: What Currencies are Supported on InviteBIG?</h4>
                            <p>InviteBIG supports most major currencies of your customers, thanks to our secure payment processor, Stripe. Please click on this links to view all of our <a href="https://support.stripe.com/questions/which-currencies-does-stripe-support#currencygroup1">Supported Currenies by Country</a></p>
                            <p>InviteBIG can also help your venue accept all major international debit or credit cards, including Visa, MasterCard, American Express, and Discover.</p>
                            <br />
                            <br />
                            <h4>Q: How do I Signup for a Stripe Account?</h4>
                            <p>To get started creating your venue's Stripe account, <a href="https://www.stripe.com">please go to Stripe.com and register</a>.</p>
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
                         <div class="signup-form">
                            <a href="/register/venue">
                                    <button type="submit" class="btn btn-primary" href="/register/venue">Start Your Free Trial</button></a>
                        </div>
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
    95a5a6
    </body>
</html>
