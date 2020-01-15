<?php
/**
 *   Canonical URL modifier Plugin
 *   -------------------------------------------
 *   /haik-contents/plugin/canonical.inc.php
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 14/12/10
 *   modified :
 *
 *   ページの canonical URL を任意のものに設定できる
 *
 *   Usage :
 *   #canonical(URL or pagename or path)
 *
 */

function plugin_canonical_convert()
{
    global $script, $vars;

    // Deny the call at layout pages
    if (isset($vars['page_alt']))
    {
        return;
    }

    $qt = get_qt();
    if (func_num_args() === 0) return;

    $target = func_get_arg(0);

    // URL
    if (is_url($target))
    {
        $canonical_url = $target;
    }
    // Pagename
    else if (is_page($target))
    {
        $canonical_url = $script . '?' . rawurlencode($target);
    }
    // Relative Path
    else
    {
        $base_dir = dirname($script . 'dummy');
        $canonical_url = $base_dir . '/' . $target;
    }

    $qt->setv('canonical_url', $canonical_url);

    return plugin_canonical_show_info();
}

function plugin_canonical_show_info()
{
    global $vars, $style_name;
    $qt = get_qt();
    $page = $vars['page'];

    if (edit_auth($page, FALSE, FALSE))
    {
        if (strpos($style_name, 'haik_') !== 0)
        {
            return '
<div style="border:solid 1px #00e;background-color:#eef;max-width:80%;width:80%;text-align:left;padding:0 1.5em;margin:1em auto;">
  <p>
    <strong>このページは canonical URL が指定されています。</strong><br>
    <a href="'.h($qt->getv('canonical_url')).'" target="_blank" rel="noopener">確認</a>
  </p>
</div>';
        }
        else
        {
            return '
<div class="alert alert-info">
  <button type="button" class="close" data-dismiss="alert">
    <span aria-hidden="true">&times;</span>
    <span class="sr-only">Close</span>
  </button>

  <strong>このページは canonical URL が指定されています。</strong><br>
  <a href="'.h($qt->getv('canonical_url')).'" target="_blank" rel="noopener" class="btn btn-info">確認</a>
</div>';
        }
    }
}
