<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: ruby.inc.php,v 1.6 2005/05/07 07:41:31 henoheno Exp $
//
// Ruby annotation plugin: Add a pronounciation into kanji-word or acronym(s)
// See also about ruby: http://www.w3.org/TR/ruby/
//
// NOTE:
//  Ruby tag works with MSIE only now,
//  but readable for other browsers like: 'words(pronunciation)'

define('PLUGIN_RUBY_USAGE', '&ruby(pronunciation){words};');

function plugin_ruby_inline()
{
	$qm = get_qm();
	
	if (func_num_args() != 2) return $qm->m['plg_ruby']['err_usage'];

	list($ruby, $body) = func_get_args();

	// strip_htmltag() is just for avoiding AutoLink insertion
	$body = strip_htmltag($body);

	if ($ruby == '' || $body == '') return $qm->m['plg_ruby']['err_usage'];

	return '<ruby><rb>' . $body . '</rb>' . '<rp>(</rp>' .
		'<rt>' .  h($ruby) . '</rt>' . '<rp>)</rp>' .
		'</ruby>';
}
?>
