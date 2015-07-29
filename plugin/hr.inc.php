<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: hr.inc.php,v 1.4 2005/01/22 03:34:17 henoheno Exp $
//
// Horizontal rule plugin

function plugin_hr_convert()
{
	global $vars;

	$args   = func_get_args();
	$ret = array_shift($args);

	if ($ret == '') {
		return '<hr class="short_line" />';
	}

	if (isset($vars['page_alt']) && $vars['page_alt'] != 'SiteNavigator2') {
		return '<hr class="short_line" />';
	}

	if (preg_match('/^([1-9][0-9]*)$/',$ret, $matches)) {
		$num =  $matches[1];
		$image_path = 'image/hr/hr'.$num.'.png';
		if (file_exists($image_path)) {
			list($w, $h, $type, $attr) = getimagesize($image_path);
			return '<hr style="height:'.$h.'px;max-width:'.$w.'px;margin: 1em auto 1em;padding: 0;background:transparent url('.$image_path.') no-repeat;border:0px none;
">';
		}
	}

	return '<hr class="short_line" />';
}
?>
