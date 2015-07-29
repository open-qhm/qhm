<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: lightbox2.inc.php,v 1.22 2005/06/16 15:04:08 hokuken.com Exp $
//
// Text lightbox2 plugin

function plugin_greybox_inline()
{
	$qm = get_qm();
	
	$addscript = '
	<script type="text/javascript">
    	var GB_ROOT_DIR = "./plugin/greybox/";
	</script>
	<script type="text/javascript" src="./plugin/greybox/AJS.js"></script>
	<script type="text/javascript" src="./plugin/greybox/AJS_fx.js"></script>
	<script type="text/javascript" src="./plugin/greybox/gb_scripts.js"></script>
	<link href="./plugin/greybox/gb_styles.css" rel="stylesheet" type="text/css" />
';
	
	$qt = get_qt();
	$qt->appendv_once('plugin_greybox', 'beforescript', $addscript);

	$args = func_get_args();
	$text = strip_autolink(array_pop($args)); // Already htmlspecialchars(text)

	list($url, $title, $group) = array_pad($args, 3, '');
	if (($url == '' && $group == '') || $text == '' || func_num_args() > 4){
		return $qm->replace('fmt_err_iln', 'greybox', $qm->m['plg_greybox']['err_usage']);
	}
	$url = htmlspecialchars($url);
	$title = htmlspecialchars($title);
	$group = htmlspecialchars($group);
	
	$title = ($title == '') ? '' : ' title="' .$title. '"';

	//image grey box
	if(preg_match("/.*(jpg|jpeg|png|gif)$/i", $url)){
		
		$rel = ($group == '') ? ' rel="gb_image[]"' : ' rel="gb_imageset[' .$group. ']"';
	}
	else{ //web grey box
		$rel = ($group == '') ? ' rel="gb_page_fs[]"' : 'rel="gb_pageset[' . $group . ']"';	
	}
	
	
	$ret = '<a href="' .$url. '" ' .$title. $rel . '>' . $text . '</a>';
	return $ret;
	
}
?>
