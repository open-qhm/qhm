<?php
/**
 *   QBlog display post-head plugin
 *   -------------------------------------------
 *   ./plugin/qblog_head.inc.php
 *   
 *   Copyright (c) 2012 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 12/07/30
 *   modified :
 *   
 *   Description
 *   
 *   Usage :
 *   
 */

function plugin_qblog_head_convert()
{
	global $vars, $script, $defaultpage;
	global $qblog_date_format, $qblog_page_re, $qblog_defaultpage, $qblog_close, $qblog_default_cat;
	
	if (! is_qblog()) return '';
	
	$closed_msg = '';
	if ($qblog_close)
	{
		if (ss_admin_check())
		{
			$closed_msg = '
<div class="alert" style="margin-top: 15px ;margin-bottom: 15px ;">
	<button class="close" data-dismiss="alert">×</button>
	<p>
		ブログは閉鎖されています。<br />
		管理者以外のアクセスはトップページへ転送されます。
	</p>
	<p>
		※ブログメニュー上のリストも管理者以外には表示されません。
	</p>
</div>
';
		}
		else
		{
			redirect($defaultpage);
		}
	}
	
	$qt = get_qt();
	
	//RSSフィードを出力
	if (exist_plugin('rss'))
	{
		$rssurl = $script . '?cmd=rss&qblog_rss=1';
		$qt->setv_once('rss_link', $rssurl);
	}

    if ( ! is_bootstrap_skin())
    {
        $include_bs = '
<link rel="stylesheet" href="skin/bootstrap/css/bootstrap-custom.min.css" />
<script type="text/javascript" src="skin/bootstrap/js/bootstrap.min.js"></script>';
        $qt->appendv_once('include_bootstrap_pub', 'beforescript', $include_bs);
    }

	//qblog.css を読み込む
	$head = '
<link rel="stylesheet" href="plugin/qblog/qblog.css' .'" />';
	
	$qt->appendv_once('qblog_beforescript', 'beforescript', $head);

	$page = $vars['page'];
	
	// ブログトップは<head>内の調整のみ
	if ($page === $qblog_defaultpage)
	{
		return $closed_msg;
	}
	
	//日付を取得
	$date = get_qblog_date($qblog_date_format, $page);

	$data = get_qblog_post_data($page);
	if ($vars['cmd'] == 'edit')
	{
		//新規ページ
		if ( ! $data)
		{
			$data['title'] = $page;
			$data['category'] = $qblog_default_cat;
		}
		
		$data['title'] = isset($vars['title']) && $vars['title'] ? $vars['title'] : $data['title'];
		$data['category'] = isset($vars['category']) && $vars['category'] ? $vars['category'] : $data['category'];

		if (isset($vars['qblog_date']))
		{
			$date = $vars['qblog_date'];
			list($y, $m, $d ) = array_pad(explode('-', $vars['qblog_date']), 3, '');
			if (checkdate($m, $d, $y))
			{
				$time = mktime(0, 0, 0, $m, $d, $y);
				$date = date($qblog_date_format, $time);
			}
		}
	}
	
	$category_url = $script.'?'.$qblog_defaultpage.'&mode=category&catname='.rawurlencode($data['category']);

	$addpostlink_html = '';
	if ( ss_admin_check())
	{
		$editpostlink = $script . '?cmd=edit&page='.$page;
		$addpostlink = $script . '?cmd=qblog&mode=addpost';
		$addpostlink_html = '
<a href="'. h($editpostlink) .'" class="badge badge-important" style="color:#fff"><i class="icon-white icon-edit" style="vertical-align:text-bottom"></i> この記事を編集</a>
<a href="'. h($addpostlink) .'" class="badge badge-info" style="color:#fff"><i class="icon-white icon-plus" style="vertical-align:text-bottom"></i> 記事の追加</a>
';
		
	}
	$head = '
<style type="text/css">
#content h2.title{display:none;}
</style>
'. $closed_msg .'
<div class="title">
<span class="qblog_post_date">'. h($date) .'</span>
'.$addpostlink_html.'
<a href="'. h($category_url) .'" class="qblog_category badge">カテゴリ：'. h($data['category']) .'</a>
</div>
<h2>'. h($data['title']) .'</h2>
';

	if (trim($data['image']) !== '')
	{
		if (is_file(SWFU_IMAGE_DIR . $data['image']))
		{
			$data['image'] = SWFU_IMAGE_DIR . $data['image'];
		}
		$head .= <<< EOH
EOH;
	}
	
	return $head;
	
}

/* End of file qblog_head.inc.php */
/* Location: ./plugin/qblog_head.inc.php */