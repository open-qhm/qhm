<?php
/**
 *   QBlog Theme Plugin
 *   -------------------------------------------
 *   ./plugin/qblog_category.inc.php
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

function plugin_qblog_category_init()
{
	qblog_update();
}

function plugin_qblog_category_convert()
{
	global $vars, $script;
	global $qblog_default_cat, $qblog_close;

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

	$display_number = TRUE;
	$display_zero = FALSE;
	if (func_num_args()) {
		$args = func_get_args();

		foreach ($args as $arg)
		{
			if ($arg == 'numoff')
			{
				$display_number = FALSE;
			}
			if ($arg == 'displayall')
			{
				$display_zero = TRUE;
			}
		}
	}

	$catlist_file = CACHEQBLOG_DIR . 'qblog_categories.dat'; 
	if( file_exists($catlist_file) ){
		$cat_list = explode("\n", file_get_contents($catlist_file));
	}
	else{
		$cat_list = array();
	}

	$li = $first_li = '';
	foreach ($cat_list as $line)
	{
		if (rtrim($line) != '')
		{
			list($cat, $num) = explode("\t", rtrim($line));
			
			if ( ! $display_zero && $num == 0)
			{
				continue;
			}
			
			$cat_link = $script.'?QBlog&mode=category&catname='.rawurlencode($cat);
			$number_str = ($display_number) ? ' ('.$num.')' : '';

			if (trim($cat) == $qblog_default_cat)
			{
				$first_li .= '<li><a href="'.$cat_link.'">'.$cat.$number_str.'</a></li>';
			}
			else
			{
				$li .= '<li><a href="'.$cat_link.'">'.$cat.$number_str.'</a></li>';
			}
		}
	}
	
	$list = <<< EOD
<ul class="qblog_categories">
{$first_li}
{$li}
</ul>
EOD;

	return $list;
}

function plugin_qblog_category_inline()
{
	global $vars, $script;
	
	$args = func_get_args();
	if( count($args)==1 )
	{
		$cat = '';
		$text = $args[0];
	}
	else
	{
		$cat = $args[0];
		$text = $args[1];
	}
	
	return '<a href="'.$script.'?cmd=qblog_category&catname='.rawurlencode($cat).'">'.$text.'</a>';
}

/* End of file qblog_category.inc.php */
/* Location: ./plugin/qblog_category.inc.php */