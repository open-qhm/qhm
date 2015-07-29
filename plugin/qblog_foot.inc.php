<?php
/**
 *   QBlog Footer Plugin
 *   -------------------------------------------
 *   ./plugin/qblog_foot.inc.php
 *
 *   Copyright (c) 2012 hokuken
 *   http://hokuken.com/
 *
 *   created  : 12/08/01
 *   modified :
 *
 */

function plugin_qblog_foot_convert()
{
	global $vars, $script, $qblog_page_re, $qblog_defaultpage;
	global $qblog_include_page, $qblog_social_widget, $qblog_social_html, $qblog_social_wiki;
	$qt = get_qt();

	$page = $vars['page'];

	if ($page === $qblog_defaultpage OR ! is_qblog())
	{
		return '';
	}

	$addquery = '';
	$datafile = CACHEQBLOG_DIR . 'qblog_recent.dat';
	if (isset($vars['mode']))
	{
		$mode = trim($vars['mode']);
		if ($mode === 'category')
		{
			//カテゴリーから来た時はキャッシュ無効
			$qt->enable_cache = FALSE;

			$cat = trim($vars['catname']);
			$datafile = CACHEQBLOG_DIR . encode($cat) . '.qbc.dat';
			$addquery = '&mode=category&catname='. rawurlencode($cat);
		}
		else if ($mode === 'archives')
		{
			//月別アーカイブから来た時はキャッシュ無効
			$qt->enable_cache = FALSE;
			$date = $vars['date'];
			$addquery = '&mode=archives&date=' . rawurlencode($date);
		}
	}

	//前後の記事を qblog_recent.dat から持ってくる。
	$fp = fopen($datafile, 'r');

	$prev = $next = $most_recent = '';
	$notfound = TRUE;
	if ($fp)
	{
		if ($mode !== 'category')
		{
			//件数の行を飛ばす
			fgets($fp, 64);
		}

		while( ! feof($fp))
		{
			$line = trim(fgets($fp, 64));
			if ($most_recent === '')
			{
				$most_recent = $line;
			}

			if ($page === $line)
			{
				$notfound = FALSE;
				if ( ! feof($fp))
				{
					$prev = trim(fgets($fp, 64));
				}
				break;
			}
			$next = $line;
		}
		fclose($fp);
	}

	//新規記事追加後で、リストに自分のページ名がない場合
	if ($notfound && $most_recent !== '')
	{
		$prev = $most_recent;
		$next = '';
	}

	//日付アーカイブからのアクセスでは、同月のみ表示
	if ($mode === 'archives')
	{
		$prev = ($prev && $date == get_qblog_date('Ym', $prev)) ? $prev : FALSE;
		$next = ($next && $date == get_qblog_date('Ym', $next)) ? $next : FALSE;
	}

	$prevlink = $script . '?' . rawurlencode($prev) . $addquery;
	$nextlink = $script . '?' . rawurlencode($next) . $addquery;
	$pagelink = $script . '?' . rawurlencode($page);

	$foot = '<ul class="pager">';
	$foot .= $next ? '<li class="next"><a href="'. h($nextlink).'">次の記事　&rarr;</a></li>' : '';
//	$foot .= '<li><a href="'. h($pagelink) .'">パーマリンク</a></li>';//TODO: 自分自身へのリンクは消される
	$foot .= $prev ? '<li class="previous"><a href="'. h($prevlink).'">&larr;　前の記事</a></li>' : '';
	$foot .= '</ul>';

	//<link> を出力する
	$head  = '<link rel="contents" href="'. h($script . '?' . $qblog_defaultpage) .'" />';
	$head .= $prev ? '<link rel="prev" href="'. h($prevlink) .'" />' : '';
	$head .= $next ? '<link rel="next" href="'. h($nextlink) .'" />' : '';
	$qt->appendv_once('qblog_foot', 'beforescript', $head);


	// ! ソーシャルボタンなど、共通部品を出力する
	if ($qblog_social_widget === 'default')
	{
		$foot = convert_html("#social_buttons") . $foot;
	}
	else if ($qblog_social_widget === 'wiki')
	{
		$foot = convert_html($qblog_social_wiki) . $foot;
	}
	else if ($qblog_social_widget === 'html')
	{
		$foot = $qblog_social_html . $foot;
	}

	return $foot;
}

/* End of file qblog_foot.inc.php */
/* Location: ./plugin/qblog_foot.inc.php */
