<?php
/**
 *   QHMデザインプラグイン
 *   -------------------------------------------
 *   qhmdesing.inc.php
 *
 *   Copyright (c) 2012 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2012-04-27
 *   modified :
 *
 *   Description :
 *   Preview And Get Design templetes
 *
 *   Usage :
 *
 */
function plugin_qhmdesign_init()
{
	if ( ! exist_plugin('qhmsetting'))
	{
		die('Fatal error: qhmsetting plugin not found');
	}
	do_plugin_init('qhmsetting');
}

function plugin_qhmdesign_action()
{
	global $vars, $script, $style_type;
	
	// 管理者のみ表示可
	
	// テンプレート名を取得
	$style = $vars['style_name'];
	
	// Clubへ認証済み、
	// スキンファイル受信
	$ret = plugin_qhmdesign_getskin($style);
	if ($ret['status']['code'] === 1)
	{
		$_SESSION['temp_skin'] = $ret['data']['skin_data'];
		$_SESSION['temp_design'] = $ret['data']['style_name'];
		$_SESSION['temp_css'] = '<link rel="stylesheet" media="screen" href="'.h($ret['data']['css']).'" type="text/css" charset="Shift_JIS" /> ';
		$_SESSION['temp_style_path'] = dirname($ret['data']['css']).'/';
		$_SESSION['temp_style_type'] = $ret['data']['isPD'] ? 'text' : $style_type;

		// FrontPage へ移動
		redirect($script);
	}
	else
	{
		return array('msg' => $ret['status']['message'], 'body' => $ret['status']['message']);
	}
}

/**
 * スキンファイルを一時的に使用する
 */
function plugin_qhmdesign_getskin($style)
{
	$result = array();
	
	$club_data = isset($_SESSION['remote_club'])? $_SESSION['remote_club']: array();

	$get_version_url = PLUGIN_QHMSETTING_CLUB_URL . 'users/get_qhm_skin/'.QHM_REVISION;
	$post = array(
		'email' => $club_data['email'],
		'password' => $club_data['password'],
		'style_name' => $style,
	);
	$res = http_request($get_version_url, $method = 'POST', '', $post);
	$ret = unserialize($res['data']);

	return $ret;
}

/**
 * デザインをダウンロードする。
 */
function plugin_qhmdesign_getdesign($style)
{
	
}

?>