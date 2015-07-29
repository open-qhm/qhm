<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: addgoogle.inc.php,v 1.4 2007/10/12 19:28:53 henoheno Exp $
//
// add to iGoogle plugin

// ----

function plugin_addgoogle_inline()
{
	global $script;
	$qm = get_qm();
	
	return '<a href="http://fusion.google.com/add?feedurl='.$script.'%3Fcmd%3Drss%26ver%3D1.0"><img
src="http://buttons.googlesyndication.com/fusion/add.gif" width="104"
height="17" border="0" alt="'. $qm->m['plg_addgoogle']['title']. '"></a>';
}
?>