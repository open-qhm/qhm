<?php
/**
*   Box Plugin
*   -------------------------------------------
*   plugin/bs_box.inc.php
*
*   Copyright (c) 2014 hokuken
*   http://hokuken.com/
*
*   created  : 14/06/02
*   modified :
*
*   Bootstrap に準拠した枠を出力する。
*   対応しているのは、alert 系、well 系、標準 panel、jumbotron。
*   jumbotron に関しては Bootstrap 2 系のhero-unit を継承しているため、hero 指定でも使えるようにしている。
*   close と指定することで、閉じるボタンを追加できる。
*   何故か、alert 以外でも動く。
*
*
*   Usage :
*     #box(success){{...}} // .alert.alert-success
*     #box{{...}} // .well
*     #box(danger,close){{...}} // .alert.alert-danger close ボタン付き
*
*/
function plugin_bs_box_convert()
{
    $args = func_get_args();
    $body = trim(array_pop($args));

    $close = FALSE;
    $type = 'well';
    $color = '';
    $size = '';

    $wrapper = array('', '');
    $use_wrapper = FALSE;
    $outer = array('', '');

    $height = FALSE;
    $cols = 12;//full width
    $offset = 0;
    $style = '';
    $attr_class = '';

    foreach ($args as $arg)
    {
        $arg = trim($arg);
        switch($arg)
        {
            case 'close':
                $close = TRUE;
                break;
            case 'alert':
                $type = 'alert';
                break;
            case 'panel':
                $type = 'panel';
                $wrapper = array(
                    '<div class="panel-body qhm-bs-block">',
                    '</div>');
                $use_wrapper = TRUE;
                break;
            case 'well':
                $type = 'well';
                break;
            case 'default':
            case 'danger':
            case 'success':
            case 'info':
            case 'warning':
            case 'primary':
                $color = $arg;
                break;
            case 'hero':
            case 'hero-unit':
            case 'jumbotron':
                $type = 'jumbotron';
                break;
            case 'large':
            case 'lg':
                $size = 'lg';
                break;
            case 'small':
            case 'sm':
                $size = 'sm';
                break;
            default:
                //col, offset option
                if (preg_match('/^(\d+)(?:\+(\d+))?$/', $arg, $mts))
                {
                    $cols = $mts[1];
                    $offset = isset($mts[2]) && $mts[2] ? $mts[2] : $offset;
                }
                else if (preg_match('/\Aheight=(.+)\z/', $arg, $mts))
                {
                    $height = $mts[1];
                    if (is_numeric($height)) $height .= 'px';
                }
                else if (preg_match('/\Aclass=(.+)\z/', $arg, $mts))
                {
                    $attr_class = $mts[1];
                }
                else if (preg_match('/\Astyle=(.+)\z/', $arg, $mts))
                {
                    $style .= $mts[1];
                }
        }
    }

    //幅の変更があったら .col-sm-X で囲む
    if ($cols != 12)
    {
        $offset_class = '';
        if ($offset) $offset_class = 'col-sm-offset-' . $offset;
        $outer = array('<div class="row"><div class="col-sm-'.$cols.' '. $offset_class .'">', '</div></div>');
    }

    $type_class = array($type);

    if ($type === 'alert' || $type === 'panel')
    {
        if ($type === 'alert' && ($color ===  '' || $color === 'default' || $color === 'primary'))
        {
            $color = 'warning';
        }
        if ($type === 'panel' && $color === '')
        {
            $color = 'default';
        }
        $type_class[] = "{$type}-{$color}";
    }

    if ($type === 'well')
    {
        if ($size != '')
        {
            $type_class[] = "{$type}-{$size}";
        }
    }

    $close = ($type === 'alert' && $close) ? '<button type="button" class="close" data-dismiss="alert">&times;</button>' : '';

    $scroll_style = $height ? 'max-height:'.h($height).';overflow-y:scroll;' : '';
    $scroll_style = $scroll_style.$style;

    $body = str_replace("\r", "\n", str_replace("\r\n", "\n", $body));
    $lines = explode("\n", $body);
    $body = convert_html($lines);

    $type_class = array_filter($type_class);
    $type_class = join(' ', $type_class);
    $type_class = trim($attr_class) != '' ? ($type_class. ' ' . $attr_class) : $type_class;

    $block_class = $use_wrapper ? '' : ' qhm-bs-block';


    $html = <<<EOD
{$outer[0]}
    <div class="qhm-bs-box {$type_class}{$block_class}" style="{$scroll_style}">
        {$wrapper[0]}
            {$close}
            {$body}
        {$wrapper[1]}
    </div>
{$outer[1]}
EOD;

    return $html;
}
