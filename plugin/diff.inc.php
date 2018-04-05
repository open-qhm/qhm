<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: diff.inc.php,v 1.18 2005/12/10 12:48:02 henoheno Exp $
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2002      Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// Showing colored-diff plugin

function plugin_diff_action()
{
	global $vars, $script;
	global $layout_pages, $style_name;

	$editable = edit_auth($page, FALSE, FALSE);
	if(!$editable){
		header("Location: $script");
		exit();
	}

	$page = isset($vars['page']) ? $vars['page'] : '';
	check_readable($page, true, true);

	//レイアウト部品の場合、スタイルを変更する
	$is_layout = FALSE;
	if (isset($layout_pages) && isset($layout_pages[$page]))
	{
		$style_name = '..';
		$is_layout = TRUE;
	}

	// スタイルシートを出力
	plugin_diff_set_css();

	$action = isset($vars['action']) ? $vars['action'] : '';
	switch ($action) {
		case 'delete': $retval = plugin_diff_delete($page);	break;
		default:       $retval = plugin_diff_view($page);	break;
	}
	return $retval;
}

function plugin_diff_view($page)
{
	global $script, $hr;
	global $layout_pages;
	$qm = get_qm();

	$r_page = rawurlencode($page);
	$s_page = htmlspecialchars($page);

	$menu = array(
		'<li>' . $qm->m['fmt_msg_addline'] . '</li>',
		'<li>' . $qm->m['fmt_msg_delline'] . '</li>'
	);

	//レイアウト部品の場合、スタイルを変更する
	$is_layout = FALSE;
	if (isset($layout_pages) && isset($layout_pages[$page]))
	{
		$is_layout = TRUE;
	}

	$is_page = is_page($page);
	if ($is_page && $is_layout)
	{
		$menu[] = ' <li><a href="'. h($script) .'?cmd=edit&amp;page='. $r_page .'">'. h($layout_pages[$page]) .'を編集する</a></li>';
	}
	else if ($is_page) {
		$menu[] = ' <li>' . str_replace('$1', '<a href="' . $script . '?' . $r_page . '">' .
			$s_page . '</a>', $qm->m['fmt_msg_goto']) . '</li>';
	} else {
		$menu[] = ' <li>' . str_replace('$1', $s_page, $qm->m['fmt_msg_deleted']) . '</li>';
	}

	$filename = DIFF_DIR . encode($page) . '.txt';
	if (file_exists($filename)) {
		if (! PKWK_READONLY) {
			$menu[] = '<li><a href="' . $script . '?cmd=diff&amp;action=delete&amp;page=' .
				$r_page . '">' . $qm->replace('plg_diff.title_delete', $s_page). '</a></li>';
		}
		$msg = '<pre>' . diff_style_to_css(htmlspecialchars(join('', file($filename)))) . '</pre>' . "\n";
	} else if ($is_page) {
		$diffdata = trim(htmlspecialchars(join('', get_source($page))));
		$msg = '<pre><span class="diff_added">' . $diffdata . '</span></pre>' . "\n";
	} else {
		return array('msg'=>$qm->m['plg_diff']['title'], 'body'=>$qm->m['fmt_err_notfound']);
	}

	$menu = join("\n", $menu);
	$body = <<<EOD
<ul>
$menu
</ul>
$hr
EOD;

	$title = $qm->m['plg_diff']['title'];
	if ($is_layout)
	{
		$title = h($layout_pages[$page]) . 'の変更点';
	}

	return array('msg'=>$title, 'body'=>$body . $msg);
}

function plugin_diff_delete($page)
{
	global $script, $vars;
	$qm = get_qm();

	$filename = DIFF_DIR . encode($page) . '.txt';
	$body = '';
	if (! is_pagename($page))     $body = 'Invalid page name';
	if (! file_exists($filename)) $body = make_pagelink($page) . '\'s diff seems not found';
	if ($body) return array('msg'=>$qm->m['plg_diff']['title_delete'], 'body'=>$body);

	if (isset($vars['pass'])) {
		if (pkwk_login($vars['pass'])) {
			unlink($filename);
			return array(
				'msg'  => $qm->m['plg_diff']['title_delete'],
				'body' => str_replace('$1', make_pagelink($page), $qm->m['plg_diff']['deleted'])
			);
		} else {
			$body .= '<p><strong>' . $qm->m['fmt_msg_invalidpass'] . '</strong></p>' . "\n";
		}
	}

	$s_page = htmlspecialchars($page);
	$body .= <<<EOD
<p>{$qm->m['plg_diff']['err_adminpass']}</p>
<form action="$script" method="post">
 <div>
  <input type="hidden"   name="cmd"    value="diff" />
  <input type="hidden"   name="page"   value="$s_page" />
  <input type="hidden"   name="action" value="delete" />
  <input type="password" name="pass"   size="12" />
  <input type="submit"   name="ok"     value="{$qm->m['fmt_btn_delete']}" />
 </div>
</form>
EOD;

	return array('msg'=>$qm->m['plg_diff']['title_delete'], 'body'=>$body);
}

function plugin_diff_set_css() {
	$qt = get_qt();
	$style_tag = <<< HTML
<style>
span.diff_added {
  color: #337ab7;
}

span.diff_removed {
  color: #a94442;
}
</style>
HTML;
	$qt->appendv_once('plugin_diff_css', 'beforescript', $style_tag);
}
