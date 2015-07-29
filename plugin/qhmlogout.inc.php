<?php


function plugin_qhmlogout_action()
{
	global $script;
	$qm = get_qm();
	
	$msg = $qm->m['plg_qhmlogout']['title'];
	ss_auth_logout();
	
	if( isset($_SESSION['temp_design']) )
		unset($_SESSION['temp_design']);

	return array('msg'=>$msg, 'body'=> $qm->replace('plg_qhmlogout.done', $script));
}


?>
