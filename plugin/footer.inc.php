<?php
/**
 *   QHM Footer plugin (Alias of Nav2 plugin)
 *   -------------------------------------------
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2014/08/28
 *   modified :
 *
 *   Switch Footer content and convert
 *
 *   Usage :
 *     #footer(OtherSiteNavigator2)
 *
 */

function plugin_footer_init()
{
    if ( ! exist_plugin('nav2'))
    {
        die('<strong>Fatal error:</strong><br>Cannot find nav2 plugin');
    }
}

function plugin_footer_convert()
{
    $args = func_get_args();
    array_unshift($args, '<from_footer_plugin>');

    return call_user_func_array('plugin_nav2_convert', $args);
}
