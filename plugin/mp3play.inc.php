<?php
// mp3play.inc.php by hokuken 2007 9/11 ver 0.9
//
// 単純に、embedタグを貼り付けるだけ。ファイルチェックするぐらいか？
//
// 使い方
// &mp3play(ファイルのパス);

function plugin_mp3play_inline()
{
    global $vars, $script;
    $qm = get_qm();
    $page = $vars['page'];
    if (! (PKWK_READONLY > 0 or is_freeze($page) or plugin_mp3play_is_edit_auth($page))) {
    	return $qm->replace('fmt_err_not_editable', '&mp3play', $page);
    }
    
    $args   = func_get_args();
    $args_num = count($args);
    
    if($args_num < 1){
        return "<p>{$qm->m['plg_mp3play']['err_usage']}</p>";
    }
    
    $filename = "";
    list($filename) = $args;
    
    //param check 
    if( !file_exists($filename) ){
        return "<p>{$qm->m['plg_mp3play']['err_file_notfound']}</p>";
    }
    
    return '<embed src="' . $filename . '" width="320" height="45" autostart="0"></embed>';
}

function plugin_mp3play_is_edit_auth($page, $user = '')
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

?>
