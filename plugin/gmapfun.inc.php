<?php
// $Id$

// Image suffixes allowed
define('PLUGIN_GMAPFUN_IMAGE', '/\.(gif|png|jpe?g)$/i');
define('PLUGIN_GMAPFUN_ICON_PATH', 'image/gmap/');
define('PLUGIN_GMAPFUN_MARK_IMAGE_SIZE', '80');
define('PLUGIN_GMAPFUN_DEFAULT', '
<div class="plugin_gmapfun" data-type="default">
  <div class="gmap_box"><div id="gmap"></div></div>
  <div class="gmap_info">
    <div class="marker_list">
      <%markerlist%>
    </div>
    <div id="marker_info" class="marker_info_insert bubble">
      <div class="body"><div></div></div>
    </div>
  </div>
</div>
');
define('PLUGIN_GMAPFUN_TYPE1', '
<div class="plugin_gmapfun" data-type="top">
  <div class="gmap_box"><div id="gmap"></div></div>
  <div class="gmap_info">
    <div class="marker_list">
      <%markerlist%>
    </div>
    <div id="marker_info"><div></div></div>
  </div>
</div>
');
define('PLUGIN_GMAPFUN_TYPE2', '
<div class="plugin_gmapfun" data-type="side">
  <div class="gmap_box_right"><div id="gmap"></div></div>
  <div class="gmap_info_left">
    <div id="marker_info" class="marker_info_left"><div></div></div>
    <div class="marker_list_left">
      <%markerlist%>
    </div>
  </div>
  <div style="clear:both;"></div>
</div>
');
define('PLUGIN_GMAPFUN_TYPE2_BOOTSTRAP', '
<div class="plugin_gmapfun" data-type="side" data-style="bootstrap">
  <div class="row">
    <div class="col-sm-5 col-sm-push-7"><div id="gmap"></div></div>
    <div class="col-sm-7 col-sm-pull-5" style="background: none">
      <div class="gmapfun-marker-info">
        <div id="marker_info" class="marker_info_left"><div></div></div>
        <div class="marker_list_left">
          <%markerlist%>
        </div>
      </div>
    </div>
  </div>
</div>
');



function plugin_gmapfun_action()
{
	global $vars;
	
	$page = $vars['page'];
	$body = '';
	
	if (is_page($page)) {
		$body = convert_html(get_source($page));
		$qt = get_qt();
		$before = $qt->getv('beforescript');
	}
    pkwk_common_headers();
    print $before.$body;

    exit;
}

function plugin_gmapfun_convert()
{
	static $s_gmapfun_cnt = 0;
	global $script,$googlemaps_apikey;
	global $vars;
	
	$qt = get_qt();
	$qt->setv('jquery_include', true);

    $args = func_get_args();
    $last = func_num_args() - 1;
    $datalist = '';
    if (strpos($args[$last], ',') !== FALSE) {
        $datalist = array_pop($args);
    }
	list($type, $w, $h, $zoom, $addr, $lat, $lng) = array_pad($args,7,'');

	$type = ($type == '') ? 'default'    : $type;
	$addr = (trim($addr) == '') ? ''           : trim($addr);
	$lat  = ($lat == '')  ? '35.658613'  : $lat;
	$lng  = ($lng == '')  ? '139.745525' : $lng;
	$zoom = ($zoom == '') ? '15'         : $zoom;

	$w = preg_match('/^[0-9]+$/', $w) ?  $w.'px' : $w;
	$h = preg_match('/^[0-9]+$/', $h) ?  $h.'px' : $h;

	switch($type) {
		case 'top':
			$gmap_width  = ($w == '') ? '' : $w;
			$gmap_height = ($h == '') ? '300px' : $h;
			$gmap_disp = PLUGIN_GMAPFUN_TYPE1;
			break;
		case 'side':
			$gmap_height = ($h == '') ? '450px' : $h;
			if (is_bootstrap_skin())
			{
				$gmap_width  = '100%';
				$gmap_disp = PLUGIN_GMAPFUN_TYPE2_BOOTSTRAP;
			}
			else
			{
				$gmap_width  = ($w == '') ? '220px' : $w;
				$gmap_disp = PLUGIN_GMAPFUN_TYPE2;
			}
			break;
		default:
			$gmap_width  = ($w == '') ? '' : $w;
			$gmap_height = ($h == '') ? '500px' : $h;
			$gmap_disp = PLUGIN_GMAPFUN_DEFAULT;
	}

	if ($addr != '')
	{
		$geoobj = plugin_gmapfun_getGeocoding($addr);
		if ($geoobj)
		{
			$lng = $geoobj['lng'];
			$lat = $geoobj['lat'];
		}
	}

    if (isset($datalist)) {
		$flist = plugin_gmapfun_makelist($datalist);
	}
	
	$addscript = '';
	if ($s_gmapfun_cnt == 0) {
		list($icon_width, $icon_height) = getimagesize(PLUGIN_GMAPFUN_ICON_PATH.'pin.png');
		list($icon_sh_width, $icon_sh_height) = getimagesize(PLUGIN_GMAPFUN_ICON_PATH.'pin_shadow.png');
		$addscript = '
<script type="text/javascript" src="//maps.google.com/maps/api/js?sensor=false"></script>
<script src="js/infobox.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
$(function(){

// マップ処理
function gmap_initialize(){
	var ibopts = {
		content: "",
		disableAutoPan: false,
		maxWidth: "200px",
		pixelOffset: new google.maps.Size(-100, 10),
		zIndex: null,
		boxStyle: {
			backgroundColor: "#333",
			opacity: 0.85,
			color:"#fff",
			borderRadius: "10px",
			MozBorderRadius: "10px",
			WebkitBorderRadius: "10px",
			position:"relative",
			width: "200px"
		},
		closeBoxMargin: "5px 5px 2px 2px",
		closeBoxURL: "//www.google.com/intl/en_us/mapfiles/close.gif",
		infoBoxClearance: new google.maps.Size(1, 1),
		isHidden: false,
		pane: "floatPane",
		enableEventPropagation: false,
	};
	var ib = new InfoBox(ibopts);


	// マップデータを読み込み表示
	var firstData = [];
	if ($(".mapmk").length) {
		firstData = $(".mapmk:first").attr("longdesc").split(",");
	}
	else {
		firstData.push("");
		firstData.push('.$lat.');
		firstData.push('.$lng.');
	}

	var latlng = new google.maps.LatLng(parseFloat(firstData[1]),parseFloat(firstData[2]));

    var gmap_opts = {
    	backgroundColor:"#fff",
    	noCler: true,
        zoom:'.$zoom.',
    	center:latlng,
    	mapTypeId:google.maps.MapTypeId.ROADMAP
    };

    var gmap = new google.maps.Map(document.getElementById("gmap"), gmap_opts);
	if ($(".mapmk").length) {
        gmap.setCenter(latlng);
    }

	var flgSize = new google.maps.Size('.$icon_width.', '.$icon_height.');
	var flgOrigin = new google.maps.Point(0, 0);
	var flgAnchor = new google.maps.Point(0, '.$icon_height.');
	var flgImage = "'.PLUGIN_GMAPFUN_ICON_PATH.'pin.png";
	var flgIcon = new google.maps.MarkerImage(flgImage, flgSize, flgOrigin, flgAnchor);

	var flgShadowSize = new google.maps.Size('.$icon_sh_width.', '.$icon_sh_height.');
	var flgShadowOrigin = new google.maps.Point(0, 0);
	var flgShadowAnchor = new google.maps.Point(0, '.$icon_sh_height.');
	var flgShadowImage = "'.PLUGIN_GMAPFUN_ICON_PATH.'pin_shadow.png";
	var flgShadowIcon = new google.maps.MarkerImage(flgShadowImage, flgShadowSize, flgShadowOrigin, flgShadowAnchor);

	var markeropts = {
		position:"",
		map: gmap,
		icon: flgIcon,
		shadow: flgShadowIcon
	};

	$("div.mapmk").each(function(){
		var pobj = $(this);
        var shopdata = ($(this).attr("longdesc")).split(",");
        var makertitle = $(this).attr("title");
		var pos = new google.maps.LatLng(parseFloat(shopdata[1]), parseFloat(shopdata[2]));
		markeropts.position = pos;
		var marker = new google.maps.Marker(markeropts);
		google.maps.event.addListener(marker, "click", function(){
	    	focus_listitem(pobj);
			show_infobox(gmap, pos, ib, shopdata[0], makertitle);
		});
		marker.setMap(gmap);
		
		if (shopdata[0].length > 0){
			$(this).css({color:"#336699"});
		}
	}).hover(function(e){$(this).addClass("titlehover");},
			 function(e){$(this).removeClass("titlehover");
	}).click(function(){
	    focus_listitem($(this));
        var shopdata = ($(this).attr("longdesc")).split(",");
        var pos = new google.maps.LatLng(parseFloat(shopdata[1]),parseFloat(shopdata[2]));
		show_infobox(gmap, pos, ib, shopdata[0], $(this).attr("title"));
		show_marker_info($(this), shopdata[0]);
        gmap.setCenter(pos);
        return false;
    }).mouseover(function(){
        var shopdata = ($(this).attr("longdesc")).split(",");
        var pos = new google.maps.LatLng(parseFloat(shopdata[1]),parseFloat(shopdata[2]));
		show_infobox(gmap, pos, ib, shopdata[0], $(this).attr("title"));
	    focus_listitem($(this));

		if ($(this).hasClass("gmap_mklist_fm"))
		{
			show_marker_info($(this), shopdata[0]);
		}
        gmap.setCenter(pos);
	});
	if ($(".mapmk:first").length) {
		show_infobox(gmap, latlng, ib, firstData[0], $(".mapmk:first").attr("title"));
	}
	show_marker_info($(".mapmk:first"), firstData[0]);

}

function focus_listitem(obj)
{
	if (obj.hasClass("gmap_mklist_fm"))
	{
		$("div.gmap_mklist_fm").css("background-image", "url('.PLUGIN_GMAPFUN_ICON_PATH.'listframebg.png)");
		obj.css("background-image", "url('.PLUGIN_GMAPFUN_ICON_PATH.'listframebg_h.png)");
	}
}

function show_infobox(gmap, pos, ib, url, str)
{
	ib.close();

	if (str.length > 0)
	{
		ib.setPosition(pos);
		ib.setContent(\'<p style="padding:0 1em;">\'+str+\'</p><div class="marker_info_balloon"></div>\');
		ib.open(gmap);
	}
}

function show_marker_info(obj, url)
{
	var fVisible = true;
	if (url.length == 0) {
		if ($("#marker_info").children("div.body").length)
		{
			$("#marker_info").children("div.body").children("div").html("").closest("#marker_info").fadeOut("fast");
		}
		else
		{
			$("#marker_info").children("div").html("");
		}
		$("#marker_info").find("input:hidden[name=pagename]").remove();
	}
	else {
		if ($("#marker_info").hasClass("marker_info_insert"))
		{
			if (obj.parent().find("#marker_info:visible").length)
			{
				$("#marker_info").fadeOut("fast");
			}
			else{
				$("#marker_info").fadeIn();
				obj.after($("#marker_info"));

				$("#marker_info").children("div.body").children("div").load(url,function(e){
					$(window).unbind("load");
					$(e).filter("script").each(function(){
						document.getElementsByTagName("head")[0].appendChild(this);
					});
					$(window).load();
				});
			}
		}
		else 
		{
			$("#marker_info").children("div").load(url);
		}
		
		var tmpurl = url.split("?");
		var page = tmpurl[1].match(/.*page=(.*)/)[1];
		
		$("#marker_info input:hidden[name=pagename]").remove();
		$("#marker_info").append("<input type=\"hidden\" name=\"pagename\" value=\""+page+"\" />");
	}
	
}

google.maps.event.addDomListener(window,"load",gmap_initialize);

if ($("div.gmap_box_right").length){
	var padding = parseInt($("div.gmap_box_right").css("padding-left").split("px")[0])
		+ parseInt($("div.gmap_box_right").css("padding-right").split("px")[0]);
	var w = $("#body").width() - ($("div.gmap_box_right").width() + padding);
	$("div.gmap_info_left").width(w);
}

});
// -->
</script>
<style type="text/css">

div.gmap_box,div.gmap_box_right{
background:transparent;
padding-left:15px;
}
div.gmap_box_right{
background:transparent url('.PLUGIN_GMAPFUN_ICON_PATH.'vline.png) no-repeat 0 0;
float:right;
}

div#gmap{
width:'.$gmap_width.';
height:'.$gmap_height.';
background:transparent url(image/loading.gif) 50% 50% no-repeat;
}

div.gmap_info, div.gmap_info_left{
width:100%;
margin:0;
overflow:hidden;
}
div.gmap_info_left{
width:290px;
float:left;
}
#marker_info{
position:relative;
}
div.marker_list, div.marker_list_left{
background:transparent url('.PLUGIN_GMAPFUN_ICON_PATH.'hline.png) repeat-x 0 100%;
padding-top:10px;
padding-bottom:10px;
}
div.marker_list_left{
background:transparent url('.PLUGIN_GMAPFUN_ICON_PATH.'hline.png) repeat-x 0 0;
}

div.marker_info_left{
height:290px;
overflow:auto;
margin-bottom:10px;
}

div.marker_info_insert{
margin-bottom:10px;
}

div.marker_info_balloon {
background:transparent url('.PLUGIN_GMAPFUN_ICON_PATH.'sankaku.png) no-repeat;
width:10px;
height:10px;
top:-10px;
left:'.(95 + $icon_width/2).'px;
position:absolute;
}

div.gmap_mklist_dump {
cursor:pointer;
}
div.mapmk{
color:#333;
margin:10px 0 0 0;
font-weight:bold;
background:url('.PLUGIN_GMAPFUN_ICON_PATH.'pin.png) no-repeat 0 0;
padding-left:'.($icon_width+5).'px;
line-height:'.$icon_height.'px;
cursor:pointer;
}

div.gmap_mklist_fm {
width:94px;
height:114px;
float:left;
background:transparent url('.PLUGIN_GMAPFUN_ICON_PATH.'listframebg.png) no-repeat;
position:relative;
cursor:pointer;
padding-left:0;
}

div.gmap_mklist_fm img{
top:7px;
left:7px;
position:absolute;
cursor:pointer;
}
div.pola_title{
bottom:5px;
left:7px;
position:absolute;
width:80px;
height:1.5em;
line-height:1.5em;
font-size:10px;
overflow:hidden;
}
div.bubble {
margin:0 0 0 30px;
border-left: 7px solid #6699CC;
border-top: 7px solid transparent;
-border-top-color: white;
opacity:0.9;
}
div.bubble div.body {
margin: 0 0 0 -30px;
border-radius: 10px;
border: 3px solid #6699CC;
background: white;
}
div.bubble div.body div {
margin:5px;
overflow:auto;
height:200px;
}
div.titlehover{
color:#0066CC;
}

[data-style="bootstrap"] .gmapfun-marker-info {
    background: transparent url(image/gmap/vline.png) no-repeat 100% 0;
}

@media (max-width: 768px) {
  [data-style="bootstrap"] #gmap {
    height: 250px;
  }
  [data-style="bootstrap"] #marker_info {
	padding: 15px 0;
    height: 150px;
  }
  [data-style="bootstrap"] .gmapfun-marker-info {
    background: none;
  }
}
</style>
';
	}
	$s_gmapfun_cnt++;

	$list_str = '';
	foreach($flist as $f){
		if ($f != '') {
			if (trim($f['address']) != '')
			{
				$geoobj = plugin_gmapfun_getGeocoding(trim($f['address']));
				if ($geoobj)
				{
					$f['lng'] = $geoobj['lng'];
					$f['lat'] = $geoobj['lat'];
				}
			}
			$f['lat'] = ($f['lat'] == '') ? '35.658613'  : $f['lat'];
			$f['lng'] = ($f['lng'] == '') ? '139.745525' : $f['lng'];
		
			if ($type == 'default')
			{
				$list_str .= '<div class="gmap_mklist_dump">';
				$list_str .= '<div class="mapmk" longdesc="'.$f['link'].','.$f['lat'].','.$f['lng'].'" title="'.$f['title'].'">'.$f['title'].'</div>
</div>';
			}
			else {
				$list_str .= '<div class="gmap_mklist_fm mapmk" longdesc="'.$f['link'].','.$f['lat'].','.$f['lng'].'" title="'.$f['title'].'">';
				if ($f['img'] != '') {
					$size = "";
					$position = "";
	 				list($width, $height) = getimagesize($f['img']);
	 				$sz = PLUGIN_GMAPFUN_MARK_IMAGE_SIZE;
	 				if ($width > $height) {
	 					$size = ' width="'.PLUGIN_GMAPFUN_MARK_IMAGE_SIZE.'"';
	 					if (PLUGIN_GMAPFUN_MARK_IMAGE_SIZE > $width) {
	 						$size = ' width="'.$width.'"';
	 					}
		 				$position = 'top:'.(7+(PLUGIN_GMAPFUN_MARK_IMAGE_SIZE - ($height *  (PLUGIN_GMAPFUN_MARK_IMAGE_SIZE / $width))) / 2) .'px;';
	 				}
	 				else {
	 					$size = ' height="'.PLUGIN_GMAPFUN_MARK_IMAGE_SIZE.'"';
	 					if (PLUGIN_GMAPFUN_MARK_IMAGE_SIZE > $height) {
		 					$size = ' height="'.$height.'"';
	 					}
	 					$position = 'left:'.(7+(PLUGIN_GMAPFUN_MARK_IMAGE_SIZE - ($width *  (PLUGIN_GMAPFUN_MARK_IMAGE_SIZE / $height))) / 2) .'px;';
	 				}
					$list_str .= '<img src="'.$f['img'].'" class="mapmk" '.$size.' style="'.$position.'" />';
				}
				$list_str .= '<div class="pola_title">'.$f['title'].'</div></div>';
			}
		}
	}
	$list_str .= '<div style="clear:both;"></div>';
	$body = str_replace('<%markerlist%>',$list_str, $gmap_disp);

	if (check_editable($vars['page'], false, false) ){
		$addscript .= '
<style type="text/css">
.gmap_edit_page
{
border-radius:7px;
-ms-filter: "alpha( opacity=60 )";/* IE8 */
filter: alpha( opacity=60 );/* IE6-7 */
opacity: 0.6;
background-color: #333;
position: absolute;
bottom:5px;
left:0px;
width:90%;
padding:10px;
text-align:center;
color:#fff;
cursor:pointer;
}
</style>
<script type="text/javascript">
<!--
	$(function(){
		$("div#marker_info").click(function(){
			var url = page_exists($(this));
			if (url)
			{
				location.href = url;
			}
			return false;
		})
		.hover(
			function(){
				var url = page_exists($(this));
				if (url)
				{
					$(this).append("<div class=\"gmap_edit_page\">クリックするとこのページの編集ができます</div>");
				}
			},
			function(){
				$(this).children("div.gmap_edit_page").remove();
			}
		);
		
		function page_exists(obj)
		{
			var pageobj = obj.find("input:hidden[name=pagename]");
			if (pageobj.length)
			{
				var tmpurl = location.href.split("?");
				return tmpurl[0]+"?cmd=edit&page="+pageobj.val();
			}
			return false;
		}
	});
// -->
</script>
';
	}

	$qt->appendv_once('plugin_gmapfun'+$s_gmapfun_cnt, 'beforescript', $addscript);


	return $body;
}

function plugin_gmapfun_makelist($datalist)
{
	global $script;

    $datalist = str_replace("\r", "\n", str_replace("\r\n", "\n", $datalist));
    $lines = explode("\n", $datalist);
    $flist = array();
    foreach ($lines as $l) {
    	if ($l != '') {
	    	list($addr,$title,$link,$img,$lat,$lng) = array_pad(explode(',', $l), 6, '');
	    	
			//画像ファイル
			if (!preg_match(PLUGIN_GMAPFUN_IMAGE, $img)) {
				$img = "";
			}
			else {
				if( !is_file($img) ){			
					$img = SWFU_IMAGE_DIR.$img;
					if( !is_file($img) ){
						$img = '';
					}
				}
			}
			
			// リンク先
			if (!is_url($link)) {
				if (is_page($link)) {
					$link = $script.'?plugin=gmapfun&page='.rawurlencode($link);
				}
				else {
					$link = '';
				}
			}
	    	
	    	$flist[] = array('lat'=>$lat, 'lng'=>$lng, 'img'=>$img, 'link'=>$link, 'title'=>$title, 'address'=>$addr);
		}
	}
	
	return $flist;
}


function plugin_gmapfun_getGeocoding($address)
{ 
    // 引数が空の場合、空の配列を返す
    if (empty($address)) {
        return FALSE;
    }

    $schema = is_https() ? 'https:' : 'http:';
    $address = rawurlencode($address);

    // Google Map Api から Json形式で緯度・経度等のデータを取得
//    $geo_url = "//maps.google.com/maps/api/geocode/json?address={$address}&sensor=false&language=ja";
    $geo_url = $schema . "//maps.googleapis.com/maps/api/geocode/json?address={$address}&language=ja&sensor=false";
    $geostr = file_get_contents($geo_url);
    $json = json_decode($geostr,true);
	return $json['results'][0]['geometry']['location'];
}
?>