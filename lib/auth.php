<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: auth.php,v 1.19 2005/06/13 14:02:07 henoheno Exp $
// Copyright (C) 2003-2005 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version
//
// Authentication related functions

define('PKWK_PASSPHRASE_LIMIT_LENGTH', 512);

// Passwd-auth related ----

function pkwk_login($pass = '')
{
	global $adminpass;

	if (! PKWK_READONLY && isset($adminpass) &&
		check_passwd($pass, $adminpass)) {
		return TRUE;
	} else {
		sleep(2);       // Blocking brute force attack
		return FALSE;
	}
}

function check_passwd($pass, $storedhash)
{
	$scheme = '';
	
	if (preg_match('/^(\{.+\})(.*)$/', $storedhash, $matches)) {
		$scheme = & $matches[1];
		$hash   = & $matches[2];
	}
	
	if ($scheme === '{PHPASS}')
	{
		require_once(LIB_DIR . 'PasswordHash.php');
		$t_hasher = new PasswordHash(8, TRUE);

		return $t_hasher->CheckPassword($pass, $hash);
		
	}
	else
	{
		return pkwk_hash_compute($pass, $storedhash) == $storedhash;
	}

}

// Compute RFC2307 'userPassword' value, like slappasswd (OpenLDAP)
// $phrase : Pass-phrase
// $scheme : Specify '{scheme}' or '{scheme}salt'
// $prefix : Output with a scheme-prefix or not
// $canonical : Correct or Preserve $scheme prefix
function pkwk_hash_compute($phrase = '', $scheme = '{x-php-md5}', $prefix = TRUE, $canonical = FALSE)
{
	if (! is_string($phrase) || ! is_string($scheme)) return FALSE;

	if (strlen($phrase) > PKWK_PASSPHRASE_LIMIT_LENGTH)
		die('pkwk_hash_compute(): malicious message length');

	// With a {scheme}salt or not
	$matches = array();
	if (preg_match('/^(\{.+\})(.*)$/', $scheme, $matches)) {
		$scheme = & $matches[1];
		$salt   = & $matches[2];
	} else if ($scheme != '') {
		$scheme  = ''; // Cleartext
		$salt    = '';
	}

	// Compute and add a scheme-prefix
	switch (strtolower($scheme)) {

	// PHP crypt()
	case '{x-php-crypt}' :
		$hash = ($prefix ? ($canonical ? '{x-php-crypt}' : $scheme) : '') .
			($salt != '' ? crypt($phrase, $salt) : crypt($phrase));
		break;

	// PHP md5()
	case '{x-php-md5}'   :
		$hash = ($prefix ? ($canonical ? '{x-php-md5}' : $scheme) : '') .
			md5($phrase);
		break;

	// PHP sha1()
	case '{x-php-sha1}'  :
		$hash = ($prefix ? ($canonical ? '{x-php-sha1}' : $scheme) : '') .
			sha1($phrase);
		break;

	// LDAP CRYPT
	case '{crypt}'       :
		$hash = ($prefix ? ($canonical ? '{CRYPT}' : $scheme) : '') .
			($salt != '' ? crypt($phrase, $salt) : crypt($phrase));
		break;

	// LDAP MD5
	case '{md5}'         :
		$hash = ($prefix ? ($canonical ? '{MD5}' : $scheme) : '') .
			base64_encode(hex2bin(md5($phrase)));
		break;

	// LDAP SMD5
	case '{smd5}'        :
		// MD5 Key length = 128bits = 16bytes
		$salt = ($salt != '' ? substr(base64_decode($salt), 16) : substr(crypt(''), -8));
		$hash = ($prefix ? ($canonical ? '{SMD5}' : $scheme) : '') .
			base64_encode(hex2bin(md5($phrase . $salt)) . $salt);
		break;

	// LDAP SHA
	case '{sha}'         :
		$hash = ($prefix ? ($canonical ? '{SHA}' : $scheme) : '') .
			base64_encode(hex2bin(sha1($phrase)));
		break;

	// LDAP SSHA
	case '{ssha}'        :
		// SHA-1 Key length = 160bits = 20bytes
		$salt = ($salt != '' ? substr(base64_decode($salt), 20) : substr(crypt(''), -8));
		$hash = ($prefix ? ($canonical ? '{SSHA}' : $scheme) : '') .
			base64_encode(hex2bin(sha1($phrase . $salt)) . $salt);
		break;

	// PHPASS - http://www.openwall.com/phpass/
	case '{phpass}'      :
		require_once(LIB_DIR . 'PasswordHash.php');
		$t_hasher = new PasswordHash(8, TRUE);
		$hash = ($prefix ? ($canonical ? '{PHPASS}' : $scheme) : '') . $t_hasher->HashPassword($phrase);
		break;

	// LDAP CLEARTEXT and just cleartext
	case '{cleartext}'   : /* FALLTHROUGH */
	case ''              :
		$hash = ($prefix ? ($canonical ? '{CLEARTEXT}' : $scheme) : '') .
			$phrase;
		break;


	// Invalid scheme
	default:
		$hash = FALSE;
		break;
	}

	return $hash;
}


// Basic-auth related ----

// Check edit-permission
function check_editable($page, $auth_flag = TRUE, $exit_flag = TRUE)
{
	global $script, $_title_cannotedit, $_msg_unfreeze;

	if (edit_auth($page, $auth_flag, $exit_flag) && is_editable($page)) {
		// Editable
		return TRUE;
	} else {
		// Not editable
		if ($exit_flag === FALSE) {
			return FALSE; // Without exit
		} else {
			// With exit
			$body = $title = str_replace('$1',
				htmlspecialchars(strip_bracket($page)), $_title_cannotedit);
			if (is_freeze($page))
				$body .= '(<a href="' . $script . '?cmd=unfreeze&amp;page=' .
					rawurlencode($page) . '">' . $_msg_unfreeze . '</a>)';
			$page = str_replace('$1', make_search($page), $_title_cannotedit);
			catbody($title, $page, $body);
			exit;
		}
	}
}

// Check read-permission
function check_readable($page, $auth_flag = TRUE, $exit_flag = TRUE)
{
	return read_auth($page, $auth_flag, $exit_flag);
}

function edit_auth($page, $auth_flag = TRUE, $exit_flag = TRUE)
{
	global $edit_auth, $edit_auth_pages, $_title_cannotedit;
	return $edit_auth ?  basic_auth($page, $auth_flag, $exit_flag,
		$edit_auth_pages, $_title_cannotedit) : TRUE;
}

function read_auth($page, $auth_flag = TRUE, $exit_flag = TRUE)
{
	global $read_auth, $read_auth_pages, $_title_cannotread;
	return $read_auth ?  basic_auth($page, $auth_flag, $exit_flag,
		$read_auth_pages, $_title_cannotread) : TRUE;
}

// Basic authentication
function basic_auth($page, $auth_flag, $exit_flag, $auth_pages, $title_cannot)
{
	global $auth_method_type, $auth_users, $_msg_auth;

	// Checked by:
	$target_str = '';
	if ($auth_method_type == 'pagename') {
		$target_str = $page; // Page name
	} else if ($auth_method_type == 'contents') {
		$target_str = join('', get_source($page)); // Its contents
	}

	$user_list = array();
	foreach($auth_pages as $key=>$val)
		if (preg_match($key, $target_str))
			$user_list = array_merge($user_list, explode(',', $val));

	if (empty($user_list)) return TRUE; // No limit
	
	
	//--------------------------------------------
	//Customize from here
	//Session Auth instead of Basic Auth
	//Thanks & Refer SiteDev + AT by AKKO
	if(in_array($_SESSION['usr'],$user_list)){
		return TRUE;
	}
	
    $fg = FALSE;
	if ($auth_flag){
		$arr_temp = array();
		foreach ($user_list as $val) {
			foreach ($auth_users as $user=>$pass) {
				if ($val == $user) {
					$auth_temp[$user] = $pass;
				}
			}
		}
		$qm = get_qm();
		$fg = ss_chkusr($qm->m['auth']['ss_chkusr'], $auth_temp);
		if($fg){
			$_SESSION['usr'] = $_POST['username'];
		 	return TRUE;
		}
	}


	if ($exit_flag) {
		$body = $title = str_replace('$1',
		htmlspecialchars(strip_bracket($page)), $title_cannot);
		$page = str_replace('$1', make_search($page), $title_cannot);
		auth_catbody($title, $body);
		exit;
	}
	
	return FALSE;
}
?>