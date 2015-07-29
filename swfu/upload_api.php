<?php
/**
 *   Upload Receiver for Image Shrinker
 *   -------------------------------------------
 *   upload_api.php
 *
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2011-10-18
 *   modified :
 *
 *   Description
 *
 *   Usage :
 *
 */


$fp = fopen('data/log.txt', 'a');

fwrite($fp, date('Y-m-d H:i:s'). "\n---------------\n");

fwrite($fp, print_r($_POST, TRUE));


fwrite($fp, "\n\n");
fwrite($fp, print_r($_FILES, TRUE));


function is_secure(){
	return false;
}

require_once('config.php');
require_once( "cheetan/cheetan.php" );
function action( &$c )
{
	$config = $c->admin->getConfig();
	$overwrite = $config['overwrite'];

	$apikey = (isset($_POST['apikey']) && trim($_POST['apikey']))? trim($_POST['apikey']): FALSE;
	if ( ! $c->admin->apiKeyIsCorrect($apikey))
	{
file_put_contents('log.txt', print_r('API KEY ERROR', TRUE));
		die('APIキーが正しくありません');
	}
file_put_contents('log.txt', print_r('API KEY OK', TRUE));

	$name = (isset($_POST['name']) && trim($_POST['name']))? trim($_POST['name']): FALSE;

	//画像の説明
	$text = (isset($_POST['description']) && trim($_POST['description']))? trim($_POST['description']): '画像の説明';
	$page = isset($_POST['page'])? $_POST['page']: '';

file_put_contents('log.txt', print_r($text, TRUE));


	//添付ファイルの取得
	if ( ! isset($_FILES['img']))
	{
		die('画像がありません。');
	}
	$file = $_FILES['img'];

	if( $name !== FALSE && preg_match('/^[-_.+a-zA-Z0-9]+$/', $name ) ){
		if ( ! $overwrite)
		{
			$name = numbering_filename($name);
		}
		$upload_file = SWFU_DATA_DIR . $name;
		$fname = $name;
	}
	else
	{
		if( !preg_match('/([^.]+)\.(.*)$/', $name, $matches) ){
			echo 'invalid file name';
			exit(0);
		}
		$text = $matches[1];
		$ext = $matches[2];
		$tmp_name = tempnam(SWFU_DATA_DIR, 'auto-');
		$upname = "{$tmp_name}.{$ext}";

		rename($tmp_name, $upname);
		$upload_file = SWFU_DATA_DIR . basename($upname);
		$fname = basename($upname);
	}

$fp = fopen('data/log.txt', 'a');
fwrite($fp, date('Y-m-d H:i:s'). "\nimage path: {$upload_file}\n");
fclose($fp);

	move_uploaded_file($file['tmp_name'], $upload_file);
//	file_put_contents($upload_file, $imagedata);
	chmod($upload_file, 0666);

	//regist db
	$stat = stat($upload_file);

	$insert_img_data = array(
		'name'=>$fname,
		'description'=>$text,
		'created'=>$stat['mtime'],
		'size'=>$stat['size'],
		'page_name'=>$page,
	);
	$insert_img_data = $c->s->input_filter($insert_img_data);

	$c->image->insert($insert_img_data);

	echo 'success';

}

?>
