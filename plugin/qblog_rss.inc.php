<?php
/**
 *   qblog_rss
 *   -------------------------------------------
 *   qblog_rss.inc.php
 *
 *   Copyright (c) 2012 hokuken
 *   http://hokuken.com/
 *
 *   created  : 12/08/07
 *   modified :
 *
 *   Description
 *   
 *   
 *   Usage :
 *   
 */
function plugin_qblog_rss_inline()
{
	global $script, $vars;
	$qm = get_qm();

	$page = $vars['page'];
	$url = $script.'?cmd=rss&qblog_rss=1';

	$args = func_get_args();
	if(count($args) > 0)
	{
		$text = $args[0];
	}

	if($text == '')
	{
		$text = '<img src="image/rss.png" title="rss" alt="rss" />';
	}

	return '<a href="'.h($url).'">'.$text.'</a>';

}

?>