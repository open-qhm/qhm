<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: navi.inc.php,v 1.22 2005/04/02 06:33:39 henoheno Exp $
//
// Navi plugin: Show DocBook-like navigation bar and contents

/*
 * Usage:
 *   #navi(contents-page-name)   <for ALL child pages>
 *   #navi([contents-page-name][,reverse]) <for contents page>
 *
 * Parameter:
 *   contents-page-name - Page name of home of the navigation (default:itself)
 *   reverse            - Show contents revese
 *
 * Behaviour at contents page:
 *   Always show child-page list like 'ls' plugin
 *
 * Behaviour at child pages:
 *
 *   The first plugin call - Show a navigation bar like a DocBook header
 *
 *     Prev  <contents-page-name>  Next
 *     --------------------------------
 *
 *   The second call - Show a navigation bar like a DocBook footer
 *
 *     --------------------------------
 *     Prev          Home          Next
 *     <pagename>     Up     <pagename>
 *
 * Page-construction example:
 *   foobar    - Contents page, includes '#navi' or '#navi(foobar)'
 *   foobar/1  - One of child pages, includes one or two '#navi(foobar)'
 *   foobar/2  - One of child pages, includes one or two '#navi(foobar)'
 */

// Exclusive regex pattern of child pages
define('PLUGIN_NAVI_EXCLUSIVE_REGEX', '');
//define('PLUGIN_NAVI_EXCLUSIVE_REGEX', '#/_#'); // Ignore 'foobar/_memo' etc.

// Insert <link rel=... /> tags into XHTML <head></head>
define('PLUGIN_NAVI_LINK_TAGS', FALSE);	// FALSE, TRUE

// ----

function plugin_navi_convert()
{
	global $vars, $script, $head_tags;
	static $navi = array();
	$qm = get_qm();

	$current = $vars['page'];
	$reverse = FALSE;
	if (func_num_args()) {
		list($home, $reverse) = array_pad(func_get_args(), 2, '');
		// strip_bracket() is not necessary but compatible
		$home    = get_fullname(strip_bracket($home), $current);
		$is_home = ($home == $current);
		if (! is_page($home)) {
			return $qm->replace('plg_navi.err_not_page', h($home));
		} else if (! $is_home &&
		    ! preg_match('/^' . preg_quote($home, '/') . '/', $current)) {
		    return $qm->replace('plg_navi.err_not_child', h($home), h(basename($current)));
		}
		$reverse = (strtolower($reverse) == 'reverse');
	} else {
		$home    = $vars['page'];
		$is_home = TRUE; // $home == $current
	}

	$pages  = array();
	$footer = isset($navi[$home]); // The first time: FALSE, the second: TRUE
	if (! $footer) {
		$navi[$home] = array(
			'up'   =>'',
			'prev' =>'',
			'prev1'=>'',
			'next' =>'',
			'next1'=>'',
			'home' =>'',
			'home1'=>'',
		);

		$pages = preg_grep('/^' . preg_quote($home, '/') .
			'($|\/)/', get_existpages());
		if (PLUGIN_NAVI_EXCLUSIVE_REGEX != '') {
			// If old PHP could use preg_grep(,,PREG_GREP_INVERT)...
			$pages = array_diff($pages,
				preg_grep(PLUGIN_NAVI_EXCLUSIVE_REGEX, $pages));
		}
		$pages[] = $current; // Sentinel :)
		$pages   = array_unique($pages);
		natcasesort($pages);
		if ($reverse) $pages = array_reverse($pages);

		$prev = $home;
		foreach ($pages as $page) {
			if ($page == $current) break;
			$prev = $page;
		}
		$next = current($pages);

		$pos = strrpos($current, '/');
		$up = '';
		if ($pos > 0) {
			$up = substr($current, 0, $pos);
			$navi[$home]['up']    = make_pagelink($up, $qm->m['plg_navi']['up']);
		}
		if (! $is_home) {
			$navi[$home]['prev']  = make_pagelink($prev, get_page_title($prev) );
			$navi[$home]['prev1'] = make_pagelink($prev, $qm->m['plg_navi']['prev']);
		}
		if ($next != '') {
			$navi[$home]['next']  = make_pagelink($next, get_page_title($next) );
			$navi[$home]['next1'] = make_pagelink($next, $qm->m['plg_navi']['next']);
		}
//		$navi[$home]['home']  = make_pagelink($home);
		$navi[$home]['home']  = make_pagelink($home, get_page_title($home));
		$navi[$home]['home1'] = make_pagelink($home, $qm->m['plg_navi']['home']);

//var_dump($navi);

		// Generate <link> tag: start next prev(previous) parent(up)
		// Not implemented: contents(toc) search first(begin) last(end)
		if (PLUGIN_NAVI_LINK_TAGS) {
			foreach (array('start'=>$home, 'next'=>$next,
			    'prev'=>$prev, 'up'=>$up) as $rel=>$_page) {
				if ($_page != '') {
					$s_page = h($_page);
					$r_page = rawurlencode($_page);
					$head_tags[] = ' <link rel="' .
						$rel . '" href="' . $script .
						'?' . $r_page . '" title="' .
						$s_page . '" />';
				}
			}
		}
	}

	$ret = '';

	if ($is_home) {
		// Show contents
		$count = count($pages);
		if ($count == 0) {
			return $qm->m['plg_navi']['err_already_view'];
		} else if ($count == 1) {
			// Sentinel only: Show usage and warning
			$home = h($home);
			$ret .= $qm->replace('plg_navi.err_no_child', h($home));
		} else {
			$ret .= '<ul>';
			foreach ($pages as $page)
				if ($page != $home)
					$ret .= ' <li>' . make_pagelink($page) . '</li>';
			$ret .= '</ul>';
		}

	} else if (! $footer) {
		// Header
		$ret = <<<EOD
<ul class="navi">
 <li class="navi_left">{$navi[$home]['prev1']}</li>
 <li class="navi_right">{$navi[$home]['next1']}</li>
 <li class="navi_none">{$navi[$home]['home']}</li>
</ul>
<hr class="full_hr" />
EOD;

	} else {
		// Footer
		$ret = <<<EOD
<hr class="full_hr" />
<ul class="navi">
 <li class="navi_left">{$navi[$home]['prev1']}<br />{$navi[$home]['prev']}</li>
 <li class="navi_right">{$navi[$home]['next1']}<br />{$navi[$home]['next']}</li>
 <li class="navi_none">{$navi[$home]['home1']}<br />{$navi[$home]['up']}</li>
</ul>
EOD;
	}
	return $ret;
}
?>
