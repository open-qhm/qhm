<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: sitemap.inc.php,v 1.3 2007/03/11 20:46:57 JuJu Exp $
// Copyright (C)
//   2007/02/27 JuJu http://su-u.jp/juju/
//              v 1.1 original
//   2007/03/04 JuJu http://su-u.jp/juju/
//              v 1.2 change XML schema www.sitemaps.org
//   2007/03/11 JuJu http://su-u.jp/juju/
//              v 1.3 add select page prefix, and page allow(disallow)
//
// License: GPL v2 or (at your option) any later version
//
// Google-Sitemaps plugin - Create Google-Sitemaps.
//
// Usage:
//    plugin=sitemap
//    plugin=sitemap&page=prefix
//    #sitemap(none)         : Don't show sitemap this page.
//    #sitemap(priority)     : The priority of this page relative to other pages on the same site.(default 0.5)
//    #sitemap([always|hourly|daily|weekly|monthly|yearly|never])
//                           : The frequency with which the URL is likely to change.
//    #sitemap(priority[,always|hourly|daily|weekly|monthly|yearly|never])
//

define('PLUGIN_SITEMAP_MAXSHOW',    2000);
define('PLUGIN_SITEMAP_READ_PAGES', true);

define('PLUGIN_SITEMAP_PLUGIN_PRIORITY_UP',   '(counter)');
define('PLUGIN_SITEMAP_PLUGIN_PRIORITY_DOWN', '(norelated)');
define('PLUGIN_SITEMAP_PLUGIN_NO_FOLLOW',     '(nofollow)');

define('PLUGIN_SITEMAP_PAGE_ALLOW',    '');
define('PLUGIN_SITEMAP_PAGE_DISALLOW', '^(Pukiwiki\/.*)$');


function plugin_sitemap_convert() { return ''; }

function plugin_sitemap_action() {

	global $whatsnew, $non_list;
	global $vars;

	$prefix = isset($vars['page']) ? strip_bracket($vars['page']) : '';

	$script = get_script_uri();
	$recent       = CACHE_DIR . 'recent.dat';
	$sitemapcache = CACHE_DIR . 'sitemap.' . (($prefix != '') ? encode($prefix) . '.' : '') . 'xml';

	if(!file_exists($recent) or !file_exists($sitemapcache) or (filemtime($recent) >= filemtime($sitemapcache))) {
		// Get all pages
		$pages = array();
		foreach(get_existpages() as $page) {
			if ( ($page != $whatsnew) and
			     ! preg_match("/$non_list/", $page) and
			     ( ($prefix == '') or (strpos($page, $prefix . '/') === 0) ) and
			     ( (PLUGIN_SITEMAP_PAGE_ALLOW     == '') or   preg_match('/' . PLUGIN_SITEMAP_PAGE_ALLOW     . '/', $page) )  and
			     ( (PLUGIN_SITEMAP_PAGE_DISALLOW  == '') or ! preg_match('/' . PLUGIN_SITEMAP_PAGE_DISALLOW  . '/', $page) ) )
				if( check_readable($page, false, false) )
					$pages[$page] = get_filetime($page);
		}
		// Sort by time
		arsort($pages, SORT_NUMERIC);
		// <url>
		$urls = '';
		$count = PLUGIN_SITEMAP_MAXSHOW;
		foreach ($pages as $page=>$time) {
			if($count > 0) {
				$r_page = rawurlencode($page);
				$link = $script . '?' . $r_page;
				$date = gmdate('Y-m-d\TH:i:s', $time + ZONETIME) . '+00:00';
				$priority   = '';
				$changefreq = '';
				$show = true;
				$_priority = 0.5;
				if(PLUGIN_SITEMAP_READ_PAGES) {
					foreach (get_source($page) as $line) {
						if (substr($line, 0, 1) == ' ' ) continue;
						if (substr($line, 0, 2) == '//') continue;
						if (preg_match('/^#([^\(]+)(?:\((.*)\))?/', $line, $matches)) {
							if ( (PLUGIN_SITEMAP_PLUGIN_PRIORITY_UP   != '') and preg_match('/^' . PLUGIN_SITEMAP_PLUGIN_PRIORITY_UP   . '$/', $matches[1], $matches2) ) $_priority += 0.2;
							if ( (PLUGIN_SITEMAP_PLUGIN_PRIORITY_DOWN != '') and preg_match('/^' . PLUGIN_SITEMAP_PLUGIN_PRIORITY_DOWN . '$/', $matches[1], $matches2) ) $_priority -= 0.1;
							if($matches[1] == 'sitemap') {
								$_priority = 0.5;
								if (preg_match('/\b(\d\.\d)\b/', $matches[2], $matches2))
									$_priority = floatval($matches2[1]);
								if (preg_match('/\b(always|hourly|daily|weekly|monthly|yearly|never)\b/', $matches[2], $matches2))
									$changefreq = $matches2[1];
								if (preg_match('/\bnone\b/', $matches[2], $matches2))
									$show = false;
								break;
							} elseif(preg_match('/^' . PLUGIN_SITEMAP_PLUGIN_NO_FOLLOW . '$/', $matches[1], $matches2)) {
								$show = false;
							}
						} elseif (preg_match('/&([^\(]+)(?:\((.*)\))?;/', $line, $matches)) {
							if ( (PLUGIN_SITEMAP_PLUGIN_PRIORITY_UP   != '') and preg_match('/^' . PLUGIN_SITEMAP_PLUGIN_PRIORITY_UP   . '$/', $matches[1], $matches2) ) $_priority += 0.2;
							if ( (PLUGIN_SITEMAP_PLUGIN_PRIORITY_DOWN != '') and preg_match('/^' . PLUGIN_SITEMAP_PLUGIN_PRIORITY_DOWN . '$/', $matches[1], $matches2) ) $_priority -= 0.1;
						} elseif (preg_match('/^NOINDEX:(?:.*)$/', $line)) {
							$show = false;
						}
					}
				}
				if($_priority > 1) $_priority = 1;
				if($_priority < 0) $_priority = 0;
				$priority = sprintf('%1.1f', $_priority);
				if($show) {
					$urls .= "  <url>\n";
					if($link       != '') $urls .= "    <loc>$link</loc>\n";
					if($date       != '') $urls .= "    <lastmod>$date</lastmod>\n";
					if($changefreq != '') $urls .= "    <changefreq>$changefreq</changefreq>\n";
					if($priority   != '') $urls .= "    <priority>$priority</priority>\n";
					$urls .= "  </url>\n";
					$count--;
				}
			} else {
				break;
			}
		}
		$xml = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
 xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
$urls</urlset>
EOD;
		$fp = fopen($sitemapcache, 'w');
		flock($fp, LOCK_EX);
		rewind($fp);
		fputs($fp, $xml);
		flock($fp, LOCK_UN);
		fclose($fp);
	} else {
		$xml = implode('', file($sitemapcache));
	}
	// Output sitemap XML
	header('Content-type: application/xml');
	print $xml;
	exit;
}
?>
