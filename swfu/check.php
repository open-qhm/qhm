<?php
	require_once( "config.php" );
	require_once( "cheetan/cheetan.php" );
	
function action( &$c )
{
	$c->set('_page_title','ファイルチェック');
	set_menu($c);
	
	$dir_obj = dir(SWFU_DATA_DIR);
	
	//make image list (and del nofile data)
	$log_del[] = array();
	$imgs[] = array();
	$tmp_imgs = $c->image->find();
	foreach($tmp_imgs as $k=>$v){
		//存在しないファイルを削除
		if( !file_exists(SWFU_DATA_DIR.$v['name']) ){
			$c->image->del('$id=='.$v['id']);
			$log_del[] = $v['name'];
		}
		else{
			$imgs[$v['name']] = $k;
		}
	}
	$c->set('log_del',$log_del);

	$log[] = array();
	while( $entry = $dir_obj->read() )
	{
		if( !is_dir(SWFU_DATA_DIR.$entry) && $entry{0}!='.' && !isset($imgs[$entry]))
		{
			$stat = stat(SWFU_DATA_DIR.$entry);
			$name = $entry;
			$time = $stat['mtime'];
			$size = $stat['size'];
			
			$c->image->regist($name, $time, $size);
			$log[] = $name;
		}
	}
	
	$c->set('log',$log);
}
?>