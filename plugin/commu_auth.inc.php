<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: commu_logout.inc.php,v 0.5 2007/10/18 15:04:08 henoheno Exp $
//
// commu_auth convert view plugin

// ----
define('PLUGIN_COMMU_INFO_NOCOMMU', 'There is no commu directory.');

function plugin_commu_auth_convert()
{
	global $script, $vars;

	//キャッシュを無効に
	if (QHM_VERSION < 4.6) {
		global $enable_cache;
		$enable_cache = false;
	} else {
		$qt = get_qt();
		$qt->enable_cache = false;
	}

	$editable = edit_auth($page, FALSE, FALSE);
    if($editable){
        return '<p style="padding:1em;background-color:#fdd;border:1px dashed #f00;"><strong>【お知らせ】管理者モード以外のアクセスは、commu_authプラグインによって'
        . 'ログインページへ転送されます</strong></p>';
    }
    else {
		if (!isset($_SESSION['commu_user']) ){
			// ログイン画面にとばす
			commu_redirect($script.'?'.rawurlencode($vars['page']));
		}

		if (func_num_args()) {
			$flg_show = false;
			$args = func_get_args();
			foreach ($args as $line) {
				if (strpos($line, '=') !== FALSE) {
					list($field, $value) = explode('=',trim($line));
					$field = trim($field);
					$value = trim($value);
				}
				if (isset ($_SESSION['commu_user'][$field])) {
					$cmpval = $_SESSION['commu_user'][$field];
					$cmpval = mb_convert_encoding($cmpval, "UTF-8", "UTF-8,EUC-JP");
					if ($cmpval == $value) {
						$flg_show = true;
					}
				}
			}
			if ($flg_show == false) {
				$url = $script.'?FrontPage';
				if (isset($vars['QHMSSID'])) {
					$url.= '&QHMSSID='.$vars['QHMSSID'];
				}
		        header("Location: $url");
		        exit;
			}
		}
	}
}
?>