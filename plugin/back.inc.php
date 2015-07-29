<?php
// $Id: back.inc.php,v 1.9 2005/06/20 14:57:34 henoheno Exp $
// Copyright (C)
//   2003-2004 PukiWiki Developers Team
//   2002      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
//
// back plugin

// Allow specifying back link by page name and anchor, or
// by relative or site-abusolute path
define('PLUGIN_BACK_ALLOW_PAGELINK', PKWK_SAFE_MODE); // FALSE(Compat), TRUE

// Allow JavaScript (Compat)
define('PLUGIN_BACK_ALLOW_JAVASCRIPT', TRUE); // TRUE(Compat), FALSE, PKWK_ALLOW_JAVASCRIPT

function plugin_back_convert()
{
	global $script;
	$qm = get_qm();

	if (func_num_args() > 4) return $qm->replace('fmt_err_cvt', 'back', $qm->m['err_usage']);
	list($word, $align, $hr, $href) = array_pad(func_get_args(), 4, '');

	$word = trim($word);
	$word = ($word == '') ? $qm->m['plg_back']['back_word'] : htmlspecialchars($word);

	$align = strtolower(trim($align));
	switch($align){
	case ''      : $align = 'center';
	               /*FALLTHROUGH*/
	case 'center': /*FALLTHROUGH*/
	case 'left'  : /*FALLTHROUGH*/
	case 'right' : break;
	default      : return $qm->replace('fmt_err_cvt', 'back', $qm->m['err_usage']);
	}

	$hr = (trim($hr) != '0') ? '<hr class="full_hr" />' . "\n" : '';

	$link = TRUE;
	$href = trim($href);
	if ($href != '') {
		if (PLUGIN_BACK_ALLOW_PAGELINK) {
			if (is_url($href)) {
				$href = rawurlencode($href);
			} else {
				$array = anchor_explode($href);
				$array[0] = rawurlencode($array[0]);
				$array[1] = ($array[1] != '') ? '#' . rawurlencode($array[1]) : '';
				$href = $script . '?' . $array[0] .  $array[1];
				$link = is_page($array[0]);
			}
		} else {
			$href = rawurlencode($href);
		}
	} else {
		if (! PLUGIN_BACK_ALLOW_JAVASCRIPT)
			return $qm->replace('fmt_err_cvt', 'back', $qm->m['err_usage']) . ': '. $qm->m['plg_back']['usage_hint'];
		$href  = 'javascript:history.go(-1)';
	}

	if($link){
		// Normal link
		return $hr . '<div style="text-align:' . $align . '">' .
			'[ <a href="' . $href . '">' . $word . '</a> ]</div>' . "\n";
	} else {
		// Dangling link
		return $hr . '<div style="text-align:' . $align . '">' .
			'[ <span class="noexists">' . $word . '<a href="' . $href .
			'">?</a></span> ]</div>' . "\n";
	}
}
?>
