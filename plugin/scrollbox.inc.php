<?php
/**
 *   QHM Scroll Box Plugin
 *   -------------------------------------------
 *   plugin/scrollbox.inc.php
 *   
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *   
 *   Usage :
 *   
 */

function plugin_scrollbox_convert()
{
    $args = func_get_args();
    $last = func_num_args() - 1;

    if (strpos($args[$last], 'style=') === 0) {
    } elseif (strpos($args[$last], 'class=') === 0) {
    } else {
        $body = array_pop($args);
    }
	
	list($w,$h,$option) = array_pad($args,3,'');
	$w = ($w == '') ? '100%' : $w;
	$h = ($h == '') ? '200px' : $h;
	$option = ($option == '') ? 'overflow:auto;border:1px solid #dcdcdc;padding:5px 10px;margin-left:auto;margin-right:auto;text-align:justify;' : $option;
	$option = h($option);
	
    if (isset($body)) {
        $body = str_replace("\r", "\n", str_replace("\r\n", "\n", $body));
        $lines = explode("\n", $body);
        $body = convert_html($lines);
    } else {
        $body = '';
    }
	
	$ret = '<div style= "';
	$ret .= 'width:'.$w.';';
	$ret .= 'height:'.$h.';';
	$ret .= $option.';"';
    $ret .= '>';
    $ret .= $body;
    $ret .= '</div>';
    return $ret;
}
?>
