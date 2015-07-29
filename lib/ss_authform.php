<?php
//-------------------------------------------------
// QHM Auth system
//
// this system is able to work on CGI PHP & Module PHP
// Thanks & Reffer from SiteDev+AT by AKKO
//

// Output message
// args : $message
// retrun : none
//<a href="{$_SERVER['PHP_SELF']}">re try</a>

function ss_msg($message) {
	global $vars;

	$script_url = qhm_get_script_path();

	$qm = get_qm();
	$retry_label = $qm->m['ss_authform']['retry_label'];
	$body = <<<EOD
<div class="box">
<p>{$message}</p>
<a href="{$script_url}?cmd=qhmauth">$retry_label</a>
</div>
EOD;

	//print $body;
	$tmp = $vars['page'];
	$vars['page'] = "certification" ;
	auth_catbody($qm->m['ss_authform']['page_title'], $body, true);
	$vars['page'] = $tmp;
}

// Output Login Form
function ss_auth_loginform($title) {

    global $vars, $script, $script_ssl, $reg_exp_host, $session_save_path;
    $title .= isset($_SESSION['usr']) ? (' : ' . $_SESSION['usr']) : '';

    $qm = get_qm();
    $qt = get_qt();

	// Output Form
	$tmp = $vars['page'];
	$vars['page'] = "Page Edit Authorization" ;

	$addjs = '
<script type="text/javascript">
	var usr = document.getElementById("username");
	usr.focus();
	usr.select();
</script>
';

$contents = <<<EOD
<form method="post">
<div class="box">
<label for="username">{$qm->m['username']}</label>
<input type="text" name="username" tabindex="1" id="username" style="" /><br />
<label for="password">{$qm->m['password']}</label>
<input type="password" name="password" tabindex="2" id="password" style="" /><br />

<input type="hidden" name="keep" value="0" />
<input type="submit" name="send" value="{$qm->m['ss_authform']['btn_login']}" tabindex="3" />

</div>
</form>

{$addjs}
EOD;

	//セッションの書き込み権限のチェック
	$sspath = session_save_path();
	$sspath = $sspath == '' ? '/tmp' : $sspath;
	$ss_write = is_writable( $sspath );

	$error_ss = '';
	if ($session_save_path != '') {
			$error_ss = '<div id="sessionerror" style="border:2px solid #66AACC;background-color:#EEEEFF;margin:5px 0;"><p>'. $qm->replace('ss_authform.ntc_session_save_path', $script.'?plugin=qhmsetting&phase=sssavepath&mode=form'). '</p></div>';
	}

	if($ss_write != true){

		if(! isset($vars['chksession']) ){

			//セッションチェックのために、sessionをセットして移動させる。
			$t = time();
			$_SESSION['chksession'] = $t;

			$cur_url = $_SERVER['REQUEST_URI'];
			$url = $cur_url . ( strpos($cur_url,'?') ? '&' : '?FrontPage&' ) . 'chksession='.$t;

			header('Content-Type: text/html;charset=utf-8');
			echo '<html><head><meta http-equiv="Refresh" content="0;url='.$url.'"/></head><body><p><a href="'.$url.'">please click here</a></p></body></html>';
			exit;
		}
		else{

			if( $vars['chksession'] == $_SESSION['chksession'] ){
				//session OK!
			}
			else{
				$error_ss = '<div id="sessionerror" style="border:2px solid #FF3300;background-color:#FFEEEE;margin:5px 0;"><p style="color:red">'. $qm->replace('ss_authform.err_session_writable', $script.'?plugin=qhmsetting&phase=sssavepath&mode=form'). '</p></div>';
			}
		}

	}

	$error_path = '';
//	catbody("Page Edit Authorization","Page Edit Authorization",$body);
	auth_catbody($qm->m['ss_authform']['page_title'], $contents.$error_path.$error_ss);
	$vars['page'] = $tmp;
}

function ss_getURL( $pURL ) {
   $_data = null;
   if( $_http = fopen( $pURL, "r" ) ) {
      while( !feof( $_http ) ) {
         $_data .= fgets( $_http, 1024 );
      }
      fclose( $_http );
   }
   return( $_data );
}

function ss_auth_start(){
    if(! isset( $_SESSION['ct']) ){
		secure_session_start();
    	if(! isset($_SESSION['ct']) ) $_SESSION['ct']=0;
	}
    if(! isset($_SESSION['login']) ) $_SESSION['login']='';
    if(! isset($_SESSION['usr']) ) $_SESSION['usr']='';
}

function ss_auth_logout(){
  session_destroy();
}

function ss_chkusr($title , $users ){

	global $script;
	global $login_log;

	$qm = get_qm();

	// キャンセルなら、トップへリダイレクト
	if(isset($_POST['send']) && $_POST['send'] == $qm->m['ss_authform']['btn_cancel']){//この比較は非推奨
		header('Location: '.$script);
		exit;
	}
	// 認証の場合
	else if( isset($_POST['send']) && $_POST['send'] == $qm->m['ss_authform']['btn_login']){

		$user = isset($_POST['username']) ? $_POST['username'] : '';
		$pass = isset($_POST['password']) ? $_POST['password'] : '';

		// User, Passwordをチェック
		$auth = (array_key_exists($user, $users)
					&& check_passwd($pass, $users[$user]));

		//認証OK、NGに応じた処理
		if ( $auth ){
			$_SESSION['usr'] = $user;

			if (ss_admin_check()) {
				$d = dir(CACHEQHM_DIR);
				while (false !== ($entry = $d->read())) {
					if($entry!='.' && $entry!='..') {
						$entry = CACHEQHM_DIR.$entry;
						if(file_exists($entry)) {
							// cacheqhmディレクトリにある3日前の一時ファイルを削除
							if (mktime(date("H"),date("i"),date("s"),date("n"),date("j")-3,date("Y")) > time(fileatime($entry)) ) {
								unlink($entry);
							}
						}
					}
				}
			   $d->close();
			}
			return TRUE;
		}
		else {

			// カウントして、3回以上試行したらエラーを出す
			$_SESSION['ct'] = $_SESSION['ct'] + 1;
		    if( $_SESSION['ct'] > 3)   {
				$_SESSION['ct'] = 0;
		        return FALSE;
		    }

			ss_msg($qm->m['ss_authform']['err_auth']);
			exit;
		}
	}
	// 通常
	else{
		ss_auth_loginform($title);
		exit;
	}
}

function auth_catbody($title, $contents, $retry=false){
	global $script,$default_script,$admin_email;

	$qm = get_qm();

	// Output HTTP headers
	pkwk_common_headers();
	header('Cache-control: no-cache');
	header('Pragma: no-cache');
	header('Content-Type: text/html; charset=' . CONTENT_CHARSET);

	// Output HTML DTD, <html>, and receive content-type
	if (isset($pkwk_dtd)) {
		$meta_content_type = pkwk_output_dtd($pkwk_dtd);
	} else {
		$meta_content_type = pkwk_output_dtd();
	}

	$qv = QHM_VERSION;
	$pv = S_VERSION;
	$rv = QHM_REVISION;

	$server_port = SERVER_PORT;
	$script_name = SCRIPT_NAME;
	$http_host = $_SERVER['HTTP_HOST'];
	$script_url = qhm_get_script_path();

	$dispjs = 'scriptErr.visibility = "hidden";';
	if ($default_script != '') {
		$s_notice_msg = $qm->replace('ss_authform.ntc_set_script', $script_url. '?plugin=qhmsetting&phase=script&mode=form');
		$dispjs = <<< EOD
 			scriptErr.style.border = '2px solid #66AACC';
 			scriptErr.style.backgroundColor = '#EEEEFF';
 			scriptErr.innerHTML = '<p>$s_notice_msg</p>';
EOD;
	}

	$s_err_msg = $qm->replace('ss_authform.err_set_script', $script_url. '?plugin=qhmsetting&phase=script&mode=form');
	$jumphome_label = $qm->m['ss_authform']['jump_home'];
	$licence_msg = $qm->replace('ss_authform.licence', $qv, $rv, $pv);

	$error_login = 	'';
	if ($retry && $admin_email != '')
	{
		$error_login = $qm->replace('ss_authform.err_login', $script.'?plugin=qhmpw').'&nbsp;&nbsp;';
	}

	echo <<<EOD
<head>
 {$meta_content_type}
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="robots" content="NOINDEX, NOFOLLOW" />
 <title>{$title}</title>
 <script type="text/javascript">
 <!--
window.onload = function() {
 	var scriptErr = document.getElementById('scripterror');

 	// wrong script
 	if (location.hostname != '{$http_host}'
 			|| location.port != '{$server_port}'
 			|| location.pathname != '{$script_name}' ) {

 		var href = location.href.replace(location.search, '');

 		if(! href.match(/\.php$/)){
 			href += 'index.php';
 		}

 		if (href == '{$script}') {
 			{$dispjs}
 		}
 		else {
 			scriptErr.style.border = '2px solid #FF3300';
 			scriptErr.style.backgroundColor = '#FFEEEE';
 			scriptErr.innerHTML = '<p style="color:red">$s_err_msg</p>';
 		}
 	}
 	else {
 		scriptErr.visibility = "hidden";
 	}

 	// wrong session save path

 	return false;
}

//-->
</script>
<style>
.wrapper {
margin: 0 auto;
width: 300px;
}
.box {
border:1px #ccc solid;
padding:1em;
color:#666;
margin-top:15px;
font-size:14px;
border-radius: 10px;
-moz-border-radius: 10px;
-webkit-border-radius: 10px;
box-shadow: 2px 2px 8px #ccc;
-moz-box-shadow: 2px 2px 8px #ccc;
-webkit-box-shadow: 2px 2px 8px #ccc;
}
.box input[type=text], .box input[type=password] {
margin: 0;
font-size: 18px;
border: 1px solid #999;
border-radius: 5px;
-moz-border-radius: 5px;
-webkit-border-radius: 5px;
padding: 3px;
width: 260px;
}
.title {
font-size:24px;
font-weight: normal;
color: #666;
text-align:center;
}
body{
font-family: Arial, sans-serif;
}
label {
display:block;
margin-top: 1em;
}
input[type=submit], input[type=button] {
border-style:none;
display: inline-block;
color: #fff;
text-decoration: none;
-moz-border-radius: 6px;
-webkit-border-radius: 6px;
-moz-box-shadow: 0 1px 3px rgba(0,0,0,0.6);
-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.6);
text-shadow: 0 -1px 1px rgba(0,0,0,0.25);
border-bottom: 1px solid rgba(0,0,0,0.25);
position: relative;
cursor: pointer;
font-size: 18px;
padding: 4px 14px 4px;
background-color: #2981e4;
margin: 1em 0 1em;
}
input[type=submit]:hover {
background-color: #2575cf;
}

#footer {
text-align:right;font-size:12px;color:#999
}
#footer a {
font-size:12px;color:#666
}

</style>
</head>
<body>
<div class="wrapper">
<h2 class="title">QHM {$title}</h2>
{$contents}
<div id="scripterror"></div>

<div id="footer">
<p style="text-align:right;">{$error_login}<a href="{$script_url}" style="">{$jumphome_label}</a></p>
<p style="">{$licence_msg}</p>
</div>
</div>
</body>
</html>
EOD;

}

function secure_session_start()
{
	global $script, $script_ssl, $vars, $session_save_path;

	// ****************************************************
	// 共用SSLのおかしなサーバー変数に最大限対応するためのロジック
	//
	$vals = parse_url( (is_https() ?  $script_ssl : $script) );
	// ******************************************************

	// make session values
	if(TRUE){

		$domain = $vals['host'];

		if($domain != 'localhost' && $domain != '127.0.0.1'){
			if(isset($vals['port']))
			{
				$domain .= ':'.$vals['port'];
			}
			$dir = str_replace('\\', '', dirname( $vals['path'] ));
			$ckpath = ($dir=='/') ? '/' : $dir.'/';

			if( function_exists('ini_set') ){
				ini_set('session.use_trans_sid',0);
				ini_set('session.name', QHM_SESSION_NAME.strlen($ckpath));
				ini_set('session.use_only_cookies', 1);
				ini_set('session.cookie_path', $ckpath);
				ini_set('session.cookie_domain', $domain);
				ini_set('session.cookie_lifetime', 0);
			}
		}
	}

	$ssname = session_name();
	if(
		(UA_PROFILE=='keitai' && isset($vars['mobssid']) && $vars['mobssid']=='yes' )
		|| (UA_PROFILE=='keitai' && isset($vars[$ssname]) )
	){
		ini_set('session.use_only_cookies', 0);
		ini_set('session.use_trans_sid', 1);
	}

	if ($session_save_path != '') {
		session_save_path($session_save_path);
	}

	session_start();
}

function get_fingerprint()
{
   // Security SALT
   global $ss_salt, $script;

   $fingerprint = ($ss_salt == '') ? 'KAIENK8H3HEBBJU3HJCKIEIA8HFUNDAP763J' : $ss_salt;

   if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
       $fingerprint .= $_SERVER['HTTP_USER_AGENT'];
   }
   if ( ! empty( $_SERVER['HTTP_ACCEPT_CHARSET'] ) ) {
       $fingerprint .= $_SERVER['HTTP_ACCEPT_CHARSET'];
   }
   $fingerprint .= $script;
   return md5( $fingerprint );
}

function ss_admin_check($clear=false){
	global $username;

	if( isset($_SESSION['usr']) && ($_SESSION['usr']==$username) ){
		return true;
	}
	else{
		return false;
	}
}


/**
* SSL通信をしているか、可能な限りチェックするためのプログラム
* 多くの共用SSLのサーバー変数がめちゃくちゃだから必要
*
* 解説 : 独自SSLの場合、HTTPS = on、SERVER_PORT = 443 がセットされる
*  困るのはそれ以外のメチャクチャな設定の場合。通常のアクセスと識別できるようなロジックが必要
*/
function is_https(){

	//$scriptは、入れ替わっている可能性があるので、元の情報によってチェックする
	global $init_scripts;
	$script = $init_scripts['normal'];
	$script_ssl = $init_scripts['ssl'];


	// サーバー環境変数の中から、SSLを決定づけられる情報で判定
	$cond = array(
		'HTTPS' => 'on',
		'SERVER_PORT' => '443',
		'HTTP_X_FORWARDED_PROTO' => 'https', //例 : ロリポップ
	);

	foreach($cond as $k=>$v){
		if( isset($_SERVER[$k]) &&  $_SERVER[$k]==$v ){
			return true;
		}
	}



	// サーバー環境変数内に、SSL通信を決定づける情報がない場合、
	// おそらく、プロキシを使ってSSL通信をしていると考えられるため
	// HTTP_X_FORWARDED系変数を使って、SSL判定を行う
	//
	//
	if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && isset($_SERVER['HTTP_VIA']) ){
		$host = $_SERVER['HTTP_VIA'];
	}
	else if( isset($_SERVER['HTTP_X_FORWARDED_HOST']) ){
		$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
	}
	else if( isset($_SERVER['SERVER_NAME']) ){
		$host = $_SERVER['SERVER_NAME'];
	}
	else if( isset($_SERVER['HTTP_HOST']) ){
		$host = $_SERVER['HTTP_HOST'];
	}
	else{ //すべての変数が取れなければ、$script とする
		return preg_match('/^https:/', $script);
	}


	$ptrn = array(
		'SSL\d+.HETEML.JP', //ヘテムル
		'SS\d+.CORESSL.JP', //Coreserver
		'SS\d+.XREA.COM',   //XREA
	);

	$ptrstr = '/('.implode(')|(', $ptrn).')/';

	if( preg_match($ptrstr, strtoupper($host)) ){
		return true;
	}

	return false;
}

function get_session_params(){


	$params = array(
		'session.use_trans_sid',
		'session.name',
		'session.use_only_cookies',
		'session.cookie_path',
		'session.cookie_domain',
		'session.cookie_lifetime',
		'session.save_path',
	);

	$params = array_flip($params);
	foreach($params as $k=>$v){
		$params[$k] = ini_get($k);
	}

	return $params;
}


?>
