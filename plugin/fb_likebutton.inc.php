<?php
/**
 *   Facebook Like|Recommend Button Plugin
 *   -------------------------------------------
 *   ./plugin/fb_likebutton.inc.php
 *   
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2011-09-2
 *   modified :
 *   
 *   Put Facebook Like|Recommend Button
 *   
 *   Usage : #fb_likebutton(options)
 *   
 */

function plugin_fb_likebutton_init()
{
	if ( ! exist_plugin("fb_root"))
	{
		die('Fatal error: fb_root plugin not found');
	}
	do_plugin_init("fb_root");
}

function plugin_fb_likebutton_inline()
{
	$args = func_get_args();
	return plugin_fb_likebutton_body($args);
}

function plugin_fb_likebutton_convert()
{
	$args = func_get_args();
	return plugin_fb_likebutton_body($args);
}

function plugin_fb_likebutton_body($args)
{
	global $script, $vars, $defaultpage;
	
	$page = $vars['page'];
	$r_page = rawurlencode($page);
	$qm = get_qm();
	$qt = get_qt();
	
	$layouts = array('standard', 'button_count', 'box_count');
	$actions = array('like', 'recommend');

	// scaffold
	$def_attrs = array(
		'href' => '',
		'layout' => array('standard', $layouts),
		'show-faces' => 'true',
		'width' => FALSE,
		'colorscheme' => FALSE,
		'action' => array('like', $actions),
		'ref' => FALSE,
		'share' => 'true',
		'kid-directed-site' => FALSE,
	);
	
	$attrs = plugin_fb_root_parse_args($args, $def_attrs);
	
	//default URL set
	if ($attrs['href'] == '')
	{
	    if ($page === $defaultpage)
	    {
    	    $attrs['href'] = dirname($script . 'dummy');
	    }
	    else
	    {
    		$attrs['href'] = $script. '?'. $r_page;
	    }
	}
	
	plugin_fb_root_set_jsapi(TRUE);
	$tag = plugin_fb_root_create_tag('fb-like', $attrs);
	
	$body = $tag;
	
	return $body;
}
