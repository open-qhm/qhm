<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: html.php,v 1.57 2006/04/15 17:33:35 teanan Exp $
// Copyright (C)
//   2002-2006 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// HTML-publishing related functions

// Show page-content
function catbody($title, $page, $body)
{
	global $script, $script_ssl, $vars, $arg, $defaultpage, $whatsnew, $help_page, $hr;
	global $attach_link, $related_link, $cantedit, $function_freeze;
	global $search_word_color, $_msg_word, $foot_explain, $note_hr, $head_tags;
	global $trackback, $trackback_javascript, $referer, $javascript;
	global $nofollow, $non_list;
	global $_LANG, $_LINK, $_IMAGE;

	global $pkwk_dtd;     // XHTML 1.1, XHTML1.0, HTML 4.01 Transitional...
	global $page_title;   // Title of this site
	global $do_backup;    // Do backup or not
	global $modifier;     // Site administrator's  web page
	global $modifierlink; // Site administrator's name
	global $owneraddr;    // Site owner address
	global $ownertel;     // Site owner tel
	global $headcopy;     // Site headcopy
	global $menuadmin;    // Menu Administrate Page
	global $style_type, $style_name, $logo_image, $logo_header;
	global $keywords, $description, $accesstag, $ga_tracking_id, $killer_fg, $killer_bg, $noindex, $accesstag_moved; //for skin by hokuken
	global $qhm_adminmenu;	// Site administration menu 20 JUN 2007
	global $custom_meta;	// Insert meta tag for specific meta tag
	global $adcode;			// AD code (exp. Google Adwords, Analytics ... )  25 JLY 2007 by hokuken.com
	global $nowindow;       // Disable including external_link.js
	global $killer_page2;   // for Killer page Design 2
	global $no_qhm_licence; // qhm licence
	global $include_skin_file_path; //orignal skin setting
	global $qhm_access_key;
	global $reg_exp_host;
	global $unload_confirm, $check_login;
	global $enable_wp_theme, $enable_wp_theme_name, $wp_add_css; //WordPress用のテーマ
	global $mobile_redirect, $googlemaps_apikey;
	global $other_plugins, $other_plugin_categories;
	global $default_script, $init_scripts;
	global $enable_smart_style, $smart_name; // smart phone
	global $is_update;
	global $enable_fitvids;

	// body部分以外は、元々の$script を使う（通常のリンク設定）を使う
	// 結果、$body内は、script_sslを使ったリンクになるが、ナビ、メニューなどは、元の$scriptを必ず使う
	$script = $init_scripts['normal'];
	$script_ssl = $init_scripts['ssl'];

	if (! file_exists(SKIN_FILE) || ! is_readable(SKIN_FILE))
		die_message('SKIN_FILE is not found');

	$_LINK = $_IMAGE = array();

	// Add JavaScript header when ...
	if ($trackback && $trackback_javascript) $javascript = 1; // Set something If you want
	if (! PKWK_ALLOW_JAVASCRIPT) unset($javascript);

	$_page  = isset($vars['page']) ? $vars['page'] : '';
	$r_page = rawurlencode($_page);

	//QHM Template
	$qt = get_qt();
	if (!$qt->set_page) {
		$qt->set_page($_page);
	}

	//QHM Messages
	$qm = get_qm();

	// Set $_LINK for skin
	$_LINK['add']      = "$script?cmd=add&amp;page=$r_page";
	$_LINK['backup']   = "$script?cmd=backup&amp;page=$r_page";
	$_LINK['copy']     = "$script?plugin=template&amp;refer=$r_page";
	$_LINK['diff']     = "$script?cmd=diff&amp;page=$r_page";
	$_LINK['edit']     = "$script?cmd=edit&amp;page=$r_page";
	$_LINK['filelist'] = "$script?cmd=filelist";
	$_LINK['freeze']   = "$script?cmd=freeze&amp;page=$r_page";
	$_LINK['help']     = "$script?" . rawurlencode($help_page);
	$_LINK['list']     = "$script?cmd=list";
	$_LINK['new']      = "$script?plugin=newpage&amp;refer=$r_page";
	$_LINK['rdf']      = "$script?cmd=rss&amp;ver=1.0";
	$_LINK['recent']   = "$script?" . rawurlencode($whatsnew);
	$_LINK['refer']    = "$script?plugin=referer&amp;page=$r_page";
	$_LINK['reload']   = "$script?$r_page";
	$_LINK['rename']   = "$script?plugin=rename&amp;refer=$r_page";
	$_LINK['delete']   = "$script?plugin=delete&amp;page=$r_page";
	$_LINK['rss']      = "$script?cmd=rss";
	$_LINK['rss10']    = "$script?cmd=rss&amp;ver=1.0"; // Same as 'rdf'
	$_LINK['rss20']    = "$script?cmd=rss&amp;ver=2.0";
	$_LINK['search']   = "$script?cmd=search";
	$_LINK['top']      = dirname($script . 'dummy.php'). '/';

	if ($trackback) {
		$tb_id = tb_get_id($_page);
		$_LINK['trackback'] = "$script?plugin=tb&amp;__mode=view&amp;tb_id=$tb_id";
	}
	$_LINK['unfreeze'] = "$script?cmd=unfreeze&amp;page=$r_page";
	$_LINK['upload']   = "$script?plugin=attach&amp;pcmd=upload&amp;page=$r_page";
	$_LINK['menuadmin']   = "$script?" . rawurlencode($menuadmin);  //Hokuken.com original
	$_LINK['qhm_adminmenu'] = qhm_get_script_path()."?cmd=qhmauth";
	$_LINK['qhm_logout'] = "$script?cmd=qhmlogout";
	$_LINK['qhm_setting'] = "$script?cmd=qhmsetting";
	$_LINK['edit_menu'] = "$script?cmd=edit&amp;page=MenuBar";
	$_LINK['edit_menu2'] = "$script?cmd=edit&amp;page=MenuBar2";
	$_LINK['edit_navi'] = "$script?cmd=edit&amp;page=SiteNavigator";
	$_LINK['edit_navi2'] = "$script?cmd=edit&amp;page=SiteNavigator2";
	$_LINK['edit_header'] = "$script?cmd=edit&amp;page=SiteHeader";
	$_LINK['yetlist']     = "$script?cmd=yetlist";

	// Compat: Skins for 1.4.4 and before
	$link_add       = & $_LINK['add'];
	$link_new       = & $_LINK['new'];	// New!
	$link_edit      = & $_LINK['edit'];
	$link_diff      = & $_LINK['diff'];
	$link_top       = & $_LINK['top'];
	$link_list      = & $_LINK['list'];
	$link_filelist  = & $_LINK['filelist'];
	$link_search    = & $_LINK['search'];
	$link_whatsnew  = & $_LINK['recent'];
	$link_backup    = & $_LINK['backup'];
	$link_help      = & $_LINK['help'];
	$link_trackback = & $_LINK['trackback'];	// New!
	$link_rdf       = & $_LINK['rdf'];		// New!
	$link_rss       = & $_LINK['rss'];
	$link_rss10     = & $_LINK['rss10'];		// New!
	$link_rss20     = & $_LINK['rss20'];		// New!
	$link_freeze    = & $_LINK['freeze'];
	$link_unfreeze  = & $_LINK['unfreeze'];
	$link_upload    = & $_LINK['upload'];
	$link_template  = & $_LINK['copy'];
	$link_refer     = & $_LINK['refer'];	// New!
	$link_rename    = & $_LINK['rename'];
	$link_delete    = & $_LINK['delete'];
	$link_menuadmin = & $_LINK['menuadmin']; //Hokuken.com original
	$link_copy      = & $_LINK['copy'];
	$link_qhm_adminmenu = & $_LINK['qhm_adminmenu']; //Hokuken.com original
	$link_qhm_logout = & $_LINK['qhm_logout']; //Hokuken.com original
	$link_qhm_setting = & $_LINK['qhm_setting']; //Hokuken.com original
	$link_edit_menu = & $_LINK['edit_menu']; //Hokuken.com original
	$link_edit_menu2 = & $_LINK['edit_menu2'];
	$link_edit_navi = & $_LINK['edit_navi']; //Hokuken.com original
	$link_edit_navi2 = & $_LINK['edit_navi2']; //Hokuken.com original
	$link_edit_header = & $_LINK['edit_header']; //Hokuken.com original
	$link_yetlist = & $_LINK['yetlist']; //Hokuken.com original

	// Init flags
	$is_page = (is_pagename($_page) && $_page != $whatsnew);
	$is_read = (arg_check('read') && is_page($_page));
	$is_freeze = is_freeze($_page);

	// Last modification date (string) of the page
	$lastmodified = $is_read ?  format_date(get_filetime($_page)) .
		' ' . get_pg_passage($_page, FALSE) : '';

	// List of attached files to the page
	$attaches = ($attach_link && $is_read && exist_plugin_action('attach')) ?
		attach_filelist() : '';

	// List of related pages
	$related  = ($related_link && $is_read) ? make_related($_page) : '';

	// List of footnotes
	ksort($foot_explain, SORT_NUMERIC);
	$notes = ! empty($foot_explain) ? $note_hr . join("\n", $foot_explain) : '';

	// Tags will be inserted into <head></head>
	$head_tag = ! empty($head_tags) ? join("\n", $head_tags) ."\n" : '';

	// 1.3.x compat
	// Last modification date (UNIX timestamp) of the page
	$fmt = $is_read ? get_filetime($_page) + LOCALZONE : 0;

	// Search words
	if ($search_word_color && isset($vars['word'])) {
		$body = '<div class="small">' . $_msg_word . htmlspecialchars($vars['word']) .
			'</div>' . $hr . "\n" . $body;

		// BugTrack2/106: Only variables can be passed by reference from PHP 5.0.5
		// with array_splice(), array_flip()
		$words = preg_split('/\s+/', $vars['word'], -1, PREG_SPLIT_NO_EMPTY);
		$words = array_splice($words, 0, 10); // Max: 10 words
		$words = array_flip($words);

		$keys = array();
		foreach ($words as $word=>$id) $keys[$word] = strlen($word);
		arsort($keys, SORT_NUMERIC);
		$keys = get_search_words(array_keys($keys), TRUE);
		$id = 0;
		foreach ($keys as $key=>$pattern) {
			$s_key    = htmlspecialchars($key);
			$pattern  = '/' .
				'<textarea[^>]*>.*?<\/textarea>' .	// Ignore textareas
				'|' . '<[^>]*>' .			// Ignore tags
				'|' . '&[^;]+;' .			// Ignore entities
				'|' . '(' . $pattern . ')' .		// $matches[1]: Regex for a search word
				'/sS';
			$decorate_Nth_word = create_function(
				'$matches',
				'return (isset($matches[1])) ? ' .
					'\'<strong class="word' .
						$id .
					'">\' . $matches[1] . \'</strong>\' : ' .
					'$matches[0];'
			);
			$body  = preg_replace_callback($pattern, $decorate_Nth_word, $body);
			$notes = preg_replace_callback($pattern, $decorate_Nth_word, $notes);
			++$id;
		}
	}





	//-----------------------------------------------------------------------
	//
	// customized by hokuken for QHM (2009/1/28)
	//
	//-----------------------------------------------------------------------


	//----------------- 携帯の場合の処理 --------------------------------------
	if( preg_match('/keitai.skin.php$/', SKIN_FILE) ){
		require(LIB_DIR.'qhm_init.php');
		require(LIB_DIR.'qhm_init_main.php');
		require(SKIN_FILE);
		return;
	}
	//------------------- IF UA is mobile, end here -----------------------

	//---------- KILLERPAGE: でもKILLERPAGE2:に統合 いつか消したい --------------
	if( $killer_fg != '' ){

		//load common setting and output header
		require(LIB_DIR.'qhm_init.php');

		$killer_page2['fg'] = $killer_fg;
		$killer_page2['bg'] = $killer_bg;
		$killer_page2['width'] = 700;
		$killer_page2['padding'] = 60;
		$killer_page2['bg_body'] = '#fff';
		$killer_page2['fg_body'] = '#000';

		require(LIB_DIR.'qhm_init_killer.php');

		$longtaketime = getmicrotime() - MUTIME;
		$taketime = sprintf('%01.03f', $longtaketime);
		$qt->setv('taketime', $taketime);
	}
	//--------------------------- いつか消したい end here ----------------


	///////////////////////////////////////////////////////////////////
	//
	// Main
	//

	//common setting
	require(LIB_DIR.'qhm_init.php');
	$qt->enable_cache = $qt->getv('editable') ? false : $qt->enable_cache;

	$qt->set_encode(($shiftjis || $eucjp) ? true : false);
	if( $shiftjis ){
		$output_encode = 'Shift_JIS';
	}
	else if( $eucjp ){
		$output_encode = 'EUC-JP';
	}
	else{
		$output_encode = CONTENT_CHARSET;
	}

	define('WORDPRESS_CHARSET', $output_encode);

	//output common header (available change encode)
	$qt->setv('meta_content_type', qhm_output_dtd($pkwk_dtd, CONTENT_CHARSET, $output_encode));

	//------- KILLERPAGE2: セールスレター型デザイン -------------------
	if( isset($killer_page2['fg']) != ''){
		require(LIB_DIR.'qhm_init_killer.php');

	//JQuery Include
	$jquery_script = '';
	$jquery_cookie_script = '';

	if ($qt->getv('jquery_include'))
	{
		$jquery_script = '<script type="text/javascript" src="js/jquery.js"></script>';
		$jquery_cookie_script = '<script type="text/javascript" src="js/jquery.cookie.js"></script>';
	}
	$bootstrap_style = $bootstrap_script = '';
	if ($qt->getv('bootstrap_script'))
	{
		$bootstrap_style = $qt->getv('bootstrap_style');
		$bootstrap_script = $qt->getv('bootstrap_script');
	}
	$qt->setv('jquery_script', ($bootstrap_style. $jquery_script . $bootstrap_script));
	$qt->setv('jquery_cookie_script', $jquery_cookie_script);



		$longtaketime = getmicrotime() - MUTIME;
		$taketime = sprintf('%01.03f', $longtaketime);
		$qt->setv('taketime', $taketime);

		$qt->read('skin/killerpage2/pukiwiki.skin.php');
		return;
	}
	//-------------------------------------------------------------


	// ---- include main design skin file ----
	if (isset($_SESSION['temp_skin']) && strlen($_SESSION['temp_skin']) > 0 )
	{
		$style_type =  $_SESSION['temp_style_type'];
	}

	//pluginでデザインが指定されている場合
	if ($include_skin_file_path!='')
	{
		$style_name = $include_skin_file_path;
	}
	require(LIB_DIR.'qhm_init_main.php');

	// meta:GENERATOR
	$generator_tag = '<meta name="GENERATOR" content="Quick Homepage Maker; version='. QHM_VERSION. '; haik='.(is_bootstrap_skin() ? 'true' : 'false').'" />' . "\n";
	$qt->prependv_once('generator_tag', 'beforescript', $generator_tag);

	//-------------------------------------------------
	// ogp タグを挿入
	//-------------------------------------------------
	if (exist_plugin('ogp')) {
		plugin_ogp_set_template($body);
	}

	//独自のテンプレートファイルをチェック
	$skin_file = SKIN_DIR . "{$style_name}/pukiwiki.skin.php";
	if ($qt->getv('layout_name'))
	{
		$layout_file = $qt->getv('layout_name').'.skin.php';
		$layout_path = SKIN_DIR . "{$style_name}/{$layout_file}";
		if (file_exists($layout_path))
		{
			$skin_file = $layout_path;
		}
	}
	else
	{
		$layout_prefix = 'content';
		if ($_page === $defaultpage)
		{
			$layout_prefix = 'default';
		}
		else if (is_qblog())
		{
			$layout_prefix = 'article';
		}

		$layout_name = isset($style_config["{$layout_prefix}_layout"]) ? $style_config["{$layout_prefix}_layout"] : "";
		$layout_path = SKIN_DIR . "{$style_name}/{$layout_name}.skin.php";
		if (file_exists($layout_path))
		{
			$skin_file = $layout_path;
		}
	}

	if( ! file_exists($skin_file)){
		$skin_file = SKIN_FILE;  //デフォルトの位置
	}

	// Read design config for customize
	$style_config = read_skin_config($style_name);
	$skin_custom_vars = get_skin_custom_vars($style_name);

	$custom_css = make_custom_css($style_name);
	$qt->prependv('beforescript', $custom_css);

	if (isset($style_config['bootstrap']) && $style_config['bootstrap'] !== false)
	{
		$qt->setv('jquery_include', true);
	}

	// Default Eyecatch
	if (isset($skin_custom_vars['default_eyecatch'])
	&& $skin_custom_vars['default_eyecatch']
	&& $qt->getv('main_visual') === ''
	&& exist_plugin('eyecatch'))
	{
		$bg_image = $color = '';
		if (isset($skin_custom_vars['eyecatch_bgimage']))
		{
			$bg_image = $skin_custom_vars['eyecatch_bgimage'];
			$bg_image = (is_url($bg_image, TRUE, TRUE) || file_exists(get_file_path($bg_image))) ? $bg_image : '';
		}
		if (isset($skin_custom_vars['enable_eyecatch_bgimage']) && ! $skin_custom_vars['enable_eyecatch_bgimage'])
		{
			$bg_image = '';
		}
		if (isset($skin_custom_vars['eyecatch_color']))
		{
			$color = 'color='.$skin_custom_vars['eyecatch_color'];
		}
		if (is_qblog())
		{
			$title_copy = $qblog_title;
		}
		else
		{
			if (isset($skin_custom_vars['eyecatch_title_type']) &&
					trim($skin_custom_vars['eyecatch_title_type']) == 'site')
			{
				$title_copy = $page_title;
			}
			else
			{
				if ($_page === $defaultpage)
				{
					$title_copy = $page_title;
				}
				else
				{
					$title_copy = get_page_title($_page);
				}
			}
		}
		$content = <<< EOD
! $title_copy
EOD;

		plugin_eyecatch_convert($bg_image, $color, '__default', $content);
	}

	if ($qt->getv('no_eyecatch'))
	{
		$qt->setv('main_visual', "<!-- no eyecatch -->");
	}

	// Determine emptiness of eyecatch
	$qt->setv('eyecatch_is_empty', ($qt->getv('no_eyecatch') || $qt->getv('main_visual') === ''));

	if (isset($skin_custom_vars['palette_color']) && trim($skin_custom_vars['palette_color']))
	{
		$qt->setv('palette_color', trim($skin_custom_vars['palette_color']));
		$qt->setv(
			'palette_color_class',
			'haik-palette-' . trim($skin_custom_vars['palette_color'])
		);
	}

	//JQuery Include
	$jquery_script = '';
	$jquery_cookie_script = '';
	if ($qt->getv('jquery_include'))
	{
		$jquery_script = '<script type="text/javascript" src="js/jquery.js"></script>';
		$jquery_cookie_script = '<script type="text/javascript" src="js/jquery.cookie.js"></script>';
	}
	if ($qt->getv('bootstrap_script'))
	{
		$bootstrap_script = $qt->getv('bootstrap_script');
	}
	$qt->setv('jquery_script', ($jquery_script . $bootstrap_script));
	$qt->setv('jquery_cookie_script', $jquery_cookie_script);


	$longtaketime = getmicrotime() - MUTIME;
	$taketime = sprintf('%01.03f', $longtaketime);
	$qt->setv('taketime', $taketime);

	//UniversalAnalytics Tracking Code
	if ($code = $qt->getv('ga_universal_analytics'))
	{
	    $qt->appendv('beforescript', $code);
	}


	//------------------------------------------------------------------
	// WordPressデザイン
	if( $enable_wp_theme &&
		($vars['cmd']!='qhmsetting' && $vars['plugin']!='qhmsetting')
	)
	{
		define('TEMPLATEPATH', 'skin/wordpress/'.$enable_wp_theme_name);
		include(LIB_DIR.'wp_adapter.php');

		wp_load_functions();

		$skin_file = get_wp_skin_file();
	}
	//-------------------------------------------------------------------

	//-------------------------------------------------------------------
	// 	プレビュー用のskinファイルを表示
	$tmpfilename = '';
	if (isset($_SESSION['temp_skin']) && strlen($_SESSION['temp_skin']) > 0 )
// && ($vars['cmd']!='qhmsetting' && $vars['plugin']!='qhmsetting'))
	{
		$tmpfilename = $skin_file = tempnam(realpath(CACHEQHM_DIR), 'qhmdesign');
		file_put_contents($skin_file, $_SESSION['temp_skin']);

		$qt->setv('default_css', $bootstrap_css.$_SESSION['temp_css']);
		$qt->setv('style_path', $_SESSION['temp_style_path']);
	}
	//-------------------------------------------------------------------

	//skinファイルを読み込んで、表示
	$qt->read($skin_file, $_page);

	// 一時ファイルの削除
	if (file_exists($tmpfilename) && strpos(basename($tmpfilename), 'qhmdesign') === 0)
	{
		unlink($tmpfilename);
	}
}

// Show 'edit' form
function edit_form($page, $postdata, $digest = FALSE, $b_template = TRUE)
{
	global $script, $vars, $rows, $cols, $hr, $function_freeze;
	global $_btn_preview, $_btn_repreview, $_btn_update, $_btn_cancel, $_msg_help;
	global $whatsnew, $_btn_template, $_btn_load, $load_template_func;
	global $notimeupdate;
	global $qhm_access_key;
	global $qblog_defaultpage, $style_name, $date_format, $qblog_default_cat;
	$qt = get_qt();

	//accesskey setting
	$accesskey = array();
	foreach(array('r','p','s','c') as $v){
		$accesskey[$v] = $qhm_access_key ? 'accesskey="'.$v.'"' : '';
	}


	// Newly generate $digest or not
	if ($digest === FALSE) $digest = md5(join('', get_source($page)));

	$refer = $template = $headertitle = '';

 	// Add plugin
	$addtag = $add_top = '';
	if(isset($vars['add'])) {
		global $_btn_addtop;
		$addtag  = '<input type="hidden" name="add"    value="true" />';
		$add_top = isset($vars['add_top']) ? ' checked="checked"' : '';
		$add_top = '<input type="checkbox" name="add_top" ' .
			'id="_edit_form_add_top" value="true"' . $add_top . ' />' . "\n" .
			'  <label for="_edit_form_add_top" class="checkbox">' .
				'<span class="small">' . $_btn_addtop . '</span>' .
			'</label>';
	}

	if($load_template_func && $b_template) {
		$pages  = array();
		foreach(get_existpages() as $_page) {
			if ($_page == $whatsnew || check_non_list($_page))
				continue;
			$s_page = htmlspecialchars($_page);
			$pages[$_page] = '   <option value="' . $s_page . '">' .
				$s_page . '</option>';
		}
		ksort($pages);
		$s_pages  = join("\n", $pages);
		$template = <<<EOD
  <select name="template_page">
   <option value="">-- $_btn_template --</option>
$s_pages
  </select>
  <input type="submit" name="template" value="$_btn_load" {$accesskey['r']} />
  <br />
EOD;


	}
	//新規作成の場合、ページ名を大見出しとして挿入する
	if (isset($vars['refer']) && $vars['refer'] != '')
	{
		$headertitle = "\n\n". '* ' . strip_bracket($page) . "\n\n";
	}

	$r_page      = rawurlencode($page);
	$s_page      = htmlspecialchars($page);
	$s_digest    = htmlspecialchars($digest);
	$s_postdata  = htmlspecialchars($refer . $headertitle . $postdata);
	$s_original  = isset($vars['original']) ? htmlspecialchars($vars['original']) : $s_postdata;
	$b_preview   = isset($vars['preview']); // TRUE when preview
	$btn_preview = $b_preview ? $_btn_repreview : $_btn_preview;

	// Checkbox 'do not change timestamp'
	$add_notimestamp = '';
	if ($notimeupdate != 0) {
		global $_btn_notchangetimestamp;
		$checked_time = isset($vars['notimestamp']) ? ' checked="checked"' : '';
		// Only for administrator
		if ($notimeupdate == 2) {
			$add_notimestamp = '   ' .
				'<input type="password" name="pass" size="12" />' . "\n";
		}
		$add_notimestamp = '<label for="_edit_form_notimestamp" class="checkbox"><input type="checkbox" name="notimestamp" ' .
			'id="_edit_form_notimestamp" value="true"' . $checked_time . ' tabindex="9" />' . "\n" .
			'   ' . '<span class="small">' .
			$_btn_notchangetimestamp . '</span></label>' . "\n" .
			$add_notimestamp .
			'&nbsp;';
	}

	$buttons_align = 'left';
	$blog_cancel_button = 'right';
	if (is_bootstrap_skin())
	{
		$buttons_align = 'right';
		$blog_cancel_button = 'left';
	}
	else
	{
		//Bootstrap の読み込み
		$include_bs = '
<link rel="stylesheet" href="skin/bootstrap/css/bootstrap-custom.min.css" />
<script type="text/javascript" src="skin/bootstrap/js/bootstrap.min.js"></script>';
		$qt->appendv_once('include_bootstrap_pub', 'beforescript', $include_bs);
	}

	// !ブログ用編集フォーム
	if ($page !== $qblog_defaultpage && is_qblog())
	{
		//メタデータを取得
		$data = get_qblog_post_data($page);
		$data['title'] = isset($vars['title']) ? $vars['title'] : $data['title'];
		$data['category'] = isset($vars['category']) ? $vars['category'] : $data['category'];
		$data['image'] = isset($vars['image']) ? $vars['image'] : $data['image'];

		$date = get_qblog_date($date_format, $page);
		if (isset($vars['qblog_date']) && $date !== trim($vars['qblog_date']))
		{
			$dates = array_pad(explode('-', $vars['qblog_date'], 3), 3, 0);
			$valid = checkdate($dates[1], $dates[2], $dates[0]);
			$date = $valid ? trim($vars['qblog_date']) : $date;
		}

		$category = (isset($data['category']) && strlen(trim($data['category'])) > 0) ? $data['category'] : '';
		$qblog_categories = array_keys(get_qblog_categories());
		$qblog_cat_json = json_encode($qblog_categories);
		$h_qblog_cat_json = h(json_encode($qblog_categories));
		$qblog_cat_list = '<ul id="qblog_categories_selector" class="qblog_categories collapse">';
		foreach ($qblog_categories as $cat)
		{
			$qblog_cat_list .= '<li>' . h($cat) . '</li>';
		}
		$qblog_cat_list .= '</ul>';

		$h2title = '新規投稿';
		if (is_page($page))
		{
			$h2title = $data['title'].'の編集';
		}
		$s_h2title = h($h2title);
		$s_blog_title = h($data['title']);

		$body = <<< EOD
<link rel="stylesheet" href="js/datepicker/css/datepicker.css" />
<link rel="stylesheet" href="plugin/qblog/qblog.css" />
<script src="js/datepicker/js/bootstrap-datepicker.js"></script>
<script tyle="text/javascript">
$(function(){
	$('#qblog_datepicker').datepicker({
		language: "japanese"
//		format: "yyyy/mm/dd"
	});
	if ($("input[name=category]").val().length == 0) {
		$('#qblog_cat_trigger').click();
	}

	if ($('h2.title').length == 0) {
		$("#edit_form_main").before('<h2 class="title">{$s_h2title}</h2>');
	}
	$('h2.title').text('{$s_h2title}');

	$('a.show-thumbnail').click(function(){
		if ($(this).next().is(':visible')) {
			$(this).next().hide();
		}
		else {
			$(this).next().show();
		}
		return false;
	});

});
</script>

<div class="qblog_edit_form">
<form action="$script" method="post" class="form-horizontal" id="edit_form_main">
$template
  $addtag
  <input type="hidden" name="cmd"    value="edit" />
  <input type="hidden" name="page"   value="{$s_page}" />
  <input type="hidden" name="digest" value="{$s_digest}" />
  <fieldset>
    <div class="form-group">
      <label class="control-label col-sm-2">日付</label>
      <div class="controls col-sm-10"><input type="text" name="qblog_date" id="qblog_datepicker" tabindex="1" class="datepicker form-control" size="16" value="{$date}"  data-date="{$date}"  data-date-format="yyyy-mm-dd" class="form-control" /></div>
    </div>
    <div class="form-group">
      <label class="control-label col-sm-2">タイトル</label>
      <div class="controls col-sm-10"><input type="text" name="title" value="{$s_blog_title}" tabindex="2" class="form-control" /></div>
  	</div>
    <div class="form-group">
      <label class="control-label col-sm-2">カテゴリ</label>
      <div class="controls col-sm-10">
        <div class="input-group">
          <input type="text" name="category" value="{$category}" placeholder="{$qblog_default_cat}" tabindex="3" class="form-control" data-provide="typeahead" data-source="{$h_qblog_cat_json}" autocomplete="off" />
          <span class="input-group-btn">
            <button type="button" id="qblog_cat_trigger" class="btn btn-default qhm-btn-default" data-toggle="collapse" data-target="#qblog_categories_selector" style="color:#333">
              カテゴリ
              <span class="caret"></span>
            </button>
          </span>
        </div>
        {$qblog_cat_list}
      </div>
    </div>
      <div class="form-group">
          <label class="control-label col-sm-2">記事の内容</label>
          <div class="controls col-sm-10">
              <textarea name="msg" id="msg" tabindex="4" rows="20" class="form-control">$s_postdata</textarea>
  		</div>
  	</div>
      <div class="form-group">
          <div class="controls col-sm-10 col-sm-offset-2">
	  		<a class="show-thumbnail" href="#">サムネイルを指定する &gt;&gt;</a>
  			<div class="set-thumbnail">
  				<small>自動で本文の画像が使われます。<br />特別に指定したい場合、画像を画像名またはURLで指定してください。</small>
                  <p style="color:#333;">画像名またはURL：<input type="text" name="image" value="{$data['image']}" tabindex="5" class="form-control" /></p>
  				<p><small><span class="swfu"><a href="swfu/index_child.php">&gt;&gt;QHMのファイル管理（SWFU）を使って画像をアップする</a></span></small></p>
  			</div>
<!--  			<span class="swfu"><a href="swfu/index_child.php"><i class="icon-picture"></i>SWFU</a><span>
			<p class="help-block">SWFUの画像を使う場合、画像詳細画面の<b>URL</b>をコピペしてください。</p>
-->
  		</div>
  	</div>
      <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2">
          <div style="float:{$buttons_align};">
            <input type="submit" name="preview" value="$btn_preview" tabindex="6" class="qhm-btn-default"/>
            <input type="submit" name="write"   value="$_btn_update" tabindex="7" class="qhm-btn-primary"/>
        		$add_notimestamp
            $add_top
        		<textarea name="original" rows="1" cols="1" style="display:none">$s_original</textarea>
          </div>
          <div style="float:{$buttons_align};">
            <input type="submit" name="cancel" value="$_btn_cancel" tabindex="8" class="btn-link"/>
          </div>
      </div>
  	</div>
  </fieldset>
</form>
</div>

EOD;
	}
	// !標準編集フォーム
	else
	{
		$body = <<< EOD

<div class="edit_form">
 <form action="$script" method="post" style="margin-bottom:0px;" id="edit_form_main">
$template
  $addtag
  <input type="hidden" name="cmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="hidden" name="digest" value="$s_digest" />
  <div class="form-group">
    <textarea name="msg" id="msg" rows="$rows" cols="$cols" tabindex="2" class="form-control">$s_postdata</textarea>
  </div>
  <div style="float:$buttons_align;">
   <input type="submit" name="preview" value="$btn_preview" tabindex="4" class="qhm-btn-default"/>
   <input type="submit" name="write"   value="$_btn_update" tabindex="5" class="qhm-btn-primary"/>
   $add_top
   $add_notimestamp
  </div>
  <textarea name="original" rows="1" cols="1" style="display:none">$s_original</textarea>
 </form>
 <form action="$script" method="post" style="margin-top:0px;margin-left:5px;float:$buttons_align;" id="edit_form_cancel">
  <input type="hidden" name="cmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="submit" name="cancel" value="$_btn_cancel" tabindex="6" class="btn-link"/>
 </form>
 <div style="clear:both;"></div>
</div>
EOD;

	}


	$addscript = <<< EOD
<script data-qhm-plugin="edit">
$(function(){

  if ($("h2.title").length == 0) {
    $(".edit_form").before('<h2 class="title">$s_page の編集</h2>');
  }
  $("h2.title").css({fontSize: "14px", marginBottom: '15px'});

  $("#editboxlink").on("click", function(){
    if ($(".qblog_edit_form").length) {
      QHM.scroll(".qblog_edit_form", 300);
    }
    else {
      QHM.scroll("h2.title", 300);
    }
    $("#msg").focus();
    return false;
  });

  if ($("#preview_body").length) {
  }
  else {
    $(".qhm-eyecatch").hide();

    setTimeout(function(){
      $("html, body").animate({scrollTop: $("h2.title").offset().top}, 300);
      $("#msg").focus();
    }, 25);
  }
});
</script>
EOD;
    $qt->appendv_once("plugin_edit_form_script", 'lastscript', $addscript);

    // List of attached files to the page by hokuken.com
    $attaches = (exist_plugin_action('attach')) ?
    attach_filelist(true) : '';
    if ($attaches !== '') {
        $body .= <<< EOD
<script type="text/javascript" src="js/yahoo.js"></script>
<script type="text/javascript" src="js/event.js"></script>
<script type="text/javascript" src="js/dom.js"></script>

<style type="text/css">
.yui-tt {
	position: absolute;
	padding: 5px;
	background-color:#eee;
	border:1px solid #aaa;
}
</style>
<script type="text/javascript" src="js/container.js"></script>
<script type="text/javascript">
    function init() {
        var el = document.getElementById('attachlist');
        if(el != null){

	        var list = el.getElementsByTagName('a');
	        for( var i=0; i<list.length; i++ ) {
	            if( list[i].getAttribute("rel") == "attachhref" ){
					var el = 'tooltip'+i;
					var url = list[i].href;
					var title = '<img src="'+list[i].href+'">';
					if ( list[i].title ) title += '<br>'+list[i].innerHTML;
					var tp = new YAHOO.widget.Tooltip( el, { context:list[i], text: title, autodismissdelay: 7500 } );
				}
	        }

        }

        var el = document.getElementById('swfulist');
        if(el != null){
        	var list = el.getElementsByTagName('a');

	        for( var i=0; i<list.length; i++ ) {
	            if( list[i].getAttribute("rel") == "attachhref" ){
					var el = 'tooltip'+i;
					var url = list[i].getAttribute("url");
					var title = '<img src="'+url+'">';
					if ( list[i].title ) title += '<br>'+list[i].innerHTML;
					var tp = new YAHOO.widget.Tooltip( el, { context:list[i], text: title, autodismissdelay: 7500 } );
				}
	        }
		}
  }
  YAHOO.util.Event.addListener(window, "load", init);
</script>
EOD;

	$body .= '<br /><div id="attachlist" style="border: 2px dashed #666;padding:5px 10px;background-color:#eee">' . $attaches . '</div>';

}

	$qm = get_qm();
	$helpstr = $qm->m['html']['view_help_message'];

	//list up swfu files
	if( has_swfu() )
	{
		require_once(SWFU_TEXTSQL_PATH);
		$db = new CTextDB(SWFU_IMAGEDB_PATH);

		$imgtitle = $qm->m['html']['img_title'];
		$imgtitle2 = $qm->m['html']['img_title2'];
		$attcstr = $qm->m['html']['attach_message'];

		//! swfuの画像データを取得して表示をする
		$rs = $db->select('$page_name=="'.$page.'"', 'created desc');

		if(count($rs) > 0){
			$body .= '<div id="swfulist" style="border:1px #aaa dashed;margin-top:10px;padding:10px;font-size:12px">';
			$body .= '<b><a href="./swfu/index_child.php?page=FrontPage&KeepThis=true&TB_iframe=true&height=450&width=650" class="thickbox">' .$attcstr. '(SWFU)</a> : </b>';

			foreach($rs as $k=>$v){
				$path = SWFU_IMAGE_DIR.$v['name'];
				$prop = SWFU_DIR.'view.php?id='.$v['id'].'&page=FrontPage&KeepThis=true&TB_iframe=true&height=450&width=650';

				$body .= '<span style="padding:2px;margin-right:5px">';

				$atitle1 = $qm->replace("html.insert_title", $v['name']);
				$atitle2 = $qm->replace("html.ar_insert_title", $v['name']);


				if( preg_match('/\.(png|jpeg|jpg|gif)$/i', $v['name']) )
				{
					$title = h($v['name']);
					$body .= '<a href="'.$prop.'" url="'.$path.'" rel="attachhref" class="thickbox" title="'.$title.'"><img src="image/file.png" width="20" height="20" alt="file" style="border-width:0" />'.$v['name'].'</a>';
					$body .= <<<EOD
<a href="#" title="$atitle1" onclick="javascript:jQuery.clickpad.cpInsert('&show({$v['name']},,{$v['description']});'); return false;"><img src="image/ins-img.png" alt="$imgtitle"/></a><a href="#" title="$atitle2" onclick="javascript:jQuery.clickpad.cpInsert('\\n#show({$v['name']},aroundl,{$v['description']})\\n'); return false;"><img src="image/ins-img2.png" alt="$imgtitle2" /></a>
EOD;
				}
				else{
					$body .= '<a href="'.$path.'"><img src="image/file.png" width="20" height="20" alt="file" style="border-width:0" />'.$v['name'].'</a>';
					$body .= <<<EOD
<a href="#" title="{$v['name']}" onclick="javascript:insert('&dlbutton({$path});'); return false;"><img src="image/ins-btn.png" alt="$imgtitle"/></a>
EOD;
				}
				$body .= '</span>';
			}

		$body .= '</div>';


		}
	}

	return $body;
}


// Related pages
function make_related($page, $tag = '')
{
	global $script, $vars, $rule_related_str, $related_str;
	global $_ul_left_margin, $_ul_margin, $_list_pad_str;

	$links = links_get_related($page);

	if ($tag) {
		ksort($links);
	} else {
		arsort($links);
	}

	$_links = array();
	foreach ($links as $page=>$lastmod) {
		if (check_non_list($page)) continue;

		$r_page   = rawurlencode($page);

		//customized by hokuken.com
		$s_page = get_page_title($page);
//		$s_page   = htmlspecialchars($page);
		$passage  = get_passage($lastmod);
		$_links[] = $tag ?
			'<a href="' . $script . '?' . $r_page . '" title="' .
			$s_page . ' ' . $passage . '">' . $s_page . '</a>' :
			'<a href="' . $script . '?' . $r_page . '">' .
			$s_page . '</a>' . $passage;
	}
	if (empty($_links)) return ''; // Nothing

	if ($tag == 'p') { // From the line-head
		$margin = $_ul_left_margin + $_ul_margin;
		$style  = sprintf($_list_pad_str, 1, $margin, $margin);
		$retval =  "\n" . '<ul' . $style . '>' . "\n" .
			'<li>' . join($rule_related_str, $_links) . '</li>' . "\n" .
			'</ul>' . "\n";
	} else if ($tag) {
		$retval = join($rule_related_str, $_links);
	} else {
		$retval = join($related_str, $_links);
	}

	return $retval;
}

// User-defined rules (convert without replacing source)
function make_line_rules($str)
{
	global $line_rules;
	static $pattern, $replace;

	if (! isset($pattern)) {
		$pattern = array_map(create_function('$a',
			'return \'/\' . $a . \'/\';'), array_keys($line_rules));
		$replace = array_values($line_rules);
		unset($line_rules);
	}

	return preg_replace($pattern, $replace, $str);
}

// Remove all HTML tags(or just anchor tags), and WikiName-speific decorations
function strip_htmltag($str, $all = TRUE)
{
	global $_symbol_noexists;
	static $noexists_pattern;

	if (! isset($noexists_pattern))
		$noexists_pattern = '#<span class="noexists">([^<]*)<a[^>]+>' .
			preg_quote($_symbol_noexists, '#') . '</a></span>#';

	// Strip Dagnling-Link decoration (Tags and "$_symbol_noexists")
	$str = preg_replace($noexists_pattern, '$1', $str);

	if ($all) {
		// All other HTML tags
		return preg_replace('#<[^>]+>#',        '', $str);
	} else {
		// All other anchor-tags only
		return preg_replace('#<a[^>]+>|</a>#i', '', $str);
	}
}

// Remove AutoLink marker with AutLink itself
function strip_autolink($str)
{
	return preg_replace('#<!--autolink--><a [^>]+>|</a><!--/autolink-->#', '', $str);
}

// Make a backlink. searching-link of the page name, by the page name, for the page name
function make_search($page)
{
	global $script;

	$s_page = htmlspecialchars($page);
	$r_page = rawurlencode($page);

	return '<a href="' . $script . '?plugin=related&amp;page=' . $r_page .
		'">' . $s_page . '</a> ';
}

// Make heading string (remove heading-related decorations from Wiki text)
function make_heading(& $str, $strip = TRUE)
{
	global $NotePattern;

	// Cut fixed-heading anchors
	$id = '';
	$matches = array();
	if (preg_match('/^(!|\*{0,3})(.*?)\[#([A-Za-z][\w-]+)\](.*?)$/m', $str, $matches)) {
		$str = $matches[2] . $matches[4];
		$id  = & $matches[3];
	} else {
		$str = preg_replace('/^(?:\*{0,3}|!)/', '', $str);
	}

	// Cut footnotes and tags
	if ($strip === TRUE)
		$str = strip_htmltag(make_link(preg_replace($NotePattern, '', $str)));

	return $id;
}

// Separate a page-name(or URL or null string) and an anchor
// (last one standing) without sharp
function anchor_explode($page, $strict_editable = FALSE)
{
	$pos = strrpos($page, '#');
	if ($pos === FALSE) return array($page, '', FALSE);

	// Ignore the last sharp letter
	if ($pos + 1 == strlen($page)) {
		$pos = strpos(substr($page, $pos + 1), '#');
		if ($pos === FALSE) return array($page, '', FALSE);
	}

	$s_page = substr($page, 0, $pos);
	$anchor = substr($page, $pos + 1);

	if($strict_editable === TRUE &&  preg_match('/^[a-z][a-f0-9]{7}$/', $anchor)) {
		return array ($s_page, $anchor, TRUE); // Seems fixed-anchor
	} else {
		return array ($s_page, $anchor, FALSE);
	}
}

// Check HTTP header()s were sent already, or
// there're blank lines or something out of php blocks
function pkwk_headers_sent()
{
	if (PKWK_OPTIMISE) return;

	$file = $line = '';
	if (version_compare(PHP_VERSION, '4.3.0', '>=')) {
		if (headers_sent($file, $line))
			die('Headers already sent at ' .
				htmlspecialchars($file) .
				' line ' . $line . '.');
	} else {
		if (headers_sent())
			die('Headers already sent.');
	}
}

// Output common HTTP headers
function pkwk_common_headers()
{
	if (! PKWK_OPTIMISE) pkwk_headers_sent();

	if(defined('PKWK_ZLIB_LOADABLE_MODULE')) {
		$matches = array();
		if(ini_get('zlib.output_compression') &&
			preg_match('/\b(gzip|deflate)\b/i', $_SERVER['HTTP_ACCEPT_ENCODING'], $matches)) {
			// Bug #29350 output_compression compresses everything _without header_ as loadable module
			// http://bugs.php.net/bug.php?id=29350
			header('Content-Encoding: ' . $matches[1]);
			header('Vary: Accept-Encoding');
		}
	}
}

// DTD definitions
define('PKWK_DTD_HTML_5',                 21);
define('PKWK_DTD_XHTML_1_1',              17); // Strict only
define('PKWK_DTD_XHTML_1_0',              16); // Strict
define('PKWK_DTD_XHTML_1_0_STRICT',       16);
define('PKWK_DTD_XHTML_1_0_TRANSITIONAL', 15);
define('PKWK_DTD_XHTML_1_0_FRAMESET',     14);
define('PKWK_DTD_HTML_4_01',               3); // Strict
define('PKWK_DTD_HTML_4_01_STRICT',        3);
define('PKWK_DTD_HTML_4_01_TRANSITIONAL',  2);
define('PKWK_DTD_HTML_4_01_FRAMESET',      1);

define('PKWK_DTD_TYPE_XHTML',  1);
define('PKWK_DTD_TYPE_HTML',   0);

// Output HTML DTD, <html> start tag. Return content-type.
function pkwk_output_dtd($pkwk_dtd = PKWK_DTD_XHTML_1_1, $charset = CONTENT_CHARSET)
{
	global $ogp_tag, $add_xmlns;
	static $called;
	if (isset($called)) die('pkwk_output_dtd() already called. Why?');
	$called = TRUE;

	$type = PKWK_DTD_TYPE_XHTML;
	$option = '';
	$html5 = FALSE;
	switch($pkwk_dtd){
	case PKWK_DTD_HTML_5:
		$type = PKWK_DTD_TYPE_HTML;
		$html5 = TRUE;
		break;
	case PKWK_DTD_XHTML_1_1             :
		$version = '1.1' ;
		$dtd     = 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd';
		break;
	case PKWK_DTD_XHTML_1_0_STRICT      :
		$version = '1.0' ;
		$option  = 'Strict';
		$dtd     = 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd';
		break;
	case PKWK_DTD_XHTML_1_0_TRANSITIONAL:
		$version = '1.0' ;
		$option  = 'Transitional';
		$dtd     = 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd';
		break;

	case PKWK_DTD_HTML_4_01_STRICT      :
		$type    = PKWK_DTD_TYPE_HTML;
		$version = '4.01';
		$dtd     = 'http://www.w3.org/TR/html4/strict.dtd';
		break;
	case PKWK_DTD_HTML_4_01_TRANSITIONAL:
		$type    = PKWK_DTD_TYPE_HTML;
		$version = '4.01';
		$option  = 'Transitional';
		$dtd     = 'http://www.w3.org/TR/html4/loose.dtd';
		break;

	default: die('DTD not specified or invalid DTD');
		break;
	}

	$charset = htmlspecialchars($charset);

	// Output XML or not   --- edit by hokuken for some javascripts on IE6 & IE7 ---
	/*if ($type == PKWK_DTD_TYPE_XHTML) echo '<?xml version="1.0" encoding="' . $charset . '" ?>' . "\n";*/

	// Output doctype
	if ($pkwk_dtd == PKWK_DTD_HTML_5)
	{
		echo '<!DOCTYPE html>' . "\n";
	}
	else {
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD ' .
			($type == PKWK_DTD_TYPE_XHTML ? 'XHTML' : 'HTML') . ' ' .
			$version .
			($option != '' ? ' ' . $option : '') .
			'//EN" "' .
			$dtd .
			'">' . "\n";
	}

	// Output <html> start tag
	echo '<html';
	if ($type == PKWK_DTD_TYPE_XHTML)
	{
		echo ' xmlns="http://www.w3.org/1999/xhtml"'; // dir="ltr" /* LeftToRight */
		echo ' xml:lang="' . LANG . '"';
		if ($ogp_tag === 1)
		{
			echo ' xmlns:og="http://ogp.me/ns#"';
		}
		//Internet Explorer に必要なxmlns を吐き出す
		$fb_xmlns = 'xmlns:fb="http://ogp.me/ns/fb#"';
		if (strtoupper(UA_NAME) === 'MSIE' && ( ! isset($add_xmlns) OR stripos($add_xmlns, $fb_xmlns) === FALSE))
		{
			echo ' ', $fb_xmlns;
		}
		if (isset($add_xmlns))
		{
			echo $add_xmlns;
		}
		if ($version == '1.0') echo ' lang="' . LANG . '"'; // Only XHTML 1.0
	}
	else
	{
		echo ' lang="' . LANG . '"'; // HTML
	}
	echo '>' . "\n"; // <html>

	if ($html5)
	{
		return '<meta charset="UTF-8">' . "\n";
	}
	else
	{
		return '<meta http-equiv="content-type" content="text/html; charset=' . $charset . '">' . "\n";
	}
}

/* End of file html.php */
/* Location: ./lib/html.php */
