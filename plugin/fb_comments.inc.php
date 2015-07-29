<?php
/**
 *   Facebook Comments Plugin
 *   -------------------------------------------
 *   ./plugin/fb_comments.inc.php
 *   
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2011-09-02
 *   modified :
 *   
 *   Put Facebook Comments
 *   
 *   Usage : #fb_comments
 *   
 */

function plugin_fb_comments_init()
{
	if ( ! exist_plugin("fb_root"))
	{
		die('Fatal error: fb_root plugin not found');
	}
	do_plugin_init("fb_root");
}

function plugin_fb_comments_convert()
{
	global $script, $vars;
	$page = $vars['page'];
	$r_page = rawurlencode($page);
	$qm = get_qm();
	$qt = get_qt();
	$args = func_get_args();

	if ( ! exist_plugin("fb_root"))
	{
		die('Fatal error: fb_root plugin not found');
	}

	$orders = array('social', 'reverse_time', 'time');

	// scaffold
	$def_attrs = array(
		'href' => '',
		'width' => FALSE,
		'numposts' => '2',// in arg: num
		'colorscheme' => 'light',
		'mobile' => FALSE,
		'order-by' => array(FALSE, $orders),
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
	$tag = plugin_fb_root_create_tag('fb-comments', $attrs);

	$body = $tag;

	return $tag;
}
