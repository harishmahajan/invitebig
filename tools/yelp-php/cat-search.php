#!/usr/bin/php
<?php

// https://www.yelp.com/developers/documentation/v2/all_category_list
// https://www.yelp.com/developers/documentation/v2/search_api

require_once('lib/OAuth.php');

$CONSUMER_KEY = "Ug11VpLLWZ12zGuCTotzXQ";
$CONSUMER_SECRET = "4wimKeg3mETrW0_G9a7H26SX7zc";
$TOKEN = "MLeYijUVGy1xbfAaT-YXf-1NFesXmOpE";
$TOKEN_SECRET = "VyclS1OaNhgr1iWseHABhmhukpI";


$API_HOST = 'api.yelp.com';
$SEARCH_PATH = '/v2/search/';

function request($host, $path) {
    $unsigned_url = "http://" . $host . $path;
    // Token object built using the OAuth library
    $token = new OAuthToken($GLOBALS['TOKEN'], $GLOBALS['TOKEN_SECRET']);
    // Consumer object built using the OAuth library
    $consumer = new OAuthConsumer($GLOBALS['CONSUMER_KEY'], $GLOBALS['CONSUMER_SECRET']);
    // Yelp uses HMAC SHA1 encoding
    $signature_method = new OAuthSignatureMethod_HMAC_SHA1();
    $oauthrequest = OAuthRequest::from_consumer_and_token(
        $consumer, 
        $token, 
        'GET', 
        $unsigned_url
    );
    
    // Sign the request
    $oauthrequest->sign_request($signature_method, $consumer, $token);
    
    // Get the signed URL
    $signed_url = $oauthrequest->to_url();
    
    // Send Yelp API Call
    $ch = curl_init($signed_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    
    return $data;
}

/* // USA

$y1 = 30.3;
$x1 = -125.415303;
$y2 = 49.393988;
$x2 = -66.510308;
*/

 /*// North America
$y1 = 8.6;
$x1 = -168.5;
$y2 = 71.9;
$x2 = -50.4;
*/

/* // South America
$y1 = -56.4;
$x1 = -91;
$y2 = 15.39;
$x2 = -31;
*/

 // W Europe
$y1 = 36.5;
$x1 = -27;
$y2 = 71.1;
$x2 = 35.4;


/* // Russia
$y1 = 54.4;
$x1 = 31.8;
$y2 = 77.6;
$x2 = 170;
*/

/* // Africa
$y1 = -36.8;
$x1 = -20;
$y2 = 33.8;
$x2 = 37.1;
*/

/* // Asia
$y1 = 4.5;
$x1 = 33.5;
$y2 = 53;
$x2 = 147;
*/

/* // Australia
$y1 = -48.5;
$x1 = 89;
$y2 = 7;
$x2 = 177;
*/

/* // World
$y1 = 0;
$x1 = 0;
$y2 = 100;
$x2 = 100;
*/

$stepx = 3;
$stepy = 3;
$total = 0;

$c = ['venues','weddingchappels','amusementparks','aquariums','museums','musicvenues','theater','bowling','stadiumsarenas','lounges','galleries','hotels','dancestudio','collegeuniv','recreation','arcades','zoos','gardens','churches','danceclubs','bars','parks','venues,weddingchappels,amusementparks,aquariums,museums,musicvenues,theater,bowling,stadiumsarenas,lounges,galleries,hotels,dancestudio,collegeuniv,recreation,arcades,zoos,gardens,churches,danceclubs,bars,parks'];
//$c = ['venues,weddingchappels,amusementparks,aquariums,museums,musicvenues,theater,bowling,stadiumsarenas,lounges,galleries,hotels,dancestudio,collegeuniv,recreation,arcades,zoos,gardens,churches,danceclubs,bars,parks'];

$totals = array();

for ($i = 0; $i < count($c); $i++)
{
	$tot = 0;
	
	for ($ix = $x1; $ix < $x2; $ix += $stepx)
	{
		for ($iy = $y1; $iy < $y2; $iy += $stepy)
		{
			$sx = $ix + $stepx;
			$sy = $iy + $stepy;
			if ($sx > $x2) $sx = $x2;
			if ($sy > $y2) $sy = $y2;
				
			$url_params = array();
			$url_params['bounds'] = $iy.",".$ix."|".$sy.",".$sx;
			$url_params['category_filter'] = $c[$i];
			$url_params['limit'] = 1;
			
			$res = request($GLOBALS['API_HOST'],$GLOBALS['SEARCH_PATH'] . "?" . http_build_query($url_params));
			$j = json_decode($res,true);
			
			if (isset($j['total']))
				$tot += $j['total'];
			
			$p = ((($ix - $x1) / ($x2 - $x1)) + $i) / count($c);
			
			echo "\r" . (floor($p * 1000)/10) . "%" . " - " . $tot;
		}
	}
	
	$totals[$c[$i]] = $tot;
}

foreach ($totals as $key=>$t)
{
	echo "\r\n".$key." ".$t;
}

?>

