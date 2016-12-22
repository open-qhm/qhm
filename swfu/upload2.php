<?php
/**
 *   Upload Receiver for raw input:file
 *   -------------------------------------------
 *   upload2.php
 *
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2011-05-16
 *   modified :
 *
 *   Description
 *
 *   Usage :
 *
 */

function is_secure(){
	return true;
}

require_once('config.php');
require_once( "cheetan/cheetan.php" );
function action( &$c )
{

	if (!isset($_FILES['file'])) {
		$c->redirect('index.php');
		exit;
	}

	$config = $c->admin->getConfig();
	$overwrite = $config['overwrite'];

	$file = $_FILES['file'];
	$upload_name = $file['name'];

	//モード
	$mode = isset($_POST['mode'])? $_POST['mode']: 'ajax';
	switch ($mode)
	{
		case 'raw':
			$mode = 'raw';
			break;
		default:
			$mode = 'ajax';
	}

	// ファイルをチェックする。スクリプトを実行可能なファイルは許可しない
	if ( ! is_valid_file_for_upload($upload_name)) {
		header('HTTP/1.1 400 Bad Request');
		echo 'Cannot upload script file';
		exit(0);
	}

	//画像の説明
	$text = '画像の説明';
	$page = isset($_POST['page'])? $_POST['page']: '';

	if( preg_match('/^[-_.+a-zA-Z0-9]+$/', $upload_name ) ){
		if ( ! $overwrite)
		{
			$upload_name = numbering_filename($upload_name);
		}
		$upload_file = SWFU_DATA_DIR . $upload_name;
		$fname = $upload_name;
	}
	else
	{
		if( !preg_match('/([^.]+)\.(.*)$/', $upload_name, $matches) ){
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

	move_uploaded_file($file['tmp_name'], $upload_file);
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

	if ($overwrite)
	{
		if ($image = $c->image->findoneby('name', $fname))
		{
			unset($insert_img_data['description'], $insert_img_data['created']);
			$insert_img_data['id'] = $image['id'];
			$c->image->update($insert_img_data);
		}
		else
		{
			$c->image->insert($insert_img_data);
		}
	}
	else
	{
		$c->image->insert($insert_img_data);
	}
	$last = $c->image->findoneby('name', $fname);
	if ($last) {
		$last_id = $last['id'];
	}
	$buttons = get_buttons($insert_img_data, false);

	$fpath = SWFU_DATA_DIR . $fname;
	$preview = '';
	if (preg_match('/^image\//', $file['type'])) {

		$imgarr = getimagesize($fpath);
		$imgwidth = $imgarr[0];
		$imgheight = $imgarr[1];
		$longside = max(array($imgwidth, $imgheight));
		if ($longside > PREVIEW_SIZE) {
			$ratio = PREVIEW_SIZE / $longside;
			$prvwidth = ceil($imgwidth * $ratio);
			$prvheight = ceil($imgheight * $ratio);
		} else {
			$prvwidth = $imgwidth;
			$prvheight = $imgheight;
		}

		$preview = "{$prvwidth}x{$prvheight}";//WIDTHxHEIGHTの形式で返す
	}

	$size = format_bytes($stat['size'], 0);

	if ($mode == 'ajax')
	{
		header("Content-Type: application/json; charset=UTF-8");
		echo '{"name":"'. $fname .'","path":"'. $fpath .'","preview":"'. $preview .'","text":"'. h($text) .'","type":"'. $file['type'] .'","size":"'. $size .'","buttons":"'. addcslashes($buttons, '"') .'"'. (isset($last_id)? ",\"id\":$last_id": '') .'}';

		exit;
	}
	// mode:raw
	else
	{
		$c->set('fname', $fname);
		$c->set('image', $last);
		$c->set('page_name', $page);
		$c->SetViewFile('ctp/upload.html');

	}

}

?>
