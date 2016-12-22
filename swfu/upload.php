<?php

require_once('config.php');

$page_name = $_POST['PAGENAME'];
$overwrite = $_POST['OVERWRITE'];

if (isset($_POST["PHPSESSID"])) {
	session_id($_POST["PHPSESSID"]);
}
session_start();

if ( ! isset($_SESSION['usr'])) {
	header('HTTP/1.1 403 Forbidden');
	exit(0);
}

$upload_name = $_FILES['Filedata']['name'];

// 拡張子をチェックする .php は許可しない
if (pathinfo($upload_name, PATHINFO_EXTENSION) === 'php') {
	header('HTTP/1.1 400 Bad Request');
	exit(0);
}

if (!isset($_FILES["Filedata"])
		|| !is_uploaded_file($_FILES["Filedata"]["tmp_name"])
		|| $_FILES["Filedata"]["error"] != 0
)
{
	exit(0);
}
else
{ // ---------------------- upload success --------------------
	if( preg_match('/^[-_.+a-zA-Z0-9]+$/', $upload_name ) ){

		while(!$overwrite && file_exists(SWFU_DATA_DIR.$upload_name)){
			$upload_name = 's_'.$upload_name;
		}
		$upload_file = SWFU_DATA_DIR.$upload_name;
		$fname = $upload_name;
	}
	else
	{
		$matches = array();

		if( !preg_match('/[^.]+\.(.*)$/', $upload_name, $matches) ){
			echo 'invalid file name';
			exit(0);
		}

		$ext = $matches[1];
		$tmp_name = tempnam(SWFU_DATA_DIR, 'auto_');
		$upname = $tmp_name.'.'.$ext;

		rename($tmp_name, $upname);
		$upload_file = SWFU_DATA_DIR. basename($upname);
		$fname = basename($upname);
	}

	move_uploaded_file($_FILES['Filedata']['tmp_name'], $upload_file);
	chmod($upload_file, 0666);

	//regist db
	$stat = stat($upload_file);

	global $insert_img_data;
	$insert_img_data = array(
		'name'=>$fname,
		'description'=>'画像の説明',
		'created'=>$stat['mtime'],
		'size'=>$stat['size'],
		'page_name'=>$page_name,
	);

	//何か出力する必要あり
	echo "Flash requires that we output something or it won't fire the uploadSuccess event";
}


//-------- cheetan module ----------
function is_secure(){
	return false;
}
function is_session(){
	return false;
}
require_once( "cheetan/cheetan.php" );
function action( &$c )
{
	global $insert_img_data;
	$insert_img_data = $c->s->input_filter($insert_img_data);

	if(isset($insert_img_data)){
		$c->image->insert($insert_img_data);
	}
}

?>
