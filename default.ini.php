<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: default.ini.php,v 1.25 2005/12/20 14:04:40 henoheno Exp $
// Copyright (C)
//   2003-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// PukiWiki setting file (user agent:default)

/////////////////////////////////////////////////
// Skin file

if (defined('TDIARY_THEME')) {
	define('SKIN_FILE', DATA_HOME . SKIN_DIR . 'tdiary.skin.php');
} else {
	define('SKIN_FILE', DATA_HOME . SKIN_DIR . 'pukiwiki.skin.php');
}

/////////////////////////////////////////////////
// 雛形とするページの読み込みを可能にする
$load_template_func = 0;

/////////////////////////////////////////////////
// 検索文字列を色分けする
$search_word_color = 1;

/////////////////////////////////////////////////
// 一覧ページに頭文字インデックスをつける
$list_index = 1;

/////////////////////////////////////////////////
// リスト構造の左マージン
$_ul_left_margin = 0;   // リストと画面左端との間隔(px)
$_ul_margin = 16;       // リストの階層間の間隔(px)
$_ol_left_margin = 0;   // リストと画面左端との間隔(px)
$_ol_margin = 16;       // リストの階層間の間隔(px)
$_dl_left_margin = 0;   // リストと画面左端との間隔(px)
$_dl_margin = 16;        // リストの階層間の間隔(px)
//$_list_pad_str = ' class="list%d" style="padding-left:%dpx;margin-left:%dpx"';
$_list_pad_str = ' class="list%d" ';
$_dlist_pad_str = ' class="list%d dl-horizontal" ';

/////////////////////////////////////////////////
// テキストエリアのカラム数
$cols = 80;

/////////////////////////////////////////////////
// テキストエリアの行数
$rows = 20;

/////////////////////////////////////////////////
// 大・小見出しから目次へ戻るリンクの文字
$top = $qm->m['fmt_msg_content_back_to_top'];

/////////////////////////////////////////////////
// 添付ファイルの一覧を常に表示する (負担がかかります)
$attach_link = 0;

/////////////////////////////////////////////////
// 関連するページのリンク一覧を常に表示する(負担がかかります)
$related_link = 0;

// リンク一覧の区切り文字
$related_str = "\n ";

// (#relatedプラグインが表示する) リンク一覧の区切り文字
$rule_related_str = "</li>\n<li>";

/////////////////////////////////////////////////
// 水平線のタグ
$hr = '<hr class="full_hr" />';

/////////////////////////////////////////////////
// 脚注機能関連

// 脚注のアンカーに埋め込む本文の最大長
define('PKWK_FOOTNOTE_TITLE_MAX', 16); // Characters

// 脚注のアンカーを相対パスで表示する (0 = 絶対パス)
//  * 相対パスの場合、以前のバージョンのOperaで問題になることがあります
//  * 絶対パスの場合、calendar_viewerなどで問題になることがあります
// (詳しくは: BugTrack/698)
define('PKWK_ALLOW_RELATIVE_FOOTNOTE_ANCHOR', 1);

// 文末の脚注の直前に表示するタグ
$note_hr = '<hr class="note_hr" />';

/////////////////////////////////////////////////
// WikiName,BracketNameに経過時間を付加する
$show_passage = 0;

/////////////////////////////////////////////////
// リンク表示をコンパクトにする
// * ページに対するハイパーリンクからタイトルを外す
// * Dangling linkのCSSを外す
$link_compact = 0;

/////////////////////////////////////////////////
// フェイスマークを使用する
$usefacemark = 1;

/////////////////////////////////////////////////
// ユーザ定義ルール
//
//  正規表現で記述してください。?(){}-*./+\$^|など
//  は \? のようにクォートしてください。
//  前後に必ず / を含めてください。行頭指定は ^ を頭に。
//  行末指定は $ を後ろに。
//
/////////////////////////////////////////////////
// ユーザ定義ルール(コンバート時に置換)
$line_rules = array(
	'COLOR\(([^\(\)]*)\){([^}]*)}'	=> '<span style="color:$1">$2</span>',
	'SIZE\(([^\(\)]*)\){([^}]*)}'	=> '<span style="font-size:$1px">$2</span>',
	'COLOR\(([^\(\)]*)\):((?:(?!COLOR\([^\)]+\)\:).)*)'	=> '<span style="color:$1">$2</span>',
	'SIZE\(([^\(\)]*)\):((?:(?!SIZE\([^\)]+\)\:).)*)'	=> '<span class="size$1">$2</span>',
	'%%%(?!%)((?:(?!%%%).)*)%%%'	=> '<ins>$1</ins>',
	'%%(?!%)((?:(?!%%).)*)%%'	=> '<del>$1</del>',
	"'''(?!')((?:(?!''').)*)'''"	=> '<em>$1</em>',
	"''(?!')((?:(?!'').)*)''"	=> '<strong>$1</strong>',
	"###(?!#)((?:(?!###).)*)###"	=> '<small>$1</small>',
	"##(?!#)((?:(?!##).)*)##"	=> '<span class="handline">$1</span>',
	"`(?!#)((?:(?!`).)*)`"      => '<code>$1</code>',
);

/////////////////////////////////////////////////
// フェイスマーク定義ルール(コンバート時に置換)

// $usefacemark = 1ならフェイスマークが置換されます
// 文章内にXDなどが入った場合にfacemarkに置換されてしまうので
// 必要のない方は $usefacemarkを0にしてください。

$facemark_rules = array(
	// Face marks
	'\s(\:\))'	=> ' <img alt="$1" src="' . IMAGE_DIR . 'face/smile.png" />',
	'\s(\:D)'	=> ' <img alt="$1" src="' . IMAGE_DIR . 'face/bigsmile.png" />',
	'\s(\:p)'	=> ' <img alt="$1" src="' . IMAGE_DIR . 'face/huh.png" />',
	'\s(\:d)'	=> ' <img alt="$1" src="' . IMAGE_DIR . 'face/huh.png" />',
	'\s(XD)'	=> ' <img alt="$1" src="' . IMAGE_DIR . 'face/oh.png" />',
	'\s(X\()'	=> ' <img alt="$1" src="' . IMAGE_DIR . 'face/oh.png" />',
	'\s(;\))'	=> ' <img alt="$1" src="' . IMAGE_DIR . 'face/wink.png" />',
	'\s(;\()'	=> ' <img alt="$1" src="' . IMAGE_DIR . 'face/sad.png" />',
	'\s(\:\()'	=> ' <img alt="$1" src="' . IMAGE_DIR . 'face/sad.png" />',
	'&amp;(smile);'	=> ' <img alt="[$1]" src="' . IMAGE_DIR . 'face/smile.png" />',
	'&amp;(bigsmile);'=>' <img alt="[$1]" src="' . IMAGE_DIR . 'face/bigsmile.png" />',
	'&amp;(huh);'	=> ' <img alt="[$1]" src="' . IMAGE_DIR . 'face/huh.png" />',
	'&amp;(oh);'	=> ' <img alt="[$1]" src="' . IMAGE_DIR . 'face/oh.png" />',
	'&amp;(wink);'	=> ' <img alt="[$1]" src="' . IMAGE_DIR . 'face/wink.png" />',
	'&amp;(sad);'	=> ' <img alt="[$1]" src="' . IMAGE_DIR . 'face/sad.png" />',
	'&amp;(heart);'	=> ' <img alt="[$1]" src="' . IMAGE_DIR . 'face/heart.png" />',
	'&amp;(worried);'=>' <img alt="[$1]" src="' . IMAGE_DIR . 'face/worried.png" />',
	'&amp;(check);'=>' <img alt="[$1]" src="' . IMAGE_DIR . 'face/check.png" />',
	
	//simbol
	'&amp;(gt);'=>'&gt;',
	'&amp;(lt);'=>'&lt;',
	'&amp;(trade);'=>'&trade;',
	'&amp;(raquo);'=>'&raquo;',

	// Face marks, Japanese style
	'\s(\(\^\^\))'	=> ' <img alt="$1" src="' . IMAGE_DIR . 'face/smile.png" />',
	'\s(\(\^-\^)'	=> ' <img alt="$1" src="' . IMAGE_DIR . 'face/bigsmile.png" />',
	'\s(\(\.\.;)'	=> ' <img alt="$1" src="' . IMAGE_DIR . 'face/oh.png" />',
	'\s(\(\^_-\))'	=> ' <img alt="$1" src="' . IMAGE_DIR . 'face/wink.png" />',
	'\s(\(--;)'	=> ' <img alt="$1" src="' . IMAGE_DIR . 'face/sad.png" />',
	'\s(\(\^\^;\))'	=> ' <img alt="$1" src="' . IMAGE_DIR . 'face/worried.png" />',
	'\s(\(\^\^;)'	=> ' <img alt="$1" src="' . IMAGE_DIR . 'face/worried.png" />',

	// Push buttons, 0-9 and sharp (Compatibility with cell phones)
	'&amp;(pb1);'	=> '[1]',
	'&amp;(pb2);'	=> '[2]',
	'&amp;(pb3);'	=> '[3]',
	'&amp;(pb4);'	=> '[4]',
	'&amp;(pb5);'	=> '[5]',
	'&amp;(pb6);'	=> '[6]',
	'&amp;(pb7);'	=> '[7]',
	'&amp;(pb8);'	=> '[8]',
	'&amp;(pb9);'	=> '[9]',
	'&amp;(pb0);'	=> '[0]',
	'&amp;(pb#);'	=> '[#]',

	// Other icons (Compatibility with cell phones)
	'&amp;(zzz);'	=> '[zzz]',
	'&amp;(man);'	=> '[man]',
	'&amp;(clock);'	=> '[clock]',
	'&amp;(mail);'	=> '[mail]',
	'&amp;(mailto);'=> '[mailto]',
	'&amp;(phone);'	=> '[phone]',
	'&amp;(phoneto);'=>'[phoneto]',
	'&amp;(faxto);'	=> '[faxto]',
);

//広告TrackのパラメータをWikiNameとして読み込まないようにするための設定変数
$adcode = array(
	'utm_source',
	'gclid',
	'OVRAW',
	'__utma',
	'fbclid'
);


/////////////////////////////////////////////////
// プラグイン表示用
$other_plugin_categories = array(
	'layout'		=>'レイアウト',
	'multimedia'	=>'マルチメディア',
	'page'			=>'ページ関連',
	'link'			=>'リンク',
	'seo'			=>'SEO',
	'calendar'		=>'カレンダー',
	'search'		=>'サイト内検索',
	'favorite'		=>'お気に入り登録',
	'information'	=>'情報表示',
	'marketing'		=>'マーケティング',
	'program'		=>'プログラム',
	'social'	=>'SNS',
);

// key = prefix(c:convert f:format i:inline) + plugin name
$other_plugins = array(
	// layout
	'cclear'         => array('name'=>"回り込み解除",	     'category'=>'layout', 'help'=>'ToolboxEtc#ot_clear',	'insert'=>"\n#clear\n"),
	'fhr'            => array('name'=>"区切り線",	         'category'=>'layout', 'help'=>'ToolboxEtc#ot_hr',		'insert'=>"\n----\n"),
	'cbox'           => array('name'=>"ボックス",	         'category'=>'layout', 'help'=>'ToolboxEtc#ot_box',	'insert'=>"\n#box(green,4px,double,#efe,75%){{\nここに文字を入れる\n}}\n"),
	'cstyle2'        => array('name'=>"段組み",           'category'=>'layout', 'help'=>'ToolboxEtc#ot_para',	'insert'=>"\n#style2(L){{\nここは、左側の情報\n}}\n#style2(R){{\nここは、右側の情報\n}}\n#clear\n"),
	'cscrollbox'     => array('name'=>"スクロール付きの枠", 'category'=>'layout', 'help'=>'ToolboxEtc#ot_scroll','insert'=>"\n#scrollbox(横幅,縦幅){{\nこの中ではQHMの書式が使えます。\n}}\n"),
	'ciframe'        => array('name'=>"iFrame",          'category'=>'layout', 'help'=>'ToolboxEtc#ot_iframe',	'insert'=>"\n#iframe(URL,500px,450px)\n"),
	'cmenu'          => array('name'=>"メニュー切り替え",	 'category'=>'layout', 'help'=>'ToolboxEtc#ot_menu',	'insert'=>"\n#menu(メニューにするページ名)\n"),
	'cmain_visual'   => array('name'=>'メインビジュアル',  'category'=>'layout', 'help'=>'ToolboxEtc#ot_mainvisual', 'insert'=>"\n#main_visual(画像ファイルのパス,画像の説明,表示位置)\n"),
	'clogo_image'    => array('name'=>'ロゴ画像の切替',    'category'=>'layout', 'help'=>'ToolboxEtc#ot_logoimage', 'insert'=>"\n#logo_image(画像のファイル名)\n"),
	'caccordion'     => array('name'=>'アコーディオン',    'category'=>'layout', 'help'=>'ToolboxEtc#ot_accordion', 'insert'=>"\n#accordion{{\n* 一つ目のタイトル\n見出し1がタイトルになります。\n\n* 二つ目のタイトル\nここに書いた文章が隠れます\n}}\n"),
	'ctabbox'        => array('name'=>'タブ',             'category'=>'layout', 'help'=>'ToolboxEtc#ot_tabbox', 'insert'=>"\n#tabbox{{\n* 一つ目のタイトル\n見出し1がタイトルになります。\n\n* 二つ目のタイトル\nここに書いた文章が隠れます\n}}\n"),
	'cselect_fsize'  => array('name'=>'文字サイズ選択',    'category'=>'layout', 'help'=>'ToolboxEtc#ot_select_fsize', 'insert'=>"\n#select_fsize\n"),
	// multimedia
	'idlbutton'      => array('name'=>"ダウンロードボタン", 'category'=>'multimedia', 'help'=>'ToolboxEtc#ot_downbutton',	'insert'=>"&dlbutton(ファイルパス);"),
	'idllink'        => array('name'=>"ダウンロードリンク", 'category'=>'multimedia', 'help'=>'ToolboxEtc#ot_downlink',	'insert'=>"&dllink(ダウンロードファイル名){表示};"),
	'cplayvideo'     => array('name'=>"ビデオ再生",        'category'=>'multimedia', 'help'=>'ToolboxEtc#ot_playvideo',	'insert'=>"\n#playvideo(動画ファイルパス,300,300)\n"),
	'cvimeo'         => array('name'=>"vimeo動画",        'category'=>'multimedia', 'help'=>'ToolboxEtc#ot_vimeo',	'insert'=>"\n#vimeo(Vimeo動画のID)\n"),
	'cjplayer'       => array('name'=>"音声再生",	          'category'=>'multimedia', 'help'=>'ToolboxEtc#ot_jplayer',	'insert'=>"\n#JPlayer{{\n音声のタイトル,設置場所/音源ファイル名\n}}\n"),
	'cslides'        => array('name'=>"画像切替スライドショー", 'category'=>'multimedia', 'help'=>'ToolboxEtc#ot_slides','insert'=>"\n#slides{{\n画像1のURL\n画像2のURL\n}}\n"),
	'cslideshow'     => array('name'=>"画像スライドショー", 'category'=>'multimedia', 'help'=>'ToolboxEtc#ot_playslide','insert'=>"\n#slideshow(350){{\n画像1のURL,画像の説明1\n画像2のURL,画像の説明2\n画像3のURL,画像の説明3\n}}\n"),
	'igreybox'       => array('name'=>"グレーボックス",    'category'=>'multimedia', 'help'=>'ToolboxEtc#ot_greybox','insert'=>"&greybox(URL,説明,グループ){表示};"),
	'ilightbox'      => array('name'=>"ライトボックス",    'category'=>'multimedia', 'help'=>'ToolboxEtc#ot_lightbox','insert'=>"&lightbox2(画像,説明,グループ){表示};"),
	'cgmapfun'      => array('name'=>"Googleマップ",    'category'=>'multimedia', 'help'=>'ToolboxEtc#ot_gmapfun','insert'=>"\n#gmapfun(){{\n住所,タイトル,ページ名\n}}\n"),
	// page
	'csecret'        => array('name'=>"認証ページ",       'category'=>'page',	'help'=>'ToolboxEtc#ot_secret',	'insert'=>"\n#secret(パスワード)\n"),
	'cclose'         => array('name'=>"閉鎖",            'category'=>'page',	'help'=>'ToolboxEtc#ot_close','insert'=>"\n#close\n"),
	'cautoclose'     => array('name'=>"有効期限設定",	    'category'=>'page',	'help'=>'ToolboxEtc#ot_expire',	'insert'=>"\n#autoclose(2007-09-16,転送先)\n"),
	'credirect'      => array('name'=>"転送",            'category'=>'page',	'help'=>'ToolboxEtc#redirect',		'insert'=>"\n#redirect(転送先ページorURL)\n"),
	'cinclude_skin'  => array('name'=>"個別デザイン変更",  'category'=>'page',	'help'=>'ToolboxEtc#ot_skin','insert'=>"\n#include_skin(デザイン名)\n"),
	'cfb_page'       => array('name'=>"Facebookタブ",  'category'=>'page',	'help'=>'ToolboxEtc#ot_fb_page','insert'=>"\n#fb_page\n"),
	'cfb_likegate'   => array('name'=>"Facebookいいね切替タブ",  'category'=>'page',	'help'=>'ToolboxEtc#ot_fb_likegate','insert'=>"\n#fb_likegate(いいね前のページ名)\n"),
	// link
	'contents'       => array('name'=>"もくじ",           'category'=>'link', 'help'=>'ToolboxEtc#ot_contents', 'insert'=>"\n#contents\n"),
	'cotherwin'      => array('name'=>"別ウィンドウリンク", 'category'=>'link', 'help'=>'ToolboxEtc#ot_open',	'insert'=>"&otherwin(URL){表示};"),
	'ianame'         => array('name'=>"アンカー",	         'category'=>'link', 'help'=>'ToolboxEtc#ot_anchor',	'insert'=>"&aname(アンカー名);"),
	'imodoru'        => array('name'=>"もどる",           'category'=>'link', 'help'=>'ToolboxEtc#ot_back',	'insert'=>"&modoru;"),
	'fnoautolink'    => array('name'=>"自動リンク無効",    'category'=>'link', 'help'=>'ToolboxEtc#ot_noautolink',	'insert'=>"\nNOAUTOLINK:\n"),
	'itag'           => array('name'=>"タグ",             'category'=>'link', 'help'=>'ToolboxEtc#ot_tag',	'insert'=>"&tag(タグ名1,タグ名2);"),
	'crelated'       => array('name'=>"関連ページ",        'category'=>'link', 'help'=>'ToolboxEtc#ot_related',	'insert'=>"\n#related\n"),
	// seo
	'ckeywords'      => array('name'=>"キーワードの変更",	 'category'=>'seo', 'help'=>'ToolboxEtc#ot_keywords',	'insert'=>"\n#keywords(key1,key2,key3,...)\n"),
	'cdescription'   => array('name'=>"サイトの説明の変更", 'category'=>'seo', 'help'=>'ToolboxEtc#ot_descri',	'insert'=>"\n#description(ここにサイトの説明)\n"),
	'ffreetitle'     => array('name'=>"フリータイトル",    'category'=>'seo', 'help'=>'ToolboxEtc#ot_freetitle',	'insert'=>"\nFREETITLE:ここにページのタイトル\n"),
	'fhead'          => array('name'=>"ヘッドコピー",	     'category'=>'seo', 'help'=>'ToolboxEtc#ot_headcopy',	'insert'=>"\nHEAD:ここにヘッドコピーを書く\n"),
	'fnoindex'       => array('name'=>"クロール禁止",	     'category'=>'seo', 'help'=>'ToolboxEtc#ot_noindex',	'insert'=>"\nNOINDEX:\n"),
	// calendar
	'cblog'          => array('name'=>"簡易ブログ",        'category'=>'calendar', 'help'=>'ToolboxEtc#ot_blog',		'insert'=>"\n* タイトル\n\n#blog\n#tagcloud\n#clear\n&br;\n#blog_viewer(Blog,this)\n"),
	'cblog_rss'          => array('name'=>"簡易ブログRSS", 'category'=>'calendar', 'help'=>'ToolboxEtc#ot_blog_rss',	 'insert'=>"&blog_rss;"),
	'ccalendar2'     => array('name'=>"カレンダー",        'category'=>'calendar', 'help'=>'ToolboxEtc#ot_calendar2',	'insert'=>"\n#calendar2\n"),
	// search
	'csearch'        => array('name'=>"検索窓",          'category'=>'search', 'help'=>'ToolboxEtc#ot_sbox',	'insert'=>"\n#search\n"),
	'csearch_menu'   => array('name'=>"検索窓(メニュー)",	'category'=>'search', 'help'=>'ToolboxEtc#ot_smenu',	'insert'=>"\n#search_menu\n"),
	'gsearch'        => array('name'=>"Google検索窓",    'category'=>'search', 'help'=>'ToolboxEtc#ot_google',	'insert'=>"\n#gsearch(ドメイン)\n"),
	// favorite
	'iaddfavorite'   => array('name'=>"ブラウザ",	          'category'=>'favorite',  'help'=>'ToolboxEtc#ot_favbrowser',	'insert'=>"&addfavorite(サイト名,button);"),
	'iyahoobookmark' => array('name'=>"Yahoo!ブックマーク", 'category'=>'favorite', 'help'=>'ToolboxEtc#ot_favyahoo',		'insert'=>"&yahoobookmark(登録するタイトル);"),
	'iaddgoogle'     => array('name'=>"Googleリーダー",    'category'=>'favorite',  'help'=>'ToolboxEtc#ot_favgoogle',		'insert'=>"&addgoogle;"),
	//information
	'ilastmod'       => array('name'=>"最終更新日",       'category'=>'information',	'help'=>'ToolboxEtc#ot_mdate',	'insert'=>"&lastmod(ページ名);"),
	'cshowrss'       => array('name'=>"RSSの読込み",	     'category'=>'information',	'help'=>'ToolboxEtc#ot_rss',	'insert'=>"\n#showrss(ここにRSSのURL)\n"),
	'cpopular'       => array('name'=>"人気の○件",       'category'=>'information',	'help'=>'ToolboxEtc#ot_popular',	'insert'=>"\n#popular(5)\n"),
	'crecent'        => array('name'=>"最新の○件",       'category'=>'information',	'help'=>'ToolboxEtc#ot_recent',	'insert'=>"\n#recent(5)\n"),
	// marketing
	'cvote'          => array('name'=>"投票",             'category'=>'marketing',	'help'=>'ToolboxEtc#ot_vote',	'insert'=>"\n#vote(項目名1,項目名2)\n"),
	'cconversion'    => array('name'=>"コンバージョン計測", 'category'=>'marketing',	'help'=>'ToolboxEtc#ot_conv',	'insert'=>"\n#conversion(step番号,グループ名)\n"),
	'iconversion'    => array('name'=>"クリックコンバージョン",'category'=>'marketing',	'help'=>'ToolboxEtc#ot_cconv',	'insert'=>"&conversion(step番号,グループ名,名前,URL){表示};"),
	'iabsplit2'      => array('name'=>"ABスプリットテスト", 'category'=>'marketing',	'help'=>'ToolboxEtc#ot_absplit',	'insert'=>"\n#absplit2(パターンAのページ名,パターンBのページ名)\n"),
	// program
	'chtml'  => array('name'=>"html",     'category'=>'program',	'help'=>'ToolboxEtc#ot_html',	'insert'=>"\n#html{{\n・・HTMLコード・・\n}}\n"),
	'cbeforescript'  => array('name'=>"beforescript",     'category'=>'program',	'help'=>'ToolboxEtc#ot_bscript',	'insert'=>"\n#beforescript{{\n・・ここにスクリプト・・\n}}\n"),
	'clastscript'    => array('name'=>"lastscript",	      'category'=>'program',	'help'=>'ToolboxEtc#ot_lscript',	'insert'=>"\n#lastscript{{\n・・ここにスクリプト・・\n}}\n"),
	// social
	'cfb_likebutton'  => array('name'=>"Facebook いいねボタン",    'category'=>'social',	'help'=>'ToolboxEtc#ot_fb_likebutton',	'insert'=>"\n#fb_likebutton\n"),
	'cfb_likebox'  => array('name'=>"Facebook いいねボックス",    'category'=>'social',	'help'=>'ToolboxEtc#ot_fb_likebox',	'insert'=>"\n#fb_likebox(FacebookページのURL)\n"),
	'cfb_recommends'  => array('name'=>"Facebook おすすめ一覧",    'category'=>'social',	'help'=>'ToolboxEtc#ot_fb_recommends',	'insert'=>"\n#fb_recommends\n"),
	'cfb_comments'  => array('name'=>"Facebook コメント欄",    'category'=>'social',	'help'=>'ToolboxEtc#ot_fb_comments',	'insert'=>"\n#fb_comments\n"),
	'ctw_button'  => array('name'=>"ツイートボタン",    'category'=>'social',	'help'=>'ToolboxEtc#ot_tw_button',	'insert'=>"\n#tw_button\n"),
	'cgp_button'  => array('name'=>"+1ボタン",    'category'=>'social',	'help'=>'ToolboxEtc#ot_gp_button',	'insert'=>"\n#gp_button\n"),
);
?>
