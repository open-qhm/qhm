<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: interwiki.inc.php,v 1.10 2004/12/04 14:48:32 henoheno Exp $
//
// InterWiki redirection plugin (OBSOLETE)

function plugin_interwiki_action()
{
	global $vars, $InterWikiName;
	$qm = get_qm();

	if (PKWK_SAFE_MODE) die_message($qm->m['plg_interwiki']['err_not_allowed']);

	$match = array();
	if (! preg_match("/^$InterWikiName$/", $vars['page'], $match))
		return plugin_interwiki_invalid();

	$url = get_interwiki_url($match[2], $match[3]);
	if ($url === FALSE) return plugin_interwiki_invalid();

	pkwk_headers_sent();
	header('Location: ' . $url);
	exit;
}

function plugin_interwiki_invalid()
{
	$qm = get_qm();
	return array(
		'msg'  => $qm->m['fmt_title_invalidiwn'],
		'body' => $qm->replace('fmt_msg_invalidiwn', '', make_pagelink('InterWikiName'))
	);
}
?>
