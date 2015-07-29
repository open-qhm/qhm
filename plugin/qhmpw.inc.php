<?php
/**
 * QHM パスワード変更プラグイン
 *
 */
if ( ! defined('ALLOW_PASSWD_PATTERN'))
{
	define('ALLOW_PASSWD_PATTERN', "/^[!-~]+$/");
}

function plugin_qhmpw_action()
{
	global $script, $admin_email, $post, $get;
	$qm = get_qm();

	if( ! is_writable('qhm.ini.php') ){
		$href  = 'javascript:history.go(-1)';
		return array('msg'=>'error', 'body'=>$qm->m['plg_qhmpw']['err_notwritable']);
	}
	
	if ($admin_email == '') {
		return array('msg'=>'error', 'body'=>$qm->m['plg_qhmpw']['err_admin_email']);
	}
	
	// 再発行URLの送信
	if (isset($post['mode']) && $post['mode'] == 'send')
	{
		$retarr = plugin_qhmpw_send_remind();
	}
	else if (isset($post['mode']) && $post['mode'] == 'set')
	{
		// ユーザー名、パスワードを変更後、認証画面へ移動
		// ftp情報をリセット
		$retarr = plugin_qhmpw_reset_password();
	}
	else if (isset($get['code']) && $get['code'] != '')
	{
		// code送信後、フォームを表示
		$retarr = plugin_qhmpw_form_reset();
	}
	else
	{
		// フォームの表示 登録メールアドレスとボタン
		$retarr = plugin_qhmpw_form_remind();
	}

	return $retarr;
}


/**
* パスワード再発行のフォーム表示
*/
function plugin_qhmpw_form_remind($error_msg = '')
{
	global $script, $vars;
	
	$qm = get_qm();

	$title = $qm->m['plg_qhmpw']['title'];
	$attention = $qm->m['plg_qhmpw']['attention'];
	$label_email = $qm->m['plg_qhmpw']['label_email'];
	$btn_send = $qm->m['plg_qhmpw']['btn_send'];
	
	if ($error_msg != '')
	{
		$error_msg = '<p style="color:#ff0000;font-size:12px;">'.$error_msg.'</p>';
	}
	
	$body = <<<EOD
<h2>{$title}</h2>
{$attention}
{$error_msg}
<form method="post" action="{$script}">
<p><label style="display:block;font-weight:bold;">{$label_email}</label>
<input type="text" name="qhmpw[email]" size="40" value=""  />
</p>
<p><input type="submit" value="{$btn_send}" style="font-size:16px" /></p>
<input type="hidden" name="mode" value="send" />
<input type="hidden" name="plugin" value="qhmpw" />
</form>

EOD;

	return array('msg'=>$title, 'body'=>$body);
}

function plugin_qhmpw_send_remind()
{
	global $script, $vars, $admin_email;

	// 登録メールアドレスチェック
	if  (trim($vars['qhmpw']['email']) != $admin_email)
	{
		$error = 'メールアドレスが登録されているものと異なります';
		return plugin_qhmpw_form_remind($error);
	}

	// qhm.ini.php の qhm_pw_str を変更
	require_once("./lib/Mcrypt.php");
	$code = $_SESSION['qhmsetting']['qhm_pw_str'] = ORMcrypt::get_key(50);

	if (exist_plugin('qhmsetting'))
	{
		call_user_func('plugin_qhmsetting_update_ini');
	}
	
	// メール送信
	require_once("./lib/simplemail.php");
	$smail = new SimpleMail();

	$smail->set_params('', $admin_email);
	$smail->subject = 'パスワードの再発行';
	$smail->to = array('name'=>'', 'email'=>$admin_email);

	$mailbody = '

パスワードの再発行をします。
下記のURLをクリックしてください。

'.$script.'?cmd=qhmpw&code='.$code.'

';
	$smail->send($mailbody);
	
	$msg = 'パスワードの再発行：メールを送信しました';
	$body = '登録メールアドレスにメールを送信しました。';
	
	return array('msg'=>$msg, 'body'=>$body);
}

/**
* パスワード再設定のフォームを表示
*/
function plugin_qhmpw_form_reset($error = '')
{
	global $script, $vars;
	global $username, $qhm_pw_str;

	$msg = 'パスワードの再設定';

	if ( ! isset($vars['code']) OR trim($vars['code']) === '' OR trim($vars['code']) !== $qhm_pw_str) {
		$body = 'パスワードの設定ができません。<br />再度、パスワードの再発行を行ってください。';
		return array('msg'=>$msg, 'body'=>$body);
	}

	$body = <<<EOD
<h2>パスワードの再設定</h2>
<p>新しいパスワードを入力してください</p>
{$error}
<form method="post" action="{$script}">
<table class="style_table" cellspacing="1" border="0">
	<tr>
		<th class="style_th">　ユーザー名　</th>
		<td class="style_td">　<input type="text" name="qhmpw[username]" size="18" value="{$username}" /></td>
	</tr>
	<tr>
		<th class="style_th">　新しいパスワード　</th>
		<td class="style_td">　<input type="password" name="qhmpw[password1]" size="18" /></td>
	</tr>
	<tr>
		<th class="style_th">　パスワード再入力　</th>
		<td class="style_td">　<input type="password" name="qhmpw[password2]" size="18"  /></td>
	</tr>
</table>

<p style="text-align:center"><input type="submit" value="設定する" style="font-size:16px" /></p>
<input type="hidden" name="code" value="{$code}" />
<input type="hidden" name="mode" value="set" />
<input type="hidden" name="plugin" value="qhmpw" />
</form>

EOD;

	return array('msg'=>$msg, 'body'=>$body);
}

function plugin_qhmpw_reset_password()
{
	global $script, $post, $username;
	global $auth_users;

	$error = '';

	//ユーザーの重複を探すために
	unset( $auth_users[ $username ] );
	
	if (isset($auth_users[$post['qhmpw']['username']]))
	{
		$error .= '他のユーザーと名前が重複しています<br />';
	}
	if ($post['qhmpw']['password1'] != $post['qhmpw']['password2'])
	{
		$error .= '新パスワードが一致しません<br />';
	}
	if( ! preg_match(ALLOW_PASSWD_PATTERN , $post['qhmpw']['password1']))
	{
		$error .= 'パスワードは、英数半角と一部の記号のみ(スペース不可)で入力してください<br />';
	}
	if( strlen($post['qhmpw']['password1']) < 6 )
	{
		$error .= 'パスワードは、6文字以上を設定してください<br />';
	}

	// 設定
	$uname = $_SESSION['qhmsetting']['username'] = $post['qhmpw']['username'];
	$_SESSION['qhmsetting']['passwd'] = '{x-php-md5}'.md5($post['qhmpw']['password1']);
	// ftp情報をリセットする
	$_SESSION['qhmsetting']['encrypt_ftp'] = '';
	$_SESSION['qhmsetting']['qhm_pw_str'] = '';
	
	if ($error != ''){
		return plugin_qhmpw_form_reset($error);
	}

	if (exist_plugin('qhmsetting'))
	{
		call_user_func('plugin_qhmsetting_update_ini');
	}
	else
	{
		return plugin_qhmpw_form_reset('設定ファイルに書き込みできませんでした');
	}

	session_destroy();
	$pwstr = '';
	$pwstr = str_pad($pwstr, count($post['qhmpw']['password1']), "*", STR_PAD_LEFT);
	
	$body = <<<EOD
<h2>パスワードの再設定完了</h2>
<p>パスワードの再設定を完了しました。</p>
<p>新しいユーザー名は、<span style="font-size:24px;">{$uname}</span>、パスワードは「{$pwstr}」です。</p>
<p>再度、ログインが必要となります。<a href="{$script}?plugin=qhmauth" style="font-weight:bold;background-color:#ff6;">ここをクリック</a>して下さい</p>
EOD;
	
	return array('msg'=>$msg, 'body'=>$body);


}

?>