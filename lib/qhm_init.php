<?php
//-------------------------------------------------
// QHM Initialization program for skin (output)
// This file is required lib/html.php
//
// QHMの編集モードで使う変数などを初期化、設定
// 最後に、ヘッダーの出力までを担当する
//

global $layout_pages;
global $qblog_defaultpage, $qblog_menubar, $qblog_title;

$is_setting = ( (isset($vars['cmd']) && $vars['cmd']=='qhmsetting') || (isset($vars['plugin']) && $vars['plugin'] =='qhmsetting') );
//$no_toolmenu = ($is_setting OR array_key_exists($_page, $layout_pages));
$no_toolmenu = array_key_exists($_page, $layout_pages);

if (isset($vars['disable_toolmenu']) && $vars['disable_toolmenu'])
{
	$is_setting = TRUE;
}

//---- set ini values for template engine
$qt->setv('version', QHM_VERSION);
$qt->setv('keywords', $keywords);
$qt->setv('description', $description);
$qt->setv('custom_meta', $custom_meta);
$qt->setv('head_tag', $head_tag);
$qt->setv('modifierlink', $modifierlink);
$qt->setv('modifier', $modifier);
$qt->setv('owneraddr', $owneraddr);
$qt->setv('ownertel', $ownertel);
$qt->getv('beforescript')? '': $qt->setv('beforescript', '');
$qt->getv('main_visual')? '': $qt->setv('main_visual', '');
$qt->getv('lastscript')? '': $qt->setv('lastscript', '');
$qt->setv('_page', $_page);
$qt->setv('_script', $script);
$qt->setv('auth_link', ($qhm_adminmenu <= 1) ? ('<a href="' . h($script . '?cmd=qhmauth') . '" class="qhm-auth-link">HAIK</a>') : '');
//head
$qt->setv('headcopy_is_empty', trim($headcopy) === '');
if ( ! $qt->getv('headcopy_is_empty'))
{
	$qt->setv('head_copy_tag', '<div id="headcopy" class="qhm-head-copy">
<h1>'.$headcopy.'</h1>
</div><!-- END: id:headcopy -->
');
}

$_go_url = $script.'?go='.get_tiny_code($_page);
$qt->setv('go_url', $_go_url);
$_qhm_rawurl = $script.'?'.rawurlencode($_page);

//---- Prohibit direct access
if (! defined('UI_LANG')) die('UI_LANG is not set');
if (! isset($_LANG)) die('$_LANG is not set');
if (! defined('PKWK_READONLY')) die('PKWK_READONLY is not set');

$link  = & $_LINK;
$image = & $_IMAGE['skin'];
$rw    = ! PKWK_READONLY;

$qt->setv_once('rss_link', $link['rss']);

//---- define global values for some plugin.
global $accesstag_moved ; //ganatracker.inc.php setting (GoogleAnalytics)
global $shiftjis; //Shift-JIS converter
global $eucjp; //EUC-JP converter
global $is_update; // for update link


if ($shiftjis)   { define('TEMPLATE_ENCODE','Shift_JIS'); }
else if ($eucjp) { define('TEMPLATE_ENCODE','EUC-JP'); }
else             { define('TEMPLATE_ENCODE','UTF-8'); }

$qhm_dir = (preg_match('/.*\.php/',$script)) ? dirname($script) : dirname($script.'index.php');
$qt->setv('qhm_dir', $qhm_dir);

$qt->setv('clickpad_js', '');

// Set toolbar-specific images
$_IMAGE['skin']['edit']     = 'edit.png';
$_IMAGE['skin']['diff']     = 'diff.png';
$_IMAGE['skin']['upload']   = 'file.png';
$_IMAGE['skin']['list']     = 'list.png';
$_IMAGE['skin']['search']   = 'search.png';
$_IMAGE['skin']['recent']   = 'recentchanges.png';
$_IMAGE['skin']['backup']   = 'backup.png';
$_IMAGE['skin']['help']     = 'help.png';
$_IMAGE['skin']['rss']      = 'rss.png';
$_IMAGE['skin']['rss10']    = & $_IMAGE['skin']['rss'];
$_IMAGE['skin']['rss20']    = 'rss20.png';
$_IMAGE['skin']['rdf']      = 'rdf.png';
$_IMAGE['skin']['rename']   = 'rename.png';
$_IMAGE['skin']['menuadmin']   = 'menuadmin.png';


// Editable mode preparation
$qt->setv('editable', check_editable($_page, FALSE, FALSE));

$has_swfu = file_exists('swfu/config.php')? 'window.qhm_has_swfu = 1;'. "\n": '';
$has_fwd3 = file_exists('fwd3/sys/config.php')? 'window.qhm_has_fwd3 = 1;'. "\n": '';

// other_plugin button
$op_func = <<<EOD
var op = $("div.other_plugin");
var optitle = $("div.other_plugin_box_title");
if (!op.is(':visible')) {
	op.show();
	if (!optitle.is(".expand")) {
		optitle.click();
	}
	document.cookie = "otherplugin=show";
}
else {
	op.hide();
	if (optitle.is(".expand")) {
		optitle.click();
	}
	document.cookie = "otherplugin=hide";
}
EOD;

$qt->setv('toolkit_upper', '');
$qt->setv('toolkit_bottom', '');

if(($qt->getv('editable') || ss_admin_check()) && !$is_setting){
	$qt->setv('jquery_include', true);

	$link_haik_parts = '//open-qhm.github.io/haik-parts/';

	$unload_confirm = isset($unload_confirm)? $unload_confirm: 1;
	$enable_unload_confirm = 'window.qhm_enable_unload_confirm = '. ($unload_confirm? 'true': 'false'). ';'. "\n";

    $btnset_name = is_qblog() ? 'qblog' : 'qhm';
    if (is_bootstrap_skin())
    {
        $btnset_name = is_qblog() ? 'qhmHaikQBlog' : 'qhmHaik';
    }

    $refleshjs = '?'.QHM_VERSION;


    $clickpad_js = <<<EOD
<!--[if IE 6]><script type="text/javascript" src="js/fixed.js"></script><![endif]-->
<script type="text/javascript" src="js/thickbox.js"></script>
<script type="text/javascript" src="js/jquery.clickpad.js{$refleshjs}"></script>
<script type="text/javascript" src="js/jquery.exnote.js"></script>
<script type="text/javascript" src="js/jquery.shortkeys.js"></script>
<script type="text/javascript" src="js/jquery.edit.js"></script>
<script type="text/javascript">
{$has_swfu}{$has_fwd3}{$enable_unload_confirm}
$(function(){
  // clickpad
  if($("#msg").length) {
    $("#msg").clickpad({buttons:"{$btnset_name}",autoGrow:false})
      .data("original", $("#msg").val());

		otherplugin = function(){
{$op_func}
		};
    showHaikParts = function(){
      tb_show('', '{$link_haik_parts}#{$style_name}?KeepThis=true&TB_iframe=true');
    }
		var ck = document.cookie.split(";");
		for (var i = 0; i < ck.length; i++) {
			if (ck[i].split("=")[0].replace(/^\s|\s$/, '').match(/otherplugin/)
				&& ck[i].split("=")[1].replace(/^\s|\s$/, '').match(/show/)) {
				otherplugin();
			}
		}

		if (window.qhm_enable_unload_confirm) {
			//form からのsubmit では遷移確認を出さない
			$("#edit_form_main, #edit_form_cancel").submit(function(e){
				window.onbeforeunload = null;
			});
			$("input:submit[name=write], input:submit[name=preview]").click(function(){
				window.onbeforeunload = null;
			});
		}

        // keyboard shortcut in textarea#msg
        var isWin = (navigator.platform.indexOf('win') != -1);
        $("#msg").keydown(function(e){
          //[esc]
          if (e.keyCode == 27) {
            $(this).blur();
          }
          //Save [Ctrl + S] [Command + S]
          else if (((isWin && e.ctrlKey) || (! isWin && e.metaKey)) && e.keyCode == 83) {
            e.preventDefault();
            $("input:submit[name=write]").click();
          }
          //Preview [Ctrl + P] [Command + P]
          else if (((isWin && e.ctrlKey) || (! isWin && e.metaKey)) && e.keyCode == 80) {
            e.preventDefault();
            $("input:submit[name=preview]").click();
          }
        });
    }

});

if (window.qhm_enable_unload_confirm) {
	window.onbeforeunload = function(e) {
		if ($("#msg").val() != $("#msg").data("original")) {
			return '{$qm->m['qhm_init']['unload_confirm']}';
		}
	}
}
</script>

<link rel="stylesheet" media="screen" href="skin/hokukenstyle/qhm.css">
<link rel="stylesheet" media="screen" href="js/thickbox.css">

EOD;
	$qt->setv('clickpad_js', $clickpad_js);

	$link_help = QHM_HOME;
	$link_map = $script.'?cmd=map&amp;refer='.rawurlencode($_page);
	$link_password = $script.'?plugin=qhmsetting&amp;phase=user2&mode=form';
	$link_qhm_update = $script.'?plugin=qhmupdate';
	$link_qblog = $script . '?' . $qblog_defaultpage;
	$link_qblog_menu = $script . '?cmd=edit&amp;page='.$qblog_menubar;
	$link_haik_skin_customizer = $script . '?cmd=qhmsetting&amp;phase=design&amp;mode=form&amp;preview=1&amp;enable_wp_theme=0&amp;design='.$style_name.'&amp;customizer=1';


    $layout_class = "thickbox";
    if (is_bootstrap_skin())
    {
        $layout_class = "";
    }

	$tools = array(
	'toplink'     => array('name'=>$qm->m['qhm_init']['toplink_name'], 'link'=>$script, 'style'=>'', 'class'=>'', 'visible'=>true, 'sub'=>array()),
	'editboxlink' => array('name'=>$qm->m['qhm_init']['editboxlink_name'], 'link'=>'#msg', 'style'=>'', 'class'=>'go_editbox', 'visible'=>true, 'sub'=>array()),
	'editlink'    => array('name'=>$qm->m['qhm_init']['editlink_name'], 'link'=>$link_edit, 'style'=>'margin-top:1.1em;', 'class'=>'', 'visible'=>true, 'sub'=>array()),
	'reflink'     => array('name'=>$qm->m['qhm_init']['reflink_name'], 'link'=>$link_upload, 'style'=>'', 'class'=>'swfu', 'visible'=>true, 'sub'=>array()),
	'pagelink'    => array('name'=>$qm->m['qhm_init']['pagelink_name'], 'link'=>'', 'style'=>'margin-top:1.1em;', 'class'=>'', 'visible'=>true, 'sub'=>array(
			'difflink' => array('name'=>$qm->m['qhm_init']['difflink_name'], 'link'=>$link_diff, 'style'=>'','class'=>'', 'visible'=>true, ),
			'backuplink' => array('name'=>$qm->m['qhm_init']['backuplink_name'], 'link'=>$link_backup, 'style'=>'','class'=>'', 'visible'=>true, ),
			'renamelink' => array('name'=>$qm->m['qhm_init']['renamelink_name'], 'link'=>$link_rename, 'style'=>'','class'=>'', 'visible'=>true, ),
			'dellink' => array('name'=>'削除', 'link'=>$link_delete, 'style'=>'','class'=>'', 'visible'=>true, ),
			'maplink' => array('name'=>$qm->m['qhm_init']['maplink_name'], 'link'=>$link_map, 'style'=>'','class'=>'', 'visible'=>true, ),
			'copylink' => array('name'=>$qm->m['qhm_init']['copylink_name'], 'link'=>$link_copy, 'style'=>'','class'=>'', 'visible'=>true, ),
			'sharelink' => array('name'=>'共有', 'link'=>'#', 'style'=>'', 'class'=>'', 'visible'=>true),
		)),
	'sitelink'   => array('name'=>$qm->m['qhm_init']['sitelink_name'], 'link'=>'', 'style'=>'', 'class'=>'', 'visible'=>true, 'sub'=>array(
			'headerlink' => array('name'=>$layout_pages['SiteHeader'] . '編集', 'link'=>$link_edit_header, 'style'=>'','class'=>$layout_class, 'visible'=>true, ),
			'navilink' => array('name'=>$qm->m['qhm_init']['navilink_name'], 'link'=>$link_edit_navi, 'style'=>'','class'=>$layout_class, 'visible'=>true, ),
			'menulink' => array('name'=>$qm->m['qhm_init']['menulink_name'], 'link'=>$link_edit_menu, 'style'=>'','class'=>$layout_class, 'visible'=>true, ),
			'menu2link' => array('name'=>$qm->m['qhm_init']['menu2link_name'], 'link'=>$link_edit_menu2, 'style'=>'','class'=>$layout_class, 'visible'=>true, ),
			'navi2link' => array('name'=> $layout_pages['SiteNavigator2'] . '編集', 'link'=>$link_edit_navi2, 'style'=>'','class'=>$layout_class, 'visible'=>true, ),
			'newlink' => array('name'=>$qm->m['qhm_init']['newlinklink_name'], 'link'=>$link_new, 'style'=>'margin-top:1em;','class'=>'', 'visible'=>true, ),
			'whatnewlink' => array('name'=>$qm->m['qhm_init']['whatnewlink_name'], 'link'=>$link_whatsnew, 'style'=>'','class'=>'', 'visible'=>true, ),
			'pagelistlink' => array('name'=>$qm->m['qhm_init']['pagelistlink_name'], 'link'=>$link_filelist, 'style'=>'','class'=>'', 'visible'=>true, ),
			'yetlistlink' => array('name'=>$qm->m['qhm_init']['yetlistlink_name'], 'link'=>$link_yetlist, 'style'=>'','class'=>'', 'visible'=>true, ),
		)),
	'toollink'   => array('name'=>$qm->m['qhm_init']['toollink_name'], 'link'=>'', 'accesskey'=>'', 'style'=>'', 'class'=>'', 'visible'=>true, 'sub'=>array(
			'swfulink' => array('name'=>$qm->m['qhm_init']['swfulink_name'], 'link'=>'swfu/index.php', 'style'=>'','class'=>'swfu', 'visible'=>true, ),
			'fwd3link' => array('name'=>$qm->m['qhm_init']['fwd3link_name'], 'link'=>'fwd3/sys/', 'style'=>'','class'=>'', 'visible'=>true, ),
			'qdsgnlink' => array('name'=>$qm->m['qhm_init']['qdsgnlink_name'], 'link'=>'qdsgn/index.php', 'style'=>'','class'=>'', 'visible'=>true, ),
			'searchlink' => array('name'=>$qm->m['qhm_init']['searchlink_name'], 'link'=>$link_search, 'style'=>'','class'=>'', 'visible'=>true, ),
		)),
	'qbloglink' => array(
		'name' => 'ブログ', 'link'=>'', 'style'=>'','class'=>'', 'visible'=>true, 'sub' => array(
			'qblogtoplink' => array('name'=>'トップ', 'link'=>$link_qblog, 'style'=>'', 'class'=>'', 'visible'=>TRUE),
			'qblogmenulink' => array('name'=>'メニュー編集', 'link'=>$link_qblog_menu, 'style'=>'','class'=>'', 'visible'=>true, ),
			'qblogconfiglink' => array('name' => '設定', 'link'=>$script.'?cmd=qblog', 'style'=>'','class'=>'', 'visible'=>TRUE	),
			'qblognewlink' => array('name'=>'記事の追加', 'link'=>$script.'?cmd=qblog&mode=addpost', 'style'=>'', 'class'=>'', 'visible'=>TRUE),
		)
	),
	'haiklink' => array(
		'name'    => 'テーマ',
		'link'    => '',
		'style'   => 'margin-top:1.1em;',
		'class'   => '',
		'visible' => true,
		'sub'     => array(
			'haikskincustomizer' => array('name'=>'編集', 'link'=>$link_haik_skin_customizer, 'style'=>'', 'class'=>'', 'visible'=>TRUE),
			'haikpreviewlink' => array('name'=>'<span class="hidden-xs hidden-sm"><i class="glyphicon glyphicon-phone"></i> <span class="sr-only">モバイル</span>プレビュー</span>', 'link'=>'#', 'style'=>'', 'class' => '', 'visible'=>TRUE),
		),
	),
	'configlink' => array('name'=>$qm->m['qhm_init']['configlink_name'], 'link'=>$link_qhm_setting, 'style'=>'margin-top:1.1em;', 'visible'=>true, 'sub'=>array()),
	'helplink'   => array('name'=>'open-qhm.net', 'link'=>$link_help, 'style'=>'', 'visible'=>true, 'sub'=>array()),
	'passwordlink'   => array('name'=>$qm->m['qhm_init']['passwordlink_name'], 'link'=>$link_password, 'style'=>'', 'visible'=>true, 'sub'=>array()),
	'logoutlink' => array('name'=>$qm->m['qhm_init']['logoutlink_name'], 'link'=>$link_qhm_logout, 'style'=>'margin-top:1.1em;', 'visible'=>true, 'sub'=>array()),
	'updatelink' => array('name'=>$qm->m['qhm_init']['updatelink_name'], 'link'=>$link_qhm_update, 'style'=>'margin-top:1.1em;', 'visible'=>true, 'sub'=>array()),
	);

	$prevdiv = '';
	if (isset($_SESSION['temp_design']))
	{
		unset($tools['editboxlink'],
			$tools['editlink'],
			$tools['reflink'],
			$tools['pagelink'],
			$tools['sitelink'],
			$tools['toollink'],
			$tools['configlink'],
			$tools['helplink'],
			$tools['qbloglink']
		);

		$btn_class = ( ! isset($_SESSION['temp_skin']) OR strlen($_SESSION['temp_skin']) === 0) ? 'local' : '';

    $custom_btn = '';
    $redirect = 0;
    // Set Skin Customizer
    if (exist_plugin('skin_customizer'))
    {
        if (isset($_SESSION['temp_design_customizer']) && $_SESSION['temp_design_customizer'])
        {
            $redirect = $_qhm_rawurl;
        }
        $custom_btn = plugin_skin_customizer_set_form();
    }
		$prevdiv = '
<div id="preview_bar_overlay"></div>
<div id="preview_bar">
'.$custom_btn.'
デザイン '. h($_SESSION['temp_design']) .' プレビュー中&nbsp;&nbsp;
<form action="'. h($script) .'" method="post">
<input type="hidden" name="cmd" value="qhmsetting" />
<input type="hidden" name="mode" value="form" />
<input type="hidden" name="phase" value="design" />
<input type="hidden" name="preview" value="0" />
<input type="hidden" name="redirect" value="'.h($redirect).'" />
<input type="submit" name="preview_cancel" value="プレビューを解除する" class="qhm-btn-default"/>
</form>
<form action="'. h($script) .'" method="post">
<input type="hidden" name="cmd" value="qhmsetting" />
<input type="hidden" name="mode" value="msg" />
<input type="hidden" name="phase" value="design" />
<input type="hidden" name="from" value="design_form" />
<input type="hidden" name="qhmsetting[style_name]" value="'. h($_SESSION['temp_design']).'" />
<input type="hidden" name="qhmsetting[style_type]" value="none" />
<input type="hidden" name="qhmsetting[enable_wp_theme]" value="'. h($_SESSION['temp_enable_wp']).'" />
<input type="submit" name="preview_set" value="このデザインを適用する" class="'. h($btn_class) .' qhm-btn-primary" />
</form>
</div>
';
	}

	//unset menu2 for 2-column style
	if (strpos($style_name, '3_') !== 0 &&
		!(file_exists("skin/hokukenstyle/$style_name/pukiwiki.skin.php") &&
			strpos(file_get_contents("skin/hokukenstyle/$style_name/pukiwiki.skin.php"), '#{$menubar2_tag}') !== FALSE))
	{
		unset($tools['sitelink']['sub']['menu2link']);
	}

	if (!$is_page) {
		if (isset($tools['editboxlink'])) unset($tools['editboxlink']);
		$tools['editlink']['visible'] = false;
		$tools['reflink']['visible'] = false;
		$tools['pagelink']['visible'] = false;
	}
	if (!$rw) {
		$tools['editlink']['visible'] = false;
		$tools['reflink']['visible'] = false;
	}
	if (!(bool)ini_get('file_uploads')) {
		$tools['reflink']['visible'] = false;
	}
	if(!file_exists('swfu/index.php')) {
		$tools['toollink']['sub']['swfulink']['visible'] = false;
		$tools['reflink']['class'] = '';
	}
	else {
		$tools['reflink']['link'] = 'swfu/index_child.php?page='.rawurlencode($vars['page']).'&amp;KeepThis=true&amp;TB_iframe=true';
	}
	if(!file_exists('fwd3/sys/fwd3.txt')) {
		$tools['toollink']['sub']['fwd3link']['visible'] = false;
	}
	if(!file_exists('qdsgn/index.php')) {
		if (isset($tools['toollink']['sub']['qdsgnlink'])) unset($tools['toollink']['sub']['qdsgnlink']);
	}
	if (strpos($style_name, 'haik_') !== 0)
	{
		if (isset($tools['haiklink'])) unset($tools['haiklink']);
	}
	else
	{
		$addjs = '
<script type="text/javascript" src="js/haik_theme_utility.js"></script>
<script type="text/javascript" src="'.PLUGIN_DIR.'skin_customizer/color_picker.js"></script>
';
		$qt->appendv('beforescript', $addjs);

		// Determine custom skin
		$style_config = read_skin_config($style_name);
		$skin_custom_vars = get_skin_custom_vars($style_name);
		if ( ! isset($style_config['custom_options']['header']) || ! $skin_custom_vars['header'])
		{
			unset($tools['sitelink']['sub']['headerlink']);
		}
	}
    if ( ! ss_admin_check())
    {
        if (isset($tools['reflink'])) unset($tools['reflink']);
        if (isset($tools['pagelink'])) unset($tools['pagelink']);
        if (isset($tools['sitelink'])) unset($tools['sitelink']);
        if (isset($tools['toollink'])) unset($tools['toollink']);
        if (isset($tools['configlink'])) unset($tools['configlink']);
        if (isset($tools['helplink'])) unset($tools['helplink']);
        if (isset($tools['haiklink'])) unset($tools['haiklink']);
    }
	else {
		if (isset($tools['passwordlink'])) unset($tools['passwordlink']);
	}
	if ($_page === $defaultpage) {

		if (isset($tools['pagelink']['sub']['dellink'])) unset($tools['pagelink']['sub']['dellink']);
	}

	if ( ! isset($_COOKIE['QHM_VERSION']) || $_COOKIE['QHM_VERSION'] <= QHM_VERSION || get_qhm_option('update') !== 'vendor')
	{
		unset($tools['updatelink']);
	}

	if (is_qblog())
	{
		if (isset($tools['pagelink']['sub']['renamelink'])) unset($tools['pagelink']['sub']['renamelink']);
	}
	if ( ! is_page($qblog_defaultpage))
	{
		if (isset($tools['qbloglink'])) unset($tools['qbloglink']);
	}


	// レイアウトページの時の管理ウィンドウの制御
	if ($no_toolmenu)
	{
      if ( ! is_bootstrap_skin())
      {
      		$tools = array('editlink' => $tools['editlink'],'reflink' => $tools['reflink'], 'pagelink'=>$tools['pagelink']);
      }

		unset($tools['pagelink']['sub']['sharelink']);
		unset($tools['pagelink']['sub']['renamelink']);
		unset($tools['pagelink']['sub']['dellink']);
		unset($tools['pagelink']['sub']['copylink']);
		unset($tools['pagelink']['sub']['maplink']);
		unset($tools['pagelink']['sub']['tinyurllink']);
		if (arg_check('backup') OR arg_check('diff'))
		{
			$tools['reflink']['visible'] = FALSE;
		}
	}

	$tools_str = '<ul class="toolbar_menu">';
	foreach ($tools as $lv1key => $lv1) {
		// main menu
		$style = ($lv1['style'] != '') ? $lv1['style'] : '';
		// visible
		if ($lv1['visible']) {
			// link
			if ($lv1['link'] != '') {
				$class= isset($lv1['class']) && ($lv1['class'] != '') ? ' class="'.$lv1['class'].'"' : '';
				$target = ($lv1key == 'helplink') ? ' target="help"' : '';
				$tools_str .= '<li style="background-image:none;'.$style.'"'.$class.'><a href="'.$lv1['link'].'"'.$target.' id="'.$lv1key.'">'.$lv1['name'].'</a>';
			}
			else {
				$class= ($lv1['class'] != '') ? ' class="'.$lv1['class'].'"' : '';
				$style = ($style != '') ? ' style="position:relative;'.$class.$style.'"' : ' style="position:relative;"';
				$tools_str .= '<li'.$style.'>'.$lv1['name'];
			}
		}
		// invisible
		else {
			$tools_str .= '<li style="background-image:none;" class="nouse">'.$lv1['name'];
		}

		// sub menu
		if (count($lv1['sub']) > 0) {
			$tools_str .= '<ul class="toolbar_submenu">';
			foreach ($lv1['sub'] as $lv2key => $lv2) {
				$class= ($lv2['class'] != '') ? ' class="'.$lv2['class'].'"' : '';
				$style = ($lv2['style'] != '') ? ' style="'.$lv2['style'].'"' : '';
				$tools_str .= '<li'.$class.$style.'>';
				$target = isset($lv2['target']) ? ' target="'.$lv2['target'].'"' : '';
				// visible
				if ($lv2['visible']) {
					// link
					if ($lv2['link'] != '') {
						$target = '';
						$tools_str .= '<a href="'.$lv2['link'].'"'.$target.' id="'.$lv2key.'">'.$lv2['name'].'</a>';
					}
					else {
						$tools_str .= $lv2['name'];
					}
				}
				// invisible
				else {
					$tools_str .= '<li style="background-image:none;" class="nouse">'.$lv2['name'];
				}
				$tools_str .= '</li>';
			}
			$tools_str .= '</ul>'; // sub menu end
		}
		$tools_str .= '</li>';
	}
	$tools_str .= '</ul>'; // main manu end

	//クッキーで大きさを調節
	if ((array_key_exists($_page, $layout_pages) && ! is_bootstrap_skin()) OR (isset($_COOKIE['toolbar_size']) && $_COOKIE['toolbar_size'] == 'min'))
	{
		$tb_max_disp = 'display:none';
		$tb_min_disp = '';
	}
	else
	{
		$tb_max_disp = '';
		$tb_min_disp = 'display:none';
	}

	$qm = get_qm();

	//最大化型のtoolbar_upper
	$tk_append = '
<!-- Toolbar upper -->
<div id="toolbar_upper_max" class="toolbar_upper" style="'.$tb_max_disp.'">
	<div class="toolkit_switch expand toolline">[ー]</div><div>'.$tools_str.'</div></div>';

//最小化型のtoolbar upper
	$tools_str = preg_replace('/\sid="([a-z_]+?)"/', ' id="$1_min"', $tools_str);
	$tools_str = str_replace('toolbar_menu', 'toolbar_menu_min', $tools_str);
	$tools_str = str_replace('margin-top:1.1em;', '', $tools_str);

	$tk_append .= '
<!-- Toolbar upper -->
<div id="toolbar_upper_min" class="toolbar_upper" style="border-bottom:1px dashed #999;position:fixed;top:0px;left:0px;line-height:0.8em;'.$tb_min_disp.'">
	<div class="toolkit_switch toolleft">[＋]</div>
	<div style="float:left;">'.$tools_str.'</div>
</div>';
	$qt->appendv('toolkit_upper', $tk_append);

	//php setting check warning
	if( defined('WARNING_OF_ENCODING') ){
		$tk_enc_msg = '<p style="font-size:16px;color:white;width:600px;margin:5px auto;background-color:#e00">'. $qm->m['qhm_init']['err_encoding']. '</p>';
		$qt->prependv('toolkit_upper', $tk_enc_msg);
	}

	//shortcut 一覧
	$tk_append = '
<div id="shortcut_list">
<p style="text-align:right;padding-right:20px;margin:0;"><a href="#" style="color:#ddd;font-size:13px">'. $qm->m['qhm_init']['sc_close'].'</a></p>
<table style="border:none;background-color:transparent;">
	<thead>
		<tr>
			<th width="80">&nbsp;</th>
			<th width="320">'. $qm->m['qhm_init']['sc_col_edit'].'</th>
			<th width="80">&nbsp;</th>
			<th width="320">'. $qm->m['qhm_init']['sc_col_move'].'</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th style="color:yellow;text-align:right;">esc</th>
			<td>'. $qm->m['qhm_init']['sc_blur'].'</td>
			<th style="color:yellow;text-align:right;">g -> t</th>
			<td>'. $qm->m['qhm_init']['sc_scroll_top'].'</td>
		</tr>
		<tr>
			<th style="color:yellow;text-align:right;">g -> e</th>
			<td>'. $qm->m['qhm_init']['sc_jump_edit']. '</td>
			<th style="color:yellow;text-align:right;">g -> h</th>
			<td>'. $qm->m['qhm_init']['sc_jump_home']. '</td>
		</tr>
		<tr>
			<th style="color:yellow;text-align:right;">g -> p</th>
			<td>'. $qm->m['qhm_init']['sc_preview']. '</td>
			<th style="color:yellow;text-align:right;">g -> q</th>
			<td>'. $qm->m['qhm_init']['sc_search']. '</td>
		</tr>
		<tr>
			<th style="color:yellow;text-align:right;">g -> s</th>
			<td>'. $qm->m['qhm_init']['sc_save']. '</td>
			<th style="color:yellow;text-align:right;">g -> n</th>
			<td>'. $qm->m['qhm_init']['sc_newpage']. '</td>
		</tr>
		<tr>
			<th style="color:yellow;text-align:right;">g -> a</th>
			<td>'. $qm->m['qhm_init']['sc_attach']. '</td>
			<th style="color:yellow;text-align:right;">g -> m</th>
			<td>'. $qm->m['qhm_init']['sc_map']. '</td>
		</tr>
		<tr>
			<th style="color:yellow;text-align:right;">g -> i</th>
			<td>'. $qm->m['qhm_init']['sc_image']. '</td>
			<th style="color:yellow;text-align:right;">g -> l</th>
			<td>'. $qm->m['qhm_init']['sc_filelist']. '</td>
		</tr>
		<tr>
			<th style="color:yellow;text-align:right;">g -> o</th>
			<td>'. $qm->m['qhm_init']['sc_open_tools']. '</td>
			<th style="color:yellow;text-align:right;">g -> c</th>
			<td>'. $qm->m['qhm_init']['sc_jump_settings']. '</td>
		</tr>
		<tr>
			<th style="color:yellow;text-align:right;">/</th>
			<td>'. $qm->m['qhm_init']['sc_focus']. '</td>
			<th style="color:yellow;text-align:right;">g -> f</th>
			<td>'. $qm->m['qhm_init']['sc_urlshorter']. '</td>
		</tr>
		<tr>
			<th style="color:yellow;text-align:right;">g -> u</th>
			<td>ページの共有・URL表示</td>
			<th style="color:yellow;text-align:right;">g -> ?</th>
			<td>'. $qm->m['qhm_init']['sc_open_help']. '</td>
		</tr>
		<tr>
			<th style="color:yellow;text-align:right;">&nbsp;</th>
			<td>&nbsp;</td>
			<th style="color:yellow;text-align:right;">g -> g</th>
			<td>'. $qm->m['qhm_init']['sc_open_google']. '</td>
		</tr>
	</tbody>
</table>
</div>
';

	//ページ共有
	$tweettext = '%TITLE% - '. $_go_url;
	$tweeturl_fmt = 'http://twitter.com/intent/tweet?text=$text';
	$tweeturl = str_replace(array('$text', '$url'), array(rawurlencode($tweettext), rawurlencode($_go_url)), $tweeturl_fmt);
	$tk_append .= '
<div class="modal fade" id="shareQHMPage">
  <div class="modal-dialog">
  <div class="modal-content">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title">このページの共有</h4>
  </div>
  <div class="modal-body clearfix form-horizontal">
    <div class="form-group">
      <label class="col-sm-3 control-label">'. $qm->m['qhm_init']['su_shorten']. '</label>
      <div class="col-sm-9">
        <input type="text" value="'.$_go_url.'" readonly="readonly" size="36" class="form-control" />
        <a href="'.$script.'?cmd=update_tinycode&page='.h($_page).'" class="help-block pull-right">'. $qm->m['qhm_init']['su_update'].'</a>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-3 control-label">'. $qm->m['qhm_init']['su_url']. '</label>
      <div class="col-sm-9">
        <input type="text" value="'.$_qhm_rawurl.'" readonly="readonly" size="36" class="form-control" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-3 control-label">Twitter</label>
      <div class="col-sm-9">
        <textarea cols="90" rows="3" class="form-control">'. h($tweettext) .'</textarea>
		<ol class="help-block">
			<li><span class="small">内容を編集して投稿できます。<br /><b>%URL%</b> と書くとURLに自動変換されます。</span></li>
			<li><a href="'. $tweeturl .'" class="shareTwitter btn qhm-btn-primary qhm-btn-sm" data-format="'. h($tweeturl_fmt) .'" data-url="'. h($_go_url) .'" target="_blank">クリックしてTwitterへ投稿</a></li>
		</ol>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-3 control-label">Facebook</label>
      <div class="col-sm-9">

        <ol class="help-block">
          <li>短縮URLをコピーする。</li>
          <li><a href="http://www.facebook.com/" class="btn qhm-btn-primary qhm-btn-sm" target="_blank">ここをクリックして、Facebook を開いて投稿</a></li>
        </ol>
      </div>
    </div>
  </div>
  </div>
  </div>
</div>


';


	// other plugin
	$op_html = '';
	if ( ! is_bootstrap_skin())
	{
		$op_cat = array();
		$op_html  = '<div class="other_plugin">';
		$op_html .= '<div class="other_plugin_box_title expand"><span>'. $qm->m['qhm_init']['ot_label']. '</span>&nbsp;&nbsp;<span class="mark">ー</span></div>';
		$op_html .= '<div class="other_plugin_box">';
		$op_html_box = "";
		foreach ($other_plugins as $opkey => $op) {
			$insert_cmd = $op['insert'];
			$insert_cmd = str_replace("\n", "##LF##", $insert_cmd);
			$op_html_box = '<li class="'.$op['help'].'"><span class="opname">'.$op['name'].'</span><span class="insert_cmd">'.$insert_cmd.'</span></li>';
			$op_cat[$op['category']][] = $op_html_box;
		}
		$op_html .= '<ul class="other_plugin_menu">';
		foreach ($other_plugin_categories as $catkey => $catname) {
			$op_html_menu  = '<li>'.$catname;
			$op_html_menu .= '<ul class="other_plugin_sub">';
			$op_html_menu .= implode('', $op_cat[$catkey]);
			$op_html_menu .= '</ul></li>';
			$op_html .= $op_html_menu;
		}
		$op_html .= '</ul>';

		$op_html .= "</div>";
		$op_html .= "</div>\n";
	}
	$tk_append .= $op_html;
	$tk_append .= $prevdiv;

	$qt->appendv('toolkit_upper', $tk_append);

}

else if ($qhm_adminmenu == 0){
	$tk_bottom = $is_page ? '<div id="toolbar"><a href="'.$link_edit.'" >Edit this page</a></div>': '<div id="toolbar">'. $qm->m['qhm_init']['err_cannot_edit']. '</div>';
	$qt->setv('toolkit_bottom', $tk_bottom);
}

//set page title (title tag of HTML)
if($is_read){
	$qt->setv_once('this_page_title', $title. " - ". $page_title);
	$qt->setv_once('this_right_title', $title);
}
else{ //編集時は、必ずシステム情報でタイトルを作る
	$qt->setv('this_page_title', $title. " - ". $page_title);
	$qt->setv('this_right_title', $title);
}

if ($title == $defaultpage){ //トップ用
	$qt->setv('this_page_title', $page_title);
}

if (preg_match("/$non_list/", $vars['page']))
{
	$noindex = TRUE;
}

//seach engine spider control
$qt->setv('noindex', '');
if ($noindex || $nofollow || ! $is_read)
{
	$noindexstr = '
<meta name="robots" content="NOINDEX,NOFOLLOW" />
<meta name="googlebot" content="noindex,nofollow" />
';
	$qt->setv('noindex', $noindexstr);
}
//set canonical url
else
{
	if ($qt->getv('canonical_url'))
	{
		$canonical_url = $qt->getv('canonical_url');
	}
	else
	{
		if ($vars['page'] === $defaultpage)
		{
			$canonical_url = dirname($script . 'dummy');
		}
		else
		{
			$canonical_url = $script . '?' . rawurlencode($vars['page']);
		}
	}
	$canonical_tag = <<< EOD
<link rel="canonical" href="{$canonical_url}">
EOD;
	$qt->prependv('beforescript', $canonical_tag);
}

//license
$qhm_admin_tag = ($qhm_adminmenu < 2) ? ' <a href="'. $link_qhm_adminmenu.'">HAIK</a> ' : '';
$qt->setv('licence_tag', "<p>".S_COPYRIGHT. $qhm_admin_tag."</p>");
if($no_qhm_licence){
	$qt->setv('licence_tag', '');
}
$qt->setv('qhm_login_link', $link_qhm_adminmenu);

//rss
$rss_label = $qm->m['qhm_init']['rss_label'];
$rss_tag = '<a href="'.$script.'?cmd=rss&amp;ver=1.0"><img src="image/rss.png" width="36" height="14" alt="'. $rss_label. '" title="'. $rss_label. '" /></a>';
$qt->setv('rss_tag', $rss_tag);

//access tag
$qt->setv('accesstag_tag', '');
if ($qt->getv('editable') === FALSE) {
    if ($is_read && !$accesstag_moved)  {
    	$qt->setv('accesstag_tag', $accesstag);
    }
    //UniversalAnalytics
    if ($is_read && $ga_tracking_id)
    {
        $tracking_code = <<< EOD
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '{$ga_tracking_id}', 'auto');
  ga('send', 'pageview');

</script>

EOD;
        $qt->setv('ga_universal_analytics', $tracking_code);
    }
}

$tmp_date = getdate();
$qt->setv('today_year', $tmp_date['year']);


//misc info setting
$summaryflag_start = '';
$summaryflag_end = '';
if( ($notes != '') || ($trackback !='') || ($referer !='') || ($related != '') || ($attaches != '')  ){
 $summaryflag_start = '<div id="summary"><!-- ■BEGIN id:summary -->';
 $summaryflag_end = '</div><!-- □ END id:summary -->';
}

$attach_tag = '';
if ($attaches != '') {
 $attach_tag = <<<EOD
 <!-- ■ BEGIN id:attach -->
 <div id="attach">
 $hr
 $attaches
 </div><!-- □ END id:attach -->
EOD;
}

$notes_tag = '';
if ($notes != '') {
 $notes_tag = <<<EOD
 <!-- ■BEGIN id:note -->
 <div id="note">
   $notes
 </div>
 <!-- □END id:note -->
EOD;
}

$trackback_tag = '';
if ($trackback) {
   $tb_id = tb_get_id($_page);
   $tb_cnt = tb_count($_page);
   $tb_label = $qm->replace("qhm_init.tb_label", $tb_cnt);
  $trackback_tag = <<<EOD
<div id="trackback"><!-- ■BEGIN id:trackback -->
<a href="{$script}?plugin=tb&amp;__mode=view&amp;tb_id={$tb_id}" onClick="OpenTrackback(this.href); return false">$tb_label</a> |
EOD;
}

$referer_tag = '';
if($referer){
	$ref_label = $qm->m['qhm_init']['ref_label'];
	$referer_tag = <<<EOD
<a href="{$script}?plugin=referer&amp;page=$r_page">$ref_label</a>
</div><!-- □ END id:trackback -->
EOD;
}

$related_tag = '';
if ($related != '') {
  $related_tag = <<<EOD
<!-- ■ BEGIN id:related -->
<div id="related">
Link: {$related}
</div>
<!-- □ END id:related -->
EOD;
}

$summarystr = <<<EOD
<!-- summary start -->
{$summaryflag_start}
$notes_tag
$trackback_tag
$referer_tag
$related_tag
$attach_tag
$summaryflag_end
<!-- summary end -->
EOD;
$qt->setv('summary', $summarystr);

//-------------------------------------------------
//
// ログインをチェックし、ログアウトしてれば再ログインをさせるjavascriptの読み込み
//-------------------------------------------------
if (exist_plugin('check_login')) {
	do_plugin_convert('check_login');
}

if (is_qblog())
{
	if ($qblog_defaultpage === $title)
	{
		$qt->setv('this_page_title', $qblog_title.' - '.$page_title);
		if ( ! $qt->getv('editable'))
		{
			$qt->setv('this_right_title', $qblog_title);
		}
		else
		{
			$qt->appendv('this_right_title', $qblog_title);
		}
	}
	else
	{
		$qt->setv('this_page_title', $qt->getv('this_right_title') . ' - ' . $qblog_title.' - '.$page_title);
	}
}

/* End of file qhm_init.php */
/* Location: ./lib/qhm_init.php */
?>
