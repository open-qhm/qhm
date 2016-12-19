<?php
/**
 *   Download Executer
 *   -------------------------------------------
 *   plugin/dlexec.php
 *
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *
 *   created  :
 *   modified : 2011-05-20
 *
 *   複数ファイルのダウンロードや、ダウンロード中にも別ページが見られるように、
 *   別セッションでダウンロードを実行するためのスクリプト
 *
 *   Usage :
 *
 */

//error handling
error_reporting(0);
ini_set('display_errors', 'Off');

//カレントディレクトリを、index.phpに変更
$qhm_path = dirname(dirname(__FILE__));
chdir($qhm_path);

//ライブラリの読み込み
require($qhm_path.'/pukiwiki.ini.php');
require($qhm_path.'/lib/func.php');
require($qhm_path.'/lib/qhm_message.php');
require($qhm_path.'/lib/simplemail.php');

if( file_exists('lib/qdmail.php') ){
	require_once('lib/qdmail.php');
}
if( file_exists('lib/qdsmtp.php') ){
	require_once('lib/qdsmtp.php');
}

/********************************************
* Main
*********************************************/

$key = md5( file_get_contents('qhm.ini.php') );
download($key);



//---------------------------------
// 関数宣言
//---------------------------------

function download($auth_key){

	global $downloadable_path;
	$qm = get_qm();

	$filename = isset($_GET['filename']) ? $_GET['filename'] : '';
	$email    = isset($_GET['email'])    ? $_GET['email']    : '';
	$title    = isset($_GET['title'])    ? $_GET['title']    : '';
	$key      = isset($_GET['key'])      ? $_GET['key']      : '';
	$page     = isset($_GET['refer'])    ? $_GET['refer']    : '';

	$filename = urldecode($filename);
	$pathinfo  = pathinfo($filename);
	$downloadable_path_array = explode(";", $downloadable_path);

	//validate
	$wikifile = 'wiki/'. encode($page) . '.txt';
	$source = file_exists($wikifile) ? file_get_contents($wikifile) : '';

	if(! in_array($pathinfo['dirname'], $downloadable_path_array, true))
	{
		header('HTTP/1.1 403 Forbidden');
		error_msg('Error : Invalid access');
		exit;
	}
	if ($page === '' OR ! preg_match('/&dl(?:button|link)\('. preg_quote($filename, '/').'(?:,|\))/', $source))
	{
		header('HTTP/1.1 403 Forbidden');
		error_msg('Error : Invalid reference');
		exit;
	}
	if($key != $auth_key)
	{
		header('HTTP/1.1 403 Forbidden');
		error_msg('Error : Invalid access');
		exit;
	}
	if($filename == '')
	{
		header('HTTP/1.1 404 Not Found');
		error_msg('Error : file does not exists');
		exit;
	}
	else
	{
		$fp = fopen($filename, 'rb');
		if($fp == FALSE)
		{
			fclose($fp);
			error_msg('Error : file does not exists');
			exit;
		}
	}

	//send mail
	if($email != ''){
        dl_sendmail($email, $filename, $title);
	}

    //get filename
	$tmparr = explode('?', basename($filename));
	$filebasename = $tmparr[0];

	header("Cache-Control: public");
	header("Pragma: public");
	header("Accept-Ranges: none");
	header("Content-Transfer-Encoding: binary");
	header("Content-Disposition: attachment; filename=$filebasename");
	header("Content-Type: application/octet-stream; name=$filebasename");

	fpassthru($fp);
	fclose($fp);



	exit();

}

function dl_sendmail($email, $filename, $title){

    global $smtp_auth, $smtp_server, $google_apps, $google_apps_domain;
    $qm = get_qm();

    $xsubject = $title == ''? $qm->replace('plg_dlbutton.subject', ''): $title;

    $xmsg = $qm->replace('plg_dlbutton.mail_body', $filename);

    $xheader = "From: " . $email . "\n";
    $xparameter = "-f" . $email;


	//Mail send setting
	if($google_apps && preg_match('/.*'.$google_apps_domain.'$/',$email))
	{
		$mail = new Qdmail();
		$mail -> smtp(true);

		$param = array(
			'host'=>'ASPMX.L.GOOGLE.com',
			'port'=> 25,
			'from'=>$email,
			'protocol'=>'SMTP',
			'user'=>'root@'.$google_apps_domain, //SMTPサーバーのユーザーID
			'pass' =>$passwd, //SMTPサーバーの認証パスワード
		);
		$mail -> smtpServer($param);

		$mail ->to($email);
		$mail ->subject($xsubject);
		$mail ->from($email);
		$mail ->text($xmsg);
		$return_flag = $mail ->send();
	}
	else
	{
		$mail = new SimpleMail();
		$mail->set_params('', $email);
		$mail->set_to('', $email);
		$mail->set_subject($xsubject);
		$mail->send($xmsg);
	}

}

function error_msg($msg)
{
	header('Content-Type:text/html;charset=utf-8');
	echo $msg;
}
