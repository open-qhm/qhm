<?php
// $Id$

function plugin_style2_convert()
{
    $args = func_get_args();
    $last = func_num_args() - 1;
    if (strpos($args[$last], 'style=') === 0) {
    } elseif (strpos($args[$last], 'class=') === 0) {
    } elseif ($args[$last] == "L") {
    } elseif ($args[$last] == "R") {
    } else {
        $body = array_pop($args);
    }
    $options = array();
    foreach ($args as $arg) {
        list($key, $val) = explode('=', $arg, 2);
        $options[$key] = htmlspecialchars($val);
    }

    if (isset($body)) {
        $body = str_replace("\r", "\n", str_replace("\r\n", "\n", $body));
        $lines = explode("\n", $body);
        $body = convert_html($lines);
    } else {
        $body = '';
    }

    $colstyle = "";
    if (isset($options['L'])) {
        $colstyle = "width:45%;float:left;text-align:left;";
    }
    if (isset($options['R'])) {
        $colstyle = "width:45%;float:left;text-align:left;margin-left:10px;";
    }
    if (isset($options['AL'])) {
        $colstyle = "float:left;text-align:left;margin-right:1em;";
    }
    if (isset($options['AR'])) {
        $colstyle = "float:right;text-align:left;margin-left:1em;";
    }

    $style = "";
    if ($colstyle != "" || isset($options['style'])) {
        $style  = ' style="' . $colstyle;
        $style .= isset($options['style']) ? $options['style'] : '';
        $style .= '"';
    }

    $ret = '<div';
    $ret .= isset($options['class']) ? ' class="' . $options['class'] . ' qhm-block"' : '';
    $ret .= $style;
    $ret .= '>';
    $ret .= $body;
    $ret .= '</div>';
    return $ret;
}
