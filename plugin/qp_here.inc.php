<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: qp_here.inc.php,v 1.1 2011/02/09 11:35:00 hokuken Inc. Exp $
//
// see insert_mark.inc.php
// 

function plugin_qp_here_convert()
{

	if (exist_plugin('insert_mark')) {
		return do_plugin_convert('insert_mark');
	}
	
}
?>
