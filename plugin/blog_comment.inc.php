<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: blog_comment.inc.php,v 0.01 2008/07/09 13:32:55 edo Exp $
//
// Blog Comment plugin 

function plugin_blog_comment_convert()
{
	static $blog_comment_called = false;
	$qm = get_qm();
	$qt = get_qt();
	if ($blog_comment_called) {
		if( $qt->getv('blog_rss_mode') ){
			return '';
		}

		return $qm->replace('fmt_err_already_called', '#blog_comment');
	}
	$blog_comment_called = true;
	return '<a name="blog_comment"></a>';
}
?>
