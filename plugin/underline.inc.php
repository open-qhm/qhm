<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: size.inc.php,v 1.10 2005/06/16 15:04:08 henoheno Exp $
//
// Text-size changing via CSS plugin

// ----
define('PLUGIN_UNDERLINE_USAGE', '&underline(text);');

function plugin_underline_inline()
{

	list($text) = func_get_args();

	return '<span style="text-decoration:underline">' . $text . '</span>';
}
?>
