<?php
/**
 *   QBlog Plugin
 *   -------------------------------------------
 *   ./plugin/qblog_list.inc.php
 *
 *   Copyright (c) 2012 hokuken
 *   http://hokuken.com/
 *
 *   created  : 12/07/26
 *   modified :
 *
 *   Description
 *
 *   Usage :
 *
 */

// Default number of 'Show latest N posts'
define('PLUGIN_QBLOG_LIST_DEFAULT_POSTS', 8);

// Limit number of executions
define('PLUGIN_QBLOG_LIST_EXEC_LIMIT', 2); // N times per one output

// Place of the cache of 'RecentChanges'
define('PLUGIN_QBLOG_LIST_CACHE', CACHEQBLOG_DIR . 'qblog_recent.dat');

define('PLUGIN_QBLOG_LIST_PAGINATE_NUM', 5);

define('PLUGIN_QBLOG_LIST_PAGINATE_FIRST_NAV', '>>>');

define('PLUGIN_QBLOG_LIST_PAGINATE_LAST_NAV', '<<<');

// List display defualt type 'line', 'table'
define('PLUGIN_QBLOG_LIST_TYPE', 'table');


function plugin_qblog_list_init()
{
	qblog_update(TRUE);//debug
}

function plugin_qblog_list_convert()
{
	global $vars, $qblog_date_format, $show_passage;
	global $qblog_page_prefix, $qblog_defaultpage, $qblog_page_format, $qblog_close;
	global $style_name;

	//閉鎖中は何も表示しない
	if ($qblog_close && ! ss_admin_check())
	{
		return '';
	}

	static $exec_count = 1;

	$qm = get_qm();
	$qt = get_qt();

	$qt->setv('jquery_include', TRUE);

	set_js_for_fix_distortion_of_thumbnails();

	$list_type = PLUGIN_QBLOG_LIST_TYPE;
	$recent_posts = PLUGIN_QBLOG_LIST_DEFAULT_POSTS;
	if (func_num_args()) {
		$args = func_get_args();

		if (count($args) > 2)
		{
			return '#qblog_list([line or table], [number])';
		}

		foreach ($args as $arg)
		{
			if (is_numeric($arg))
			{
				$recent_posts = (int)$arg;
			}

			if ($arg == 'line')
			{
				$list_type = $arg;
			}
		}
	}

	//表示モード
	//recent, archives, category
	$mode = isset($vars['mode']) ? $vars['mode'] : 'recent';

	//表示ページ：ページネーション
	//表示ページは必ず 1以上の整数
	$page_num = isset($vars['p']) ? (int)$vars['p'] : 1;
	$page_num = ($page_num <= 0) ? 1 : $page_num;

	// !前処理

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


	//---- キャッシュのための処理を登録 -----
	$qt->enable_cache = FALSE;
	//------------------------------------


	if (! file_exists(PLUGIN_QBLOG_LIST_CACHE))
		return $qm->m['plg_recent']['err_file_notfound'] . '<br />';

	$script = get_script_uri();
	$date = $items = '';

	//h2.title 前に挿入するHTML
	$pre_title_html = '';

	// !新規記事追加リンクを表示
	// デフォルトブログページが編集できるユーザー
	$editable = FALSE;
	if (check_editable($qblog_defaultpage, FALSE, FALSE) === TRUE)
	{
		$editable = TRUE;
		$search_replace = array(
			'YYYY' => date('Y'),
			'MM'   => date('m'),
			'DD'   => date('d'),
		);
		$newpage = str_replace(array_keys($search_replace), array_values($search_replace),
			$qblog_page_format);
		$number_holder_pos = strpos($newpage, '#');
		if ($number_holder_pos !== FALSE)
		{
			$filename_prefix = encode(substr($newpage, 0, $number_holder_pos));
			$files = glob(DATA_DIR . $filename_prefix . '*');

			$pattern = '/^(' . str_replace('#', '(\d+)', preg_quote($newpage)) . ')$/';
			$max = 1;
			foreach ($files as $file)
			{
				$pagename = decode(basename($file, '.txt'));
				if (preg_match($pattern, $pagename, $mts))
				{
					$max = max($mts[2], $max);
				}
			}

			$newpage = str_replace('#', $max + 1, $newpage);
		}

		$addpostlink = $script . '?cmd=qblog&mode=addpost';
		$pre_title_html .= '<a href="'. h($addpostlink) .'" class="badge badge-info" style="color:#fff"><i class="icon-white icon-edit" style="vertical-align:text-bottom;"></i> 記事の追加</a> ';
	}


	// !モードによって、読み込むキャッシュを替える
	$pages = array();
	$start = ($page_num-1) * $recent_posts;

	$addquery = '';
	switch ($mode)
	{
		case 'archives':
			$date = $vars['date'];
			$addquery = '&mode=archives&date=' . rawurlencode($date);
			if (preg_match('/^(\d{4})(\d{2})$/', $date, $mts))
			{
				$year = $mts[1];
				$month = $mts[2];
			}
			else
			{
				$year = date('Y');
				$month = date('m');
			}
			$date = $year . $month;

			$files = glob(DATA_DIR . encode($qblog_page_prefix . $date) . '*');
			foreach ($files as $file)
			{
				$pages[] = decode(basename($file, '.txt'));
			}

			$count_pages = count($pages);

			natsort($pages);
			$pages = array_reverse($pages);

			$pages = array_slice($pages, $start, $recent_posts);

			$subtitle = "{$year}年{$month}月";
			$pre_title_html .= '<span class="badge">'.h($subtitle).'</span> ';
			break;
		case 'category':
			$cat = isset($vars['catname']) ? $vars['catname'] : $qblog_default_cat;
			$addquery = '&mode=category&catname='. rawurlencode($cat);
			$pages = explode("\n", trim(file_get_contents(CACHEQBLOG_DIR . encode($cat) . '.qbc.dat')));
			$count_pages = count($pages);
			$pages = array_slice($pages, $start, $recent_posts);

			$pre_title_html .= '<span class="badge">カテゴリ：'.h($cat).'</span> ';
			break;
		default://recent mode
			// Get latest N changes
			$count_pages = (int)array_pop(file_head(PLUGIN_QBLOG_LIST_CACHE, 1));
			$lines = file_slice(PLUGIN_QBLOG_LIST_CACHE, $start + 1, $recent_posts);
			foreach ($lines as $line)
			{
				$pages[] = rtrim($line);
			}
	}

	//! 記事毎のデータをまとめる
	$posts = array();
	foreach ($pages as $i => $page) {
		//キャッシュファイルを読み込む
		$data = get_qblog_post_data($page);
		if ($data === FALSE)
		{
			continue;
		}

		$r_page = rawurlencode($page);

		if (is_file(SWFU_IMAGE_DIR . $data['image']))
		{
			$data['image'] = SWFU_IMAGE_DIR . $data['image'];
		}
		if (trim($data['image']) === '')
		{
			$data['image'] = PLUGIN_DIR . 'qblog/qblog_thumbnail.png';
		}

		if (trim($data['image']) === '')
		{
			$data['image'] = PLUGIN_DIR . 'qblog/qblog_thumbnail.png';
		}

		$posts[$i] = array(
			'page'  => $page,
			'title' => $data['title'],
			'abstract' => $data['abstract'],
			'image' => $data['image'],
			'category' => $data['category'],
			'url' => $script . '?' . $r_page . $addquery,
			'date' => get_qblog_date($qblog_date_format, $page),
		);
	}

	// !ページネーションリンクを足す
	$paginates = array();
	if ($count_pages > $recent_posts)
	{
		if ($page_num > 1)
		{
			$paginates[PLUGIN_QBLOG_LIST_PAGINATE_LAST_NAV] = $script .'?'. $qblog_defaultpage. '&p=1' . $addquery;
		}

		$paginate_length = ceil($count_pages / $recent_posts);

		if (PLUGIN_QBLOG_LIST_PAGINATE_NUM < $paginate_length)
		{

		}

		$range = (int)floor(PLUGIN_QBLOG_LIST_PAGINATE_NUM / 2);
		$start = (int)max(1, $page_num - $range);
		$end = (int)min($paginate_length+1, $start + PLUGIN_QBLOG_LIST_PAGINATE_NUM);

		// 最初<<< 1 | 2 | 3 | 4 | 5 >>>最後
		// 最初<<< 5 | 6 | 7 | 8 | 9 >>>最後

		for ($i = $start; $i < $end; $i++)
		{
			$paginates[$i] = $script .'?'. $qblog_defaultpage .'&p='. ($i) . $addquery;
			if ($page_num == ($i))
			{
				$paginates[$i] = '';
			}
		}

		if ($page_num < $paginate_length)
		{
			$paginates[PLUGIN_QBLOG_LIST_PAGINATE_FIRST_NAV] = $script .'?'. $qblog_defaultpage .'&p='. ($paginate_length).$addquery;
		}
	}


    $template_name = 'qblog_list_template.html';
    if (file_exists(SKIN_DIR.$style_name.'/'.$template_name))
    {
		$template_path = SKIN_DIR.$style_name.'/'.$template_name;
    }
    else
    {
		$template_path = PLUGIN_DIR . 'qblog/list_template.html';
    }

	//! テンプレートを読み込む
	ob_start();
	include($template_path);
	$items .= ob_get_clean();

	//! h2.title にbadge を挿入
	if ($list_type === 'table')
	{
		$qt->prependv('this_right_title', $pre_title_html);
	}

	return '<div id="qblog">'. $items .'</div>';
}

/**
 * サムネイル画像の歪みを修正するJSを発行
 */
function set_js_for_fix_distortion_of_thumbnails() {
	$js = file_get_contents(PLUGIN_DIR . '/qblog/fix_distortion_of_thumbnails.js');
	$qt = get_qt();
	$qt->appendv_once('qblog_list_fix_distortion_of_thumbnails', 'beforescript', wrap_script_tag($js));
}

/* End of file qblog_list.inc.php */
/* Location: ./plugin/qblog_list.inc.php */
