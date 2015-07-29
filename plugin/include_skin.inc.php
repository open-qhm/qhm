<?php
/**
 *   QHM Include Design Plugin
 *   -------------------------------------------
 *   plugin/include_skin.inc.php
 *   
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 
 *   modified :
 *   
 *   指定したデザインテンプレートを使用します。
 *   
 *   Usage :
 *   
 */

function plugin_include_skin_convert()
{
	global $vars, $include_skin_file_path;
	$qm = get_qm();
	
	$args = func_get_args();
	if(count($args)<1){
		return $qm->replace('fmt_err_cvt', 'include_skin', $qm->m['plg_include_skin']['err_usage']);
	}
	
	$skin_file = array_pop($args);
	
	if( file_exists('skin/hokukenstyle/'.$skin_file) )
	{
		$include_skin_file_path = $skin_file;	
	}
	else
	{
		return $qm->replace('fmt_err_cvt', 'include_skin', $qm->replace('plg_include_skin.err_not_found', $skin_file));
	}
}
?>
