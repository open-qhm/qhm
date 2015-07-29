<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: lookup.inc.php,v 1.22 2005/07/18 18:59:27 teanan Exp $
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// InterWiki lookup plugin

function plugin_lookup_convert()
{
	global $vars;
	static $id = 0;
	$qm = get_qm();

	$num = func_num_args();
	if ($num == 0 || $num > 3) return $qm->replace('fmt_err_cvt', 'lookup', $qm->m['plg_lookup']['err_usage']);

	$args = func_get_args();
	$interwiki = htmlspecialchars(trim($args[0]));
	$button    = isset($args[1]) ? trim($args[1]) : '';
	$button    = ($button != '') ? h($button) : $qm->m['plg_lookup']['btn_submit'];
	$default   = ($num > 2) ? h(trim($args[2])) : '';
	$s_page    = h($vars['page']);
	++$id;

	$script = get_script_uri();
	$ret = <<<EOD
<form action="$script" method="post">
 <div>
  <input type="hidden" name="plugin" value="lookup" />
  <input type="hidden" name="refer"  value="$s_page" />
  <input type="hidden" name="inter"  value="$interwiki" />
  <label for="_p_lookup_$id">$interwiki:</label>
  <input type="text" name="page" id="_p_lookup_$id" size="30" value="$default" />
  <input type="submit" value="$button" />
 </div>
</form>
EOD;
	return $ret;
}

function plugin_lookup_action()
{
	global $post; // Deny GET method to avlid GET loop
	$qm = get_qm();

	$page  = isset($post['page'])  ? $post['page']  : '';
	$inter = isset($post['inter']) ? $post['inter'] : '';
	if ($page == '') return FALSE; // Do nothing
	if ($inter == '') return array('msg'=>$qm->m['plg_lookup']['err_invalid_access'], 'body'=>'');

	$url = get_interwiki_url($inter, $page);
	if ($url === FALSE) {
		$msg = $qm->replace('fmt_err_iw_not_found', $inter);
		$msg = h($msg);
		return array('msg'=>$qm->m['plg_lookup']['title_not_found'], 'body'=>$msg);
	}

	pkwk_headers_sent();
	header('Location: ' . $url); // Publish as GET method
	exit;
}
?>
