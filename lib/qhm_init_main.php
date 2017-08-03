<?php

//------------------------------------------------
//
// デザインを常に最新の状態にするために、GETパラメタを挿入
//------------------------------------------------
$reflesh = '?'.filemtime('qhm.ini.php');


//-------------------------------------------------
//
// ロゴ部分の生成
//-------------------------------------------------
$qt->setv('logo_header', 'error01');


if($ip = $qt->getv('logo_image')) {   //logoimage.inc.php によって置き換えられている場合
    $style_type = 'image';
    $logo_img_path = $ip;
    $logo_title = $qt->getv('logo_title');
    $logo_title = $logo_title? $logo_title: $page_title;
}
else {
    $logo_img_path = $logo_image;
    $logo_title = $page_title;
}

if($style_type == "image"){   // ロゴに画像を使う
    $logo_cnts = '<img src="'.$logo_img_path.$reflesh.'" alt="'.$logo_title.'" title="'. $logo_title.'" />';
    $logo_ext = '';
}
else{ // text string logo
    $logo_cnts = $logo_title;
    $logo_ext = '_text';
}
$qt->setv('logo_header', '<div id="logo'.$logo_ext.'"><a href="'.$link_top.'">'.$logo_cnts.'</a></div>'."\n");


//-------------------------------------------------
//
// 外部リンクを別ウインドウで開くためのjavascriptの読み込み
//-------------------------------------------------
if (exist_plugin('external_link'))
{
    plugin_external_link_js($nowindow, $reg_exp_host);
}


//-------------------------------------------------
//
// デザインの設定
//-------------------------------------------------

// デザインのプレビューによる「一時的な変更処理」 for qhmsetting.inc.php
if( isset($vars['plugin']) && $vars['plugin']=='qhmsetting' &&
    isset($vars['phase'])  && $vars['phase']=='design' &&
    isset($vars['mode'])   && $vars['mode']=='form' &&
    isset($vars['design'])
){
    $_SESSION['temp_design'] = $vars['design'];
    $_SESSION['temp_enable_wp'] = $vars['enable_wp_theme']=='1' ? 1 : 0;
    $_SESSION['wp_add_css'] = $vars['wp_add_css'];
}

if( isset($_SESSION['temp_design']) &&
    $vars['cmd']!=='qhmsetting' && $vars['plugin']!=='qhmsetting'){

    $style_name = $enable_wp_theme_name = $_SESSION['temp_design'];

    if( isset($_SESSION['temp_enable_wp']) )
        $enable_wp_theme = $_SESSION['temp_enable_wp'];
    if( isset($_SESSION['wp_add_css']))
        $wp_add_css = $_SESSION['wp_add_css'];
}

// Determine custom skin
$style_config = read_skin_config($style_name);

$is_bootstrap_skin = false;
$bootstrap_css = '';
if ($style_config['bootstrap'])
{
    $is_bootstrap_skin = true;
}
else if ($qt->getv('is_bootstrap_skin'))
{
    $is_bootstrap_skin = true;
}
$qt->setv('is_bootstrap_skin', $is_bootstrap_skin);


//Bootstrap Include
$bootstrap_css = '';
$bootstrap_script = '';
if ($is_bootstrap_skin)
{
    $bootstrap_css = '<link rel="stylesheet" href="skin/bootstrap/css/bootstrap.min.css" />';
    if (file_exists(SKIN_DIR.$style_name.'/base.css')) {
        $bootstrap_css .= '<link rel="stylesheet" href="'.SKIN_DIR.$style_name.'/base.css">';
    }
    $bootstrap_script = '<script type="text/javascript" src="skin/bootstrap/js/bootstrap.min.js"></script>';
    //FontAwesome
    $bootstrap_script .= '<script src="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"></script>';
    $qt->setv('bootstrap_script', $bootstrap_script);
}

// CSSの生成

$default_css = '
<link rel="stylesheet" media="screen" href="'.SKIN_DIR.$style_name.'/main.css'.$reflesh.'">
';
if (file_exists(SKIN_DIR.$style_name.'/main_print.css'))
{
    $default_css .= '<link rel="stylesheet" media="print" href="'.SKIN_DIR.$style_name.'/main_print.css">
';
}
$qt->setv('default_css', $bootstrap_css.$default_css);
$qt->setv('style_name', $style_name);
$qt->setv('style_path', SKIN_DIR.$style_name.'/');

if( file_exists('favicon.ico') ){
    $qt->appendv('default_css', '<link rel="shortcut icon" href="favicon.ico"  type="image/x-icon" />');
}

if(($qt->getv('editable') || ss_admin_check()) && !$is_setting){
    //Bootstrap
    $include_bs = '
<link rel="stylesheet" href="skin/bootstrap/css/bootstrap-custom.min.css" />
<script type="text/javascript" src="skin/bootstrap/js/bootstrap.min.js"></script>';

    if ($is_bootstrap_skin)
    {
        $include_bs = '';
    }

    $qt->appendv_once('include_bootstrap_pub', 'beforescript', $include_bs);
}

// Javascript 読み込み
$include_js = '
<script src="js/qhm.min.js"></script>';
$qt->appendv_once('include_qhm_js', 'beforescript', $include_js);

// 自分自身へのリンクを削除する
// ※ おかしな設定の共用SSLにも対応する
$ss = is_https() ? $script_ssl : $script;

$pgname = rawurlencode($title);
$search = array();
$replace = array();
$pairs = array();
preg_match_all('/<\s*a[^>]*>(.*?)<\s*\/a\s*>/',$body,$matches);
for ($i=0; $i< count($matches[0]); $i++) {
    if(preg_match('/'. str_replace('/','\/',$ss) .'\?'.$pgname.'"/',$matches[0][$i])){
        $search = $matches[0][$i];
        $replace = $matches[1][$i];
        $pairs[$search] = $replace;
    }
}
$qt->setv('body', ($pairs==null) ? $body : strtr($body,$pairs));

//qhmsetting の場合、ナビやメニューは不要
if ($vars['plugin'] == 'qhmsetting' OR $vars['cmd'] == 'qhmsetting') return;

//-------------------------------------------------
//
// !ナビ、ナビ２、メニュー部分の生成
//
//-------------------------------------------------

if ( ! $qt->getv('no_menus'))
{
    $scripturi = $script.'\?'.rawurlencode($vars['page']);
    $ptn = '|<li>(.+href="('.$scripturi.')".+)?</li>|';

    if (exist_plugin_convert('nav'))
    {
        global $navbar;
        $vars['page_alt'] = $navbar; //swfuの制御のため

        if ( ! $qt->getv('no_site_navigator'))
        {
            //プレビューならクラスを付ける
            $focus_class = 'focus';
            if ($is_bootstrap_skin) $focus_class .= ' active';
            if ($vars['preview'] && $vars['page'] == $navbar)
            {
                $site_navigator = preg_replace($ptn, '<li class="'.$focus_class.'">$1</li>', convert_html($vars['msg']));
                $site_navigator = '<div class="preview_highlight">'. $site_navigator .'</div>';
            }
            else
            {
                $site_navigator = preg_replace($ptn, '<li class="'.$focus_class.'">$1</li>', do_plugin_convert('nav'));
            }

            $qt->setv('site_navigator_is_empty', trim($site_navigator) === '');

            if (!$qt->getv('SiteNavigatorInsertMark')) {
                $site_navigator = "\n<!-- SITENAVIGATOR CONTENTS START -->\n" . $site_navigator . "\n<!-- SITENAVIGATOR CONTENTS END -->\n";
                $qt->setv('SiteNavigatorInsertMark', true);
            }
            $qt->setv('site_navigator', $site_navigator);
        }
        else
        {
            $qt->setv('site_navigator', '');
            $qt->setv('site_navigator_is_empty', true);
        }

        unset($vars['page_alt']);
    }

    if (exist_plugin_convert('menu')) {

        global $menubar, $menubar2, $qblog_menubar;
        $vars['page_alt'] = $menubar;

        if (is_qblog() || $vars['page'] === $qblog_menubar)
        {
            do_plugin_convert('menu', $qblog_menubar);
        }

        $ptn = '"'.$script.'?'.rawurlencode($vars['page']).'"';
        $ptn = '|<(h[2-4][^>]+)>(.+href="('.$scripturi.')".+)?</(h[2-4])>|';
        $_menubody = preg_replace($ptn, '<$1 class="focus">$2</$4>', do_plugin_convert('menu'));

        //プレビューならクラスを付ける
        if ($vars['preview'] && $vars['page'] === $qblog_menubar )
        {
            if (trim($_menubody) !== '')
            {
                $_menubody = '<div class="preview_highlight">'. $_menubody .'</div>';
            }
        }

        if ($is_bootstrap_skin)
        {
            $_menubody = preg_replace('/<ul class="list1"\s*>/', '<ul class="list1 list-group">', $_menubody);
            $_menubody = preg_replace('/<li>/', '<li class="list-group-item">', $_menubody);
            $_menuscript = '
<script type="text/javascript">
$(function(){
  $(".list-group > .list-group-item").find(".list-group-item").removeClass("list-group-item");
  $("#menubar .list-group .list-group-item a").each(function(){
    var url = $(this).attr("href");
    if (url == "'.$scripturi.'") {
      $(this).parent().addClass("active");
    }
  });
});
</script>
';

            $qt->appendv_once('menu_bootstrap_script', 'beforescript', $_menuscript);


        }

        unset($vars['page_alt']);

        $qt->setv('menubar_is_empty', trim($_menubody) === '');

        if (!$qt->getv('MenuBarInsertMark')) {
            $_menubody = "\n<!-- MENUBAR CONTENTS START -->\n" . $_menubody . "\n<!-- MENUBAR CONTENTS END -->\n";
            $qt->setv('MenuBarInsertMark', true);
        }

        $addclass = '';
        //プレビューならクラスを付ける
        if ($vars['preview'] && $vars['page'] == $menubar)
        {
            $addclass = ' preview_highlight';
        }

        $menubar_tagstr = <<<EOD
<!-- ■BEGIN id:menubar -->
<div id="menubar" class="bar{$addclass}">
{$_menubody}
</div>
<!-- □END id:menubar -->
EOD;
        $qt->setv('menubar_tag', $menubar_tagstr);

        if (exist_plugin_convert('menu2')) {
            $vars['page_alt'] = $menubar2;

            $ptn = '"'. $script.'?'.rawurlencode($vars['page']).'"';
            $ptn = '|<(h[2-4][^>]+)>(.+href="('.$scripturi.')".+)?</(h[2-4])>|';
            $_menubody = preg_replace($ptn, '<$1 class="focus">$2</$4>', do_plugin_convert('menu2'));
            if ($is_bootstrap_skin)
            {
                $_menubody = preg_replace('/<ul class="list1"\s*>/', '<ul class="list1 list-group">', $_menubody);
                $_menubody = preg_replace('/<li>/', '<li class="list-group-item">', $_menubody);
            }

            unset($vars['page_alt']);

            $qt->setv('menubar2_is_empty', trim($_menubody) === '');

            if (!$qt->getv('MenuBar2InsertMark')) {
                $_menubody = "\n<!-- MENUBAR2 CONTENTS START -->\n" . $_menubody . "\n<!-- MENUBAR2 CONTENTS END -->\n";
                $qt->setv('MenuBar2InsertMark', true);
            }

            //プレビューならクラスを付ける
            $addclass = '';
            if ($vars['preview'] && $vars['page'] == $menubar2)
            {
                $addclass = ' preview_highlight';
            }

            $menubar_tagstr = <<<EOD
<!-- ■BEGIN id:menubar -->
<div id="menubar2" class="bar{$addclass}">
{$_menubody}
</div>
<!-- □END id:menubar -->
EOD;
            $qt->setv('menubar2_tag', $menubar_tagstr);
        }
    }

    if (exist_plugin_convert('nav2'))
    {
        $ptn = '"'.$script.'?'.rawurlencode($vars['page']).'"';
        $vars['page_alt'] = 'SiteNavigator2'; //swfuの制御のため
        //プレビューならクラスを付ける
        if ($vars['preview'] && $vars['page'] == 'SiteNavigator2')
        {
            $site_navigator2 = str_replace($ptn, $ptn.' class="focus"', convert_html($vars['msg']));
            if (trim($site_navigator2) !== '')
            {
                $site_navigator2 = '<div class="preview_highlight">'. $site_navigator2 .'</div>';
            }
        }
        else
        {
            $site_navigator2 = str_replace($ptn, $ptn.' class="focus"', do_plugin_convert('nav2'));
        }

        $qt->setv('site_navigator2_is_empty', trim($site_navigator2) === '');
        if (!$qt->getv('SiteNavigator2InsertMark')) {
            $site_navigator2 = "\n<!-- SITENAVIGATOR2 CONTENTS START -->\n" . $site_navigator2 . "\n<!-- SITENAVIGATOR2 CONTENTS END -->\n";
            $qt->setv('SiteNavigator2InsertMark', true);
        }
        $qt->setv('site_navigator2', $site_navigator2);
        unset($vars['page_alt']);
    }

    if (exist_plugin_convert('header'))
    {
        $ptn = '"'.$script.'?'.rawurlencode($vars['page']).'"';
        $vars['page_alt'] = 'SiteHeader'; //swfuの制御のため
        //プレビューならクラスを付ける
        if ($vars['preview'] && $vars['page'] == 'SiteHeader')
        {
            $site_header = convert_html($vars['msg']);
            if (trim($site_header) !== '')
            {
                $site_header = '<div class="preview_highlight">'. $site_header .'</div>';
            }
        }
        else
        {
            $site_header = do_plugin_convert('header');
        }

        $qt->setv('site_header_is_empty', trim($site_header) === '');
        if (!$qt->getv('SiteHeaderInsertMark')) {
            $site_header = "\n<!-- SITEHEADER CONTENTS START -->\n" . $site_header . "\n<!-- SITEHEADER CONTENTS END -->\n";
            $qt->setv('SiteHeaderInsertMark', true);
        }
        $qt->setv('site_header', $site_header);
        unset($vars['page_alt']);
    }
}

//w3c tag
$w3c_tagstr = <<<EOD
<a href="http://validator.w3.org/check?uri=referer"><img
        src="image/valid-xhtml10.png"
        alt="Valid XHTML 1.0 Transitional" height="31" width="88" />
EOD;
$qt->setv('w3c_tag', $w3c_tagstr);


// iPhone, iPod, android デザイン
if ($enable_smart_style && is_smart_phone() && !is_bootstrap_skin()) {
    if (exist_plugin('use_smart')) {
        $qt->appendv('site_navigator2', do_plugin_convert('use_smart'));
    }

    if (plugin_use_smart_is_enable()) {
        $smart_css = ' <meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="apple-mobile-web-app-capable" content="yes">
<link rel="stylesheet" href="'.SMART_DIR.$smart_name.'/smart.css" media="screen" text/css="text/css">
';
        $smart_lastscript .= '
<script type="text/javascript" charset="utf-8">
if (typeof window.onload === "undefined") {
    window.onload = function(){
        setTimeout(function(){window.scrollTo(0,1);},100);
    };
} else {
    var olfunc = window.onload;
    window.onload = function(){
        if (typeof olfunc === "function") olfunc();
        setTimeout(function(){window.scrollTo(0,1);},100);
    };
}
</script>
';

        $qt->setv('default_css', $smart_css);
        $qt->appendv('lastscript', $smart_lastscript);
    }

}

// Fit videos to screen
if ($enable_fitvids)
{
    $ignore_list = $qt->getv('fitvids_ignore_list');
    if (is_array($ignore_list))
    {
        $ignore_list = array_unique($ignore_list);
        $ignore_list = join(" ", $ignore_list);
    }
    $ignore_list = h($ignore_list);
    $fitvids_js = <<< EOS
<script>
$("#body, [role=main]").fitVids({ignore:"{$ignore_list}"});
</script>
EOS;
    $qt->appendv_once('fitvids_js', 'lastscript', $fitvids_js);
}

/* End of file qhm_init_main.php */
/* Location: ./lib/qhm_init_main.php */
