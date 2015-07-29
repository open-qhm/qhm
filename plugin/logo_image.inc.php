<?php
/**
 *   Setting Logo Image Plugin
 *   -------------------------------------------
 *   logo_image.inc.php
 *   
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2010-09-13
 *   modified : 2010-10-14 logo_title を設定できるように
 *   
 *   logo画像を変更できる
 *  
 *   Usage : 
 *     #logo_image(imgurl[,title]);
 *
 */

function plugin_logo_image_convert()
{

	$args = func_get_args();
	//error
	if (count($args) == 0) {
		return '';
	}
	$url = $args[0];
	$title = isset($args[1])? $args[1]: false;

	if( preg_match('/.*\.(png|gif|jpg|jpeg)/i', $url) ){

		$fp = fopen($url, 'r');
		if($fp){			
			$qt = get_qt();
			$qt->setv('logo_image', $url);
			
			if ($title) {
				$qt->setv('logo_title', $title);
			}
		}
		fclose($fp);
	}

	return null;
}

?>
