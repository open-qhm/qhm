<?php
/**
 *   Facebook Recommendations Plugin
 *   -------------------------------------------
 *   ./plugin/fb_recommends.inc.php
 *
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2011-09-02
 *   modified :
 *
 *   Put Facebook Recommendations
 *
 *   Usage : #fb_recommends
 *
 */

function plugin_fb_recommends_init()
{
	if ( ! exist_plugin("fb_root"))
	{
		die('Fatal error: fb_root plugin not found');
	}
	do_plugin_init("fb_root");
}


function plugin_fb_recommends_convert()
{
	if ($alt = plugin_fb_root_is_deprecated('recommends'))
	{
		$args = func_get_args();
		return call_user_func_array($alt, $args);
	}

	global $script, $vars;
	$page = $vars['page'];
	$r_page = rawurlencode($page);
	$qm = get_qm();
	$qt = get_qt();
	$args = func_get_args();

	// scaffold
	$def_attrs = array(
		'site' => '',
		'width' => FALSE,
		'height' => FALSE,
		'header' => 'true',
		'colorscheme' => 'light',
		'ref' => FALSE,
		'action' => 'og.likes',
		'app-id' => FALSE,
		'linktarget' => FALSE,
		'max-age' => FALSE,
	);

	$attrs = plugin_fb_root_parse_args($args, $def_attrs);
	//default site set
	if ($attrs['site'] == '')
	{
		$parsed = parse_url($script);
		$host = $parsed['host'];
		$attrs['site'] = $host;
	}

	plugin_fb_root_set_jsapi(TRUE);
	$body = plugin_fb_root_create_tag('fb-recommendations', $attrs);

	if (edit_auth($page, FALSE, FALSE))
	{
		$fb_pagebox_help = h(QHM_HOME . '?PageName');
		$warning = <<< EOM
			<div class="alert alert-warning">
				Facebook Recommends は Graph API v2.3 より廃止されました。<br>
				<strong>2015 年 6 月 23 日</strong>に完全に使えなくなります。
			</div>
EOM;
		$body = $warning . $body;

	}

	return $body;
}
