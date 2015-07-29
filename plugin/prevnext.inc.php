<?php
/**
 *   prevnextプラグイン
 *   -------------------------------------------
 *   prevnext.inc.php
 *   
 *   Copyright (c) 2009 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2010 4/5
 *   modified : 
 *   
 *   Description
 *    $custom_meta に、next、prevタグを入れる
 *
 *   Usage :
 *     #nextprev(Prevページ名,Nextページ名)
 */


function plugin_prevnext_convert()
{
	global $custom_meta, $script;
	
	$tag = '
<link rel="prev" href="%PREV%"> 
<link rel="next" href="%NEXT%">
';
	
	$args = func_get_args();
	$np = array_pad($args, 2, '');
	
	foreach($np as $k=>$v){
		$np[$k] = $script.'?'.rawurlencode($v);
	}

	$custom_meta .= str_replace(array('%PREV%','%NEXT%'), $np, $tag);

	return '';
}

?>
