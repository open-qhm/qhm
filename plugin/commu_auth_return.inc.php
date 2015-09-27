<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: commu_logout.inc.php,v 0.5 2007/10/18 15:04:08 henoheno Exp $
//
// commu_auth convert view plugin

// ----
define('PLUGIN_COMMU_INFO_NOCOMMU', 'There is no commu directory.');
define('PLUGIN_COMMU_AUTH', 'plugin_commu_auth');

function plugin_commu_auth_return_inline()
{
	global $script;

	//キャッシュを無効に
	if (QHM_VERSION < 4.6) {
		global $enable_cache;
		$enable_cache = false;
	} else {
		$qt = get_qt();
		$qt->enable_cache = false;
	}

	$editable = edit_auth($page, FALSE, FALSE);

	if (!isset($_SESSION['commu_user']) ){
		// 何も表示しない
		return '';
// ログイン画面にとばす
//		commu_redirect($script.'?'.rawurlencode($vars['page']));
	}

	if (func_num_args()) {
		$flg_show = false;
		$args = func_get_args();
		$text = array_pop($args);
		
		// フィールドの指定がない場合は、認証成功として表示する
		if (count($args) == 0) {
			return $text;
		} 
		
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
//						return convert_html($text);
					return $text;
				}
			}
		}
		if ($flg_show == false) {
			return '';
		}
	}
}
?>