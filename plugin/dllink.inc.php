<?php
// dllink.inc.php by hokuken 2007 9/11 ver 0.9
//
// クリックするとダウンロードダイアログを表示させる
// ボタンを生成する
//
// 使い方
// &dllink(ファイルのパス,[ボタンのラベル],[メールアドレス],[タイトル]);

if( file_exists('lib/qdmail.php') ){
	require_once('lib/qdmail.php');
}
if( file_exists('lib/qdsmtp.php') ){
	require_once('lib/qdsmtp.php');
}

function plugin_dllink_inline()
{
    global $vars, $script;
    $qm = get_qm();
    $page = $vars['page'];
    if (! (PKWK_READONLY > 0 or is_freeze($page) or plugin_dllink_is_edit_auth($page))) {
    	return $qm->replace('fmt_err_not_editable', '&dllink', $page);
    }

    $args   = func_get_args();
    $args_num = count($args);

    if($args_num < 1 || 5 < $args_num){
    	return $qm->replace('fmt_err_iln', 'dllink', $qm->m['plg_dllink']['err_usage']);
    }

    $filename = "";
    $label = "";
    $email = "";
    $title = "";

    $label = strip_autolink(array_pop($args)); // Already htmlspecialchars(text)

    list($filename, $email, $title) = array_pad($args,3,'');

    //param check
    if( !is_url($filename) && !file_exists($filename) ){
    	return $qm->replace('plg_dlbutton.err_file_notfound', '');
    }

   	$label = $label == ''? $qm->m['plg_dllink']['label']: $label;

    if($email != ''){
        if (!preg_match('/^[a-zA-Z0-9_\.\-]+?@[A-Za-z0-9_\.\-]+$/',$email)) {
        	return $qm->replace('plg_dlbutton.err_invalid_email', $email);
        }
    }

    $title = $title == ''? $qm->replace('plg_dlbutton.subject'): $title;

    if (isset($vars['page_alt'])) $page = $vars['page_alt'];

    //url encode
    $title = urlencode($title);
	$filename = urlencode($filename);
	$page = urlencode($page);

    //ボタン作成
	$md5 = md5( file_get_contents('qhm.ini.php') );

    if($email == ''){
        $dlurl = dirname($script) . '/plugin/dlexec.php?filename=' .$filename.'&key='.$md5;
    }
    else{
        $dlurl = dirname($script) . '/plugin/dlexec.php?filename=' .$filename. '&key='.$md5.'&email=' .$email. '&title=' .$title;
    }

    $dlurl .= '&refer=' . $page;

    $btn = '<a  onClick=\'location.href="' .h($dlurl). '"\' style="cursor:pointer;">' . $label . '</a>';
    return $btn;
}

function plugin_dllink_is_edit_auth($page, $user = '')
{
	global $edit_auth, $edit_auth_pages, $auth_method_type;
	if (! $edit_auth) {
		return FALSE;
	}
	// Checked by:
	$target_str = '';
	if ($auth_method_type == 'pagename') {
		$target_str = $page; // Page name
	} else if ($auth_method_type == 'contents') {
		$target_str = join('', get_source($page)); // Its contents
	}

	foreach($edit_auth_pages as $regexp => $users) {
		if (preg_match($regexp, $target_str)) {
			if ($user == '' || in_array($user, explode(',', $users))) {
				return TRUE;
			}
		}
	}
	return FALSE;
}
