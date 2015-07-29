<?php
class CImage extends CModel
{
	var $table			= "data/image.txt";
	var $validatefunc	= array(
							"name" => "notempty",
							);
	var $validatemsg	= array(
							"name" => "ファイル名を入力してください<br />",
							);
							
	function regist($name, $time='', $size='')
	{
		if(!file_exists(SWFU_DATA_DIR.$name))
			return false;
		
		if($time=='' || $size==''){
			$stat = stat(SWFU_DATA_DIR.$name);
			$time = $stat['mtime'];
			$size = $stat['size'];
		}
		
		$d = array(
			'name'=>$name,
			'created'=>$time,
			'size'=>$size,
			'description'=>'画像の説明',
		);
		
		return $this->insert($d);
	}
}
?>