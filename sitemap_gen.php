<?php
	require("php/functions.php");
	
	$out = "<?xml version='1.0' encoding='UTF-8'?>\n<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9' " .
			"xmlns:image='http://www.google.com/schemas/sitemap-image/1.1' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.sitemaps.org/schemas/sitemap/0.9 " .
            "http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd'>\n";
			
	$cities = array();
	$cities[] = "all";
	$vtypes = array();
	$vtypes[] = "all";
	$types = array();
	$url = array();
	$urls = array();
	$venues = array();
	$txt = array();
	
	$urls[] = "";
	//$urls[] = "/online-event-booking";
	//$urls[] = "/online-booking-system";
	//$urls[] = "/venue-management-software";
	/////$urls[] = "/request-a-demo";
	$urls[] = "/venues";
	$urls[] = "/login";
	$urls[] = "/register";
	$urls[] = "/register/venue";
	
	//////// write these before proceeding to venues
	for ($i=0; $i<count($urls); $i++)
	{
		$out .= "<url><loc>".$urls[$i]."</loc><changefreq>daily</changefreq></url>\n";
		$txt[] = $urls[$i];
	}
	$urls = array();
	
	////////  Get venues and images and insert into $out
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT venueid, shorturl, searchstring FROM venues WHERE visibility = 'public' AND status != 'deleted' ORDER BY shorturl ASC"))
	{
		$stmt->execute();
		$stmt->bind_result($i,$u,$s);
		while ($stmt->fetch())
		{			
			$venues[] = array('id'=>$i,'url'=>$u,'searchstring'=>$s,'images'=>array());
		}
		$GLOBALS['db']->CloseConn();
	}
	
	for ($i=0; $i<count($venues); $i++)
	{
		$stmt = $GLOBALS['db']->stmt_init();
		if ($stmt->prepare("SELECT photo,caption FROM venues_photos WHERE venueid = ? ORDER BY placement ASC"))
		{
			$stmt->bind_param("i",$venues[$i]['id']);
			$stmt->execute();
			$stmt->bind_result($p,$c);
			while ($stmt->fetch())
			{			
				$venues[$i]['images'][] = array('url'=>"/assets/content/".$p,'caption'=>$c);
			}
			$GLOBALS['db']->CloseConn();
		}
	}
	
	for ($i=0; $i<count($venues); $i++)
	{
		$out .= "<url><loc>/venue/".$venues[$i]['url']."</loc>\n";
		for ($i2=0; $i2<count($venues[$i]['images']); $i2++)
			$out .= "<image:image>\n<image:loc>" . $venues[$i]['images'][$i2]['url'] . "</image:loc>\n" .
					"<image:caption>" . $venues[$i]['images'][$i2]['caption'] . "</image:caption>\n</image:image>\n";
		$out .= "<changefreq>daily</changefreq></url>\n";
		
		$out .= "<url><loc>/reserve/book-an-event-at-".$venues[$i]['url']."</loc>\n<changefreq>daily</changefreq></url>\n";
		$out .= "<url><loc>/venues/".$venues[$i]['searchstring']."</loc>\n<changefreq>daily</changefreq></url>\n";
		
		$txt[] = "/venue/" . $venues[$i]['url'];
		$txt[] = "/venues/".$venues[$i]['searchstring'];
		$txt[] = "/reserve/book-an-event-at-" . $venues[$i]['url'];
	}
	
	///////////// Venues written, proceed to directory listing URLs
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT DISTINCT CONCAT(city,'-',state) FROM venues ORDER BY city ASC"))
	{
		$stmt->execute();
		$stmt->bind_result($s);
		while ($stmt->fetch())
		{
			$cities[] = strtolower(str_replace(" ","-",$s));
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT CONCAT(city,'-',state) FROM venues ORDER BY city ASC"))
	{
		$stmt->execute();
		$stmt->bind_result($a);
		while ($stmt->fetch())
		{
			$cities[] = strtolower(str_replace(" ","-",$a));
		}
		$GLOBALS['db']->CloseConn();
	}
	$cities = array_values(array_unique($cities));
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT DISTINCT name FROM venues_types ORDER BY name ASC"))
	{
		$stmt->execute();
		$stmt->bind_result($s);
		while ($stmt->fetch())
		{
			$vtypes[] = strtolower(str_replace(" ","-",$s));
		}
		$GLOBALS['db']->CloseConn();
	}
	
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT DISTINCT name FROM resources_types ORDER BY name ASC"))
	{
		$stmt->execute();
		$stmt->bind_result($s);
		while ($stmt->fetch())
		{
			$types[] = strtolower(str_replace(" ","-",$s));
		}
		$GLOBALS['db']->CloseConn();
	}
		
	$stmt = $GLOBALS['db']->stmt_init();
	if ($stmt->prepare("SELECT name FROM features ORDER BY name ASC"))
	{
		$stmt->execute();
		$stmt->bind_result($s);
		while ($stmt->fetch())
		{
			$types[] = strtolower(str_replace(" ","-",$s));
		}
		$GLOBALS['db']->CloseConn();
	}
	$types = array_values(array_unique($types));
	
	for ($i=0; $i<count($cities); $i++)
	{
		$url[] = $cities[$i];
		
		for ($i3=0; $i3<count($vtypes); $i3++)
		{
			$url[] = $cities[$i]."/".$vtypes[$i3];
			
			/*
			// DISABLED FOR NOW, UNTIL WE HAVE MORE VENUES
			for ($i2=0; $i2<count($types); $i2++)
				$url[] = $cities[$i]."/".$vtypes[$i3]."/type/".$types[$i2];
			*/
		}
	}
	$url = array_values(array_unique($url));
	
	for ($i=0; $i<count($url); $i++)
		$urls[] = "/venues/".$url[$i];
	
	$urls[] = "/pricing";
	$urls[] = "/contact";
	$urls[] = "/privacy";
	$urls[] = "/terms";
	$urls[] = "/help";
	$urls[] = "/forgot";
	//$urls[] = "/sitemap";
	
	for ($i=0; $i<count($urls); $i++)
	{
		$out .= "<url><loc>".$urls[$i]."</loc><changefreq>daily</changefreq></url>\n";
		$txt[] = $urls[$i];
	}
	
	$out .= "</urlset>\n";
	
	$file = fopen("./sitemap.xml","w");
	fwrite($file,$out);
	fclose($file);
	
	/*
	// not needed, the problem is that phantomjs caches the partial file, so phantom/prerender need
	//   to be restarted to clear that cache.  PhantomJS2 has page.clearMemoryCache(), but we're not using v2
	
	// find all CSS and JS files to add to the prerender.txt file
	$txt2 = array();
	$dir = new RecursiveDirectoryIterator("/var/www/invitebig");
	$it = new RecursiveIteratorIterator($dir);
	$files = new RegexIterator($it, "/(\.css)|(\.js)|(.html)/");
	foreach ($files as $file)
	{
			if ($file->isFile())
			{
					$txt2[] = str_replace("/var/www/invitebig/","/",$file->getPathname());
			}
	}
	*/
		
	$txtStr = "";
	/*
	for ($i=0; $i<count($txt2); $i++)
	{
		$txtStr .= $txt2[$i] . "\n";
	}
	*/
	for ($i=0; $i<count($txt); $i++)
	{
		$txtStr .= $txt[$i] . "\n";
	}
	$file2 = fopen("./prerender.txt","w");
	fwrite($file2,$txtStr);
	fclose($file2);
?>