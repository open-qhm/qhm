<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// chpassword.inc.php パスワード変更プラグイン
//
// Text chpassword plugin

function plugin_chpassword_inline()
{
	global $script, $vars;
	global $auth_users;
	$qm = get_qm();
    
	$args = func_get_args();
	$text = strip_autolink(array_pop($args)); // Already htmlspecialchars(text)
    
	list($dispflg) = array_pad($args, 1, 'false');

    $_page  = isset($vars['page']) ? $vars['page'] : '';
	$url = $script.'?plugin=qhmsetting&mode=form&phase=';
	
	if ($text == '') {
		$text = $qm->m['plg_chpassword']['label'];
	}
	
	$is_login = false;
	if (isset($_SESSION['usr']) && array_key_exists($_SESSION['usr'], $auth_users)) {
		if (ss_admin_check()) {
			// 管理者
			$url .= 'admin';
		}
		else {
			$url .= 'user2';
		}
		$is_login = true;
	}
	else {
		// ログイン
		$url = $script.'?cmd=qhmauth';
	}
	
	$ret = '';
  	if ($dispflg == 'true' || ($dispflg == 'false' && $is_login)) {
		$ret = '<a href="' . $url . '" >' . $text . '</a>';
  	}
	
	return $ret;
}
?>
