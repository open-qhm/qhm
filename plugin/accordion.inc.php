<?php
// $Id$

define('PLUGIN_ACCORDION_DEF_STYLE', 'background:transparent url("image/accordion_title_bg.png") repeat-x 0 0;background-size: 1px 100%;line-height:30px;color:#666;');
define('PLUGIN_ACCORDION_DEF_HOVER', 'background:transparent url("image/accordion_title_hoverbg.png") repeat-x 0 0;background-size: 1px 100%;text-decoration:none;color:#fff;');
define('PLUGIN_ACCORDION_DEF_BOX', 'padding:5px;');
define('PLUGIN_ACCORDION_DEF_CLOSE', 'image/accordion_close.png');
define('PLUGIN_ACCORDION_DEF_OPEN', 'image/accordion_open.png');

function plugin_accordion_convert()
{
	static $s_acc_cnt = 0;
	static $s_acc_confnum = 0;
	static $s_acc_hdr_cnt = 0;

	//jquery ライブラリの読み込み
	$qt = get_qt();
	$qt->setv('jquery_include', true);
	
    $args = func_get_args();
    $last = func_num_args() - 1;

	$body = '';
	$ret = $head = '';
	$options = array(
		'style' => PLUGIN_ACCORDION_DEF_STYLE,
		'hover' => PLUGIN_ACCORDION_DEF_HOVER,
		'box'   => PLUGIN_ACCORDION_DEF_BOX,
		'close' => PLUGIN_ACCORDION_DEF_CLOSE,
		'open'  => PLUGIN_ACCORDION_DEF_OPEN
	);

	$config = false;
	foreach ($args as $arg) {
		if (strpos($arg, 'conf', 0) !== FALSE) {
			$config = true;
		}
	}

	if ($config) {
		if ($last > 0) {
			$body = array_pop($args);
		}
		$options['conf'] = 'conf';
		$s_acc_confnum++;
	}
	else {
	    if ($s_acc_cnt == 0) {
			$s_acc_confnum++;
	    }

		$body = array_pop($args);
	    foreach ($args as $arg) {
	        list($key, $val) = explode('=', $arg, 2);
	        $options[$key] = htmlspecialchars($val);
	    }
	    $options['style'] = isset($options['style']) ? $options['style'] : PLUGIN_ACCORDION_DEF_STYLE;
	    $options['hover'] = isset($options['hover']) ? $options['hover'] : PLUGIN_ACCORDION_DEF_HOVER;
	    $options['box']   = isset($options['box'])   ? $options['box']   : PLUGIN_ACCORDION_DEF_BOX;
	    $options['close'] = isset($options['close']) ? $options['close'] : PLUGIN_ACCORDION_DEF_CLOSE;
	    $options['open']  = isset($options['open'])  ? $options['open']  : PLUGIN_ACCORDION_DEF_OPEN;
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
	    $options['style'] = isset($options['style']) ? $options['style'] : PLUGIN_ACCORDION_DEF_STYLE;
	    $options['hover'] = isset($options['hover']) ? $options['hover'] : PLUGIN_ACCORDION_DEF_HOVER;
	    $options['box']   = isset($options['box'])   ? $options['box']   : PLUGIN_ACCORDION_DEF_BOX;
	    $options['close'] = isset($options['close']) ? $options['close'] : PLUGIN_ACCORDION_DEF_CLOSE;
	    $options['open'] = isset($options['open'])   ? $options['open']  : PLUGIN_ACCORDION_DEF_OPEN;
   	}
   	else {
        $lines = explode("\n", $body);
        $buff = "";
        $box = $title = $link = $hdr_id = array();
	    foreach ($lines as $l) {
			if (preg_match("/^\*\s?(.*)$/",$l,$matches)) {
				$s_acc_hdr_cnt++;
				$hdr_id[] = $s_acc_hdr_cnt;
				if (count($title) > 0) {
					$box[] = $buff;
				}
				$tmp = substr(convert_html($matches[1]), 3, -5);
				if (preg_match("/^<a href=\"([^\"]*)\".*?>(.*?)<\/a>/",$tmp, $matches2)) {
					$link[] = $matches2[1];
					$tmp = $matches2[2];
				}
				else {
					$link[] = '#';
				}
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

	if (preg_match('/\.(?:jpg|jpeg|png|gif)$/i', $options['open'])) {
		$options['open'] = '<img src="'.$options['open'].'" />';
	}
	if (preg_match('/\.(?:jpg|jpeg|png|gif)$/i', $options['close'])) {
		$options['close'] = '<img src="'.$options['close'].'" />';
	}

    // はじめての定義の場合、javascriptを出力
	if ($s_acc_cnt == 0) {
		$head = '
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("ul.accordion > li > a.acctitle")
		.click(
			function() {
				if (!$(this).attr("href").match(/^#/)) {
					location.href = $(this).attr("href");
					return false;
				}
				$(this).parent().parent().siblings("ul.accordion").find("a.acctitle").removeClass("focustitle");
				$(">ul:not(:animated)",$(this).parent()).toggle();

				if ($(this).next("ul.subbox").is(":visible")) {
					$(this).addClass("focustitle");
				}
				else {
					$(this).removeClass("focustitle");
				}
				return false;
			}
		);
});
//-->
</script>
';
	}

	$s_acc_cnt++;
	$dclass = "dropn$s_acc_confnum";
	if (isset($options['conf']) || $s_acc_cnt == 1) {
		$head .= '
<style type="text/css">
ul.accordion{
list-style:none;
margin:0 !important;
padding:0;
}
ul.'.$dclass.' li a.acctitle{
display:block;
'.$options['style'].'
}
ul.'.$dclass.' li a.acctitle:hover,
ul.'.$dclass.' li a.focustitle {
'.$options['hover'].'
}
ul.subbox{
list-style:none;
margin:0 0!important;
display:none;
padding:0;
}
ul.'.$dclass.' div.accbox{
'.$options['box'].'
}
span.accexpand{
margin:auto 5px;
}
</style>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$("ul.'.$dclass.' > li > a.acctitle").each(function(){
		if (!$(this).children("span.accexpand").is(":visible")) {
			$(this).prepend(\'<span class="accexpand">'.$options['close'].'</span>\');
		}
	});
	$("ul.'.$dclass.' > li > a.acctitle").click(
			function() {
				if ($(this).next("ul.subbox").is(":visible")) {
					$("span.accexpand",this).html(\''.$options['open'].'\');
				}
				else {
					$("span.accexpand",this).html(\''.$options['close'].'\');
				}
				return false;
	});
});
//-->
</script>
';
	}
	if (!isset($options['conf'])) {
		for ($i=0; $i<count($box); $i++) {
			$url = $link[$i] == '#'? '#accbox_'. $hdr_id[$i]: $link[$i];
			$ul_id = ' id="accbox_'. $hdr_id[$i]. '"';
		    $ret .= '<ul class="accordion '.$dclass.'"><li><a class="acctitle" href="'.$url.'">'.$title[$i].'</a>';
		    $ret .= '<ul class="subbox"'. $ul_id .'><li><div class="accbox">'. convert_html($box[$i]) . '</div></li></ul>';
		    $ret .= '</li></ul>';
		}
	}
	$qt->appendv_once('plugin_accordion'.$s_acc_confnum, 'beforescript', $head);

    return $ret;
}
?>