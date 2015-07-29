<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: blog_body.inc.php,v 0.01 2008/07/09 13:01:03 edo Exp $
//
// Blog plugin 

function plugin_blog_body_convert()
{
	static $blog_body_called = false;
	$qm = get_qm();
	$qt = get_qt();
	
	if ($blog_body_called) {
		if( $qt->getv('blog_rss_mode') ){
			return '';
		}

		return $qm->replace('fmt_err_already_called', '#blog_body');
	}
	$blog_body_called = true;

	return '';
}
?>
