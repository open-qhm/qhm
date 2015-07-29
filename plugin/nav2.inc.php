<?php
/**
 *   QHM Nav2 plugin
 *   -------------------------------------------
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2014/08/28
 *   modified :
 *
 *   Switch Nav2 content and convert
 *
 *   Usage :
 *     #nav2(OtherSiteNavigator2)
 *
 */

function plugin_nav2_convert()
{
    global $vars;
    $nav2_page = 'SiteNavigator2';

    static $nav2 = NULL;
    $qm = get_qm();
    $qt = get_qt();

    $args = func_get_args();
    $from_footer = FALSE;
    if ($args[0] === '<from_footer_plugin>')
    {
        $from_footer = TRUE;
        array_shift($args);
    }

    $num = count($args);
    if ($num > 0) {
        if ($from_footer)
        {
            $plugin_name = 'footer';
            $parts_label = 'フッター';
        }
        else
        {
            $plugin_name = 'nav2';
            $parts_label = 'ナビ2';
        }
        // Try to change default 'SiteNavigator2' page name (only)
        if ($num > 1)       return '#'.$plugin_name.': 引数が多すぎます。' ."<br />\n";
        if ($nav2 !== NULL) return '#'.$plugin_name.': 既に変更されています：' . h($nav2) . "<br />\n";
        if ($qt->getv('plugin_nav2_source')) return '#'.$plugin_name.': 既に'.$parts_label.'の内容を変更しています。' . "<br />\n";

        //ナビの内容を受け取る
        if (strpos($args[0], "\r") !== FALSE)
        {
            if (isset($vars['page_alt'])) return '#'.$plugin_name.': 利用できません。';

            $source = str_replace("\r", "\n", $args[0]);
            $qt->setv_once('plugin_nav2_source', $source);
            return '';
        }
        if (! is_page($args[0])) {
            return 'ページがみつかりません：' . h($args[0]) . "<br />\n";
        } else {
            $nav2 = $args[0]; // Set
            return '';
        }

    } else {
        // Output nav2 page data
        $page = ($nav2 === NULL) ? $nav2_page : $nav2;

        if ($source = $qt->getv('plugin_nav2_source'))
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
            $nav2text = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', $vars['msg']);

            return convert_html($nav2text);
        } else {
            // Cut fixed anchors
            $nav2text = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', get_source($page));

            return convert_html($nav2text);
        }
    }
}
