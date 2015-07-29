<?php
/**
 *   Table of Contents
 *   -------------------------------------------
 *   /plugin/contents.inc.php
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 14/08/25
 *   modified :
 *
 */

define('PLUGIN_CONTENTS_DEFAULT_MIN_LEVEL', 1);
define('PLUGIN_CONTENTS_DEFAULT_MAX_LEVEL', 4);
define('PLUGIN_CONTENTS_QHM_TARGET_SELECTOR', '#body');
define('PLUGIN_CONTENTS_HAIK_TARGET_SELECTOR', '[role=main]');
define('PLUGIN_CONTENTS_DEFAULT_FLAT_FLAG', FALSE);
define('PLUGIN_CONTENTS_DEFAULT_IGNORE_CLASS', 'no-toc');

function plugin_contents_convert()
{
    $qt = get_qt();
    $qt->setv('jquery_include', TRUE);

    $qt->setjsv('enable_toc', TRUE);

    $args = func_get_args();

    // min level of captured headings
    $min_level  = PLUGIN_CONTENTS_DEFAULT_MIN_LEVEL;
    $max_level = PLUGIN_CONTENTS_DEFAULT_MAX_LEVEL;

    // target for capturing headings
    // targets array is for fallback when cannot select
    $targets = array(
        PLUGIN_CONTENTS_HAIK_TARGET_SELECTOR,
        PLUGIN_CONTENTS_QHM_TARGET_SELECTOR,
        'body',
    );
    $custom_target = FALSE;

    // ignoring when capturing
    $ignore_class = PLUGIN_CONTENTS_DEFAULT_IGNORE_CLASS;

    // custom element selector
    $custom_selector = FALSE;

    // show flat table of contents
    $flat = PLUGIN_CONTENTS_DEFAULT_FLAT_FLAG;

    // add class name for navbar
    $nav = FALSE;
    $nav_right = FALSE;

    $title  = '';

    // selected element option
    $el_html_content = FALSE;
    $el_use_title = TRUE;

    $class_attr = 'plugin-contents';

    foreach ($args as $arg)
    {
        $arg = trim($arg);
        switch ($arg)
        {
            case 'flat':
                $flat = TRUE;
                break;
            case 'navright':
                $nav_right = TRUE;
            case 'nav':
                $flat = TRUE;
                $nav = TRUE;
                break;
            case 'html':
                $el_html_content = TRUE;
                break;
            case 'noalttext':
                $el_use_title = FALSE;
                break;
            case (preg_match('/\A(\d)[:-](\d)\z/', $arg, $mts) ? TRUE : FALSE):
                $min_level = $mts[1];
                $max_level = $mts[2];
                break;
            case (preg_match('/\Atarget=(.+)/', $arg, $mts) ? TRUE : FALSE):
                $custom_target = $mts[1];
                break;
            case (preg_match('/\Aselector=(.+)/', $arg, $mts) ? TRUE : FALSE):
                $custom_selector = $mts[1];
                break;
            case 'legacy':
                return plugin_contents_legacy_convert();
            default:
                $title = $arg;
        }
    }

    if ($custom_target)
    {
        $targets = array($custom_target);
    }

    if ($custom_selector)
    {
        $selector = $custom_selector;
    }
    else
    {
        $selector = plugin_contents_create_selector($min_level, $max_level);
    }

    $selector_attr = h($selector);
    $target_attr = h(json_encode($targets));
    $ignore_attr = h($ignore_class);
    $title_attr = h($title);
    $flat_attr = $flat ? 1 : 0;
    $el_title_attr = $el_html_content ? 'html' : 'text';
    $use_title_attr = $el_use_title ? 1 : 0;
    $exclude_attr = '.qhm-eyecatch';
    $custom_class = $nav ? 'nav navbar-nav' : '';
    $custom_class .= $nav_right ? ' navbar-right' : '';

    return <<< EOD
<div class="contents">
    <nav class="{$class_attr}"
      data-selector="{$selector_attr}"
      data-target="{$target_attr}"
      data-ignore="{$ignore_attr}"
      data-flat="{$flat_attr}"
      data-title="{$title_attr}"
      data-element-title-content="{$el_title_attr}"
      data-element-use-title-attr="{$use_title_attr}"
      data-element-is-not-descendant-of="{$exclude_attr}"
      data-custom-class="{$custom_class}"></nav>
</div>
EOD;

}

function plugin_contents_create_selector($min_level, $max_level)
{
    if ($min_level > $max_level)
    {
        list($min_level, $max_level) = array($max_level, $min_level);
    }

    $selectors = array();
    $span = range($min_level, $max_level);
    foreach ($span as $level)
    {
        $selectors[] = 'h' . $level;
    }
    return join(',', $selectors);
}

function plugin_contents_legacy_convert()
{
    // This character string is substituted later.
    return '<#_contents_>';
}
