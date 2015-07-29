<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: lightbox2.inc.php,v 1.22 2005/06/16 15:04:08 hokuken.com Exp $
//
// Text lightbox2 plugin

define('LIGHTBOX2_LIB','./plugin/lightbox2/');

function plugin_lightbox2_inline()
{
	global $vars;
	$qm = get_qm();
	
	$qt = get_qt();
	$qt->setv('jquery_include', true);
		
	$addscript = '
<script type="text/javascript" src="js/jquery.dimensions.min.js"></script>
<script type="text/javascript" src="js/jquery.dropshadow.js"></script>
<script type="text/javascript" src="'.LIGHTBOX2_LIB.'/js/jquery.lightbox.js"></script>
<link href="'.LIGHTBOX2_LIB.'/css/lightbox.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
$(document).ready(function(){
	$(".lightbox").lightbox();
});
</script>
';
	$qt->appendv_once('plugin_lightbox2', 'beforescript', $addscript);

	$args = func_get_args();
	$text = strip_autolink(array_pop($args)); // Already htmlspecialchars(text)

	list($img, $title, $group, $bg_color) = array_pad($args, 4, '');
	if (($img == '' && $group == '') || $text == '' || func_num_args() > 5){
		return $qm->replace('fmt_err_iln', 'lightbox2', $qm->m['plg_lightbox2']['err_usage']);
	}

	$img = htmlspecialchars($img);
	$title = htmlspecialchars($title);
	$group = htmlspecialchars($group);

	if($bg_color!=''){
		$addstyle = '<style type="text/css">div#overlay{background-color:'.$bg_color.';}</style>';
		$qt->appendv_once('plugin_lightbox2_bgcolor', 'beforescript', $addstyle);
	}
	
	if($group == ''){
		$group = "";
	}
	else{
		$group = ' rel="'.$group.'"';
	}

	$ret = '<a href="' .$img. '" title="' .$title. '" class="lightbox" ' .$group. '>' .$text. '</a>';
	

	return $ret;
	
}
?>
