<?php
// $Id: blog.inc.php,v 0.01 2008/07/09 16:26:34 edo Exp $
//
// Blog plugin
// based on Calendar2 plugin
//
// Usage:
//	#blog({[pagename|*],[yyyymm]})


function plugin_blog_rss_inline()
{
	global $script, $vars;
	$qm = get_qm();

	$page = $vars['page'];
	$url = $script.'?cmd=rss&blog_rss='.rawurlencode($page);

	$args = func_get_args();
	if(count($args) > 0)
	{
		$text = $args[0];
	}

	if($text == '')
	{
		$text = '<img src="image/rss.png" title="rss" alt="rss" />';
	}

	return '<a href="'.$url.'">'.$text.'</a>';
}

?>
