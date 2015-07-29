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
 * 2005-09-25 1.1   -Release
 * 2006-04-20 2.0   -GoogleMaps API ver2
 * 2006-07-15 2.1   -googlemaps2_insertmarker.inc.phpを追加。usetoolオプションの廃止。
 *                   ブロック型の書式を使えるようにした。
 *                  -googlemaps2にdbclickzoom, continuouszoomオプションを追加。
 *                  -googlemaps2_markのimageオプションで添付画像を使えるようにした。
 *                  -OverViewMap, マウスクリック操作の改良。
 *                  -XSS対策。googlemaps2_markのformatlist, formatinfoの廃止。
 *                  -マーカーのタイトルをツールチップで表示。
 *                  -アンカー名にpukiwikigooglemaps2_というprefixをつけるようにした。
 * 2006-07-29 2.1.1 -includeやcalender_viewerなど複数のページを一つのページにまとめて
 *                   出力するプラグインでマップが表示されないバグを修正。
 * 2006-08-24 2.1.2 -単語検索でマーカー名がハイライトされた時のバグを修正。
 * 2006-09-30 2.1.3 -携帯電話,PDAなど非対応のデバイスでスクリプトを出力しないようにした。
 * 2006-12-30 2.2   -マーカーのフキダシを開く時に画像の読み込みを待つようにした。
 *                  -GMapTypeControlを小さく表示。
 *                  -GoogleのURLをmaps.google.comからmaps.google.co.jpに。
 *                  -googlemaps2にgeoctrl, crossctrlオプションの追加。
 *                  -googlemaps2_markにmaxurl, minzoom, maxzoomオプションの追加。
 *                  -googlemaps2_insertmarkerでimage, maxurl, minzoom, maxzoomを入力可能に。
 *                  -googlemaps2_drawにfillopacity, fillcolor, inradiusオプションの追加。
 *                  -googlemaps2_drawにpolygonコマンドの追加。
 * 2007-01-10 2.2.1 -googlemaps2のoverviewctrlのパラメータで地図が挙動不審になるバグを修正。
 *                  -googlemaps2_insertmarkerがincludeなどで複数同時に表示されたときの挙動不審を修正
 * 2007-01-22 2.2.2 -googlemaps2のwidth, heightで単位が指定されていないときは暗黙にpxを補う。
 *                  -googlemaps2のoverviewtypeにautoを追加。地図のタイプにオーバービューが連動。
 * 2007-01-31 2.2.3 -googlemaps2でcrossコントロール表示時にフキダシのパンが挙動不審なのを修正。
 *                  -GoogleのロゴがPukiwikiのCSSによって背景を透過しない問題を暫定的に修正。
 * 2007-08-04 2.2.4 -IEで図形を描画できないバグを修正。
 *                  -googlemaps2にgeoxmlオプションの追加。
 * 2007-09-25 2.2.5 -geoxmlでエラーがあるとinsertmarkerが動かないバグを修正。
 * 2007-12-01 2.3.0 -googlemaps2のgeoctrl, overviewtypeオプションの廃止
 *                  -googlemaps2にgooglebar, importicon, backlinkmarkerオプションの追加
 *                  -googlemaps2_markのmaxurlオプションの廃止。（一時的にmaxcontentにマッピングした）
 *                  -googlemaps2_markにmaxcontent, maxtitle, titleispagenameオプションを追加。
 * 2008-10-21 2.3.1 -apiのバージョンを2.132dに固定した。
 */

global $googlemaps_apikey;
define ('PLUGIN_GOOGLEMAPS2_DEF_KEY', $googlemaps_apikey);

define ('PLUGIN_GOOGLEMAPS2_DEF_MAPNAME', 'googlemaps2');     //Map名
define ('PLUGIN_GOOGLEMAPS2_DEF_WIDTH'  , '400px');           //横幅
define ('PLUGIN_GOOGLEMAPS2_DEF_HEIGHT' , '400px');           //縦幅
define ('PLUGIN_GOOGLEMAPS2_DEF_LAT'    ,  35.036198);        //経度
define ('PLUGIN_GOOGLEMAPS2_DEF_LNG'    ,  135.732103);       //緯度
define ('PLUGIN_GOOGLEMAPS2_DEF_ZOOM'   ,  13);       //ズームレベル
define ('PLUGIN_GOOGLEMAPS2_DEF_TYPE'   ,  'normal'); //マップのタイプ(normal, satellite, hybrid)
define ('PLUGIN_GOOGLEMAPS2_DEF_MAPCTRL',  'normal'); //マップコントロール(none,smallzoom,small,normal,large)
define ('PLUGIN_GOOGLEMAPS2_DEF_TYPECTRL'    ,'normal'); //maptype切替コントロール(none, normal)
define ('PLUGIN_GOOGLEMAPS2_DEF_SCALECTRL'   ,'none');   //スケールコントロール(none, normal)
define ('PLUGIN_GOOGLEMAPS2_DEF_OVERVIEWCTRL','none');   //オーバービューマップ(none, hide, show)
define ('PLUGIN_GOOGLEMAPS2_DEF_CROSSCTRL'   ,'none');   //センタークロスコントロール(none, show)
define ('PLUGIN_GOOGLEMAPS2_DEF_OVERVIEWWIDTH', '150');  //オーバービューマップの横幅
define ('PLUGIN_GOOGLEMAPS2_DEF_OVERVIEWHEIGHT','150');  //オーバービューマップの縦幅
define ('PLUGIN_GOOGLEMAPS2_DEF_API', 2);                //APIの後方互換用フラグ(1=1系, 2=2系). 廃止予定。
define ('PLUGIN_GOOGLEMAPS2_DEF_TOGGLEMARKER', false);     //マーカーの表示切替チェックの表示
define ('PLUGIN_GOOGLEMAPS2_DEF_NOICONNAME'  , 'Unnamed'); //アイコン無しマーカーのラベル
define ('PLUGIN_GOOGLEMAPS2_DEF_DBCLICKZOOM'  , true);     //ダブルクリックでズームする(true, false)
define ('PLUGIN_GOOGLEMAPS2_DEF_CONTINUOUSZOOM', true);    //滑らかにズームする(true, false)
define ('PLUGIN_GOOGLEMAPS2_DEF_GEOXML', '');              //読み込むKML, GeoRSSのURL
define ('PLUGIN_GOOGLEMAPS2_DEF_GOOGLEBAR', false);        //GoogleBarの表示
define ('PLUGIN_GOOGLEMAPS2_DEF_IMPORTICON', '');          //アイコンを取得するPukiwikiページ
define ('PLUGIN_GOOGLEMAPS2_DEF_BACKLINKMARKER', false);    //バックリンクでマーカーを集める

//Pukiwikiは1.4.5から携帯電話などのデバイスごとにプロファイルを用意して
//UAでスキンを切り替えて表示できるようになったが、この定数ではGoogleMapsを
//表示可能なプロファイルを設定する。
//対応デバイスのプロファイルをカンマ(,)区切りで記入する。
//Pukiwiki1.4.5以降でサポートしてるデフォルトのプロファイルはdefaultとkeitaiの二つ。
//ユーザーが追加したプロファイルがあり、それもGoogleMapsが表示可能なデバイスなら追加すること。
//またデフォルトのプロファイルを"default"以外の名前にしている場合も変更すること。
//注:GoogleMapsは携帯電話で表示できない。
define ('PLUGIN_GOOGLEMAPS2_PROFILE', 'default');

function plugin_googlemaps2_is_supported_profile () {
	if (defined("UA_PROFILE")) {
		return in_array(UA_PROFILE, preg_split('/[\s,]+/', PLUGIN_GOOGLEMAPS2_PROFILE));
	} else {
		return 1;
	}
}

function plugin_googlemaps2_get_default () {
	global $vars;
	return array(
		'mapname'        => PLUGIN_GOOGLEMAPS2_DEF_MAPNAME,
		'key'            => PLUGIN_GOOGLEMAPS2_DEF_KEY,
		'width'          => PLUGIN_GOOGLEMAPS2_DEF_WIDTH,
		'height'         => PLUGIN_GOOGLEMAPS2_DEF_HEIGHT,
		'lat'            => PLUGIN_GOOGLEMAPS2_DEF_LAT,
		'lng'            => PLUGIN_GOOGLEMAPS2_DEF_LNG,
		'zoom'           => PLUGIN_GOOGLEMAPS2_DEF_ZOOM,
		'mapctrl'        => PLUGIN_GOOGLEMAPS2_DEF_MAPCTRL,
		'type'           => PLUGIN_GOOGLEMAPS2_DEF_TYPE,
		'typectrl'       => PLUGIN_GOOGLEMAPS2_DEF_TYPECTRL,
		'scalectrl'      => PLUGIN_GOOGLEMAPS2_DEF_SCALECTRL,
		'overviewctrl'   => PLUGIN_GOOGLEMAPS2_DEF_OVERVIEWCTRL,
		'crossctrl'      => PLUGIN_GOOGLEMAPS2_DEF_CROSSCTRL,
		'overviewwidth'  => PLUGIN_GOOGLEMAPS2_DEF_OVERVIEWWIDTH,
		'overviewheight' => PLUGIN_GOOGLEMAPS2_DEF_OVERVIEWHEIGHT,
		'api'            => PLUGIN_GOOGLEMAPS2_DEF_API,
		'togglemarker'   => PLUGIN_GOOGLEMAPS2_DEF_TOGGLEMARKER,
		'noiconname'     => PLUGIN_GOOGLEMAPS2_DEF_NOICONNAME,
		'dbclickzoom'    => PLUGIN_GOOGLEMAPS2_DEF_DBCLICKZOOM,
		'continuouszoom' => PLUGIN_GOOGLEMAPS2_DEF_DBCLICKZOOM,
		'geoxml'         => PLUGIN_GOOGLEMAPS2_DEF_GEOXML,
		'googlebar'      => PLUGIN_GOOGLEMAPS2_DEF_GOOGLEBAR,
		'importicon'     => PLUGIN_GOOGLEMAPS2_DEF_IMPORTICON,
		'backlinkmarker' => PLUGIN_GOOGLEMAPS2_DEF_BACKLINKMARKER,
	);
}

function plugin_googlemaps2_convert() {
	static $init = true;
	$args = func_get_args();
	$ret = "<div>".plugin_googlemaps2_output($init, $args)."</div>";
	$init = false;
	return $ret;
}

function plugin_googlemaps2_inline() {
	static $init = true;
	$args = func_get_args();
	array_pop($args);
	$ret = plugin_googlemaps2_output($init, $args);
	$init = false;
	return $ret;
	
}

function plugin_googlemaps2_action() {
	global $vars;
	$qm = get_qm();
	$action = isset($vars['action']) ? $vars['action'] : '';
	$page = isset($vars['page']) ? $vars['page'] : '';

	switch($action) {
		case '':
			break;
		// maxContent用のレイアウトスタイルでページのbodyを出力
		case 'showbody':
			if (is_page($page)) {
				$body = convert_html(get_source($page));	
			} else {
				if ($page == '') {
					$page = '('. $qm->m['plg_googlemaps2']['empty_page_name']. ')';
				}
				$body = h($page);
				$body .= '<br />'. $qm->m['plg_googlemaps2']['err_unknown_page'];
			}
			pkwk_common_headers();
			header('Cache-control: no-cache');
			header('Pragma: no-cache');
			header('Content-Type: text/html; charset='.CONTENT_CHARSET);
			print <<<EOD
<div>
$body
</div>
EOD;
			break;
	}
	exit;
}

function plugin_googlemaps2_getbool($val) {
	if ($val == false) return false;
	if (!strcasecmp ($val, "false") || 
		!strcasecmp ($val, "no"))
		return false;
	return true;
}

function plugin_googlemaps2_addprefix($page, $name) {
	return "pukiwikigooglemaps2_".$page.'_'.$name;
}

function plugin_googlemaps2_output($doInit, $params) {
	global $vars;
	$qm = get_qm();

	if (!plugin_googlemaps2_is_supported_profile()) {
		return $qm->m['plg_googlemaps2']['err_unsupport_dev'];
	}
	$defoptions = plugin_googlemaps2_get_default();
	
	$inoptions = array();
	$isSetZoom = false;
	foreach ($params as $param) {
		$pos = strpos($param, '=');
		if ($pos == false) continue;
		$index = trim(substr($param, 0, $pos));
		$value = htmlspecialchars(trim(substr($param, $pos+1)));
		$inoptions[$index] = $value;
		if ($index == 'cx') {$cx = (float)$value;}//for old api
		if ($index == 'cy') {$cy = (float)$value;}//for old api
		if ($index == 'zoom') {$isSetZoom = true;}//for old api
	}

	if (array_key_exists('define', $inoptions)) {
		$vars['googlemaps2'][$inoptions['define']] = $inoptions;
		return "";
	}
	
	$coptions = array();
	if (array_key_exists('class', $inoptions)) {
		$class = $inoptions['class'];
		if (array_key_exists($class, $vars['googlemaps2'])) {
			$coptions = $vars['googlemaps2'][$class];
		}
	}
	$options = array_merge($defoptions, $coptions, $inoptions);
	$mapname        = plugin_googlemaps2_addprefix($vars['page'], $options['mapname']);
	$key            = $options['key'];
	$width          = $options['width'];
	$height         = $options['height'];
	$lat            = (float)$options['lat'];
	$lng            = (float)$options['lng'];
	$zoom           = (integer)$options['zoom'];
	$mapctrl        = $options['mapctrl'];
	$type           = $options['type'];
	$typectrl       = $options['typectrl'];
	$scalectrl      = $options['scalectrl'];
	$overviewctrl   = $options['overviewctrl'];
	$crossctrl      = $options['crossctrl'];
	$overviewwidth  = $options['overviewwidth'];
	$overviewheight = $options['overviewheight'];
	$api            = (integer)$options['api'];
	$noiconname     = $options['noiconname'];
	$togglemarker   = plugin_googlemaps2_getbool($options['togglemarker']);
	$dbclickzoom    = plugin_googlemaps2_getbool($options['dbclickzoom']);
	$continuouszoom = plugin_googlemaps2_getbool($options['continuouszoom']);
	$geoxml         = preg_replace("/&amp;/i", '&', $options['geoxml']);
    $googlebar      = plugin_googlemaps2_getbool($options['googlebar']);
    $importicon     = $options['importicon'];
    $backlinkmarker = plugin_googlemaps2_getbool($options['backlinkmarker']);


	$page = $vars['page'];
	//apiのチェック
	if ( ! (is_numeric($api) && $api >= 0 && $api <= 2) ) {
		$api = 2;
	}
	//古い1系APIとの互換性のためcx, cyが渡された場合lng, latに代入する。
	if ($api < 2) {
		if (isset($cx) && isset($cy)) {
			$lat = $cx;
			$lng = $cy;
		} else {
			$tmp = $lng;
			$lng = $lat;
			$lat = $tmp;
		}
	} else {
		if (isset($cx)) $lng = $cx;
		if (isset($cy)) $lat = $cy;
	}
	
	// zoomレベル
	if ($api < 2 && $isSetZoom) {
		$zoom = 17 - $zoom;
	}
	// width, heightの値チェック
	if (is_numeric($width)) { $width = (int)$width . "px"; }
	if (is_numeric($height)) { $height = (int)$height . "px"; }

	// Mapタイプの名前を正規化
	$type = plugin_googlemaps2_get_maptype($type);

	// 初期化処理の出力
	if ($doInit) {
		$output = plugin_googlemaps2_init_output($key, $noiconname);
	} else {
		$output = "";
	}
	$pukiwikiname = $options['mapname'];
	$output .= <<<EOD
<div id="$mapname" style="width: $width; height: $height;"></div>

<script type="text/javascript">
//<![CDATA[
onloadfunc.push( function () {

if (typeof(googlemaps_maps['$page']) == 'undefined') {
	googlemaps_maps['$page'] = new Array();
	googlemaps_markers['$page'] = new Array();
	googlemaps_marker_mgrs['$page'] = new Array();
	googlemaps_icons['$page'] = new Array();
	googlemaps_crossctrl['$page'] = new Array();
}

var map = new GMap2(document.getElementById("$mapname"));
map.pukiwikiname = "$pukiwikiname";
GEvent.addListener(map, "dblclick", function() {
		this.closeInfoWindow();
});
onloadfunc2.push( function () {
	p_googlemaps_regist_to_markermanager("$page", "$mapname", true);
});

map.setCenter(PGTool.getLatLng($lat, $lng, "$api"), $zoom, $type);

var marker_mgr = new GMarkerManager(map);

// 現在(2.70)のMarker Managerではマーカーをhideしていても、描画更新時に
// マーカーを表示してしまうため、リフレッシュ後にフラグを確認して再び隠す。
// 一度表示されて消えるみたいな挙動になるが、他に手段が無いので仕方が無い。
GEvent.addListener(marker_mgr, "changed", function(bounds, markerCount) {
	var markers = googlemaps_markers["$page"]["$mapname"];
	for (key in markers) {
		var m = markers[key];
		if (m.isVisible() == false) {
			m.marker.hide();
		}
	}
});

EOD;
	// Show Map Control/Zoom 
	switch($mapctrl) {
		case "small":
			$output .= "map.addControl(new GSmallMapControl());\n";
			break;
		case "smallzoom":
			$output .= "map.addControl(new GSmallZoomControl());\n";
			break;
		case "none":
			break;
		case "large":
		default:
			$output .= "map.addControl(new GLargeMapControl());\n";
			break;
	}
	
	// Scale
	if ($scalectrl != "none") {
		$output .= "map.addControl(new GScaleControl());\n";
	}
	
	// Show Map Type Control and Center
	if ($typectrl != "none") {
		$output .= "map.addControl(new GMapTypeControl(true));\n";
	}
	
	// Double click zoom
	if ($dbclickzoom) {
		$output .= "map.enableDoubleClickZoom();\n";
	} else {
		$output .= "map.disableDoubleClickZoom();\n";
	}

	// Continuous zoom
	if ($continuouszoom) {
		$output .= "map.enableContinuousZoom();\n";
	} else {
		$output .= "map.disableContinuousZoom();\n";
	}
	
	// OverviewMap
	if ($overviewctrl != "none") {
		$ovw = preg_replace("/(\d+).*/i", "\$1", $overviewwidth);
		$ovh = preg_replace("/(\d+).*/i", "\$1", $overviewheight);
		if ($ovw == "") $ovw = PLUGIN_GOOGLEMAPS2_DEF_OVERVIEWWIDTH;
		if ($ovh == "") $ovh = PLUGIN_GOOGLEMAPS2_DEF_OVERVIEWHEIGHT;
		$output .= "var ovctrl = new GOverviewMapControl(new GSize($ovw, $ovh));\n";
		$output .= "map.addControl(ovctrl);\n";

		if ($overviewctrl == "hide") {
		$output .= "ovctrl.hide(true);\n";
		}
	}

	// Geo XML
	if ($geoxml != "") {
		$output .= "try {\n";
		$output .= "var geoxml = new GGeoXml(\"$geoxml\");\n";
		$output .= "map.addControl(geoxml);\n";
		$output .= "} catch (e) {}\n";
	}
	
	// GoogleBar
	if ($googlebar) {
        $output .= "map.enableGoogleBar();\n";
    }

	// Center Cross Custom Control
	if ($crossctrl != "none") {
		$output .= "var crossctrl = new PGCross();\n";
		$output .= "crossctrl.initialize(map);\n";
		$output .= "var pos = crossctrl.getDefaultPosition();\n";
		$output .= "pos.apply(crossctrl.container);\n";
		$output .= "var crossChangeStyleFunc = function () {\n";
		$output .= "	switch (map.getCurrentMapType()) {\n";
		$output .= "		case G_NORMAL_MAP:    crossctrl.changeStyle('#000000', 0.5); break;\n";
		$output .= "		case G_SATELLITE_MAP: crossctrl.changeStyle('#FFFFFF', 0.9); break;\n";
		$output .= "		case G_HYBRID_MAP:    crossctrl.changeStyle('#FFFFFF', 0.9); break;\n";
		$output .= "		default: crossctrl.changeStyle('#000000', 0.5); break;\n";
		$output .= "	}\n";
		$output .= "}\n";
		$output .= "GEvent.addListener(map, 'maptypechanged', crossChangeStyleFunc);\n";
		$output .= "crossChangeStyleFunc();\n";
		$output .= "googlemaps_crossctrl['$page']['$mapname'] = crossctrl;\n";
	}

	// マーカーの表示非表示チェックボックス
	if ($togglemarker) {
		$output .= "onloadfunc.push( function(){p_googlemaps_togglemarker_checkbox('$page', '$mapname', '$noiconname');} );";
	}

	$output .= "PGTool.transparentGoogleLogo(map);\n";
	$output .= "googlemaps_maps['$page']['$mapname'] = map;\n";
	$output .= "googlemaps_markers['$page']['$mapname'] = new Array();\n";
	$output .= "googlemaps_marker_mgrs['$page']['$mapname'] = marker_mgr;\n";
	$output .= "});\n";
	$output .= "//]]>\n";
	$output .= "</script>\n";
    
    // 指定されたPukiwikiページからアイコンを収集する
    if ($importicon != "") {
        $lines = get_source($importicon);
        foreach ($lines as $line) {
            $ismatch = preg_match('/googlemaps2_icon\(.*?\)/i', $line, $matches);
            if ($ismatch) {
                $output .= convert_html("#" . $matches[0]) . "\n";
            }
        }
    }

    // このページのバックリンクからマーカーを収集する。
    if ($backlinkmarker) {
        $links = links_get_related_db($vars['page']);
        if (! empty($links)) {
            $output .= "<ul>\n";
            foreach(array_keys($links) as $page) {
                $ismatch = preg_match('/#googlemaps2_mark\(([^, \)]+), *([^, \)]+)(.*?)\)/i', 
                    get_source($page, TRUE, TRUE), $matches);
                if ($ismatch) {
                    $markersource = "&googlemaps2_mark(" . 
                        $matches[1] . "," . $matches[2] . 
                        ", title=" . $page . ", maxcontent=" . $page;
                    if ($matches[3] != "") {
                        preg_match('/caption=[^,]+/', $matches[3], $m_caption);
                        if ($m_caption) $markersource .= "," . $m_caption[0];
                        preg_match('/icon=[^,]+/', $matches[3], $m_icon);
                        if ($m_icon) $markersource .= "," . $m_icon[0];
                    }
                    $markersource .= ");";
                    $output .= "<li>" . make_link($markersource) . "</li>\n";
                }
            }
            $output .= "</ul>\n";
        }
    }

	return $output;
}

function plugin_googlemaps2_get_maptype($type) {
	switch (strtolower(substr($type, 0, 1))) {
		case "n": $type = 'G_NORMAL_MAP'   ; break;
		case "s": $type = 'G_SATELLITE_MAP'; break;
		case "h": $type = 'G_HYBRID_MAP'   ; break;
		default:  $type = 'G_NORMAL_MAP'   ; break;
	}
	return $type;
}

function plugin_googlemaps2_init_output($key, $noiconname) {
	global $vars;
	$qm = get_qm();
	$page = $vars['page'];
	return <<<EOD
<script src="http://maps.google.co.jp/maps?file=api&amp;v=2.132d&amp;key=$key" 
type="text/javascript" charset="UTF-8"></script>
<script type="text/javascript">
//<![CDATA[

if (typeof(googlemaps_maps) == 'undefined') {
	// add vml namespace for MSIE
	var agent = navigator.userAgent.toLowerCase();
	if (agent.indexOf("msie") != -1 && agent.indexOf("opera") == -1) {
		try {
		document.namespaces.add('v', 'urn:schemas-microsoft-com:vml');
		document.createStyleSheet().addRule('v\:*', 'behavior: url(#default#VML);');
		} catch(e) {}
	}

	var googlemaps_maps = new Array();
	var googlemaps_markers = new Array();
	var googlemaps_marker_mgrs = new Array();
	var googlemaps_icons = new Array();
	var googlemaps_crossctrl = new Array();
	var onloadfunc = new Array();
	var onloadfunc2 = new Array();//onloadfuncの後に実行（アイコンの一括登録などをする）
}

function PGMarker (point, icon, page, map, hidden, visible, title, maxtitle, maxcontent, minzoom, maxzoom) {
	var marker = null;
	if (hidden == false) {
		var opt = new Object();
		if (icon != '') { opt.icon = googlemaps_icons[page][icon]; }
		if (title != '') { opt.title = title; }
		marker = new GMarker(point, opt);
		GEvent.addListener(marker, "click", function() { this.pukiwikigooglemaps.onclick(); });
		marker.pukiwikigooglemaps = this;
	}

	this.marker = marker;
	this.icon = icon;
	this.map = map;
	this.point = point;
	this.minzoom = minzoom;
	this.maxzoom = maxzoom;

	var _visible = false;
	var _html = null;
	var _zoom = null;

	this.setHtml = function(h) {_html = h;}
	this.setZoom = function(z) {_zoom = parseInt(z);}
	this.getHtml = function() {return _html;}
	this.getZoom = function() {return _zoom;}

	this.onclick = function () {
		var map = googlemaps_maps[page][this.map];
        var maxContentDiv = document.createElement('div');

        maxContentDiv.innerHTML = 'Loading...';
        infowindowopts = {maxContent:maxContentDiv, maxTitle:maxtitle};
        if (maxcontent == "") {
            map.getInfoWindow().disableMaximize();
            infowindowopts = {};
        }

		if (_zoom) {
			if (map.getZoom() != _zoom) {
				map.setZoom(_zoom);
			}
			map.panTo(this.point);
		}

		if ( _html && this.marker ) {
			
			//未ロードの画像があれば読み込みを待ってから開く
			var root = document.createElement('div');
			root.innerHTML = _html;

			var checkNodes = new Array();
			var doneOpenInfoWindow = false;
			checkNodes.push(root);

			while (checkNodes.length) {
				var node = checkNodes.shift();
				if (node.hasChildNodes()) {
					for (var i=0; i<node.childNodes.length; i++) {
						checkNodes.push(node.childNodes.item(i));
					}
				} else {
					var tag = node.tagName;
					if (tag && tag.toUpperCase() == "IMG") {
						if (node.complete == false) {
							//画像の読み込みを待ってからInfoWindowを開く
							var openInfoWindowFunc = function (xmlhttp) {
								marker.openInfoWindowHtml(_html, infowindowopts);
							}
							var async = false;
							if (agent.indexOf("msie") != -1 && agent.indexOf("opera") == -1) {
								async = true;
							}
							if (PGTool.downloadURL(node.src, openInfoWindowFunc, async, null, null)) {
								doneOpenInfoWindow = true;
							}
							break;
						}
					}
				}
			}
			if (doneOpenInfoWindow == false) {
				this.marker.openInfoWindow(_html, infowindowopts);
                if (maxcontent) {
                    maxContentDiv.style.width = "100%";
                    maxContentDiv.style.height = "98%";
                    maxContentDiv.innerHTML = '<iframe src="' + maxcontent + 
                    '" frameborder="0" height=100% width=100%>required iframe enabled browser</iframe>';
                }
			}
		} else {
			map.panTo(this.point);
		}
	}
	
	this.isVisible = function () {
		return _visible;
	}
	this.show = function () {
		if (_visible) return;
		if (this.marker) this.marker.show();
		_visible = true;
	}

	this.hide = function () {
		if (!_visible) return;
		if (this.marker != null) this.marker.hide();
		_visible = false;
	}
	
	if (visible) {
		this.show();
	} else {
		this.hide();
	}
	return this;
}


var PGTool = new function () {
	this.fmtNum = function (x) {
		var n = x.toString().split(".");
		n[1] = (n[1] + "000000").substr(0, 6);
		return n.join(".");
	}
	this.getLatLng = function (x, y, api) {
		switch (api) {
			case 0:
				x = x - y * 0.000046038 - x * 0.000083043 + 0.010040;
				y = y - y * 0.00010695  + x * 0.000017464 + 0.00460170;
			case 1:
				t = x;
				x = y;
				y = t;
				break;
		}
		return new GLatLng(x, y);
	}
	this.getXYPoint = function (x, y, api) {
		if (api < 2) {
			t = x;
			x = y;
			y = t;
		}
		if (api == 0) {
			nx = 1.000083049 * x + 0.00004604674815 * y - 0.01004104571;
			ny = 1.000106961 * y - 0.00001746586797 * x - 0.004602192204;
			x = nx;
			y = ny;
		}
		return {x:x, y:y};
	}
	this.createXmlHttp = function () {
		if (typeof(XMLHttpRequest) == "function") {
			return new XMLHttpRequest();
		}
		if (typeof(ActiveXObject) == "function") {
			try {
				return new ActiveXObject("Msxml2.XMLHTTP");
			} catch(e) {};
			try {
				return new ActiveXObject("Microsoft.XMLHTTP");
			} catch(e) {};
		}
		return null;
	}
	this.downloadURL = function (url, func, async, postData, contentType) {
		var xmlhttp = this.createXmlHttp();
		if (!xmlhttp) {
			return null;
		}
		if (async && func) {
			xmlhttp.onreadystatechange = function () {
				if (xmlhttp.readyState == 4) {
					func(xmlhttp);
				}
			};
		}
		try {
			if (postData) {
				xmlhttp.open("POST", url, async);
				if (!contentType) {
					contentType = "application/x-www-form-urlencoded";
				}
				xmlhttp.setRequestHeader("Content-Type", contentType);
				xmlhttp.send(postData);
			} else {
				xmlhttp.open("GET", url, async);
				xmlhttp.send(null);
			}
		} catch(e) {
			return false;
		}
		if (!async && func) func(xmlhttp);
	}

	// Pukiwiki のデフォルトのCSSではGoogleのロゴがFirefox, Operaで
	// 透過しなくなったので(2.72)、透過させる。
	this.transparentGoogleLogo = function(map) {
		var container = map.getContainer();
		for (var i=0; i<container.childNodes.length; i++) {
			var node = container.childNodes.item(i);
			if (node.tagName != "A") continue;
			if (node.hasChildNodes() == false) continue;

			var img = node.firstChild;
			if (img.tagName != "IMG") continue;
			if (img.src.match(/http:.*\/poweredby\.png/) == null) continue;

			node.style.backgroundColor = "transparent";
			break;
		}
		return;
	}
}

var PGDraw = new function () {
	var self = this;
	this.weight = 10;
	this.opacity = 0.5;
	this.color = "#00FF00";
	this.fillopacity = 0;
	this.fillcolor = "#FFFF00";

	this.line = function (plist) {
		return new GPolyline(plist, this.color, this.weight, this.opacity);
	}
	
	this.rectangle = function (p1, p2) {
		var points = new Array (
			p1,
			new GLatLng(p1.lat(), p2.lng()),
			p2,
			new GLatLng(p2.lat(), p1.lng()),
			p1
		);
		return draw_polygon (plist);
	}
	
	this.circle  = function (point, radius) {
		return draw_ngon(point, radius, 0, 48, 0, 360);
	}
	
	this.arc = function (point, outradius, inradius, st, ed) {
		while (st > ed) { ed += 360; }
		if (st == ed) {
			return this.circle(point, outradius, inradius);
		}
		return draw_ngon(point, outradius, inradius, 48, st, ed);
	}
	
	this.ngon = function (point, radius, n, rotate) {
		if (n < 3) return null;
		return draw_ngon(point, radius, 0, n, rotate, rotate+360);
	}
	
	this.polygon = function (plist) {
		return draw_polygon (plist);
	}
	
	function draw_ngon (point, outradius, inradius, div, st, ed) {
		if (div <= 2) return null;

		var incr = (ed - st) / div;
		var lat = point.lat();
		var lng = point.lng();
		var out_plist = new Array();
		var in_plist  = new Array();
		var rad = 0.017453292519943295; /* Math.PI/180.0 */
		var en = 0.00903576399827824;   /* 1/(6341km * rad) */
		var out_clat = outradius * en; 
		var out_clng = out_clat/Math.cos(lat * rad);
		var in_clat = inradius * en; 
		var in_clng = in_clat/Math.cos(lat * rad);
		
		for (var i = st ; i <= ed; i+=incr) {
			if (i+incr > ed) {i=ed;}
			var nx = Math.sin(i * rad);
			var ny = Math.cos(i * rad);

			var ox = lat + out_clat * nx;
			var oy = lng + out_clng * ny;
			out_plist.push(new GLatLng(ox, oy));

			if (inradius > 0) {
			var ix = lat + in_clat  * nx;
			var iy = lng + in_clng  * ny;
			in_plist.push (new GLatLng(ix, iy));
			}
		}

		var plist;
		if (ed - st == 360) {
			plist = out_plist;
			plist.push(plist[0]);
		} else {
			if (inradius > 0) {
				plist = out_plist.concat( in_plist.reverse() );
				plist.push(plist[0]);
			} else {
				out_plist.unshift(point);
				out_plist.push(point);
				plist = out_plist;
			}
		}

		return draw_polygon(plist);
	}

	function draw_polygon (plist) {
		if (self.fillopacity <= 0) {
		return new GPolyline(plist, self.color, self.weight, self.opacity);
		}
		return new GPolygon(plist, self.color, self.weight, self.opacity, 
		self.fillcolor, self.fillopacity); 
	}

}


//
// Center Cross コントロール
//
function PGCross() {
	this.map = null;
	this.container = null;
};
PGCross.prototype = new GControl(false, false);

PGCross.prototype.initialize = function(map) {
	this.map = map;
	this.container = document.createElement("div");
	var crossDiv = this.createWidget(16, 2, "#000000");
	this.container.appendChild(crossDiv);
	this.container.width = crossDiv.width;
	this.container.height = crossDiv.height;
	
	var cross = this;
	GEvent.addDomListener(map, "resize", function(e) {
		var size = cross.getCrossCenter();
		cross.container.style.top  = size.height + 'px';
		cross.container.style.left = size.width  + 'px';
	});
	// TODO:Cross上でのマウスイベントを下のレイヤーのMapに伝播させる。
	//GEvent.addDomListener(crossDiv, "dblclick", function(e) {
	//		if (map.doubleClickZoomEnabled())
	//			map.zoomIn();
	//});
	
	map.getContainer().appendChild(this.container);
	
	info = map.getInfoWindow();
	var container = this.container;
	var hidefunc = function() { map.getContainer().removeChild(container); }
	var showfunc = function() { map.getContainer().appendChild(container); }
	GEvent.addListener(map, "infowindowclose", function(){ showfunc(); });
	GEvent.addListener(info, "maximizeclick", function(){ hidefunc(); });
	GEvent.addListener(info, "restoreend", function(){ showfunc(); });

	return this.container;
}

PGCross.prototype.getCrossCenter = function() {
	var msize = this.map.getSize();
	var x = (msize.width  - this.container.width)/2.0;
	var y = (msize.height - this.container.height)/2.0;
	return new GSize(Math.ceil(x), Math.ceil(y));
}

PGCross.prototype.createWidget = function(nsize, lwidth, lcolor) {
	var hsize = (nsize - lwidth) / 2;
	var nsize = hsize * 2 + lwidth;
	var border = document.createElement("div");
	border.width = nsize;
	border.height = nsize;
	var table = '\
<table width="'+nsize+'" border="0" cellspacing="0" cellpadding="0">\
  <tr>\
  <td style="width:'+ hsize+'px; height:'+hsize+'px; background-color:transparent; border:0px;"></td>\
  <td style="width:'+lwidth+'px; height:'+hsize+'px; background-color:'+lcolor+';  border:0px;"></td>\
  <td style="width:'+ hsize+'px; height:'+hsize+'px; background-color:transparent; border:0px;"></td>\
  </tr>\
  <tr>\
  <td style="width:'+ hsize+'px; height:'+lwidth+'px; background-color:'+lcolor+'; border:0px;"></td>\
  <td style="width:'+lwidth+'px; height:'+lwidth+'px; background-color:'+lcolor+'; border:0px;"></td>\
  <td style="width:'+ hsize+'px; height:'+lwidth+'px; background-color:'+lcolor+'; border:0px;"></td>\
  </tr>\
  <tr>\
  <td style="width:'+ hsize+'px; height:'+hsize+'px; background-color:transparent; border:0px;"></td>\
  <td style="width:'+lwidth+'px; height:'+hsize+'px; background-color:'+lcolor+';  border:0px;"></td>\
  <td style="width:'+ hsize+'px; height:'+hsize+'px; background-color:transparent; border:0px;"></td>\
  </tr>\
</table>';
	border.innerHTML = table;
	border.firstChild.style.MozOpacity = 0.5;
	border.firstChild.style.filter = 'alpha(opacity=50)';
	return border;
}

PGCross.prototype.getDefaultPosition = function() {
	return new GControlPosition(G_ANCHOR_BOTTOM_RIGHT, this.getCrossCenter());
}

PGCross.prototype.changeStyle = function(color, opacity) {
	var table = this.container.firstChild.firstChild;
	var children = table.getElementsByTagName("td");
	for (var i = 0; i < children.length; i++) {
		var node = children[i];
		if (node.style.backgroundColor != "transparent") {
			node.style.backgroundColor = color;
		}
	}
	table.style.MozOpacity = opacity;
	table.style.filter = 'alpha(opacity=' + (opacity * 100) + ')';
}

//
// マーカーON/OFF
//

function p_googlemaps_marker_toggle (page, mapname, check, name) {
	var markers = googlemaps_markers[page][mapname];
	for (key in markers) {
		var m = markers[key];
		if (m.icon == name) {
			if (check.checked) {
				m.show();
			} else {
				m.hide();
			}
		}
	}
}

function p_googlemaps_togglemarker_checkbox (page, mapname, undefname) {
	var icons = {};
	var markers = googlemaps_markers[page][mapname];
	for (key in markers) {
		var map = markers[key].map;
		var icon = markers[key].icon;
		icons[icon] = 1;
	}
	var iconlist = new Array();
	for (n in icons) {
		iconlist.push(n);
	}
	iconlist.sort();

	var r = document.createElement("div");
	var map = document.getElementById(mapname);
	map.parentNode.insertBefore(r, map.nextSibling);

	for (i in iconlist) {
		var name = iconlist[i];
		var id = "ti_" + mapname + "_" + name;
		var input = document.createElement("input");
		var label = document.createElement("label");
		input.setAttribute("type", "checkbox");
		input.id = id;
		label.htmlFor = id;
		if (name == "") {
		label.appendChild(document.createTextNode(undefname));
		} else {
		label.appendChild(document.createTextNode(name));
		}
		eval("input.onclick = function(){p_googlemaps_marker_toggle('" + page + "','" + mapname + "', this, '" + name + "');}");

		r.appendChild(input);
		r.appendChild(label);
		input.setAttribute("checked", "checked");
	}
}

function p_googlemaps_regist_marker (page, mapname, center, key, option) {
	if (document.getElementById(mapname) == null) {
		mapname = mapname.replace(/^pukiwikigooglemaps2_/, "");
		page = mapname.match(/(^.*?)_/)[1];
		mapname = mapname.replace(/^.*?_/, "");
		alert({$qm->m['plg_googlemaps2']['err_mark_failed']});
		return;
	}
	var m = new PGMarker(center, option.icon, page, mapname, option.noicon, true, option.title, option.maxtitle, option.maxcontent, option.minzoom, option.maxzoom);
	m.setHtml(option.infohtml);
	m.setZoom(option.zoom);
	googlemaps_markers[page][mapname][key] = m;
}

function p_googlemaps_regist_to_markermanager (page, mapname, use_marker_mgr) {
	var markers = googlemaps_markers[page][mapname];
	
	if (use_marker_mgr == false) {
		for (key in markers) {
			var m = markers[key];

			if (m.marker) {
				googlemaps_maps[page][mapname].addOverlay(m.marker);
			}
		}
		return;
	}

	var mgr = googlemaps_marker_mgrs[page][mapname];
	var levels = new Object();

	for (key in markers) {
		var m = markers[key];
		var minzoom = m.minzoom<0 ? 0:m.minzoom;
		var maxzoom = m.maxzoom>17? 17:m.maxzoom;
		if (minzoom > maxzoom) {
			maxzoom = minzoom;
		}

		if (m.marker) {
			if (levels[minzoom] == undefined) {
				levels[minzoom] = new Object();
			}
			if (levels[minzoom][maxzoom] == undefined) {
				levels[minzoom][maxzoom] = new Array();
			}
			levels[minzoom][maxzoom].push(m.marker);
		}
	}

	for (minzoom in levels) {
		for (maxzoom in levels[minzoom]) {
			if (levels[minzoom][maxzoom])
			mgr.addMarkers(levels[minzoom][maxzoom], parseInt(minzoom), parseInt(maxzoom));
		}
	}
	mgr.refresh();
}

window.onload = function () {
	if (GBrowserIsCompatible()) {
		while (onloadfunc.length > 0) {
			onloadfunc.shift()();
		}
		while (onloadfunc2.length > 0) {
			onloadfunc2.shift()();
		}
	}
}

window.onunload = function () {
	GUnload();
}
//]]>
</script>\n
EOD;
}

?>
