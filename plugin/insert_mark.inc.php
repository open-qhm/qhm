<?php
/**
 *   QHM Insert a Placeholder Plugin
 *   -------------------------------------------
 *   plugin/insert_mark.inc.php
 *   
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2011-02-09
 *   modified :
 *   
 *   指定した場所にプレースホルダーを挿入する。
 *   QuickCommu や Quick myShop 、QNewsletter から読み込む際に使う。
 *   
 *   Usage :
 *   
 */

define('PLUGIN_INSERT_MARK_FORMAT', "\n<!-- \$pagename CONTENTS START -->\n<!-- \$pagename CONTENTS END -->\n");

function plugin_insert_mark_convert() {
	global $vars;
	$qt = get_qt();
	
	//ナビ・メニュー・ナビ２の場合、
	if (isset($vars['page_alt']) && is_page($vars['page_alt'])) {
		$page_alt = $vars['page_alt'];
		$im_key = "{$page_alt}InsertMark";
		if (!$qt->getv($im_key)) {
			$qt->setv($im_key, true);
			return str_replace('$pagename', strtoupper($page_alt), PLUGIN_INSERT_MARK_FORMAT);
		}
	}
	//コンテンツの場合
	else {
		if (!$qt->getv('body_insert_mark')) {
			$qt->setv('body_insert_mark', true);
			return "\n<!-- BODYCONTENTS START -->\n<!-- BODYCONTENTS END -->\n";
		}
	}

}

?>
