<?php
/**
 *   Bootstrap Thumbnails Plugin
 *   -------------------------------------------
 *   /plugin/thumbnails.inc.php
 *   
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 14/06/10
 *   modified :
 *   
 *   Description
 *   
 *   Usage :
 *   
 */

function plugin_thumbnails_convert()
{

	if ( ! exist_plugin('cols'))
	{
		return '';
	}

	plugin_cols_type('set', 'thumbnails');

	$args = func_get_args();
	$body = call_user_func_array('plugin_cols_convert', $args);

	plugin_cols_type('set', 'normal');

	return $body;
}
