<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: backup.inc.php,v 1.27 2005/12/10 12:48:02 henoheno Exp $
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// Backup plugin

// Prohibit rendering old wiki texts (suppresses load, transfer rate, and security risk)
define('PLUGIN_BACKUP_DISABLE_BACKUP_RENDERING', PKWK_SAFE_MODE || PKWK_OPTIMISE);

function plugin_backup_action()
{
	global $vars, $do_backup, $hr, $script;
	global $layout_pages, $style_name;
	$qm = get_qm();

	$editable = edit_auth($page, FALSE, FALSE);
	if (!$editable) {
		header("Location: $script");
		exit();
	}

	if (!$do_backup) return;

	$page = isset($vars['page']) ? $vars['page']  : '';
	if ($page == '') return array('msg'=>$qm->m['plg_backup']['title_backuplist'], 'body'=>plugin_backup_get_list_all());

	//レイアウト部品の場合、スタイルを変更する
	$is_layout = FALSE;
	if (isset($layout_pages) && isset($layout_pages[$page]))
	{
		$style_name = '..';
		$is_layout = TRUE;
	}

	check_readable($page, true, true);
	$s_page = htmlspecialchars($page);
	$r_page = rawurlencode($page);

	$action = isset($vars['action']) ? $vars['action'] : '';
	if ($action == 'delete') return plugin_backup_delete($page);

	$s_action = $r_action = '';
	if ($action != '') {
		$s_action = htmlspecialchars($action);
		$r_action = rawurlencode($action);
	}

	$s_age  = (isset($vars['age']) && is_numeric($vars['age'])) ? $vars['age'] : 0;
	if ($s_age <= 0)
	{
		$title = $is_layout ? (h($layout_pages[$page]).'のバックアップ一覧') : $qm->m['plg_backup']['title_pagebackuplist'];
		return array( 'msg'=> $title, 'body'=>plugin_backup_get_list($page));
	}


	$script = get_script_uri();

	$body  = '<ul>' . "\n";
	if (!$is_layout)
	{
		$body .= ' <li><a href="' . $script . '?cmd=backup">' . $qm->m['plg_backup']['backuplist'] . '</a></li>' ."\n";
	}

	$href    = $script . '?cmd=backup&amp;page=' . $r_page . '&amp;age=' . $s_age;
	$is_page = is_page($page);

	if ($is_page && $action != 'diff') {
		exist_plugin('diff') && plugin_diff_set_css();
		$body .= ' <li>' . str_replace('$1', '<a href="' . $href .
			'&amp;action=diff">' . $qm->m['plg_backup']['diff'] . '</a>',
			$qm->m['plg_backup']['view']) . '</li>' . "\n";
	}

	if ($is_page && $action != 'nowdiff') {
		exist_plugin('diff') && plugin_diff_set_css();
		$body .= ' <li>' . str_replace('$1', '<a href="' . $href .
			'&amp;action=nowdiff">' . $qm->m['plg_backup']['nowdiff'] . '</a>',
			$qm->m['plg_backup']['view']) . '</li>' . "\n";
	}

	if ($action != 'source')
		$body .= ' <li>' . str_replace('$1', '<a href="' . $href .
			'&amp;action=source">' . $qm->m['plg_backup']['source'] . '</a>',
			$qm->m['plg_backup']['view']) . '</li>' . "\n";

	if (!PLUGIN_BACKUP_DISABLE_BACKUP_RENDERING && $action)
		$body .= ' <li>' . str_replace('$1', '<a href="' . $href .
			'">' . $qm->m['plg_backup']['backup'] . '</a>',
			$qm->m['plg_backup']['view']) . '</li>' . "\n";

	if ($is_page && $is_layout)
	{
		$body .= ' <li><a href="' . $script . '?cmd=edit&amp;page=' . $r_page . '">' . h($layout_pages[$page]) . 'を編集する</a>';
	}
	else if ($is_page) {
		$body .= ' <li>' . str_replace('$1',
			'<a href="' . $script . '?' . $r_page . '">' . $s_page . '</a>',
			$qm->m['fmt_msg_goto']) . "\n";
	} else {
		$body .= ' <li>' . str_replace('$1', $s_page, $qm->m['plg_backup']['deleted']) . "\n";
	}

	$backups = get_backup($page);
	$backups_count = count($backups);
	if ($s_age > $backups_count) $s_age = $backups_count;

	if ($backups_count > 0) {
		$body .= '  <ul>' . "\n";
		foreach($backups as $age => $val) {
			$date = format_date($val['time'], TRUE);
			$body .= ($age == $s_age) ?
				'   <li><em>' . $age . ' ' . $date . '</em></li>' . "\n" :
				'   <li><a href="' . $script . '?cmd=backup&amp;action=' .
				$r_action . '&amp;page=' . $r_page . '&amp;age=' . $age .
				'">' . $age . ' ' . $date . '</a></li>' . "\n";
		}
		$body .= '  </ul>' . "\n";
	}
	$body .= ' </li>' . "\n";
	$body .= '</ul>'  . "\n";

	if ($action == 'diff') {
		$title = $is_layout ? h($layout_pages[$page]) .' のバックアップ差分(No.$2)' : $qm->m['plg_backup']['title_backupdiff'];
		$old = ($s_age > 1) ? join('', $backups[$s_age - 1]['data']) : '';
		$cur = join('', $backups[$s_age]['data']);
		$body .= plugin_backup_diff(do_diff($old, $cur));
	} else if ($s_action == 'nowdiff') {
		$title = $is_layout ? h($layout_pages[$page]).' のバックアップの現在との差分(No.$2)' : $qm->m['plg_backup']['title_backupnowdiff'];
		$old = join('', $backups[$s_age]['data']);
		$cur = join('', get_source($page));
		$body .= plugin_backup_diff(do_diff($old, $cur));
	} else if ($s_action == 'source') {
		$title = $is_layout ? h($layout_pages[$page]) .' のバックアップソース(No.$2)' : $qm->m['plg_backup']['title_backupsource'];
		$body .= '<pre>' . htmlspecialchars(join('', $backups[$s_age]['data'])) .
			'</pre>' . "\n";
	} else {
		if (PLUGIN_BACKUP_DISABLE_BACKUP_RENDERING) {
			die_message($qm->m['fmt_err_prohibited']);
		} else {
			$title = $is_layout ? h($layout_pages[$page]).' のバックアップ(No.$2)' :  $qm->m['plg_backup']['title_backup'];
			$body .= $hr . "\n" .
				drop_submit(convert_html($backups[$s_age]['data']));
		}
	}

	return array('msg'=>str_replace('$2', $s_age, $title), 'body'=>$body);
}

// Delete backup
function plugin_backup_delete($page)
{
	global $vars, $layout_pages;

	$is_layout = FALSE;
	if (isset($layout_pages) && isset($layout_pages[$page]))
	{
		$is_layout = TRUE;
	}

	$qm = get_qm();

	if (!_backup_file_exists($page))
		return array('msg'=>$qm->m['plg_backup']['title_pagebackuplist'], 'body'=>plugin_backup_get_list($page)); // Say "is not found"

	$body = '';
	if (isset($vars['pass'])) {
		if (pkwk_login($vars['pass'])) {
			_backup_delete($page);
			$pagelink = $is_layout ? h($layout_pages[$page]) : make_pagelink($page);
			$addlink = $is_layout ? "\n" . '<p><a href="'. h($script).'?cmd=edit&amp;page='. rawurlencode($page) .'">戻る</a></p>' : '';
			return array(
				'msg'  => $is_layout ? h($layout_pages[$page]). ' のバックアップを削除' : $qm->m['plg_backup']['title_backup_delete'],
				'body' => str_replace('$1', $pagelink, $qm->m['plg_backup']['backup_deleted']) . $addlink
			);
		} else {
			$body = '<p><strong>' . $qm->m['fmt_err_invalidpass'] . '</strong></p>' . "\n";
		}
	}

	$script = get_script_uri();
	$s_page = htmlspecialchars($page);
	$body .= <<<EOD
<p>{$qm->m['plg_backup']['backup_adminpass']}</p>
<form action="$script" method="post">
 <div>
  <input type="hidden"   name="cmd"    value="backup" />
  <input type="hidden"   name="page"   value="$s_page" />
  <input type="hidden"   name="action" value="delete" />
  <input type="password" name="pass"   size="12" />
  <input type="submit"   name="ok"     value="{$qm->m['fmt_btn_delete']}" />
 </div>
</form>
EOD;

	$title = $is_layout ? h($layout_pages[$page]). ' のバックアップを削除' : $qm->m['plg_backup']['title_backup_delete'];
	return	array('msg'=>$title, 'body'=>$body);
}

function plugin_backup_diff($str)
{
	global $hr;
	$qm = get_qm();
	$ul = <<<EOD
$hr
<ul>
 <li>{$qm->m['fmt_msg_addline']}</li>
 <li>{$qm->m['fmt_msg_delline']}</li>
</ul>
EOD;

	return $ul . '<pre>' . diff_style_to_css(htmlspecialchars($str)) . '</pre>' . "\n";
}

function plugin_backup_get_list($page)
{
	global $layout_pages;

	//レイアウト部品の場合、スタイルを変更する
	$is_layout = FALSE;
	if (isset($layout_pages) && isset($layout_pages[$page]))
	{
		$is_layout = TRUE;
	}

	$qm = get_qm();

	$script = get_script_uri();
	$r_page = rawurlencode($page);
	$s_page = htmlspecialchars($page);

	//バックアップ一覧へのリンクは、
	//レイアウト部品の場合、編集リンクを表示する

	$backuplist_link = $is_layout ?
		('<a href="'.h($script).'?cmd=edit&amp;page='.$r_page.'">' .h($layout_pages[$page]). 'を編集する</a>') :
		('<a href="'.h($script).'?cmd=backup">'.$qm->m['plg_backup']['backuplist'].'</a>');

	$retval = array();
	$retval[0] = '
<ul>
  <li>
	' .$backuplist_link. '
	<ul>
';
	$retval[1] = "\n";
	$retval[2] = <<<EOD
	</ul>
  </li>
</ul>
EOD;

	$backups = _backup_file_exists($page) ? get_backup($page) : array();
	if (empty($backups)) {
		$pagelink = $is_layout ? h($layout_pages[$page]) : make_pagelink($page);
		$msg = str_replace('$1', $pagelink, $qm->m['plg_backup']['nobackup']);
		$retval[1] .= '   <li>' . $msg . '</li>' . "\n";
		return join('', $retval);
	}

	if (!PKWK_READONLY) {
		$retval[1] .= '   <li><a href="' . $script . '?cmd=backup&amp;action=delete&amp;page=' .
			$r_page . '">';
		$retval[1] .= str_replace('$1', $is_layout ? h($layout_pages[$page]) : $s_page, $qm->m['plg_backup']['title_backup_delete']);
		$retval[1] .= '</a></li>' . "\n";
	}

	$href = $script . '?cmd=backup&amp;page=' . $r_page . '&amp;age=';
	$_anchor_from = $_anchor_to   = '';
	foreach ($backups as $age=>$data) {
		if (!PLUGIN_BACKUP_DISABLE_BACKUP_RENDERING) {
			$_anchor_from = '<a href="' . $href . $age . '">';
			$_anchor_to   = '</a>';
		}
		$date = format_date($data['time'], TRUE);
		$retval[1] .= <<<EOD
   <li>$_anchor_from$age $date$_anchor_to
	 [ <a href="$href$age&amp;action=diff">{$qm->m['plg_backup']['diff']}</a>
	 | <a href="$href$age&amp;action=nowdiff">{$qm->m['plg_backup']['nowdiff']}</a>
	 | <a href="$href$age&amp;action=source">{$qm->m['plg_backup']['source']}</a>
	 ]
   </li>
EOD;
	}

	return join('', $retval);
}

// List for all pages
function plugin_backup_get_list_all($withfilename = FALSE)
{
	global $cantedit;

	$pages = array_diff(get_existpages(BACKUP_DIR, BACKUP_EXT), $cantedit);

	if (empty($pages)) {
		return '';
	} else {
		return page_list($pages, 'backup', $withfilename);
	}
}
?>
