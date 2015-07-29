<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: blog_more.inc.php,v 0.01 2008/07/09 13:03:14 edo Exp $
//
// Blog More plugin 

function plugin_blog_more_convert()
{
	static $blog_more_called = false;
	$qm = get_qm();
	$qt = get_qt();
	if ($blog_more_called) {
		if( $qt->getv('blog_rss_mode') ){
			return '';
		}

		return $qm->replace('fmt_err_already_called', '#blog_more');
	}
	$blog_more_called = true;
	return '<a name="blog_more"></a>';
}
?>
