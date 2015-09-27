<?php
/**
 *   Facebook LikeBox Plugin
 *   -------------------------------------------
 *   ./plugin/fb_likebox.inc.php
 *
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2011-09-02
 *   modified :
 *
 *   Put Facebook LikeBox
 *
 *   Usage : #fb_likebox
 *
 */

function plugin_fb_likebox_init()
{
	if ( ! exist_plugin("fb_root"))
	{
		die('Fatal error: fb_root plugin not found');
	}
	do_plugin_init("fb_root");
}

function plugin_fb_likebox_convert()
{
	if ($alt = plugin_fb_root_is_deprecated('likebox'))
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
		'href' => '',
		'width' => FALSE,
		'height' => FALSE,
		'colorscheme' => 'light',
		'show-faces' => 'true',
		'stream' => 'true',
		'header' => 'true',
		'force-wall' => 'false',
		'show-border' => 'true',
	);

	$attrs = plugin_fb_root_parse_args($args, $def_attrs);
	//no URL error
	if ($attrs['href'] == '')
	{
		$errmsg = 'error - #fb_likebox: no facebook page url';
		return "<p>{$errmsg}</p>\n";
	}

	plugin_fb_root_set_jsapi(TRUE);
	$body = plugin_fb_root_create_tag('fb-like-box', $attrs);

	if (edit_auth($page, FALSE, FALSE))
	{
		$fb_pagebox_help = h(QHM_HOME . '?FacebookPlugins#pagebox');
		$warning = <<< EOM
			<div class="alert alert-warning">
				Facebook Like Box は Graph API v2.3 より廃止されました。<br>
				<strong>2015 年 6 月 23 日</strong>に完全に使えなくなりますので、
				<code>#fb_pagebox</code> プラグインへの移行をしてください。<br>
				なお、このプラグインは <strong>2015 年 6 月 23 日</strong>に自動的に
				<code>#fb_pagebox</code> を利用するように切り替わります。<br>
				オプションは引き継ぎませんので、細かい設定を行いたい場合は
				<a href="{$fb_pagebox_help}">こちら</a>
				を参考に
				<code>#fb_pagebox</code> プラグインへ書き換えてください。
			</div>
EOM;
		$body = $warning . $body;

	}

	return $body;
}
