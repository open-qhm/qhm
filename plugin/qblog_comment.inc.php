<?php
/**
 *   QBlog Comment Plugin
 *   -------------------------------------------
 *   ./pluginqblog_comment.inc.php
 *   
 *   Copyright (c) 2012 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 12/08/06
 *   modified :
 *   
 *   DATA: cacheqblog/ENCODED_PAGE_NAME.qbcm.dat
 *   
 */


define('PLUGIN_QBLOG_COMMENT_SIZE_MSG',  70);
define('PLUGIN_QBLOG_COMMENT_INPUT_SIZE', 15);
define('PLUGIN_QBLOG_MAX_COMMENT_TITLE_LENGTH', 140);
define('PLUGIN_QBLOG_MAX_COMMENT_NAME_LENGTH', 70);
define('PLUGIN_QBLOG_COMMENT_STRIM_WIDTH', 180);


function plugin_qblog_comment_action()
{
	global $script, $vars, $now;
	global $qblog_date_format, $qblog_comment_check, $qblog_defaultpage;
	$qm = get_qm();//TODO: remove

	if (PKWK_READONLY) die_message($qm->m['fmt_err_pkwk_readonly']);

	if ($vars['mode'] === 'accept')
	{
		plugin_qblog_comment_accept();
	}
	else if ($vars['mode'] === 'hide')
	{
		plugin_qblog_comment_hide();
	}
	else if ($vars['mode'] === 'get_comment')
	{
		plugin_qblog_comment_get();
	}
	
	$page = $vars['refer'];
	

	if ($vars['msg'] == '') return array('msg'=>'', 'body'=>''); // Do nothing
	
	//QBlog ページ以外設置不可
	if (! is_qblog($page))
	{
		return array('msg'=>'', 'body'=>'');
	}

	$comment_title   = isset($vars['title']) ? $vars['title'] : '';
	$writer_name  = isset($vars['name']) ? $vars['name'] : '';

	$editable = check_editable($qblog_defaultpage, FALSE, FALSE);

	//CSRF 対策
	if (md5(join('', get_source($vars['refer']))) != $vars['digest']) {
		redirect($script);
	}

	$error = FALSE;
	if (mb_strlen($comment_title) > PLUGIN_QBLOG_MAX_COMMENT_TITLE_LENGTH)
	{
		$vars['qblog_comment_title_error'] = 'タイトルは'. PLUGIN_QBLOG_MAX_COMMENT_TITLE_LENGTH .'文字以内で入力してください。';
		$error = TRUE;
	}
	if (mb_strlen($writer_name) > PLUGIN_QBLOG_MAX_COMMENT_NAME_LENGTH)
	{
		$vars['qblog_comment_name_error'] = 'お名前は'. PLUGIN_QBLOG_MAX_COMMENT_NAME_LENGTH .'文字以内で入力してください。';
		$error = TRUE;
	}
	if ( ! $editable && $vars['authcode_master'] !== $vars['authcode'])
	{
		$vars['qblog_comment_auth_error'] = '認証コードを入力してください。';
		$error = TRUE;
	}

	if ($error)
	{
		return array('msg' => '', 'body' => '');
	}
	
	//管理人コメントは承認待ちしない
	$is_admin = ss_admin_check();
	$qblog_comment_check = $is_admin ? 0 : $qblog_comment_check;
	
	$comment_data = array(
		'msg'   => $vars['msg'],
		'title' => $comment_title,
		'name'  => $writer_name,
		'admin' => $is_admin
	);
	
	$title = $qm->m['fmt_title_updated'];
	$body = '';
	

	qblog_add_comment($page, $comment_data);

	$wiki = 'コメントありがとうございました。
コメントは管理者による承認後に表示されます。

#beforescript{{
<style type="text/css">
body {
margin-top:60px;
font-size: 15px;
background-color: #fff;
}
h2.title {
display:none;
}
#body {
text-align: center;
}
#body h2 {
font-size: 24px;
margin-top: 20px;
}
</style>
}}
';
	redirect($page, $wiki, 5);
	
}

function plugin_qblog_comment_convert()
{
	global $script, $vars, $digest, $username;
	global $qblog_defaultpage, $qblog_comment_check, $qblog_enable_comment;
	
	static $called = FALSE;
	
	$page = $vars['page'];
	
	if (PKWK_READONLY) return ''; // Show nothing
	
	if ($called) return ''; // Only once
	$called = TRUE;
	
	//QBlog ページ以外設置不可
	if ($page === $qblog_defaultpage OR ! is_qblog())
	{
		return '';
	}
	
	if (isset($qblog_enable_comment) && ! $qblog_enable_comment)
	{
		return '';
	}

	$editable = check_editable($qblog_defaultpage, FALSE, FALSE);
	
	$qt = get_qt();
	$qt->setv('jquery_include', TRUE);
	$qm = get_qm();// TODO: remove
	
	// !既存のコメントを出力
	$comments = array();
	if (file_exists(CACHEQBLOG_DIR . encode($page) . '.qbcm.dat'))
	{
		$comments = unserialize(file_get_contents(CACHEQBLOG_DIR . encode($page) . '.qbcm.dat'));
	}
	if (count($comments) > 0)
	{
		foreach ($comments as $i => $comment)
		{
			if ( ! $comment['show']) {
				unset($comments[$i]);
				continue;
			}

			$cnt = $i+1;
			
			if ($editable OR $comment['accepted'])
			{
				$comment['msg'] = h($comment['msg']);
				//他のコメントにレスができる
				$comment['msg'] = preg_replace('/(&gt;&gt;|※)(\d+)/', '<a href="#qbcomment_$2">$1$2</a>', $comment['msg']);
				$comment['msg'] = nl2br($comment['msg']);
				
				//URLをリンクに
				$ptns = array(
//					'/((?:[a-zA-Z0-9-]+\.)+[a-zA-Z0-9]{2,3})([^a-zA-Z0-9])/',
					'/(?:https?|ftp)(?::\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/',
				);
				$rpls = array(
//					'<a href="$0">$0</a>',
					'<a href="$0">$0</a>'
				);
				$comment['msg'] = preg_replace($ptns, $rpls, $comment['msg']);
				
				$comments[$i]['msg'] = $comment['msg'];
			}
		}
	}
	
	//認証コード生成
	$authcode = '' . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9);
	
	$action = $script . '?' . rawurlencode($page);

	$name = '';
	if ($editable)
	{
		$name = ($username === $_SESSION['usr']) ? '管理人' : $_SESSION['usr'];
	}
	$name = isset($vars['name']) ? $vars['name'] : $name;

	$title = isset($vars['title']) ? $vars['title'] : '';
	
	$msg = isset($vars['msg']) ? $vars['msg'] : '';

	// !エラー処理
	$auth_err = $title_err = $name_err = FALSE;
	if (isset($vars['qblog_comment_auth_error']))
	{
		$auth_err = $vars['qblog_comment_auth_error'];
	}
	if (isset($vars['qblog_comment_title_error']))
	{
		$title_err = $vars['qblog_comment_title_error'];
	}
	if (isset($vars['qblog_comment_name_error']))
	{
		$name_err = $vars['qblog_comment_name_error'];
	}
	
	ob_start();
	include(PLUGIN_DIR . 'qblog/comment_template.html');
	$html = ob_get_clean();

	return $html;
}

function plugin_qblog_comment_accept()
{
	global $vars, $script;
	
	$page = $vars['refer'];
	$id = $vars['id'];
	$accept = $vars['accept'];
	
	header("Content-Type: application/text; charset=UTF-8");
	if (qblog_accept_comment($page, $id))
	{
		echo '1';
	}
	else
	{
		echo '0';
	}
	
	exit;
}

function plugin_qblog_comment_hide()
{
	global $vars, $script;
	
	$page = $vars['refer'];
	$id = $vars['id'];
	$hide = $vars['hide'];
	
	header("Content-Type: application/text; charset=UTF-8");
	if (qblog_hide_comment($page, $id))
	{
		echo '1';
	}
	else
	{
		echo '0';
	}
	
	exit;
}

function plugin_qblog_comment_get()
{
	global $vars, $script;
	
	$page = $vars['refer'];
	$id = $vars['id'];
	
	$datafile = CACHEQBLOG_DIR . encode($page). '.qbcm.dat';
	$comments = unserialize(file_get_contents($datafile));
	
	if (isset($comments[$id]))
	{
		echo mb_strimwidth($comments[$id]['msg'], 0, PLUGIN_QBLOG_COMMENT_STRIM_WIDTH, '...');
	}
	
	exit;
}

/* End of file qblog_comment.inc.php */
/* Location: ./plugin/qblog_comment.inc.php */
