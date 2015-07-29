<?php
/**
 *   WordPress Template Adapter
 *   -------------------------------------------
 *   wp_adapter.php
 *
 *   Copyright (c) 2009 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2009 6.25
 *   modified : 2009 6.25
 *
 *   Description
 *    WordPressのテンプレートをQHMで使えるように、WPのテンプレートタグ(関数群)を
 *    エミュレートするように、関数を定義するファイル。
 *    場合によっては、意図しない動きをすることもあるかも
 *
 *    メニュー部分を見栄えよくするために、HTMLタグの書き換えを行ったりもします
 *    
 *   Usage :
 *    QHMに読み込んで使う。
 *
 *   Reference:
 *    テンプレートタグの一覧
 *    http://wpdocs.sourceforge.jp/%e3%83%86%e3%83%b3%e3%83%97%e3%83%ac%e3%83%bc%e3%83%88%e3%82%bf%e3%82%b0
 */

//
// http://www.toptut.com/2008/01/23/download-japanese-fleur-free-wordpress-theme/
//
//

error_reporting(E_ERROR | E_PARSE); // Avoid E_WARNING, E_NOTICE, etc
//error_reporting(E_ALL);
//header('Content-type: text/text');

global $_wordpress_template_have_content;
$_wordpress_template_have_content = true;

global $wpdb;
$wpdb = new WordPressDB();


class WP_Query{
	var $query;
	
	function init(){}
	function parse_query($query){}
	function parse_query_vars(){}
	function get($query_var){}
	function set($query_var, $value){}
	function &get_posts(){}
	function next_post(){}
	function the_post(){}
	function have_posts(){}
	function rewind_posts(){}
	function &query($query){}
	function get_queried_object(){}
	function get_queried_object_id(){}
	function WP_Query($query = ''){}
	
}

////////////////////////////////////////////////////////////
//
// ! WPテンプレート用の読み込みロジック
//

function get_wp_skin_file(){
	
	$files = array('page.php','single.php','index.php');
	
	foreach($files as $f){
		if(file_exists(TEMPLATEPATH.'/'.$f)){
			return TEMPLATEPATH.'/'.$f;
		}
	}

	echo '<b>Error : NO WordPress Design Template file</b>';
	exit;
}

function wp_load_functions(){
	if( file_exists(TEMPLATEPATH.'/functions.php') )
	{
		include(TEMPLATEPATH.'/functions.php');
	}
}

function wp_get_page($page, $tag='li'){

	if(!is_page($page)){
		echo $page.' does not exist.';
		return;
	}
	
	$lines = get_source($page);
	$ms = array();
	foreach($lines as $key=>$line)
	{
		if( preg_match('/^\*+(.*)\[#.*?\]/', $line, $ms) ) //見出しを削除
		{
			$lines[$key] = trim($ms[1]);
		}
		else if( preg_match('/^-+(.*)/', $line, $ms) )
		{
			$lines[$key] = trim($ms[1]);
		}
	}
	
	$html = convert_html($lines);
	
	$ptn = array('/<p.*?>/','/<\/p>/','/<br.*?>/');
	$rep = array('','','');
	$html = preg_replace($ptn, $rep, $html);
	
	$lines = explode("\n", $html);
	foreach($lines as $str){
		$str = trim($str);
		if($str != ''){
			echo "<$tag>$str</$tag>\n";
		}
	}
}

class WordPressDB{

	function get_results(){
		return '';
	}
	
	function linkcategories(){
		return '';
	}
}


////////////////////////////////////////////////////////////
// 
// ! 筆者タグ
//

/**
* 現在の記事の著者名（公開用）を表示
*/
function the_author(){
	$qt = get_qt();
	echo $qt->getv('modifier');
}

/**
* 2.8 から非推奨 現在の記事の著者の自己紹介文を表示 (→ the_author_meta)
*/
function the_author_description(){
}

/**
*2.8 から非推奨 現在の記事の著者のログインユーザ名を表示 (→ the_author_meta)
*/
function the_author_login(){
	if( isset($_SESSION['usr']) )
		echo $_SESSION['usr'];
}

/**
*2.8 から非推奨 現在の記事の著者の（下の）名を表示(→ the_author_meta)
*/
function the_author_firstname(){
}

/**
*2.8 から非推奨 現在の記事の著者の姓を表示(→ the_author_meta)
*/
function the_author_lastname(){
}

/**
*2.8 から非推奨 現在の記事の著者のニックネームを表示(→ the_author_meta)
*/
function the_author_nickname(){
}

/**
*2.8 から非推奨	 現在の記事の著者のユーザID を表示(→ the_author_meta)
*/
function the_author_ID(){
}

/**
*2.8 から非推奨	 現在の記事の著者の E-メールアドレスを表示(→ the_author_meta)
*/
function the_author_email(){

}
/**
*2.8 から非推奨 現在の記事の著者のウェブサイトの URI を表示(→ the_author_meta)
*/
function the_author_url(){
	$qt = get_qt();
	echo $qt->getv('modifierlink');
}
/**
* 2.1↑ 現在の記事の著者名（公開用）をウェブサイトへのリンク付きで表示	
*/
function the_author_link(){
	$qt = get_qt();
	echo '<a href="'.$qt->getv('modifierlink'). '">'.$qt->getv('modifier').'</a>';
}
/**
*2.8 から非推奨 現在の記事の著者の ICQ 番号（2.0.x 以下にあった項目）を表示(→ the_author_meta)
*/
function the_author_icq(){
}

/*2.8 から非推奨 現在の記事の著者の AIM（AOLインスタント・メッセンジャー）のスクリーンネームを表示(→ the_author_meta)*/
function the_author_aim(){

}

/*2.8 から非推奨	 現在の記事の著者の Yahoo IM ID を表示
→ the_author_meta*/
function the_author_yim(){}

/*2.8 から非推奨	 現在の記事の著者の MSN IM ID（2.0.x 以下にあった項目）を表示
→ the_author_meta*/
function the_author_msn(){}

/*現在の記事の著者の投稿数を表示*/
function the_author_posts(){}

/*現在の記事の著者名（公開用）を著者アーカイブへのリンク付きで表示*/
function the_author_posts_link(){}

/*非推奨	 → wp_list_authors*/
function list_authors(){}

/*ブログの著者一覧を表示。投稿があれば著者アーカイブへのリンク付き	 －	 投稿数、管理者除外、表示名、未投稿者、フィードリンク*/
function wp_list_authors(){}



////////////////////////////////////////////////////////////
// 
// ! カテゴリタグ
//

/*現在の記事の全カテゴリを、そのカテゴリアーカイブへのリンク付きで表示*/
function the_category(){}
/*（フィード用）*/
function the_category_rss(){}
/*the_category_ID (非推奨	 → get_the_category)*/
function the_category_ID(){}
/*非推奨	 → get_the_category_by_ID, get_the_category*/
function the_category_head(){}
/*現在のページのカテゴリ名を表示／取得	 －	 表示／取得、接頭辞*/
function single_cat_title(){}
/*category_nicename*/
function category_nicename(){}
/*指定したカテゴリの概要を取得	 －	 カテゴリID*/
function category_description(){}
/*2.1↑	 セレクトボックス（ドロップダウンメニューなど）を使ったカテゴリリストを表示／取得	 －	 表示／取得、ソート順ほか多数*/
function wp_dropdown_categories(){}
/*2.1 から非推奨	 → wp_dropdown_categories*/
function dropdown_cats(){}
/*2.1↑	 リンク付きカテゴリリストを表示	 －	 ソート順ほか多数*/
function wp_list_categories(){
	wp_get_page('SiteNavigator2');
}
/* 2.1 から非推奨	 リンク付きカテゴリリストを表示。wp_list_cats とはパラメータの書き方が異なる。2.0.x 系の EasyAll テーマで使用
→ wp_list_categories*/
function list_cats(){
	wp_get_page('SiteNavigator2');
}
/*2.1 から非推奨	 リンク付きカテゴリリストを表示。そのリンク先はカテゴリアーカイブページ。2.0.x 系のデフォルトテーマで使用
→ wp_list_categories*/
function wp_list_cats($param){
	wp_get_page('SiteNavigator2');
}
/*条件分岐タグ：現在の記事がパラメータで指定したカテゴリに属するとき true を返す	 ループ中/ ループ外	*/
function in_category(){}
/*現在の または 指定したカテゴリと、そこから最上位までの各カテゴリを取得*/
function get_category_parents(){}
/*現在の記事が属する各カテゴリの情報を取得。カテゴリID・カテゴリ名・カテゴリスラッグ・カテゴリ概要・親カテゴリが配列に格納されるので、好きに取り出して使う。*/
function get_the_category(){}
/*get_category_link*/
function get_category_link(){}
/*get_categories 2.8? */
function get_categories(){}

////////////////////////////////////////////////////////////
// 
// ! コメントタグ
//

/*記事のコメント、トラックバック、ピンの合計数を表示。	 ループ中	 コメント数表示テキスト*/
function comments_number(){}
/*記事コメントへのURLを表示。	 ループ中*/
function comments_link(){}
/*記事コメントRSSフィードのURLを表示。	 ループ中	 リンクラベルテキスト、リンク先ファイル名*/
function comments_rss_link(){}
/*ポップアップコメントフォーム用のJavaScriptを出力。	ループ外	 ポップアップウィンドウサイズ*/
function comments_popup_script(){}
/*コメントフォームのポップアップへのリンクを表示。	 ループ中	 コメント数表示テキスト、CSSクラス	原文*/
function comments_popup_link(){}
/*コメントのIDを表示。	 ループ中	*/
function comment_ID(){}
/*コメント投稿者名を表示。	 ループ中*/
function comment_author(){}
/**/
function comment_author_IP(){}
/**/
function comment_author_email(){}
/*コメント投稿者のサイトURLを表示。*/
function comment_author_url(){}
/*コメント投稿者へメールを送るリンクを表示。	 ループ中	 リンクラベルテキスト、前後テキスト*/
function comment_author_email_link(){}
/*コメント投稿者のサイトへのリンクを表示。	 ループ中	 リンクラベルテキスト、前後テキスト*/
function comment_author_url_link(){}
/*コメント投稿者名と、もしあればサイトへのリンクを表示。	 ループ中*/
function comment_author_link(){}
/*フィードバックの種類を出力。	 ループ中	 表示テキスト*/
function comment_type(){}
/*コメント本文を表示。	 ループ中*/
function comment_text(){}
/*コメント概要を表示。	 ループ中*/
function comment_excerpt(){}
/*コメント投稿日を表示。	 ループ中	 出力形式*/
function comment_date(){}
/*コメント投稿時を表示。	 ループ中	 出力形式*/
function comment_time(){}
/*フィード用形式でコメント投稿者名を表示。	 ループ中*/
function comment_author_rss(){}
/*フィード用形式でコメント本文を表示。	 ループ中	*/
function comment_text_rss(){}
/*フィード用形式でコメントへのリンクを表示。	 ループ中*/
function comment_link_rss(){}
/*フィード用形式でコメントがつけられた記事の固定リンクを表示。	 ループ中*/
function permalink_comments_rss(){}
/*2.7↑	 現在の投稿に対する全てのコメントを表示。従来のコメントループに替わるもの。*/
function wp_list_comments(){}
/*2.7↑	 既存コメントに返信するためのリンクを表示*/
function comment_reply_link(){}
/*2.7↑	 既存コメントへの返信を止める（コメント投稿欄を通常に戻す）ためのリンクを表示*/
function cancel_comment_reply_link(){}
/*2.7↑	 コメント投稿欄の見出しを表示*/
function comment_form_title(){}
/*2.7↑*/
function comment_id_fields(){}
	 

////////////////////////////////////////////////////////////
// 
// ! 日付タグ
//

/*現在の記事の投稿日を YYYY-MM-DD フォーマットで表示	 ループ中*/
function the_date_xml(){
	$qt = get_qt();
	echo format_date(get_filetime($qt->getv('_page')));
}

/*現在の記事の投稿日時を表示／取得。フォーマットを指定しなければ一般設定「日付のフォーマット」で表示。1ページに同一投稿日の記事があれば、その最初の記事にのみ表示。全記事に表示するには the_time	 ループ中	 日時フォーマット、前後の文字、表示／取得*/
function the_date(){
	$qt = get_qt();
	echo format_date(get_filetime($qt->getv('_page')));
}

/*現在の記事の投稿日時を表示。フォーマットを指定しなければ一般設定「時間のフォーマット」で表示。	 ループ中	 日時フォーマット	原文*/
function the_time($param=''){
	echo get_the_time($param);
}
/*2.1↑	 現在の記事の最終更新日時を表示。フォーマットを指定しなければ一般設定「日付のフォーマット」で表示。	 ループ中	 日時フォーマット*/
function the_modified_date(){
	$qt = get_qt();
	echo format_date(get_filetime($qt->getv('_page')));
}
/*2.1↑	 現在の記事の最終更新日時を表示。フォーマットを指定しなければ一般設定「時間のフォーマット」で表示。	 ループ中	 日時フォーマット*/
function the_modified_time(){
	$qt = get_qt();
	echo format_date(get_filetime($qt->getv('_page')));
}
/*1.5↑	 現在の記事の投稿日時を取得	 ループ中	 日時フォーマット*/
function get_the_time($param=''){
	$qt = get_qt();
	$t = get_filetime($qt->getv('_page'));
	
	switch($param)
	{
		case 'm':
		case 'd':
		case 'y':
			return date($param, $t);
		default:
			return date("Y-m-d", $t);
	}
}
/*現在のページの年月タイトルを表示／取得。月別アーカイブでのみ動作	 －	 表示／取得、前テキスト*/
function single_month_title(){}
/*カレンダーを表示。投稿のある日付は日別アーカイブへリンク	 －	 曜日表示形式*/
function get_calendar(){}
/*（非推奨？）[1]	 現在の記事が投稿された曜日を表示 → the_time	 ループ中*/
function the_weekday(){}
/*（非推奨？）[1]	 現在の記事が投稿された曜日を、前の記事と異なるときにのみ表示 → the_date を利用した the_time*/
function the_weekday_date(){}
	 

////////////////////////////////////////////////////////////////
//
//! Generalタグ
//

/*設置した WordPress の各種情報（主に管理画面の一般設定やユーザープロフィールの項目）を表示。値を取得するには get_bloginfo。sidebar.php や header.php でよく使われる。	 －	 表示したい項目（ブログ名、URI、RSS、文字コード、設置ディレクトリURI、ほか）*/
function bloginfo($param){
	echo get_bloginfo($param);
}

/*（フィード用）*/
function bloginfo_rss($param){
	echo get_bloginfo_rss($param);
}

/*bloginfo と同様の項目の値を取得	 －	 取得したい項目（ブログ名、URI、RSS、文字コード、設置ディレクトリURI、ほか）*/
function get_bloginfo($param){
	global $page_title, $script;
	$qt = get_qt();

	switch($param)
	{
		case 'stylesheet_url' :
			return TEMPLATEPATH.'/style.css';	break;
		case 'name':   //（「一般設定」管理画面で指定したブログのタイトル）
			return $page_title; break;
		case 'description':  //（「一般設定」管理画面で指定したブログの説明文）
			$ms = array();
			preg_match('/<h1>(.*?)<\/h1>/', $qt->getv('head_copy_tag'), $ms);
			return $ms[1]; break;
		case 'url': //（ブログのサイトURL）
			return dirname($script); break;
		case 'rdf_url':  //（RDF/RSS 1.0 形式のメインフィードURL）
			return str_replace('cmd=rss','cmd=rss&ver=1.0', $qt->getv('rss_link')); break;
		case 'rss_url':  //（RSS 0.92 形式のメインフィードURL）
			return $qt->getv('rss_link'); break;
		case 'rss2_url':  //（RSS 2.0 形式のメインフィードURL）
			return str_replace('cmd=rss','cmd=rss&ver=2.0', $qt->getv('rss_link')); break;
		case 'atom_url':  //（Atom形式のメインフィードURL）
			return str_replace('cmd=rss','cmd=atom', $qt->getv('rss_link')); break;
		case 'comments_rss2_url':  //（RSS 2.0形式のコメントフィードURL）
			return ''; break;
		case 'pingback_url':  //（ピンバック用URL。XML-RPCファイルを指す）
			return ''; break;
		case 'admin_email':  //（「一般設定」管理画面で指定した管理人のメールアドレス）
			return ''; break;
		case 'charset':  //（「表示設定」管理画面で指定された文字コード）
			return WORDPRESS_CHARSET; break;
		case 'version':  //（現在使用中のWordPressのバージョン）
			return ''; break;
		case 'html_type':  //
			return ''; break;
		case 'wpurl':  //（WordPressをインストールしたURL）（Version 1.5以降）
			return ''; break;
		case 'template_url':  //（使用中テンプレートのURL）（Version 1.5以降）
			return dirname($script).'/'.TEMPLATEPATH; break;
		case 'stylesheet_directory':  //（使用中のメインCSSファイルが置かれたディレクトリのURL）（Version 2.3.1 で廃止）
			return dirname($script).'/'.TEMPLATEPATH;break;
		case 'stylesheet_url':  //（使用中のメインCSSファイルのURL）（Version 1.5以降）
			return dirname($script).'/'.TEMPLATEPATH; break;
		case 'template_directory':  //
			return TEMPLATEPATH; break;
		case 'text_direction':
			return 'ltr'; break;
		case 'home':
			return dirname($script.'dummy');
		default:
			return '';
	}
}

/* （フィード用）*/
function get_bloginfo_rss($param){
	global $page_title, $script;
	$qt = get_qt();

	switch($param)
	{
		case 'rdf_url':  //（RDF/RSS 1.0 形式のメインフィードURL）
			return str_replace('cmd=rss','cmd=rss&ver=1.0', $qt->getv('rss_link')); break;
		case 'rss_url':  //（RSS 0.92 形式のメインフィードURL）
			return $qt->getv('rss_link'); break;
		case 'rss2_url':  //（RSS 2.0 形式のメインフィードURL）
			return str_replace('cmd=rss','cmd=rss&ver=2.0', $qt->getv('rss_link')); break;
		case 'atom_url':  //（Atom形式のメインフィードURL）
			return str_replace('cmd=rss','cmd=atom', $qt->getv('rss_link')); break;
		default:
			return $qt->getv('rss_link');
	}
}

function wp_title(){
	$qt = get_qt();
	echo '- '.$qt->getv('this_right_title');
}
/*月別アーカイブリスト等、日付に基づくリンク付きアーカイブリストを表示。月別・日別・週別、最近の投稿 n件	 －	 種別、件数、リスト形式、前後の文字、記事数の表示*/
function wp_get_archives($param){

	if(preg_match('/format=link/',$param))
	{
		echo '';
		return ;
	}
	
	wp_get_page('MenuBar');
}

/*2.1 から非推奨	 → wp_get_archives*/
function get_archives($type='', $limit='', $format='', $before='', $after='</li>', $show_post_count=''){
	if($after=='</li>'){
		wp_get_page('MenuBar');
	}
	else{	
		echo convert_html( get_source('MenuBar') );
	}
}
/*マルチループ（複数ループ）作成時の条件指定*/
function get_posts(){}
/*	 1.5↑	「ページ」一覧を表示／取得	 －	 表示／取得、ソート順、除外／表示ページ、表示階層の深さ、サブページ指定、日付表示、見出し有無*/
function wp_list_pages(){
	wp_get_page('SiteNavigator');
}
/*2.7↑*/
function wp_page_menu(){}
/*「ページ」一覧をセレクトボッックス（ドロップダウンメニュー）で表示*/
function wp_dropdown_pages(){}
/*1.5↑	 ログイン／ログアウトリンクを表示*/
function wp_loginout(){
	global $script;
	
	if( isset($_SESSION['usr']) ){
		echo '<a href="'.$script.'?cmd=qhmlogout">Logout</a>';
	}
	else{
		echo '<a href="'.$script.'?cmd=qhmauth">Login</a>';
	}
}
/*1.5↑	 ユーザ登録／サイト管理リンクを表示	 －	 前後テキスト*/
function wp_register(){}
/*2.7↑*/
function wp_logout_url(){
	global $script;
	
	if( isset($_SESSION['usr']) ){
		return $script.'?cmd=qhmlogout';
	}
	else{
		return '';
	}
}

/* 1.5↑	 ループの前に書くことで、ページに表示する記事をコントロール	 ループ前	 カテゴリ、著者、記事、日時、ソート順、表示数、改ページ、オフセット*/
function query_posts(){}
/*1.5↑	 記事の音声・動画ファイルへのリンクを RSSフィード内に挿入（ポッドキャスト向け）*/
function rss_enclosure(){}
/*2.1↑	 現在の検索文字列を表示*/
function the_search_query(){
	global $vars;
	echo $vars['word'];
}
	 

///////////////////////////////////////////////////////////////
//
//! リンクタグ
//

/*現在の記事の編集リンクを表示	 ループ中	 リンク・前後テキスト*/
function edit_post_link($link='edit this', $before, $after){
	global $script;
	$qt = get_qt();

	$edit_link = $script.'?cmd=edit&page='.rawurlencode($qt->getv('_page'));
	echo $before.'<a href="'.$edit_link.'">'.$link.'</a>'.$after;
}

/*現在のコメントの編集リンクを表示	 ループ・コメントループ中	 リンク・前後テキスト*/
function edit_comment_link(){}
/*2.7↑*/
function edit_tag_link(){}
/*2.7↑*/
function edit_bookmark_link(){}
/* 改ページ（<!--nextpage-->）されている記事に各ページへのリンクを表示	 ループ中	 前後テキスト、ページ番号／次頁、リンクフォーマット、リンク表示先	原文*/
function wp_link_pages(){}
/*2.1から非推奨	 → wp_link_pages*/
function link_pages(){}
/*任意の年別アーカイブページの URI を取得		 年*/
function get_year_link(){}
/*任意の月別アーカイブページの URI を取得		 年・月*/
function get_month_link(){}
/*任意の日別アーカイブページの URI を取得*/
function get_day_link(){}
/*アーカイブページで前のページ（通常は新しい記事）へのリンクを表示	 －	 ラベル（リンクテキスト）、最大ページ数*/
function previous_posts_link(){}
/*アーカイブページで次のページ（通常は古い記事）へのリンクを表示	 －	 ラベル（リンクテキスト）、最大ページ数*/
function next_posts_link(){}
/*2.7↑ コメントを改ページする場合に、前ページへのリンクを表示	*/
function previous_comments_link(){}
/* 2.7↑	 コメントを改ページする場合に、次ページへのリンクを表示	*/
function next_comments_link(){}
/*2.7↑*/
function paginate_comments_links(){}


//////////////////////////////////////////////////////////////
//
//! リンク管理タグ
//

/*2.3 から非推奨	 リンク管理画面で「表示：はい」になっている全てのリンクを、カテゴリ順に表示する。リンクカテゴリ画面の「カテゴリー設定」は有効だが「フォーマット」は無視される。
→ wp_list_bookmarks		 ソート順（カテゴリ名順／カテゴリID順。昇順／降順はアンダーバーで）*/
function get_links_list(){}
/*2.3 から非推奨	 パラメータで指定したカテゴリIDに属するリンクを表示。各リンクはカテゴリの「フォーマット」で設定したタグで括られるので、もしそれが <li> タグなら、<ul> タグや見出しの追加が必要。
→ get_bookmarks*/
function wp_get_links(){}
/*2.3 から非推奨	 → get_bookmarks*/
function get_links(){}
/*2.1 から非推奨	 全カテゴリまたは指定したカテゴリのリンクを、パラメータの条件に従って表示
→ wp_get_links*/
function wp_get_linksbyname(){}
/*2.1 から非推奨	 → get_links*/
function get_linksbyname(){}
/*2.1↑	 ブックマーク（ブログロール）一覧を表示／取得		 表示／取得、ソート順ほか多数*/
function wp_list_bookmarks(){}
/*2.1↑*/
function get_bookmarks(){}


//////////////////////////////////////////////////////////////////
//
//! パーマリンクタグ
//

/*現在の記事へのアンカータグ（<a id="...."）を出力	 ループ中	 id 属性の値（記事ID／スラッグ）*/
function permalink_anchor(){}
/*指定した記事のパーマリンクURI を取得。ループ中でパラメータなしで使うと、現在の記事のパーマリンクURI を取得。	 －	 記事ID*/
function get_permalink(){
	$qt = get_qt();
	return $qt->getv('go_url');
}
/*（フィード用）*/
function permalink_single_rss(){}
/*現在の記事のパーマリンクURI を表示*/
function the_permalink(){ echo get_permalink(); }

////////////////////////////////////////////////////////////
//
//! 投稿タグ
//

/*現在の記事の記事ID を表示	 ループ中*/
function the_ID(){ echo '1'; }


/*現在の記事のタイトルを表示／取得	 ループ中	 表示／取得、前後テキスト*/
function the_title(){echo '';}

/*2.3↑	 現在の記事のタイトルを表示／取得。HTMLタグ除去・文字実体参照に変換	 ループ中	 表示／取得、前後テキスト*/
function the_title_attribute(){}

/*単体記事ページのときに記事タイトルを表示／取得	 －	 表示／取得、前テキスト*/
function single_post_title(){}
/*（フィード用）*/
function the_title_rss(){}

/* 現在の記事の本文を表示。記事中に <!--more--> がある場合、単体記事ページ以外ではそれより前の部分を表示し「続きを読む」リンクを添える。	 ループ中	 「続きを読む」の文言、	原文 */
function the_content(){
	global $_wordpress_template_have_content;
	$qt = get_qt();
	$_wordpress_template_have_content = false;

	echo $qt->getv('toolkit_upper');
	echo $qt->getv('qp_here_start');
	
	echo $qt->getv('body');
	
	echo $qt->getv('qp_here_end');
	
}

/*（フィード用）*/
function the_content_rss(){}
/*現在の記事の抜粋文を表示。HTMLタグや画像は除外。「抜粋表示オプション」が空なら最初の120語を出力。抜粋されずに長文となるときは日本語・マルチバイト特有の問題を参照	 ループ中*/
function the_excerpt(){}
/*（フィード用）*/
function the_excerpt_rss(){}
/*単体記事ページで次の記事へのリンクを表示	 ループ中	 表示フォーマット、リンクテキスト、同一カテゴリ、除外カテゴリ*/
function next_post_link(){}
/*単体記事ページで前の記事へのリンクを表示	 ループ中	 表示フォーマット、リンクテキスト、同一カテゴリ、除外カテゴリ*/
function previous_post_link(){}
/*非推奨	 → next_post_link*/
function next_post(){}
/*非推奨	 → previous_post_link*/
function previous_post(){}
/*index・カテゴリ・アーカイブページなどで前後のページへのリンクを表示		 前後リンク間の文字、リンクテキスト	原文*/
function posts_nav_link(){}
/*2.7*/
function sticky_class(){}
/*現在の記事のメタ情報（カスタムフィールドの「キー：値」の組）を番号なし箇条書きリストで表示	 ループ中*/
function the_meta(){}


//////////////////////////////////////////////////////
//
// ! タグ用タグ
// 
/*2.3↑	 現在の記事のタグ一覧を表示	 ループ中	 前・後・区切り文字*/
function the_tags(){}
/*2.3↑	 現在の記事のタグ情報を配列で取得	 ループ中	*/
function get_the_tags(){}
/*2.3↑	 現在の記事のタグを HTML文字列に整形して取得	 ループ中	 前・後・区切り文字*/
function get_the_tag_list(){}
/*2.3↑	 現在のページのタグ名を表示／取得	 －	 表示／取得、接頭辞*/
function single_tag_title(){}
/*2.3↑*/
function get_tag_link(){}
/*2.3↑	 タグクラウドを表示	 －	 文字サイズ、表示数・順序・形式、除外／対象タグ*/
function wp_tag_cloud(){}
/*2.3↑*/
function wp_generate_tag_cloud(){}
/*2.8↑*/
function tag_description(){}


/////////////////////////////////////////////////////////////
//
//! トラックバックタグ
//

/*現在の記事のトラックバック URI を表示／取得	 ループ中	 表示／取得*/
function trackback_url(){}
/*現在の記事のトラックバック RDF 情報を出力。トラックバックURI を自動的に検出する Trackback Auto-Discovery 用コード（参考）	 ループ中*/
function trackback_rdf(){}



////////////////////////////////////////////////////////////
//
// ! その他、重要な関数群
// 

/**/
function get_header(){
	$filename = TEMPLATEPATH.'/header.php';
	
	if(file_exists($filename))
	{
	
		$cachename = CACHE_DIR.str_replace('/','_',TEMPLATEPATH).'_header.qtc';
		
	    if (! file_exists($cachename) || filemtime($cachename) < filemtime($filename)) {
			$str = file_get_contents(TEMPLATEPATH.'/header.php');
		
			$str = preg_replace('/<!DOCTYPE[^>]+>/','',$str);
			$str = preg_replace('/<html(?:.*?[^?])?>/','',$str);			
			
			file_put_contents($cachename, $str);
		}

		require_once($cachename);
	}
}


function is_single(){
	return false;
}

function wp_head(){
	global $wp_add_css;
	$qt = get_qt();
	
	$tag = $qt->getv('iphone_meta');
	$tag .= $qt->getv('custom_meta');
	$tag .= $qt->getv('noindex');
	$tag .= $qt->getv('external_link');

	$tag .= $qt->getv('jquery_script');
	$tag .= $qt->getv('jquery_cookie_script');

	$tag .= $qt->getv('clickpad_js');
		
	$tag .= $qt->getv('head_tag');
	$tag .= $qt->getv('beforescript');

	$tag .= '
<style type="text/css">
  <!--
'.$wp_add_css.'
  -->
  </style>
';

	echo $tag;	
}

function get_sidebar(){
	if(file_exists(TEMPLATEPATH.'/sidebar.php'))
		include(TEMPLATEPATH.'/sidebar.php');
}

/**
* WordPress ページへのリンクのリスト (以下ページリストとする) を表示
* ==> ナビへのリンクを出すべきかも
*/

function _e($param){
	echo $param;
}

function __($param){
	return $param;
}




function wp_meta(){
	//echo 'wp_meta()';
}

function get_settings($param){
	global $script;
	
	switch($param){
	
		case 'home':
			echo dirname($script.'dummy');break;
		default:
			echo '';
	}
}

function have_posts(){
	global $_wordpress_template_have_content;
	return $_wordpress_template_have_content;
}

function the_post(){
	global $_wordpress_template_have_content;
	return $_wordpress_template_have_content;
}

function comments_template(){
	return '';
}

function get_footer(){
	if(file_exists(TEMPLATEPATH.'/footer.php'))
		include(TEMPLATEPATH.'/footer.php');

}

function wp_footer(){
	$qt = get_qt();
	
	echo $qt->getv('accesstag_tag');
	echo $qt->getv('lastscript');
}

function get_search_form(){
	if(file_exists(TEMPLATEPATH.'/searchform.php'))
		include(TEMPLATEPATH.'/searchform.php');

}

function wp_specialchars($s){
	echo htmlspecialchars($s);
}

function is_category(){
	return false;
}

function is_date(){
	return false;
}

function is_day(){

}

function is_month(){

}

function is_year(){

}

function is_tag(){
	return false;
}

function is_paged(){

}

function is_home(){
}
function is_search(){
	return false;
}


function is_404(){
	return false;
}

function is_archive(){

}

function get_option($param){

	if( preg_match('/comments/',$param) ){
		return 0;
	}

	return get_bloginfo($param);
}

function is_singular(){
}

function do_action(){

}

function language_attributes(){
}

function body_class(){

}



function get_num_queries(){
}

function timer_stop(){
}



function load_theme_textdomain(){

}

function automatic_feed_links(){
}

function add_action(){
}

function remove_action(){
}

function add_filter(){
}

function post_class(){
}

function post_comments_feed_link(){

}

function get_query_var(){
}

function apply_filters(){
}


function wp_cache_get(){
}

function wp_cache_add(){
}

function is_admin(){ return false; }

function update_option(){}
function register_sidebar(){}
function wp_register_sidebar_widget(){}
function wp_register_widget_control(){}
function get_current_theme(){}
function add_option(){}
function get_theme_mod(){}
function attribute_escape(){}
function is_front_page(){}
function home_url($param){
	global $script;
	return dirname($script).$param; 
}
function esc_attr(){}
function has_post_thumbnail(){}
function wp_get_attachment_image_src(){}
function get_post_thumbnail_id(){}
function get_the_post_thumbnail(){}
function header_image(){}
function esc_attr_e(){}
function wp_nav_menu(){}
function dynamic_sidebar(){}
function is_active_sidebar(){}
?>