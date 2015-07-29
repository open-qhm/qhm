<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: add.inc.php,v 1.7 2005/05/02 02:46:23 henoheno Exp $
//
// Add plugin - Append new text below/above existing page
// Usage: cmd=add&page=pagename

function plugin_add_action()
{
	global $get, $post, $vars;
	$qm = get_qm();

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	$page = isset($vars['page']) ? $vars['page'] : '';
	check_editable($page);

	$get['add'] = $post['add'] = $vars['add'] = TRUE;
	return array(
		'msg'  => $qm->m['plg_add']['title'],
		'body' =>
			'<ul>' . "\n" .
			' <li>' . $qm->m['plg_add']['note'] . '</li>' . "\n" .
			'</ul>' . "\n" .
			edit_form($page, '')
		);
}
?>
