<?php
/**
 *   Bootstrap Panel Plugin
 *   -------------------------------------------
 *   plugin/panel.inc.php
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 14/06/10
 *   modified :
 *
 *   @see: http://getbootstrap.com/components/#panels
 *
 *   Usage :
 *     #panel(primary){{
 *     * Panel Header
 *     ====
 *     Panel Contents
 *     ====
 *     Panel footer
 *     }}
 *
 */
function plugin_panel_convert()
{
    $qm = get_qm();
    $qt = get_qt();

    $args   = func_get_args();
    $body   = array_pop($args);

    $msg = '';
    $delim = "\r====\r";

    $notitle = FALSE;
    $panel_type = 'default';
    foreach($args as $arg)
    {
        switch ($arg)
        {
            case 'primary':
            case 'success':
            case 'info':
            case 'warning':
            case 'danger':
            case 'default':
              	$panel_type = $arg;
              	break;
            case 'notitle':
              	$notitle = TRUE;
              	break;
        }
    }

    $data = explode($delim, $body, 3);
    $data_length = count($data);
    $header = $body = $footer = FALSE;

    // header, body, footer
    if ($data_length > 1)
    {
        if ($data_length == 2)
        {
            if ($notitle)
            {
                list($body, $footer) = array_pad($data, 2, '');
            }
            else
            {
                list($header, $body) = array_pad($data, 2, '');
            }
        }
        else
        {
            list($header, $body, $footer) = array_pad($data, 3, '');
        }
    }
    // body
    else
    {
        $body = $data[0];
    }

    $header_fmt = '<div class="panel-heading">%s</div>' . "\n";
    $body_fmt   = '<div class="panel-body qhm-bs-block">%s</div>' . "\n";
    $footer_fmt = '<div class="panel-footer">%s</div>' . "\n";

    $html = '<div class="qhm-plugin-panel panel panel-'.h($panel_type).'">' . "\n";

    if ($header !== FALSE)
    {
        $header = str_replace("\r", "\n", str_replace("\r\n", "\n", $header));
        $lines = explode("\n", $header);
        $header_html = convert_html($lines, TRUE);
        $header_html = preg_replace('/<h([1-7]) /', '<h\1 class="panel-title" ', $header_html);
        $html .= sprintf($header_fmt, $header_html);
    }

    $body = str_replace("\r", "\n", str_replace("\r\n", "\n", $body));
    $lines = explode("\n", $body);
    $html .= sprintf($body_fmt, convert_html($lines));

    if ($footer !== FALSE)
    {
        $footer = str_replace("\r", "\n", str_replace("\r\n", "\n", $footer));
        $lines = explode("\n", $footer);
        $html .= sprintf($footer_fmt, convert_html($lines, TRUE));
    }
  	$html .= '</div>' . "\n";

    return $html;
}
