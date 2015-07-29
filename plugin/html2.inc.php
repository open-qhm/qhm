<?php
/**
 *   Output 1-Line HTML Plugin
 *   -------------------------------------------
 *   plugin/html2.inc.php
 *   
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 
 *   modified :
 *   
 *   1行HTMLをそのまま出力します。
 *   
 *   Usage :
 *     #html2(HTML)
 *   
 */

function plugin_html2_convert()
{
	global $vars;
	$qm = get_qm();
	
	$page = $vars['page'];
	if (! (PKWK_READONLY > 0 or is_freeze($page) or plugin_html2_is_edit_auth($page))) {
		return $qm->replace('fmt_msg_not_editable', '#html2', $page);
	}

	$args   = func_get_args();
	$ret = array_shift($args);
	
	foreach($args as $tmpstr){
		$ret .= ',';
		$ret .= $tmpstr;
	}
	
	return $ret;
}

function plugin_html2_is_edit_auth($page, $user = '')
{
	global $edit_auth, $edit_auth_pages, $auth_method_type;
	if (! $edit_auth) {
		return FALSE;
	}
	// Checked by:
	$target_str = '';
	if ($auth_method_type == 'pagename') {
		$target_str = $page; // Page name
	} else if ($auth_method_type == 'contents') {
		$target_str = join('', get_source($page)); // Its contents
	}

	foreach($edit_auth_pages as $regexp => $users) {
		if (preg_match($regexp, $target_str)) {
			if ($user == '' || in_array($user, explode(',', $users))) {
				return TRUE;
			}
		}
	}
	return FALSE;
}
?>
