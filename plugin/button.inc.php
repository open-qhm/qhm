<?php
/**
 *   button
 *   -------------------------------------------
 *   button.inc.php
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 14/05/30
 *   modified : 14/06/05
 *
 *   Description
 *
 *
 *   Usage : &button(link_to[,type][,size]){label};
 *
 */
function plugin_button_convert()
{
    $args   = func_get_args();
    $text   = strip_autolink(array_pop($args));

    return plugin_button_body($args, $text);
}

function plugin_button_inline()
{
    $args   = func_get_args();
    $text   = strip_autolink(array_pop($args));

    return plugin_button_body($args, $text);
}

function plugin_button_body($args, $text)
{
    global $script, $vars;


    $type = ' btn-default';
    $size = '';
    $block = '';
    $class = '';

    if (count($args) > 0)
    {
        $href = array_shift($args);

        if (is_page($href))
        {
            $href = $script.'?'.rawurlencode($href);
        }
        //存在しないページ
        else if ( ! is_url($href) && is_pagename($href))
        {
            $href = $script . '?cmd=edit&page=' . rawurlencode($href);
        }

        foreach($args as $arg)
        {
            $arg = trim($arg);
            switch($arg){
            case 'primary':
            case 'info':
            case 'success':
            case 'warning':
            case 'danger':
            case 'link':
            case 'default':
                $type = ' btn-'.$arg;
                break;
            case 'large':
            case 'lg':
                $size = ' btn-lg';
                break;
            case 'small':
            case 'sm':
                $size = ' btn-sm';
                break;
            case 'mini':
            case 'xs':
                $size = ' btn-xs';
                break;
            case 'block':
                $block = ' btn-'.$arg;
                break;
            case 'round':
            case 'rounded':
                $class .= ' btn-round';
                break;
            case 'gradient':
            case 'ghost':
            case 'ghost-w':
                $class .= ' btn-'. $arg;
                break;
            default:
                $class .= ' '.$arg;
            }
        }
    }
    else
    {
        $href = "#";
    }

    $html = '<a class="btn'.$type.$block.$size.$class.'" href="'.h($href).'">'.$text.'</a>';

    return $html;
}
