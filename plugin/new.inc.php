<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: new.inc.php,v 1.9 2005/06/16 15:04:08 henoheno Exp $
//
// New! plugin
//
// Usage:
//	&new([nodate]){date};     // Check the date string
//	&new(pagename[,nolink]);  // Check the pages's timestamp
//	&new(pagename/[,nolink]);
//		// Check multiple pages started with 'pagename/',
//		// and show the latest one

define('PLUGIN_NEW_DATE_FORMAT', '<span class="comment_date">%s</span>');

function plugin_new_inline()
{
	global $vars;
	$qm = get_qm();
	$new_elapses = array(
		86400     => $qm->m['plg_new']['label_1d'],  // 1day
		86400 * 5 => $qm->m['plg_new']['label_5d']  // 5days
	);

	$retval = '';
	$args = func_get_args();
	$date = strip_autolink(array_pop($args)); // {date} always exists

	if($date !== '') {
		// Show 'New!' message by the time of the $date string
		if (func_num_args() > 2) return $qm->m['plg_new']['err_usage'];

		$timestamp = strtotime($date);
		if ($timestamp === -1) return $qm->m['plg_new']['err_invalid_date'];
		$timestamp -= ZONETIME;

		$retval = in_array('nodate', $args) ? '' : htmlspecialchars($date);
	} else {
		// Show 'New!' message by the timestamp of the page
		if (func_num_args() > 3) return $qm->m['plg_new']['err_usage_page'];

		$name = strip_bracket(! empty($args) ? array_shift($args) : $vars['page']);
		$page = get_fullname($name, $vars['page']);
		$nolink = in_array('nolink', $args);

		if (substr($page, -1) == '/') {
			// Check multiple pages started with "$page"
			$timestamp = 0;
			$regex = '/^' . preg_quote($page, '/') . '/';
			foreach (preg_grep($regex, get_existpages()) as $page) {
				// Get the latest pagename and its timestamp
				$_timestamp = get_filetime($page);
				if ($timestamp < $_timestamp) {
					$timestamp = $_timestamp;
					$retval    = $nolink ? '' : make_pagelink($page);
				}
			}
			if ($timestamp == 0)
				return $qm->m['plg_new']['err_no_page'];
		} else {
			// Check a page
			if (is_page($page)) {
				$timestamp = get_filetime($page);
				$retval    = $nolink ? '' : make_pagelink($page, $name);
			} else {
				return $qm->m['plg_new']['err_no_page'];
			}
		}
	}

	// Add 'New!' string by the elapsed time
	$erapse = UTIME - $timestamp;
	foreach ($new_elapses as $limit=>$tag) {
		if ($erapse <= $limit) {
			$retval .= sprintf($tag, get_passage($timestamp));
			break;
		}
	}

	if($date !== '') {
		// Show a date string
		return sprintf(PLUGIN_NEW_DATE_FORMAT, $retval);
	} else {
		// Show a page name
		return $retval;
	}
}
?>
