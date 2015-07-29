<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: color.inc.php,v 1.22 2005/06/16 15:04:08 henoheno Exp $
//
// Text color plugin

// Allow CSS instead of <font> tag
// NOTE: <font> tag become invalid from XHTML 1.1
define('PLUGIN_COLOR_ALLOW_CSS', TRUE); // TRUE, FALSE

// ----
define('PLUGIN_COLOR_REGEX', '/^(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z-]+)$/i');

function plugin_color_inline()
{
	global $pkwk_dtd;
	$qm = get_qm();
	
	$args = func_get_args();
	$text = strip_autolink(array_pop($args)); // Already htmlspecialchars(text)

	list($color, $bgcolor) = array_pad($args, 2, '');
	if ($color != '' && $bgcolor != '' && $text == '') {
		// Maybe the old style: '&color(foreground,text);'
		$text    = htmlspecialchars($bgcolor);
		$bgcolor = '';
	}
	if (($color == '' && $bgcolor == '') || $text == '' || func_num_args() > 3)
		return $qm->m['plg_color']['err_usage'];

	// Invalid color
	foreach(array($color, $bgcolor) as $col){
		if ($col != '' && ! preg_match(PLUGIN_COLOR_REGEX, $col))
			return $qm->replace('plg_color.err_invalid_color', h($col));
	}

	if (PLUGIN_COLOR_ALLOW_CSS === TRUE || ! isset($pkwk_dtd) || $pkwk_dtd == PKWK_DTD_XHTML_1_1) {
		$delimiter = '';
		if ($color != '' && $bgcolor != '') $delimiter = '; ';
		if ($color   != '') $color   = 'color:' . $color;
		if ($bgcolor != '') $bgcolor = 'background-color:' . $bgcolor;
		return '<span style="' . $color . $delimiter . $bgcolor . '">' .
			$text . '</span>';
	} else if ($bgcolor != '') {
		return $qm->m['plg_color']['err_bgcolor'];
	} else {
		return '<font color="' . $color . '">' . $text . '</font>';
	}
}
?>
