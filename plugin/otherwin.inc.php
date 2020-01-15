<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// otherwin.inc.php 別ウインドウで開くリンクを作る
//
// Text otherwin plugin

// Allow CSS instead of <font> tag
// NOTE: <font> tag become invalid from XHTML 1.1
define('PLUGIN_OTHERWIN_ALLOW_CSS', TRUE); // TRUE, FALSE

// ----
define('PLUGIN_OTHERWIN_USAGE', '&othewin(url[,winname]){text};');
define('PLUGIN_OTHERWIN_REGEX', '/^(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z-]+)$/i');

function plugin_otherwin_inline()
{
	global $pkwk_dtd;
    
	$args = func_get_args();
	$text = strip_autolink(array_pop($args)); // Already htmlspecialchars(text)
    
	list($url, $target) = array_pad($args, 2, '');
	if (($url == '' && $target == '') || $text == '' || func_num_args() > 3)
		return PLUGIN_OTHERWIN_USAGE;
    
	$url = is_url($url) ? $url : 'index.php?'.rawurlencode($url);
	$target = ($target == '') ? '_blank' : $target;
	$rel = $target === '_blank' ? 'rel="noopener"' : '';
    
	$ret = '<a href="' . $url . '" target="'. $target . '" '. $rel .'>' . $text . '</a>';
	
	return $ret;
}
?>
