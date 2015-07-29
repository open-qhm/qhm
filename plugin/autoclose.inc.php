<?php
// autoclose.inc.php by hokuken 2007 9/11 ver 0.9
//
// 指定した時刻が過ぎると、自動的にページを閉じるプラグイン。
// 指定したURLに転送することも可能
//
// 使い方
// #autoclose(パスワード(英数のみ));

function plugin_autoclose_convert(){
    global $vars, $script;
    $qm = get_qm();
    $qt = get_qt();
    
	//---- キャッシュのための処理を登録 -----
	if($qt->create_cache) {
		$args = func_get_args();
		return $qt->get_dynamic_plugin_mark(__FUNCTION__, $args);
	}
	//------------------------------------
    
    $page = isset($vars['page']) ? $vars['page'] : ''; 
    $args = func_get_args();
    $args_num = count($args);
    
    //args check
    if($args_num != 1 && $args_num != 2){
    	return $qm->replace('fmt_err_cvt', 'autoclose', $qm->m['plg_autoclose']['err_usage']);
     }
    
    list($date, $url) = array_pad($args, 2, '');
    
    $closedate = strtotime($date);
    
    if(is_url($url)){
        
    }
    else if($url != ""){
        $url = $script . "?" . urlencode($url);
    }
    else { //no-target
        $url = $script . "?plugin=autoclose&page=" . urlencode($page);
    }
    
    
    $editable = edit_auth($page, FALSE, FALSE);
    
    if($editable){
        $tag_s = "<div style=\"border:dashed 1px #f00;background-color:#fee;padding:1em;\">";
        $tag_e = "</div>";
        
        $msg = $qm->replace('plg_autoclose.format_ntc', date('Y/m/d(D) H:i', $closedate), $url);
        return $tag_s . $qm->m['plg_autoclose']['ntc_admin'] . $msg . $tag_e;
    }
    else{
        $diff =  strtotime($date) - time();
        if($diff < 0 ){
            header("Location: " . $url);
            exit;
        }
        else{
            return "";
        }
    }
}

function plugin_autoclose_action(){
    global $vars, $script;
    $qm = get_qm();
    
    $page = isset($vars['page']) ? $vars['page'] : '';
    $errmsg = isset($vars['errmsg']) ? $vars['errmsg'] : '';
    $page_title = urldecode($page);
    
    $closemsg = $qm->replace('plg_autoclose.closed', $page_title);

    $body = <<<EOD
<h2>{$qm->m['plg_autoclose']['title_closed']}</h2>
<p>{$closemsg}</p>
EOD;
    
    $page_title = $qm->replace('plg_autoclose.title_auth', $page_title);
    return array('msg'=>$page_title, 'body'=>$body);
}

function plugin_autoclose_is_edit_auth($page, $user = '')
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
