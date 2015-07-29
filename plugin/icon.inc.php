<?php
/**
 *   Haik Icon Plugin
 *   -------------------------------------------
 *   plugin/icon.inc.php
 *
 *   Copyright (c) 2013 hokuken
 *   http://hokuken.com/
 *
 *   created  : 13/01/29
 *   modified :
 *
 *   Description
 *
 *   Usage :
 *
 */

function plugin_icon_inline()
{
	$args = func_get_args();

	$class = '';
	$icon_base = 'glyphicon';
	$icon_prefix = $icon_base . '-';
	$icon_name = $icon_options = '';

	foreach ($args as $arg)
	{
		if ($arg === 'glyphicon')
		{
			$icon_base = 'glyphicon';
			$icon_prefix = $icon_base . '-';
		}
		if ($arg === 'font-awesome' OR $arg === 'fa')
		{
    		$icon_base = 'fa';
    		$icon_prefix = $icon_base . '-';
    		plugin_icon_set_font_awesome();
		}
		else if ($icon_base === 'fa' && preg_match('/^[1-5]x|lg$/', $arg))
		{
    		$icon_options = " {$icon_prefix}{$arg}";
		}
		else if ($arg !== '')
		{
			$icon_name = $arg;
		}
	}

	$icon_name = $icon_prefix.$icon_name;

	$format = '<i class="%s %s%s"></i>';
	return sprintf($format, h($icon_base), h($icon_name), $icon_options);
}

function plugin_icon_set_font_awesome()
{
    $qt = get_qt();
    $addcss = '
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
';
    $qt->appendv_once('plugin_icon_font_awesome', 'beforescript', $addcss);
}
