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

define ('PLUGIN_GOOGLEMAPS2_INSERTMARKER_DIRECTION', 'down'); //追加していく方向(up, down)
define ('PLUGIN_GOOGLEMAPS2_INSERTMARKER_TITLE_MAXLEN', 40); //タイトルの最長の長さ
define ('PLUGIN_GOOGLEMAPS2_INSERTMARKER_CAPTION_MAXLEN', 400); //キャプションの最長の長さ
define ('PLUGIN_GOOGLEMAPS2_INSERTMARKER_URL_MAXLEN', 1024); //URLの最長の長さ

function plugin_googlemaps2_insertmarker_action() {
	global $script, $vars, $now;
	$qm = get_qm();

	if (PKWK_READONLY) die_message($qm->m['fmt_err_pkwk_readonly']);
	
	if(is_numeric($vars['lat'])) $lat = $vars['lat']; else return;
	if(is_numeric($vars['lng'])) $lng = $vars['lng']; else return;
	if(is_numeric($vars['zoom'])) $zoom = $vars['zoom']; else return;
	if(is_numeric($vars['mtype'])) $mtype = $vars['mtype']; else return;

	$map    = htmlspecialchars(trim($vars['map']));
	$icon   = htmlspecialchars($vars['icon']);
	$title   = substr($vars['title'], 0, PLUGIN_GOOGLEMAPS2_INSERTMARKER_TITLE_MAXLEN);
	$caption = substr($vars['caption'], 0, PLUGIN_GOOGLEMAPS2_INSERTMARKER_CAPTION_MAXLEN);
	$image   = substr($vars['image'], 0, PLUGIN_GOOGLEMAPS2_INSERTMARKER_URL_MAXLEN);
	$maxurl  = substr($vars['maxurl'], 0, PLUGIN_GOOGLEMAPS2_INSERTMARKER_URL_MAXLEN);

	$minzoom = $vars['minzoom'] == '' ? '' : (int)$vars['minzoom'];
	$maxzoom = $vars['maxzoom'] == '' ? '' : (int)$vars['maxzoom'];

	$title   = htmlspecialchars(str_replace("\n", '', $title));
	$caption = htmlspecialchars(str_replace("\n", '', $caption));
	$image   = htmlspecialchars($image);
	$maxurl  = htmlspecialchars($maxurl);

	if ($map == '') return;
	$marker = '-&googlemaps2_mark('.$lat.', '.$lng.', map='.$map.', title='.$title;
	if ($caption != '') $marker .= ', caption='.$caption;
	if ($icon != '')    $marker .= ', icon='.$icon;
	if ($image != '')   $marker .= ', image='.$image;
	if ($maxurl != '')  $marker .= ', maxurl='.$maxurl;
	if ($minzoom != '')  $marker .= ', minzoom='.$minzoom;
	if ($maxzoom != '')  $marker .= ', maxzoom='.$maxzoom;
	$marker .= ');';
	
	$no       = 0;
	$postdata = '';
	$above    = ($vars['direction'] == 'up');
	foreach (get_source($vars['refer']) as $line) {
		if (! $above) $postdata .= $line;
		if (preg_match('/^#googlemaps2_insertmarker/i', $line) && $no++ == $vars['no']) {
			if ($above) {
				$postdata = rtrim($postdata) . "\n" . $marker . "\n";
			} else {
				$postdata = rtrim($postdata) . "\n" . $marker . "\n";
			}
		}
		if ($above) $postdata .= $line;
	}

	$title = $qm->m['fmt_title_updated'];
	$body = '';
	if (md5(@join('', get_source($vars['refer']))) != $vars['digest']) {
		$title = $qm->m['plg_comment']['title_collided'];
		$body  = $qm->m['plg_comment']['wng_collided'] . make_pagelink($vars['refer']);
	}

	page_write($vars['refer'], $postdata);

	$retvars['msg']  = $title;
	$retvars['body'] = $body;
	$vars['page'] = $vars['refer'];

	//表示していたポジションを返すcookieを追加
	$cookieval = 'lat|'.$lat.'|lng|'.$lng.'|zoom|'.$zoom.'|mtype|'.$mtype;
	if ($minzoom) $cookieval .= '|minzoom|'.$minzoom;
	if ($maxzoom) $cookieval .= '|maxzoom|'.$maxzoom;
	setcookie('pukiwkigooglemaps2insertmarker'.$vars['no'], $cookieval);
	return $retvars;
}

function plugin_googlemaps2_insertmarker_get_default() {
	global $vars;
	return array(
		'map'       => PLUGIN_GOOGLEMAPS2_DEF_MAPNAME,
		'direction' => PLUGIN_GOOGLEMAPS2_INSERTMARKER_DIRECTION
	);
}
//inline型はテキストのパースがめんどくさそうなのでとりあえず放置。
function plugin_googlemaps2_insertmarker_inline() {
	$qm = get_qm();
	return "<div>{$qm->m['plg_googlemaps2_insertmarker']['err_not_impl_inline']}</div>\n";
}
function plugin_googlemaps2_insertmarker_convert() {
	global $vars, $digest, $script;
	static $numbers = array();
	$qm = get_qm();

	if (!defined('PLUGIN_GOOGLEMAPS2_DEF_KEY')) {
		return $qm->replace('plg_googlemaps2.err_not_called', 'googlemaps2_insertmarker');
	}
	if (!plugin_googlemaps2_is_supported_profile()) {
		return '';
	}

	if (PKWK_READONLY) {
		return $qm->m['fmt_err_pkwk_readonly']. "<br>";
	}

	//オプション
	
	$defoptions = plugin_googlemaps2_insertmarker_get_default();
	$inoptions = array();
	foreach (func_get_args() as $param) {
		$pos = strpos($param, '=');
		if ($pos == false) continue;
		$index = trim(substr($param, 0, $pos));
		$value = htmlspecialchars(trim(substr($param, $pos+1)));
		$inoptions[$index] = $value;
	}

	if (array_key_exists('define', $inoptions)) {
		$vars['googlemaps2_insertmarker'][$inoptions['define']] = $inoptions;
		return '';
	}

	$coptions = array();
	if (array_key_exists('class', $inoptions)) {
		$class = $inoptions['class'];
		if (array_key_exists($class, $vars['googlemaps2_insertmarker'])) {
			$coptions = $vars['googlemaps2_icon'][$class];
		}
	}
	$options = array_merge($defoptions, $coptions, $inoptions);
	$map       = plugin_googlemaps2_addprefix($vars['page'], $options['map']);
	$mapname   = $options['map'];//ユーザーに表示させるだけのマップ名（prefix除いた名前）
	$direction = $options['direction'];
	$script    = get_script_uri();
	$s_page    = $vars['page'];

	if (! isset($numbers[$s_page]))
		$numbers[$s_page] = 0;
	$no = $numbers[$s_page]++;

	$imprefix = "_p_googlemaps2_insertmarker_".$s_page."_".$no;
	$err_map_not_found = $qm->replace('plg_googlemaps2_insertmarker.err_map_not_found', $mapname);
	$output = <<<EOD
<form action="$script" id="${imprefix}_form" method="post">
<div style="padding:2px;">
  <input type="hidden" name="plugin"    value="googlemaps2_insertmarker" />
  <input type="hidden" name="refer"     value="$s_page" />
  <input type="hidden" name="direction" value="$direction" />
  <input type="hidden" name="no"        value="$no" />
  <input type="hidden" name="digest"    value="$digest" />
  <input type="hidden" name="map"       value="$mapname" />
  <input type="hidden" name="zoom"      value="10" id="${imprefix}_zoom"/>
  <input type="hidden" name="mtype"     value="0"  id="${imprefix}_mtype"/>

  {$qm->m['plg_googlemaps2_insertmarker']['label_latitude']}: <input type="text" name="lat" id="${imprefix}_lat" size="10" />
  {$qm->m['plg_googlemaps2_insertmarker']['label_longitude']}: <input type="text" name="lng" id="${imprefix}_lng" size="10" />
  {$qm->m['plg_googlemaps2_insertmarker']['label_title']}:
  <input type="text" name="title"    id="${imprefix}_title" size="20" />
  {$qm->m['plg_googlemaps2_insertmarker']['label_icon']}:
  <select name="icon" id ="${imprefix}_icon">
  <option value="Default">{$qm->m['plg_googlemaps2_insertmarker']['option_default']}</option>
  </select>
  <br />
  {$qm->m['plg_googlemaps2_insertmarker']['label_img']}:
  <input type="text" name="image"    id="${imprefix}_image" size="20" />
  {$qm->m['plg_googlemaps2_insertmarker']['label_maxurl']}:
  <input type="text" name="maxurl"   id="${imprefix}_maxurl" size="20" />
  <br />
  {$qm->m['plg_googlemaps2_insertmarker']['label_minzoom']}:
  <select name="minzoom" id ="${imprefix}_minzoom">
  <option value="">--</option>
  <option value="0"> 0</option> <option value="1"> 1</option>
  <option value="2"> 2</option> <option value="3"> 3</option>
  <option value="4"> 4</option> <option value="5"> 5</option>
  <option value="6"> 6</option> <option value="7"> 7</option>
  <option value="8"> 8</option> <option value="9"> 9</option>
  <option value="10">10</option> <option value="11">11</option>
  <option value="12">12</option> <option value="13">13</option>
  <option value="14">14</option> <option value="15">15</option>
  <option value="16">16</option> <option value="17">17</option>
  </select>
  {$qm->m['plg_googlemaps2_insertmarker']['label_maxzoom']}:
  <select name="maxzoom" id ="${imprefix}_maxzoom">
  <option value="">--</option>
  <option value="0"> 0</option> <option value="1"> 1</option>
  <option value="2"> 2</option> <option value="3"> 3</option>
  <option value="4"> 4</option> <option value="5"> 5</option>
  <option value="6"> 6</option> <option value="7"> 7</option>
  <option value="8"> 8</option> <option value="9"> 9</option>
  <option value="10">10</option> <option value="11">11</option>
  <option value="12">12</option> <option value="13">13</option>
  <option value="14">14</option> <option value="15">15</option>
  <option value="16">16</option> <option value="17">17</option>
  </select>
  <br />
  {$qm->m['plg_googlemaps2_insertmarker']['label_detail']}:
  <textarea name="caption" id="${imprefix}_caption" rows="2" cols="55"></textarea>
  <input type="submit" name="Mark" value="{$qm->m['plg_googlemaps2_insertmarker']['btn_mark']}"/>
</div>
</form>

<script type="text/javascript">
//<![CDATA[
onloadfunc.push(function() {
	var map = googlemaps_maps['$s_page']['$map'];
	if (!map) {
		var form = document.getElementById("${imprefix}_form");
		form.innerHTML = '<div>$err_map_not_found</div>';
	} else {
		var lat   = document.getElementById("${imprefix}_lat");
		var lng   = document.getElementById("${imprefix}_lng");
		var zoom  = document.getElementById("${imprefix}_zoom");
		var mtype = document.getElementById("${imprefix}_mtype");
		var form  = document.getElementById("${imprefix}_form");
		var icon  = document.getElementById("${imprefix}_icon");

		//地図がドラッグされたりするたびに動的にパラメータを代入する
		GEvent.addListener(map, 'moveend', function() {
			lat.value = PGTool.fmtNum(map.getCenter().lat());
			lng.value = PGTool.fmtNum(map.getCenter().lng());
			zoom.value = parseInt(map.getZoom());
			mtype.value = -1;
			var curmaptype = map.getCurrentMapType();
			var maptypes  = map.getMapTypes();
			var cname = curmaptype.getName(false);
			for (i in maptypes) {
				if (maptypes[i].getName(false) == cname) {
					mtype.value = i;
					break;
				}
			}
		});
		
		//クッキーがあれば地図の位置を初期化をする。使い終えたらクッキーの中身をクリアする。
		(function () {
			var cookies = document.cookie.split(";");
			for (i in cookies) {
				var kv = cookies[i].split("=");
				for (j in kv) {
					kv[j] = kv[j].replace(/^\s+|\s+$/g, "");
				}
				if (kv[0] == "pukiwkigooglemaps2insertmarker$no") {
					if (kv.length == 2 && kv[1].length > 0) {
						var mparam = {lat:0, lng:0, zoom:10, mtype:0};
						var oparam = {maxzoom:"", minzoom:""};
						var params = decodeURIComponent(kv[1]).split("|");
						for (var j = 0; j < params.length; j++) {
							//dump(params[j] + "=" + params[j+1] + "\\n");
							switch (params[j]) {
								case "lat": mparam.lat = parseFloat(params[++j]); break;
								case "lng": mparam.lng = parseFloat(params[++j]); break;
								case "zoom": mparam.zoom = parseInt(params[++j]); break;
								case "mtype": mparam.mtype = parseInt(params[++j]); break;
								case "maxzoom": oparam.maxzoom = parseInt(params[++j]); break;
								case "minzoom": oparam.minzoom = parseInt(params[++j]); break;
								default: j++; break;
							}
						}
						map.setCenter(new GLatLng(mparam.lat, mparam.lng), 
								mparam.zoom, map.getMapTypes()[mparam.mtype]);

						var smz;
						var options;
						smz = document.getElementById("${imprefix}_minzoom")
						options = smz.childNodes;
						for (var j=0; j<options.length; j++) {
							var option = options.item(j);
							if (option.value == oparam.minzoom) {
								option.selected = true;
								break;
							}
						}

						smz = document.getElementById("${imprefix}_maxzoom")
						options = smz.childNodes;
						for (var j=0; j<options.length; j++) {
							var option = options.item(j);
							if (option.value == oparam.maxzoom) {
								option.selected = true;
								break;
							}
						}
					}
					break;
				}
			}
			document.cookie = "pukiwkigooglemaps2insertmarker$no=;";
		})();

		//入力チェック
		form.onsubmit = function () {
			if (isNaN(parseFloat(lat.value)) || isNaN(lat.value) || 
				isNaN(parseFloat(lng.value)) || isNaN(lng.value)) {
				alert({$qm->m['plg_googlemaps2_insertmarker']['err_invalid_coor']});
				return false;
			}
			return true;
		};
	}
	//このページに存在しているicon定義を全て読みこんでセレクトを更新。
	onloadfunc.push(function() {
		for(iconname in googlemaps_icons['$s_page']) {
			var opt = document.createElement("option");
			opt.value = iconname;
			opt.appendChild(document.createTextNode(iconname));
			icon.appendChild(opt);
		}
	});
});
//]]>
</script>
EOD;

	return $output;
}


?>
