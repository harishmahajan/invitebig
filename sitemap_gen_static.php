<?php
	$out = "<?xml version='1.0' encoding='UTF-8'?>\n<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9' " .
			"xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.sitemaps.org/schemas/sitemap/0.9 " .
            "http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd'>\n";
	
	$arr = glob("assets/content/*.*",GLOB_MARK);
	$arr2 = glob("assets/content/thumbnails*.*",GLOB_MARK);
	
	for ($i=0; $i<count($arr); $i++)
	{
		$out .= "<url><loc>/assets/content/".urlencode(str_replace("assets/content/","",$arr[$i]))."</loc><changefreq>weekly</changefreq></url>\n";
	}
	
	for ($i=0; $i<count($arr2); $i++)
	{
		$out .= "<url><loc>/assets/content/thumbnails/".urlencode(str_replace("assets/content/thumbnails","",$arr2[$i]))."</loc><changefreq>weekly</changefreq></url>\n";
	}
	
	$out .= "</urlset>\n";
	
	$file = fopen("assets/content/sitemap-static.xml","w");
	fwrite($file,$out);
	fclose($file);
?>
