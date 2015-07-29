<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: menu.inc.php,v 1.8 2004/11/27 12:23:02 henoheno Exp $
//

function plugin_menu2_convert()
{
	global $vars, $menubar2;
	static $menu = NULL;
	$qm = get_qm();
	$qt = get_qt();

	$num = func_num_args();
	if ($num > 0) {
		// Try to change default 'MenuBar' page name (only)
		if ($num > 1)       return $qm->m['plg_menu']['err_usage'];
		if ($menu !== NULL) return $qm->replace('plg_menu.err_already_set', h($menu));
		if ($qt->getv('plugin_menu2_source')) return '#menu2: 既にメニューの内容を変更しています。' . "<br />\n";

		$args = func_get_args();
		//メニューの内容を受け取る
		if (strpos($args[0], "\r") !== FALSE)
		{
			if (isset($vars['page_alt'])) return '#menu2: 利用できません。';

			$source = str_replace("\r", "\n", $args[0]);
			$qt->setv_once('plugin_menu2_source', $source);
			return '';
		}
		if (! is_page($args[0])) {
			return $qm->replace('plg_menu.err_no_page', h($args[0]));
		} else {
			$menu = $args[0]; // Set
			return '';
		}

	} else {
		// Output menubar page data
		$page = ($menu === NULL) ? $menubar2 : $menu;

		if ($source = $qt->getv('plugin_menu2_source'))
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
			$menutext = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', $vars['msg']);

			return convert_html($menutext);
		}
		else if ($vars['page'] == $page) {
			return '<!-- '. $qm->replace('plg_menu.ntc_already_view', h($page)).' -->';
		} else {
			// Cut fixed anchors
			$menutext = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', get_source($page));

			return convert_html($menutext);
		}
	}
}
