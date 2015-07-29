<?php
/**
 *   QBlog List Recent Comments Plugin
 *   -------------------------------------------
 *   ./plugin/qblog_recent_comments.inc.php
 *   
 *   Copyright (c) 2012 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 12/08/06
 *   modified :
 *   
 *   #qblog_recent_comments(N)
 */

define('PLUGIN_QBLOG_RECENT_COMMENTS_DEFAULT_NUM', 10);

function plugin_qblog_recent_comments_convert()
{
	global $script, $qblog_date_format, $qblog_close;

	//閉鎖中は何も表示しない
	if ($qblog_close && ! ss_admin_check())
	{
		return '';
	}
	
	//---- キャッシュのための処理を登録 -----
	$qt = get_qt();
	if($qt->create_cache) {
	  $args = func_get_args();
	  return $qt->get_dynamic_plugin_mark(__FUNCTION__, $args);
	}
	//------------------------------------
	
	$args = func_get_args();
	$nodate = in_array('nodate', $args);
	
	$datafile = CACHEQBLOG_DIR . 'qblog_recent_comments.dat';
	
	$comment_page_lines = explode("\n", file_get_contents($datafile));

	$comment_pages = array();
	foreach ($comment_page_lines as $line)
	{
		if (trim($line) === '') continue;
		list($time, $pagename) = explode("\t", $line);
		if (is_page($pagename))
		{
			$comment_pages[$pagename] = $time;
		}
	}
	
	//calc num show
	$num = (func_num_args() > 0) ?
		min(func_get_arg(0), PLUGIN_QBLOG_RECENT_COMMENTS_DEFAULT_NUM) :
		PLUGIN_QBLOG_RECENT_COMMENTS_DEFAULT_NUM;
	$num = min(count($comment_pages), $num);
	
	$html_str = '<ul class="qblog_recent_comments">';
	
	$cnt = 0;
	foreach ($comment_pages as $pagename => $time)
	{
		if ($cnt == PLUGIN_QBLOG_RECENT_COMMENTS_DEFAULT_NUM)
		{
			break;
		}
		$page_title = get_page_title($pagename);
		$page_title = ($nodate ? '' : date('m.d', $time) . ' ') . $page_title;
		$html_str .= '<li><a href="'.$script.'?'.rawurlencode($pagename).'">'.h($page_title) .'</a></li>'. "\n";
		$cnt++;
	}
	$html_str .= '</ul>';

	return $html_str;
}

/* End of file qbloc_recent_comments.inc.php */
/* Location: ./plugin/qblog_recent_comments.inc.php */