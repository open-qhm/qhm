<?php

define('PLUGIN_QHMSETTING_LOGO_PATH', CACHE_DIR.'qhm_logo.');
define('PLUGIN_QHMSETTING_LOGO_PREV_PATH', CACHE_DIR.'qhm_logo_preview.');
define('PLUGIN_QHMSETTING_USER_INI_FILE', 'qhm_users.ini.txt');
define('PLUGIN_QHMSETTING_ACCESS_INI_FILE', 'qhm_access.ini.txt');
define('PLUGIN_QHMSETTING_ALLOW_PASSWD_PATTERN', "/^[!-~]+$/");

/**
* qhmsettingが動作するメインの関数
*/
function plugin_qhmsetting_action()
{
	global $vars, $username, $style_name, $script;
	$qt = get_qt();
	$qt->setv('no_menus', TRUE);//メニューやナビ等をconvertしない

	$include_bs = '
<link rel="stylesheet" href="skin/bootstrap/css/bootstrap.min.css" />
<script type="text/javascript" src="skin/bootstrap/js/bootstrap.min.js"></script>';
	$qt->appendv_once('include_bootstrap_pub', 'beforescript', $include_bs);

	$head = '
<link rel="stylesheet" href="skin/hokukenstyle/qhm.css" />
<style type="text/css">
body {background-color: #E7E7E7;}
</style>';
	$qt->appendv('beforescript', $head);

	//check admin, setting
	if($username != $_SESSION['usr'] && $vars['phase']!='user2' && $vars['phase'] != 'script' && $vars['phase'] != 'sssavepath'){
		return array('msg'=>'アクセスできません', 'body'=>'<h2>アクセス制限</h2><p>このページは、管理者のみアクセスできます</p>');
	}

	if( !is_writable('qhm.ini.php') ){
		return array('msg'=>'設定ファイルエラー', 'body'=>'<h2>エラー</h2><p>設定ファイル qhm.ini.php に書き込めません。qhm.ini.phpに書き込み権限(666)を設定して下さい。');
	}

	//POSTされている場合
	$phase = isset($vars['phase']) ? $vars['phase'] : 'default';
	$mode = isset($vars['mode']) ? $vars['mode'] : '';

	$func = 'plugin_qhmsetting_'.$phase.'_'.$mode;

	if( function_exists($func) ){
		$ret = '<div class="admin"><p><a href="'.$script.'">QHMトップ</a> &gt; <a href="'.$script.'?cmd=qhmsetting">設定一覧</a> &gt; here</p>'
			.plugin_qhmsetting_phpversion_block()
			.$func() . '</div>';
	}
	else{

		$title = '
<p><a href="'.$script.'">QHMトップ</a> &gt; here</p>'
.plugin_qhmsetting_phpversion_block()
.'<h2>QHM v'. QHM_VERSION. ' haik設定</h2>';
		$ret = $title.plugin_qhmsetting_default();
	}

	if (isset($_SESSION['flash_msg']))
	{
		if (FALSE && strpos($_SESSION['flash_msg'], '<') !== FALSE)
		{
			$ret = $_SESSION['flash_msg'] . $ret;
		}
		else
		{
			$ret = '
<div style="background-color:#fee;border:1px solid #c99;padding: 10px;">
	'. $_SESSION['flash_msg'].'
</div>
' . $ret;
		}
		unset($_SESSION['flash_msg']);
	}



	$style_name = '..';
	return array('msg'=>"QHMサイト情報設定", 'body'=>$ret);
}

function plugin_qhmsetting_default()
{

	global $script, $other_plugins;
	$qt = get_qt();

	$scrt = $script . '?plugin=qhmsetting&amp;mode=form&amp;phase=';

	$setlist = array(
		'design'    => array(
			'help' => 'ChangeDesign',
			'url'  => $scrt. 'design',
			'img'  => IMAGE_DIR. 'settings_design.png',
			'title' => 'デザインの変更',
			'subtitle' => 'ロゴ画像の設定、ロゴ部分の文字、テンプレートの設定を行います。',
			'limited' => false,
		),
		'info'      => array(
			'help' => 'SiteConfig',
			'url'  => $scrt. 'info',
			'img'  => IMAGE_DIR. 'settings_site.png',
			'title' => 'サイト情報の設定',
			'subtitle' => 'キーワード、サイト説明、ヘッダー、フッター、アクセス解析タグなどの設定を行います。',
			'limited' => false,
		),
		'admin'     => array(
			'help' => 'SetPassword',
			'url'  => $scrt. 'admin',
			'img'  => IMAGE_DIR. 'settings_user.png',
			'title' => 'ユーザー名、パスワードの変更',
			'subtitle' => '編集用のユーザー名、パスワードの設定を行います。',
			'limited' => false,
		),
		'qblog' => array(
			'help' => 'QBlogSetting',
			'url' => $script . '?cmd=qblog',
			'img' => IMAGE_DIR. 'settings_blog.png',
			'title' => 'ブログ設定',
			'subtitle' => 'QHMブログの設定を行います。',
			'limited' => false,
		),
		'useradmin' => array(
			'help' => 'UserAuthSetting',
			'url'  => $scrt. 'user',
			'img'  => IMAGE_DIR. 'settings_access.png',
			'title' => 'アクセス権限設定',
			'subtitle' => '特定のページにアクセス権限を設定し、アクセスできるユーザーを追加設定できます。',
			'limited' => true,
		),
		'clear'     => array(
			'help' => 'SettingCache',
			'url'  => $scrt. 'clear',
			'img'  => IMAGE_DIR. 'settings_cache.png',
			'title' => '高速化設定、キャッシュの初期化',
			'subtitle' => '表示を高速化するためのキャッシュ機能を設定、キャッシュの初期化、テンプレートを初期化を行います。',
			'limited' => false,
		),
		'back'      => array(
			'help' => 'EasyBackup',
			'url'  => $script. '?cmd=dump',
			'img'  => IMAGE_DIR. 'settings_backup.png',
			'title' => 'バックアップ',
			'subtitle' => 'QHMのバックアップをダウンロードできます。フルバックアップ、重要ファイルのみのバックアップなど可能です。',
			'limited' => true,
		),
		'counter'   => array(
			'help' => 'Counter',
			'url'  => $scrt. 'counter',
			'img'  => IMAGE_DIR. 'settings_counter.png',
			'title' => 'アクセスカウンター',
			'subtitle' => 'アクセスカウンターをリセットします。',
			'limited' => false,
		),
		'chmod'     => array(
			'help' => 'UserAuthSetting',
			'url'  => $scrt. 'chmod',
			'img'  => IMAGE_DIR. 'settings_file.png',
			'title' => 'ファイル権限設定',
			'subtitle' => '削除できない、FTPエラーが起こる原因である「ファイル権限」を設定、チェックします。',
			'limited' => true,
		),
		'mail'      => array(
			'help' => 'MailSetting',
			'url'  => $scrt. 'mail',
			'img'  => IMAGE_DIR. 'settings_mail.png',
			'title' => 'メール送信設定',
			'subtitle' => '送信メールサーバーを設定できます（SMTP送信、GoogleAppsなどの場合）',
			'limited' => true,
		),
		'close'     => array(
			'help' => 'SettingCloseSite',
			'url'  => $scrt. 'close',
			'img'  => IMAGE_DIR. 'settings_close.png',
			'title' => 'サイトの閉鎖／公開',
			'subtitle' => 'QHMで作成された全ページを閉鎖します。閉鎖後は、管理者権限でログインすることで編集、閲覧が可能です。',
			'limited' => true,
		),
		'mobile'    => array(
			'help' => 'RedirectMobile',
			'url'  => $scrt. 'mobile',
			'img'  => IMAGE_DIR. 'settings_mobile.png',
			'title' => '携帯アクセス転送',
			'subtitle' => '携帯端末からのアクセスを携帯専用サイトなどに転送します。',
			'limited' => false,
		),
/*
		'gmap'      => array(
			'help' => 'GoogleMapsKey',
			'url'  => $scrt. 'gmap',
			'img'  => IMAGE_DIR. 'settings_googlemap.png',
			'title' => 'Googleマップキー',
			'subtitle' => 'QHMでGoogleマップを使うためのキーを設定します。',
			'limited' => false,
		),
*/
		'sns'       => array(
			'help' => 'SettingOGP',
			'url'  => $scrt. 'sns',
			'img'  => IMAGE_DIR. 'settings_sns.png',
			'title' => 'ソーシャル連携',
			'subtitle' => 'QHMとSNSの連携設定をします。',
			'limited' => true,
		),
		'update'       => array(
			'help' => 'HowToUseUpdatePlugin',
			'url'  => $script. '?cmd=system_updater',
			'img'  => IMAGE_DIR. 'settings_update.png',
			'title' => 'アップデート',
			'subtitle' => 'QHMのアップデートを行います。',
			'limited' => true,
		),

	);

	foreach ($setlist as $setname => $set) {
		$setlist[$setname]['help'] = '';
//--<LimitedSetting>--
		if ($set['limited']) {
			$setlist[$setname]['limited'] = false;
		}
//--</LimitedSetting>--
	}
//--<UnlimitBackup>--
//--</UnlimitBackup>--

	$html = '';

	// !commu がインストールされていたらバージョンを調べる
	// v2.5 未満の場合、SWFUなどが正常に動かないと警告を出す

	$idx_php = file_get_contents('index.php');
	if (preg_match_all('/require/', $idx_php, $mts) > 1)
	{
		if (file_exists('commu/config.php')
			&& preg_match("/COMMU_VERSION[\"'], '(.*?)'/", file_get_contents('commu/config.php'), $mts)
			&& $mts[1] < 2.5)
		{
			$html .= <<<EOD
<p class="warning" style="background:#fff6bf;color:#514721;border-color:#ffd324;">
	SWFUなどが正常に動作しない場合は、
	ご利用中の<strong>Quick Commu</strong>を最新版にバージョンアップすることで問題が解決されます。
</p>
EOD;
		}
	}

	$update_showcase = '';
	if (get_qhm_option('banner'))
	{
		$update_list_url = h('//ensmall.net/update/index.php?cmd=hkn_upinfo&cat=openqhm');
		$update_showcase = <<< EOD
			<style>
			.qhmsetting-update-showcase {
				width: 100%;
				margin: 10px auto 50px;
				padding: 0 40px;
			}
			</style>
			<h3>お知らせ</h3>
			<div class="qhmsetting-update-showcase">
				<div class="fb-page" data-href="https://www.facebook.com/open.qhm" data-width="500" data-height="300" data-small-header="true" data-adapt-container-width="true" data-hide-cover="true" data-show-facepile="false" data-show-posts="true"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/open.qhm"><a href="https://www.facebook.com/open.qhm">Open QHM</a></blockquote></div></div>
			</div>
EOD;
		$fb_init = <<< EOD
			<div id="fb-root"></div>
			<script>(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = "//connect.facebook.net/ja_JP/sdk.js#xfbml=1&version=v2.4&appId=182764055138172";
			fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>
EOD;
		$qt->setv('fb_init', $fb_init);
	}
// HTML生成
	$html .= <<<EOD
<p>QHMの設定を行います。<br />
以下の項目から、変更したいものをクリックしてください。</p>

{$update_showcase}

<table class="table table-bordered">
EOD;

	$scnt = 0;
	foreach ($setlist as $set) {
		if ($scnt % 2 == 0) {
			$html .= '
	<tr>';
		}

		if ($set['limited']) {
			$html .= '
		<td style="background-color:#e0e0e0;"><p>
			<img src="'.$set['img'].'" alt="'.$set['title'].'" title="'.$set['title'].'" style="vertical-align:top;" />
			<span style="font-weight:bold;color:#666;">'. $set['title']. '</span>
		</p><span style="color:#888;">'.$set['subtitle'].'</span></td>';
		} else {
			$html .= '
		<td><p>
			<img src="'.$set['img'].'" alt="'.$set['title'].'" title="'.$set['title'].'" style="vertical-align:top;" />
			<a href="'.$set['url'].'" style="font-weight:bold;">'.$set['title'].'</a>'.$set['help'].'
		</p>'.$set['subtitle'].'</td>';
		}

		if ($scnt %2 == 1) {
			$html .= '
	</tr>';
		}

		$scnt++;
	}


	$html .= '
</table>
';

	return $html;

}

/**
 *   PHPバージョンを判定し、4の場合警告を出す
 */
function plugin_qhmsetting_phpversion_block() {
	$msg = '';
	$ver = phpversion();
	if (version_compare($ver, '5', '<')) {//TODO:

		$wkstr = '
#style(class=box_red_dsm){{
CENTER:&deco(bold,red,,){このQHMはPHP4で動作しています};

QHMではPHP4をサポートしておりません。
正常動作させるには、ご利用のサーバーのPHPをバージョンアップ（PHP5.3 以上推奨）してください。
}}
';
		$msg = convert_html($wkstr);
	}
	return $msg;

}

/**
* デザインの設定（フォームを表示）
*/
function plugin_qhmsetting_design_form($error = '')
{
	global $logo_image, $script, $vars, $style_name;
	global $enable_wp_theme, $enable_wp_theme_name, $wp_add_css;
	global $other_plugins;
	global $smart_name;

	$qt = get_qt();
	$addcsv = '
<style type="text/css">
#designList td, #smartDesignList td {
	padding: 2px;
}
td label {
	cursor: pointer;
}
input[name="qhmsetting[style_name]"], input[name="qhmsetting[smart_name]"],
#designList input[name=design] {
	display:none;
}
</style>
';
	$qt->appendv('beforescript', $addcsv);

	// ヘルプリンク作成
	$hlp_design    = '';

	$params = plugin_qhmsetting_getparams();

	//現在の状態を格納
	if(isset($_SESSION['temp_design']) || isset($vars['design']))
	{
		if (isset($vars['preview'])) {
			$hash = '';
			if ($vars['preview'])
			{
				$_SESSION['temp_design'] = $vars['design'];
				$_SESSION['temp_enable_wp'] = $vars['enable_wp_theme']=='1' ? 1 : 0;
				$_SESSION['wp_add_css'] = $vars['wp_add_css'];
				$_SESSION['temp_design_customizer'] = $vars['customizer'] == '1' ? 1 : 0;
			}
			//プレビュー解除
			else
			{
				if (isset($_SESSION['temp_skin']))
				{
					$hash = '#qhmDesignGetter';
				}
				else
				{
					$hash = '#qhmdesign';
				}
				unset(
					$_SESSION['temp_design'],
					$_SESSION['temp_enable_wp'], $_SESSION['wp_add_css'],
					$_SESSION['temp_skin'], $_SESSION['temp_css'],
					$_SESSION['temp_style_type'], $_SESSION['temp_style_path']
				);
			}

			if (isset($vars['redirect']))
			{
  				$_SESSION['flash_msg'] = 'プレビューを解除しました。';
  				$redirect = $script;

  			  if (is_url($vars['redirect']))
  			  {
      				$redirect = $vars['redirect'];
  			  }
  			  else if ($vars['redirect'] == 0)
  			  {
      				$redirect = $script. '?cmd=qhmsetting&mode=form&phase=design'. $hash;
  			  }
			}
      redirect($redirect);
			exit;
		}
		$tmp_design_name = isset($vars['design']) ? $vars['design'] : $_SESSION['temp_design'];
		$wp_or_qhm = ( isset($vars['enable_wp_theme']) ? $vars['enable_wp_theme'] : $_SESSION['temp_enable_wp'] )
				 ? 'WordPressテーマ' : 'QHM専用';

		$body_msg = <<<EOD
<div style="color:#c00;border:1px solid #c00;background-color:#fcc;width:80%;margin:0 auto;padding:0 1em;text-align:center;">
<b>{$wp_or_qhm}の「{$tmp_design_name}」デザインのプレビュー状態です。</b>
<a href="{$script}?cmd=qhmsetting&mode=form&phase=design&preview=0&redirect=0">[解除]</a>
</div>
EOD;
	}
	else{
		$wp_or_qhm = $enable_wp_theme ? 'WordPressテーマ' : 'QHM専用';
		$design_name = $enable_wp_theme ? $enable_wp_theme_name : $style_name;
		$body_msg = <<<EOD
<div class="alert alert-success">
<b>{$wp_or_qhm}の「{$design_name}」デザインを利用中です</b>
</div>
EOD;

	}

	//WordPressテーマのスキャン
	$hd = opendir('skin/wordpress/');
	$wp_dirs = array();
	while( $entry = readdir($hd) )
	{
		if(is_dir('skin/wordpress/'.$entry) && ($entry!='.') && ($entry!='..') && (file_exists('skin/wordpress/'.$entry.'/index.php')))
		{
			$wp_dirs[] = $entry;
		}
	}
	closedir($hd);
	sort($wp_dirs);

	//スマートフォンデザインのスキャン

	$hd = opendir(SMART_DIR);
	$smart_dirs = array();
	while( $entry = readdir($hd) )
	{
		if(is_dir(SMART_DIR.$entry) && ($entry!='.') && ($entry!='..') && (file_exists(SMART_DIR.$entry.'/smart.css')))
		{
			$smart_dirs[] = $entry;
		}
	}
	closedir($hd);
	sort($smart_dirs);


	//ここから
	$body = $body_msg;
	$body .= '<h2>デザインの設定'.$hlp_design.'</h2>';


	//======================================================
	//  QHM専用デザイン設定フォーム
	//======================================================
	$wp_index = count($wp_dirs) ? '<li><a href="#wordpress">WordPressテーマの設定</a></li>' : '';
	$body .= <<<EOD
<p>　QHM専用デザインを使う、もしくは、WordPressテンプレートを使うことができます。利用したいデザインは、事前に skin/hokukenstyle、もしくは、skin/wordpress にフォルダに分けて、アップロードしてください。</p>
<ul class="list1">
<li><strong>項目</strong>
	<ul class="list2">
		<li><a href="#qhmdesign">QHM専用デザインの設定</a></li>
		{$wp_index}
		<li><a href="#smartdesign">スマートフォンのデザイン設定</a></li>
	</ul>
</li>
</ul>
EOD;
	$body .= '<br />';
	$body .= ($error!='') ? '<p style="color:red">'.$error.'</p>' : '';

/*
	$body .= '<h2 id="qhmdesign">QHM専用デザインの設定</h2>
<div style="border:2px solid #ccc;padding:1em;background-color:#fafafa">
';
*/
	$body .= '<h2 id="qhmlogo">ロゴの設定</h2>
<div class="well">
';


	//デザインタイプの指定
	if( $params['style_type'] == 'text' )
	{
		$logotype_msg = 'テキストを使う設定';
		$img_tag = 'ロゴ画像なし';
	}
	else
	{
		$logotype_msg = '画像を使う設定';
		$img_tag = '<img src="'.$logo_image.'" style="width:450px" />';
	}

	$body .= <<<EOD
<form method="post" action="{$script}" enctype="multipart/form-data">
	<p><label><input type="radio" name="qhmsetting[style_type]" checked="checked" value="none" />変更なし (現在: {$logotype_msg})</label></p>
	<hr style="border-color: #ccc;border-style:solid;border-width:1px;border-bottom-style:none;" />
	<p><label><input type="radio" name="qhmsetting[style_type]" id="logoImageRadio" value="image" /> 画像を使う(500KB以下推奨)：</label><input type="file" name="imagefile" id="logoImageButton" /><br />
	<span style="font-size:80%">現在の設定：</span>{$img_tag}</p>
	<p><label><input type="radio" name="qhmsetting[style_type]" id="logoTextRadio" value="text" /> テキストを使う</label><br />
	<input type="text" name="qhmsetting[page_title]" id="logoTextInput" value="{$params['page_title']}" size="35" /></p>
	<p>
		<input type="submit" value="変更する" class="btn btn-primary" />
		<input type="hidden" name="cmd" value="qhmsetting" />
		<input type="hidden" name="mode" value="confirm" /><input type="hidden" name="phase" value="design" />
		<input type="hidden" name="from" value="design_form" /><input type="hidden" name="pcmd" value="post" />
	</p>
</form>
</div>

<h2 id="qhmdesign">デザインテンプレート（テーマ）の設定</h2>
<div class="well">

<form method="post" action="{$script}">
<input type="hidden" name="qhmsetting[style_type]" value="none" />

EOD;


	//デザインテンプレートの指定
	$obj = dir(SKIN_DIR);
	$dirs = $dsgndirs = array();

	while( $entry = $obj->read() )
	{
		if(is_dir(SKIN_DIR.$entry) && ($entry!='.') && ($entry!='..') && (file_exists(SKIN_DIR.$entry.'/main.css')))
		{
			if (preg_match('/^(haik_).*$/', $entry)) {
				$dsgndirs['1'][] = $entry;
			}
			else if (preg_match('/^(i_).*$/', $entry)) {
				$dsgndirs['2'][] = $entry;
			}
			else if (preg_match('/^(qd_).*$/', $entry)) {
				$dsgndirs['3'][] = $entry;
			}
			else if (preg_match('/^(org_).*$/', $entry)) {
				$dsgndirs['4'][] = $entry;
			}
			else if (preg_match('/^(qrs_).*$/', $entry)) {
				$dsgndirs['5'][] = $entry;
			}
			else if (preg_match('/^(g_).*$/', $entry)) {
				$dsgndirs['6'][] = $entry;
			}
			else {
				$dsgndirs['7'][] = $entry;
			}
		}
	}
	ksort($dsgndirs);
	foreach ($dsgndirs as $row) {
		sort($row);
		$dirs = array_merge($dirs,$row);
	}
	unset($dsgndirs);

	//======================================================
	//  デザイン選択フォーム
	//======================================================


	$prev_link_format = '<a href="'. $script. '?cmd=qhmsetting&phase=design&mode=msg&from=design_form&enable_wp_theme=0&qhmsetting[style_name]=%s">[適用]</a>&nbsp;&nbsp;';
	$del_link_format = '';
//--<RemoveQHMDesign>--
	$del_link_format = '<a href="'. $script. '?cmd=qhmsetting&phase=design&mode=remove&from=design_form&option=confirm&qhmsetting[style_name]=%s">[削除]</a>';
//--</RemoveQHMDesign>--

	$body .= '<div id="qhmdesignStatus"></div>'. "\n";
	$body .= '<p>サムネイルをクリックするとプレビューできます。</p>';
	$body .= '
<script type="text/javascript">
$(function(){
	// !change bgcolor on select
	$("#designList label")
	.on("click", function(e){
		e.stopPropagation();
		e.preventDefault();
		$("input:radio", this).prop("checked", true);
		$(this).closest("form").submit();
	})
	.bind("mouseover", function(){
		$(this).closest("td").css({"outline" : "5px solid #CBE86B"});
	})
	.bind("mouseout", function(){
		$(this).closest("td").css({"outline" : ""});
	});

	if (typeof QhmSetting == "undefined") {
		QhmSetting = {
			prev_link_format: "'. addcslashes($prev_link_format, '"') .'",
			del_link_format: "'. addcslashes($del_link_format, '"') .'"
		};
	}
});
</script>
';

	$body .= '<table id="designList">';

	$preview_style = isset($_SESSION['temp_design'])? $_SESSION['temp_design']: '';

	$cell_cnt = count($dirs) + (3- count($dirs) % 3);

	for ($i = 0; $i < $cell_cnt; $i++) {
		if ($i % 3 == 0) {
			$body .= '<tr>';
		}
		if (isset($dirs[$i])) {
			$dir = $dirs[$i];
			$thumb = file_exists(SKIN_DIR . $dir . '/thumbnail.png')? SKIN_DIR . $dir . '/thumbnail.png': SKIN_DIR . $dir . '/thumbnail.jpg';
			$ckd = ( $dir == $params['style_name'] ) ? ' checked="checked" class="currentStyle"' : '';
			$ckd_msg = $ckd=='' ? '' : ' (現在の設定)';
			$links = $ckd=='' ? '%s%s': '';
			$prev_link = $preview_style == $dir? '': sprintf($prev_link_format, $dir);
			$del_link = $preview_style == $dir? '': sprintf($del_link_format, $dir);
			$links = sprintf($links, $prev_link, $del_link);
			$body .= '<td width="33%" style="vertical-align:top;"><label>
	<img src="'.$thumb.'" alt="'.$dir.'サムネール" width="180" height="200" /><br />
	<input type="radio" name="design" value="'.$dir.'"'.$ckd.' style="margin-right:5px;" />'.$dir.$ckd_msg.'
	</label>'. $links .'
</td>
';
		}
		else
		{
			$body .= '<td></td>';
		}

		if ($i % 3 == 2) {
			$body .= '</tr>';
		}
	}
	$body .= '</table>';

//--<GetQHMDesign>--
	//======================================================
	//  !デザイン取得
	//======================================================
	if (is_writable(SKIN_DIR)) {
		$body .= '
<p>
  <a href="'.h($script).'?cmd=theme_uploader" class="btn btn-link">
    <i class="glyphicon glyphicon-upload"></i> テーマファイルをアップロードする</a>
</p>
<!-- Modal -->
<div class="modal fade" id="authModal" tabindex="-1" role="dialog" aria-labelledby="authModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="authDialogLabel">Ensmall Club 認証</h4>
      </div>
      <div class="modal-body">
        <div class="error-msg"></div>
        <div class="form-group">
          <label for="" class="control-label">メールアドレス</label>
          <input type="text" name="email" id="email" class="form-control" />
        </div>
        <div class="form-group">
          <label for="" class="control-label">パスワード</label>
          <input type="password" name="password" id="password" class="form-control" />
        </div>
      	<input type="hidden" name="phase" value="club" />
      	<input type="hidden" name="mode" value="auth" />
      	<input type="hidden" name="from" value="design_form" />
      	<input type="hidden" name="plugin" value="qhmsetting" />
      	<input type="hidden" name="pcmd"   value="post" />

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">キャンセル</button>
        <button type="button" class="btn btn-primary" data-modal-type="login">ログイン</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="designModal" tabindex="-1" role="dialog" aria-labelledby="designModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="authDialogLabel">取得できるデザイン一覧</h4>
      </div>
      <div class="modal-body" style="max-height:550px;overflow-y:scroll;">
        <div class="row">
          <div class="col-sm-3" data-box="menu">
            <div class="list-group affix" data-spy="affix" data-offset-top="60" data-offset-bottom="200">
              <a href="#haik_ikk" class="list-group-item" data-design-type="haik">[-]haik</a>
              <a href="#i_001" class="list-group-item">[1]花柄</a>
              <a href="#i_101" class="list-group-item">[2]ビジネス</a>
              <a href="#i_201" class="list-group-item">[3]透明モダン</a>
              <a href="#i_301" class="list-group-item">[4]ノート型</a>
              <a href="#i_401" class="list-group-item">[5]アート系</a>
              <a href="#i_501" class="list-group-item">[6]モダン・ポップ</a>
              <a href="#i_601" class="list-group-item">[7]高級感</a>
              <a href="#i_701" class="list-group-item">[8]シンプル・和</a>
              <a href="#i_801" class="list-group-item">[9]ノート型2</a>
              <a href="#biz_black" class="list-group-item">[-]標準デザイン</a>
            </div>
          </div>
          <div class="col-sm-9" data-box="designlist">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">キャンセル</button>
        <button type="button" class="btn btn-primary" data-modal-type="download">取得</button>
      </div>
    </div>
  </div>
</div>
';
	}
//--</GetQHMDesign>--

	$body .= '
<input type="hidden" name="phase" value="design" />
<input type="hidden" name="mode" value="form" />
<input type="hidden" name="preview" value="1" />
<input type="hidden" name="enable_wp_theme" value="0" />
<input type="hidden" name="plugin" value="qhmsetting" />
<input type="hidden" name="pcmd"   value="post" />
</form>

';


//--<FTPAccess>--
	//======================================================
	//  !FTP接続フォーム
	//======================================================
	$body .= '
<div class="modal fade" id="ftpModal" tabindex="-1" role="dialog" aria-labelledby="ftpModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="authDialogLabel">FTP接続</h4>
      </div>
      <div class="modal-body">
        <p>PHPがセーフモードで稼働しているため、FTP接続が必要です。このサーバーのFTPアカウントとパスワードを入力してください。</p>
        <div class="form-group">
          <label for="" class="control-label">FTPアカウント</label>
          <input type="text" name="username" id="ftpusername" class="form-control" />
        </div>
        <div class="form-group">
          <label for="" class="control-label">パスワード</label>
          <input type="password" name="password" id="ftppassword" class="form-control" />
        </div>
        <div class="form-group">
          <label for="" class="control-label">設置先フォルダ</label>
          <input type="text" name="dir" id="ftpdir" class="form-control" />
          <input type="hidden" name="use_dir" value="0" id="ftpusedir" />
        </div>

        <input type="hidden" name="design_name" value="" />
        <input type="hidden" name="phase" value="ftp" />
        <input type="hidden" name="mode" value="access" />
        <input type="hidden" name="from" value="design_form" />
        <input type="hidden" name="plugin" value="qhmsetting" />
        <input type="hidden" name="pcmd"   value="post" />

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">キャンセル</button>
        <button type="button" class="btn btn-primary" data-modal-type="connect">接続</button>
      </div>
    </div>
  </div>
</div>
';
//--</FTPAccess>--

$body .= "</div>";

	//======================================================
	//  WordPressデザイン設定フォーム
	//======================================================
	if(count($wp_dirs)){
		$wp_add_css = isset($_SESSION['wp_add_css'])? $_SESSION['wp_add_css']: $wp_add_css;
		$body .= '<br /><br /><br /><h2 id="wordpress">WordPressテーマを利用する</h2>'."\n";
		$body .= <<<EOD
<div class="well">
	<p>以下から、WordPressのテーマを選択して下さい。</p>
<form method="post" action="{$script}">
EOD;

		$body .= '<select name="qhmsetting[enable_wp_theme_name]">';
		foreach($wp_dirs as $dir){
			$ckd = ( $dir == $params['enable_wp_theme_name'] ) ? 'selected="selected"' : '';
			$body .= '<option value="'.$dir.'" '.$ckd.'>'.$dir.'</option>';
		}
		$body .= '</select>';

		$body .= '
※ロゴ画像は、選べません<br /><br />
<b>追加スタイル</b><br />
<textarea name="qhmsetting[wp_add_css]" rows="5" style="width:80%">'
.$wp_add_css.
'</textarea>
<input type="hidden" name="phase" value="design" />
<input type="hidden" name="mode" value="confirm" />
<input type="hidden" name="from" value="design_form" />
<input type="hidden" name="plugin" value="qhmsetting" />
<input type="hidden" name="pcmd"   value="post" />
<input type="hidden" name="enable_wp_theme" value="1" />
<p style="text-align:center">
	<input type="button" value="プレビュー" id="WpPreviewButton" style="" class="btn btn-primary" />
	<input type="submit" value="設定を確認する" style="" class="btn btn-primary" /></p>
</form>
<p><strong>ご確認下さい : </strong></p>
<ul style="margin-left:3%;">
<li>WordPressのテーマは、ブログのためのデザインです。ご自身で多少のカスタマイズが必要です</li>
<li>テンプレートに埋め込まれている文字や、サイドバーに読み込むリンクなど、適切に再編集して下さい</li>
<li>標準的な作り方に沿わないテーマを使うと、一部のプラグインが動作しないことがあります</li>
<li>WPテーマとの完全な互換性はありません。ご自身でのカスタマイズが必要となります</li>
</ul>
</div>
';

	}

	//======================================================
	// !スマートフォン デザイン設定
	//======================================================
	if(count($smart_dirs)){
		$body .= '<br /><br /><br /><h2 id="smartdesign">スマートフォンのデザイン設定</h2>'."\n";
		$body .= <<<EOD
<div class="well">
	<script type="text/javascript">
	$(function(){
		$("input:checkbox[name='qhmsetting[enable_smart_style]']")
		.click(function(){
			if (this.checked)
			{
				$("#smartDesignList").parent().slideDown("normal");
			}
			else
			{
				//設定を外した時は保存する
				if ($(this).data("default"))
				{
					$(this).closest("form").submit();
				}
				else
				{
					$("#smartDesignList").parent().slideUp("normal");
				}
			}
		})
		.each(function(){
			$(this).data("default", this.checked);
			if ( ! this.checked)
			{
				$("#smartDesignList").parent().hide();
			}

		});

		// !change bgcolor on select
		$("#smartDesignList input:radio").click(function(){
			$(this).closest("form").submit();
		})
			.closest("td").hover(
				function(){
					$(this).css({"outline" : "5px solid #CBE86B"});
				},
				function(){
					$(this).css({"outline" : ""});
				}
			);
	});
	</script>
	<p>
		「スマートフォンデザインを利用する」にチェックを入れて、スマートフォンのデザインを選択して下さい。<br />
		デザインをクリックすると反映されます。
	</p>
	<form method="post" action="{$script}">
EOD;

		$checked = ($params['enable_smart_style'] == '1') ? ' checked="checked"' : '';
		$body .= '
	<p><input type="hidden" name="qhmsetting[enable_smart_style]" value="0" />
<label><input type="checkbox" name="qhmsetting[enable_smart_style]" value="1"'.$checked.' />スマートフォンデザインを利用する</label></p>
';
		$body .= '<div><table id="smartDesignList">';
		$smart_cnt = ceil(count($smart_dirs) / 3) * 3;
		for ($i = 0; $i < $smart_cnt; $i++) {
			if ($i % 3 == 0) {
				$body .= '<tr>';
			}
			if (isset($smart_dirs[$i])) {
				$dir = $smart_dirs[$i];
				$thumb = file_exists(SMART_DIR . $dir . '/thumbnail.png')? SMART_DIR . $dir . '/thumbnail.png': SMART_DIR . $dir . '/thumbnail.jpg';
				$ckd = ( $dir == $params['smart_name'] ) ? ' checked="checked" class="currentStyle"' : '';
				$ckd_msg = $ckd=='' ? '' : ' (現在の設定)';
				$body .= '<td width="33%"><label>
		<img src="'.$thumb.'" alt="'.$dir.'サムネール" width="153" height="200" /><br />
		<input type="radio" name="qhmsetting[smart_name]" value="'.$dir.'"'.$ckd.' />'.$dir.$ckd_msg.'
	</label></td>
	';
			}
			if ($i % 3 == 2) {
				$body .= '</tr>';
			}
		}
		$body .= '</table></div>
<input type="hidden" name="phase" value="design" />
<input type="hidden" name="mode" value="confirm" />
<input type="hidden" name="from" value="design_form" />
<input type="hidden" name="plugin" value="qhmsetting" />
<input type="hidden" name="pcmd"   value="post" />
</form>
</div>
';
	}

	$body .= '<br />';
	$body .= $body_msg;


	return $body;
}

function plugin_qhmsetting_design_confirm()
{
	global $vars, $page_title, $script;

	// --------------------------------------------
	// 直接のアクセスを拒否する
	if( !isset($vars['from']) || $vars['from']!='design_form' )
	{
		return 'このページへの直接アクセスは、無効です。';
	}


	// ---------------------------------------------
	// sessionにデータを格納 & フィルタ
	// ---------------------------------------------

	//postからのデータを格納
	foreach( $vars['qhmsetting'] as $key=>$val)
	{
		$_SESSION['qhmsetting'][$key]
			= $vars['qhmsetting'][$key]
			= htmlspecialchars($val);
	}

	//////////////////////////////////////////////////////////
	//
	// WordPressデザイン設定
	//
	if( $vars['enable_wp_theme']=='1')
	{
		$style_name = $vars['qhmsetting']['enable_wp_theme_name'];
		//search thumnail image file
		$style_thumb = 'skin/wordpress/'.$style_name.'/screenshot';
		$style_img = '';
		if( file_exists($style_thumb.'.jpg') )
			$style_img = $style_thumb.'.jpg';
		else if( file_exists($style_thumb.'.png') )
			$style_img = $style_thumb.'.png';
		else if( file_exists($style_thumb.'.gif') )
			$style_img = $style_thumb.'.gif';

		$style_img = ($style_img != '') ? '<img src="'.$style_img.'" title="Thumbnail" />' : '';

		$wp_add_css = htmlspecialchars($vars['qhmsetting']['wp_add_css']);
		// ---------------------------------------------
		// Output confirmation contents
		//

		$body = <<<EOD
	<h2>デザイン設定の確認</h2>
	<p><b>WordPressテーマ : </b>{$style_name}<br />
	{$style_img}</p>
	<p style="font-weight:bold">追加スタイル</p>
	<p style="border:1px dashed #999;padding:10px">
	$wp_add_css
	</p>

	<form method="post" action="{$script}">
	<p style="text-align:center"><input type="button" value="戻る" onclick="history.back();" class="btn" /><input type="submit" value="設定する" style="font-size:16px" class="btn btn-primary" /></p>
	<input type="hidden" name="phase" value="design" />
	<input type="hidden" name="mode" value="msg" />
	<input type="hidden" name="plugin" value="qhmsetting" />
	<input type="hidden" name="from" value="design_form" />
	<input type="hidden" name="qhmsetting[enable_wp_theme_name]" value="{$style_name}" />
	<input type="hidden" name="qhmsetting[enable_wp_theme]" value="1" />
	</form>
EOD;


	}
	///////////////////////////////////////////////////////////////////
	//
	// スマートフォンデザイン設定
	//
	else if (isset($vars['qhmsetting']['smart_name']) && $vars['qhmsetting']['smart_name'] != '') {

		$enable_smart_style = $vars['qhmsetting']['enable_smart_style'];
		$use_smart = 'スマートフォンデザイン：'. (($enable_smart_style == '1') ? '利用する' : '利用しない');

		//design template setting
		$smart_name = $vars['qhmsetting']['smart_name'];

		//search thumnail image file
		$style_thumb = SMART_DIR.$smart_name.'/thumbnail';
		$style_img = '';
		if( file_exists($style_thumb.'.jpg') )
			$style_img = $style_thumb.'.jpg';
		else if( file_exists($style_thumb.'.png') )
			$style_img = $style_thumb.'.png';
		else if( file_exists($style_thumb.'.gif') )
			$style_img = $style_thumb.'.gif';

		$style_img = ($style_img != '') ? '<img src="'.$style_img.'" title="Thumbnail" />' : '';

		$vars['from'] = 'design_form';
		$vars['smart_name_setting'] = 1;
		return plugin_qhmsetting_design_msg();


		// ---------------------------------------------
		// Output confirmation contents
		//
		$body = '<h2>スマートフォンデザイン設定の確認</h2>';
		$body .= '<p>'.$use_smart.'</p>';
		if ($enable_smart_style == '1') {
			$body .= '<p><b>テンプレート</b><br />'.$smart_name.'<br />'.$style_img.'</p>';
		}
		$body .= '
	<form method="post" action="'.$script.'">
	<p style="text-align:center"><input type="submit" value="設定する" class="btn btn-primary" /></p>
	<input type="hidden" name="phase" value="design" />
	<input type="hidden" name="mode" value="msg" />
	<input type="hidden" name="plugin" value="qhmsetting" />
	<input type="hidden" name="from" value="design_form" />
	<input type="hidden" name="qhmsetting[enable_smart_style]" value="'.$enable_smart_style.'" />
	<input type="hidden" name="qhmsetting[smart_name]" value="'.$smart_name.'" />
	</form>
';


	}
	///////////////////////////////////////////////////////////////////
	//
	// QHM専用デザイン設定
	//
	else{
		//WordPress をオフ
		$_SESSION['qhmsetting']['enable_wp_theme'] = 0;

		//imageを選んだ場合、page_titleをデフォルトで上書き
		if($vars['qhmsetting']['style_type']==='image')
		{
			$_SESSION['qhmsetting']['page_title'] = $vars['qhmsetting']['page_title'] = $page_title;
		}
		//変更しないを選んだ場合
		if($vars['qhmsetting']['style_type']==='none')
		{
			$_SESSION['qhmsetting']['page_title'] = $vars['qhmsetting']['page_title'] = $page_title;
			unset($_SESSION['qhmsetting']['style_type']);
		}

		// ---------------------------------------------
		// validation check
		// ---------------------------------------------
		$error = '';

		//画像を使う場合の処理
		if( $vars['qhmsetting']['style_type']=='image')
		{
			// ファイルエラーチェック
			switch ($_FILES["imagefile"]["error"]) {
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$error .= "ロゴ画像のサイズが大きすぎます。<br />";
				break;
			case UPLOAD_ERR_PARTIAL;
			case UPLOAD_ERR_NO_FILE;
				$error .= "ロゴ画像をアップロードできません。";
			}

			// ファイルサイズをもう一度チェック
			if($_FILES["imagefile"]["size"] > 1024 * 1024){
				$error .= "ロゴ画像が、1MB以上でサイズオーバーしています。";
			}

			// png, jpg, gif以外のファイルを拒否
			if(!preg_match("/^image\/.*(jpeg|png|gif)$/i", $_FILES["imagefile"]["type"]) AND !preg_match('/\.(jpe?g|png|gif)$/', $_FILES['imagefile']['name'])) {
				$error .= "ロゴ画像は、jpeg, png, gif形式で、作成して下さい。:".$_FILES["imagefile"]["type"];
			}

			//特定の文字以外の文字を使用したファイルを拒否
			if(preg_match("/[^\w\d\-\.]/", $_FILES["imagefile"]["name"])){
				$error .= "ロゴ画像は、英数半角のみでファイル名を付けて下さい。（".$_FILES["file"]["name"]."）";
			}

			$logoname = CACHE_DIR.$_FILES['imagefile']['name'];
			if( file_exists($logoname) && !is_writable($logoname)  )
			{
				$error .= "ロゴ画像を上書きできません。ロゴ画像の書き込み権限を有効にするか、アップするファイル名を変更してください。";
			}
		}

		if($error != ""){
			unset($_SESSION['qhmsetting']);
			return plugin_qhmsetting_design_form($error);

		}


		// ---------------------------------------------
		// ここからが処理
		// ---------------------------------------------

		$style_type = $vars['qhmsetting']['style_type'];
		$logo_img = '';

		if( $style_type==='none' )
		{
			$logo_type = '変更しない ';
		}
		else if( $style_type=='text')
		{
			$logo_type = 'テキスト';
			$logo_img = '『 '.$vars['qhmsetting']['page_title'].' 』';
		}
		else //画像の場合の処理
		{
			//var_dump($_FILES);
			$logo_type = '画像';
			$tmparray = explode('.', $_FILES['imagefile']['name']);
			$type = $tmparray[ count($tmparray)-1 ];
			$prev_file = PLUGIN_QHMSETTING_LOGO_PREV_PATH.$type;
			if(move_uploaded_file($_FILES['imagefile']['tmp_name'], $prev_file))
			{

				chmod($prev_file, 0666);
				$logo_img = '<img src="'.$prev_file.'" style="width:450px;" /><br />'
					.'ページタイトル: '.$_SESSION['qhmsetting']['page_title'];

				$_SESSION['qhmsetting_logo_prev_file'] = $prev_file;
				$_SESSION['qhmsetting_logo_file'] = PLUGIN_QHMSETTING_LOGO_PATH.$type;
			}
			else{
				//error handling
				return plugin_qhmsetting_design_form('ロゴ画像をセットできませんでした。');
			}
		}


		//design template setting
		$style_name = $vars['qhmsetting']['style_name'];

		//search thumnail image file
		$style_thumb = SKIN_DIR.$style_name.'/thumbnail';
		$style_img = '';
		if( file_exists($style_thumb.'.jpg') )
			$style_img = $style_thumb.'.jpg';
		else if( file_exists($style_thumb.'.png') )
			$style_img = $style_thumb.'.png';
		else if( file_exists($style_thumb.'.gif') )
			$style_img = $style_thumb.'.gif';

		$style_img = ($style_img != '') ? '<img src="'.$style_img.'" title="Thumbnail" />' : '';


		// ---------------------------------------------
		// Output confirmation contents
		//

		$vars['from'] = 'design_form';
		return plugin_qhmsetting_design_msg();

		$body = <<<EOD
	<h2>デザイン設定の確認</h2>
	<p><b>ロゴのタイプ : </b>{$logo_type}<br />
	{$logo_img}</p>
	<p><b>デザインテンプレート</b><br />
	{$style_name}<br />
	{$style_img}</p>

	<form method="post" action="{$script}">
	<p style="text-align:center"><input type="submit" value="設定する" class="btn btn-primary" /></p>
	<input type="hidden" name="phase" value="design" />
	<input type="hidden" name="mode" value="msg" />
	<input type="hidden" name="plugin" value="qhmsetting" />
	<input type="hidden" name="from" value="design_form" />

	<input type="hidden" name="qhmsetting[style_name]" value="{$style_name}" />
	<input type="hidden" name="qhmsetting[style_type]" value="{$style_type}" />
	<input type="hidden" name="qhmsetting[page_title]" value="{$vars['qhmsetting']['page_title']}" />
	<input type="hidden" name="qhmsetting[enable_wp_theme]" value="0" />
	</form>
EOD;
	}

	return $body;
}

function plugin_qhmsetting_design_msg()
{
	global $vars, $logo_image, $script;

	// --------------------------------------------
	// 直接のアクセスを拒否する
	if( !isset($vars['from']) || $vars['from']!='design_form' )
	{
		return 'このページへの直接アクセスは、無効です。';
	}

	// ---------------------------------------------
	// sessionにデータを格納 & フィルタ
	foreach( $vars['qhmsetting'] as $key=>$val)
	{
		$_SESSION['qhmsetting'][$key]
			= $vars['qhmsetting'][$key]
			= htmlspecialchars($val);
	}

	//imageの場合、ロゴ画像の位置を変更し、設定に加える
	if( ! isset($_SESSION['qhmsetting']['enable_wp_theme']) OR $_SESSION['qhmsetting']['enable_wp_theme'] == '0')
	{
		if($_SESSION['qhmsetting']['style_type']=='image')
		{
			rename($_SESSION['qhmsetting_logo_prev_file'], $_SESSION['qhmsetting_logo_file']);
			$_SESSION['qhmsetting']['logo_image'] = $_SESSION['qhmsetting_logo_file'];
		}
	}

	//noneの場合、変更しない
	if($_SESSION['qhmsetting']['style_type']==='none')
	{
		unset($_SESSION['qhmsetting']['style_type']);
	}

	plugin_qhmsetting_update_ini();

	unset(
		$_SESSION['temp_design'],
		$_SESSION['temp_enable_wp'], $_SESSION['wp_add_css'],
		$_SESSION['temp_skin'], $_SESSION['temp_css'],
		$_SESSION['temp_style_type'], $_SESSION['temp_style_path']
	);

	if (isset($vars['smart_name_setting']))
	{
		$to = $script . '?cmd=qhmsetting&mode=form&phase=design';
		$_SESSION['flash_msg'] = 'スマートフォンのデザイン設定を変更しました';
		$msg = '';
	}
	else
	{
		$to = '';
		$msg = 'デザインの変更を完了しました';
	}

	//goto top
	redirect($to, $msg);
}


//--<RemoveQHMDesign>--
/**
 *   デザイン削除の確認
 */
function plugin_qhmsetting_design_remove()
{
	global $vars, $script;

	// --------------------------------------------
	// 直接のアクセスを拒否する
	if( !isset($vars['from']) || $vars['from']!='design_form' )
	{
		return 'このページへの直接アクセスは、無効です。';
	}

	$params = plugin_qhmsetting_getparams();

	$d_cur = trim($params['style_name']);
	$d_rem = trim($vars['qhmsetting']['style_name']);
	$dir = SKIN_DIR . $d_rem;
	$thumb = $dir . '/thumbnail.jpg';
	$thumb = file_exists($thumb)? $thumb: $dir . '/thumbnail.png';

	$body = '';
	//SKIN DIR が書き込み権限なし
	if (!is_writable(SKIN_DIR)) {
		return plugin_qhmsetting_design_form("skin/hokukenstyle/ に書き込み権限がありません");
	}
	//現行のデザインは削除できない
	else if ($d_cur == $d_rem) {
		return plugin_qhmsetting_design_form("現行のデザイン：$d_rem は削除できません");
	}
	//デザイン削除可能
	else if (file_exists($dir)) {
		if ($vars['option'] == 'confirm') {
		//確認画面を表示
		$body .= <<<EOD
	<h2>QHM専用デザイン削除の確認</h2>
	<p><b>デザイン名 : </b>{$d_rem}<br />
	<img src="{$thumb}" width="180" height="200" alt="{$d_rem}"/></p>

	<form method="post" action="{$script}">
	<p style="text-align:center"><input type="submit" value="削除する" class="btn btn-danger" /></p>
	<input type="hidden" name="qhmsetting[style_name]" value="{$d_rem}" />
	<input type="hidden" name="phase" value="design" />
	<input type="hidden" name="mode" value="remove" />
	<input type="hidden" name="option" value="complete" />
	<input type="hidden" name="plugin" value="qhmsetting" />
	<input type="hidden" name="from" value="design_form" />
	<input type="hidden" name="qhmsetting[enable_wp_theme_name]" value="{$style_name}" />
	<input type="hidden" name="qhmsetting[enable_wp_theme]" value="1" />
	</form>
EOD;

		}
		//デザインを削除
		else if ($vars['option'] == 'complete') {
			plugin_qhmsetting_remove_dir($dir);

			return plugin_qhmsetting_design_form("QHM専用デザイン：$d_rem を削除しました");
		}
	}
	//デザインが存在しない
	else {
		return plugin_qhmsetting_design_form("存在しないデザイン：$d_rem が指定されました");

	}

	return $body;

}

//--</RemoveQHMDesign>--

function plugin_qhmsetting_remove_dir($dir, $remain_dir = false) {
	if ($handle = opendir("$dir")) {
		while (false !== ($item = readdir($handle))) {
			if ($item != "." && $item != "..") {
				if (is_dir("$dir/$item")) {
					remove_directory("$dir/$item");
				} else {
					unlink("$dir/$item");
				}
			}
		}
		closedir($handle);
		if (!$remain_dir) {
			if (is_link($dir)) {
				unlink($dir);
			} else {
				rmdir($dir);
			}
		}
	}
}



/**
* サイト情報
*/
function plugin_qhmsetting_info_getVals($params)
{
	global $script, $script_ssl;

	return array(
		'page_title' => array(
			'title' => 'タイトル文字',
			'msg' => 'ページタイトルを設定します',
			'default' => $params['page_title']
		),
		'owneraddr' => array(
			'title' => '所在地',
			'msg' => '会社などは、会社の住所を。個人は、アバウトに住所を書く',
			'default' => $params['owneraddr']
		),
		'ownertel' => array(
			'title' => '連絡先',
			'msg' => '会社などは電話番号、個人なら適当 (contact me by emailなど)に',
			'default' => $params['ownertel']
		),
		'headcopy' => array(
			'title' => 'ヘッドコピー',
			'msg' => 'ページの一番上の１行メッセージ（SEOに重要)',
			'default' => $params['headcopy']
		),
		'keywords' => array(
			'title' => 'キーワード',
			'msg' => '検索エンジンに必要なキーワードです。　例）ホームページ,作成,北研',
			'default' => $params['keywords'],
			'option' => 'textarea',
		),
		'description' => array(
			'title' => '説明',
			'msg' => '検索エンジンに必要なホームページの説明です。',
			'default' => $params['description'],
			'option' => 'textarea',
		),
		'custom_meta' => array(
			'title' => 'その他のタグ',
			'msg' => '&lt;head&gt;タグ内に、入れたいタグを自由に書いて下さい',
			'default' => $params['custom_meta'],
			'option' => 'textarea',
		),
		'accesstag' => array(
			'title' => 'アクセス解析タグ',
			'msg' => 'アクセス解析用のタグを入力します。<code>&lt;body&gt;</code>タグの終了直前に出力されます。管理者モードでは解析されません',
			'default' => $params['accesstag'],
			'option' => 'textarea',
		),
		'ga_tracking_id' => array(
		    'title' => 'GAトラッキングID',
		    'msg'   => 'Googleのユニバーサルアナリティクスを利用する場合、トラッキングIDを入力します。トラッキングコードは<code>&lt;head&gt;</code>タグの終了直前に出力されます。管理者モードでは解析されません',
		    'default' => $params['ga_tracking_id'],
		),
		'modifier' => array(
			'title' => 'サイト管理者',
			'msg' => 'サイトの管理者名を入力します（会社名、組織名など）',
			'default' => $params['modifier']
		),
		'modifierlink' => array(
			'title' => '管理者ページ',
			'msg' => '管理者が別のサイトを持っていれば、そこへのリンクを書く',
			'default' => $params['modifierlink']
		),
		'nowindow' => array(
			'title' => '外部リンクの<br />開き方',
			'msg' => '外部リンクを別ウインドウで開く、開かないの設定<br />（おすすめは、「別ウインドウ」です)',
			'default' => $params['nowindow'],
			'option' => array(
				0 => "別ウインドウ",
				1 => "別ウインドウを開かない",
				2 => "特定のウインドウ",
			),
		),
		'reg_exp_host' => array(
			'title' => '別ウインドウで<br />開かない設定',
			'msg' => '別ウインドウで開かないホスト名を | で区切って入力してください',
			'default' => $params['reg_exp_host'],
		),
		'qhm_adminmenu' => array(
			'title' => '管理者認証への<br />リンク表示',
			'msg' => '管理者ページへのアクセス方法を指定します<br />（おすすめは、「右下のQHMリンクから」です)',
			'default' => $params['qhm_adminmenu'],
			'option' => array(
				0 => "常にedit thisを表示",
				1 => "右下のQHMリンクから",
				2 => "なし(QHMAdminというページ)",
			),
		),
		'no_qhm_licence' => array(
			'title' => 'ライセンス表記',
			'msg' => 'ページ右のQHMライセンス表記を表示、非表示を設定します<br />（<a href="http://www.open-qhm.net/index.php?QHMLicence#n8d1069b" target="new">非表示にすると認証ページアクセスが難しくなります</a>）',
			'default' => $params['no_qhm_licence'],
			'option' => array(
				0 => "表示する",
				1 => "非表示（非推奨・QHMAdminページからログイン）",
			),
		),
/*		'qhm_access_key' => array(
			'title' => 'ショートカット',
			'msg' => '編集時に、ショートカットキーを使うか設定ができます。<br />',
			'default' => $params['qhm_access_key'],
			'option' => array(
				0 => "利用しない",
				1 => "利用する",
			),
		),*/
		'autolink' => array(
			'title' => '自動リンク',
			'msg' => 'ページ名が長く、「\'」や「"」を利用し、QHMが正常に動かない場合、<br />「オフ」にして下さい<br />',
			'default' => $params['autolink'],
			'option' => array(
				8 => "利用する",
				0 => "利用しない",
			),
		),
		'dummy_script' => array(
			'title' => 'リンク設定',
			'msg'   => 'リンク設定 : ' . $script . '<br />SSLリンク設定：' . $script_ssl
//--<SSLScript>--
				. '<br /><a href="'.$script.'?cmd=qhmsetting&mode=form&phase=script">変更するには、ここをクリック</a>'
//--</SSLScript>--
				,
			'default' => '',
			'option' => 'none'

		),
		'enable_fitvids' => array(
			'title' => '動画のサイズ調整',
			'msg'   => '貼り付けた YouTube, Vimeo 動画のサイズを自動で横幅ぴったりに調整します。',
			'default' => $params['enable_fitvids'],
			'option'  => array(
				1 => '調整する',
				0 => '調整しない'
			),
		),
		'unload_confirm' => array(
			'title' => 'ページ移動確認',
			'msg'   => '編集中に間違えて他のページへ移動して変更内容を消してしまわないよう確認するようにします。',
			'default' => $params['unload_confirm'],
			'option' => array(
				1 => '確認する',
				0 => '確認しない'
			),
		),
		'check_login' => array(
			'title' => 'ログインチェック機能',
			'msg'   => '編集中に時間が経つとログアウト状態になることがありますが、それを検知し再ログインフォームを表示します。チェックのタイミングを指定してください。',
			'default' => $params['check_login'],
			'option' => array(
				2 => '10秒間に一度',
				1 => '「ページの更新」など押下時',
				0 => 'チェックしない'
			),
		),
		'http_scheme' => array(
			'title' => 'HTTP/SSL接続',
			'msg'   => 'デザインの取得ができない場合、HTTP接続に切り替えてください。',
			'default' => $params['http_scheme'],
			'option' => array(
				'' => '自動',
				'https' => 'SSL接続',
				'http'  => 'HTTP接続'
			)
		),
	);

}

function plugin_qhmsetting_info_form($error = '')
{
	global $script;
	global $other_plugins;
	$hlp_info = '';

	$error_msg = ($error!='') ? '<p style="color:red">'.$error.'</p>' : '';

	$params = plugin_qhmsetting_getparams();
	$values = plugin_qhmsetting_info_getVals($params);

	$body = '<h2>QHM v'. QHM_VERSION. ' haik 設定'.$hlp_info.'</h2>';
	$body .= $error_msg;
	$body .= <<<EOD
<p>サイト情報の設定では、titleタグ、ヘッドコピー、フッターの情報、アクセス解析タグなどの設定を行います。</p>
<form method="post" action="$script" class="form-horizontal">
<!-- <table class="table table-bordered"  cellspacing="1" border="0"> -->
<tbody>
EOD;

	$input = '';
	foreach($values as $key=>$value)
	{

		if( isset($value['option'] ) )
		{

			if( is_array($value['option']) )
			{
				$input = '<select name="qhmsetting['.$key.']" class="form-control">'."\n";
				foreach($value['option'] as $okey=>$oval )
				{
					$selected = ($value['default']==$okey) ? 'selected="selected"' : '';
					$input .= '<option value="'.$okey.'" '.$selected.'>'.$oval.'</option>'."\n";
				}
				$input .= '</select>';
			}
			else if( $value['option']=='textarea' )
			{
				$input = '<textarea name="qhmsetting['.$key.']" rows="5" cols="55" class="form-control">'.h( $value['default'] ).'</textarea>';
			}
			else if( $value['option']=='none'){
				$input = '';
			}
		}
		else
		{
			if ( $key == 'modifierlink' && $value['default'] == 'http://www.example.co.jp/') {
				$value['default'] = dirname($script). '/';
			}
			$input = '<input type="text" size="50" name="qhmsetting['.$key.']" value="'.h( $value['default'] ).'"  class="form-control" />';
		}

		$body .= '
<div class="form-group">
  <label for="" class="control-label col-sm-3">'.$value['title'].'</label>
	<div class=" col-sm-9">
	  '.$input.'
  	<span class="help-block">'.$value['msg'].'</span>
	</div>
</div>
';
	}

	$body .= '
<div class="form-group">
  <div class="col-sm-9 col-sm-offset-3">
    <input type="submit" name="ok" value="設定を確認する" class="btn btn-primary" /></p>
  </div>
</div>
<input type="hidden" name="mode" value="download" />
<input type="hidden" name="phase" value="info" />
<input type="hidden" name="mode" value="confirm" />
<input type="hidden" name="plugin" value="qhmsetting" />
<input type="hidden" name="from" value="info_form" />
</form>
';

	return $body;
}

function plugin_qhmsetting_info_confirm()
{
	global $vars, $script;

	// --------------------------------------------
	// 直接のアクセスを拒否する
	if( !isset($vars['from']) || $vars['from']!='info_form' )
	{
		return 'このページへの直接アクセスは、無効です。';
	}


	// -----------------------------------
	// validation check
	// -----------------------------------
	$error = '';

	if($error != '')
		return plugin_qhmsetting_admin_form($error);


	// -----------------------------------
	// processing here
	// -----------------------------------
	$params = plugin_qhmsetting_getparams();
	$values = plugin_qhmsetting_info_getVals($params);

	$body = '<h2>QHM v'. QHM_VERSION. ' haik 設定</h2>';
	$body .= '
<p>以下の内容でよければ、ページ下部の「設定する」をクリックしてください</p>
<table class="table table-bordered">
<tbody>
';

	$qsv = $vars['qhmsetting'];
	foreach($values as $key=>$value)
	{
		if( isset($value['option']) && is_array($value['option']) )
		{
			$data = $value['option'][ $qsv[$key] ];
		}
		else
		{
			$data = $qsv[$key];
		}

		$body .= '<tr>
    <th>'.$value['title'].'</th>
    <td>'. nl2br(h($data)) .'</td>
</tr>
';
	}

	$body .= '
</tbody>
</table>
';

	$body .= '<form method="post" action="'.$script.'">';
	foreach($qsv as $key=>$val){
		$body .= '<input type="hidden" name="qhmsetting['.$key.']" value="'. h($val).'" />'."\n";
	}
	$body .= '
  <div class="form-group" style="text-align: center">
    <input type="button" name="back" value="戻る" onclick="history.back();" class="btn btn-default" /> <input type="submit" name="ok" value="設定する" class="btn btn-primary" />
  </div>
  <input type="hidden" name="phase" value="info" />
  <input type="hidden" name="mode" value="msg" />
  <input type="hidden" name="plugin" value="qhmsetting" />
  <input type="hidden" name="from" value="info_form" />
</form>';

//	var_dump($vars['qhmsetting']);
//	exit;

	return $body;
}

function plugin_qhmsetting_info_msg()
{
	global $vars, $script;

	// --------------------------------------------
	// 直接のアクセスを拒否する
	if( !isset($vars['from']) || $vars['from']!='info_form' )
	{
		return 'このページへの直接アクセスは、無効です。';
	}


	// --------------------------------------------
	foreach($vars['qhmsetting'] as $key=>$val)
	{
		$_SESSION['qhmsetting'][$key] = $val;
	}

	plugin_qhmsetting_update_ini();

	redirect($script . '?cmd=qhmsetting', 'サイト情報の変更完了');
	exit;

}

/**
* 編集用ID、パスワードの設定
*/
function plugin_qhmsetting_admin_form($error = '')
{
	global $script, $vars;
	global $other_plugins;

	$hlp_admin = '';

	$params = plugin_qhmsetting_getparams();


	$error_msg = ($error!='') ? '<p style="color:red">'.$error.'</p>' : '';

	//ユーザー名だけ特別扱い
	if( isset($vars['qhmsetting']['username']) ){
		$uname = $vars['qhmsetting']['username'];
	}
	else{
		$uname = $params['username'];
	}

	$body = <<<EOD
<h2>編集用ユーザー名の変更{$hlp_admin}</h2>
<p>ここで設定したユーザー名とパスワードは、忘れないようにして下さい。<br />
※英数半角と一部の記号のみ</p>
{$error_msg}
<form method="post" action="{$script}">

  <div class="form-group">
    <label for="" class="control-label">ユーザー名</label>
    <div class="row">
      <div class="col-sm-5">
        <input type="text" name="qhmsetting[username]" size="18" value="{$uname}" class="form-control">
      </div>
    </div>
  </div>
  <div class="form-group">
    <label for="" class="control-label">現在のパスワード</label>
    <div class="row">
      <div class="col-sm-5">
        <input type="password" name="qhmsetting[password]" size="18" class="form-control">
      </div>
    </div>
  </div>
  <div class="form-group">
    <label for="" class="control-label">新パスワード</label>
    <div class="row">
      <div class="col-sm-5">
        <input type="password" name="qhmsetting[password1]" size="18" class="form-control" style="margin-bottom: 5px;">
    		<input type="password" name="qhmsetting[password2]" size="18"  class="form-control">
    		<span class="help-block">確認のため、２度入力してください</span>
      </div>
    </div>
  </div>
  <div class="form-group">
    <label for="" class="control-label">メールアドレス</label>
    <div class="row">
      <div class="col-sm-12">
        <input type="text" name="qhmsetting[admin_email]" size="40" value="{$params['admin_email']}" class="form-control">
      </div>
    </div>
  </div>

  <div class="form-group">
    <input type="submit" value="設定を確認する" class="btn btn-primary" />
  </div>
  <input type="hidden" name="phase" value="admin" />
  <input type="hidden" name="mode" value="confirm" />
  <input type="hidden" name="plugin" value="qhmsetting" />
  <input type="hidden" name="from" value="admin_form" />
</form>

EOD;

	return $body;
}

function plugin_qhmsetting_admin_confirm()
{
	global $vars, $script;
	global $auth_users, $username, $passwd;

	// --------------------------------------------
	// 直接のアクセスを拒否する
	if( !isset($vars['from']) || $vars['from']!='admin_form' )
	{
		return 'このページへの直接アクセスは、無効です。';
	}


	// -----------------------------------
	// validation check
	// -----------------------------------
	$error = '';

	//ユーザーの重複を探すために
	unset( $auth_users[ $username ] );

	if( isset( $auth_users[ $vars['qhmsetting']['username'] ] ) )
		$error .= '他のユーザーと名前が重複しています<br />';
	if( $passwd != pkwk_hash_compute( $vars['qhmsetting']['password'] ) )
		$error .= '現在のパスワードと、一致しません<br />';
	if( !ctype_alnum($vars['qhmsetting']['username']) )
		$error .= 'ユーザー名は、半角英数のみで入力してください<br />';
	if( $vars['qhmsetting']['password1'] != $vars['qhmsetting']['password2'] )
		$error .= '新パスワードが一致しません<br />';
	if( !preg_match(PLUGIN_QHMSETTING_ALLOW_PASSWD_PATTERN , $vars['qhmsetting']['password1']) )
		$error .= 'パスワードは、英数半角と一部の記号のみ(スペース不可)で入力してください<br />';
	if( strlen($vars['qhmsetting']['password1']) < 6 )
		$error .= 'パスワードは、6文字以上を設定してください<br />';

	$email_match = '/^([a-z0-9_]|\-|\.|\+)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,6}$/i';
	if( !preg_match($email_match, $vars['qhmsetting']['admin_email']) )
		$error .= 'メールアドレスを正しく、入力してください<br />';

	if($error != '')
		return plugin_qhmsetting_admin_form($error);




	// -----------------------------------
	// process from here
	// -----------------------------------
//	$password = md5($vars['qhmsetting']['password1']);
	$password = $vars['qhmsetting']['password1'];

	$body = <<<EOD
<h2>ユーザー設定の確認</h2>
<p>以下の内容でよろしいでしょうか？</p>
<ul class="nav nav-stacked">
	<li><label>ユーザー名　　　：　</label><span style="font-size:24px">{$vars['qhmsetting']['username']}</span></li>
	<li><label>パスワード　　　：　</label>***********</li>
	<li><label>メールアドレス　：　</label><span style="font-size:24px">{$vars['qhmsetting']['admin_email']}</span></li>
</ul>
<form method="post" action="{$script}">
<p style="text-align:center"><input type="button" value="戻る" onclick="history.back();" class="btn btn-default" /> <input type="submit" value="設定する" class="btn btn-primary" /></p>
<input type="hidden" name="phase" value="admin" />
<input type="hidden" name="mode" value="msg" />
<input type="hidden" name="plugin" value="qhmsetting" />
<input type="hidden" name="from" value="admin_form" />
<input type="hidden" name="qhmsetting[username]" value="{$vars['qhmsetting']['username']}" />
<input type="hidden" name="qhmsetting[password]" value="$password" />
<input type="hidden" name="qhmsetting[admin_email]" value="{$vars['qhmsetting']['admin_email']}" />
<input type="hidden" name="qhmsetting[old_password]" value="{$vars['qhmsetting']['password']}" />
</form>
EOD;

	return $body;
}

function plugin_qhmsetting_admin_msg()
{
	global $vars, $script;
	$qt = get_qt();
	$params = plugin_qhmsetting_getparams();

	// --------------------------------------------
	// 直接のアクセスを拒否する
	if( !isset($vars['from']) || $vars['from']!='admin_form' )
	{
		return 'このページへの直接アクセスは、無効です。';
	}

	$uname = $_SESSION['qhmsetting']['username'] = $vars['qhmsetting']['username'];
	$_SESSION['qhmsetting']['passwd'] = '{x-php-md5}'.md5($vars['qhmsetting']['password']);
	$admin_email = $_SESSION['qhmsetting']['admin_email'] = $vars['qhmsetting']['admin_email'];

	// encrypt_ftp の再暗号化
	if ($params['encrypt_ftp'] != '')
	{
		require_once("./lib/Mcrypt.php");

		$mc = new ORMcrypt(trim($params['username']).trim($vars['qhmsetting']['old_password']));
		$codearr = array_pad(explode(',', $params['encrypt_ftp']), 2, '');
		$data = $mc->decrypt(trim($codearr[0]),trim($codearr[1]));
		$mc->set_key($uname.$vars['qhmsetting']['password']);
		$arr = $mc->encrypt($data);
		$_SESSION['qhmsetting']['encrypt_ftp'] = implode(',', $arr);
	}

	plugin_qhmsetting_update_ini();

	session_destroy();

	$qt->appendv('beforescript', '<meta http-equiv="refresh" content="5;URL='. h($script. '?cmd=qhmauth') .'" />');


	return <<<EOD
<h2>ユーザーの変更完了</h2>
<p>編集ユーザーの変更を完了しました。</p>
<p>新しいユーザー名は、<span style="font-size:24px;">{$uname}</span>、パスワードは「******」です。</p>
<p>管理者のメールアドレスに{$admin_email}を設定しました。<br />
パスワードの再取得などの際にこのメールアドレスを利用します。
</p>
<p>再度、ログインが必要となります。<a href="{$script}?plugin=qhmauth" style="font-weight:bold;background-color:#ff6;">ここをクリック</a>して下さい</p>
<p>
※5秒後にログイン画面に移動します</p>
EOD;

}


//--<MailForm>--
/**
* メール送信設定
*/
function plugin_qhmsetting_mail_form($error='')
{
	global $script;
	global $other_plugins;
	$hlp_mail = '';

	$params = plugin_qhmsetting_getparams();
	$error_msg = ($error!='') ? '<p style="color:red">'.$error.'</p>' : '';

	//notify
	if($params['notify']){
		$notify_checked1 = "checked";
		$notify_checked2 = "";
	}
	else{
		$notify_checked1 = "";
		$notify_checked2 = "checked";
	}
	//notify_diff_only
	if($params['notify_diff_only']){
		$notify_diff_only_checked1 = "checked";
		$notify_diff_only_checked2 = "";
	}
	else{
		$notify_diff_only_checked1 = "";
		$notify_diff_only_checked2 = "checked";
	}
	//smtp_auth
	if($params['smtp_auth']){
		$smtp_auth_checked1 = "checked";
		$smtp_auth_checked2 = "";
	}
	else{
		$smtp_auth_checked1 = "";
		$smtp_auth_checked2 = "checked";
	}
	//smtp_auth
	if($params['google_apps']){
		$google_apps_checked1 = "checked";
		$google_apps_checked2 = "";
	}
	else{
		$google_apps_checked1 = "";
		$google_apps_checked2 = "checked";
	}
	//sendmail setting
	if($params['exclude_to_name']){
		$exclude_to_name1 = 'checked';
		$exclude_to_name2 = '';
	}
	else{
		$exclude_to_name1 = '';
		$exclude_to_name2 = 'checked';
	}

	//is_qmail setting
	if($params['is_qmail']){
		$is_qmail1 = 'checked';
		$is_qmail2 = '';
	}
	else{
		$is_qmail1 = '';
		$is_qmail2 = 'checked';
	}

	//mail_encode
	if($params['mail_encode'] === 'ISO-2022-JP'){
		$mail_encode_checked1 = "checked";
		$mail_encode_checked2 = "";
	}
	else{
		$mail_encode_checked1 = "";
		$mail_encode_checked2 = "checked";
	}


	$body = <<<EOD
<h2>メール送信設定{$hlp_mail}</h2>
<h3>更新通知設定</h3>
<p>ページが更新される度に、メールで知らせる機能です。</p>
{$error_msg}
<form method="post" action="{$script}" class="form-horizontal">
  <div class="form-group">
    <label for="" class="control-label col-sm-4">更新通知</label>
    <div class="col-sm-8">
      <label class="radio-inline"><input type="radio" name="qhmsetting[notify]" value="1" $notify_checked1 /> 通知する</label>
      <label class="radio-inline"><input type="radio" name="qhmsetting[notify]" value="0" $notify_checked2 /> 通知しない</label>
    </div>
  </div>
  <div class="form-group">
    <label for="" class="control-label col-sm-4">差分のみ送信</label>
    <div class="col-sm-8">
  		<label class="radio-inline"><input type="radio" name="qhmsetting[notify_diff_only]" value="1" $notify_diff_only_checked1 /> 差分のみ</label>
  		<label class="radio-inline"><input type="radio" name="qhmsetting[notify_diff_only]" value="0" $notify_diff_only_checked2 /> 全文</label>
    </div>
  </div>
  <div class="form-group">
    <label for="" class="control-label col-sm-4">送信者メールアドレス</label>
    <div class="col-sm-8">
      <input type="text" name="qhmsetting[notify_from]" value="{$params['notify_from']}" class="form-control">
    </div>
  </div>
  <div class="form-group">
    <label for="" class="control-label col-sm-4">更新通知先</label>
    <div class="col-sm-8">
      <input type="text" name="qhmsetting[notify_to]" value="{$params['notify_to']}" class="form-control">
    </div>
  </div>

<ul class="list1">
	<li><b>個人で使っている場合</b><br />
	毎回メールで通知されると面倒なので、「通知しない」がお勧めです
	</li>
	<li><b>社内情報共有の場合</b><br />
	通知機能をオンにして、通知先をメーリングリストに設定すると便利です
	</li>
</ul>

<br>

<h4>オプション</h4>
<p>メール送信サーバー(SMTP)の設定を行います。<br />
※ 通常、QHM自体がメールを送信できるため、以下の設定を行う必要はありません</p>

  <div class="form-group">
    <label for="" class="control-label col-sm-4">SMTPサーバー</label>
    <div class="col-sm-8">
      <input type="text" name="qhmsetting[smtp_server]" value="{$params['smtp_server']}" class="form-control">
    </div>
  </div>
  <div class="form-group">
    <label for="" class="control-label col-sm-4">SMTP AUTH</label>
    <div class="col-sm-8">
      <label class="radio-inline"><input type="radio" name="qhmsetting[smtp_auth]" value="1" {$smtp_auth_checked1}> 利用する</label>
      <label class="radio-inline"><input type="radio" name="qhmsetting[smtp_auth]" value="0" {$smtp_auth_checked2}>利用しない</label>
    </div>
  </div>
  <div class="form-group">
    <label for="" class="control-label col-sm-4">POPサーバー</label>
    <div class="col-sm-8">
      <input type="text" name="qhmsetting[pop_server]" value="{$params['pop_server']}" class="form-control">
    </div>
  </div>
  <div class="form-group">
    <label for="" class="control-label col-sm-4">POPアカウント</label>
    <div class="col-sm-8">
      <input type="text" name="qhmsetting[pop_userid]" value="{$params['pop_userid']}" class="form-control">
    </div>
  </div>
  <div class="form-group">
    <label for="" class="control-label col-sm-4">POPパスワード</label>
    <div class="col-sm-8">
      <input type="password" name="qhmsetting[pop_passwd]" value="{$params['pop_passwd']}" class="form-control">
    </div>
  </div>

<br>

<h3>Google Apps利用者</h3>
<p>GoogleAppsを導入している方で、自動返信メールの投稿内容、ダウンロード通知など、<a href="http://www.google.com/support/a/bin/answer.py?answer=55299&topic=14868" target="new">「自分のアカウントにメールを送ると届かない」（参照：GoogleAppsヘルプ）</a>場合、以下を設定して下さい。</p>


  <div class="form-group">
    <label for="" class="control-label col-sm-5">GoogleApps宛にメールを送る</label>
    <div class="col-sm-7">
      <label class="radio-inline"><input type="radio" name="qhmsetting[google_apps]" value="1" {$google_apps_checked1} /> 利用する</label>
      <label class="radio-inline"><input type="radio" name="qhmsetting[google_apps]" value="0" {$google_apps_checked2} /> 利用しない</label>
    </div>
  </div>
  <div class="form-group">
    <label for="" class="control-label col-sm-5">メールのドメイン</label>
    <div class="col-sm-7">
      <input type="text" name="qhmsetting[google_apps_domain]" value="{$params['google_apps_domain']}" class="form-control">
    </div>
  </div>

<br>

<h3>メール送信宛先設定</h3>
<p>通常、メールを送信する場合、「相手の名前 &lt;hogehoge@hoge.com&gt;」として送ります。<br />
しかし、一部のサーバーでは、相手の名前を含めるとメールが送れない場合があります。<br />
特殊なサーバーをお使いの場合、以下で「メールアドレスのみ」を使う設定を行って下さい。</p>

  <div class="form-group">
    <label class="control-label col-sm-5">メールアドレスのみで送信</label>
		<div class="col-sm-7">
		  <label class="radio-inline"><input type="radio" name="qhmsetting[exclude_to_name]" value="1" {$exclude_to_name1} /> 利用する</label>
		  <label class="radio-inline"><input type="radio" name="qhmsetting[exclude_to_name]" value="0" {$exclude_to_name2} /> 利用しない(推奨)</label>
    </div>
  </div>

<br />

<h3>メールエンコードの設定</h3>
<p>メールのエンコードを設定します。<br />
日本語以外の言語を利用したい方は、UTF-8 を指定してください。
</p>

  <div class="form-group">
    <label class="control-label col-sm-5">メールエンコードの設定</label>
		<div class="col-sm-7">
		  <label class="radio"><input type="radio" name="qhmsetting[mail_encode]" value="ISO-2022-JP" {$mail_encode_checked1}> ISO-2022-JP</label>
		  <label class="radio"><input type="radio" name="qhmsetting[mail_encode]" value="UTF-8" {$mail_encode_checked2}> UTF-8</label>
    </div>
  </div>

<br />

<h3>送信メールサーバー（MTA）の設定</h3>
<p>サーバーの送信メールサーバー（MTA）が qmail に設定されている場合、この設定を有効にしてください。<br />
qmailではメールの件名が長い場合に、件名が途中で切れ、メール本文にヘッダー情報が表示される場合があります。<br />
qmail以外（sendmail）などの場合は「無効」に設定してください。<br />
多くのサーバーは、sendmailを利用していますので「無効」で結構です。</p>

  <div class="form-group">
    <label class="control-label col-sm-5">送信メールサーバーの設定</label>
		<div class="col-sm-7">
		  <label class="radio-inline"><input type="radio" name="qhmsetting[is_qmail]" value="1" {$is_qmail1} /> 有効</label>
		  <label class="radio-inline"><input type="radio" name="qhmsetting[is_qmail]" value="0" {$is_qmail2} /> 無効</label>
    </div>
  </div>

<br />

  <div class="form-group">
    <div class="col-sm-8 col-sm-offset-4">
      <input type="submit" value="設定を確認する" class="btn btn-primary" />
    </div>
  </div>
  <input type="hidden" name="phase" value="mail" />
  <input type="hidden" name="mode" value="confirm" />
  <input type="hidden" name="plugin" value="qhmsetting" />
  <input type="hidden" name="from" value="mail_form" />
</form>


EOD;

	return $body;
}

function plugin_qhmsetting_mail_confirm()
{
	global $vars, $script;

	// --------------------------------------------
	// 直接のアクセスを拒否する
	if( !isset($vars['from']) || $vars['from']!='mail_form' )
	{
		return 'このページへの直接アクセスは、無効です。';
	}


	// -----------------------------------
	// validation check
	// -----------------------------------

	foreach($vars['qhmsetting'] as $key => $val)
	{
		$_SESSION['qhmsetting'][$key] = $val;
	}
//	var_dump($_SESSION['qhmsetting']);

	$error = '';

	//本当は、もっとややこしくないと正しくないらしい
	$email_match = '/^([a-z0-9_]|\-|\.|\+)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,6}$/i';
	$hankaku = '/^[a-z0-9_\-\.\+@]+$/i';

	if($vars['qhmsetting']['notify']==1)
	{
		if(! preg_match($email_match, $vars['qhmsetting']['notify_from']) )
			$error .= '送信者のメールアドレスを正しく、入力してください<br />';
		if(! preg_match($email_match, $vars['qhmsetting']['notify_to']) )
			$error .= '更新通知先を正しく、設定して下さい。<br />';


		if(! preg_match($hankaku, $vars['qhmsetting']['smtp_server']) )
			$error .= 'SMTPサーバーを設定して下さい';

		if( $vars['qhmsetting']['smtp_auth'] )
		{
			if(! preg_match($hankaku, $vars['qhmsetting']['pop_server'] ) )
				$error .= 'POPサーバーを正しく、設定して下さい。<br />';
			if(! preg_match($hankaku, $vars['qhmsetting']['pop_userid'] ) )
				$error .= 'POPアカウントを入力してください。<br />';
			if(! preg_match($hankaku, $vars['qhmsetting']['pop_passwd'] ) )
				$error .= 'POPパスワードを入力してください。<br />';
		}
	}

	if( $vars['qhmsetting']['google_apps'] && (! preg_match($hankaku, $vars['qhmsetting']['google_apps_domain']) ) )
	{
		$error .= 'ドメインを正しく設定して下さい。';
	}

	if($error != '')
		return plugin_qhmsetting_mail_form($error);




	// -----------------------------------
	// process from here
	// -----------------------------------

	$notify_msg = '';
	if($vars['qhmsetting']['notify'])
	{
		$notify_msg = "利用する<br />(送り先:{$vars['qhmsetting']['notify_to']}、";
		if($notify_diff_only)
			$notify_msg .= ' 内容:差分のみ)';
		else
			$notify_msg .= ' 内容:全文)';
	}
	else
	{
		$notify_msg = '利用しない';
	}

	$notify_name_msg = $vars['qhmsetting']['notify_from'];

	$smtp_auth_msg = '利用しない';
	if($vars['qhmsetting']['smtp_auth'])
	{
		$smtp_auth_msg = 'サーバー: '.$vars['qhmsetting']['pop_server'].'<br />';
		$smtp_auth_msg .= 'POPユーザー: '.$vars['qhmsetting']['pop_userid'].'<br />';
		$smtp_auth_msg .= 'POPパスワード: ********<br />';
	}

	$google_apps_msg = '設定しない';
	if($vars['qhmsetting']['google_apps'])
	{
		$google_apps_msg = '利用する<br />'.$vars['qhmsetting']['google_apps_domain'];
	}

	$exclude_to_name_msg = '通常送信(名前+メールアドレス)';
	if($vars['qhmsetting']['exclude_to_name'])
	{
		$exclude_to_name_msg = '<span style="color:red">メールアドレスのみで送る</span>';
	}

	$is_qmail_msg = '無効';
	if($vars['qhmsetting']['is_qmail'])
	{
		$is_qmail_msg = '有効';
	}

	$mail_encode_msg = $vars['qhmsetting']['mail_encode'];


	$hiddens = '';
	foreach($vars['qhmsetting'] as $key=>$val )
	{
		$hiddens .= '<input type="hidden" name="'.$key.'" value="'.$val.'" />'."\n";
	}


	$body = <<<EOD
<h2>メール設定の確認</h2>
<table class="table table-bordered">
	<tr>
		<th>更新通知</th>
		<td>{$notify_msg}</td>
	</tr>
	<tr>
		<th>メール送信者設定</th>
		<td>{$notify_name_msg}</td>
	</tr>
	<tr>
		<th>メール送信サーバー</th>
		<td>{$vars['qhmsetting']['smtp_server']}</td>
	</tr>
	<tr>
		<th>SMTP Auth<br />pop before smtp</th>
		<td>{$smtp_auth_msg}</td>
	</tr>
	<tr>
		<th>GoogleApps設定</th>
		<td>{$google_apps_msg}</td>
	</tr>
	<tr>
		<th>メール送信宛先設定</th>
		<td>{$exclude_to_name_msg}</td>
	</tr>
	<tr>
		<th>メールエンコードの設定</th>
		<td>{$mail_encode_msg}</td>
	</tr>
	<tr>
		<th>送信メールサーバーの設定</th>
		<td>{$is_qmail_msg}</td>
	</tr>
</table>

<form method="post" action="{$script}">
<p style="text-align:center"><input type="button" name="back" value="戻る" onclick="history.back();" class="btn btn-default" /> <input type="submit" value="設定する" class="btn btn-primary" /></p>
<input type="hidden" name="phase" value="mail" />
<input type="hidden" name="mode" value="msg" />
<input type="hidden" name="plugin" value="qhmsetting" />
<input type="hidden" name="from" value="mail_form" />
{$hiddens}

</form>

EOD;

	return $body;
}

function plugin_qhmsetting_mail_msg()
{

	global $vars, $script;

	// --------------------------------------------
	// 直接のアクセスを拒否する
	if( !isset($vars['from']) || $vars['from']!='mail_form' )
	{
		return 'このページへの直接アクセスは、無効です。';
	}


	//set session
	foreach( $vars['qhmsetting'] as $key=>$val)
	{
		$_SESSION['qhmsetting'][$key]
			= $vars['qhmsetting'][$key]
			= htmlspecialchars($val);
	}

	plugin_qhmsetting_update_ini();

	$message = <<<EOD
メール送信設定を完了しました。
自動返信メールフォームなどを利用して、テストを行って下さい。

EOD;

	redirect($script . '?cmd=qhmsetting', $message);
	exit;

}
//--</MailForm>--



//--<UserForm>--
/**
* ユーザー権限設定
*/
function plugin_qhmsetting_user_form($error = '')
{
	global $custom_meta, $script;
	global $other_plugins;
	$hlp_useradmin = '';

	$custom_meta .= '<script type="text/javascript" src="./js/jquery.js"></script>
<script type="text/javascript" src="./js/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="./js/opadmin.js"></script>
<script type="text/javascript">
$(document).ready(function()
    {
        $("#patternlist").tablesorter();
    }
);
</script>
<style type="text/css">
/* tables */
table.tablesorter {
	font-family:arial;
	background-color: #CDCDCD;
	margin:10px 0pt 15px;
	font-size: 8pt;
	width: 100%;
	text-align: left;
}
table.tablesorter thead tr th, table.tablesorter tfoot tr th {
	background-color: #e6EEEE;
	border: 1px solid #FFF;
	font-size: 8pt;
	padding: 4px;
}
table.tablesorter thead tr .header {
	background-image: url(image/hokuken/bg.gif);
	background-repeat: no-repeat;
	background-position: center right;
	cursor: pointer;
}
table.tablesorter tbody td {
	color: #3D3D3D;
	padding: 4px;
	background-color: #FFF;
	vertical-align: top;
}
table.tablesorter tbody tr.odd td {
	background-color:#F0F0F6;
}
table.tablesorter thead tr .headerSortUp {
	background-image: url(image/hokuken/asc.gif);
}
table.tablesorter thead tr .headerSortDown {
	background-image: url(image/hokuken/desc.gif);
}
table.tablesorter thead tr .headerSortDown, table.tablesorter thead tr .headerSortUp {
background-color: #8dbdd8;
}
.admin input[type=submit], .admin input[type=button] {
font-size:12px;
padding: 4px 3px;
}
</style>
';
	$back_url = '';

	$users_data = _get_users_data();
	$access_data = null;

	//user accessの取得
	$fp = fopen('qhm_access.ini.txt', "r");
	if ($fp) {
		flock( $fp, LOCK_SH );

		while (!feof($fp)) {
			$line = fgets($fp);
			if (trim($line) != "") {
				list($type,$pattern,$name) = explode(',', $line);
				$access_data[] = array("type"=>trim($type), "pattern"=>$pattern, "user"=>trim($name));
			}
		}
		fclose($fp);
	}

	$body = <<<EOD
$back_url
<h2>アクセス権限設定{$hlp_useradmin}</h2>
<p style="color:red">{$error}</p>
<form method="post" action="{$script}" id="frm_users" name="frm_users">
<fieldset>
<legend style="color:#666;padding:0px 5px">ユーザーの設定</legend>
<table class="table table-bordered" cellspacing="5" cellpadding="0" border="0"><tbody>
<tr>
<td class="style_td" style="width:300px;padding:5px 5px 0px 5px;" valign="top">
<table class="style_table" align="center" style="width:100%;font-size:12px;" cellspacing="1" cellpadding="0" border="0"><tbody>
  <tr>
  <th class="style_th">ユーザ名</th><td class="style_td" width="100"><input type="text" name="qhmsetting[username]" id="username" value="" style="width:90px" /></td>
  </tr>
  <tr>
  <th class="style_th">パスワード</th><td class="style_td" width="100"><input type="password" name="qhmsetting[passwd]" id="passwd" value="" style="width:90px" /></td>
  </tr>
  <tr>
  <th class="style_th">パスワード<br>再入力</th><td class="style_td" width="100"><input type="password" name="qhmsetting[repasswd]" id="repasswd" value="" style="width:90px" /></td>
  </tr>
</tbody></table>
</td>
<td width="70" valign="top" style="padding-top:40px;"><input type="submit" name="user_add" value="追　加 ->" style="width:90px;" class="btn btn-primary" />
<!-- plugin用のhidden parameter -->
<input type="hidden" name="phase" value="user" />
<input type="hidden" name="mode" value="msg" />
<input type="hidden" name="plugin" value="qhmsetting" />
</td>
<td style="width:370px;padding:5px;background-color:#fff;" valign="top">
<table class="style_th" align="center" style="width:100%;font-size:12px;color:#333;"><tbody>
<tr><td height="20" class="style_td_title" style="padding:1px 3px;">ユーザーリスト</td></tr>
EOD;

	if (is_array($users_data)) {

		ksort($users_data);

		foreach ($users_data as $key=>$row) {
			$body .= <<<EOD
	<tr><td class="style_td" style="padding:1px 3px;" onclick="this.style.backgroundColor='skyblue';selectUser('target_user','{$key}');" onmouseover="hightlight(this,'pink');" onmouseout="hightlight(this,'#EFEFF1');"><div>{$key} <input type="button" onclick="rewritePasswd('{$key}');" value="パスワード変更" class="btn btn-primary"  /> <input type="button" onclick="deleteUser('{$key}');" value="削除" class="btn btn-danger" /></div></td></tr>
EOD;
		}
	}

	$body .= <<<EOD
</tbody></table>
</td>
</tr>
<tr>
  <td height="20" colspan="4" style="padding:5px;color:red;font-size:12px;" align="right">※ パスワードの変更、ユーザの削除は、リストからユーザーを選択してください
  <input type="hidden" name="target_user" id="target_user" value="" />
  <input type="hidden" name="op_passwd" value="" />
  <input type="hidden" name="user_op" value="" />

  </td></tr>
</tbody></table>
</fieldset>
<fieldset>
<legend style="color:#666;padding:0px 5px;">アクセス権限の設定</legend>

<p>アクセス権限の追加</p>
<table class="table table-bordered" width="100%" style="margin:5px;" cellspacing="1" cellpadding="0" border="0"><tbody>
<tr height="20">
  <th class="style_th" height="20" style="text-align:center;margin:5px auto;font-size:8pt;">ページ名のパターン</th>
  <th class="style_th" height="20" style="text-align:center;margin:5px auto;font-size:8pt;">ユーザー名</th>
  <th class="style_th" height="20" style="text-align:center;margin:5px auto;font-size:8pt;">アクセス権限</th>
  <th class="style_th" valign="center" width="80" style="text-align:center;margin:5px auto;font-size:8pt;" rowspan="2"><input type="submit" name="add_access" value="追　加" style="width:60px" class="btn btn-primary" /></th>
</tr>
<tr height="40">
  <td class="style_td" height="40" width="350"><select name="pattern_pos"><option value="all">完全</option><option value="front">前方</option><option value="back">後方</option><option value="part">部分</option><option value="other">その他</option></select> <input type="text" name="pattern" id="pattern" value="" style="width:200px" /></td>
  <td class="style_td" height="40" width="100">
  <select name="access_user">

EOD;

	foreach ($users_data as $key => $val) {
		$body .=  '<option value="'.$key.'">'.$key.'</option>';
	}

	$body .= <<<EOD
  </select></td>
  <td class="style_td" height="40" width="150"><input type="radio" name="type" value="r" checked="checked" /> 閲覧制限<br /><input type="radio" name="type" value="e" /> 編集許可</td>
</tr>
</tbody>
</table>
<p>アクセス権限のリスト</p>
<!-- <p><span style="color:gray;font-size:12px">※ 項目をクリックすると、ソートできます。Shiftを押しながらクリックすると、マルチソートできます</span></p> -->
<table id="patternlist" class="tablesorter table table-bordered" width="100%" style="margin:5px 5px 5px 5px;" cellspacing="1" cellpadding="0" border="0">
<thead>
<tr>
  <th class="style_th" height="20" style="text-align:center;margin:5px auto">ページ名のパターン</th>
  <th class="style_th" height="20" style="text-align:center;margin:5px auto">ユーザ名</th>
  <th class="style_th" height="20" width="90" style="text-align:center;margin:5px auto">アクセス権限</th>
  <th class="style_th" height="20" style="text-align:center;margin:5px auto">操　作</th>
</tr>
</thead>
<tbody>
EOD;

	if (is_array($access_data)) {
		$row_cnt = 0;
		foreach ($access_data as $key=>$row) {
			$type_name = _get_type_name($row["type"]);
			$body .= <<<EOD
<tr>
  <td class="style_td" height="20">{$row["pattern"]}</td>
  <td class="style_td" height="20" width="140">{$row["user"]}</td>
  <td class="style_td" height="20" width="90">{$type_name}</td>
  <td class="style_td" height="20" width="80" style="text-align:center;margin:5px auto"><input type="button" onclick="deletePattern('{$row["user"]}','{$row_cnt}');" style="width:60px" value="削　除" class="btn btn-danger" /></td>
</tr>
EOD;
			$row_cnt++;
		}
	}

	$body .= <<<EOD
</tbody>
</table>
</fieldset>
<input type="hidden" name="pattern_op" value="" /><input type="hidden" name="delno" id="delno" value="" />
</form>

EOD;

	return $body;

}

function plugin_qhmsetting_user_msg()
{
	global $vars, $script;
	$msg = '';

	// writable check
	if( !is_writable(PLUGIN_QHMSETTING_USER_INI_FILE) )
	{
		return '<h2>エラー</h2><p>qhm_users.ini.txt の書き込み権限がありません。</p>';
	}
	// writable check
	if( !is_writable(PLUGIN_QHMSETTING_ACCESS_INI_FILE) )
	{
		return '<h2>エラー</h2><p>qhm_access.ini.txt の書き込み権限がありません。</p>';
	}


	// ------------------------------------
	// add user
	if( isset($vars['user_add']) )
	{
		$error = _check_userdata($vars['qhmsetting']);
		if( $error != '' )
			return plugin_qhmsetting_user_form($error);

		$data = $vars['qhmsetting']['username']
				.',{x-php-md5}'.md5($vars['qhmsetting']['passwd'])."\n";

		_write_userfile($data, "a");


		$msg = "ユーザーを追加しました。";
	}


	// -------------------------------------
	// del user
	if( $vars['user_op'] == 'delete_user')
	{
		$users_data = _get_users_data();
		$user = $vars['target_user'];

		if( isset($users_data[$user]) )
		{

			$acclist = _get_accessdata();

			$dat = '';
			foreach($acclist as $k=>$acc){
				if($acc['user']===$user){
					//do nothing
				}
				else{
					$dat .= $acc['type'].','.$acc['pattern'].','.$acc['user']."\n";
				}
			}
			_write_accessfile($dat, 'w');


			unset($users_data[$user]);
			$data = '';
			foreach($users_data as $key=>$value)
			{
				$data .= $key.','.$value['passwd']."\n";
			}

			_write_userfile($data, "w");


		}

		$msg = "ユーザーを削除しました。";
	}


	// --------------------------------------
	// reset passwd
	if( $vars['user_op'] == 'rewrite_password' )
	{
		$msg = "ユーザーパスワードを設定しました";
		$passwd = $vars['op_passwd'];

		//error
		$error = '';

		if( $passwd == ''){
			$error = 'パスワードなしは、設定できません。';
		}
		else if( !preg_match("/^[a-zA-Z0-9]+$/",$passwd) ){
			$error = 'パスワードは、半角英数を入力してください';
		}

		if($error!=''){
			return plugin_qhmsetting_user_form($error);
		}


		$usr = $vars['target_user'];
		$pw = '{x-php-md5}'.md5($vars['op_passwd']);

		$users_data = _get_users_data();

		if( isset( $users_data[$usr] ) )
		{
			$users_data[$usr]['passwd'] = $pw;
		}

		$data = '';
		foreach($users_data as $key=>$value)
		{
			$data .= $key.','.$value['passwd']."\n";
		}

		_write_userfile($data, "w");
	}

	// -------------------------------------------
	// 権限変更
	//
	if( isset( $vars['add_access']  ) )
	{

		$type =  input_filter($vars['type']);
		$pattern = _get_pregdata($vars["pattern"], $vars["pattern_pos"]);
		$user = input_filter($vars['access_user']);

		$data = $type.",".$pattern.",".$user."\n";
		_write_accessfile($data, "a");

		$msg = '権限を追加しました。';

	}


	if( $vars['pattern_op'] == 'delete_pattern' )
	{
		//データ作成
		$acclist = _get_accessdata();
		unset($acclist[ $vars['delno']  ]);

		$data = '';
		foreach($acclist as $key=>$value)
		{
			$data .= $value['type'].','.$value['pattern'].','.$value['user']."\n";
		}

		_write_accessfile($data,"w");

		$msg = 'アクセス権限を削除しました。';
	}

	//----------------------------------------------
	// 終了処理

	return plugin_qhmsetting_user_form($msg);
}
//--</UserForm>--


function plugin_qhmsetting_user2_form($error = '')
{
	global $custom_meta, $script;
	$custom_meta .= '<script type="text/javascript" src="./js/opadmin.js"></script>';

	if(! isset($_SESSION['usr']) ){
		return '<p><a href="'.$script.'?cmd=qhmauth">ログイン</a>してください。</p>';
	}

	$body = <<<EOD
<h2>パスワード変更</h2>
<p style="color:red">{$error}</p>
<form method="post" action="{$script}" id="frm_users" name="frm_users">

<table class="style_table" cellspacing="1" border="0">
	<tr>
		<th class="style_th">　ユーザー名　</th>
		<td class="style_td">　{$_SESSION['usr']}　</td>
	</tr>
	<tr>
		<th class="style_th">　現在のパスワード　</th>
		<td class="style_td">　<input type="password" name="op_passwd" size="18" /></td>
	</tr>
	<tr>
		<th class="style_th">　新パスワード　</th>
		<td class="style_td">　<input type="password" name="op_passwd1" size="18" />　<br />
		　<input type="password" name="op_passwd2" size="18"  />　<br />
		確認のため、２度入力してください</td>
	</tr>
</table>
<p style="text-align:center"><input type="submit" name="resetpasswd" value="変更" class="btn btn-primary" /> <input type="button" name="back" value="戻る" onclick="location.href='{$script}'" class="btn" /></p>
<input type="hidden" name="plugin" value="qhmsetting" />
<input type="hidden" name="phase" value="user2" />
<input type="hidden" name="mode" value="msg" />
</form>
<br />
EOD;

	return $body;
}

function plugin_qhmsetting_user2_msg()
{
	global $vars, $script, $auth_users;
	$msg = '';

	// writable check
	if( !is_writable(PLUGIN_QHMSETTING_USER_INI_FILE) )
	{
		return '<h2>エラー</h2><p>qhm_users.ini.txt の書き込み権限がありません。</p>';
	}
	// writable check
	if( !is_writable(PLUGIN_QHMSETTING_ACCESS_INI_FILE) )
	{
		return '<h2>エラー</h2><p>qhm_access.ini.txt の書き込み権限がありません。</p>';
	}

	// --------------------------------------
	// reset passwd
	$msg = "ユーザーパスワードを設定しました";
	$passwd  = $vars['op_passwd'];
	$passwd1 = $vars['op_passwd1'];
	$passwd2 = $vars['op_passwd2'];

	//error
	$error = '';
	if( $auth_users[ $_SESSION['usr'] ] != '{x-php-md5}'.md5($passwd) ){
		$error = '現在のパスワードが一致しません。<br />';
	}
	if( $passwd1 == ''){
		$error .= 'パスワードなしは、設定できません。<br />';
	}
	else if( $passwd1 !== $passwd2){
		$error .= 'パスワードが違います<br />';
	}
	else if( !preg_match(PLUGIN_QHMSETTING_ALLOW_PASSWD_PATTERN, $passwd1) ){
		$error .= 'パスワードは、英数半角と一部の記号のみ(スペース不可)で入力してください<br />';
	}
	else if( strlen($passwd1) < 6 ){
		$error .= 'パスワードが短すぎます(6文字以上)<br />';
	}

	if($error!=''){
		return plugin_qhmsetting_user2_form($error);
	}


	$usr = $_SESSION['usr'];
	$pw = '{x-php-md5}'.md5($passwd1);

	$users_data = _get_users_data();

	if( isset( $users_data[$usr] ) )
	{
		$users_data[$usr]['passwd'] = $pw;
	}

	$data = '';
	foreach($users_data as $key=>$value)
	{
		$data .= $key.','.$value['passwd']."\n";
	}

	_write_userfile($data, "w");

	$msg = '変更しました';
	return plugin_qhmsetting_user2_form($msg);
}


//--<ChmodForm>--
function plugin_qhmsetting_chmod_form($error = '')
{
	global $custom_meta, $script;
	global $other_plugins;
	$hlp_chmod = '';

	$back_url = '';

	$dirs = array('attach','backup','cache','cacheqhm','cacheqblog','counter','diff','trackback','wiki');
	$files = array('qhm.ini.php', 'qhm_access.ini.txt', 'qhm_users.ini.txt');



	//書き込み権限のないファイル、apacheのみ書き込みファイル探し
	$not_writable = array();
	$not_writable_list = '';

	$web_file = array();
	$web_file_list = '';

	//その他警告
	$warns = array();
	$warns_list = '';

	foreach($dirs as $dir)
	{
		if (is_dir($dir))
		{
			$obj = dir($dir);
			while( ($file=$obj->read()) )
			{
				$path = $dir.'/'.$file;

				if( !is_writable($path) && ($file!='..')
						&& ($file!='.htaccess') && ($file!='.htpasswd') && ($file!='index.html') )
				{
					$not_writable[] = $path;
					$not_writable_list .= '<li>'.$path.'</li>'."\n";
				}

				if( is_writable($path) && ($file!='..')
						&& ($file!='.htaccess') && ($file!='.htpasswd') && ($file!='index.html') )
				{
					$perms = substr(sprintf('%o', fileperms($path)), -4);
					if($perms == '0644')
					{
						$web_file[] = $path;
						$web_file_list .= '<li>'.$path.'</li>';
					}
				}
			}
			$obj->close();
		}
		else
		{
			$warns = array($dir);
			$warns_list .= '<li>フォルダが見つかりません：'. h($dir) .'<br /><span style="font-size:small;color:#666;">権限777でフォルダ '. h($dir) .' を作成してください。</span></li>'. "\n";
		}
	}

	foreach($files as $path)
	{
		if( !is_writable($path) )
		{
			$not_writable[] = $path;
			$not_writable_list .= '<li>'.$path.'</li>'."\n";
		}

		if( is_writable($path) )
		{
			$perms = substr(sprintf('%o', fileperms($path)), -4);
			if($perms == '0644')
			{
				$web_file[] = $path;
				$web_file_list .= '<li>'.$path.'</li>';
			}
		}
	}


	$not_writable_list = ($not_writable_list=='') ?
		'' : '<ul>'.$not_writable_list.'</ul>';

	if($web_file_list!='')
	{
		$web_file_list = '<ul>'.$web_file_list.'</ul>';
		$web_file_list .= '<form method="post" action="'.$script.'">';

		$cnt = 0;
		foreach($web_file as $file){
			$web_file_list .= '<input type="hidden" name="webfile['.$cnt.']" value="'.$file.'" />'."\n";
			$cnt++;
		}
		$web_file_list .= '<p style="text-align:center;"><input type="submit" name="chmod" value="権限変更を実行する" class="btn btn-primary" /></p><input type="hidden" name="phase" value="chmod" />
<input type="hidden" name="mode" value="msg" />
<input type="hidden" name="plugin" value="qhmsetting" />';
		$web_file_list .= '</form>';

	}

	//apacheしか書けないファイル探し

	if ($not_writable_list == '') {
		$not_writable_list = '<div style="padding:5px;width:350px;background-color:#ddeeff;border:2px solid #6699CC;text-align:center;margin:1em auto;">書き込み権限に問題はありませんでした</div>';
	}
	else {
		$not_writable_list = '<p>以下のファイルは、QHMによって利用するにも関わらず、書き込みができません。<br />
FTPソフトなどを使って、書き込みを権限を設定して下さい</p>'.$not_writable_list;
	}

	if ($web_file_list == '') {
		$web_file_list = '<div style="padding:5px;width:350px;background-color:#ddeeff;border:2px solid #6699CC;text-align:center;margin:1em auto;">Webサーバーの書き込みに問題はありませんでした</div>';
	}
	else {
		$web_file_list = '<p>PHPの仕様上、PHPプログラムが作成したファイルは、webサーバー(apache)のみが、<br />
書き込み可能のファイルが作成されます。</p>
<p>使う上で問題はありませんが、削除できないファイルなどがある場合は、<b>権限変更</b>を<br />
実行することで、削除や変更、FTPによる上書きが可能になります。</p>'.$web_file_list;
	}

	//その他警告
	if ($warns_list != '')
	{
		$warns_dscr = '<p>ファイル・フォルダに関するいくつかの警告があります。</p>';
		$warns_list = $warns_dscr . "\n" . '<ul>'. $warns_list. '</ul>';
	}


	$body = <<<EOD
{$back_url}
<h2>権限チェック{$hlp_chmod}</h2>
<h3>書き込み権限がないファイル</h3>
{$not_writable_list}
<br />
<h3>Webサーバーのみ書き込み可能</h3>
{$web_file_list}
<br />
<h3>その他警告</h3>
{$warns_list}
EOD;


	return $body;
}

function plugin_qhmsetting_chmod_msg()
{
	global $vars, $script;

	$list = '<ul>';
	foreach($vars['webfile'] as $key=>$name)
	{
		chmod($name, 0666);
		$list .= '<li>'.$name."</li>\n";
	}
	$list .= '</ul>';

	$back_url = '<a href="'.$script.'?plugin=qhmsetting&amp;phase=chmod&amp;mode=form">戻る</a>';

	$body = <<<EOD
<p>$back_url</p>
<h2>完了</h2>
<p>以下のファイルの権限を変更しました。</p>
{$list}
<p>$back_url</p>
EOD;

	return $body;

}
//--</ChmodForm>--

function plugin_qhmsetting_counter_form($error='')
{
	global $script, $vars;
	global $other_plugins;
	$hlp_counter  = '';

	//reset
	$message = '';
	if( isset($vars['reset']) )
	{
		$page = decode(basename($vars['reset'], '.count'));
		$message =  h($page) . 'のアクセスカウントをリセットしました。';
		$_SESSION['flash_msg'] = $message;
		file_put_contents('counter/'.$vars['reset'],'');
		redirect($script . '?cmd=qhmsetting&phase=counter&mode=form');
		exit;
	}


	$list = get_existpages('counter','.count');

	$body = '
<style type="text/css">
ul {
list-style-type:none;
padding-left: 0;
}
ul li {
margin-bottom: 4px;
}
.admin input[type=button] {
background-color: #2981e4;
font-size: 12px;
padding: 4px 3px;
}
.admin input[type=button]:hover {
background-color: #2575cf;
}
</style>
';
	$body .= '<ul>';

	foreach($list as $fname=>$pname)
	{
		$url = $script.'?'.rawurlencode($pname);
		$reset_url = $script.'?cmd=qhmsetting&amp;phase=counter&amp;mode=form&amp;reset='
			. rawurlencode($fname);

		$body .= '<li><input type="button" value="リセット" onclick="javascript:location.href=\''
			.$reset_url.'\'" class="btn btn-primary" ><a href="'.$url.'"> '.$pname.
			'</a> </li>'."\n";
	}

	$body .= '</ul>';

	return $body;
}

function plugin_qhmsetting_clear_form($error = '')
{
	global $custom_meta, $script;
	global $other_plugins;
	$qt = get_qt();

	$hlp_clear = '';

    $files = array();
    $search_files = array();
    $files_cache = array();
    if ($dir = opendir(CACHE_DIR))
    {
        while (($file = readdir($dir)) !== false)
        {
            if (preg_match('/\.qtc$/', $file))
            {
                $files[] = $file;
            }
            else if (preg_match('/_search\.txt$/', $file))
            {
                $search_files[] = $file;
            }
        }
        closedir($dir);
    }

	// キャッシュ設定
	if($qt->enable_cache){
		$checked_cache = 'checked="checked"';
	}
	else{
		$checked_cache = '';
	}

	$body = <<< EOD
<h2>高速化設定、キャッシュ、テンプレートの初期化{$hlp_clear}</h2>
<p>高速化、表示に関する設定を行います。</p>
<h3>高速化設定（キャッシュ機能の有効化）</h3>
<p>QHMの表示をキャッシュ化して「高速化」することができます。<br />
※一部のプラグインを使ったページはキャッシュできません</p>
<form action="{$script}" method="post">
<p><label><input type="checkbox" {$checked_cache} name="enable_cache" /> 高速化設定を有効にする</label></p>
<input type="submit" name="cache_setting" value="設定する" class="btn btn-primary" />
<input type="hidden" name="phase" value="clear" />
<input type="hidden" name="mode" value="msg" />
<input type="hidden" name="cmd" value="qhmsetting" />
</form>
<h3 style="margin-top:2em;">高速化キャッシュの削除</h3>
<form action="{$script}" method="post">
<p>高速化のための全ページをキャッシュを初期化します。更新したページが、更新されない、正常に動作しないときに実行してください。</p>
<input type="submit" name="cache_del" value="キャッシュの初期化を実行する" class="btn btn-primary" />
<input type="hidden" name="phase" value="clear" />
<input type="hidden" name="mode" value="msg" />
<input type="hidden" name="cmd" value="qhmsetting" />
</form>

<h3 style="margin-top:2em;">テンプレートキャッシュの初期化</h3>
<p>テンプレートキャッシュを削除します。<br />
削除を行うことで、ページ全体のレイアウトを再構築できます。</p>
<form action="{$script}" method="post">
<ul>
EOD;
	$cnt = 0;
	foreach($files as $file){
		$rm_file = CACHE_DIR.$file;
		$body .= "<li>{$rm_file}<input type=\"hidden\" name=\"rm[{$cnt}]\" value=\"{$rm_file}\" /></li>\n";
		$cnt ++;
	}
	$body .= <<< EOD
</ul>
<p><input type="submit" name="clear" value="削除を実行する" class="btn btn-primary" /></p>
<input type="hidden" name="tmp_del" value="tmp_del" />
<input type="hidden" name="phase" value="clear" />
<input type="hidden" name="mode" value="msg" />
<input type="hidden" name="cmd" value="qhmsetting" />
</form>
EOD;

    if (count($search_files) > 0)
    {
        $body .= <<< EOD
<h3 style="margin-top:2em;">検索用キャッシュの初期化</h3>
<p>search2 プラグインで使う検索用キャッシュを削除します。<br />
削除を行うことで、検索時に使うキャッシュファイルを再構築できます。</p>
<form action="{$script}" method="post">
<ul>
EOD;
        $cnt = 0;
        foreach($search_files as $file){
            $rm_file = CACHE_DIR.$file;
            $open_url = $script . '?cmd=qhmsetting&phase=clear&mode=view_search2_cache&file='.rawurlencode($rm_file);
            $body .= "<li class=\"qhm-search2-cache-file\" data-href=\"{$open_url}\">{$rm_file}<input type=\"hidden\" name=\"rm[{$cnt}]\" value=\"{$rm_file}\" /></li>\n";
            $cnt ++;
        }
        $body .= <<< EOD
</ul>
<p><input type="submit" name="clear" value="削除を実行する" class="btn btn-primary" /></p>
<input type="hidden" name="search_del" value="search_del" />
<input type="hidden" name="phase" value="clear" />
<input type="hidden" name="mode" value="msg" />
<input type="hidden" name="cmd" value="qhmsetting" />
</form>

<script>
$(function(){
  $(".qhm-search2-cache-file").on("click", function(e){
    if (e.shiftKey) {
      window.open($(this).data("href"));
    }
  });
});
</script>
EOD;
    }


    $haik_cache_files = glob(CACHE_DIR.'custom_skin.*');
    $haik_del_images = array();
    $haik_cache_images = array();

    foreach($haik_cache_files as $file)
    {
        if (preg_match('/\.dat$/', $file))
        {
            $data = file_get_contents($file);
            $data = unserialize($data);
            foreach ($data as $key => $val)
            {
                if (preg_match('/\.(gif|jpe?g|png)$/i', $val))
                {
                    $haik_cache_images[] = $val;
                }
            }
        }
        else if (preg_match('/\.(gif|jpe?g|png)$/i', $file))
        {
            $haik_del_images[$file] = $file;
        }
    }

    foreach($haik_cache_images as $file)
    {
        if (array_key_exists($file, $haik_del_images))
        {
            unset($haik_del_images[$file]);
        }
    }

    if (count($haik_del_images) > 0)
    {
        $body .= <<< EOD
<h3 style="margin-top:2em;">haikテーマ用キャッシュの初期化</h3>
<p>haikテーマの編集でアップロードした画像を削除します。<br />
※ 現在、カスタムで設定していない画像を削除します</p>
<form action="{$script}" method="post">
<ul>
EOD;
        $cnt = 0;
        foreach($haik_del_images as $file){
            $body .= "<li class=\"qhm-haik-cache-file\">{$file}<input type=\"hidden\" name=\"rm[{$cnt}]\" value=\"{$file}\" /></li>\n";
            $cnt ++;
        }
        $body .= <<< EOD
</ul>
<p><input type="submit" name="clear" value="削除を実行する" class="btn btn-primary" /></p>
<input type="hidden" name="haik_del" value="haik_del" />
<input type="hidden" name="phase" value="clear" />
<input type="hidden" name="mode" value="msg" />
<input type="hidden" name="cmd" value="qhmsetting" />
</form>
EOD;
    }


	return $body;
}


function plugin_qhmsetting_clear_msg($error = '')
{
	global $vars, $script;
	$error = '';

	//----------------- テンプレートの削除 ---------------
	if( isset($vars['tmp_del']) )
	{
		foreach($vars['rm'] as $rm_file)
		{
			if(file_exists($rm_file) && is_writable($rm_file)){
				unlink($rm_file);

			}
			else{
				$error .= $rm_file."\n";
			}
		}

		$log_msg = $error==='' ?
			''
			: nl2br('削除できなかったファイル'."\n".$error);

		$_SESSION['flash_msg'] = <<<EOD
<h2>削除を完了しました</h2>
<p>テンプレートキャッシュを削除しました</p>
$log_msg
EOD;


		redirect($script. '?cmd=qhmsetting&mode=form&phase=clear');
		return $body;
	}

	//----------------- 検索用キャッシュの削除 ---------------
	if( isset($vars['search_del']) )
	{
		foreach($vars['rm'] as $rm_file)
		{
			if(file_exists($rm_file) && is_writable($rm_file)){
				unlink($rm_file);
			}
			else{
				$error .= $rm_file."\n";
			}
		}

		$log_msg = $error==='' ?
			''
			: nl2br('削除できなかったファイル'."\n".$error);

		$_SESSION['flash_msg'] = <<<EOD
<h2>削除を完了しました</h2>
<p>検索用キャッシュを削除しました</p>
$log_msg
EOD;


		redirect($script. '?cmd=qhmsetting&mode=form&phase=clear');
		return $body;
	}

	//----------------- haik用キャッシュの削除 ---------------
	if( isset($vars['haik_del']) )
	{
		foreach($vars['rm'] as $rm_file)
		{
			if(file_exists($rm_file) && is_writable($rm_file)){
				unlink($rm_file);
			}
			else{
				$error .= $rm_file."\n";
			}
		}

		$log_msg = $error==='' ?
			''
			: nl2br('削除できなかったファイル'."\n".$error);

		$_SESSION['flash_msg'] = <<<EOD
<h2>削除を完了しました</h2>
<p>haik用キャッシュを削除しました</p>
$log_msg
EOD;


		redirect($script. '?cmd=qhmsetting&mode=form&phase=clear');
		return $body;
	}


	//-------------------- キャッシュ機能の設定 ------------
	if( isset($vars['cache_setting']) )
	{
		if( isset($vars['enable_cache']) )
		{
			$_SESSION['qhmsetting']['enable_cache'] = 1;
			$msg = '有効にしました。';
		}
		else{
			$_SESSION['qhmsetting']['enable_cache'] = 0;
			$msg = '無効にしました。';
		}

		plugin_qhmsetting_update_ini();

		$_SESSION['flash_msg'] = '<h2>高速化設定の変更完了</h2><p>キャッシュ機能を、'.$msg.'</p>';
		redirect($script. '?cmd=qhmsetting&mode=form&phase=clear');

		return '<p>キャッシュ機能を、'.$msg.'</p>'.'<p><a href="'.$script.'?cmd=qhmsetting">設定に戻る</a></p>';
	}

	//-------------------- キャッシュの削除 ---------------
	if( isset($vars['cache_del']) )
	{
		$files = array();
		if ($dir = opendir(CACHE_DIR)) {
		    while (($file = readdir($dir)) !== false) {
		        if (preg_match('/\.(tmp|tmpr)$/', $file)) {
		            $files[] = $file;
		            unlink(CACHE_DIR.$file);
		        }
		    }
		    closedir($dir);
		}

		if (count($files) > 0)
		{
			$_SESSION['flash_msg'] = '<h2>高速化キャッシュの削除完了</h2>';
			$_SESSION['flash_msg'] .= '<p>以下のファイルを削除しました</p>
<ul>
<li>'
.join('</li><li>', $files).
'</li>
</ul>
';
		}
		else
		{
			$_SESSION['flash_msg'] = '<p>高速化キャッシュは既に削除されています</p>';
		}
		redirect($script. '?cmd=qhmsetting&mode=form&phase=clear');

		return '<p><a href="'.$script.'?cmd=qhmsetting">設定に戻る</a></p>
<p>以下のファイルを削除しました</p>
<ul>
<li>'
.implode('</li><li>', $files).
'</li>
</ul>
';
	}
}

function plugin_qhmsetting_clear_view_search2_cache()
{
    global $vars;
    $file = $vars['file'];
    if (preg_match('/cache\/(.+)_search\.txt/', $file, $mts))
    {
        $page = decode($mts[1]);
        $content = file_get_contents($file);
        return '<h2>'.$page.' の検索用キャッシュ</h2>' . '<pre>' . h($content) . '</pre>';
    }
    return '<p>利用できません</p>';
}

//--<CloseForm>--
function plugin_qhmsetting_close_form($error = '')
{
	global $script, $site_close_all;
	global $other_plugins;
	$hlp_close = '';

	$reverse = '公開';
	$current = '閉鎖';
	$btn_class = 'btn-success';
	if($site_close_all === 0){
		$tmp = $current;
		$current = $reverse;
		$reverse = $tmp;
		$btn_class = 'btn-danger';
	}
	$value = ($site_close_all ^ 1);

	$body = <<<EOD
<h2>サイトの公開／閉鎖設定 {$hlp_close}</h2>
<p>大幅なサイトの更新を行う場合などに、「サイト全体」を閉鎖することができます。<br />
閉鎖中は、ログイン中のみ閲覧・編集が可能です。</p>

<div class="well">
<form action="{$script}" method="post">
<input type="submit" name="close_setting" value="{$reverse}する" class="btn {$btn_class}" />
<input type="hidden" name="qhmsetting[site_close_all]" value="{$value}" />
<input type="hidden" name="phase" value="close" />
<input type="hidden" name="mode" value="msg" />
<input type="hidden" name="cmd" value="qhmsetting" />
</form>
<p>
	現在の設定：{$current}中
</p>
</div>
<p>※設定を押すと、即時に反映します。</p>
EOD;

	return $body;
}

function plugin_qhmsetting_close_msg($error = '')
{
	global $script, $site_close_all, $vars;

	//サイト閉鎖
	if( isset($vars['qhmsetting']['site_close_all']) && $vars['qhmsetting']['site_close_all'] === '1')
	{
		$msg = 'サイトを閉鎖しました。';
		$note = 'ログイン時のみサイトの閲覧、編集が可能です。';
		$_SESSION['qhmsetting']['site_close_all'] = 1;
	}
	else
	{
		$msg = 'サイトを公開しました。';
		$note = 'サイトを一般公開しました。';
		$_SESSION['qhmsetting']['site_close_all'] = 0;
	}

	plugin_qhmsetting_update_ini();

	$_SESSION['flash_msg'] = <<<EOD
<h2>$msg</h2>
<p>$note</p>
EOD;

	redirect($script. '?cmd=qhmsetting');
	return $body;

}
//--</CloseForm>--


/**
* モバイル転送
*/
function plugin_qhmsetting_mobile_form($error = '')
{
	global $script, $vars;
	global $other_plugins;
	$hlp_mobile = '';

	$params = plugin_qhmsetting_getparams();

	$error_msg = ($error!='') ? '<p style="color:red">'.$error.'</p>' : '';

	$body = <<<EOD
<h2>携帯端末アクセスの転送先{$hlp_mobile}</h2>
<p>以下にURLを指定して下さい。なお、空を設定すると転送しません。</p>
{$error_msg}
<form method="post" action="{$script}" class="form-horizontal">
  <div class="form-group">
    <label for="" class="control-label col-sm-3">転送先URL</label>
    <div class="col-sm-9">
      <input type="text" name="qhmsetting[mobile_redirect]" value="{$params['mobile_redirect']}" class="form-control">
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-9 col-sm-offset-3">
      <input type="submit" value="設定する" onclick="return confirm('設定を実行しますか？');" class="btn btn-primary" />
    </div>
  </div>
  <input type="hidden" name="phase" value="mobile" />
  <input type="hidden" name="mode" value="msg" />
  <input type="hidden" name="plugin" value="qhmsetting" />
</form>

EOD;

	return $body;
}

function plugin_qhmsetting_mobile_msg()
{
	global $vars, $script;

	$url = $_SESSION['qhmsetting']['mobile_redirect'] = $vars['qhmsetting']['mobile_redirect'];

	if(! is_url($url) && $url!='' ){
		return plugin_qhmsetting_mobile_form('URLが不正です。正しいものを入力してください。');
	}

	plugin_qhmsetting_update_ini();

	$ret =  <<<EOD
<h2>転送設定完了</h2>
<p>%REP%</p>
EOD;

	if($url == ''){
		$ret = str_replace('%REP%', '携帯端末からのアクセスを転送しません。' , $ret);
	}
	else{
		$ret = str_replace('%REP%', '携帯端末からのアクセスを「'.h($url).'」に転送します。', $ret);
	}

	$_SESSION['flash_msg'] = $ret;
	redirect($script. '?cmd=qhmsetting');
}



/**
* Googleマップキーの設定
*/
function plugin_qhmsetting_gmap_form($error = '')
{
	global $script, $vars;
	global $other_plugins;
	$hlp_gmap = '';

	$params = plugin_qhmsetting_getparams();

	$error_msg .= ($error!='') ? '<p style="color:red">'.$error.'</p>' : '';

	$body = <<<EOD
<h2>Googleマップのキーを設定します{$hlp_gmap}</h2>
<p>Google Maps API Keyを<a href="http://code.google.com/intl/ja/apis/maps/signup.html" target="new">こちらから</a>取得して設定してください。</p>
{$error_msg}
<form method="post" action="{$script}">
<table class="style_table" cellspacing="1" border="0">
	<tr>
		<th class="style_th">　Key　</th>
		<td class="style_td">　<input type="text" name="qhmsetting[googlemaps_apikey]" size="50" value="{$params['googlemaps_apikey']}"  />　</td>
	</tr>
</table>

<p style="text-align:center"><input type="submit" value="設定する" style="font-size:16px" onclick="return confirm('設定を実行しますか？');" class="btn btn-primary" /></p>
<input type="hidden" name="phase" value="gmap" />
<input type="hidden" name="mode" value="msg" />
<input type="hidden" name="plugin" value="qhmsetting" />
</form>

EOD;

	return $body;
}

/**
* Googleマップキーの設定
*/
function plugin_qhmsetting_gmap_msg()
{
	global $vars, $script;

	$_SESSION['qhmsetting']['googlemaps_apikey']
		= str_replace("'", "\'", $vars['qhmsetting']['googlemaps_apikey']);

	plugin_qhmsetting_update_ini();

	return <<<EOD
<h2>転送設定完了</h2>
<p>Google Maps API Keyを設定しました。<br />
{$vars['qhmsetting']['googlemaps_apikey']}</p>
<p><a href="{$script}?plugin=qhmsetting" style="font-weight:bold;background-color:#ff6;">戻る</a></p>
EOD;
}


//--<SNSForm>--

/**
 * Open Graph Protocol の設定
 */
function plugin_qhmsetting_sns_form($errmsg = '')
{
	global $vars, $script, $description, $defaultpage;
	global $ogp_tag, $og_description, $og_image, $add_xmlns, $fb_admins, $fb_app_id;
	$qt = get_qt();
	$qt->setv('jquery_include', TRUE);
	$html = '';

	if (isset($og_image) && $og_image != '')
	{
		$og_image_url = $og_image;
	}
	else
	{
		$og_image_url = dirname($script). '/image/hokuken/ogp_default.png';
	}

	$beforescript = '
<link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" media="screen" href="js/thickbox.css" type="text/css" charset="Shift_JIS" />
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/thickbox.js"></script>
<script type="text/javascript">
$(function(){
	//OGP
	function switchConf(checked) {
		var $conf = $("#ogpConfigWrapper");
		if ( ! checked) {
			$conf.slideUp("fast");
			$("input,textarea,select", $conf).prop("disabled", true);
		}
		else {
			$conf.slideDown("fast");
			$("input,textarea,select", $conf).prop("disabled", false);
		}
	}
	$("#ogpTag")
	.each(function(){
		switchConf($(this).is(":checked"));
	})
	.click(function(){
		switchConf($(this).is(":checked"));
	});

	//Facebook
	var selected = 0;
	$("form")
	.each(function(){
		if ($("#fbAdminsInput").val().length > 0) {
			selected = 1;
			$("#fbType").val("admins");
		} else {
			$("#fbType").val("app_id");
		}
	})
	.submit(function(){
		var selected = $("#fbConfTabs").tabs("option", "selected"),
			otherId;

		if (selected == 0) {
			otherId = "#fbAdminsInput";
		} else {
			otherId = "#fbAppIdInput";
		}
		// 他方の値がない場合、スルー
		if ($(otherId).val().length === 0) {
			return true;
		}

		var dis = (otherId == "#fbAdminsInput")? "Facebook 管理アカウント": "Facebook アプリ",
			msg = "この設定を保存すると、"+ dis +"の設定は破棄されます。よろしいですか？";
		if ( ! confirm(msg)) return false;
	});
	$("#fbConfTabs").tabs({
		selected: selected,
		show: function(event, ui){
			if ($("#fbConfTabs").tabs("option", "selected") == 0) {
				$("#fbType").val("app_id");
			} else {
				$("#fbType").val("admins");
			}
		}
	});
});
</script>
';
	$qt->appendv('beforescript', $beforescript);

	if ($errmsg != '')
	{
		$html .= '
<p style="color:red;">
	'. $errmsg. '
</p>
';
	}
	$swfulink = has_swfu()? '<br /><br />■<a href="'. dirname($script). '/swfu/index_child.php?page='. rawurlencode($defaultpage). '&amp;KeepThis=true&amp;TB_iframe=true" class="thickbox">SWFU起動</a>（アップロードしたら<strong>URL</strong>をコピペしてください）': '';

	$html .= '
<h2>QHMとSNS連携の設定</h2>
<p>
	QHMのページを<strong>Open Graph Protocol</strong>に対応したSNSにシェアする際などに読み込まれる内容を制御することができます。<br />
	また、このQHMをFacebook アプリとして登録することで、Facebook プラグインと連携もできます。
</p>

<form action="'. $script. '" method="post">
<input type="hidden" name="plugin" value="qhmsetting" />
<input type="hidden" name="phase" value="sns" />
<input type="hidden" name="mode" value="msg" />

<input type="hidden" name="ogp_tag" value="0" />
<label><input type="checkbox" name="ogp_tag" value="1" id="ogpTag"'. ($ogp_tag? ' checked="checked"': ''). ' /> Open Graph Protocol タグを有効にする</label>

<div id="ogpConfigWrapper" style="border:1px solid #aaa;margin: 10px;padding: 10px;">
	<div id="ogpConfig">
		<h3>Open Graph Protocol 設定</h3>
		<ul style="margin: 10px 10px;">
			<li>
				サイトの説明&nbsp;<span style="color:#999">'. h('og:description').'</span><br />
				<textarea name="og_description" id="" cols="60" rows="4" placeholder="'. h($description). '">'. h($og_description). '</textarea>
				<br />
				<span style="font-size:12px;">※「サイトの設定」で記述した<strong>サイトの説明</strong>と異なる文章を表示させたい場合、こちらに記入してください。</span>
				<br /><br />
			</li>
			<li>
				サイト画像&nbsp;<span style="color:#999">'. h('og:image').'</span><br />
				<img src="'. $og_image_url. '" alt="サイト画像" title="サイト画像" style="max-width:100px;max-height:100px;" />
				URL: <input type="text" name="og_image" value="'. h($og_image). '" size="30" style="width:30em;" />'. $swfulink. '<br />
				<span style="font-size:12px;">※あまり大きな画像はおすすめしません。<br />設定される場合、100px〜200px四方くらいの画像をご用意ください。</span>
			</li>
		</ul>
	</div>
	<br /><br />
	<div id="fbConfig">

		<h3>Facebook 設定</h3>
		<input type="hidden" name="fb_type" id="fbType" />

		<p style="margin-left:30px;">
			Facebook との連携設定を行います。<br />
			Open Graph Protocol タグにFacebook 用の拡張タグが追加されます。<br />
			※「アプリ設定」と「管理アカウント設定」はどちらか一方が有効になりますのでご注意ください。
		</p>


		<div id="fbConfTabs" style="border:none;">

		<ul style="margin:5px 0;">
			<li><a href="#fbAppIdConf">アプリ設定</a></li>
			<li><a href="#fbAdminsConf">管理アカウント設定</a></li>
		</ul>

		<div id="fbAppIdConf">
			<p style="font-size: .8em">
				Facebook アプリIDを設定することで、QHMのFacebook 系プラグインでアプリIDが使用されます。<br />
				例えば、「いいね！」を押した方のウォールに最新情報を投稿できるようになります。<br />
				※このQHMをFacebook アプリとして登録する必要があります。
			</p>
			アプリID: <input type="text" name="fb_app_id" id="fbAppIdInput" value="'. h($fb_app_id). '" />

		</div>


		<div id="fbAdminsConf">
			<p style="font-size: .8em">
				このQHMの所有者や編集者のFacebook アカウントを関連付けることができます。<br />
				複数人設定する場合、半角カンマ区切りでFacebook アカウントIDを記入してください。<br />
				※Facebook ページIDは利用できません。
			</p>
			Facebook アカウントID:<br />
			<input type="text" name="fb_admins" id="fbAdminsInput" value="'. h($fb_admins). '" size="50" style="width:100%;" />

		</div>

		</div>

	</div>
</div>
<br />
<input type="submit" value="設定する" class="btn btn-primary" />
</form>

<br />
<br />

';

	return $html;
}

function plugin_qhmsetting_sns_msg()
{
	global $vars, $script;

	$errmsg = '';
	// OGP 設定
	if (isset($vars['ogp_tag']))
	{
		//有効
		if ($vars['ogp_tag'] == '1')
		{
			//ogp tag
			$_SESSION['qhmsetting']['ogp_tag'] = 1;

			//og:description
			$_SESSION['qhmsetting']['og_description'] = trim($vars['og_description']);

			//og:image
			$og_image = trim($vars['og_image']);
			if (isset($vars['og_image']) && is_url($vars['og_image']) && preg_match('/\.(jpg|jpeg|gif|png)$/', $og_image))
			{
				$_SESSION['qhmsetting']['og_image'] = $og_image;
			}
			//空の場合も許可
			else if ($og_image == '')
			{
				$_SESSION['qhmsetting']['og_image'] = '';
			}
			else
			{
				$errmsg = '画像のURLが不正です。<br />';
			}

			//Facebook 設定は排他的（fb_app_id XOR fb_admins）
			$fb_app_id = isset($vars['fb_app_id']) && trim($vars['fb_app_id'])? $vars['fb_app_id']: FALSE;
			$fb_admins = isset($vars['fb_admins']) && trim($vars['fb_admins'])? $vars['fb_admins']: FALSE;
			$fb_type = isset($vars['fb_type']) && trim($vars['fb_type'])? $vars['fb_type']: 'app_id';//default: app_id

			//両方設定されている場合、fb_type をチェックする
			if ($fb_app_id !== FALSE && $fb_admins !== FALSE)
			{
				if ($fb_type == 'app_id')
				{
					$fb_admins = FALSE;
				}
				else
				{
					$fb_app_id = FALSE;
				}
			}
			//app_id
			if ($fb_app_id !== FALSE)
			{
				if (preg_match('/^(:?\d+)?$/', $fb_app_id))
				{
					$_SESSION['qhmsetting']['fb_app_id'] = trim($vars['fb_app_id']);
					$_SESSION['qhmsetting']['fb_admins'] = '';//delete
				}
				else
				{
					$errmsg = 'Facebook アプリIDは数字のみで入力してください。<br />';
				}
			}
			//admins
			else if ($fb_admins !== FALSE)
			{
				$admins = explode(',', $fb_admins);
				foreach ($admins as $i => $admin)
				{
					$admin = trim($admin);
					if (preg_match('/^[.0-9a-z_-]+$/i', $admin))
					{
						$admins[$i] = $admin;
					}
					else
					{
						$errmsg = 'Facebook 管理アカウント名に不正な文字列が含まれています<br />';
						break;
					}
				}
				$_SESSION['qhmsetting']['fb_admins'] = join(',', $admins);
				$_SESSION['qhmsetting']['fb_app_id'] = '';//delete
			}
			else
			{
				//両方消す
				$_SESSION['qhmsetting']['fb_app_id'] = $_SESSION['qhmsetting']['fb_admins'] = '';
			}

		}
		//無効
		else
		{
			$_SESSION['qhmsetting']['ogp_tag'] = 0;
			//og:* は初期値に戻す
			$_SESSION['qhmsetting']['og_image'] = $_SESSION['qhmsetting']['og_description'] = '';
		}
	}


	if ($errmsg == '')
	{
		plugin_qhmsetting_update_ini();
		$msg = 'SNS連携設定完了';
		redirect($script. '?cmd=qhmsetting', $msg);

	}
	else
	{
		return plugin_qhmsetting_sns_form($errmsg);
	}

}
//--</SNSForm>--



//--<SSLScript>--
/**
* スクリプトのパスの設定
*/
function plugin_qhmsetting_script_form($error = '')
{
	global $script, $script_ssl, $other_plugins;

	$body = <<<EOD
<script type="text/javascript">
<!--
window.onload = function() {
	var href = location.href.replace(location.search, '');
	document.getElementById('frm_script').action = href;
}
function fill_link(){
	var href = location.href.replace(location.search, '');
	document.getElementById('op_script').value = href;
}
// -->
</script>
<h2>QHM リンク設定</h2>
<p>QHMが利用するドメイン名、パス、リンクのための情報を設定します。{$help_link}<p/>

<p style="color:red">{$error}</p>
<form method="post" action="" id="frm_script" name="frm_script">

<table class="style_table" cellspacing="1" border="0" style="font-size:12px">
	<tr>
		<th class="style_th">　リンク設定　</th>
		<td class="style_td">　<input type="text" id="op_script" name="op_script" size="50" value="" /> <input type="button" value="自動取得" onclick="fill_link();" class="btn btn-primary" /><br />
		<span style="font-size:12px;color:#29A1AC">現在の設定 : {$script}</span></td>
	</tr>
	<tr>
		<th class="style_th">　SSLリンク設定　</th>
		<td class="style_td">　<input type="text" name="op_script_ssl" size="50" /><br />
		<span style="font-size:12px;color:#29A1AC">現在の設定 : {$script_ssl}</span>　</td>
	</tr>
	<tr>
		<th class="style_th">　ユーザー名　</th>
		<td class="style_td">　<input type="text" name="op_username" size="18" />　</td>
	</tr>
	<tr>
		<th class="style_th">　パスワード　</th>
		<td class="style_td">　<input type="password" name="op_passwd" size="18" /></td>
	</tr>
</table>
<p style="text-align:center"><input type="button" name="back" value="戻　る" onclick="location.href = location.href.replace(location.search, '') + '?cmd=qhmauth';" class="btn" />　　<input type="submit" name="setscript" value="設　定" class="btn btn-primary" /></p>
<input type="hidden" name="plugin" value="qhmsetting" />
<input type="hidden" name="phase" value="script" />
<input type="hidden" name="mode" value="msg" />
</form>
<br />

<div style="border:2px dotted #CCC;background-color:#eee;margin:1.5em auto;padding:0 10px;width:90%;text-align:left;font-size:12px;">
<p style="font-weight:bold">【解説】</p>
<p>リンク設定・SSLリンク設定は、通常<b>必要ありません</b>。<br />
次のような場合、設定を行います。</p>

<ol>
	<li>正しくID・パスワードを入力してもログインできない場合、「リンク設定」を行います</li>
	<li>一部ページで共用SSLを利用する場合、「リンク設定」、「SSL用リンク設定」を行います</li>
</ol>

<p>※ ログイン画面で、『リンクが正常に…』と表示されても、問題なくログインできる場合、「リンク設定」を行う必要はありません。<br />
※ サイト全体を共用SSL通信を使う場合、リンク設定に共用SSLのURLを設定します。</p>

<p>リンク設定を行う場合、「http://あなたのホームページの設置先/index.php」とします<br />
例）http://example.com/index.php</p>

<p>サーバーの変更や誤った設定をした場合、設定を初期化してください。<br />
初期化するには、「リンク設定」「SSLリンク設定」を空白にし、「設定」をクリックしてください。</p>
</div>
EOD;

	return $body;
}

function plugin_qhmsetting_script_msg()
{
	global $vars, $script, $username, $auth_users;
	$msg = '';

	// --------------------------------------
	// reset passwd
	$msg = "リンクが正常に動作しないサーバー用の設定をしました";
	$oppasswd  = $vars['op_passwd'];
	$opusername = $vars['op_username'];
	$opscript = $vars['op_script'];
	$opscript_ssl = $vars['op_script_ssl'];

	//error
	$error = '';
	if ( $username != $opusername) {
		$error = 'ユーザー名が一致しません。<br />';
	}

	if( $auth_users[$username] != '{x-php-md5}'.md5($oppasswd) ){
		$error .= 'パスワードが一致しません。<br />';
	}
	if($opscript != '' && !is_url($opscript)){
		$error .= 'URLが正しくありません。<br />';
	}

	if($error!=''){
		return plugin_qhmsetting_script_form($error);
	}


	$_SESSION['qhmsetting']['script'] = $opscript;
	$_SESSION['qhmsetting']['script_ssl'] = $opscript_ssl;
	plugin_qhmsetting_update_ini();

	header('location: '.$opscript.'?cmd=qhmauth');
	exit;
//	$msg = '変更しました';
//	return plugin_qhmsetting_script_form($msg);
}
//--</SSLScript>--


/**
* session save path の設定
*/
function plugin_qhmsetting_sssavepath_form($error = '')
{
	global $script, $session_save_path;
	$save_dir = CACHEQHM_DIR;

	$sts = '';
	$btn = '<input type="submit" id="set" name="set" value="設　定" class="btn btn-primary" />';
	if ($session_save_path != '') {
		$sts = '<div style="border:2px solid #66AACC;background-color:#EEEEFF;margin:5px auto;padding:0 10px;width:90%;text-align:center;"><p>現在、QHM内にセッションの保存先が設定されています。<br />設定を元に戻す場合は、「解除」を行ってください。</p></div>';
		$btn = '<input type="submit" id="unset" name="unset" value="解　除" class="btn btn-danger" />';
	}

	$body = <<<EOD
<h2>セッションが正常に動作しないサーバーの設定</h2>
<p>サーバー側でセッションの保存先が正しく設定されていない場合、QHMの認証ができません。<br />そこで、対処法としてQHM内にセッションを保存します。（フォルダ：{$save_dir}）</p>
{$sts}
<p style="color:red">{$error}</p>
<form method="post" action="{$script}" id="frm_script" name="frm_script">

<table class="style_table" cellspacing="1" border="0">
	<tr>
		<th class="style_th">　ユーザー名　</th>
		<td class="style_td">　<input type="text" name="op_username" size="18" />　</td>
	</tr>
	<tr>
		<th class="style_th">　パスワード　</th>
		<td class="style_td">　<input type="password" name="op_passwd" size="18" /></td>
	</tr>
</table>
<p style="text-align:center"><input type="button" name="back" value="戻　る" onclick="location.href='{$script}?cmd=qhmauth';" class="btn" />　　{$btn}　</p>

<input type="hidden" name="plugin" value="qhmsetting" />
<input type="hidden" name="phase" value="sssavepath" />
<input type="hidden" name="mode" value="msg" />
</form>
<br />
EOD;

	return $body;
}

function plugin_qhmsetting_sssavepath_msg()
{
	global $vars, $script, $username, $auth_users;
	$msg = '';

	// --------------------------------------
	// reset passwd
	$msg = "セッションが正常に動作しないサーバー用の設定をしました";
	$oppasswd  = $vars['op_passwd'];
	$opusername = $vars['op_username'];

	//error
	$error = '';
	if ( $username != $opusername) {
		$error = 'ユーザー名が一致しません。<br />';
	}

	if( $auth_users[$username] != '{x-php-md5}'.md5($oppasswd) ){
		$error .= 'パスワードが一致しません。<br />';
	}

	if($error!=''){
		return plugin_qhmsetting_sssavepath_form($error);
	}

	if (isset($vars['set'])) {
		$_SESSION['qhmsetting']['session_save_path'] = CACHEQHM_DIR;
	}
	else if (isset($vars['unset'])) {
		$_SESSION['qhmsetting']['session_save_path'] = '';
	}

	plugin_qhmsetting_update_ini();

	header('location: '.$script.'?cmd=qhmauth');
	exit;

//	$msg = '変更しました';
//	return plugin_qhmsetting_script_form($msg);
}


/**
* 現在の設定ファイルの値を取得して、
* $_SESSION['qhmsetting']の値で上書きして、pukiwiki.ini.phpを上書きする
*/
function plugin_qhmsetting_update_ini($params = '')
{

	//htmlspecialcharsするリスト。
	//逆変換する処理にも対応する必要あり (-> plugin_qhmsetting_getparams)
	$escapes = plugin_qhmsetting_get_escapes();

	//インストールシステムと、QHM上の両方で使うための処理
	if (is_array($params)) {
		$install_mode = true;
	}
	else {
		$install_mode = false;
		$params = plugin_qhmsetting_getparams();
	}

	//現在の設定変数にエスケープ処理を施し、PHPスクリプトに変換
	$qhm_ini_php = '';

	foreach($params as $key=>$val)
	{
		$val = addcslashes($val, '\\\'');
		$val = str_replace("\r\n", "\n", $val);

		if ( isset($escapes[$key]) )
			$val = htmlspecialchars($val); //「"」を「&quot;」に

		//PHPスクリプトに変換
		$qhm_ini_php .= '$'.$key." = " . ( preg_match('/^[0-9]{1,3}$/', $val) ? $val : "'".$val."'" ) . ";\n";
	}

	$str  = '<?php'."\n\n";
	$str .= $qhm_ini_php."\n";
	$str .= '?>';

	if ( $install_mode ) {
		return $str;
	} else {
		return file_put_contents('qhm.ini.php', $str);
	}
}

function plugin_qhmsetting_get_escapes(){

	return array(
		'page_title'=>'dummy',
		'owneraddr'=>'dummy',
		'ownertel' =>'dummy',
		'headcopy' =>'dummy',
		'keywords' =>'dummy',
		'description' =>'dummy',
	);

}

/**
* pukiwiki.ini.php の設定項目のすべてを paramsにセットし、
* $_SESSION['qhmsetting']の値で上書きして、returnする。
*/
function plugin_qhmsetting_getparams($update=TRUE, $inifile = '')
{
	//設定ファイルの読み込み。インストールシステムで使うことを想定
	if($inifile == ''){
		require(INI_FILE);
	}
	else{
		require($inifile);
	}

	//設定する変数の一覧
	$inivals = array(
		'style_type',
		'style_name',
		'logo_image',
		'page_title',
		'enable_wp_theme',
		'enable_wp_theme_name',
		'wp_add_css',
		'owneraddr',
		'ownertel',
		'headcopy',
		'keywords',
		'description',
		'custom_meta',
		'qhm_adminmenu',
		'accesstag',
		'ga_tracking_id',
		'modifier',
		'modifierlink',
		'nowindow',
		'username',
		'passwd',
		'notify',
		'notify_diff_only',
		'smtp_server',
		'notify_to',
		'notify_from',
		'smtp_auth',
		'pop_server',
		'pop_userid',
		'pop_passwd',
		'google_apps',
		'google_apps_domain',
		'script',
		'script_ssl',
		'no_qhm_licence',
		'qhm_access_key',
		'reg_exp_host',
		'enable_cache',
		'autolink',
		'session_save_path',
		'site_close_all',
		'mobile_redirect',
		'googlemaps_apikey',
		'exclude_to_name',
		'enable_smart_style',
		'smart_name',
		'check_login',
		'enable_fitvids',
		'unload_confirm',
		'http_scheme',
		'admin_email',
		'encrypt_ftp',
		'qhm_pw_str',
		'ogp_tag',
		'og_description',
		'og_image',
		'add_xmlns',
		'fb_admins',
		'fb_app_id',
		'qblog_social_widget',
		'qblog_social_html',
		'qblog_social_wiki',
		'qblog_title',
		'qblog_enable_comment',
		'qblog_default_cat',
		'qblog_close',
		'qblog_enable_ping',
		'qblog_ping',
		'qblog_comment_notice',
		'is_qmail',
		'mail_encode',
	);


	//空の場合、デフォルト値を設定する変数と、その値
	$defvals = array(
		'qhm_adminmenu'    => '1',
		'nowindow'         => '0',
		'notify'           => '0',
		'notify_diff_only' => '1',
		'smtp_auth'        => '0',
		'no_qhm_licence'   => '0',
		'qhm_access_key'   => '1',
		'enable_cache'     => '0',
		'site_close_all'   => '0',
		'enable_wp_theme'  => '0',
		'wp_add_css'       => '',
		'exclude_to_name'  => '0',
		'enable_smart_style'=> '0',
		'smart_name'       => 'blue',
		'check_login'      => '1',
		'enable_fitvids'   => '1',
		'unload_confirm'   => '1',
		'http_scheme'      => '',
		'ogp_tag'          => '0',
		'og_description'   => '',
		'og_image'         => '',
		'add_xmlns'        => '',
		'fb_admins'        => '',
		'fb_app_id'        => '',
		'qblog_social_widget' => 'default',
		'qblog_social_html'=> '',
		'qblog_social_wiki'=> '',
		'qblog_title'      => '',
		'qblog_enable_comment'=> '1',
		'qblog_default_cat'=> 'ブログ',
		'qblog_close'      => '0',
		'qblog_enable_ping'=> '0',
		'qblog_ping'       => '',
		'qblog_comment_notice'=> '0',
		'is_qmail'         => '0',
    'mail_encode'      => 'ISO-2022-JP',
	);

	//設定変数を格納した配列の生成、デフォルト値もセット
	$params = array();
	foreach($inivals as $vname){
		eval('$params["'.$vname.'"] = $'.$vname.';');

		if( isset( $defvals[$vname] ) && (($params[$vname]==='') OR is_null($params[$vname]))) {
			$params[$vname] = $defvals[$vname];
		}
	}

	//セッションの設定変数で上書き
	if($update && isset($_SESSION['qhmsetting']))
	{
		//set up new params
		foreach($_SESSION['qhmsetting'] as $key=>$val)
		{
			$params[$key] = $val;
		}

		//セッションのクリアー
		unset($_SESSION['qhmsetting']);
	}


	//htmlエスケープしている変数を逆変換
	$escapes = plugin_qhmsetting_get_escapes();
	foreach( $escapes as $key=>$v){
		if( isset( $params[$key] ) ){
			$params[$key] = htmlspecialchars_decode($params[$key]);
		}
	}

	return $params;
}

function _get_type_name($mark) {
	$ret = "";
	switch ($mark) {
	case "r":
		$ret = "閲覧制限";
		break;
	case "e":
		$ret = "編集許可";
		break;
	default:
		$ret = "閲覧制限";
		break;
	}
	return $ret;
}

function _get_users_data()
{
	//user dataの取得
	$users_data = array();
	$fp = fopen(PLUGIN_QHMSETTING_USER_INI_FILE, "r");
	if ($fp) {
		flock( $fp, LOCK_SH );

		while (!feof($fp)) {
			$line = fgets($fp);
			if (trim($line)) {
				list($name,$passwd) = explode(',', $line);
				if ($name != ""){
					$users_data[trim($name)] = array("passwd"=>trim($passwd));
				}
			}
		}
		fclose($fp);
	}

	return $users_data;

}

function _get_accessdata() {
	$access_data = array();

	$fp = fopen(PLUGIN_QHMSETTING_ACCESS_INI_FILE, "r");
	if ($fp) {
		flock( $fp, LOCK_SH );

		while (!feof($fp)) {
			$line = fgets($fp);
			if (trim($line) != "") {
				list($type,$pattern,$name) = explode(',', $line);
				$access_data[] = array("type"=>trim($type), "pattern"=>$pattern, "user"=>trim($name));
			}
		}
		fclose($fp);
	}

	return $access_data;
}

function _add_del($data) {
	return $data . ",";
}

function _check_userdata($params) {

	global $username;

	$error = '';
	foreach ($params as $key=>$value) {

		switch ($key) {
		case "username":
			$title = 'ユーザー名';
			break;
		case "passwd":
			$title = 'パスワード';
			break;
		case "repasswd":
			$title = 'パスワード(確認)';
			break;
		}

		if (!preg_match( '/^[0-9a-zA-Z]+$/', $value) ) {
			// 半角英数字
			$error .= $title."は半角英数字で入力してください<br />";
		}
	}

	if($params['passwd'] != $params['repasswd'] )
	{
		$error .= 'パスワードと、確認が一致しません。<br />';
	}

	if( $params['username'] == $username )
	{
		$error .= '管理者名と重複しています<br />';
	}

	return $error;
}

function _write_userfile($stream, $mode) {
	// ファイルの作成
	$fp  = fopen(PLUGIN_QHMSETTING_USER_INI_FILE, $mode);
	flock( $fp, LOCK_EX );

	if($mode==='w'){
		ftruncate($fp, 0);
		rewind($fp);
	}

	if ($fp) {
		fwrite($fp, $stream);
		fclose($fp);
		chmod(PLUGIN_QHMSETTING_USER_INI_FILE, 0666);
	}
}

function _write_accessfile($stream, $mode) {
	// ファイルの作成
	$fp  = fopen(PLUGIN_QHMSETTING_ACCESS_INI_FILE, $mode); // ファイルの読込み
	flock( $fp, LOCK_EX );

	if($mode==='w'){
		ftruncate($fp, 0);
		rewind($fp);
	}

	if ($fp) {
		fwrite($fp, $stream);
		fclose($fp);
		chmod(PLUGIN_QHMSETTING_ACCESS_INI_FILE, 0666);
	}
}

function _get_pregdata($data, $mark) {
	switch ($mark) {
	case "front":
		$data = "/^".$data.".*/";
		break;
	case "back":
		$data = "/.*".$data."$/";
		break;
	case "part":
		$data = "/.*".$data.".*/";
		break;
	case "all":
		$data = "/^".$data."$/";
		break;
	default:
		break;
	}

	return $data;
}

//--<FTPAccess>--
/**
 *   localhost にFTP接続し、フォルダを作る
 */
function plugin_qhmsetting_ftp_access() {
	global $vars;

	$club_data = isset($_SESSION['remote_club'])? $_SESSION['remote_club']: array();

	$out = '';
	if ($club_data) {
		$username = $vars['username'];
		$password = $vars['password'];
		$dir = $vars['use_dir']? $vars['dir']: FALSE;
		$design = preg_match('/^[\w_]+$/', trim($vars['design_name']))? $vars['design_name']: '';
		$path = getcwd();//qhm path
		if ($design) {
			if ($conn_id = ftp_connect('localhost', 21)) {
				$login = ftp_login($conn_id, $username, $password);
				if ($login) {
					ftp_pasv($conn_id, true);
					//設置先フォルダが指定された場合、絶対パスかどうか判定する
					if ($dir !== FALSE && $dir[0] != '/') {
						$dir = ftp_pwd($conn_id). $dir;
					}
					$designdir = ($dir? $dir: $path). '/skin/hokukenstyle/';
					if (ftp_chdir($conn_id, $designdir)) {
						ftp_mkdir($conn_id, $design);
						ftp_chmod($conn_id, 0777, $designdir. $design);
						$out = 'OK';
					} else {
						$out = 'NG_Dir';
					}


				} else {
					$out .= 'FTPアカウント、あるいはパスワードが間違っています';
				}

				ftp_close($conn_id);
			} else {
				$out .= 'FTP接続できません';
			}
		} else {
			$out .= 'デザイン名が不正です';
		}

	} else {
		$out .= 'Ensmall Clubに認証されていません';
	}
	header("Content-Type: application/text; charset=UTF-8");
	echo '<result>', $out, '</result>';
	exit;

}
//--</FTPAccess>--



function plugin_qhmsetting_club_has_qhm()
{
	global $vars;
	$club_data = isset($_SESSION['remote_club'])? $_SESSION['remote_club']: array();

	header("Content-Type: application/text; charset=UTF-8");
	if ($club_data) {
		echo "<result>valid</result>";
	} else {
		echo "<result>invalid</result>";
	}

	if (ini_get('safe_mode')) {
		echo '<safe_mode>1</safe_mode>';
	}
	exit;
}

//--</GetQHMDesign>--

function plugin_qhmsetting_post($url, $data, $optional_headers = null) {
	if(function_exists('stream_get_contents')){
		$params = array(
			'http' => array(
				'method' => 'POST',
				'content' => $data
			)
		);

		if ($optional_headers !== null) {
			$params['http']['header'] = $optional_headers;
		}
		$ctx = stream_context_create($params);

		$fp = @fopen($url, 'rb', false, $ctx);

		//ストリームオープン失敗
		if (!$fp) {
			//echo "Problem with $url, $php_errormsg";
			return false;
		}

		$response = @stream_get_contents($fp);
		//echo '<br />';
		//読み込み失敗
		if ($response === false) {
			//echo "Problem reading data from $url, $php_errormsg";
		}
		return $response;
	}
	else if(!function_exists('stream_get_contents')) {

		$url_parse = parse_url($url);
		$port = $url_parse['scheme'] == 'http'? "80": '443';

		if ($fp = fsockopen($url_parse['host'], $port)) {
			fputs ($fp, "POST ".$url_parse['path']." HTTP/1.1\r\n");
			fputs ($fp, "User-Agent:PHP/".phpversion()."\r\n");
			fputs ($fp, "Host: ".$_SERVER["HTTP_HOST"]."\r\n");
			fputs ($fp, "Content-Type:
			application/x-www-form-urlencoded\r\n");
			fputs ($fp, "Content-Length: ".strlen($data)."\r\n\r\n");
			fputs ($fp, $data);
			while (!feof($fp)) {
				$response .= fgets($fp, 4096);
			}
			fclose($fp);
		}
		return $response;
	}
}


if (!function_exists('http_build_query')) {
/**
 *   @sea http://php.net/manual/ja/function.http-build-query.php
 */
function http_build_query($data, $prefix='', $sep='', $key='') {
	$ret = array();
	foreach ((array)$data as $k => $v) {
		if (is_int($k) && $prefix != null) {$k = urlencode($prefix . $k);}
		if (!empty($key)) {$k = $key.'['.urlencode($k).']';}

		if (is_array($v) || is_object($v)) {
			array_push($ret, http_build_query($v, '', $sep, $k));
		}
		else {array_push($ret, $k.'='.urlencode($v));}
	}

	if (empty($sep)) {$sep = ini_get('arg_separator.output');}

	return implode($sep, $ret);
}
}


?>
