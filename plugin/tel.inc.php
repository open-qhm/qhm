<?php
/**
 *   tel
 *   -------------------------------------------
 *   tel.inc.php
 *
 *   Copyright (c) 2015 hokuken
 *   http://hokuken.com/
 *
 *   created  : 15/06/25
 *   modified :
 *
 *   Description
 *
 *
 *   Usage : &tel(link_to[,type][,size][,design][,other]){label};
 *
 */
function plugin_tel_convert()
{
    $args   = func_get_args();
    $text   = strip_autolink(array_pop($args));

    return plugin_tel_body($args, $text);
}

function plugin_tel_inline()
{
    $args   = func_get_args();
    $text   = strip_autolink(array_pop($args));

    return plugin_tel_body($args, $text);
}

function plugin_tel_body($args, $text)
{
    global $script, $vars;

    $href = "";
    $class = "";

    if (count($args) > 0)
    {
        $tel = trim(array_shift($args));
        $href = "tel:{$tel}";

        $other = '';
        $type = '';
        $size = '';
        $block = '';
        $design = '';
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
                $design .= ' btn-round';
                break;
            case 'gradient':
            case 'ghost':
            case 'ghost-w':
                $design .= ' btn-'. $arg;
                break;
            default:
                $other .= ' ' . $arg;
            }
        }

        if ($type == '' && $size.$block.$design  != '')
        {
            $class = "btn btn-default{$size}{$block}{$design}";
        }
        else if ($type != '')
        {
            $class = "btn {$type}{$size}{$block}{$design}";
        }
    }
    else
    {
	    $href = "#";
    }

    $html = '<a href="'.h($href).'" class="'.h($class).h($other).'">'.$text.'</a>';

    return $html;
}
