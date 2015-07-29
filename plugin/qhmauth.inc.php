<?php
/**
* QHM Auth プラグイン
*/

function plugin_qhmauth_inline()
{
	global $script;

	$args = func_get_args();
	$text = trim(array_pop($args));
	$text = $text === '' ? 'QHM' : $text;

	$redirect_to = $script . '?cmd=qhmauth';
	return '<a href="'.h($redirect_to).'">'.$text.'</a>';
}

function plugin_qhmauth_convert()
{
	global $script;
	header('Location: '.$script.'?cmd=qhmauth');
	exit;
}


function plugin_qhmauth_action()
{
	global $script, $auth_method_type, $auth_users, $edit_auth_pages;
	$qm = get_qm();

	$page = isset($vars['page']) ? $vars['page'] : '';

	$msg = $qm->m['plg_qhmauth']['title'];


	// Checked by:
	$target_str = '';
	if ($auth_method_type == 'pagename') {
		$target_str = $page; // Page name
	} else if ($auth_method_type == 'contents') {
		$target_str = join('', get_source($page)); // Its contents
	}

	$user_list = array();
	foreach($edit_auth_pages as $key=>$val)
		if (preg_match($key, $target_str))
			$user_list = array_merge($user_list, explode(',', $val));

	if (empty($user_list)) return array('msg'=>$msg, 'body'=>"<p>{$qm->m['plg_qhmauth']['err_pkwk_ini']}</p>"); //TRUE; // No limit


	//--------------------------------------------
	//Customize from here
	//Session Auth instead of Basic Auth
	//Thanks & Refer SiteDev + AT by AKKO

	 if(array_key_exists($_SESSION['usr'],$auth_users)){
		return array('msg'=>$msg, 'body'=>"<p>". $qm->replace('plg_qhmauth.err_has_auth', $_SESSION['usr'], $script). "</p>");

		//return TRUE;
	}

    $fg = FALSE;

	$fg = ss_chkusr($qm->m['plg_qhmauth']['title'],$auth_users);
	if($fg){
		$_SESSION['usr'] = $_POST['username'];

		header( 'Location: '.$script );
		exit;
	}

	auth_catbody($msg, $qm->replace('plg_qhmauth.err_deny', $script));
	exit;
}
