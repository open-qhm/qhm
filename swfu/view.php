<?php
require_once( "config.php" );
require_once( "cheetan/cheetan.php" );

function action( &$c )
{
	set_menu($c);

	//表示
	if(isset($_GET['id']) && $img = $c->image->findone('$id=='.$_GET['id']))
	{
		$c->set('image', $img);
		$c->set('_page_title',$img['name'].'詳細');
	}
	else{
		$c->set('_page_title','見つかりません');
	}

	if(isset($_SESSION['swfu']['page_name']))
	{
		$page = $_SESSION['swfu']['page_name'];
		$c->set('page_name', $page);

		$images = $c->image->find('$page_name=="'.$page.'"','created desc');
		$c->set('images',$images);
	}
	
	//apikey
	$apikey = $c->admin->regenerateApiKey();
	$c->set('apikey', $apikey);
	
	//更新の確認
	if( isset($_POST['confirm']) )
	{
		$c->set('id', $_POST['id']);
		$c->set('name', $_POST['name']);
		$c->set('description', $_POST['description']);
		$c->set('page_name', $_POST['page_name']);
		
		$labels = explode(',',$_POST['label']);
		foreach($labels as $k=>$v)
			$labels[$k] = trim($v);
		$c->set('label', implode(',',$labels));

		//file name check
		$cur_rs = $c->image->findone('$id=="'.$_POST['id'].'"');
		if($cur_rs['name']!=$_POST['name'])
		{
			if( !preg_match('/^[a-zA-Z0-9._-]+$/', $_POST['name']) )
			{
				$update_error .= 'ファイル名は、英数半角以外使えません<br />';
				$c->set('notice_name','<span style="color:red">←</span>');
			}
			if( $c->image->findone('$name=="'.$_POST['name'].'"') )
			{
				$update_error .= '同じファイル名が、存在します<br />';
				$c->set('notice_name','<span style="color:red">←</span>');
			}
			
			$matches = array();
			preg_match('/^(.*)\.(.*)$/', $_POST['name'], $matches);
			$new_ext = $matches[2];
			preg_match('/^(.*)\.(.*)$/', $cur_rs['name'], $matches);
			$cur_ext = $matches[2];
			
			if($cur_ext != $new_ext)
			{
				$update_error .= '拡張子は、変更しないで下さい<br />';
				$c->set('notice_name','<span style="color:red">←</span>');
			}
		}
		$c->set('update_error', $update_error);
			
		$c->setViewFile('ctp/image_confirm.html');
		return ;
	}
	//更新を実行
	if( isset($_POST['update']) )
	{
		$old = $c->image->findone('$id=="'.$_POST['id'].'"');
		$new = array(
			'id'=> $_POST['id'],
			'name'=> $_POST['name'],
			'description'=> $_POST['description'],
			'page_name'=> $_POST['page_name'],
			'label'=> $_POST['label'],
			'created'=>time(),
		);
	
		$c->image->update($new);
		if($old['name']!=$new['name'])
			rename(SWFU_DATA_DIR.$old['name'], SWFU_DATA_DIR.$new['name']);
			
		$c->redirect('view.php?id='.$_POST['id']);
	}
	//キャンセル
	if( isset($_POST['cancel']) )
	{
		$c->redirect('view.php?id='.$_POST['id']);
	}

	//ファイルの上書き
	if (isset($_POST['replace']))
	{	
		if (!isset($_FILES["newfile"]) 
		|| !is_uploaded_file($_FILES["newfile"]["tmp_name"]) 
		|| $_FILES["newfile"]["error"] != 0) {
			$up_errmsg = 'このファイルはアップロードできません【'.$_FILES["newfile"]["error"].'】';
			$c->set('up_errmsg','<span style="color:red">'.$up_errmsg.'</span><br />');
			return;
		}

		$old = $c->image->findone('$id=="'.$_POST['id'].'"');
		$upload_file = $old['name'];
		$up_parts = pathinfo($upload_file);
		
		$newfile = $_FILES['newfile']['name'];
		$new_parts = pathinfo($newfile);

		if ($new_parts['extension'] == $up_parts['extension']) {
			$upload_file = SWFU_DATA_DIR.$upload_file;

			move_uploaded_file($_FILES['newfile']['tmp_name'], $upload_file);
			chmod($upload_file, 0666);

			$c->flash('view.php?id='.$_POST['id']);
			return;
		}
		else {
			$up_errmsg = '上書きするファイルと元ファイルのファイル拡張子が違います';
			$c->set('up_errmsg','<span style="color:red">'.$up_errmsg.'</span><br />');
			return;
		}
	}

	//削除
	if( isset($_GET['delete']) )
	{
		$fname = $_GET['delete'];
		$img = $c->image->findone('$name=="'.$fname.'"');
		$c->image->del('$id=="'.$img['id'].'"');
		
		if(file_exists(SWFU_DATA_DIR.$fname))
			unlink(SWFU_DATA_DIR.$fname);
		
		if(isset($_SESSION['swfu']['page_name'])){
			$c->redirect('list.php?page='.rawurlencode($_SESSION['swfu']['page_name']) );
		}
		else{
			$c->redirect('index.php');
		}
	}
	
	//ダウンロード
	if( isset($_GET['dl']) ){
		$fname = $_GET['dl'];
		
		$fp = fopen(SWFU_DATA_DIR.$fname, "rb");
	
		header("Cache-Control: public");
		header("Pragma: public");
		header("Accept-Ranges: none");
		header("Content-Transfer-Encoding: binary");
		header("Content-Disposition: attachment; filename={$fname}");
		header("Content-Type: application/octet-stream; name=$fname");
		
		fpassthru($fp);
		fclose($fp);
	
		exit();
	}
}
?>