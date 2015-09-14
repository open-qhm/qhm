<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: commu_info.inc.php,v 0.5 2007/10/18 15:04:08 henoheno Exp $
//
// commu_info inline view plugin

// ----
define('PLUGIN_COMMU_INFO_NOCOMMU', 'There is no commu directory.');

function plugin_commu_info_inline()
{
	//キャッシュを無効に
	if (QHM_VERSION < 4.6) {
		global $enable_cache;
		$enable_cache = false;
	} else {
		$qt = get_qt();
		$qt->enable_cache = false;
	}

	$args = func_get_args();
	$text = strip_autolink(array_pop($args));
	
	if($text == ""){
		$text = '登録情報変更';
	}
	
	$dirpath = '';
	if ($handle = opendir('./commu')) {
		$dirpath = "./commu/";
		closedir($handle);
	}
	else if ($handle = opendir('../commu')){
		$dirpath = "../commu/";
		closedir($handle);
	}
	else {
		return PLUGIN_COMMU_INFO_NOCOMMU;
	}
	
	return '<a href="'.$dirpath.'user_view.php">'.$text.'</a>';

}
?>