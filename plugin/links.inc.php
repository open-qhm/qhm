<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: links.inc.php,v 1.23 2005/02/27 09:43:12 henoheno Exp $
//
// Update link cache plugin

function plugin_links_action()
{
	global $script, $post, $vars, $foot_explain;
	$qm = get_qm();

	if (PKWK_READONLY) die_message($qm->m['fmt_err_pkwk_readonly']);

	$msg = $body = '';
	if (empty($vars['action']) || empty($post['adminpass']) || ! pkwk_login($post['adminpass'])) {
		$msg   = & $qm->m['plg_links']['title_update'];
		$body  = convert_html($qm->m['plg_links']['usage']);
		$body .= <<<EOD
<form method="POST" action="$script">
 <div>
  <input type="hidden" name="plugin" value="links" />
  <input type="hidden" name="action" value="update" />
  <label for="_p_links_adminpass">{$qm->m['adminpass']}</label>
  <input type="password" name="adminpass" id="_p_links_adminpass" size="20" value="" />
  <input type="submit" value="{$qm->m['plg_links']['btn_submit']}" />
 </div>
</form>
EOD;

	} else if ($vars['action'] == 'update') {
		links_init();
		$foot_explain = array(); // Exhaust footnotes
		$msg  = & $qm->m['plg_links']['title_update'];
		$body = & $qm->m['plg_links']['done'];
	} else {
		$msg  = & $qm->m['plg_links']['title_update'];
		$body = & $qm->m['plg_links']['err_invalid' ];
	}
	return array('msg'=>$msg, 'body'=>$body);
}
?>
