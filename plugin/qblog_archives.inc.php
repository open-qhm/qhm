<?php
/**
 *   QBlog Achives Plugin
 *   -------------------------------------------
 *   ./plugin/qblog_archives.inc.php
 *
 *   Copyright (c) 2012 hokuken
 *   http://hokuken.com/
 *
 *   created  : 12/07/27
 *   modified :
 *
 *   Description
 *   
 *   
 *   Usage :
 *   
 */
function plugin_qblog_archives_convert()
{
	global $vars, $script, $qblog_close;

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

	$archives_file = CACHEQBLOG_DIR . 'qblog_archives.dat'; 
	if( file_exists($archives_file) ){
		$archives_list = file_get_contents($archives_file);
	}
	else{
		$archives_list = array();
	}

	$list = '';
	$list .= '<ul class="qblog_archives">';
	foreach (explode("\n", $archives_list) as $line)
	{
		if (rtrim($line) != '')
		{
			list($year, $month, $num) = explode(",", rtrim($line));
			$archives_url = $script.'?QBlog&amp;mode=archives&amp;date='.rawurlencode($year.$month);
			$list .= '<li><a href="'.$archives_url.'">'.$year.'年'.$month.'月 ('.$num.')'.'</a></li>';
		}
	}
	$list .= '</ul>';
	
	return $list;
}

?>