<?php
//---------------------------------------
// セールスレター型特有の設定
// lib/html.php に読み込まれることを想定
//

//-------------------------------------------------
//
// 外部リンクを別ウインドウで開くためのjavascriptの読み込み
//-------------------------------------------------
if (exist_plugin('external_link'))
{
	plugin_external_link_js($nowindow, $reg_exp_host);
}

//when admin call Bootstrap and jquery
if (($qt->getv('editable') || ss_admin_check()))
{
    $qt->setv('jquery_include', true);
    $qt->setv('bootstrap_script', '<script type="text/javascript" src="skin/bootstrap/js/bootstrap.min.js"></script>');
    $qt->setv('bootstrap_style', '<link rel="stylesheet" href="skin/bootstrap/css/bootstrap-custom.min.css" />');
}

// Javascript 読み込み
$include_js = '
<script src="js/qhm.js"></script>';
$qt->appendv_once('include_qhm_js', 'beforescript', $include_js);

//------------------------------------------------------
//
//  killerpage2 css settings
//
//------------------------------------------------------

//shadeborder
$sb_beforescript = '
<!--[if lte IE 8]>
<script type="text/javascript" src="skin/killerpage2/shadedborder.js"></script>
<![endif]-->';
$sb_lastscript = '
<!--[if lte IE 8]>
<script language="javascript" type="text/javascript">
$("#body img:last").on("load", function(){
	var el = document.getElementById("wrapper");
	var wpr_h = el.offsetHeight;
    var myBorder = RUZEE.ShadedBorder.create({ corner:10, shadow:24, mwheight:wpr_h });
    myBorder.render("wrapper");
});
</script>
<![endif]-->
<style type="text/css">
#wrapper {
	border-radius: 10px;
	box-shadow: 0 3px 5px 5px rgba(0,0,0,0.2);
}
</style>
';

// for iPhone & iPod Touch & android
if (is_smart_phone()) {
	if (exist_plugin('use_smart')) {
		$qt->appendv('enable_smart_style', do_plugin_convert('use_smart'));
	}

	//smart 表示
	if (plugin_use_smart_is_enable()) {
		$smart_css = '
<meta name="apple-mobile-web-app-capable" content="yes">
<style type="text/css">
#wrapper{
-webkit-border-radius: 20px;
-webkit-box-shadow: 4px 4px 10px #333;
}
</style>';
		$smart_lastscript .= '
<script type="text/javascript" charset="utf-8">
if (typeof window.onload == "undefined") {
	window.onload = function(){
		setTimeout(function(){window.scrollTo(0,1);},100);
	};
} else {
	var olfunc = window.onload;
	window.onload = function(){
		olfunc();
		setTimeout(function(){window.scrollTo(0,1);},100);
	};
}
</script>
';

		$qt->appendv('beforescript', $smart_css);
		$qt->appendv('lastscript', $smart_lastscript);

		//shadedborder を無効化する
		$sb_beforescript = $sb_lastscript = '';
	}
}
//読込順の関係で、killerpage_css へ追加する
$qt->appendv('killerpage_css', $sb_beforescript);
$qt->appendv('lastscript', $sb_lastscript);


$killer_fgcolor = rawurlencode($killer_page2['fg']);
$killer_bg = $killer_page2['bg'];
$killer_width = $killer_page2['width'];
$killer_padding = $killer_page2['padding'];
$killer_bg_body = $killer_page2['bg_body'];
$killer_fg_body = $killer_page2['fg_body'];
$killer_body_width = $killer_width - ($killer_padding * 2);

if( preg_match('/^bg_/',$killer_bg) ){
	$killer_page2['body_bg_img'] = 'image/'.$killer_bg.'.png';
	$killer_bg = '#ccc';
}

if( isset($killer_page2['body_bg_img']) ){
	$body_bg_setting = 'background-image:url('.$killer_page2['body_bg_img'].');';
}
else{
	$body_bg_setting = 'background-color:'.$killer_bg.';';
}

$killer_logo_img_css = '';
$qt->setv('killer_logo_img', '');
if( isset($killer_page2['img']) ){
	$killer_logo_img = '<img src="'.$killer_page2['img'].'" title="" alt="" />';
	$qt->setv('killer_logo_img', $killer_logo_img);
	$killer_logo_img_css = 'padding-top:0px;';
}


$killerpage_css = <<<EOD
<link rel="stylesheet" media="screen,print" href="skin/killerpage2/main.css.php?onefg=$killer_fgcolor" />
<link rel="stylesheet" media="screen,print" href="skin/killerpage2/default.css" />
<style type="text/css"><!--
body       { $body_bg_setting color:$killer_fg_body}
div#wrapper{ background-color:$killer_bg_body; width:{$killer_width}px; $killer_logo_img_css}
div#body   { background-color:$killer_bg_body; margin-left:{$killer_padding}px; margin-right:{$killer_padding}px; width:{$killer_body_width}px; }
.sb-inner { background-color:$killer_bg_body; }
div#preview {background-color:$killer_bg_body;border:1px #fcc dashed;}
--></style>
$custom_meta
EOD;
$qt->appendv('killerpage_css', $killerpage_css);


//settings of access analytics & section edit mode
$qt->setv('sec_edit_css', '');
if ($qt->getv('editable'))
{
	$qt->setv('sec_edit_css', '<style type="text/css">
<!--
a.anchor_super{ display:inline; text-decoration:none;}
a.anchor_super img{border:none;};
-->
</style>
');
}


//UniversalAnalytics Tracking Code
if ($code = $qt->getv('ga_universal_analytics'))
{
    $qt->appendv('beforescript', $code);
}

$qt->setv('body', $body);

$generator_tag = '<meta name="GENERATOR" content="Quick Homepage Maker; version='. QHM_VERSION. '; haik=false" />' . "\n";
$qt->prependv_once('generator_tag', 'beforescript', $generator_tag);
