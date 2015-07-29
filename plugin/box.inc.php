<?php
// $Id$

function plugin_box_convert()
{
    $args = func_get_args();
    $body = array_pop( $args );
    $argsnum = count( $args );
    $qm = get_qm();
    $qt = get_qt();

    if($argsnum != 5){
        return $qm->replace('fmt_err_cvt', 'box', $qm->m['plg_box']['err_usage']);
    }

    $border_color = $args[0];
    $border_width = $args[1];
    $border_style = $args[2];
    $bgcolor = $args[3];
    $width = $args[4];



    //Convert multi-line args to HTML
    if (isset($body)) {
        $body = str_replace("\r", "\n", str_replace("\r\n", "\n", $body));
        $lines = explode("\n", $body);
        $body = convert_html($lines);
    } else {
        $body = '';
    }

    $style = <<< EOD
<style type="text/css">
.qhm-box-plugin {
  box-sizing: content-box;
}
</style>
EOD;

    $qt->appendv_once("plugin_box_plugin_style", "beforescript", $style);

    $ret = '<div class="ie5">';
    $ret .= '<div class="qhm-box-plugin qhm-block"';
    $ret .= ' style="';
    $ret .= 'border:'.$border_color.' '.$border_width.' '.$border_style.';';
    $ret .= 'background-color:' . $bgcolor .';';
    $ret .= 'max-width:' . $width . ';' . 'width:'.$width.';';
    $ret .= 'padding:3px 15px;margin:5px auto;text-align:left';
    $ret .= '">';
    $ret .= $body;
    $ret .= '</div>';
    $ret .= '</div>';

    return $ret;
}
