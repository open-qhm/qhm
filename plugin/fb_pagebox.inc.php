<?php
/**
 *   Facebook LikeBox Plugin
 *   -------------------------------------------
 *   ./plugin/fb_likebox.inc.php
 *
 *   Copyright (c) 2015 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2015-06-11
 *   modified :
 *
 *   Put Facebook Page Plugin
 *
 *   Usage : #fb_pagebox
 *
 */

function plugin_fb_pagebox_init()
{
	if ( ! exist_plugin("fb_root"))
	{
		die('Fatal error: fb_root plugin not found');
	}
	do_plugin_init("fb_root");
}

function plugin_fb_pagebox_convert()
{
	global $script, $vars;
	$page = $vars['page'];
	$r_page = rawurlencode($page);
	$qm = get_qm();
	$qt = get_qt();
	$args = func_get_args();

	// scaffold
	$def_attrs = array(
		'href'          => '',
		'width'         => FALSE,
		'height'        => FALSE,
		'hide-cover'    => FALSE,
		'show-facepile' => TRUE,
		'show-posts'    => FALSE,
		'hide-cta'      => FALSE,
	);

	$attrs = plugin_fb_root_parse_args($args, $def_attrs);
	//no URL error
	if ($attrs['href'] === '')
	{
		$errmsg = 'error - #fb_pagebox: no facebook page url';
		return "<p>{$errmsg}</p>\n";
	}

	plugin_fb_root_set_jsapi(TRUE);
	return plugin_fb_root_create_tag('fb-page', $attrs);
}
