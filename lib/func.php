<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: func.php,v 1.73 2006/05/15 16:41:39 teanan Exp $
// Copyright (C)
//   2002-2006 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// General functions

function is_interwiki($str)
{
	global $InterWikiName;
	return preg_match('/^' . $InterWikiName . '$/', $str);
}

function is_pagename($str)
{
	global $BracketName;

	$is_pagename = (! is_interwiki($str) &&
		  preg_match('/^(?!\/)' . $BracketName . '$(?<!\/$)/', $str) &&
		! preg_match('#(^|/)\.{1,2}(/|$)#', $str));

	if (defined('SOURCE_ENCODING')) {
		switch(SOURCE_ENCODING){
		case 'UTF-8': $pattern =
			'/^(?:[\x00-\x7F]|(?:[\xC0-\xDF][\x80-\xBF])|(?:[\xE0-\xEF][\x80-\xBF][\x80-\xBF]))+$/';
			break;
		case 'EUC-JP': $pattern =
			'/^(?:[\x00-\x7F]|(?:[\x8E\xA1-\xFE][\xA1-\xFE])|(?:\x8F[\xA1-\xFE][\xA1-\xFE]))+$/';
			break;
		}
		if (isset($pattern) && $pattern != '')
			$is_pagename = ($is_pagename && preg_match($pattern, $str));
	}

	return $is_pagename;
}

function is_url($str, $only_http = FALSE, $omit_protocol = FALSE)
{
  	$scheme = $only_http ? 'https?' : 'https?|ftp|news';
  	$scheme = $omit_protocol ? ('(('. $scheme . '):)?') : ('(' . $scheme . '):');
  	return preg_match('/^(' . $scheme . ')(\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]*)$/', $str);
}

function is_image($str)
{
	return preg_match('/\.(gif|png|jpe?g)$/i', $str);
}

// If the page exists
function is_page($page, $clearcache = FALSE)
{
	if ($clearcache) clearstatcache();
	return file_exists(get_filename($page));
}

function is_editable($page)
{
	global $cantedit;
	static $is_editable = array();

	if (! isset($is_editable[$page])) {
		$is_editable[$page] = (
			is_pagename($page) &&
			! is_freeze($page) &&
			! in_array($page, $cantedit)
		);
	}

	return $is_editable[$page];
}

function is_freeze($page, $clearcache = FALSE)
{
	global $function_freeze;
	static $is_freeze = array();

	if ($clearcache === TRUE) $is_freeze = array();
	if (isset($is_freeze[$page])) return $is_freeze[$page];

	if (! $function_freeze || ! is_page($page)) {
		$is_freeze[$page] = FALSE;
		return FALSE;
	} else {
		$fp = fopen(get_filename($page), 'rb') or
			die('is_freeze(): fopen() failed: ' . htmlspecialchars($page));
		flock($fp, LOCK_SH) or die('is_freeze(): flock() failed');
		rewind($fp);
		$buffer = fgets($fp, 9);
		flock($fp, LOCK_UN) or die('is_freeze(): flock() failed');
		fclose($fp) or die('is_freeze(): fclose() failed: ' . htmlspecialchars($page));

		$is_freeze[$page] = ($buffer != FALSE && rtrim($buffer, "\r\n") == '#freeze');
		return $is_freeze[$page];
	}
}

/**
 *   iPhone, iPod, android からのアクセスかどうか判定する
 */
function is_smart_phone() {
	return
		strpos(UA_NAME, 'iPhone') !== FALSE ||
		strpos(UA_NAME, 'iPod')   !== FALSE ||
		strpos(UA_NAME, 'Mobile Safari') !== FALSE;
}

/**
 *
 */
function is_qblog($page = NULL)
{
	global $vars, $qblog_defaultpage, $qblog_page_format;
	static $is_qblog;

	if (is_null($page))
	{
		$page = $vars['page'];
		if (isset($is_qblog))
			return $is_qblog;
	}

	$is_qblog = FALSE;

	$search_replaces = array(
		'YYYY' => '\d{4}',
		'MM'   => '\d{2}',
		'DD'   => '\d{2}',
		'\#'   => '\d+', // PHP7.3 対応 @see https://www.php.net/manual/ja/migration73.other-changes.php#migration73.other-changes.pcre
		'#'    => '\d+'  // PHP7.2 以下対応
	);
	$re = preg_quote($qblog_page_format);
	$re = str_replace(
		array_keys($search_replaces), array_values($search_replaces),
		$re);
	$re = '/^' . $re . '$/';

//var_dump($qblog_page_format, preg_match($qblog_page_format, $page), '/^QBlog\-\d{4}\d{2}\d{2}\-\d+$/', preg_match('/^QBlog\-\d{4}\d{2}\d{2}\-\d+$/', $page));
	if ($page === $qblog_defaultpage OR
		preg_match($re, $page))
	{
		$is_qblog = TRUE;
		return $is_qblog;
	}
}

/**
 * 現在のリクエストがSSLかどうかを判定して返す
 */
function is_ssl()
{
	static $is_ssl;
	if ( is_null($is_ssl))
	{
		foreach(array(
				'HTTPS' => 'on',
				'SERVER_PORT' => '443',
				'HTTP_X_FORWARDED_PROTO' => 'https', //例 : ロリポップ
			) as $k=>$v){

			if( isset($_SERVER[$k]) &&  $_SERVER[$k]==$v ){
				$is_ssl = TRUE;
				return $is_ssl;
			}
		}
		$is_ssl = FALSE;
	}
	return $is_ssl;
}

// Handling $non_list
// $non_list will be preg_quote($str, '/') later.
function check_non_list($page = '')
{
	global $non_list;
	static $regex;

	if (! isset($regex)) $regex = '/' . $non_list . '/';

	return preg_match($regex, $page);
}

// Auto template
function auto_template($page)
{
	global $auto_template_func, $auto_template_rules;

	if (! $auto_template_func) return '';

	$body = '';
	$matches = array();
	foreach ($auto_template_rules as $rule => $template) {
		$rule_pattrn = '/' . $rule . '/';

		if (! preg_match($rule_pattrn, $page, $matches)) continue;

		$template_page = preg_replace($rule_pattrn, $template, $page);
		if (! is_page($template_page)) continue;

		$body = join('', get_source($template_page));

		// Remove fixed-heading anchors
		$body = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', $body);

		// Remove '#freeze'
		$body = preg_replace('/^#freeze\s*$/m', '', $body);

		$count = count($matches);
		for ($i = 0; $i < $count; $i++)
			$body = str_replace('$' . $i, $matches[$i], $body);

		break;
	}
	return $body;
}

// Expand all search-words to regexes and push them into an array
function get_search_words($words = array(), $do_escape = FALSE)
{
	static $init, $mb_convert_kana, $pre, $post, $quote = '/';

	if (! isset($init)) {
		// function: mb_convert_kana() is for Japanese code only
		if (LANG == 'ja' && function_exists('mb_convert_kana')) {
			$mb_convert_kana = create_function('$str, $option',
				'return mb_convert_kana($str, $option, SOURCE_ENCODING);');
		} else {
			$mb_convert_kana = create_function('$str, $option',
				'return $str;');
		}
		if (SOURCE_ENCODING == 'EUC-JP') {
			// Perl memo - Correct pattern-matching with EUC-JP
			// http://www.din.or.jp/~ohzaki/perl.htm#JP_Match (Japanese)
			$pre  = '(?<!\x8F)';
			$post =	'(?=(?:[\xA1-\xFE][\xA1-\xFE])*' . // JIS X 0208
				'(?:[\x00-\x7F\x8E\x8F]|\z))';     // ASCII, SS2, SS3, or the last
		} else {
			$pre = $post = '';
		}
		$init = TRUE;
	}

	if (! is_array($words)) $words = array($words);

	// Generate regex for the words
	$regex = array();
	foreach ($words as $word) {
		$word = trim($word);
		if ($word == '') continue;

		// Normalize: ASCII letters = to single-byte. Others = to Zenkaku and Katakana
		$word_nm = $mb_convert_kana($word, 'aKCV');
		$nmlen   = mb_strlen($word_nm, SOURCE_ENCODING);

		// Each chars may be served ...
		$chars = array();
		for ($pos = 0; $pos < $nmlen; $pos++) {
			$char = mb_substr($word_nm, $pos, 1, SOURCE_ENCODING);

			// Just normalized one? (ASCII char or Zenkaku-Katakana?)
			$or = array(preg_quote($do_escape ? htmlspecialchars($char) : $char, $quote));
			if (strlen($char) == 1) {
				// An ASCII (single-byte) character
				foreach (array(strtoupper($char), strtolower($char)) as $_char) {
					if ($char != '&') $or[] = preg_quote($_char, $quote); // As-is?
					$ascii = ord($_char);
					$or[] = sprintf('&#(?:%d|x%x);', $ascii, $ascii); // As an entity reference?
					$or[] = preg_quote($mb_convert_kana($_char, 'A'), $quote); // As Zenkaku?
				}
			} else {
				// NEVER COME HERE with mb_substr(string, start, length, 'ASCII')
				// A multi-byte character
				$or[] = preg_quote($mb_convert_kana($char, 'c'), $quote); // As Hiragana?
				$or[] = preg_quote($mb_convert_kana($char, 'k'), $quote); // As Hankaku-Katakana?
			}
			$chars[] = '(?:' . join('|', array_unique($or)) . ')'; // Regex for the character
		}

		$regex[$word] = $pre . join('', $chars) . $post; // For the word
	}

	return $regex; // For all words
}

// 'Search' main function
function do_search($word, $type = 'AND', $non_format = FALSE, $base = '')
{
	global $script, $whatsnew, $non_list, $search_non_list;
	global $_msg_andresult, $_msg_orresult, $_msg_notfoundresult;
	global $search_auth, $show_passage;

	$retval = array();

	$b_type = ($type == 'AND'); // AND:TRUE OR:FALSE
	$word = mb_convert_encoding($word, SOURCE_ENCODING, 'auto');
	$word = mb_ereg_replace("　", " ", $word);
	$keys = get_search_words(preg_split('/\s+/', $word, -1, PREG_SPLIT_NO_EMPTY));
	foreach ($keys as $key=>$value)
		$keys[$key] = '/' . $value . '/S';

	$pages = get_existpages();

	// Avoid
	if ($base != '') {
		$pages = preg_grep('/^' . preg_quote($base, '/') . '/S', $pages);
	}
	if (! $search_non_list) {
		$pages = array_diff($pages, preg_grep('/' . $non_list . '/S', $pages));
	}
	$pages = array_flip($pages);
	unset($pages[$whatsnew]);

	$count = count($pages);
	foreach (array_keys($pages) as $page) {
		$b_match = FALSE;

		// Search for page name
		if (! $non_format) {
			foreach ($keys as $key) {
				$b_match = preg_match($key, $page);
				if ($b_type xor $b_match) break; // OR
			}
			if ($b_match) continue;
		}

		// Search auth for page contents
		if ($search_auth && ! check_readable($page, false, false)) {
			unset($pages[$page]);
			--$count;
		}

		// Search for page contents
		foreach ($keys as $key) {
			$b_match = preg_match($key, get_source($page, TRUE, TRUE));
			if ($b_type xor $b_match) break; // OR
		}
		if ($b_match) continue;

		unset($pages[$page]); // Miss
	}
	if ($non_format) return array_keys($pages);

	$r_word = rawurlencode($word);
	$s_word = htmlspecialchars($word);
	if (empty($pages))
		return str_replace('$1', $s_word, $_msg_notfoundresult);

	ksort($pages);

	$retval = '<ul>' . "\n";
	foreach (array_keys($pages) as $page) {
		$r_page  = rawurlencode($page);
		$s_page  = htmlspecialchars($page);
		$passage = $show_passage ? ' ' . get_passage(get_filetime($page)) : '';
		$retval .= ' <li><a href="' . $script . '?cmd=read&amp;page=' .
			$r_page . '&amp;word=' . $r_word . '">' . $s_page .
			'</a>' . $passage . '</li>' . "\n";
	}
	$retval .= '</ul>' . "\n";

	$retval .= str_replace('$1', $s_word, str_replace('$2', count($pages),
		str_replace('$3', $count, $b_type ? $_msg_andresult : $_msg_orresult)));

	return $retval;
}

// Argument check for program
function arg_check($str)
{
	global $vars;
	return isset($vars['cmd']) && (strpos($vars['cmd'], $str) === 0);
}

// Encode page-name
function encode($key)
{
	return ($key == '') ? '' : strtoupper(bin2hex($key));
	// Equal to strtoupper(join('', unpack('H*0', $key)));
	// But PHP 4.3.10 says 'Warning: unpack(): Type H: outside of string in ...'
}

// Decode page name
function decode($key)
{
	return hex2bin($key);
}

// PHP 5.4.1 移行では組み込み関数
if ( ! function_exists('hex2bin'))
{
	// Inversion of bin2hex()
	function hex2bin($hex_string)
	{
		// preg_match : Avoid warning : pack(): Type H: illegal hex digit ...
		// (string)   : Always treat as string (not int etc). See BugTrack2/31
		return preg_match('/^[0-9a-f]+$/i', $hex_string) ?
			pack('H*', (string)$hex_string) : $hex_string;
	}
}

// Remove [[ ]] (brackets)
function strip_bracket($str)
{
	$match = array();
	if (preg_match('/^\[\[(.*)\]\]$/', $str, $match)) {
		return $match[1];
	} else {
		return $str;
	}
}

// Create list of pages
function page_list($pages, $cmd = 'read', $withfilename = FALSE)
{
	global $script, $list_index, $vars;
	global $pagereading_enable;
	$qm = get_qm();

	// ソートキーを決定する。 ' ' < '[a-zA-Z]' < 'zz'という前提。
	$symbol = ' ';
	$other = 'zz';

	$retval = '';

	if($pagereading_enable) {
		mb_regex_encoding(SOURCE_ENCODING);
		$readings = get_readings($pages);
	}

	$list = $matches = array();

	// Shrink URI for read
	if ($cmd == 'read') {
		$href = $script . '?';
	} else {
		$href = $script . '?cmd=' . $cmd . '&amp;page=';
	}

	foreach($pages as $file=>$page) {
		$r_page  = rawurlencode($page);
		$s_page  = htmlspecialchars($page, ENT_QUOTES);
		$passage = get_pg_passage($page);

		//customized by hokuken.com
		$t_page = get_page_title($s_page);
		$t_page = ($t_page == $s_page) ? '' : '('.$t_page.')';

		$str = '   <li><a href="' . $href . $r_page . '">' .
			$s_page . $t_page . '</a>' . $passage;

		if ($withfilename) {
			$s_file = htmlspecialchars($file);
			$str .= "\n" . '    <ul><li>' . $s_file . '</li></ul>' .
				"\n" . '   ';
		}
		$str .= '</li>';

		// WARNING: Japanese code hard-wired
		if($pagereading_enable) {
			if(mb_ereg('^([A-Za-z])', mb_convert_kana($page, 'a'), $matches)) {
				$head = $matches[1];
			} elseif (isset($readings[$page]) && mb_ereg('^([ァ-ヶ])', $readings[$page], $matches)) { // here
				$head = $matches[1];
			} elseif (mb_ereg('^[ -~]|[^ぁ-ん亜-熙]', $page)) { // and here
				$head = $symbol;
			} else {
				$head = $other;
			}
		} else {
			$head = (preg_match('/^([A-Za-z])/', $page, $matches)) ? $matches[1] :
				(preg_match('/^([ -~])/', $page, $matches) ? $symbol : $other);
		}

		$list[$head][$page] = $str;
	}
	ksort($list);

	$tmparr1 = isset($list[$symbol])? $list[$symbol]: null;
	unset($list[$symbol]);
	$list[$symbol] = $tmparr1;


	$cnt = 0;
	$arr_index = array();
	$retval .= '<ul>' . "\n";
	foreach ($list as $head => $ppages) {
		if (is_null($ppages)) {
			continue;
		}

		if ($head === $symbol) {
			$head = $qm->m['func']['list_symbol'];
		} else if ($head === $other) {
			$head = $qm->m['func']['list_other'];
		}

		if ($list_index) {
			++$cnt;
			$arr_index[] = '<a id="top_' . $cnt .
				'" href="#head_' . $cnt . '"><strong>' .
				$head . '</strong></a>';
			$retval .= ' <li><a id="head_' . $cnt . '" href="#top_' . $cnt .
				'"><strong>' . $head . '</strong></a>' . "\n" .
				'  <ul>' . "\n";
		}
		ksort($ppages);
		$retval .= join("\n", $ppages);
		if ($list_index)
			$retval .= "\n  </ul>\n </li>\n";
	}
	$retval .= '</ul>' . "\n";
	if ($list_index && $cnt > 0) {
		$top = array();
		while (! empty($arr_index))
			$top[] = join(' | ' . "\n", array_splice($arr_index, 0, 16)) . "\n";

		$retval = '<div id="top" style="text-align:center">' . "\n" .
			join('<br />', $top) . '</div>' . "\n" . $retval;
	}
	return $retval;
}

// Show text formatting rules
function catrule()
{
	global $rule_page;

	if (! is_page($rule_page)) {
		return '<p>Sorry, page \'' . htmlspecialchars($rule_page) .
			'\' unavailable.</p>';
	} else {
		return convert_html(get_source($rule_page));
	}
}

// Show (critical) error message
function die_message($msg)
{
	$title = $page = 'Runtime error';
	$body = <<<EOD
<h3>Runtime error</h3>
<strong>Error message : $msg</strong>
EOD;

	pkwk_common_headers();
//	if(defined('SKIN_FILE') && file_exists(SKIN_FILE) && is_readable(SKIN_FILE)) {
//		catbody($title, $page, $body);
//	} else {
		header('Content-Type: text/html; charset=utf-8');
		print <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title>$title</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
 </head>
 <body>
 $body
 </body>
</html>
EOD;
//	}
	exit;
}

// Have the time (as microtime)
function getmicrotime()
{
	list($usec, $sec) = explode(' ', microtime());
	return ((float)$sec + (float)$usec);
}

// Get the date
function get_date($format, $timestamp = NULL)
{
	$format = preg_replace('/(?<!\\\)T/',
		preg_replace('/(.)/', '\\\$1', ZONE), $format);

	$time = ZONETIME + (($timestamp !== NULL) ? $timestamp : UTIME);

	return date($format, $time);
}
// Get the date
function get_qblog_date($format, $page = '')
{
	global $qblog_page_re;
	$format = preg_replace('/(?<!\\\)T/',
		preg_replace('/(.)/', '\\\$1', ZONE), $format);

	if (preg_match($qblog_page_re, $page, $mts))
	{
		$y = $mts[1];
		$m = $mts[2];
		$d = $mts[3];
		$time = mktime(0, 0, 0, $m, $d, $y);
		return date($format, $time);
	}

	return FALSE;
}


// Format date string
function format_date($val, $paren = FALSE)
{
	global $date_format, $time_format, $weeklabels;

	$val += ZONETIME;

	$date = date($date_format, $val) .
		' (' . $weeklabels[date('w', $val)] . ') ' .
		date($time_format, $val);

	return $paren ? '(' . $date . ')' : $date;
}

// Get short string of the passage, 'N seconds/minutes/hours/days/years ago'
function get_passage($time, $paren = TRUE)
{
	static $units = array('m'=>60, 'h'=>24, 'd'=>1);

	$time = max(0, (UTIME - $time) / 60); // minutes

	foreach ($units as $unit=>$card) {
		if ($time < $card) break;
		$time /= $card;
	}
	$time = floor($time) . $unit;

	return $paren ? '(' . $time . ')' : $time;
}

// Hide <input type="(submit|button|image)"...>
function drop_submit($str)
{
	return preg_replace('/<input([^>]+)type="(submit|button|image)"/i',
		'<input$1type="hidden"', $str);
}

// Generate AutoLink patterns (thx to hirofummy)
function get_autolink_pattern(& $pages)
{
	global $WikiName, $autolink, $nowikiname;

	$config = new Config('AutoLink');
	$config->read();
	$ignorepages      = $config->get('IgnoreList');
	$forceignorepages = $config->get('ForceIgnoreList');
	unset($config);
	$auto_pages = array_merge($ignorepages, $forceignorepages);

	foreach ($pages as $page)
		if (preg_match('/^' . $WikiName . '$/', $page) ?
		    $nowikiname : strlen($page) >= $autolink)
			$auto_pages[] = $page;

	if (empty($auto_pages)) {
		$result = $result_a = $nowikiname ? '(?!)' : $WikiName;
	} else {
		$auto_pages = array_unique($auto_pages);
		sort($auto_pages, SORT_STRING);

		$auto_pages_a = array_values(preg_grep('/^[A-Z]+$/i', $auto_pages));
		$auto_pages   = array_values(array_diff($auto_pages,  $auto_pages_a));

		$result   = get_autolink_pattern_sub($auto_pages,   0, count($auto_pages),   0);
		$result_a = get_autolink_pattern_sub($auto_pages_a, 0, count($auto_pages_a), 0);
	}
	return array($result, $result_a, $forceignorepages);
}

function get_autolink_pattern_sub(& $pages, $start, $end, $pos)
{
	if ($end == 0) return '(?!)';

	$result = '';
	$count = $i = $j = 0;
	$x = (mb_strlen($pages[$start]) <= $pos);
	if ($x) ++$start;

	for ($i = $start; $i < $end; $i = $j) {
		$char = mb_substr($pages[$i], $pos, 1);
		for ($j = $i; $j < $end; $j++)
			if (mb_substr($pages[$j], $pos, 1) != $char) break;

		if ($i != $start) $result .= '|';
		if ($i >= ($j - 1)) {
			$result .= str_replace(' ', '\\ ', preg_quote(mb_substr($pages[$i], $pos), '/'));
		} else {
			$result .= str_replace(' ', '\\ ', preg_quote($char, '/')) .
				get_autolink_pattern_sub($pages, $i, $j, $pos + 1);
		}
		++$count;
	}
	if ($x || $count > 1) $result = '(?:' . $result . ')';
	if ($x)               $result .= '?';

	return $result;
}

// Get absolute-URI of this script
function get_script_uri($init_uri = '')
{
	global $script_directory_index;
	static $script;

	if ($init_uri == '') {
		// Get
		if (isset($script)) return $script;

		// Set automatically
		$msg     = 'get_script_uri() failed: Please set $script at INI_FILE manually';

		$script  = (SERVER_PORT == 443 ? 'https://' : 'http://'); // scheme
		$script .= SERVER_NAME;	// host
		$script .= ((SERVER_PORT == 80 || SERVER_PORT == 443) ? '' : ':' . SERVER_PORT);  // port

		// SCRIPT_NAME が'/'で始まっていない場合(cgiなど) REQUEST_URIを使ってみる
		$path    = SCRIPT_NAME;
		if ($path{0} != '/') {
			if (! isset($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI']{0} != '/')
				die_message($msg);

			// REQUEST_URIをパースし、path部分だけを取り出す
			$parse_url = parse_url($script . $_SERVER['REQUEST_URI']);
			if (! isset($parse_url['path']) || $parse_url['path']{0} != '/')
				die_message($msg);

			$path = $parse_url['path'];
		}
		$script .= $path;

		if (! is_url($script, TRUE) && php_sapi_name() == 'cgi')
			die_message($msg);
		unset($msg);

	} else {
		// Set manually
		if (isset($script)) die_message('$script: Already init');
		if (! is_url($init_uri, TRUE)) die_message('$script: Invalid URI');
		$script = $init_uri;
	}

	// Cut filename or not
	if (isset($script_directory_index)) {
		if (! file_exists($script_directory_index))
			die_message('Directory index file not found: ' .
				htmlspecialchars($script_directory_index));
		$matches = array();
		if (preg_match('#^(.+/)' . preg_quote($script_directory_index, '#') . '$#',
			$script, $matches)) $script = $matches[1];
	}

	return $script;
}

// Remove null(\0) bytes from variables
//
// NOTE: PHP had vulnerabilities that opens "hoge.php" via fopen("hoge.php\0.txt") etc.
// [PHP-users 12736] null byte attack
// http://ns1.php.gr.jp/pipermail/php-users/2003-January/012742.html
//
// 2003-05-16: magic quotes gpcの復元処理を統合
// 2003-05-21: 連想配列のキーはbinary safe
//
function input_filter($param)
{
	static $magic_quotes_gpc = NULL;
	if ($magic_quotes_gpc === NULL)
	    $magic_quotes_gpc = get_magic_quotes_gpc();

	if (is_array($param)) {
		return array_map('input_filter', $param);
	} else {
		$result = str_replace("\0", '', $param);
		if ($magic_quotes_gpc) $result = stripslashes($result);
		return $result;
	}
}

// Compat for 3rd party plugins. Remove this later
function sanitize($param) {
	return input_filter($param);
}

// Explode Comma-Separated Values to an array
function csv_explode($separator, $string)
{
	$retval = $matches = array();

	$_separator = preg_quote($separator, '/');
	if (! preg_match_all('/("[^"]*(?:""[^"]*)*"|[^' . $_separator . ']*)' .
	    $_separator . '/', $string . $separator, $matches))
		return array();

	foreach ($matches[1] as $str) {
		$len = strlen($str);
		if ($len > 1 && $str{0} == '"' && $str{$len - 1} == '"')
			$str = str_replace('""', '"', substr($str, 1, -1));
		$retval[] = $str;
	}
	return $retval;
}

// Implode an array with CSV data format (escape double quotes)
function csv_implode($glue, $pieces)
{
	$_glue = ($glue != '') ? '\\' . $glue{0} : '';
	$arr = array();
	foreach ($pieces as $str) {
		if (preg_match('/[' . '"' . "\n\r" . $_glue . ']/', $str))
			$str = '"' . str_replace('"', '""', $str) . '"';
		$arr[] = $str;
	}
	return join($glue, $arr);
}

//// Compat ////

// is_a --  Returns TRUE if the object is of this class or has this class as one of its parents
// (PHP 4 >= 4.2.0)
if (! function_exists('is_a')) {

	function is_a($class, $match)
	{
		if (empty($class)) return FALSE;

		$class = is_object($class) ? get_class($class) : $class;
		if (strtolower($class) == strtolower($match)) {
			return TRUE;
		} else {
			return is_a(get_parent_class($class), $match);	// Recurse
		}
	}
}

// array_fill -- Fill an array with values
// (PHP 4 >= 4.2.0)
if (! function_exists('array_fill')) {

	function array_fill($start_index, $num, $value)
	{
		$ret = array();
		while ($num-- > 0) $ret[$start_index++] = $value;
		return $ret;
	}
}

// md5_file -- Calculates the md5 hash of a given filename
// (PHP 4 >= 4.2.0)
if (! function_exists('md5_file')) {

	function md5_file($filename)
	{
		if (! file_exists($filename)) return FALSE;

		$fd = fopen($filename, 'rb');
		if ($fd === FALSE ) return FALSE;
		$data = fread($fd, filesize($filename));
		fclose($fd);
		return md5($data);
	}
}

// sha1 -- Compute SHA-1 hash
// (PHP 4 >= 4.3.0, PHP5)
if (! function_exists('sha1')) {
	if (extension_loaded('mhash')) {
		function sha1($str)
		{
			return bin2hex(mhash(MHASH_SHA1, $str));
		}
	}
}

//strip Google Adwords Code by HOKUKEN.COM 7/25 2007
// Remove Parameter of ad code, (exp. Google Adwords, Google Analytics, ... )
//
// 制限事項
//
// &以下は確実に削除できるが、WikiNameの位置にパラメータがセットされた場合、
// $adcodeで定義していない名前以外、WikiNameとして扱われる
// 基本的に、WikiNameまで入れたパスで、コードを作るようにして下さいな。
//
function strip_adcode($str)
{
	global $adcode, $defaultpage;

	$adcode[] = QHM_SESSION_NAME;

	$match = array();
	$match = explode("&", $str);
	$str = $match[0];

	if($match != ''){
		foreach($adcode as $var){
			$reg_str_2 = '/^' . $var . '=/';

			if (preg_match($reg_str_2, $str)){
				return $defaultpage;
			}
		}

		return $match[0];
	}
	else{  // example.com/?abc=123&cde=456... の場合
		return $str;
	}
}

//force output message function for some plugin
//
// Show (critical) error message
function force_output_message($title, $page, $body)
{
	pkwk_common_headers();
	if(defined('SKIN_FILE') && file_exists(SKIN_FILE) && is_readable(SKIN_FILE)) {
		catbody($title, $page, $body);
	} else {
		header('Content-Type: text/html; charset=utf-8');
		print <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title>$title</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
 </head>
 <body>
 $body
 </body>
</html>
EOD;
	}
	exit;
}

if( !function_exists('file_put_contents') ){
	function file_put_contents($filename, $data, $flag=FALSE )
	{
		$mode = $flag ? 'a' : 'w';
		$fp = fopen($filename, $mode); if($fp===FALSE) return FALSE;
		flock($fp, LOCK_EX);
		fputs($fp, $data);
		flock($fp, LOCK_UN);
		fclose($fp);
		return TRUE;
	}
}

//For qhm template engine & qhm cache engine
function qhm_output_dtd($pkwk_dtd, $content_charset, $encode){

	// Output HTTP headers
	pkwk_common_headers();
	header('Cache-control: no-cache');
	header('Pragma: no-cache');
	header('Content-Type: text/html; charset=' . $encode);

	// Output HTML DTD, <html>, and receive content-type
	$meta_content_type = pkwk_output_dtd($pkwk_dtd);

	if( $content_charset != $encode)
	{
		$meta_content_type = str_replace($content_charset, $encode, $meta_content_type);
	}

	return $meta_content_type;
}

function output_site_close_message($site_name, $login_url)
{
	global $qhm_adminmenu;

	$qhm_sign = ($qhm_adminmenu < 2) ? '<a href="'. h($login_url) . '">HAIK</a>' : 'HAIK';

	pkwk_common_headers();
	$qm = get_qm();
	$closetitle = $qm->m['func']['close_title'];
	$closemsg = $qm->m['func']['close_message'];

	header('Content-Type: text/html; charset=utf-8');
	print <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title>$closetitle : $site_name (Close this site)</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta name="GENERATOR" content="Quick Homepage Maker" />
  <style>
  body{
  	background-color:#ccc;
  	color:#333;
  	font-family: "Arial", "San-serif";
  	font-size: 10pt;
  	line-height:1.5em;
  }
  #wrapper {
  	background-color:#fff;
  	padding:20px;
  	margin:20px auto;
  	width:500px;
  	border: 1px solid #aaa;
  }
  #wrapper h1{
  	font-size:12pt;
  }
  #login{
  		color:#999;
  		font-size:11px;
  		text-align:right;
  }
  #login a{
  		color:#999;
		text-decoration:none;
  }
  </style>
 </head>
 <body>
 <div id="wrapper">
 <h1>$closetitle</h1>
 <p>$closemsg</p>
 <br />

 <h1>Closed</h1>
 <p>Sorry, but this site is now closed</p>

 <div id="login">powered by {$qhm_sign}</div>
 </div>
 </body>
</html>
EOD;

	exit;
}

function wikiescape($string){
	//今のところ、#html{{ と、 #html2 だけ
	$ret = '';
	$lines = explode("\n", $string);
	foreach($lines as $line){
		$ret .= preg_replace('/^(#)(html|beforescript|style|lastscript)/i', "$1 $2", $line) ."\n";
	}

	return $ret;
}

function get_page_title($pagename, $lines=10){
	if (!file_exists(get_filename($pagename))) {
		return '';
	}
	$fp = fopen(get_filename($pagename), 'r');
	flock($fp, LOCK_SH);

	$title = $pagename;
	for($i=0; $i<$lines; $i++){
		$str = fgets($fp);
		if( preg_match('/^TITLE:(.*)/', $str, $ms) ){
			$title = $ms[1];
			break;
		}
	}

	fclose($fp);

	return h($title);
}

function get_qblog_category($page, $lines = 10)
{
	global $qblog_default_cat;

	if ( ! file_exists(get_filename($page)))
	{
		return '';
	}

	$data = get_qblog_post_data($page);
	$cat = isset($data['category']) ? $data['category'] : $qblog_default_cat;

	return $cat;
}

/**
 * ブログカテゴリー一覧を返す。
 * @return asoc {cat_name : num, ...}
 */
function get_qblog_categories()
{
	$cat_list = explode("\n", file_get_contents(CACHEQBLOG_DIR . 'qblog_categories.dat'));
	$categories = array();
	foreach ($cat_list as $cat_data)
	{
		list($cat_name, $num) = explode("\t", $cat_data);
		$categories[$cat_name] = $num;
	}
	return $categories;
}

function get_qblog_post_data($page)
{
	$datafile = CACHEQBLOG_DIR . encode($page) . '.qbp.dat';
	if ($page == '' OR ! file_exists($datafile))
	{
		return FALSE;
	}

	return unserialize(file_get_contents($datafile));
}

//指定した送信先へPing送信先
function send_qblog_ping()
{
	global $script, $qblog_title, $page_title, $qblog_defaultpage;
	global $qblog_ping, $qblog_enable_ping;

	if ( ! $qblog_enable_ping)
	{
		return FALSE;
	}

	$site_name = $qblog_title .' - '. $page_title;
	$site_url = $script . '?' . $qblog_defaultpage;

	//create XML
	$xml = '<'.'?xml version="1.0"?'.'>
<methodCall>
	<methodName>weblogUpdates.ping</methodName>
	<params>
		<param>
			<value>'. h($site_name) .'</value>
		</param>
		<param>
			<value>'. h($site_url) .'</value>
		</param>
	</params>
</methodCall>';

	$xml = str_replace("\n", "\r\n", $xml);

	$ping_urls = explode("\n", $qblog_ping);

	foreach ($ping_urls as $url)
	{
		$url = trim($url);
		if ($url == '') continue;

		$arr = parse_url($url);
		$host = $arr["host"];
		$path = $arr["path"];

		//create request
		$req =
		"POST $path HTTP/1.0\r\n"
		. "Host: $host\r\n"
		. "Content-Type: text/xml\r\n"
		. "Content-Length: ". strlen($xml) . "\r\n"
		. "\r\n"
		. $xml;

		//send ping
		$sock = @fsockopen($host, 80, $errno, $errstr, 2.0);
		$result = "";
		if ($sock)
		{
			fputs($sock, $req);
			while (!feof($sock))
			{
				$result .= fread($sock, 1024);
			}
		}


	}

	return TRUE;
}

function sent_qblog_comment_notice($page, $data)
{
	global $script, $qblog_comment_check, $admin_email;
	global $qblog_title;
	$r_page = rawurlencode($page);

	//管理者メルアド必須
	if (trim($admin_email) === '')
	{
		return FALSE;
	}

	require(LIB_DIR . 'simplemail.php');
	$smail = new SimpleMail();

	$data['id'] = 'qbcomment_' . $data['id'];//for #hash
	$data['url'] = $script . '?' . $r_page;
	$data['body'] = $data['msg'];
	$data['datetime'] = date('Y年m月d日 H時i分s秒');

	$ptns = $smail->mksearch($data);
	$rpls = $smail->mkreplace($data);

	$ptns[] = '<%header%>';
	$rpls[] = 'ブログにコメントがありました';

	$ptns[] = '<%footer%>';
	$rpls[] = $qblog_comment_check ? 'よろしければ承認してください。' : '';

	$ptns[] = '<%blog_title%>';
	$rpls[] = $qblog_title;

	$subject_fmt = '[<%blog_title%>] ブログに新しいコメントがあります。';
	$body_fmt = <<< EOM
<%header%>

ブログ名：<%blog_title%>
記事：<%url%>

日時： <%datetime%>
お名前： <%name%>
タイトル： <%title%>

コメント：
<%body%>


<%footer%>
----
<%url%>#<%id%>

EOM;

	//設定
	$notifier = 'QHM';
	$smail->set_params($notifier, $admin_email);
	$smail->set_to('', $admin_email);
	$smail->set_subject($subject_fmt);

	//管理者へ送信
	$smail->replace_send($ptns, $rpls, $body_fmt);
	//TODO: お名前サーバーや、GoogleAppsへ対応

	return TRUE;
}


/**
 * Redirect to URL or Page
 */
function redirect($url = '', $msg = '', $refresh_sec = 2)
{
	global $script, $style_name, $vars;
	$qt = get_qt();

	if (is_url($url))
	{
		//
	}
	else if (is_page($url))
	{
		$url = $script . '?' . $url;
	}
	//デフォルトページ
	else
	{
		$url = $script;
	}

	if ($msg !== '')
	{
		$style_name = '../';
		$title = array_shift(explode("\n", $msg));
		$head = '<meta http-equiv="refresh" content="'. h($refresh_sec) .';URL='. h($url) .'" />';
		$qt->appendv('beforescript', $head);

		$vars['disable_toolmenu'] = TRUE;

		$body = convert_html('
* '. $msg. '

'. $refresh_sec .'秒後に移動します。
移動しない場合は[[ここをクリック>'. $url .']]
');
		force_output_message($title, '', $body);
	}
	else
	{
		header('Location: ' . $url);
	}
	exit;
}

function h($string, $flags = ENT_QUOTES){
	return htmlspecialchars($string, $flags);
}

function h_decode($string){
	return htmlspecialchars_decode($string, ENT_QUOTES);
}

function qhm_get_script_path() {
	$tmp_script = '';
	preg_match("/(.*?php)/", basename( $_SERVER['PHP_SELF'] ), $ms);
	$tmp_script = $ms[1];
	return $tmp_script;
}

/**
 * removes entities &lt; &gt; &amp; and eventually &quot; from HTML string
 *
 */
if (!function_exists("htmlspecialchars_decode")) {
   if (!defined("ENT_COMPAT")) { define("ENT_COMPAT", 2); }
   if (!defined("ENT_QUOTES")) { define("ENT_QUOTES", 3); }
   if (!defined("ENT_NOQUOTES")) { define("ENT_NOQUOTES", 0); }
   function htmlspecialchars_decode($string, $quotes=2) {
      $d = $quotes & ENT_COMPAT;
      $s = $quotes & ENT_QUOTES;
      return str_replace(
         array("&lt;", "&gt;", ($s ? "&quot;" : "&.-;"), ($d ? "&#039;" : "&.-;"), "&amp;"),
         array("<",    ">",    "'",                      "\"",                     "&"),
         $string
      );
   }
}

/**
* SWFUを持っているか（有効か？）をチェックして返す
*/
function has_swfu(){
	if( file_exists(SWFU_TEXTSQL_PATH)
	   	&&  file_exists(SWFU_IMAGEDB_PATH)
	   	&&  is_writable(SWFU_IMAGEDB_PATH)
	   	&&  is_writable(SWFU_IMAGE_DIR)
	){
		return true;
	}
	else{
		return false;
	}
}

/**
* 拡張子から、mime-typeを返す
*/
function get_mimetype($fname){
	$ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));

	switch($ext){

		case 'txt' : return 'text/plain';
		case 'csv' : return 'text/csv';
		case 'html':
		case 'htm' : return 'text/html';

		//
		case 'pdf' : return 'application/pdf';
		case 'css' : return 'text/css';
		case 'js'  : return 'text/javascript';

		//image
		case 'jpg' :
		case 'jpeg': return 'image/jpeg';
		case 'png' : return 'image/png';
		case 'gif' : return 'image/gif';
		case 'bmp' : return 'image/bmp';

		//av
		case 'mp3' : return 'audio/mpeg';
		case 'm4a' : return 'audio/mp4';
		case 'wav' : return 'audio/x-wav';
		case 'mpg' :
		case 'mpeg': return 'video/mpeg';
		case 'wmv' : return 'video/x-ms-wmv';
		case 'swf' : return 'application/x-shockwave-flash';

		//archives
		case 'zip' : return 'application/zip';
		case 'lha' :
		case 'lzh' : return 'application/x-lzh';
		case 'tar' :
		case 'tgz' :
		case 'gz'  : return 'application/x-tar';


		//office files
		case 'doc' :
		case 'dot' : return 'application/msword';
		case 'docx': return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
		case 'xls' :
		case 'xlt' :
		case 'xla' : return 'application/vnd.ms-excel';
		case 'xlsx': return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
		case 'ppt' :
		case 'pot' :
		case 'pps' :
		case 'ppa' : return 'application/vnd.ms-powerpoint';
		case 'pptx': return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';

	}

	return 'application/octet-stream';


}

/**
 * Converts PHP variable or array into a "JSON" (JavaScript value expression
 * or "object notation") string.
 *
 * @compat
 *    Output seems identical to PECL versions. "Only" 20x slower than PECL version.
 * @bugs
 *    Doesn't take care with unicode too much - leaves UTF-8 sequences alone.
 *
 * @param  $var mixed  PHP variable/array/object
 * @return string      transformed into JSON equivalent
 */
if (!function_exists("json_encode")) {
   function json_encode($var, /*emu_args*/$obj=FALSE) {

      #-- prepare JSON string
      $json = "";

      #-- add array entries
      if (is_array($var) || ($obj=is_object($var))) {

         #-- check if array is associative
         if (!$obj) foreach ((array)$var as $i=>$v) {
            if (!is_int($i)) {
               $obj = 1;
               break;
            }
         }

         #-- concat invidual entries
         foreach ((array)$var as $i=>$v) {
            $json .= ($json ? "," : "")    // comma separators
                   . ($obj ? ("\"$i\":") : "")   // assoc prefix
                   . (json_encode($v));    // value
         }

         #-- enclose into braces or brackets
         $json = $obj ? "{".$json."}" : "[".$json."]";
      }

      #-- strings need some care
      elseif (is_string($var)) {
         if (!utf8_decode($var)) {
            $var = utf8_encode($var);
         }
         $var = str_replace(array("\\", "\"", "/", "\b", "\f", "\n", "\r", "\t"), array("\\\\", "\\\"", "\\/", "\\b", "\\f", "\\n", "\\r", "\\t"), $var);
         $var = json_encode_string($var);
         $json = '"' . $var . '"';
         //@COMPAT: for fully-fully-compliance   $var = preg_replace("/[\000-\037]/", "", $var);
      }

      #-- basic types
      elseif (is_bool($var)) {
         $json = $var ? "true" : "false";
      }
      elseif ($var === NULL) {
         $json = "null";
      }
      elseif (is_int($var) || is_float($var)) {
         $json = "$var";
      }

      #-- something went wrong
      else {
         trigger_error("json_encode: don't know what a '" .gettype($var). "' is.", E_USER_ERROR);
      }

      #-- done
      return($json);
   }
}


function json_encode_string($in_str) {
	//fb($in_str, "json_encode_string");
	$debug = 'before:'."\n" . $in_str;
	mb_internal_encoding("UTF-8");
	$convmap = array(0x80, 0xFFFF, 0, 0xFFFF);
	$str = "";
	for($i=mb_strlen($in_str)-1; $i>=0; $i--)
	{
		$mb_char = mb_substr($in_str, $i, 1);
		if(mb_ereg("&#(\\d+);", mb_encode_numericentity($mb_char, $convmap, "UTF-8"), $match))
		{
			$str = sprintf("\\u%04x", $match[1]) . $str;
		}
		else
		{
			$str = $mb_char . $str;
		}
	}
	$debug .= "\n\nafter:\n" . $str;
	//file_put_contents("debug.txt", $debug);
	return $str;
}

if ( ! function_exists('json_decode'))
{
   function json_decode($json, $assoc=FALSE, /*emu_args*/$n=0,$state=0,$waitfor=0) {

      #-- result var
      $val = NULL;
      static $lang_eq = array("true" => TRUE, "false" => FALSE, "null" => NULL);
      static $str_eq = array("n"=>"\012", "r"=>"\015", "\\"=>"\\", '"'=>'"', "f"=>"\f", "b"=>"\b", "t"=>"\t", "/"=>"/");

      #-- flat char-wise parsing
      for (/*n*/; $n<strlen($json); /*n*/) {
         $c = $json[$n];

         #-= in-string
         if ($state==='"') {

            if ($c == '\\') {
               $c = $json[++$n];
               // simple C escapes
               if (isset($str_eq[$c])) {
                  $val .= $str_eq[$c];
               }

               // here we transform \uXXXX Unicode (always 4 nibbles) references to UTF-8
               elseif ($c == "u") {
                  // read just 16bit (therefore value can't be negative)
                  $hex = hexdec( substr($json, $n+1, 4) );
                  $n += 4;
                  // Unicode ranges
                  if ($hex < 0x80) {    // plain ASCII character
                     $val .= chr($hex);
                  }
                  elseif ($hex < 0x800) {   // 110xxxxx 10xxxxxx
                     $val .= chr(0xC0 + $hex>>6) . chr(0x80 + $hex&63);
                  }
                  elseif ($hex <= 0xFFFF) { // 1110xxxx 10xxxxxx 10xxxxxx
                     $val .= chr(0xE0 + $hex>>12) . chr(0x80 + ($hex>>6)&63) . chr(0x80 + $hex&63);
                  }
                  // other ranges, like 0x1FFFFF=0xF0, 0x3FFFFFF=0xF8 and 0x7FFFFFFF=0xFC do not apply
               }

               // no escape, just a redundant backslash
               //@COMPAT: we could throw an exception here
               else {
                  $val .= "\\" . $c;
               }
            }

            // end of string
            elseif ($c == '"') {
               $state = 0;
            }

            // yeeha! a single character found!!!!1!
            else/*if (ord($c) >= 32)*/ { //@COMPAT: specialchars check - but native json doesn't do it?
               $val .= $c;
            }
         }

         #-> end of sub-call (array/object)
         elseif ($waitfor && (strpos($waitfor, $c) !== false)) {
            return array($val, $n);  // return current value and state
         }

         #-= in-array
         elseif ($state===']') {
            list($v, $n) = json_decode($json, 0, $n, 0, ",]");
            $val[] = $v;
            if ($json[$n] == "]") { return array($val, $n); }
         }

         #-= in-object
         elseif ($state==='}') {
            list($i, $n) = json_decode($json, 0, $n, 0, ":");   // this allowed non-string indicies
            list($v, $n) = json_decode($json, 0, $n+1, 0, ",}");
            $val[$i] = $v;
            if ($json[$n] == "}") { return array($val, $n); }
         }

         #-- looking for next item (0)
         else {

            #-> whitespace
            if (preg_match("/\s/", $c)) {
               // skip
            }

            #-> string begin
            elseif ($c == '"') {
               $state = '"';
            }

            #-> object
            elseif ($c == "{") {
               list($val, $n) = json_decode($json, $assoc, $n+1, '}', "}");
//               if ($val && $n && !$assoc) {
               if ($val && $n && $assoc === FALSE) {
                  $obj = new stdClass();
                  foreach ($val as $i=>$v) {
                     $obj->{$i} = $v;
                  }
                  $val = $obj;
                  unset($obj);
               }
            }
            #-> array
            elseif ($c == "[") {
               list($val, $n) = json_decode($json, $assoc, $n+1, ']', "]");
            }

            #-> comment
            elseif (($c == "/") && ($json[$n+1]=="*")) {
               // just find end, skip over
               ($n = strpos($json, "*/", $n+1)) or ($n = strlen($json));
            }

            #-> numbers
            elseif (preg_match("#^(-?\d+(?:\.\d+)?)(?:[eE]([-+]?\d+))?#", substr($json, $n), $uu)) {
               $val = $uu[1];
               $n += strlen($uu[0]) - 1;
               if (strpos($val, ".")) {  // float
                  $val = (float)$val;
               }
               elseif ($val[0] == "0") {  // oct
                  $val = octdec($val);
               }
               else {
                  $val = (int)$val;
               }
               // exponent?
               if (isset($uu[2])) {
                  $val *= pow(10, (int)$uu[2]);
               }
            }

            #-> boolean or null
            elseif (preg_match("#^(true|false|null)\b#", substr($json, $n), $uu)) {
               $val = $lang_eq[$uu[1]];
               $n += strlen($uu[1]) - 1;
            }

            #-- parsing error
            else {
               // PHPs native json_decode() breaks here usually and QUIETLY
              trigger_error("json_decode: error parsing '$c' at position $n", E_USER_WARNING);
               return $waitfor ? array(NULL, 1<<30) : NULL;
            }

         }//state

         #-- next char
         if ($n === NULL) { return NULL; }
         $n++;
      }//for

      #-- final result
      return ($val);
   }
}

if (!function_exists("gzopen") && function_exists("gzopen64")) {
	function gzopen($file, $mode) {
		return gzopen64($file, $mode);
	}
}

if ( ! function_exists('hv'))
{
    /**
     * Print Haik-skin variable
     *
     * @param string $name name of custom-skin config
     * @param boolean $return when true return value without print
     * @return void|mixed when $return is true then return value (escaped)
     */
    function hv($name, $return = false)
    {
        global $style_name;
        $skin_custom_vars = get_skin_custom_vars($style_name);

        $value = isset($skin_custom_vars[$name]) ? $skin_custom_vars[$name] : '';
        if ($return)
        {
            return $value;
        }
        else
        {
            echo h($value, ENT_NOQUOTES);
        }
    }
}

if ( ! function_exists('read_skin_config'))
{
    function read_skin_config($style_name)
    {
        static $saved_config = array();
        if (isset($saved_config[$style_name]))
        {
            return $saved_config[$style_name];
        }

        $config = array();
        $skin_dir = SKIN_DIR . $style_name;
        $config_path = $skin_dir . '/config.php';
        if (is_dir($skin_dir) && file_exists($config_path))
        {
            $config = include($config_path);
            $saved_config[$style_name] = $config;
        }

        return $config;
    }

}

class QHM_SkinCustomVariables {
	private static $saved_config = array();

	public static function load($style_name)
	{
		$skin_custom_vars = array();

		$style_config = read_skin_config($style_name);
		foreach ($style_config['custom_options'] as $name => $value)
		{
			$skin_custom_vars[$name] = $value['value'];
		}

		$custom_skin_file = CACHE_DIR.'custom_skin.'.$style_name.'.dat';
		if (file_exists($custom_skin_file))
		{
			$custom_skin_data = file_get_contents($custom_skin_file);
			$custom_skin_data = unserialize($custom_skin_data);

			if ($custom_skin_data)
			{
				foreach($custom_skin_data as $key => $value)
				{
					if (isset($skin_custom_vars[$key]))
					{
						$skin_custom_vars[$key] = trim($value);
					}
				}
			}
		}

		return $skin_custom_vars;
	}

	public static function get($style_name)
	{
		if (isset(self::$saved_config[$style_name]))
		{
			return self::$saved_config[$style_name];
		}

		$skin_custom_vars = self::load($style_name);

		self::$saved_config[$style_name] = $skin_custom_vars;

		return $skin_custom_vars;
	}

	public static function set($style_name, $key, $value = null)
	{
		$config = self::get($style_name);
		if (!empty($config) && isset($config[$key]))
		{
			// reset
			if ($value !== null)
			{
				$config[$key] = $value;
				self::$saved_config[$style_name] = $config;
			}
			else {
				$default_config = self::load($style_name);
				self::$saved_config[$style_name][$key] = $default_config[$key];
			}
		}
	}
}

if ( ! function_exists('get_skin_custom_vars'))
{
	function get_skin_custom_vars($style_name)
	{
		return QHM_SkinCustomVariables::get($style_name);
	}
}


if ( ! function_exists('make_custom_css'))
{
    function make_custom_css($style_name)
    {
        $config = read_skin_config($style_name);
        $css = '';
        $skin_dir = SKIN_DIR . $style_name;
        $custom_file_path = $skin_dir . '/custom.css.php';
        if (file_exists($custom_file_path))
        {
            ob_start();
            include($custom_file_path);
            $css = ob_get_clean();
        }
        return $css;
    }
}

if ( ! function_exists('get_file_path'))
{
    function get_file_path($filename)
    {
        if ($filename === '') return '';

        if (is_url($filename, FALSE, TRUE)) return $filename;

        if (file_exists(SWFU_IMAGE_DIR . $filename))
        {
            return SWFU_IMAGE_DIR . $filename;
        }
        else if (file_exists(IMAGE_DIR . $filename))
        {
            return IMAGE_DIR . $filename;
        }

        return $filename;
    }
}

if ( ! function_exists('is_bootstrap_skin'))
{
    function is_bootstrap_skin($specified_style_name = '')
    {
        global $style_name;
        if ($specified_style_name === '')
        {
            $specified_style_name = $style_name;
        }
        $style_config = read_skin_config($specified_style_name);

        return isset($style_config['bootstrap']) ? $style_config['bootstrap'] : false;
    }
}

function get_bs_style($color, $type = 'btn')
{
    $class = '';
    $color = strtolower($color);

    $type = strtolower($type);
    $type = ($type == 'button') ? 'btn' : $type;

    switch ($color)
    {
    case 'primary':
    case 'info':
    case 'success':
    case 'danger':
    case 'warning':
      	$class = $type . '-' .$color;
      	break;

    case 'blue':
    case 'skyblue':
        $class = $type . (($type == 'btn' && $color == 'blue') ? '-primary' : '-info');
        break;

    case 'green':
        $class = $type . '-success';
        break;

    case 'red':
    case 'error':
        if ($type == 'btn' OR $type == 'progress' OR $type == 'alert')
        {
            $class = $type . '-danger';
        }
        else if ($type == 'label' OR $type == 'badge')
        {
            $class = $type . '-important';
        }
        break;

    case 'orange':
    case 'yellow':
        if ($type != 'alert')
        {
            $class = $type . '-warning';
        }
        break;

    case 'link':
        if ($type == 'btn')
        {
            $class = $type . '-link';
        }
        break;

    case 'theme':
        $class = $type . '-theme';
        break;

    case 'default':
    case 'normal':
        $class = $type . '-default';
        break;
    }

    $class = ($class != '') ? ($type . ' ' . $class) : $type;

    return $class;
}

if (!function_exists('str_getcsv'))
{
    function str_getcsv($input, $delimiter = ',', $enclosure = '"', $escape = '\\', $eol = '\n')
    {
        if (is_string($input) && !empty($input))
        {
            $output = array();
            $tmp    = preg_split("/".$eol."/",$input);
            if (is_array($tmp) && !empty($tmp))
            {
                while (list($line_num, $line) = each($tmp))
                {
                    if (preg_match("/".$escape.$enclosure."/",$line))
                    {
                        while ($strlen = strlen($line))
                        {
                            $pos_delimiter       = strpos($line,$delimiter);
                            $pos_enclosure_start = strpos($line,$enclosure);
                            if (
                                is_int($pos_delimiter) && is_int($pos_enclosure_start)
                                && ($pos_enclosure_start < $pos_delimiter)
                                )
                                {
                                $enclosed_str = substr($line,1);
                                $pos_enclosure_end = strpos($enclosed_str,$enclosure);
                                $enclosed_str = substr($enclosed_str,0,$pos_enclosure_end);
                                $output[$line_num][] = $enclosed_str;
                                $offset = $pos_enclosure_end+3;
                            }
                            else
                            {
                                if (empty($pos_delimiter) && empty($pos_enclosure_start))
                                {
                                    $output[$line_num][] = substr($line,0);
                                    $offset = strlen($line);
                                }
                                else
                                {
                                    $output[$line_num][] = substr($line,0,$pos_delimiter);
                                    $offset = (
                                                !empty($pos_enclosure_start)
                                                && ($pos_enclosure_start < $pos_delimiter)
                                                )
                                                ?$pos_enclosure_start
                                                :$pos_delimiter+1;
                                }
                            }
                            $line = substr($line,$offset);
                        }
                    }
                    else
                    {
                        $line = preg_split("/".$delimiter."/",$line);
                        /*
                         * Validating against pesky extra line breaks creating false rows.
                         */
                        if (is_array($line) && !empty($line[0]))
                        {
                            $output[$line_num] = $line;
                        }
                    }
                }

                if (count($output) === 1)
                {
                    return $output[0];
                }

                return $output;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
}

function get_qhm_option($key = NULL)
{
	static $options = array();
	if (empty($options))
	{
		$option_statements = explode(';', QHM_OPTIONS);
		foreach ($option_statements as $statement)
		{
			list($_key, $_value) = explode('=', $statement, 2);
			$_key = trim($_key);
			$_value = trim($_value);
			// type cast
			if ($_value === 'true') $_value = true;
			else if ($_value === 'false') $_value = false;
			else if (is_numeric($_value)) $_value = (float)$_value;

			$options[$_key] = $_value;
		}
	}
	if ($key === NULL)
	{
		return $options;
	}
	else if (array_key_exists($key, $options))
	{
		return $options[$key];
	}
	return NULL;
}

/**
 * XSS プロテクションを無効化するレスポンスヘッダーを出力する。
 */
function cancel_xss_protection() {
    header('X-XSS-Protection: 0');
}

/**
 * 生の JavaScript を <script></script> で囲む。
 *
 * @param [String] $js JavaScript
 * @param [String] $delimiter タグの前後に入れる区切り文字。デフォルトで改行
 */
function wrap_script_tag($js, $delimiter = "\n") {
	$lines = array();
	$lines[] = '<script>';
	$lines[] = $js;
	$lines[] = '</script>';
	return join($delimiter, $lines) . $delimiter;
}
