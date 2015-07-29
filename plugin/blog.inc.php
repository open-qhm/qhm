<?php
// $Id: blog.inc.php,v 0.01 2008/07/09 16:26:34 edo Exp $
//
// Blog plugin
// based on Calendar2 plugin
//
// Usage:
//	#blog({[pagename|*],[yyyymm]})

require_once(PLUGIN_DIR.'topicpath.inc.php');
require_once(PLUGIN_DIR.'blog_viewer.inc.php');

function plugin_blog_convert()
{
	$qt = get_qt();
	//---- キャッシュのための処理を登録 -----
	if($qt->create_cache) {
		$args = func_get_args();
		return $qt->get_dynamic_plugin_mark(__FUNCTION__, $args);
	}
	//------------------------------------	
	
	global $script, $vars, $post, $get, $weeklabels, $WikiName, $BracketName;
	$qm = get_qm();

	$date_str = get_date('Ym');
	$base     = strip_bracket($vars['page']);

	$today_view = false;
	if (func_num_args()) {
		$args = func_get_args();
		foreach ($args as $arg) {
			if (is_numeric($arg) && strlen($arg) == 6) {
				$date_str = $arg;
			} else if ($arg == 'off') {
				$today_view = FALSE;
			} else {
				$base = strip_bracket($arg);
			}
		}
	}
	if ($base == '*') {
		$base   = '';
		$prefix = '';
	} else {
		$prefix = $base . '/';
	}
	$r_base   = rawurlencode($base);
	$s_base   = htmlspecialchars($base);
	$r_prefix = rawurlencode($prefix);
	$s_prefix = htmlspecialchars($prefix);

	$yr  = substr($date_str, 0, 4);
	$mon = substr($date_str, 4, 2);
	if ($yr != get_date('Y') || $mon != get_date('m')) {
		$now_day = 1;
		$other_month = 1;
	} else {
		$now_day = get_date('d');
		$other_month = 0;
	}

	$today = getdate(mktime(0, 0, 0, $mon, $now_day, $yr) - LOCALZONE + ZONETIME);

	$m_num = $today['mon'];
	$d_num = $today['mday'];
	$year  = $today['year'];

	$f_today = getdate(mktime(0, 0, 0, $m_num, 1, $year) - LOCALZONE + ZONETIME);
	$wday = $f_today['wday'];
	$day  = 1;

	$m_name = $year . '.' . $m_num;

	$y = substr($date_str, 0, 4) + 0;
	$m = substr($date_str, 4, 2) + 0;

	$prev_date_str = ($m == 1) ?
		sprintf('%04d%02d', $y - 1, 12) : sprintf('%04d%02d', $y, $m - 1);

	$next_date_str = ($m == 12) ?
		sprintf('%04d%02d', $y + 1, 1) : sprintf('%04d%02d', $y, $m + 1);

	$ret = '';
	$ret = '<div id="blog_calendar" style="float:left;margin:0 15px;">'."\n";
	if ($today_view) {
		$ret = '<table border="0" summary="calendar frame">' . "\n" .
			' <tr>' . "\n" .
			'  <td valign="top">' . "\n";
	}
	else {
		$today_view_str = "&amp;view=off";
	}
	
	$ret .= <<<EOD
   <table class="style_calendar" cellspacing="1" border="0" summary="calendar body">
    <tr>
     <td class="style_td_caltop" colspan="7">
      <a href="$script?plugin=blog&amp;file=$r_base&amp;date=$prev_date_str$today_view_str">&lt;&lt;</a>
      <strong>$m_name</strong>
      <a href="$script?plugin=blog&amp;file=$r_base&amp;date=$next_date_str$today_view_str">&gt;&gt;</a>
EOD;

	if ($prefix) $ret .= "\n" .
		'      <br />[<a href="' . $script . '?' . $r_base . '">' . $s_base . '</a>]';

	$ret .= "\n" .
		'     </td>' . "\n" .
		'    </tr>'  . "\n" .
		'    <tr>'   . "\n";

	foreach($weeklabels as $label)
		$ret .= '     <td class="style_td_week" width="30" height="30">' . $label . '</td>' . "\n";

	$ret .= '    </tr>' . "\n" .
		'    <tr>'  . "\n";
	// Blank
	for ($i = 0; $i < $wday; $i++)
		$ret .= '     <td class="style_td_blank">&nbsp;</td>' . "\n";

	while (checkdate($m_num, $day, $year)) {
		$dt     = sprintf('%4d-%02d-%02d', $year, $m_num, $day);
		$page   = $prefix . $dt;
		$r_page = rawurlencode($page);
		$s_page = htmlspecialchars($page);

		if ($wday == 0 && $day > 1)
			$ret .=
			'    </tr>' . "\n" .
			'    <tr>' . "\n";

		$style = 'style_td_day'; // Weekday
		if (! $other_month && ($day == $today['mday']) && ($m_num == $today['mon']) && ($year == $today['year'])) { // Today
			$style = 'style_td_today';
		} else if ($wday == 0) { // Sunday
			$style = 'style_td_sun';
		} else if ($wday == 6) { //  Saturday
			$style = 'style_td_sat';
		}

		if (is_page($page)) {
			$link = '<a href="' . $script . '?' . $r_page . '" title="' . $s_page .
				'"><strong>' . $day . '</strong></a>';
		} else {
			if (PKWK_READONLY) {
				$link = '<span class="small">' . $day . '</span>';
			} else {
				$editable = check_editable($page, FALSE, FALSE);
				// 管理ログイン後
				if ($editable === TRUE) {
					$link = $script . '?cmd=blog_edit&amp;page=' . $r_page;
					$link = '<a class="small" href="' . $link . '" title="' . $s_page . '">' . $day . '</a>';
				} else {
					$link = '<span class="small">' . $day . '</span>';
				}
			}
		}

		$ret .= '     <td class="' . $style . '">' . "\n" .
			'      ' . $link . "\n" .
			'     </td>' . "\n";
		++$day;
		$wday = ++$wday % 7;
	}

	if ($wday > 0)
		while ($wday++ < 7) // Blank
			$ret .= '     <td class="style_td_blank">&nbsp;</td>' . "\n";

	$ret .= '    </tr>'   . "\n" .
		'   </table>' . "\n";

	if ($today_view) {
		$tpage = $prefix . sprintf('%4d-%02d-%02d', $today['year'],
			$today['mon'], $today['mday']);
		$r_tpage = rawurlencode($tpage);
		if (is_page($tpage)) {
			$_page = $vars['page'];
			$get['page'] = $post['page'] = $vars['page'] = $tpage;
			$str = convert_html(get_source($tpage));
			$str .= '<hr /><a class="small" href="' . $script .
				'?cmd=edit&amp;page=' . $r_tpage . '">' .
				$qm->m['plg_calendar2']['edit'] . '</a>';
			$get['page'] = $post['page'] = $vars['page'] = $_page;
		} else {
			$str = $qm->replace('plg_calendar2.err_empty', make_pagelink(sprintf('%s%4d-%02d-%02d', $prefix,
				$today['year'], $today['mon'], $today['mday'])));
		}
		$ret .= '  </td>' . "\n" .
			'  <td valign="top">' . $str . '</td>' . "\n" .
			' </tr>'   . "\n" .
			'</table>' . "\n";
	}
	$ret .= '</div>'."\n";

	return $ret;
}

function plugin_blog_action()
{
	global $vars;

	$page = strip_bracket($vars['page']);
	$vars['page'] = '*';
	if ($vars['file']) $vars['page'] = $vars['file'];

	$date = $vars['date'];
	
	if ($vars['view']) $view = $vars['view'];

	if ($date == '') $date = get_date('Ym');
	$yy = sprintf('%04d.%02d', substr($date, 0, 4),substr($date, 4, 2));

	$aryargs = array($vars['page'], $date);
	if ($view) {
		array_push($aryargs, $view);
	}

	$s_page  = htmlspecialchars($vars['page']);

	$ret['msg']  = 'calendar ' . $s_page . '/' . $yy;
	$ret['body'] = call_user_func_array('plugin_blog_convert', $aryargs);

	$vars['page'] = $s_page.'/'.$yy;
	$topic = call_user_func('plugin_topicpath_convert');
	$ret['body'] = $topic .'<br />' . $ret['body'];

	$param_yy = str_replace('.','-', $yy);
	$list = call_user_func_array('plugin_blog_viewer_convert', array($s_page, $param_yy));
	$ret['body'] .= '<br style="clear:both;" />'.$list;


	$vars['page'] = $page;

	return $ret;
}
?>
