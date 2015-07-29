<?php

/***************************************************************************
 *                         QHM Plugin
 *                    ------------------------------
 *   Filename:             close.inc.php
 *   Project:              hokuken lab.
 *   Company:              hokuken lab.
 *   Copyright:            (C) 2007 hokuken lab.
 *   Website:              http://www.hokuken.com
 *   Version:              1.0
 *   Build Date:           2008-04-02
 *
 *   If you find bugs/errors/anything else you would like to point to out
 *   to us please feel free to contact us.
 *
 *   What it does:
 *   showing message on close
 *
 ***************************************************************************/

function plugin_close_convert()
{
	global $vars, $script;
	$qm = get_qm();
	$qt = get_qt();
	
    $page = isset($vars['page']) ? $vars['page'] : '';
    
    //キャッシュ無効
	$qt->enable_cache = false;
    
	$title = $qm->m['plg_close']['title'];
	$msg =$qm->m['plg_close']['ntc'];
	
	//parse arguments
    $args = func_get_args();
	$num = count($args);
	
	if($num == 0){
		//do nothing
	}
	else if($num == 1){
		$title = array_pop($args);
	}
	else{
		list($title, $msg) = $args;
	}
	        
    $editable = edit_auth($page, FALSE, FALSE);
    if($editable){
    	return $qm->m['plg_close']['ntc_admin'];
    }
    else{        
		force_output_message($title, '', $msg);
	}
}

?>
