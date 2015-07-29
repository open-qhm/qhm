<?php
// $Id: nowindow.inc.php,v 1.1 2007/10/06 00:06:30 hokuken Exp $
// License: The same as PukiWiki
//
// Nowindow plugin

// disable including external_link.js
function plugin_nowindow_convert()
{
	global $nowindow;

	$nowindow = 1;

	return '';
}
?>
