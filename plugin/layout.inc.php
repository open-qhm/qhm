<?php
/**
 *   Switch Design Layout Plugin
 *   -------------------------------------------
 *   plugin/layout.inc.php
 *   
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2014-05-21
 *   modified :
 *   
 *   指定したレイアウトテンプレートを使用します。
 *   
 *   Usage :
 *     #layout(template_name)
 */

function plugin_layout_convert()
{
    global $vars, $include_skin_file_path, $style_name;
    $qm = get_qm();
    $qt = get_qt();

    $args = func_get_args();
    if (count($args)<1)
    {
        return $qm->replace('必ずレイアウト名を指定してください。');
    }

    $template_name = array_pop($args);
    $qt->setv('layout_name', $template_name);

    return '';
}
