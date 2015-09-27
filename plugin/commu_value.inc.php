<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: size.inc.php,v 1.10 2005/06/16 15:04:08 henoheno Exp $
//
// Text-size changing via CSS plugin

// ----
define('PLUGIN_COMMU_USAGE', '&commu_value(fieldname)');
define('PLUGIN_COMMU_USAGE_CONV', '#commu_value(fieldname)');
define('PLUGIN_NOT_COMMU_FIELD', 'fieldname is not setted');

function plugin_commu_value_inline()
{
	if (func_num_args() != 2) return PLUGIN_COMMU_USAGE;

	//キャッシュを無効に
	if (QHM_VERSION < 4.6) {
		global $enable_cache;
		$enable_cache = false;
	} else {
		$qt = get_qt();
		$qt->enable_cache = false;
	}


	list($name) = func_get_args();

	// strip_autolink() is not needed for size plugin
	//$body = strip_htmltag($body);
	
	if ($name == '')
		return PLUGIN_COMMU_USAGE;

	if (isset($_SESSION['commu_user'])) {
		$userdata = $_SESSION['commu_user'];
		if (isset($userdata[$name])) {
			$buffer = $userdata[$name];
			$buffer = mb_convert_encoding($buffer, "UTF-8", "UTF-8,EUC-JP");
			if ($name == 'expiration' && $buffer != '') {
				$buffer = date('Y年m月d日',$buffer);
			}
			return $buffer;
		}
		else {
			return PLUGIN_NOT_COMMU_FIELD;
		}
	}
	else {
		return PLUGIN_NOT_COMMU_FIELD;
	}
}

function plugin_commu_value_convert()
{
	if (func_num_args() != 1) return PLUGIN_COMMU_USAGE_CONV;

	
	//キャッシュを無効に
	if (QHM_VERSION < 4.6) {
		global $enable_cache;
		$enable_cache = false;
	} else {
		$qt = get_qt();
		$qt->enable_cache = false;
	}

	list($name) = func_get_args();

	// strip_autolink() is not needed for size plugin
	//$body = strip_htmltag($body);
	if ($name == '')
		return PLUGIN_COMMU_USAGE_CONV;

	if (isset($_SESSION['commu_user'])) {
		$userdata = $_SESSION['commu_user'];
		if (isset($userdata[$name])) {
			$buffer = $userdata[$name];
			$buffer = mb_convert_encoding($buffer, "UTF-8", "UTF-8,EUC-JP");
			if ($name == 'expiration' && $buffer != '') {
				$buffer = date('Y年m月d日',$buffer);
			}
			return $buffer;
		}
		else {
			return PLUGIN_NOT_COMMU_FIELD;
		}
	}
	else {
		return PLUGIN_NOT_COMMU_FIELD;
	}
}

?>
