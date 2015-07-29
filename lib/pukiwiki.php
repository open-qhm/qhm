<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: pukiwiki.php,v 1.11 2005/09/11 05:58:33 henoheno Exp $
//
// PukiWiki 1.4.*
//  Copyright (C) 2002-2005 by PukiWiki Developers Team
//  http://pukiwiki.sourceforge.jp/
//
// PukiWiki 1.3.*
//  Copyright (C) 2002-2004 by PukiWiki Developers Team
//  http://pukiwiki.sourceforge.jp/
//
// PukiWiki 1.3 (Base)
//  Copyright (C) 2001-2002 by yu-ji <sng@factage.com>
//  http://factage.com/sng/pukiwiki/
//
// Special thanks
//  YukiWiki by Hiroshi Yuki <hyuki@hyuki.com>
//  http://www.hyuki.com/yukiwiki/
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

if (! defined('DATA_HOME')) define('DATA_HOME', '');

/////////////////////////////////////////////////
// Include subroutines

if (! defined('LIB_DIR')) define('LIB_DIR', '');




require(LIB_DIR . 'func.php');
require(LIB_DIR . 'file.php');
require(LIB_DIR . 'plugin.php');
require(LIB_DIR . 'html.php');
require(LIB_DIR . 'backup.php');

require(LIB_DIR . 'convert_html.php');
require(LIB_DIR . 'make_link.php');
require(LIB_DIR . 'diff.php');
require(LIB_DIR . 'config.php');
require(LIB_DIR . 'link.php');
require(LIB_DIR . 'auth.php');
require(LIB_DIR . 'proxy.php');
if (! extension_loaded('mbstring')) {
	require(LIB_DIR . 'mbstring.php');
}

// Defaults
$notify = $trackback = $referer = 0;

// Load *.ini.php files and init PukiWiki
require(LIB_DIR . 'init.php');

// Load authorization library  QQQ
require(LIB_DIR .'ss_authform.php');
ss_auth_start();

//strip session_name from $vars['page']
if($vars['page'] === session_name().'='.session_id())
	$vars['page'] = $defaultpage;

// Load optional libraries
if ($notify) {
	require(LIB_DIR . 'mail.php'); // Mail notification
}
if ($trackback || $referer) {
	// Referer functionality uses trackback functions
	// without functional reason now
	require(LIB_DIR . 'trackback.php'); // TrackBack
}

require(PLUGIN_DIR . 'secedit.inc.php');

//キャッシュ有効フラグをQHM Template にセット
$qt->enable_cache = $enable_cache;
//ページ名をセット
if (!$qt->set_page) {
	$qt->set_page(isset($vars['page'])  ? $vars['page']  : '');
}

// haik テーマの場合、ナビ2 を フッターに変える
if (strpos($style_name, 'haik_') === 0)
{
    $layout_pages['SiteNavigator2'] = 'フッター';
}

/////////////////////////////////////////////////
// Main

$retvars = array();
$is_cmd = FALSE;
if (isset($vars['cmd'])) {
	$is_cmd  = TRUE;
	$plugin = & $vars['cmd'];
} else if (isset($vars['plugin'])) {
	$plugin = & $vars['plugin'];
} else {
	$plugin = '';
}
if ($plugin != '') {
	if (exist_plugin_action($plugin)) {
		// Found and exec
		$retvars = do_plugin_action($plugin);
		if ($retvars === FALSE) exit; // Done

		if ($is_cmd) {
			$base = isset($vars['page'])  ? $vars['page']  : '';
		} else {
			$base = isset($vars['refer']) ? $vars['refer'] : '';
		}
	} else {
		// Not found
		header('HTTP/1.1 501 Not Implemented');
		$msg = 'plugin=' . h($plugin) .
			' is not implemented.';
		$retvars = array('msg'=>$msg,'body'=>$msg);
		$base    = & $defaultpage;
	}
}

$title = h(strip_bracket($base));
$page  = make_search($base);
if (isset($retvars['msg']) && $retvars['msg'] != '') {
	$title = str_replace('$1', $title, $retvars['msg']);
	$page  = str_replace('$1', $page,  $retvars['msg']);
}


//------------------------------------------------------
// * サイト全体を閉鎖するオプションがOnの場合、
//　　qhmloginへのアクセス以外、全部、「閉鎖中」を出す
// 2009 6/18 (by hokuken.com)
//------------------------------------------------------
if( $site_close_all &&  !ss_admin_check() && $vars['phase'] != 'sssavepath'){
	$_SESSION['usr'] = null;
	output_site_close_message($page_title, $script.'?cmd=qhmauth');
	exit;
}
//--

// 共用SSLの多くは、SSL通信なのに、SERVER変数が、http系になっていることがほとんど
// そこで、SSL通信の場合、$scriptは、https://.. にするために、$script_sslを使うようにする
// 独自SSLを導入している場合でも、対応できる ( lib/init.php で、$script_ssl を作っているので )
// 　※ なお、convert_htmlが終わった時点で、元に戻す（このelseブロックの最後を参照）
if( is_https() ){
	$scr_tmp     = $script;
	$script      = $script_ssl;
	$script_ssl  = $scr_tmp;	
}

if (isset($retvars['body']) && $retvars['body'] != '') {
	$body = & $retvars['body'];
} else {
	if ($base == '' || ! is_page($base)) {
		$base  = & $defaultpage;
		$title = h(strip_bracket($base));
		$page  = make_search($base);
	}

	$vars['cmd']  = 'read';
	$vars['page'] = & $base;

	//--------------------------------------------------------------------------
	//
	// * キャッシュを有効にするカスタマイズ by hokuken.com
	//
	// ・編集モードの場合、キャッシュは強制的に無効
	//
	// ・キャッシュは、有効だが、キャッシュファイル無効の場合は、$qt->create_cache = true;
	// 　として、動的にロードされるべきプラグインの制御を有効にする(ex: popularプラグインなど)
	//
	// ・キャッシュが有効で、キャッシュファイルがあるなら、キャッシュを出力
	//
	//--------------------------------------------------------------------------

	//編集状態の場合、強制的にキャッシュを無効
	$qt->enable_cache = edit_auth($base, FALSE, FALSE)===TRUE ? false : $qt->enable_cache;
	
	//携帯の場合、強制的にキャッシュ機能をオフ。もし、転送設定があれば、転送する
	if( preg_match('/keitai.skin.php$/', SKIN_FILE) ){
		$qt->enable_cache = false;
		
		if( is_url( $mobile_redirect ) ){
		
			if( isset($_GET[session_name()]) ){ //sessionを利用している場合
				$mobile_redirect .= strpos($mobile_redirect, '?')===FALSE ? '?' : '&';
				$mobile_redirect .= session_name().'='.session_id();
			}
			
			header('Location: '. $mobile_redirect);
			exit;
		}
	}
	//スマートフォンの場合、強制的にキャッシュ機能をオフ
	if (is_smart_phone()) {
		$qt->enable_cache = false;
	}
	
	//$scriptが変化している場合、キャッシュの有効期限をリフレッシュ
	chk_script($script);

	if( $qt->enable_cache ){ //キャッシュ有効
		//キャッシュを出力して終了
		if ($qt->cache_is_available()) {
			$qt->disp();
			exit;
		} else {
			//ここではコンテンツ部分のみ生成するため、キャッシュは保存しない
			//出力の部分で保存します
			$qt->create_cache = true;
			$src = get_source($base);
			
			if (is_qblog())
			{
				array_unshift($src, "#qblog_head\n");
				array_push($src, "#qblog_foot\n", "#qblog_comment");
			}
			
			$body = convert_html($src);
			
			if (!$qt->enable_cache) { //プラグインによってキャッシュを無効にされている場合
				//動的プラグインを実行しておく
				$qt->create_cache = false;
				$body = $qt->replace_dynamic_plugin($body);
			}
		}
	}
	else{
		$src = get_source($base);
		
		if (is_qblog())
		{
			array_unshift($src, "#qblog_head\n");
			array_push($src, "#qblog_foot\n", "#qblog_comment");
		}
		
		$body = convert_html($src);
	}
	if (!$qt->getv('body_insert_mark')) {
		$body = "\n<!-- BODYCONTENTS START -->\n" . $body . "\n<!-- BODYCONTENTS END -->\n";
		$qt->setv('body_insert_mark', true);
	}
	
	if ($trackback) $body .= tb_get_rdf($base); // Add TrackBack-Ping URI
	if ($referer) ref_save($base);
}

// Output
catbody($title, $page, $body);
exit;
?>