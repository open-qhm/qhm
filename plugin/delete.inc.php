<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: edit.inc.php,v 1.40 2006/03/21 14:26:25 henoheno Exp $
// Copyright (C) 2001-2006 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version
//
// Edit plugin (cmd=delete)

function plugin_delete_action()
{
	global $vars, $script, $load_template_func, $style_name, $layout_pages;
	global $defaultpage;
	$qm = get_qm();
	$qt = get_qt();

	if (PKWK_READONLY) die_message($qm->m['fmt_err_pkwk_readonly']);

	$page = isset($vars['page']) ? $vars['page'] : $defaultpage;
	$s_page = h($page);
	$digest = md5(join('', get_source($page)));

	check_editable($page, true, true);

	if ($page === $defaultpage)
	{
	$body = <<<EOD
<p>
{$page}は削除できません。
</p>
EOD;
		return array('msg'=>'$1の削除', 'body'=> $body);
	}

	$body = <<<EOD
<p>
{$page}を削除しますか？
</p>
<form method="POST" action="{$script}">
 <div>
  <p><input type="hidden" name="plugin" value="delete" />
  <input type="hidden" name="write" value="1" />
  <input type="hidden" name="page" value="{$s_page}" />
  <input type="hidden" name="digest" value="{$digest}" />
  <input type="submit" value="実行する" />
  </p>
 </div>
</form>
EOD;

	$body .= '<hr />'.convert_html(get_source($page));
	
	
	
	if (isset($vars['write']))
	{
		plugin_delete_write();
		redirect($defaultpage, $page.'を削除しました');
	}
	
	return array('msg'=>'$1の削除', 'body'=> $body);
}

// Write, add, or insert new comment
function plugin_delete_write()
{
	global $vars, $trackback, $layout_pages;
	global $notimeupdate, $do_update_diff_table;
	$qm = get_qm();

	$page   = isset($vars['page'])   ? $vars['page']   : '';
	$digest = isset($vars['digest']) ? $vars['digest'] : '';

	// Collision Detection
	$oldpagesrc = join('', get_source($page));
	$oldpagemd5 = md5($oldpagesrc);
	if ($digest == $oldpagemd5) {
		$retvars = array();
		page_write($page, '');
		$retvars['msg' ] = $qm->m['fmt_title_deleted'];
		$retvars['body'] = str_replace('$1', htmlspecialchars($page), $qm->m['fmt_title_deleted']);
	
		if ($trackback) tb_delete($page);
	}
	else
	{
		$retvars['msg' ] = '$1 を削除できません';
		$retvars['body'] = $page.'を削除できませんでした。';
	}

	return $retvars;
}

?>