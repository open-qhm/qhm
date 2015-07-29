<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: addyahoo.inc.php,v 1.4 2007/10/12 19:26:53 henoheno Exp $
//
// add to myYahoo plugin
// ----

function plugin_addfavorite_inline()
{
	global $script;
	$qm = get_qm();
	
	$args = func_get_args();
	$text = trim(strip_autolink(array_pop($args))); // Already htmlspecialchars(text)

	list($sitename, $linktype) = array_pad($args, 2, '');
	
	if ($sitename == '') {
		return $qm->m['plg_addfavorite']['err_no_sitename'];
	}

	if ($text == '') {
		$text_ie = $qm->m['plg_addfavorite']['ie'];
		$text_fx = $qm->m['plg_addfavorite']['firefox'];
		$text_op = $qm->m['plg_addfavorite']['opera'];
	}
	else {
		$text_ie = $text_fx = $text_op = $text;
	}

	switch ($linktype) {
		case "button":
$src = <<< EOD
<script type="text/javascript">
<!--
if(navigator.userAgent.indexOf("MSIE") > -1){ //Internet Explorer
document.write('<!-'+'-[if IE]>');
document.write('<input type="button" value="{$text_ie}"');
document.write(' onclick="window.external.AddFavorite(\'{$script}\',\'{$sitename}\')">');
document.write('<![endif]-'+'->');
}
else if(navigator.userAgent.indexOf("Firefox") > -1){ //Firefox
document.write('<input type="button" value="{$text_fx}"');
document.write(' onclick="window.sidebar.addPanel(\'{$sitename}\',\'{$script}\',\'\');">');
}
else if(navigator.userAgent.indexOf("Opera") > -1){ //Opera
document.write('<a href="{$script}" rel="sidebar" title="{$sitename}">{$text_op}</a>');
}
else {
void(0);
}
//-->
</script>
EOD;
			break;
		default:
$src = <<< EOD
<script type="text/javascript">
<!--
if(navigator.userAgent.indexOf("MSIE") > -1){ //Internet Explorer
document.write('<!-'+'-[if IE]>');
document.write('<a href="#" onclick="javascript:window.external.addFavorite(\'{$script}\',\'{$sitename}\')">{$text_ie}</a>');
document.write('<![endif]-'+'->');
}
else if(navigator.userAgent.indexOf("Firefox") > -1){ //Firefox
document.write('<a href="#" onclick="javascript:window.sidebar.addPanel(\'{$sitename}\',\'{$script}\',\'\');">{$text_fx}</a>');
}
else if(navigator.userAgent.indexOf("Opera") > -1){ //Opera
document.write('<a href="#" onclick="{$script}" rel="sidebar" title="{$sitename}">{$text_op}</a>');
}
else {
void(0);
}
//-->
</script>
EOD;
	}

	return $src;

}
?>