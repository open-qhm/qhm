<?php
function plugin_beforescript_convert()
{
	global $vars;
	$qm = get_qm();
	
	$page = $vars['page'];
	if (! (PKWK_READONLY > 0 or is_freeze($page) or plugin_beforescript_is_edit_auth($page))) {
		return $qm->replace('fmt_err_not_editable', '#html', $page);
	}

	$args   = func_get_args();
	$addscript = array_pop($args);

	$qt = get_qt();
	$qt->appendv('beforescript', $addscript);
	
	return "";
}

function plugin_beforescript_is_edit_auth($page, $user = '')
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
