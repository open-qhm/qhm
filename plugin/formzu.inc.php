<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: formzu.inc.php,v 0.5 2006/06/26 15:04:08 henoheno Exp $
//
// Formzu inline view plugin

// Allow CSS instead of <font> tag
// NOTE: <font> tag become invalid from XHTML 1.1
define('PLUGIN_FORMZU_ALLOW_CSS', TRUE); // TRUE, FALSE

function plugin_formzu_convert()
{
	global $pkwk_dtd;
	$qm = get_qm();

	$args = func_get_args();
	$args_cnt = count( $args );
	list($formurl, $hight, $width, $align) = array_pad($args, 4, '');

	if ($args_cnt < 3 || $args_cnt > 4) {  //correct args
		return $qm->replace('fmt_err_cvt', 'formzu', $qm->m['plg_formzu']['err_usage']);
	}
	if ($args_cnt == 3) {
		$align = 'center'; 
	}

	if (PLUGIN_FORMZU_ALLOW_CSS === TRUE || ! isset($pkwk_dtd) || $pkwk_dtd == PKWK_DTD_XHTML_1_1) {
		return '<div style="text-align:' .$align. '"><iframe src="' . $formurl . '" frameborder="0" height="' . $hight . '" width="' . $width . '" style="margin:0px;text-align:' . $align . ';">'. $qm->replace('plg_formzu.ntc', $formurl). '</iframe></div>';
	} else {
		return $qm->m['plg_formzu']['err_invalid_arg'];
	}
}
?>
