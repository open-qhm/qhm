<?php
/**
 *   QHM: Use Smart Design Plugin
 *   -------------------------------------------
 *   plugin/use_smart.inc.php
 *   
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2011-02-23
 *   modified :
 *   
 *   スマートフォン用デザインを使用するかどうか、
 *   ユーザー側の設定を切り換え、保存するためのプラグイン。
 *   COOKIE[enable_smart_style] に 0/1 を保持する
 *   
 *   Usage :
 *   
 */

define('PLUGIN_USE_SMART_FORMAT', '<p><a href="%s">%s</a></p>');

function plugin_use_smart_action() {
	global $script, $vars;

	$page = $vars['refer'];
	$r_page = rawurlencode($page);
	
	if (isset($_COOKIE['enable_smart_style'])) {
		$enable_smart_style = ($_COOKIE['enable_smart_style'] + 1) % 2;
	}
	else {
		$enable_smart_style = 0;
	}
    //set cookie for split test
    setcookie('enable_smart_style', $enable_smart_style, time() + 86400 * 30); //1 month
	
	header('Location: '. $script . '?' . $r_page);
	exit;
	
}

function plugin_use_smart_convert() {
	global $script, $vars, $defaultpage;
	$qm = get_qm();

	$text = $qm->m['plg_use_smart']['switch_pc'];
	
	if (isset($_COOKIE['enable_smart_style']) && !$_COOKIE['enable_smart_style']) {
		$text = $qm->m['plg_use_smart']['switch_mob'];
	}
	
	$page = isset($vars['page'])? $vars['page']: $defaultpage;
	$r_page = rawurlencode($page);
	$link = $script . '?cmd=use_smart&refer=' . $r_page;
	
	
	return sprintf(PLUGIN_USE_SMART_FORMAT, $link, $text);
	
}

function plugin_use_smart_inline()
{
	global $enable_smart_style;
	
	$args = func_get_args();
	$enable = array_shift($args);

	$enable_smart_style = true;
	if ($enable == 'false')
	{
		$enable_smart_style = false;
	}
	return '';
}


/**
 *   スマートフォン用デザインがCOOKIE 上で有効かどうか判別する。
 *   COOKIE にセットされていない場合、有効とする。
 */
function plugin_use_smart_is_enable() {
	return !isset($_COOKIE['enable_smart_style']) || (isset($_COOKIE['enable_smart_style']) && $_COOKIE['enable_smart_style']);
}

?>