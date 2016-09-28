<?php
/* Pukiwiki GoogleMaps plugin 2.3
 * http://reddog.s35.xrea.com
 * -------------------------------------------------------------------
 * Copyright (c) 2005, 2006, 2007, 2008 OHTSUKA, Yoshio
 * This program is free to use, modify, extend at will. The author(s)
 * provides no warrantees, guarantees or any responsibility for usage.
 * Redistributions in any form must retain this copyright notice.
 * ohtsuka dot yoshio at gmail dot com
 * -------------------------------------------------------------------
 * 2008-10-21 2.3.1 詳細はgooglemaps2.inc.php
 */

define ('PLUGIN_GOOGLEMAPS2_ICON_IMAGE', 'http://www.google.com/mapfiles/marker.png');
define ('PLUGIN_GOOGLEMAPS2_ICON_SHADOW','http://www.google.com/mapfiles/shadow50.png');
define ('PLUGIN_GOOGLEMAPS2_ICON_IW', 20);
define ('PLUGIN_GOOGLEMAPS2_ICON_IH', 34);
define ('PLUGIN_GOOGLEMAPS2_ICON_SW', 37);
define ('PLUGIN_GOOGLEMAPS2_ICON_SH', 34);
define ('PLUGIN_GOOGLEMAPS2_ICON_IANCHORX', 10);
define ('PLUGIN_GOOGLEMAPS2_ICON_IANCHORY', 34);
define ('PLUGIN_GOOGLEMAPS2_ICON_SANCHORX', 10);
define ('PLUGIN_GOOGLEMAPS2_ICON_SANCHORY', 0);
define ('PLUGIN_GOOGLEMAPS2_ICON_TRANSPARENT', 'http://www.google.com/mapfiles/markerTransparent.png');
define ('PLUGIN_GOOGLEMAPS2_ICON_AREA', '1 7 7 0 13 0 19 7 19 12 13 20 12 23 11 34 9 34 8 23 6 19 1 13 1 70');

function plugin_googlemaps2_icon_get_default () {
	return array(
		'image'       => PLUGIN_GOOGLEMAPS2_ICON_IMAGE,
		'shadow'      => PLUGIN_GOOGLEMAPS2_ICON_SHADOW,
		'iw'          => PLUGIN_GOOGLEMAPS2_ICON_IW,
		'ih'          => PLUGIN_GOOGLEMAPS2_ICON_IH,
		'sw'          => PLUGIN_GOOGLEMAPS2_ICON_SW,
		'sh'          => PLUGIN_GOOGLEMAPS2_ICON_SH,
		'ianchorx'    => PLUGIN_GOOGLEMAPS2_ICON_IANCHORX,
		'ianchory'    => PLUGIN_GOOGLEMAPS2_ICON_IANCHORY,
		'sanchorx'    => PLUGIN_GOOGLEMAPS2_ICON_SANCHORX,
		'sanchory'    => PLUGIN_GOOGLEMAPS2_ICON_SANCHORY,
		'transparent' => PLUGIN_GOOGLEMAPS2_ICON_TRANSPARENT,
		'area'        => PLUGIN_GOOGLEMAPS2_ICON_AREA
	);
}

function plugin_googlemaps2_icon_convert() {
	$args = func_get_args();
	return plugin_googlemaps2_icon_output($args[0], array_slice($args, 1));
}

function plugin_googlemaps2_icon_inline() {
	$args = func_get_args();
	array_pop($args);
	return plugin_googlemaps2_icon_output($args[0], array_slice($args, 1));
}

function plugin_googlemaps2_icon_output($name, $params) {
	global $vars;
	$qm = get_qm();

	if (!defined('PLUGIN_GOOGLEMAPS2_DEF_KEY')) {
		return $qm->replace('plg_googlemaps2.err_not_called', 'googlemaps2_icon');
	}
	if (!plugin_googlemaps2_is_supported_profile()) {
		return '';
	}

	$defoptions = plugin_googlemaps2_icon_get_default();

	$inoptions = array();
	foreach ($params as $param) {
		list($index, $value) = preg_split('/=/', $param);
		$index = trim($index);
		$value = htmlspecialchars(trim($value));
		$inoptions[$index] = $value;
	}

	if (array_key_exists('define', $inoptions)) {
		$vars['googlemaps2_icon'][$inoptions['define']] = $inoptions;
		return "";
	}

	$coptions = array();
	if (array_key_exists('class', $inoptions)) {
		$class = $inoptions['class'];
		if (array_key_exists($class, $vars['googlemaps2_icon'])) {
			$coptions = $vars['googlemaps2_icon'][$class];
		}
	}
	$options = array_merge($defoptions, $coptions, $inoptions);
	$image       = $options['image'];
	$shadow      = $options['shadow'];
	$iw          = (integer)$options['iw'];
	$ih          = (integer)$options['ih'];
	$sw          = (integer)$options['sw'];
	$sh          = (integer)$options['sh'];
	$ianchorx    = (integer)$options['ianchorx'];
	$ianchory    = (integer)$options['ianchory'];
	$sanchorx    = (integer)$options['sanchorx'];
	$sanchory    = (integer)$options['sanchory'];
	$transparent = $options['transparent'];
	$area        = $options['area'];

	$coords = array();
	if (isset($area)) {
		$c = substr($area, 0, 1);
		switch ($c) {
			case "'":
			case "[";
			case "{";
				$area = substr($area, 1, strlen($area)-2);
				break;
			case "&":
				if (substr($area, 0, 6) == "&quot;") {
					$area = substr($area, 6, strlen($area)-12);
				}
				break;
		}
		foreach (explode(' ', $area) as $p) {
			if (strlen($p) <= 0) continue;
			array_push($coords, $p);
		}
	}
	$coords = join($coords, ",");
	$page = $vars['page'];

	// Output
	$output = <<<EOD
<script type="text/javascript">
//<![CDATA[
onloadfunc.push( function () {
	var icon = new GIcon();
	icon.image = "$image";
	icon.shadow = "$shadow";
	icon.iconSize = new GSize($iw, $ih);
	icon.shadowSize = new GSize($sw, $sh);
	icon.iconAnchor = new GPoint($ianchorx, $ianchory);
	icon.infoWindowAnchor = new GPoint($sanchorx, $sanchory);
	icon.transparent = "$transparent";
	icon.imageMap = [$coords];
	icon.pukiwikiname = "$name";
	googlemaps_icons["$page"]["$name"] = icon;
});
//]]>
</script>

EOD;
	return $output;
}

?>
