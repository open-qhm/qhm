<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: addyahoo.inc.php,v 1.4 2007/10/12 19:26:53 henoheno Exp $
//
// add to myYahoo plugin
// ----

function plugin_addyahoo_inline()
{
	global $script;
	$qm = get_qm();

	return '<a target=_blank
href="http://add.my.yahoo.co.jp/rss?url='.$script.'?cmd=rss&ver=1.0"><img
src="http://img.yahoo.co.jp/i/jp/my/addtomy1.gif" width="91"
height="17" border="0" align=middle alt="'. $qm->m['plg_addyahoo']['title']. '"></a>';

}

?>