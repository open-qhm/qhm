<?php
/**
 *   label
 *   -------------------------------------------
 *   label.inc.php
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 14/06/09
 *   modified : 
 *
 *   Description
 *   
 *   
 *   Usage : &label([type][classname]){label};
 *   
 */
function plugin_label_inline()
{
    $args   = func_get_args();
    $text   = strip_autolink(array_pop($args));

    return plugin_label_body($args, $text);
}

function plugin_label_body($args, $text)
{
    global $script, $vars;
    

    $type = ' label-default';
    $class = '';

    foreach($args as $arg)
    {
        $arg = trim($arg);
        switch($arg){
        case 'primary':
        case 'info':
        case 'success':
        case 'warning':
        case 'danger':
        case 'default':
            $type = ' label-'.$arg;
            break;
        default:
            $class .= ' '.$arg;
        }
    }
    
    $html = '<span class="label'.$type.$class.'">'.$text.'</span>';
    
    return $html;
}
