<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: file.php,v 1.72 2006/06/11 14:42:09 henoheno Exp $
// Copyright (C)
//   2002-2006 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// File related functions

// RecentChanges
define('PKWK_MAXSHOW_ALLOWANCE', 10);
define('PKWK_MAXSHOW_CACHE', 'recent.dat');
define('QHM_TINYURL_TABLE', 'tinyurl.dat');
define('QHM_LASTMOD', 'qhm_lastmod.dat');

define('QBLOG_MAX_RECENT_COMMENTS', 50);

// AutoLink
define('PKWK_AUTOLINK_REGEX_CACHE', 'autolink.dat');

// Get source(wiki text) data of the page
function get_source($page = NULL, $lock = TRUE, $join = FALSE)
{
	$result = $join ? '' : array();

	if (is_page($page)) {
		$path  = get_filename($page);

		if ($lock) {
			$fp = @fopen($path, 'r');
			if ($fp == FALSE) return $result;
			flock($fp, LOCK_SH);
		}

		if ($join) {
			// Returns a value
			$result = str_replace("\r", '', fread($fp, filesize($path)));
		} else {
			// Returns an array
			// Removing line-feeds: Because file() doesn't remove them.
			$result = str_replace("\r", '', file($path));
		}

		if ($lock) {
			flock($fp, LOCK_UN);
			@fclose($fp);
		}
	}

	return $result;
}

// Get last-modified filetime of the page
function get_filetime($page)
{
	return is_page($page) ? filemtime(get_filename($page)) - LOCALZONE : 0;
}

// Get physical file name of the page
function get_filename($page)
{
	return DATA_DIR . encode($page) . '.txt';
}

// Put a data(wiki text) into a physical file(diff, backup, text)
function page_write($page, $postdata, $notimestamp = FALSE)
{
	global $trackback;

	if (PKWK_READONLY) return; // Do nothing

	$postdata = make_str_rules($postdata);

	// Create and write diff
	$oldpostdata = is_page($page) ? join('', get_source($page)) : '';
	$diffdata    = do_diff($oldpostdata, $postdata);
	file_write(DIFF_DIR, $page, $diffdata);

	// Create backup
	make_backup($page, $postdata == ''); // Is $postdata null?

	// Create wiki text
	file_write(DATA_DIR, $page, $postdata, $notimestamp);

	if ($trackback) {
		// TrackBack Ping
		$_diff = explode("\n", $diffdata);
		$plus  = join("\n", preg_replace('/^\+/', '', preg_grep('/^\+/', $_diff)));
		$minus = join("\n", preg_replace('/^-/',  '', preg_grep('/^-/',  $_diff)));
		tb_send($page, $plus, $minus);
	}

	links_update($page);
}

// Modify original text with user-defined / system-defined rules
function make_str_rules($source)
{
	global $str_rules, $fixed_heading_anchor;

	$lines = explode("\n", $source);
	$count = count($lines);

	$modify    = TRUE;
	$multiline = 0;
	$matches   = array();
	for ($i = 0; $i < $count; $i++) {
		$line = & $lines[$i]; // Modify directly

		// Ignore null string and preformatted texts
		if ($line == '' || $line{0} == ' ' || $line{0} == "\t") continue;

		// Modify this line?
		if ($modify) {
			if (! PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK &&
			    $multiline == 0 &&
			    preg_match('/^#[^{]*(\{\{+)\s*$/', $line, $matches)) {
			    	// Multiline convert plugin start
				$modify    = FALSE;
				$multiline = strlen($matches[1]); // Set specific number
			}
		} else {
			if (! PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK &&
			    $multiline != 0 &&
			    preg_match('/^\}{' . $multiline . '}\s*$/', $line)) {
			    	// Multiline convert plugin end
				$modify    = TRUE;
				$multiline = 0;
			}
		}
		if ($modify === FALSE) continue;

		// Replace with $str_rules
		foreach ($str_rules as $pattern => $replacement)
			$line = preg_replace('/' . $pattern . '/', $replacement, $line);
		
		// Adding fixed anchor into headings
		if ($fixed_heading_anchor &&
		    preg_match('/^((?:\*{1,3}|!).*?)(?:\[#([A-Za-z][\w-]*)\]\s*)?$/', $line, $matches) &&
		    (! isset($matches[2]) || $matches[2] == '')) {
			// Generate unique id
			$anchor = generate_fixed_heading_anchor_id($matches[1]);
			$line = rtrim($matches[1]) . ' [#' . $anchor . ']';
		}
	}

	// Multiline part has no stopper
	if (! PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK &&
	    $modify === FALSE && $multiline != 0)
		$lines[] = str_repeat('}', $multiline);

	return implode("\n", $lines);
}

// Generate ID
function generate_fixed_heading_anchor_id($seed)
{
	// A random alphabetic letter + 7 letters of random strings from md()
	return chr(mt_rand(ord('a'), ord('z'))) .
		substr(md5(uniqid(substr($seed, 0, 100), TRUE)),
		mt_rand(0, 24), 7);
}

// Read top N lines as an array
// (Use PHP file() function if you want to get ALL lines)
function file_head($file, $count = 1, $lock = TRUE, $buffer = 8192)
{
	$array = array();

	$fp = @fopen($file, 'r');
	if ($fp === FALSE) return FALSE;
	set_file_buffer($fp, 0);
	if ($lock) flock($fp, LOCK_SH);
	rewind($fp);
	$index = 0;
	while (! feof($fp)) {
		$line = fgets($fp, $buffer);
		if ($line != FALSE) $array[] = $line;
		if (++$index >= $count) break;
	}
	if ($lock) flock($fp, LOCK_UN);
	if (! fclose($fp)) return FALSE;

	return $array;
}

// Read top N lines as an array
// (Use PHP file() function if you want to get ALL lines)
function file_slice($file, $offset = 0, $count = 1, $lock = TRUE, $buffer = 8192)
{
	$array = array();

	$fp = @fopen($file, 'r');
	if ($fp === FALSE) return FALSE;
	set_file_buffer($fp, 0);
	if ($lock) flock($fp, LOCK_SH);
	rewind($fp);
	$index = 0;
	while (! feof($fp)) {
		$line = fgets($fp, $buffer);

		//index がoffset 未満の時はcontinue
		if ($index < $offset)		
		{
			$index++;
			continue;
		}
		else
		{
			//index がoffset 以上の時は配列に格納
			if ($line)
				$array[] = $line;
		}

		//index+1 がcount+offset 以上の時は、break
		if (++$index >= $count+$offset) break;
	}
	if ($lock) flock($fp, LOCK_UN);
	if (! fclose($fp)) return FALSE;

	return $array;
}


// Output to a file
function file_write($dir, $page, $str, $notimestamp = FALSE)
{
	global $notify, $notify_diff_only, $notify_subject;
	global $whatsdeleted, $maxshow_deleted;
	global $qblog_page_re;
	$qm = get_qm();

	if (PKWK_READONLY) return; // Do nothing
	if ($dir != DATA_DIR && $dir != DIFF_DIR) die($qm->m['file']['err_invalid_dir']);

	$page = strip_bracket($page);
	$file = $dir . encode($page) . '.txt';
	$file_exists = file_exists($file);
	
	// ----
	// Record last modified date for QHM cache func.
	$lm_file = CACHE_DIR . QHM_LASTMOD;
	file_put_contents($lm_file, date('Y-m-d H:i:s'));

	// ----
	// Delete?

	if ($dir == DATA_DIR && $str === '') {
		// Page deletion
		if (! $file_exists) return; // Ignore null posting for DATA_DIR

		// Update RecentDeleted (Add the $page)
		add_recent($page, $whatsdeleted, '', $maxshow_deleted);

		//QBlog 記事 であれば、削除処理を呼び出す
		if (preg_match($qblog_page_re, $page))
		{
			qblog_remove_post($page);
		}

		// Remove the page
		unlink($file);

		// Update RecentDeleted, and remove the page from RecentChanges
		lastmodified_add($whatsdeleted, $page);

		// Clear is_page() cache
		is_page($page, TRUE);

		return;

	} else if ($dir == DIFF_DIR && $str === " \n") {
		return; // Ignore null posting for DIFF_DIR
	}

	// ----
	// File replacement (Edit)

	if (! is_pagename($page))
		die_message(str_replace('$1', htmlspecialchars($page),
		            str_replace('$2', 'WikiName', $qm->m['fmt_err_invalidiwn'])));

	$str = rtrim(preg_replace('/' . "\r" . '/', '', $str)) . "\n";
	$timestamp = ($file_exists && $notimestamp) ? filemtime($file) : FALSE;

	$fp = fopen($file, 'a') or die($qm->replace('file.err_not_writable', h(basename($dir)), encode($page)));
	set_file_buffer($fp, 0);
	flock($fp, LOCK_EX);
	ftruncate($fp, 0);
	rewind($fp);
	fputs($fp, $str);
	flock($fp, LOCK_UN);
	fclose($fp);

	if ($timestamp) pkwk_touch_file($file, $timestamp);

	// Optional actions
	if ($dir == DATA_DIR) {
		// Update RecentChanges (Add or renew the $page)
		if ($timestamp === FALSE) lastmodified_add($page);

		add_tinycode($page);

		// Command execution per update
		if (defined('PKWK_UPDATE_EXEC') && PKWK_UPDATE_EXEC)
			system(PKWK_UPDATE_EXEC . ' > /dev/null &');

	} else if ($dir == DIFF_DIR && $notify) {
		if ($notify_diff_only) $str = preg_replace('/^[^-+].*\n/m', '', $str);
		$footer['ACTION'] = 'Page update';
		$footer['PAGE']   = & $page;
		$footer['URI']    = get_script_uri() . '?' . rawurlencode($page);
		$footer['USER_AGENT']  = TRUE;
		$footer['REMOTE_ADDR'] = TRUE;

		if(isset($_SESSION['usr']))
			$str .= "\n\n ". $qm->replace('file.lbl_editor', $_SESSION['usr']). "\n";

		pkwk_mail_notify($notify_subject, $str, $footer) or
			die($qm->m['file']['err_mail_failed']);
	}

	is_page($page, TRUE); // Clear is_page() cache
}

// Update RecentDeleted
function add_recent($page, $recentpage, $subject = '', $limit = 0)
{
	if (PKWK_READONLY || $limit == 0 || $page == '' || $recentpage == '' ||
	    check_non_list($page)) return;
	
	$qm = get_qm();

	// Load
	$lines = $matches = array();
	foreach (get_source($recentpage) as $line)
		if (preg_match('/^-(.+) - (\[\[.+\]\])$/', $line, $matches))
			$lines[$matches[2]] = $line;

	$_page = '[[' . $page . ']]';

	// Remove a report about the same page
	if (isset($lines[$_page])) unset($lines[$_page]);

	// Add
	array_unshift($lines, '-' . format_date(UTIME) . ' - ' . $_page .
		htmlspecialchars($subject) . "\n");

	// Get latest $limit reports
	$lines = array_splice($lines, 0, $limit);

	// Update
	$fp = fopen(get_filename($recentpage), 'w') or
		die_message($qm->replace('file.err_not_wraitable', h($recentpage)));
	set_file_buffer($fp, 0);
	flock($fp, LOCK_EX);
	rewind($fp);
	fputs($fp, '#freeze'    . "\n");
	fputs($fp, '#norelated' . "\n");
	fputs($fp, '#nofollow'  . "\n");
	fputs($fp, join('', $lines));
	flock($fp, LOCK_UN);
	fclose($fp);

}

// Update PKWK_MAXSHOW_CACHE itself (Add or renew about the $page) (Light)
// Use without $autolink
function lastmodified_add($update = '', $remove = '')
{
	global $maxshow, $whatsnew, $autolink;
	$qm = get_qm();

	// AutoLink implimentation needs everything, for now
	if ($autolink) {
		put_lastmodified(); // Try to (re)create ALL
		return;
	}

	if (($update == '' || check_non_list($update)) && $remove == '')
		return; // No need

	$file = CACHE_DIR . PKWK_MAXSHOW_CACHE;
	if (! file_exists($file)) {
		put_lastmodified(); // Try to (re)create ALL
		return;
	}

	// Open
	pkwk_touch_file($file);
	$fp = fopen($file, 'r+') or
		die_message($qm->replace('fmt_err_open_cachedir', PKWK_MAXSHOW_CACHE));
	set_file_buffer($fp, 0);
	flock($fp, LOCK_EX);

	// Read (keep the order of the lines)
	$recent_pages = $matches = array();
	foreach(file_head($file, $maxshow + PKWK_MAXSHOW_ALLOWANCE, FALSE) as $line)
		if (preg_match('/^([0-9]+)\t(.+)/', $line, $matches))
			$recent_pages[$matches[2]] = $matches[1];

	// Remove if it exists inside
	if (isset($recent_pages[$update])) unset($recent_pages[$update]);
	if (isset($recent_pages[$remove])) unset($recent_pages[$remove]);

	// Add to the top: like array_unshift()
	if ($update != '')
		$recent_pages = array($update => get_filetime($update)) + $recent_pages;

	// Check
	$abort = count($recent_pages) < $maxshow;

	if (! $abort) {
		// Write
		ftruncate($fp, 0);
		rewind($fp);
		foreach ($recent_pages as $_page=>$time)
			fputs($fp, $time . "\t" . $_page . "\n");
	}

	flock($fp, LOCK_UN);
	fclose($fp);

	if ($abort) {
		put_lastmodified(); // Try to (re)create ALL
		return;
	}



	// ----
	// Update the page 'RecentChanges'

	$recent_pages = array_splice($recent_pages, 0, $maxshow);
	$file = get_filename($whatsnew);

	// Open
	pkwk_touch_file($file);
	$fp = fopen($file, 'r+') or
		die_message($qm->replace('file.err_cannot_open', h($whatsnew)));
	set_file_buffer($fp, 0);
	flock($fp, LOCK_EX);

	// Recreate
	ftruncate($fp, 0);
	rewind($fp);
	foreach ($recent_pages as $_page=>$time)
		fputs($fp, '-' . htmlspecialchars(format_date($time)) .
			' - ' . '[[' . htmlspecialchars($_page) . ']]' . "\n");
	fputs($fp, '#freeze'    . "\n");
	fputs($fp, '#norelated' . "\n");
	fputs($fp, '#nofollow'  . "\n");

	flock($fp, LOCK_UN);
	fclose($fp);
}

// Re-create PKWK_MAXSHOW_CACHE (Heavy)
function put_lastmodified()
{
	global $maxshow, $whatsnew, $autolink;
	$qm = get_qm();

	if (PKWK_READONLY) return; // Do nothing

	// Get WHOLE page list
	$pages = get_existpages();

	// Check ALL filetime
	$recent_pages = array();
	foreach($pages as $page)
		if ($page != $whatsnew && ! check_non_list($page))
			$recent_pages[$page] = get_filetime($page);

	// Sort decending order of last-modification date
	arsort($recent_pages, SORT_NUMERIC);

	// Cut unused lines
	// BugTrack2/179: array_splice() will break integer keys in hashtable
	$count   = $maxshow + PKWK_MAXSHOW_ALLOWANCE;
	$_recent = array();
	foreach($recent_pages as $key=>$value) {
		unset($recent_pages[$key]);
		$_recent[$key] = $value;
		if (--$count < 1) break;
	}
	$recent_pages = & $_recent;

	// Re-create PKWK_MAXSHOW_CACHE
	$file = CACHE_DIR . PKWK_MAXSHOW_CACHE;
	pkwk_touch_file($file);
	$fp = fopen($file, 'r+') or
		die_message($qm->replace('fmt_err_open_cachedir', PKWK_MAXSHOW_CACHE));
	set_file_buffer($fp, 0);
	flock($fp, LOCK_EX);
	ftruncate($fp, 0);
	rewind($fp);
	foreach ($recent_pages as $page=>$time)
		fputs($fp, $time . "\t" . $page . "\n");
	flock($fp, LOCK_UN);
	fclose($fp);

	// Create RecentChanges
	$file = get_filename($whatsnew);
	pkwk_touch_file($file);
	$fp = fopen($file, 'r+') or
		die_message($qm->replace('file.err_cannot_open', h($whatsnew)));
	set_file_buffer($fp, 0);
	flock($fp, LOCK_EX);
	ftruncate($fp, 0);
	rewind($fp);
	foreach (array_keys($recent_pages) as $page) {
		$time      = $recent_pages[$page];
		$s_lastmod = htmlspecialchars(format_date($time));
		$s_page    = htmlspecialchars($page);
		$pagetile  = get_page_title($page);
		fputs($fp, '-' . $s_lastmod . ' - [[' .$pagetile. '>' . $s_page . ']]' . "\n");
	}
	fputs($fp, '#freeze'    . "\n");
	fputs($fp, '#norelated' . "\n");
	fputs($fp, '#nofollow'  . "\n");
	flock($fp, LOCK_UN);
	fclose($fp);

	// For AutoLink
	if ($autolink) {
		list($pattern, $pattern_a, $forceignorelist) =
			get_autolink_pattern($pages);

		$file = CACHE_DIR . PKWK_AUTOLINK_REGEX_CACHE;
		pkwk_touch_file($file);
		$fp = fopen($file, 'r+') or
			die_message($qm->replace('fmt_err_open_cachedir', PKWK_AUTOLINK_REGEX_CACHE));
		set_file_buffer($fp, 0);
		flock($fp, LOCK_EX);
		ftruncate($fp, 0);
		rewind($fp);
		fputs($fp, $pattern   . "\n");
		fputs($fp, $pattern_a . "\n");
		fputs($fp, join("\t", $forceignorelist) . "\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}
}

// Get elapsed date of the page
function get_pg_passage($page, $sw = TRUE)
{
	global $show_passage;
	if (! $show_passage) return '';

	$time = get_filetime($page);
	$pg_passage = ($time != 0) ? get_passage($time) : '';

	return $sw ? '<small>' . $pg_passage . '</small>' : ' ' . $pg_passage;
}

// Last-Modified header
function header_lastmod($page = NULL)
{
	global $lastmod;

	if ($lastmod && is_page($page)) {
		pkwk_headers_sent();
		header('Last-Modified: ' .
			date('D, d M Y H:i:s', get_filetime($page)) . ' GMT');
	}
}

// Get a page list of this wiki
function get_existpages($dir = DATA_DIR, $ext = '.txt')
{
	$aryret = array();
	$qm = get_qm();
	
	$pattern = '((?:[0-9A-F]{2})+)';
	if ($ext != '') $ext = preg_quote($ext, '/');
	$pattern = '/^' . $pattern . $ext . '$/';

	$dp = @opendir($dir) or
		die_message($qm->replace('fmt_err_not_found_or_readable', $dir));
	$matches = array();
	while ($file = readdir($dp))
		if (preg_match($pattern, $file, $matches))
			$aryret[$file] = decode($matches[1]);
	closedir($dp);

	return $aryret;
}

// Get PageReading(pronounce-annotated) data in an array()
function get_readings()
{
	global $pagereading_enable, $pagereading_kanji2kana_converter;
	global $pagereading_kanji2kana_encoding, $pagereading_chasen_path;
	global $pagereading_kakasi_path, $pagereading_config_page;
	global $pagereading_config_dict;
	$qm = get_qm();

	$pages = get_existpages();

	$readings = array();
	foreach ($pages as $page) 
		$readings[$page] = '';

	$deletedPage = FALSE;
	$matches = array();
	foreach (get_source($pagereading_config_page) as $line) {
		$line = chop($line);
		if(preg_match('/^-\[\[([^]]+)\]\]\s+(.+)$/', $line, $matches)) {
			if(isset($readings[$matches[1]])) {
				// This page is not clear how to be pronounced
				$readings[$matches[1]] = $matches[2];
			} else {
				// This page seems deleted
				$deletedPage = TRUE;
			}
		}
	}

	// If enabled ChaSen/KAKASI execution
	if($pagereading_enable) {

		// Check there's non-clear-pronouncing page
		$unknownPage = FALSE;
		foreach ($readings as $page => $reading) {
			if($reading == '') {
				$unknownPage = TRUE;
				break;
			}
		}

		// Execute ChaSen/KAKASI, and get annotation
		if($unknownPage) {
			switch(strtolower($pagereading_kanji2kana_converter)) {
			case 'chasen':
				if(! file_exists($pagereading_chasen_path))
					die_message($qm->replace('file.err_chasen_notfound', $pagereading_chasen_path));

				$tmpfname = tempnam(realpath(CACHE_DIR), 'PageReading');
				$fp = fopen($tmpfname, 'w') or
					die_message($qm->replace('file.err_cannot_write_tmpfile', $tmpfname));
				foreach ($readings as $page => $reading) {
					if($reading != '') continue;
					fputs($fp, mb_convert_encoding($page . "\n",
						$pagereading_kanji2kana_encoding, SOURCE_ENCODING));
				}
				fclose($fp);

				$chasen = "$pagereading_chasen_path -F %y $tmpfname";
				$fp     = popen($chasen, 'r');
				if($fp === FALSE) {
					unlink($tmpfname);
					die_message($qm->replace('file.err_chasen_failed', $chasen));
				}
				foreach ($readings as $page => $reading) {
					if($reading != '') continue;

					$line = fgets($fp);
					$line = mb_convert_encoding($line, SOURCE_ENCODING,
						$pagereading_kanji2kana_encoding);
					$line = chop($line);
					$readings[$page] = $line;
				}
				pclose($fp);

				unlink($tmpfname) or
					die_message($qm->replace('file.err_cannot_remove_tmpfile', $tmpfname));
				break;

			case 'kakasi':	/*FALLTHROUGH*/
			case 'kakashi':
				if(! file_exists($pagereading_kakasi_path))
					die_message('KAKASI not found: ' . $pagereading_kakasi_path);

				$tmpfname = tempnam(realpath(CACHE_DIR), 'PageReading');
				$fp       = fopen($tmpfname, 'w') or
					die_message('Cannot write temporary file "' . $tmpfname . '".' . "\n");
				foreach ($readings as $page => $reading) {
					if($reading != '') continue;
					fputs($fp, mb_convert_encoding($page . "\n",
						$pagereading_kanji2kana_encoding, SOURCE_ENCODING));
				}
				fclose($fp);

				$kakasi = "$pagereading_kakasi_path -kK -HK -JK < $tmpfname";
				$fp     = popen($kakasi, 'r');
				if($fp === FALSE) {
					unlink($tmpfname);
					die_message($qm->replace('file.err_kakasi_failed', $kakasi));
				}

				foreach ($readings as $page => $reading) {
					if($reading != '') continue;

					$line = fgets($fp);
					$line = mb_convert_encoding($line, SOURCE_ENCODING,
						$pagereading_kanji2kana_encoding);
					$line = chop($line);
					$readings[$page] = $line;
				}
				pclose($fp);

				unlink($tmpfname) or
					die_message($qm->replace('file.err_cannot_remove_tmpfile', $tmpfname));
				break;

			case 'none':
				$patterns = $replacements = $matches = array();
				foreach (get_source($pagereading_config_dict) as $line) {
					$line = chop($line);
					if(preg_match('|^ /([^/]+)/,\s*(.+)$|', $line, $matches)) {
						$patterns[]     = $matches[1];
						$replacements[] = $matches[2];
					}
				}
				foreach ($readings as $page => $reading) {
					if($reading != '') continue;

					$readings[$page] = $page;
					foreach ($patterns as $no => $pattern)
						$readings[$page] = mb_convert_kana(mb_ereg_replace($pattern,
							$replacements[$no], $readings[$page]), 'aKCV');
				}
				break;

			default:
				die_message($qm->replace('file.err_unknown_kk_converter', $pagereading_kanji2kana_converter));
				break;
			}
		}

		if($unknownPage || $deletedPage) {

			asort($readings); // Sort by pronouncing(alphabetical/reading) order
			$body = '';
			foreach ($readings as $page => $reading)
				$body .= '-[[' . $page . ']] ' . $reading . "\n";

			page_write($pagereading_config_page, $body);
		}
	}

	// Pages that are not prounouncing-clear, return pagenames of themselves
	foreach ($pages as $page) {
		if($readings[$page] == '')
			$readings[$page] = $page;
	}

	return $readings;
}

// Get a list of encoded files (must specify a directory and a suffix)
function get_existfiles($dir, $ext)
{
	$qm = get_qm();
	$pattern = '/^(?:[0-9A-F]{2})+' . preg_quote($ext, '/') . '$/';
	$aryret = array();
	$dp = @opendir($dir) or die_message($qm->replace('fmt_err_not_found_or_readable', $dir));
	while ($file = readdir($dp))
		if (preg_match($pattern, $file))
			$aryret[] = $dir . $file;
	closedir($dp);
	return $aryret;
}

// Get a list of related pages of the page
function links_get_related($page)
{
	global $vars, $related;
	static $links = array();

	if (isset($links[$page])) return $links[$page];

	// If possible, merge related pages generated by make_link()
	$links[$page] = ($page == $vars['page']) ? $related : array();

	// Get repated pages from DB
	$links[$page] += links_get_related_db($vars['page']);

	return $links[$page];
}

// _If needed_, re-create the file to change/correct ownership into PHP's
// NOTE: Not works for Windows
function pkwk_chown($filename, $preserve_time = TRUE)
{
	static $php_uid; // PHP's UID
	$qm = get_qm();

	if (! isset($php_uid)) {
		if (extension_loaded('posix')) {
			$php_uid = posix_getuid(); // Unix
		} else {
			$php_uid = 0; // Windows
		}
	}

	// Lock for pkwk_chown()
	$lockfile = CACHE_DIR . 'pkwk_chown.lock';
	$flock = fopen($lockfile, 'a') or
		die($qm->replace('file.err_pkwk_chown_cannot_open', basename(h($lockfile))));
	flock($flock, LOCK_EX) or die($qm->m['file']['err_pkwk_chown_lock_failed']);

	// Check owner
	$stat = stat($filename) or
		die($qm->replace('err_pkwk_chown_stat_failed', basename(h($filename))));
	if ($stat[4] === $php_uid) {
		// NOTE: Windows always here
		$result = TRUE; // Seems the same UID. Nothing to do
	} else {
		$tmp = $filename . '.' . getmypid() . '.tmp';

		// Lock source $filename to avoid file corruption
		// NOTE: Not 'r+'. Don't check write permission here
		$ffile = fopen($filename, 'r') or
			die($qm->replace('file.err_pkwk_chown_cannot_open', basename(h($filename))));

		// Try to chown by re-creating files
		// NOTE:
		//   * touch() before copy() is for 'rw-r--r--' instead of 'rwxr-xr-x' (with umask 022).
		//   * (PHP 4 < PHP 4.2.0) touch() with the third argument is not implemented and retuns NULL and Warn.
		//   * @unlink() before rename() is for Windows but here's for Unix only
		flock($ffile, LOCK_EX) or die($qm->m['file']['err_pkwk_chown_lock_failed']);
		$result = touch($tmp) && copy($filename, $tmp) &&
			($preserve_time ? (touch($tmp, $stat[9], $stat[8]) || touch($tmp, $stat[9])) : TRUE) &&
			rename($tmp, $filename);
		flock($ffile, LOCK_UN) or die($qm->m['file']['err_pkwk_chown_lock_failed']);

		fclose($ffile) or die($qm->m['file']['err_pkwk_chown_fclose_failed']);

		if ($result === FALSE) @unlink($tmp);
	}

	// Unlock for pkwk_chown()
	flock($flock, LOCK_UN) or die($qm->m['file']['err_pkwk_chown_lock_failed']);
	fclose($flock) or die($qm->m['file']['err_pkwk_chown_lock_failed']);

	return $result;
}

// touch() with trying pkwk_chown()
function pkwk_touch_file($filename, $time = FALSE, $atime = FALSE)
{
	$qm = get_qm();
	// Is the owner incorrected and unable to correct?
	if (! file_exists($filename) || pkwk_chown($filename)) {
		if ($time === FALSE) {
			$result = touch($filename);
		} else if ($atime === FALSE) {
			$result = touch($filename, $time);
		} else {
			$result = touch($filename, $time, $atime);
		}
		return $result;
	} else {
		die($qm->replace('file.err_pkwk_touch_invalid_uid', h(basename($filename))));
	}
}

//regist tinyurl table
function add_tinycode($page)
{
	if($page=='')
		return false;
	$qm = get_qm();
	
	$file = CACHE_DIR.QHM_TINYURL_TABLE;
	
	if( !file_exists( $file ) )
	{
		$pages = array_diff(get_existpages(), array($whatsnew));
		$str = '';
		$table = array();
		foreach($pages as $k=>$v)
		{
			$tname = get_random_string(6);
			while( isset($table[$tname]) )  // prob is X/62^6 !!
			{
				$tname = get_random_string(6); 
			}
			
			$table[$tname] = '';
			$str .= $tname.','.$v."\n";
		}
				
		$fp = fopen($file, 'w') or
			die_message($qm->replace('file.err_cannot_open', h($file)));
		set_file_buffer($fp, 0);
		flock($fp, LOCK_EX);
		
		fputs($fp, $str);
		
		flock($fp, LOCK_UN);
		fclose($fp);
	}
	else
	{
	
		$table = get_tiny_table();
		
		$r_table = array_flip($table);
		if(isset($r_table[$page]))
			return '';
		
		$tname = get_random_string(6);
		while( isset($table[$tname]) )  // prob is X/62^6 !!
		{
			$tname = get_random_string(6); 
		}
		
		$str = $tname.','.$page."\n";

		$fp = fopen($file, 'a') or
			die_message($qm->replace('file.err_cannot_open', h($file)));
		set_file_buffer($fp, 0);
		flock($fp, LOCK_EX);
		
		fputs($fp, $str);
		
		flock($fp, LOCK_UN);
		fclose($fp);

	}
}

function del_tinycode($page)
{
	$qm = get_qm();
	
	$table = get_tiny_table(false);
	unset($table[$page]);
	
	$str = '';
	foreach($table as $key=>$val)
	{
		$str .= $val.','.$key."\n";
	}

	$file = CACHE_DIR.QHM_TINYURL_TABLE;
	$fp = fopen($file, 'w') or
		die_message($qm->replace('file.err_cannot_open', h($file)));
	set_file_buffer($fp, 0);
	flock($fp, LOCK_EX);
	
	fputs($fp, $str);
	
	flock($fp, LOCK_UN);
	fclose($fp);
}

function get_tiny_table($key_is_code=true)
{
	$file = CACHE_DIR.QHM_TINYURL_TABLE;
	
	$lines = explode("\n",file_get_contents($file));
	$table = array();
	foreach($lines as $line)
	{
		if( trim($line) != '')
		{
			$arr = explode(',', $line);
			
			if($key_is_code)
			{
				$table[ trim($arr[0]) ] = trim($arr[1]);
			}
			else
			{
				$table[ trim($arr[1]) ] = trim($arr[0]);			
			}	
		}
	}
	
	return $table;
}

function get_tiny_code($page)
{
	$table = get_tiny_table(false);

	if( isset($table[$page]) )
	{
		return $table[$page];
	}
	else
	{
		return null;
	}
}

function get_tiny_page($code)
{
	$table = get_tiny_table();
	if(isset($table[$code]))
		return $table[$code];
	else
		return '';
}

function get_random_string($length=6)
{
	static $seed = '0123456789abcdefghifklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$max = strlen($seed)-1;
	
	$str = '';
	for($i=0; $i<$length; $i++)
		$str .= substr($seed, rand(0, $max), 1);

	return $str;
}

function chk_script($script){
	
	$file = CACHE_DIR.'qhm_script.txt';
	if(file_exists($file)){
		$past_script = trim(file_get_contents($file));
		if($script == $past_script){
			return true;
		}
	}

	file_put_contents($file, $script);

	$lm_file = CACHE_DIR . QHM_LASTMOD;
	file_put_contents($lm_file, date('Y-m-d H:i:s'));

}

function qblog_update($force = FALSE)
{
	static $updated = FALSE;
	if ($updated) return;

	$updated = TRUE;
	
	// update caches
	// qblog_recent.dat
	// qblog_categories.dat
	// qblog_archived.dat
	// *.qbc.dat
	
	qblog_update_post($force);
	qblog_update_recent($force);
	qblog_update_categories($force);
	qblog_update_archives($force);

	return;
}

function qblog_update_recent($force = FALSE)
{
	global $qblog_page_prefix, $qblog_page_re;
	
	//タイムスタンプをチェック
	$datafile = CACHEQBLOG_DIR . 'qblog_recent.dat';
	if ($force === FALSE && filemtime($datafile) >= filemtime(CACHE_DIR . QHM_LASTMOD))
	{
		return;
	}
	
	//すべてのブログ記事ファイルを配列に格納
	$files = glob(DATA_DIR . encode($qblog_page_prefix) . '*');
	
	$pages = array();
	foreach ($files as $file)
	{
		$pagename = decode(basename($file, '.txt'));
		if (preg_match($qblog_page_re, $pagename))
			$pages[] = $pagename;
	}
	
	//日付+ナンバリングでソート
	natsort($pages);
	$pages = array_reverse($pages);

	
	//件数を1行目に追加
	array_unshift($pages, count($pages));
	
	//その順番で qblog_recent.dat に保存
	$data = join("\n", $pages);
	file_put_contents($datafile, $data, LOCK_EX);

}

function qblog_update_categories($force = FALSE)
{
	global $qblog_page_prefix, $qblog_page_re, $qblog_default_cat;

	//タイムスタンプをチェック
	$cat_list_file = CACHEQBLOG_DIR . 'qblog_categories.dat';
	$lastmod_file = CACHE_DIR . QHM_LASTMOD;

	if ($force === FALSE && (filemtime($cat_list_file) >= filemtime($lastmod_file)))
	{
		return;
	}

	//カテゴリ毎のキャッシュファイルを空に
	$files = glob(CACHEQBLOG_DIR . '*.qbc.dat');
	foreach ($files as $file)
	{
		file_put_contents($file, '');
	}
	
	// ブログ記事のカテゴリを取得する
	$categories = array();
	
	$files = glob(CACHEQBLOG_DIR . '*.qbp.dat');
	foreach ($files as $file)
	{
		$pagename = decode(basename($file, '.qbp.dat'));
		$data = get_qblog_post_data($pagename);
		if ($data === FALSE)
		{
			continue;
		}
		
		if (isset($data['category']) OR trim($data['category']) !== '')
		{
			$categories[$data['category']][] = $pagename;
		}
		else
		{
			$categories[$qblog_default_cat][] = $pagename;
		}
	}
	
	//カテゴリー毎のキャッシュファイルを作成・更新
	foreach ($categories as $category => $pages)
	{
		qblog_update_category($category, $pages, TRUE);
	}

	// --------------------
	// categoryの一覧を作成
	// --------------------
	
	$cat_data = '';

	// カテゴリ毎のファイルを取得し、属するページ数をカウント
	$files = glob(CACHEQBLOG_DIR . '*.qbc.dat');
	foreach ($files as $file)
	{
		$cat = decode(basename($file, '.qbc.dat'));
		$pages = count($categories[$cat]);
		$cat_data .= $cat ."\t". $pages ."\n";
	}
	
	// カテゴリの一覧を作成
	file_put_contents($cat_list_file, $cat_data, LOCK_EX);
	
}

function qblog_update_category($category, $add_pages, $force = FALSE)
{
	$cat_file = CACHEQBLOG_DIR . encode($category) . '.qbc.dat';

	//更新しない
	$lastmod_file = CACHE_DIR . QHM_LASTMOD;
	
	if ($force === FALSE && file_exists($cat_file) && (filemtime($cat_file) >= filemtime($lastmod_file)))
	{
		return;
	}

	//カテゴリの更新
	$pages = array();
	if (file_exists($cat_file))
	{
		$pages = explode("\n", file_get_contents($cat_file));
	}
	
	if ( ! is_array($add_pages))
	{
		$add_pages = array($add_pages);
	}
	
	$pages = array_merge($pages, $add_pages);
	$pages = array_unique($pages);

	//日付+ナンバリングでソート
	natsort($pages);
	$pages = array_reverse($pages);


	file_put_contents($cat_file, join("\n", $pages), LOCK_EX);
}

function qblog_update_archives($force = FALSE)
{
	global $qblog_page_prefix, $qblog_page_re;
	
	//タイムスタンプをチェック
	$datafile = CACHEQBLOG_DIR . 'qblog_archives.dat';
	if ($force === FALSE && filemtime($datafile) >= filemtime(CACHE_DIR . QHM_LASTMOD))
	{
		return;
	}
	
	//すべてのブログ記事を配列に格納
	$files = glob(DATA_DIR . encode($qblog_page_prefix) . '*');
	
	$pages = array();
	$year_month_list = array();
	foreach ($files as $file)
	{
		$pagename = decode(basename($file, '.txt'));
		if (preg_match($qblog_page_re, $pagename, $mts))
		{
			$pages[] = $pagename;
			//年月をキーに、記事数をカウント
			if ( ! isset($year_month_list[$mts[1].$mts[2]]))
			{
				$year_month_list[$mts[1].$mts[2]] = 0;
			}
			$year_month_list[$mts[1].$mts[2]]++;
		}
	}
	
	//新しい順にソート
	krsort($year_month_list);
	
	foreach ($year_month_list as $year_month => $count)
	{
		$year = substr($year_month, 0, 4);
		$month = substr($year_month, 4, 2);
		$year_month_list[$year_month] = join(",", array($year, $month, $count));
	}

	//保存
	$data = join("\n", $year_month_list);
	file_put_contents($datafile, $data, LOCK_EX);
	
}

/**
 * 記事単位のキャッシュを更新する
 *
 * boolean $force 
 * string $page
 * asoc $option
 *
 * @return boolean キャッシュファイルが扱えるかどうか
 */
function qblog_update_post($force = FALSE, $page = NULL, $option = NULL)
{
	global $vars;
	global $qblog_defaultpage, $qblog_page_prefix, $qblog_page_re, $qblog_default_cat;

	//ページが未指定の場合
	if (is_null($page))
	{
		$page = $vars['page'];
		//ページがブログでなければ何もしない
		if ( ! is_qblog())
		{
			return FALSE;
		}
	}
	if ($page === $qblog_defaultpage)
	{
		return FALSE;
	}
	
	//wikiファイルとキャッシュファイルのmtime を比べる
	$wikifile = get_filename($page);
	$datafile = CACHEQBLOG_DIR . encode($page) . '.qbp.dat';

	if ( ! file_exists($wikifile))
	{
		return FALSE;
	}
	//キャッシュが古ければ更新
	if ($force === FALSE && file_exists($datafile) && filemtime($wikifile) <= filemtime($datafile))
	{
		return TRUE;
	}

	//オプションが未指定の場合
	//すでにデータファイルがあればそれを読み込む
	if (is_null($option))
	{
		$option = array('image'=>'', 'category'=>$qblog_default_cat);
		if (file_exists($datafile))
		{
			$data = unserialize(file_get_contents($datafile));
			$data = ($data === FALSE) ? array() : $data;
			$option = array_merge($option, $data);
		}
	}

	
	$source = get_source($page);
	
	$title = '';//ページタイトル
	$header = '';//見出し
	$image = (isset($option['image']) && $option['image'] !== '') ? $option['image'] : '';//画像パス
	$visible = TRUE; //表示

	// 説明ページに表示しないプラグイン
	global $ignore_plugin, $strip_plugin, $strip_plugin_inline;
	foreach($source as $i => $line)
	{
		if (preg_match($ignore_plugin, $line))
		{	// リストから省く
			$source = array();
			break;
		}
		
		//タイトルのセット
		if (preg_match('/^TITLE:(.*)/', $line, $ms))
		{
			$title = $ms[1];
			unset($source[$i]);
		}
		//見出し
		else if (preg_match('/^(?:\*){1,3}(.*)\[#\w+\]\s?/', $line, $ms))
		{
			if ($header === '')
			{
				$header = trim($ms[1]);
			}
			$source[$i] = trim($ms[1]);
		}
		//使わないブロックプラグインやキーワード
		else if (preg_match($strip_plugin, $line))
		{
			unset($source[$i]);
		}
		//ブロックプラグインを除く
		else if (preg_match('/^#qblog/', $line))
		{
			unset($source[$i]);
		}
		else if ($image === '' && preg_match('/(?:^#show|&show)\(([^,]+)(.*)\)/', $line, $mts))
		{
			if (strpos($mts[2], 'nolink') === FALSE)
			{
				$image = $mts[1];
			}
		}
	}

	//閉鎖されている場合は表示フラグをオフ
	if (count($source) === 0)
	{
		$visible = FALSE;
	}

	//タイトルがない場合は最初の見出しを使う
	if ($title === '')
	{
		$title = $header;
	}

	//特定のインラインプラグインを除く
	$source = preg_replace($strip_plugin_inline, '', $source);
	//コンバートし、タグを除く
	$abstract = strip_tags(convert_html($source));
	//指定文字数抜粋し、改行文字を除く
	$abst_length = 100;
	if (mb_strlen($abstract) > $abst_length)
	{
		$abstract = str_replace(array("\n", "\r"), '', mb_substr($abstract, 0, $abst_length - 1) . '…');
	}
	else
	{
		$abstract = str_replace(array("\n", "\r"), '', $abstract);
	}
	
	$category = $option['category'];//カテゴリ
	
	//ファイルを保存
	$data = compact(array('title', 'abstract', 'image', 'category', 'visible'));
	file_put_contents($datafile, serialize($data), LOCK_EX);

	return TRUE;
}

function qblog_save_post_data($page, $data)
{
	$datafile = CACHEQBLOG_DIR . encode($page) . '.qbp.dat';
	file_put_contents($datafile, serialize($data), LOCK_EX);
}

/**
 * ブログ記事へコメントを追加する
 */
function qblog_add_comment($page, $comment_data, $timestamp = NULL)
{
	global $qblog_datetime_format, $qblog_comment_check, $qblog_comment_notice;
	if (is_null($timestamp))
	{
		$timestamp = time();
	}
	$comment_date = date($qblog_datetime_format, $timestamp);
	
	$accept = $qblog_comment_check ? 0 : 1;
	$comment_data = array_merge(array(
			'id'       => '',//set after
			'msg'      => '',
			'title'    => '',
			'name'     => '',
			'datetime' => $comment_date,
			'ipaddress' => $_SERVER['REMOTE_ADDR'],
			'accepted' => $accept,
			'show'     => 1,
			'admin'    => 0
		), $comment_data);
	

	$comments = array();
	$newid = 1;
	//read data file
	if (file_exists(CACHEQBLOG_DIR . encode($page) . '.qbcm.dat'))
	{
		$comments = unserialize(file_get_contents(CACHEQBLOG_DIR . encode($page) . '.qbcm.dat'));
		if ($comments !== FALSE)
		{
			$newest = array_pop($comments);
			$newid  = $newest['id'] + 1;
			array_push($comments, $newest);
		}
		else
		{
			$comments = array();
		}
	}
	$comment_data['id'] = $newid;
	$comments[$newid] = $comment_data;
	
	file_put_contents(CACHEQBLOG_DIR . encode($page) . '.qbcm.dat', serialize($comments), LOCK_EX);
	
	//承認が必要であれば未承認コメント一覧を更新
	if ($qblog_comment_check)
	{
		qblog_update_pending_comment($page, $comment_data, $timestamp);
	}
	//承認不要であれば最新コメント一覧を更新
	else
	{
		qblog_update_recent_comment($page, $timestamp);
	}
	
	//管理者以外のコメントの場合、通知メールを送信
	if ( ! $comment_data['admin'] && $qblog_comment_notice)
	{
		sent_qblog_comment_notice($page, $comment_data);
	}
	
}

function qblog_update_recent_comment($page, $timestamp = NULL)
{
	if (is_null($timestamp))
	{
		$timestamp = time();
	}
	//最近コメントされたページ名一覧を読み込む
	$recent_comments = array();
	if (file_exists(CACHEQBLOG_DIR . 'qblog_recent_comments.dat'))
	{
		$recent_comment_lines = explode("\n", file_get_contents(CACHEQBLOG_DIR . 'qblog_recent_comments.dat'));
		foreach ($recent_comment_lines as $line)
		{
			list($time, $pagename) = explode("\t", $line);
			$recent_comments[$pagename] = $time;
		}
	}
	$recent_comments[$page] = $timestamp;
	arsort($recent_comments);
	
	$datastr = '';
	$cnt = 0;
	foreach ($recent_comments as $pagename => $time)
	{
		if ($cnt > QBLOG_MAX_RECENT_COMMENTS)
			break;

		$datastr .= $time . "\t" . $pagename . "\n";
		$cnt++;
	}
	file_put_contents(CACHEQBLOG_DIR . 'qblog_recent_comments.dat', $datastr, LOCK_EX);
}

function qblog_update_pending_comment($page, $comment_data, $timestamp = NULL)
{
	if (is_null($timestamp))
	{
		$timestamp = time();
	}

	$datafile = CACHEQBLOG_DIR . 'qblog_pending_comments.dat';
	$pending_comments = array();
	if (file_exists($datafile))
	{
		$pending_comments = unserialize(file_get_contents($datafile));
		$pending_comments = ($pending_comments === FALSE) ? array() : $pending_comments;
	}
	
	$data = array(
		'id'    => $comment_data['id'],
		'page'  => $page,
		'time'  => $timestamp,
		'name'  => $comment_data['name'],
		'title' => $comment_data['title']
	);
	
	array_unshift($pending_comments, $data);
	
	file_put_contents($datafile, serialize($pending_comments), LOCK_EX);

	//タイムスタンプを更新
	pkwk_touch_file(CACHE_DIR . QHM_LASTMOD);
	
}

function qblog_accept_comment($page, $id)
{
	$datafile = CACHEQBLOG_DIR . encode($page) . '.qbcm.dat';
	//コメントファイルの承認フラグを立てる
	$comments = unserialize(file_get_contents($datafile));
	if ( ! isset($comments[$id]))
	{
		return FALSE;
	}
	
	$comments[$id]['accepted'] = 1;
	file_put_contents($datafile, serialize($comments), LOCK_EX);
	
	//最新コメントに追加
	qblog_update_recent_comment($page);
	
	//承認待ちリストから削除
	$datafile = CACHEQBLOG_DIR . 'qblog_pending_comments.dat';
	$pending_comments = unserialize(file_get_contents($datafile));
	$pending_comments = ($pending_comments === FALSE) ? array() : $pending_comments;
	
	foreach ($pending_comments as $i => $comment)
	{
		if ($comment['page'] === $page && $comment['id'] == $id)
		{
			unset($pending_comments[$i]);
			break;
		}
	}
	file_put_contents($datafile, serialize($pending_comments), LOCK_EX);
	
	//タイムスタンプを更新
	pkwk_touch_file(CACHE_DIR . QHM_LASTMOD);
	
	return TRUE;
}

function qblog_hide_comment($page, $id)
{
	$datafile = CACHEQBLOG_DIR . encode($page) . '.qbcm.dat';
	//コメントファイルの承認フラグを立てる
	$comments = unserialize(file_get_contents($datafile));
	if (isset($comments[$id]))
	{
		$comments[$id]['show'] = 0;
		file_put_contents($datafile, serialize($comments), LOCK_EX);
	}

	//承認待ちリストから削除
	$datafile = CACHEQBLOG_DIR . 'qblog_pending_comments.dat';
	$pending_comments = unserialize(file_get_contents($datafile));
	$pending_comments = ($pending_comments === FALSE) ? array() : $pending_comments;
	
	foreach ($pending_comments as $i => $comment)
	{
		if ($comment['page'] === $page && $comment['id'] == $id)
		{
			unset($pending_comments[$i]);
			break;
		}
	}
	
	file_put_contents($datafile, serialize($pending_comments), LOCK_EX);
	
	return TRUE;	
}

/**
 * ブログ記事を削除した場合、
 * 通常の削除処理に加え、ブログ用キャッシュファイルの調整が必要。
 */
function qblog_remove_post($page)
{
	$datafile = CACHEQBLOG_DIR . encode($page) . '.qbp.dat';
	$comment_datafile = CACHEQBLOG_DIR . encode($page) . '.qbcm.dat';
	
	if (file_exists($datafile))
		unlink($datafile);
	if (file_exists($comment_datafile))
		unlink($comment_datafile);

	// !ブログの削除処理
	//最近のコメント一覧の調整（qblog_recent_comments.dat）
	$commentfile = CACHEQBLOG_DIR.'qblog_recent_comments.dat';
	$comment_page_lines = explode("\n", file_get_contents($commentfile));
	$datastr = '';
	foreach ($comment_page_lines as $line)
	{
		if ($cnt > QBLOG_MAX_RECENT_COMMENTS) break;
		if (trim($line) === '') continue;

		list($time, $pagename) = explode("\t", $line);
		if ($comment['page'] != $pagename)
		{
			$datastr .= $time . "\t" . $pagename . "\n";
		}
	}
	file_put_contents($commentfile, $datastr, LOCK_EX);
	
	
	//承認待ちコメント一覧の調整（qblog_pending_comments.dat）
	$commentfile = CACHEQBLOG_DIR.'qblog_pending_comments.dat';
	$pending_comments = unserialize(file_get_contents($commentfile));
	$pending_comments = ($pending_comments === FALSE) ? array() : $pending_comments;
	foreach ($pending_comments as $i => $comment)
	{
		if ($comment['page'] == $page)
		{
			unset($pending_comments[$i]);
		}
	}
	file_put_contents($commentfile, serialize($pending_comments), LOCK_EX);
}

/**
 * ブログ記事名を作成、
 * 同じ日付のブログ記事があった場合、ナンバリングする
 */
function qblog_get_newpage($date = NULL)
{
	global $qblog_page_format;
	
	if (is_null($date))
	{
		$search_replace = array(
			'YYYY' => date('Y'),
			'MM'   => date('m'),
			'DD'   => date('d')
		);
	}
	else
	{
		$search_replace = array(
			'YYYY' => substr($date, 0, 4),
			'MM'   => substr($date, 5, 2),
			'DD'   => substr($date, 8, 2),
		);
	}
	
	// QBlogに保存するページ名を作成
	$newpage = str_replace(
					array_keys($search_replace), 
					array_values($search_replace),
					$qblog_page_format
				);
	$number_holder_pos = strpos($newpage, '#');
	if ($number_holder_pos !== FALSE)
	{
		$filename_prefix = encode(substr($newpage, 0, $number_holder_pos));
		$files = glob(DATA_DIR . $filename_prefix . '*');
		
		// PHP7.3 より正規表現において # が特殊文字として扱われる
		$pattern = '/^(' . str_replace(['\#', '#'], '(\d+)', preg_quote($newpage)) . ')$/';
		$max = 0;
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

	return $newpage;

}

/* End of file file.php */
/* Location: ./lib/file.php */