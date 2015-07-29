<?php

require_once( "config.php" );
require_once( "cheetan/cheetan.php" );

function action( &$c )
{	
	set_menu($c);
	$c->set('_page_title','SWFUの設定');

	$admin_org = array();
	$admin_org = $c->admin->find('','id ASC');
	$config = $c->admin->getConfig();
	$c->set("settings", $admin_org);

	// 変更処理
	if (isset($_POST['set'])) {
		$admin = $c->data['admin'];
		$errmsg = "";
		$setdata = array();
		foreach ($admin_org as $row) {
			$name = $row['name'];
			if (isset($admin[$name])) {
				$id = $row['id'];
				$value = (isset($admin[$name])) ? $admin[$name] : "";
				switch ($name) {
					case 'overwrite':
						$errmsg .= $c->v->notempty($value, $row['jname'].'を選択してください');
						break;
					case 'recent_page':
					case 'recent_file':
						$errmsg .= $c->v->notempty($value, $row['jname'].'を入力してください');
						$errmsg .= $c->v->number($value, $row['jname'].'は半角数字で入力してください<br />');
						break;
					case 'list_num':
						$errmsg .= $c->v->notempty($value, $row['jname'].'を入力してください');
						$errmsg .= $c->v->number($value, $row['jname'].'は半角数字で入力してください<br />');
						if ($errmsg == "") $errmsg .= ($value < 1) ? $row['jname'].'は1以上を設定してください<br />' : '';
						break;
					case 'list_cols':
						$errmsg .= $c->v->notempty($value, $row['jname'].'を入力してください');
						$errmsg .= $c->v->number($value, $row['jname'].'は半角数字で入力してください<br />');
						if ($errmsg == "") $errmsg .= ($value < 1) ? $row['jname'].'は1以上を設定してください<br />' : '';
						break;
				}
				$setdata[$name] = array('id'=>$id, 'value'=>$value);
			}
		}
		
		if ($errmsg == "") {
			if (isset($setdata['list_num']) && isset($setdata['list_cols'])) {
				$num = (int)$setdata['list_num']['value'];
				$col = (int)$setdata['list_cols']['value'];
				$setdata['list_num']['value'] = $num * $col;
			}
			foreach ($setdata as $row) {
				if ($c->admin->update($row) == true) {
				}
				else {
					$errmsg .= "更新に失敗しました。【id=".$id.", value=".$value."】<br />";
				}
			}
		}
		if ($errmsg == "") {
			$c->redirect("admin_settings.php");
			return;
		}
		
		$c->set("admin", $admin);
	}
	else {
		if (isset($c->data['admin'])) {
			// 戻るボタンなど
			$admin = $c->data['admin'];
			$c->set( "admin", $admin );
		}
		else {
			$num = (int)$config['list_num'];
			$col = (int)$config['list_cols'];
			$config['list_num'] = ($num % $col == 0) ? $num / $col : $num / $col + 1;
			
			$c->set( "admin", $config);
		}
	}

	if ($errmsg != "") $errmsg = '<p style="color:red">'.$errmsg."</p>";
	$c->set( "errmsg", $errmsg );

}

?>
