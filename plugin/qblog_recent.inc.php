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
define('PLUGIN_QBLOG_RECENT_DEFAULT_NUM', 10);
 
function plugin_qblog_recent_convert()
{
	global $vars, $script, $qblog_page_re, $qblog_close;

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

	$display_num = PLUGIN_QBLOG_RECENT_DEFAULT_NUM;
	if (func_num_args()) {
		$args = func_get_args();
		$display_num = $args[0];
	}

	$recent_file = CACHEQBLOG_DIR . 'qblog_recent.dat'; 
	if( file_exists($recent_file) ){
		$recent_list = explode("\n", file_get_contents($recent_file));
	}
	else{
		$recent_list = array();
	}

	//件数を抜く
	$size = array_shift($recent_list);
	
	$list = '';
	$list .= '<ul class="qblog_recent">';
	foreach ($recent_list as $i => $line)
	{
		if ($i >= $display_num)
		{
			break;
		}

		if (rtrim($line) != '')
		{
			$pagename = rtrim($line);
			$title = get_page_title($pagename);
			
			if ($pagename == $title)
			{
				if (preg_match($qblog_page_re, $pagename, $mts))
				{
					$blog_date = "{$mts[1]}年{$mts[2]}月{$mts[3]}日";
					$title = " No.{$mts[4]}";
				}
			}
//! 表示方法　要検討
			$list .= '<li><a href="'.$script.'?'.rawurldecode($pagename).'">'. $blog_date.$title .'</a></li>';
		}
	}
	$list .= '</ul>';


    if ( ! is_bootstrap_skin())
    {
        $include_bs = '
<link rel="stylesheet" href="skin/bootstrap/css/bootstrap-custom.min.css" />
<script type="text/javascript" src="skin/bootstrap/js/bootstrap.min.js"></script>';
        $qt->appendv_once('include_bootstrap_pub', 'beforescript', $include_bs);
    }

    //qblog.css を読み込む
    $head = '
    <link rel="stylesheet" href="plugin/qblog/qblog.css" />';
    $qt->appendv_once('qblog_beforescript', 'beforescript', $head);

	
	return $list;
}
