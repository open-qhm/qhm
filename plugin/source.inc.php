<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: source.inc.php,v 1.14 2005/01/29 02:07:58 henoheno Exp $
//
// Source plugin

// Output source text of the page
function plugin_source_action()
{
	global $vars, $script;

	if (PKWK_SAFE_MODE) die_message('PKWK_SAFE_MODE prohibits this');

	$page = isset($vars['page']) ? $vars['page'] : '';
	$vars['refer'] = $page;

	$editable = ss_admin_check();
    if(!$editable){
        header("Location: $script");
        exit();        
    }
    
    $qm = get_qm();

	if (! is_page($page) || ! check_readable($page, false, false))
		return array('msg' => $qm->m['plg_source']['title_notfound'],
			'body' => $qm->m['plg_source']['err_notfound']);

	return array('msg' => $qm->m['plg_source']['title'],
		'body' => '<pre id="source">' .
		htmlspecialchars(join('', get_source($page))) . '</pre>');
}
?>
