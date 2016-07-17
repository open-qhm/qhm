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

define ('PLUGIN_GOOGLEMAPS2_MK_DEF_CAPTION', '');         //マーカーのキャプション
define ('PLUGIN_GOOGLEMAPS2_MK_DEF_MAXCONTENT', '');      //吹き出しを最大時にしたときに表示するPukiwikiのページ名かURL
define ('PLUGIN_GOOGLEMAPS2_MK_DEF_MAXTITLE', '');        //吹き出しを最大時にしたときのタイトル
//define ('PLUGIN_GOOGLEMAPS2_MK_DEF_MAXURL', '');          //MaxContentの別名. 廃止予定。
define ('PLUGIN_GOOGLEMAPS2_MK_DEF_NOLIST', false);       //マーカーのリストを出力しない
define ('PLUGIN_GOOGLEMAPS2_MK_DEF_NOINFOWINDOW', false); //マーカーのinfoWindowを表示しない
define ('PLUGIN_GOOGLEMAPS2_MK_DEF_ZOOM', null);          //マーカーの初期zoom値。nullは初期値無し。
define ('PLUGIN_GOOGLEMAPS2_MK_DEF_MINZOOM',  0);         //マーカーが表示される最小ズームレベル
define ('PLUGIN_GOOGLEMAPS2_MK_DEF_MAXZOOM', 17);         //マーカーが表示される最大ズームレベル
define ('PLUGIN_GOOGLEMAPS2_MK_DEF_ICON', '');        //アイコン。空の時はデフォルト
define ('PLUGIN_GOOGLEMAPS2_MK_DEF_NOICON', false);   //アイコンを表示しない。
define ('PLUGIN_GOOGLEMAPS2_MK_DEF_TITLEISPAGENAME', false); //title省略時にページ名を使う。

//FORMATLISTはhtmlに出力されるマーカーのリストの雛型
//FMTINFOはマップ上のマーカーをクリックして表示されるフキダシの（中の）雛型
define ('PLUGIN_GOOGLEMAPS2_MK_DEF_FORMATLIST' , '<b>%title%</b> - %caption% %maxcontent%');
define ('PLUGIN_GOOGLEMAPS2_MK_DEF_FORMATINFO' , '<b>%title%</b><br/><div style=\'width:215px;\'><span style=\'float:left; padding-right: 3px; padding-bottom: 3px;\'>%image%</span>%caption%</div>');

//リストをクリックするとマップにフォーカスさせる。(0 or 1)
define ('PLUGIN_GOOGLEMAPS2_MK_DEF_ALINK' , 1);

function plugin_googlemaps2_mark_get_default () {
	global $vars, $script;
	$qm = get_qm();

	return array (
		'title'        => $qm->m['plg_googlemaps2_mark']['label_def_name'],
		'caption'      => PLUGIN_GOOGLEMAPS2_MK_DEF_CAPTION,
		'maxcontent'   => PLUGIN_GOOGLEMAPS2_MK_DEF_MAXCONTENT,
		'maxtitle'     => PLUGIN_GOOGLEMAPS2_MK_DEF_MAXTITLE,
		'image'        => '',
		'icon'         => PLUGIN_GOOGLEMAPS2_MK_DEF_ICON,
		'nolist'       => PLUGIN_GOOGLEMAPS2_MK_DEF_NOLIST,
		'noinfowindow' => PLUGIN_GOOGLEMAPS2_MK_DEF_NOINFOWINDOW,
		'noicon'       => PLUGIN_GOOGLEMAPS2_MK_DEF_NOICON,
		'zoom'         => PLUGIN_GOOGLEMAPS2_MK_DEF_ZOOM,
		'maxzoom'      => PLUGIN_GOOGLEMAPS2_MK_DEF_MAXZOOM,
		'minzoom'      => PLUGIN_GOOGLEMAPS2_MK_DEF_MINZOOM,
		'map'          => PLUGIN_GOOGLEMAPS2_DEF_MAPNAME,
		'formatlist'   => PLUGIN_GOOGLEMAPS2_MK_DEF_FORMATLIST,
		'formatinfo'   => PLUGIN_GOOGLEMAPS2_MK_DEF_FORMATINFO,
		'alink'        => PLUGIN_GOOGLEMAPS2_MK_DEF_ALINK,
        'titleispagename' => PLUGIN_GOOGLEMAPS2_MK_DEF_TITLEISPAGENAME,
	);
}

function plugin_googlemaps2_mark_convert() {
	$qm = get_qm();
	$args = func_get_args();
	if (sizeof($args)<2) {
		return $qm->replace('fmt_err_cvt', 'googlemaps2_mark', $qm->m['plg_googlemaps2_mark']['err_usage']);
	}
	return plugin_googlemaps2_mark_output($args[0], $args[1], array_slice($args, 2));
}

function plugin_googlemaps2_mark_inline() {
	$qm = get_qm();
	$args = func_get_args();
	array_pop($args);
	if (sizeof($args)<2) {
		return $qm->replace('fmt_err_cvt', 'googlemaps2_mark', $qm->m['plg_googlemaps2_mark']['err_usage']);
	}
	return plugin_googlemaps2_mark_output($args[0], $args[1], array_slice($args, 2));
}

function plugin_googlemaps2_mark_output($lat, $lng, $params) {
	global $vars;
	$qm = get_qm();

	if (!defined('PLUGIN_GOOGLEMAPS2_DEF_KEY')) {
		return $qm->replace('plg_googlemaps2.err_not_called', 'googlemaps2_mark');
	}

	$defoptions = plugin_googlemaps2_mark_get_default();

	$inoptions = array();
	foreach ($params as $param) {
		list($index, $value) = preg_split('=', $param);
		$index = trim($index);
		$value = htmlspecialchars(trim($value));
		$inoptions[$index] = $value;
		if ($index == 'zoom') {$isSetZoom = true;}//for old api
	}

	if (array_key_exists('define', $inoptions)) {
		$vars['googlemaps2_mark'][$inoptions['define']] = $inoptions;
		return "";
	}

	$coptions = array();
	if (array_key_exists('class', $inoptions)) {
		$class = $inoptions['class'];
		if (array_key_exists($class, $vars['googlemaps2_mark'])) {
			$coptions = $vars['googlemaps2_mark'][$class];
		}
    }

    // map maxurl to maxcontent if maxurl exists.
    if (array_key_exists('maxurl', $coptions) && !array_key_exists('maxcontent', $coptions)) {
        $coptions['maxcontent'] = $coptions['maxurl'];
    }
    if (array_key_exists('maxurl', $inoptions) && !array_key_exists('maxcontent', $inoptions)) {
        $inoptions['maxcontent'] = $inoptions['maxurl'];
    }

	$options = array_merge($defoptions, $coptions, $inoptions);
	$lat = trim($lat);
	$lng = trim($lng);
	$title        = $options['title'];
	$caption      = $options['caption'];
	$maxcontent   = $options['maxcontent'];
	$maxtitle     = $options['maxtitle'];
	$image        = $options['image'];
	$icon         = $options['icon'];
	$nolist       = plugin_googlemaps2_getbool($options['nolist']);
	$noinfowindow = plugin_googlemaps2_getbool($options['noinfowindow']);
	$noicon       = plugin_googlemaps2_getbool($options['noicon']);
	$zoom         = $options['zoom'];
	$maxzoom      = (int)$options['maxzoom'];
	$minzoom      = (int)$options['minzoom'];
	$map          = plugin_googlemaps2_addprefix($vars['page'], $options['map']);
	//XSS対策のため次の二つのオプションはユーザーから設定不可にする。
	$formatlist   = $defoptions['formatlist'];
	$formatinfo   = $defoptions['formatinfo'];
	$alink        = $options['alink'];
	$titleispagename = plugin_googlemaps2_getbool($options['titleispagename']);
	$api = $vars['googlemaps2_info'][$map]['api'];

	if ($nolist) {
		$alink = false;
	}

	$maxcontentfull = $maxcontent;
	if ($maxcontent != '') {
		if (!preg_match('/^http:\/\/.*$/i', $maxcontent)) {
			$encurl = rawurlencode($maxcontent);
			$maxcontent = get_script_uri();
			$maxcontentfull = $maxcontent;
			$maxcontent .= '?cmd=googlemaps2&action=showbody&page=';
			$maxcontentfull .= '?';
			$maxcontent .= $encurl;
			$maxcontentfull .= $encurl;
		}
    }

    if ($titleispagename) {
        $title = $vars['page'];
    }

    if ($maxtitle == '') {
        $maxtitle = $title;
    }

	//携帯デバイス用リスト出力
	if (!plugin_googlemaps2_is_supported_profile()) {
		if ($nolist == false) {
			return plugin_googlemaps_mark_simple_format_listhtml(
				$formatlist, $title, $caption, $maxcontentfull);
		}
		return '';
	}

	$page = $vars['page'];

	if ($api < 2 && $isSetZoom) $zoom = 19 - $zoom;
	if ($zoom == null) $zoom = 'null';

	if ($noicon == true) {
		$noinfowindow = true;
	}

	//Pukiwikiの添付された画像の表示
	$q = '/^http:\/\/.*(\.jpg|\.gif|\.png)$/i';
	if ($image != '' && !preg_match($q, $image)) {
		$image = $script.'?plugin=ref'.'&page='.
		rawurlencode($vars["page"]).'&src='.rawurlencode($image);
	}
	if ($noinfowindow == false) {
		$infohtml = plugin_googlemaps_mark_format_infohtml(
			$map, $formatinfo, $alink,
			$title, $caption, $image);
	} else {
		$infohtml = null;
	}

	$key = "$map,$lat,$lng";

	if ($nolist == false) {
		$listhtml = plugin_googlemaps_mark_format_listhtml(
			$page, $map, $formatlist, $alink,
			$key, $infohtml,
			$title, $caption, $image,
			$zoom, $maxcontentfull);
	}

	// Create Marker
	$output = <<<EOD
<script type="text/javascript">
//<![CDATA[
onloadfunc.push(function() {
	p_googlemaps_regist_marker ('$page', '$map', PGTool.getLatLng($lat , $lng, '$api'), '$key',
	{noicon: '$noicon', icon:'$icon', zoom:$zoom, maxzoom:$maxzoom, minzoom:$minzoom, title:'$title', infohtml:'$infohtml', maxtitle:'$maxtitle', maxcontent:'$maxcontent'});
});
//]]>
</script>\n
EOD;

	//Show Markers List
	if ($nolist == false) {
		$output .= $listhtml;
	}

	return $output;
}

function plugin_googlemaps_mark_simple_format_listhtml($format, $title, $caption, $maxcontentfull) {
	if ($maxcontentfull) {
		$maxcontentfull = '<a href=\''.$maxcontentfull.'\'>[PAGE]</a>';
	}
	$html = $format;
	$html = str_replace('%title%', $title, $html);
	$html = str_replace('%caption%', $caption, $html);
	$html = str_replace('%image%', '', $html);
	$html = str_replace('%maxcontent%', $maxcontentfull, $html);
	return $html;
}

function plugin_googlemaps_mark_format_listhtml($page, $map, $format, $alink,
	$key, $infohtml, $title, $caption, $image, $zoomstr, $maxcontentfull) {

	if ($alink == true) {
		$atag = "<a id=\"".$map."_%title%\"></a>";
		$atag .= "<a href=\"#$map\"";
	}

	$atag .= " onclick=\"googlemaps_markers['$page']['$map']['$key'].onclick();\">%title%</a>";

	if ($maxcontentfull) {
		$maxcontentfull = '<a href=\''.$maxcontentfull.'\'>[PAGE]</a>';
	}

	$html = $format;
	if ($alink == true) {
		$html = str_replace('%title%', $atag , $html);
	}
	$html = str_replace('%title%', $title, $html);
	$html = str_replace('%caption%', $caption, $html);
	$html = str_replace('%image%', '<img src="'.$image.'" border=0/>', $html);
	$html = str_replace('%maxcontent%', $maxcontentfull, $html);
	return $html;
}

function plugin_googlemaps_mark_format_infohtml($map, $format, $alink, $title, $caption, $image) {
	$qm = get_qm();

	$html = str_replace('\'', '\\\'', $format);
	if ($alink == true) {
		$atag = "%title% <a href=\\'#".$map."_%title%\\'>"
			.$qm->m['plg_googlemaps2_mark']['link'].'</a>';
		$html = str_replace('%title%', $atag , $html);
	}
	$html = str_replace('%title%',$title , $html);
	$html = str_replace('%caption%', $caption, $html);

	if ($image != '') {
		$image = '<img src=\\\''.$image.'\\\' border=0/>';
	}
	$html = str_replace('%image%', $image, $html);

	return $html;
}

?>
