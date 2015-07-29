<?php
function plugin_lastscript_convert()
{
	global $vars;
	$qm = get_qm();
	$qt = get_qt();
	$page = $vars['page'];
	if (! (PKWK_READONLY > 0 or is_freeze($page) or plugin_lastscript_is_edit_auth($page))) {
		return $qm->replace('fmt_err_not_editable', '#html', $page);
	}

	$args   = func_get_args();
//	$lastscript .= array_pop($args);
	$js = array_pop($args);
	$qt->appendv('lastscript', $js);

	return "";
}

function plugin_lastscript_is_edit_auth($page, $user = '')
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
