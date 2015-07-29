<?php
/**
 *   QHM Header plugin
 *   -------------------------------------------
 *
 *   Copyright (c) 2015 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2014/07/10
 *   modified :
 *
 *   Switch Header content and convert
 *
 *   Usage :
 *     #nav2(OtherSiteHeader)
 *
 */

function plugin_header_convert()
{
    global $vars;
    $header_page = 'SiteHeader';

    static $header = NULL;
    $qm = get_qm();
    $qt = get_qt();

    $args = func_get_args();
    $num = count($args);

    $plugin_name = 'header';
    $parts_label = 'ヘッダー';
    if ($num > 0) {
        // Try to change default 'SiteHeader' page name (only)
        if ($num > 1)       return '#'.$plugin_name.': 引数が多すぎます。' ."<br />\n";
        if ($header !== NULL) return '#'.$plugin_name.': 既に変更されています：' . h($header) . "<br />\n";
        if ($qt->getv('plugin_header_source')) return '#'.$plugin_name.': 既に'.$parts_label.'の内容を変更しています。' . "<br />\n";

        //ナビの内容を受け取る
        if (strpos($args[0], "\r") !== FALSE)
        {
            if (isset($vars['page_alt'])) return '#'.$plugin_name.': 利用できません。';

            $source = str_replace("\r", "\n", $args[0]);
            $qt->setv_once('plugin_header_source', $source);
            return '';
        }
        if (! is_page($args[0])) {
            return 'ページがみつかりません：' . h($args[0]) . "<br />\n";
        } else {
            $header = $args[0]; // Set
            return '';
        }

    } else {
        // Output nav2 page data
        $page = ($header === NULL) ? $header_page : $header;

        if ($source = $qt->getv('plugin_header_source'))
        {
            // Cut fixed anchors
            $source = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', $source);

            return convert_html($source);
        }
        if (! is_page($page)) {
            return '';
        }
        else if (isset($vars['preview']) && $vars['page'] == $page)
        {
            // Cut fixed anchors
            $headertext = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', $vars['msg']);

            return convert_html($headertext);
        } else {
            // Cut fixed anchors
            $headertext = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', get_source($page));

            return convert_html($headertext);
        }
    }
}
