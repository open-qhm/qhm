<?php
/**
 *   QHM Change Encoding EUC-JP Plugin
 *   -------------------------------------------
 *   plugin/eucjp.inc.php
 *   
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 
 *   modified :
 *   
 *   This plugin makes the page EUC-JP encoding
 *   
 *   Usage :
 *   
 */

// ---------------------------------------------
// ----------------------------------------------- 


function plugin_eucjp_convert()
{
	global $eucjp, $vars;
	$qm = get_qm();

	//edit auth check
    $editable = edit_auth($vars['page'], FALSE, FALSE);
    if($editable){
    	return $qm->m['plg_eucjp']['ntc_admin'];
    }
    else{
		$eucjp = TRUE;
		return "";
	}
}


?>
