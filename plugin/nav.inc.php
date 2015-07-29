<?php
/**
 *   QHM Nav plugin
 *   -------------------------------------------
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2014/06/11
 *   modified :
 *
 *   Switch Nav content and convert
 *
 *   Usage :
 *     #nav(OtherSiteNavigator)
 *
 */

function plugin_nav_convert()
{
	global $vars, $navbar;
	static $nav = NULL;
	$qm = get_qm();
	$qt = get_qt();

	$num = func_num_args();
	if ($num > 0) {
		// Try to change default 'SiteNavigator' page name (only)
		if ($num > 1)       return '#nav: 引数が多すぎます。' ."<br />\n";
		if ($nav !== NULL) return '#nav: 既に変更されています：' . h($nav) . "<br />\n";
        if ($qt->getv('plugin_nav_source')) return '#nav: 既にナビの内容を変更しています。' . "<br />\n";

		$args = func_get_args();
		//ナビの内容を受け取る
        if (strpos($args[0], "\r") !== FALSE)
        {
            if (isset($vars['page_alt'])) return '#nav: 利用できません。';

            $source = str_replace("\r", "\n", $args[0]);
            $qt->setv_once('plugin_nav_source', $source);
            return '';
        }
		if (! is_page($args[0])) {
			return 'ページがみつかりません：' . h($args[0]) . "<br />\n";
		} else {
			$nav = $args[0]; // Set
			return '';
		}

	} else {
		// Output navbar page data
		$page = ($nav === NULL) ? $navbar : $nav;

        if ($source = $qt->getv('plugin_nav_source'))
        {
			// Cut fixed anchors
			$source = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', $source);

			return convert_html($source);
        }
		if (! is_page($page)) {
			return '';
		}
		else if (isset($vars['preview']) && $vars['page'] == $page)
		{
			// Cut fixed anchors
			$navtext = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', $vars['msg']);

			return convert_html($navtext);
		} else {
			// Cut fixed anchors
			$navtext = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', get_source($page));

			return convert_html($navtext);
		}
	}
}
