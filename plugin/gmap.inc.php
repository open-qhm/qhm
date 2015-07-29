<?php
/**
 *   Google Map Plugin
 *   -------------------------------------------
 *   gmap.inc.php
 *
 *   Copyright (c) 2012 hokuken
 *   http://hokuken.com/
 *
 *   created  : 14/08/05
 *   modified :
 *
 *   Description
 *   
 *   
 *   Usage :
 *   
 */

function plugin_gmap_convert()
{
	$qt = get_qt();

	$args = func_get_args();
	$spots = array_pop($args);
	
	$list_class = 'list';
	$width = '100%';
	$height = '';
	$zoom = '';
	foreach ($args as $arg)
	{
		$arg = trim($arg);
		if (strpos($arg, 'zoom=') === 0)
		{
			if (preg_match('/([0-9]+)/', $arg, $matches))
			{
				$zoom = $matches[1];
			}
			continue;
		}
		if (preg_match('/^([0-9]+)x([0-9]+)$/', $arg, $matches))
		{
			$width = $matches[1].'px';
			$height = $matches[2].'px';
			continue;
		}
		if (preg_match('/^([0-9.]+(px|%)?)$/', $arg, $matches))
		{
			$arg = is_numeric($matches[1]) ? $matches[1].'px' : $matches[1];
			$height = $arg;
			continue;
		}
		
		switch($arg)
		{
			case 'none':
			case 'hide':
				$list_class = 'hide';
				break;
			case 'right':
			case 'after':
				$list_class = 'after';
				break;
			case 'left':
			case 'before':
				$list_class = 'before';
				break;
			case 'list':
				break;
		}
	}
	
	// マーカーの取得
	$markers = plugin_gmap_get_markers($spots);
	
	plugin_gmap_set_js();
	
	$cnt = 1;
	$marker_html = '';
	$marker_html .= '<div class="col-sm-12">';
	foreach ($markers as $marker)
	{
		$marker_html .= '<dl data-mapping="m_'.$cnt.'" data-lat="'.$marker['lat'].'" data-lng="'.$marker['lng'].'"><div class="info-box"><dt>'.$marker['label'].'</dt><dd>'.$marker['content'].'</dd></div></dl>';
		$cnt++;
	}
	$marker_html .= '</div>';

	$html = '
<div class="qhm-plugin-gmap container-fluid">
	<div id ="map_canvas" data-map-width="'.$width.'" data-map-height="'.$height.'" data-map-zoom="'.$zoom.'" class="qhm-plugin-gmap-map pull-left"></div>
	<div class="qhm-plugin-gmap-markers gmap-markers pull-left" data-list-type="'.$list_class.'">
		'.$marker_html.'
	</div>
</div>
';

	return $html;
}

function plugin_gmap_set_js()
{
	$qt = get_qt();

	$plugin_script = '
<script type="text/javascript" src="//maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript" src="'.PLUGIN_DIR.'gmap/jquery.gmap.js"></script>
<script type="text/javascript">
$(function(){
	$(".qhm-plugin-gmap").gmap();
});
</script>
';

	$qt->appendv_once('plugin_gmap_script', 'lastscript', $plugin_script);
	
	$plugin_head = '
	<link rel="stylesheet" href="'.PLUGIN_DIR.'gmap/gmap.css">
';
	$qt->appendv_once('plugin_gmap_style', 'beforescript', $plugin_head);

}

function plugin_gmap_get_markers($spots)
{
	$markers = array();
	$spots = str_replace("\r", "\n", str_replace("\r\n", "\n", $spots));
	$spots = explode("\n", $spots);
	
	setlocale(LC_ALL, 'ja_JP.UTF-8');

	foreach ($spots as $spot)
	{
		if (strlen(trim($spot)) == 0)
		{
			continue;
		}

		$mapdata = str_getcsv(trim($spot), ',', '"');
		
		// アドレスから軽度、緯度を取得する
		$geoobj = plugin_gmap_getGeocoding($mapdata[0]);
		
		$lng = $geoobj ? $geoobj['lng'] : '135.505969';
		$lat = $geoobj ? $geoobj['lat'] : '34.77114';
		if (isset($mapdata[3]) && $mapdata[3] != '')
		{
			$lng = $mapdata[3];
		}
		if (isset($mapdata[4]) && $mapdata[4] != '')
		{
			$lat = $mapdata[4];
		}
		
		$markers[] = array(
			'label' => $mapdata[1],
			'content' => $mapdata[2],
			'lng' => $lng,
			'lat' => $lat,
		);
	}
	
	return $markers;
}

function plugin_gmap_getGeocoding($address)
{ 
    // 引数が空の場合、空の配列を返す
    if (empty($address)) {
        return FALSE;
    }
    
    $r_address = rawurlencode($address);

    // Google Map Api から Json形式で緯度・経度等のデータを取得
    $geo_url = "http://maps.google.com/maps/api/geocode/json?address={$r_address}&sensor=false&language=ja";
    $geostr = file_get_contents($geo_url);
	$json = json_decode($geostr,true);

	return $json['results'][0]['geometry']['location'];
}

?>