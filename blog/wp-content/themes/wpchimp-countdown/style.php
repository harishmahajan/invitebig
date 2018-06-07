<?php
function wpchimpcs_HSV_to_RGB($c1) {
    $sat = array();
    $c2 = array();

    while ($c1['h'] < 0)
        $c1['h'] += 360;
    while ($c1['h'] > 360)
        $c1['h'] -= 360;

    if ($c1['h'] < 120) {
        $sat['r'] = (120 - $c1['h']) / 60.0;
        $sat['g'] = $c1['h'] / 60.0;
        $sat['b'] = 0;
    } else if ($c1['h'] < 240) {
        $sat['r'] = 0;
        $sat['g'] = (240 - $c1['h']) / 60.0;
        $sat['b'] = ($c1['h'] - 120) / 60.0;
    } else {
        $sat['r'] = ($c1['h'] - 240) / 60.0;
        $sat['g'] = 0;
        $sat['b'] = (360 - $c1['h']) / 60.0;
    }
    $sat['r'] = min($sat['r'], 1);
    $sat['g'] = min($sat['g'], 1);
    $sat['b'] = min($sat['b'], 1);

    $c2['r'] = (1 - $c1['s'] + $c1['s'] * $sat['r']) * $c1['v'];
    $c2['g'] = (1 - $c1['s'] + $c1['s'] * $sat['g']) * $c1['v'];
    $c2['b'] = (1 - $c1['s'] + $c1['s'] * $sat['b']) * $c1['v'];

    return $c2;
}

function wpchimpcs_RGB_to_HSV($c1) {
    $c2 = array();

    $themin = min($c1['r'],min($c1['g'], $c1['b']));
    $themax = max($c1['r'],max($c1['g'], $c1['b']));
    $delta = $themax - $themin;

    $c2['v'] = $themax;
    $c2['s'] = 0;
    if ($themax > 0)
        $c2['s'] = $delta / $themax;
    $c2['h'] = 0;

    if($delta > 0) {
        if ($themax == $c1['r'] && $themax != $c1['g'])
            $c2['h'] += ($c1['g'] - $c1['b']) / $delta;
        if ($themax == $c1['g'] && $themax != $c1['b'])
            $c2['h'] += (2 + ($c1['b'] - $c1['r']) / $delta);
        if ($themax == $c1['b'] && $themax != $c1['r'])
            $c2['h'] += (4 + ($c1['r'] - $c1['g']) / $delta);
        $c2['h'] *= 60;
    }
    return $c2;
}

function wpchimpcs_hex_to_RGB($hex) {
$hex = str_replace("#", "", $hex);

   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
      $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
      $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
   } else {
      $r = hexdec(substr($hex, 0, 2));
      $g = hexdec(substr($hex, 2, 2));
      $b = hexdec(substr($hex, 4, 2));
   }
   
   return array('r' => $r, 'g' => $g, 'b' => $b);
}

function wpchimpcs_RGB_to_hex($rgb) {
   $hex = "#";
   $hex .= str_pad(dechex($rgb['r']), 2, "0", STR_PAD_LEFT);
   $hex .= str_pad(dechex($rgb['g']), 2, "0", STR_PAD_LEFT);
   $hex .= str_pad(dechex($rgb['b']), 2, "0", STR_PAD_LEFT);
   
   return $hex;
}

function wpchimpcs_lighten($c, $q) {
    $c = wpchimpcs_hex_to_RGB($c);
    $hsv = wpchimpcs_RGB_to_HSV($c);
    $hsv['v'] += 255 * $q;
    $hsv['v'] = max(0, min(255, $hsv['v']));
    $m = wpchimpcs_HSV_to_RGB($hsv);
    return wpchimpcs_RGB_to_hex($m);   
}

function wpchimpcs_darken($c, $q) {
    $c = wpchimpcs_hex_to_RGB($c);
    $hsv = wpchimpcs_RGB_to_HSV($c);
    $hsv['v'] -= 255 * $q;
    $hsv['v'] = max(0, min(255, $hsv['v']));
    $m = wpchimpcs_HSV_to_RGB($hsv);
    return wpchimpcs_RGB_to_hex($m);   
}

function wpchimpcs_get_inline_style() {
    $style = '';
    
    $counter_glow = get_theme_mod('wpchimpcs_counter_glow');
    if($counter_glow) {
        $style .= ".landing .counter { text-shadow: 0px 0px 50px {$counter_glow}; }";
    }
    
    $links = get_theme_mod('wpchimpcs_link_color');
    if($links) {
        $style .= "a { color: {$links}; }";
        
        $style .= 'a:hover { color: ' . wpchimpcs_lighten($links, 0.1) . '; }';
        $style .= 'a:visited { color: ' . wpchimpcs_darken($links, 0.05) . '; }';
    }
    
    $bg = get_theme_mod('wpchimpcs_background_color');
    if($bg) {
        $style .= "body { background-color: {$bg}; }";
        
        $border = wpchimpcs_darken($bg, 0.4);
        
        $rgb = wpchimpcs_hex_to_RGB($bg);
        $l = ($rgb['r'] + $rgb['g'] + $rgb['b']) / 3.0;
        if($l < 255 * 0.3) {
            $border = wpchimpcs_lighten($bg, 0.6);
        }
        
        $style .= ".landing .counter .part { border-color: {$border}; }";
    }
    
    $fg = get_theme_mod('wpchimpcs_foreground_color');
    if($fg) {
        $style .= "body { color: {$fg}; }";
        for($i = 1; $i <= 6; ++$i)
            $style .= "h{$i} a, h{$i} a:hover, h{$i} a:visited { color: {$fg}; }";
        
        $style .= ".blog-title { color: {$fg}; }";
        $style .= ".footer-info a.muted { color: {$fg} !important; }";
        $style .= '.post-meta { color: ' . wpchimpcs_lighten($fg, 0.4) . '; }';
    }
    
    return $style;
}

