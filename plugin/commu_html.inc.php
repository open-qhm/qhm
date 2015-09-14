<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: commu_html.inc.php,v 1.10 2009/10/05 17:20:00 hokuken Exp $
//
// commu_html plugin
// replace user data    ex <%lastname%> -> hogehoge 

// ----

function plugin_commu_html_convert()
{
	global $vars;

	//キャッシュを無効に
	if (QHM_VERSION < 4.6) {
		global $enable_cache;
		$enable_cache = false;
	} else {
		$qt = get_qt();
		$qt->enable_cache = false;
	}

	$page = $vars['page'];
	if (! (PKWK_READONLY > 0 or is_freeze($page) or plugin_commu_html_is_edit_auth($page))) {
		return "<p>commu_html(): Current page, $page, must be edit_authed or frozen or whole system must be PKWK_READONLY.</p>";
	}

	$args   = func_get_args();
	$body   = array_pop($args);
    $noskin = in_array("noskin", $args);
	
	$s = array();
	$r = array();
	$cnt = 0;
	if (isset($_SESSION['commu_user'])) {
		foreach ($_SESSION['commu_user'] as $key => $val) {
			$s[$cnt] = '/<%'.$key.'%>/';
			$r[$cnt] = mb_convert_encoding($val, "UTF-8", "UTF-8,EUC-JP");
			$cnt++;
		}
		$body = preg_replace($s, $r, $body);
	}

	if ($noskin) {
		pkwk_common_headers();
		print $body;
		exit;
	}
	return $body;
}

function plugin_commu_html_is_edit_auth($page, $user = '')
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
