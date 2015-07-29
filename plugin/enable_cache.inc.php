<?php
/**
 *   QHM Enable Cache Plugin
 *   -------------------------------------------
 *   plugin/enable_cache.inc.php
 *   
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 
 *   modified : 2010-11-08
 *   
 *   Usage :
 *   
 */

// ---------------------------------------------
// ----------------------------------------------- 


function plugin_enable_cache_convert()
{
	global $vars;
	$qm = get_qm();
	$qt = get_qt();

	//edit auth check
    $editable = edit_auth($vars['page'], FALSE, FALSE);
	$v = func_get_args();
	if(isset($v[0]) && strtolower($v[0])=="false")
	{
		if ($editable) {
			return $qm->m['plg_enable_cache']['ntc_admin2'];
		} else {
    		$qt->enable_cache = 0;
    		return '';
		}
	}
	else {
		if ($editable) {
			return $qm->m['plg_enable_cache']['ntc_admin'];
		} else {
			$qt->enable_cache = 1;
			return '';
		}
	}
}


?>
