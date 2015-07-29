<?php

// ---------------------------------------------
// ----------------------------------------------- 


function plugin_shiftjis_convert()
{
	global $shiftjis, $vars;
	$qm = get_qm();

	//edit auth check
    $editable = edit_auth($vars['page'], FALSE, FALSE);
    if($editable){
    	return $qm->m['plg_shiftjis']['ntc_admin'];
    }
    else{
		$shiftjis = TRUE;
		return "";
	}
}


?>
