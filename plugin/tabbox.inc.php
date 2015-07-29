<?php
// $Id$

define('PLUGIN_TABBOX_DEF_TAB', 'background:#fff url(image/tabbox_tab.png) repeat-x scroll 0 0;font-size:0.9em;border-top: 1px solid #ccc;border-left: 1px solid #ccc;border-right: 1px solid #ccc;border-top-left-radius:7px;border-top-right-radius:7px;-webkit-border-top-left-radius:7px;-webkit-border-top-right-radius:7px;-moz-border-radius-topleft:7px;-moz-border-radius-topright:7px;');
define('PLUGIN_TABBOX_DEF_SELECTED', 'background:#fff url(image/tabbox_selected.png) repeat scroll 0 0;border-bottom:1px none #fff;color:#000000;font-weight:bold;');
define('PLUGIN_TABBOX_DEF_HOVER', 'text-decoration:underline;');
define('PLUGIN_TABBOX_DEF_BOX', 'background:#fff none repeat scroll 0 0;border:1px solid #ccc;');
define('PLUGIN_TABBOX_DEF_DEFAULT', 1);
define('PLUGIN_TABBOX_DEF_HEIGHT', 'auto');


function plugin_tabbox_convert()
{
	static $s_tab_cnt = 0;
	static $s_tab_confnum = 0;

	//jquery ライブラリの読み込み
	$qt = get_qt();
	$qt->setv('jquery_include', true);
	
    $args = func_get_args();
    $last = func_num_args() - 1;

	$body = '';
	$ret = $head = '';
	$options = array(
		'tab'      => PLUGIN_TABBOX_DEF_TAB,
		'selected' => PLUGIN_TABBOX_DEF_SELECTED,
		'hover'    => PLUGIN_TABBOX_DEF_HOVER,
		'box'      => PLUGIN_TABBOX_DEF_BOX,
		'default'  => PLUGIN_TABBOX_DEF_DEFAULT,
		'height'   => PLUGIN_TABBOX_DEF_HEIGHT
	);
	$config = false;
	foreach ($args as $arg) {
		if (trim($arg) == 'conf') {
			$config = true;
		}
	}

	if ($config) {
		if ($last > 0) {
			$body = array_pop($args);
		}
		$options['conf'] = 'conf';
		$s_tab_confnum++;
	}
	else {
	    if ($s_tab_cnt == 0) {
			$s_tab_confnum++;
	    }

		$body = array_pop($args);
	    foreach ($args as $arg) {
	        list($key, $val) = explode('=', $arg, 2);
	        $options[$key] = htmlspecialchars($val);
	    }
	    $options['tab'] = isset($options['tab']) ? $options['tab'] : PLUGIN_TABBOX_DEF_TAB;
	    $options['selected'] = isset($options['selected']) ? $options['selected'] : PLUGIN_TABBOX_DEF_SELECTED;
	    $options['hover'] = isset($options['hover']) ? $options['hover'] : PLUGIN_TABBOX_DEF_HOVER;
	    $options['box']   = isset($options['box'])   ? $options['box']   : PLUGIN_TABBOX_DEF_BOX;
	    $options['default']  = isset($options['default'])  ? $options['default']  : PLUGIN_TABBOX_DEF_DEFAULT;
	    $options['height']  = isset($options['height'])  ? $options['height']  : PLUGIN_TABBOX_DEF_HEIGHT;
	}
    $body = str_replace("\r", "\n", str_replace("\r\n", "\n", $body));
    
    // confが指定された場合
   	if (isset($options['conf'])) {
   		$tmp = explode("\n", $body);
	    foreach ($tmp as $buff) {
			list($key, $val) = explode('=', $buff, 2);
			$options[$key] = htmlspecialchars($val);
	    }
	    // confに指定がない場合、デフォルトを設定
	    $options['tab'] = isset($options['tab']) ? $options['tab'] : PLUGIN_TABBOX_DEF_TAB;
	    $options['selected'] = isset($options['selected']) ? $options['selected'] : PLUGIN_TABBOX_DEF_TAB;
	    $options['hover'] = isset($options['hover']) ? $options['hover'] : PLUGIN_TABBOX_DEF_HOVER;
	    $options['box']   = isset($options['box'])   ? $options['box']   : PLUGIN_TABBOX_DEF_BOX;
	    $options['default'] = isset($options['default'])   ? $options['default']  : PLUGIN_TABBOX_DEF_DEFAULT;
	    $options['height'] = isset($options['default'])   ? $options['default']  : PLUGIN_TABBOX_DEF_HEIGHT;
   	}
   	else {
        $lines = explode("\n", $body);
        $buff = "";
        $box = array();
   		$title = array();
   		$link = array();
	    foreach ($lines as $l) {
			if (preg_match("/^\*\s?(.*)$/",$l,$matches)) {
				if (count($title) > 0) {
					$box[] = $buff;
				}
				$tmp = strip_htmltag(substr(convert_html($matches[1]), 3, -5), FALSE);
				$title[] = $tmp;
				$buff = '';
    		}
    		else {
    			$buff .= "{$l}\n";
    		}
	    }
	    if (count($title) > 0) {
			$box[] = $buff;
	    }
   	}

    // はじめての定義の場合、javascriptを出力
	if ($s_tab_cnt == 0) {
		$head = '
<script type="text/javascript">
<!--
$(document).ready(function(){

  	$("div.max").each(function(){
		var maxh = 0;
		$(this).find("div").each(function(){
			if ($(this).height() > maxh) {
				maxh = $(this).height();
			}
		});
		$(this).find("div").each(function(){
			$(this).height(maxh);
		});
	});

	$("div.tabpanel > div.tabbox").hide();
	if (window.location.hash.length == 0) {
		// default指定があるため
	}
	else {
		var thistab = window.location.hash.split("-");
		$("ul.tablist li").children("a").each(function(){
			var tmptab = $(this).attr("href").split("-");
			if (thistab[0] == tmptab[0]) {
				$(this).removeClass("selected");
			}
		});
		$(window.location.hash).show();
		$("a[href="+window.location.hash+"]").addClass("selected");
	}

	$("ul.tablist").each(function(){
		var tabDefault = $(this).find("a.selected").attr("href").split("#");
		$("#"+tabDefault[tabDefault.length-1]).show();
	});
	$("ul.tablist > li > a").click(function() {
		if ($(this).is(".selected")) {
			return false;
		}
		var objtab = $(this);
  		splitID = $(this).attr("href").split("#");
  		targetID = "#"+splitID[splitID.length-1];
  		var prevh = $(this).closest("div.tabpanel").find("div:visible").height();
  		$(this).closest("div.tabpanel").find("div.tabbox:visible").animate({height:$(targetID).height()},"fast","swing",function(){
  			$(this).hide();
  			objtab.closest("ul.tablist").find("li").children("a").removeClass();
			objtab.addClass("selected");
			$(targetID + ", "+ targetID + " *").show();
			$(this).height(prevh);
		});

		return false;
	});
});
//-->
</script>
<style type="text/css">
ul.tablist{
margin: 0 !important;
padding: 1px 0;
width:100%;
}

ul.tablist li {
float:left;
list-style-image:none;
list-style-position:outside;
list-style-type:none;
margin: 0 0 -1px;
padding: 0;
}

ul.tablist a {
outline:none;
display:block;
margin:0 1px 0 0;
padding:3px 10px;
position:relative;
z-index:1;
line-height:normal;
background:#fff url(image/tabbox_tab.png) repeat-x scroll 0 0;
font-size:0.9em;
border-top: 1px solid #ccc;
border-left: 1px solid #ccc;
border-right: 1px solid #ccc;
}

div.tabpanel div.tabbox {
display:block;
clear:left;
font-weight:normal;
line-height:normal;
padding:10px 10px 8px;
margin:0 0;
position:relative;
z-index:1;
}
</style>
';
	}

	$s_tab_cnt++;
	$dclass = "tabconf$s_tab_confnum";
	if (isset($options['conf']) || $s_tab_cnt == 1) {
		$head .= '
<style type="text/css">
div.'.$dclass.' ul.tablist a {
'.$options['tab'].'
}

div.'.$dclass.' ul.tablist a:hover {
'.$options['hover'].'
}

div.'.$dclass.' ul.tablist a.selected {
cursor:default;
text-decoration:none;
z-index:5;
'.$options['selected'].'
}

div.'.$dclass.' div.tabbox {
'.$options['box'].'
}
</style>
';
	}
	if (!isset($options['conf'])) {
		$ret .= '<div class="tabpanel '.$dclass.' '.$options['height'].'">';
		$ret .= '<ul class="tablist">';
		$tabid = 'tab'.$s_tab_cnt;
		for ($i=0; $i<count($title); $i++) {
			$tabcnt = $i+1;
			$selclass = ($options['default'] == $tabcnt) ? ' class="selected"' : '';
		    $ret .= '<li><a '.$selclass.' href="#'.$tabid.'-'.$tabcnt.'">'.$title[$i].'</a></li>';
		}
		$ret .= '</ul>';

		for ($i=0; $i<count($box); $i++) {
			$tabcnt = $i+1;
		    $ret .= '<div id="'.$tabid.'-'.$tabcnt.'" class="tabbox">'. convert_html($box[$i]) . '</div>';
		}
		$ret .= '</div>';
	}

	$qt->appendv_once('plugin_tabbox'.$s_tab_confnum, 'beforescript', $head);

    return $ret;
}
?>