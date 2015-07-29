<?php
/**
 *   Main Visual Setting Plugin
 *   -------------------------------------------
 *   main_visual.inc.php
 *   
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2010-09-13
 *   modified :
 *   
 *   hokukenstyleデザインで、main_visualを利用するときに使うプラグイン
 *  
 *   Usage : 
 *     #mainvidual(imgurl|PageName[,title]);
 *
 */

function plugin_main_visual_convert()
{

	$args = func_get_args();
	//error
	if (count($args) == 0) {
		return '';
	}
	$url = get_file_path($args[0]);
	$title = count($args)>1 ? htmlspecialchars($args[1]) : htmlspecialchars($url);

	$align = 'center';
	if( isset($args[2]) ){
		switch( $args[2] ){
			case 'right' : $align = 'right'; break;
			case 'left' : $align = 'left'; break;
		}
	}
	
	$mv = '';
	
	if( preg_match('/.*\.(png|gif|jpg|jpeg)/i', $url ) ){

		$fp = fopen($url, 'r');
		if($fp){
			$mv = '<div style="text-align:'.$align.'"><img src="'.$url.'" title="'.$title.'" alt="'.$title.'" /></div>';
		}
		fclose($fp);
	}
	else if( is_page($url) ){
		$mv = convert_html( get_source($url) );	
	}
	
	if($mv != ''){
		$qt = get_qt();
		$qt->setv('main_visual', "<div id=\"main_visual\">\n".$mv."\n</div>\n");
	}
	
	return '';
}
