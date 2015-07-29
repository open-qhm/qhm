<?php
/**
 *   QHM Secret Plugin ver 0.9
 *   -------------------------------------------
 *   plugin/secret.inc.php
 *   
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2007-09-11
 *   modified :
 *   
 *   簡易パスワード認証ページを作るプラグイン
 *   
 *   Usage :
 *     &secret(パスワード(英数のみ));
 *   
 */
function plugin_secret_convert(){
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
    $en_page = rawurlencode($page);
    
    $secretplugin = isset($vars['secretplugin']) ? $vars['secretplugin'] : '';
    $password = isset($vars['password']) ? $vars['password'] : '';
    $auth_url = $script . "?plugin=secret&page=" . urlencode($page);
 
    $args = func_get_args();
    $masterpasswd = array_pop($args);
    
    if($masterpasswd == ""){
    	return $qm->replace('fmt_err_cvt', 'secret', 'パスワードを設定して下さい。');
    }
    
    $editable = edit_auth($page, FALSE, FALSE);
    
    if($editable){
        return "<div style=\"border:dashed 1px #f00;background-color:#fee;padding:1em;\"><p><strong>{$qm->m['plg_secret']['ntc_admin']}</strong></p></div>";
    }
    else{
    
    	//session check 
    	if( isset($_SESSION['secretplugin_'.$en_page] ) &&
    		$_SESSION['secretplugin_'.$en_page] == $en_page )
    	{
    		return '';
    	}
        
        if($secretplugin == "secretplugin"){
            
            //passwd check
            if($password == $masterpasswd){
            	$_SESSION['secretplugin_'.$en_page] = $en_page;
                return "";
            }
            else{
                header("Location: $auth_url&errmsg=true");
                exit();
            }
        }
        
        
        //携帯アクセスのことを考えての処理(cookieをちぇっくして、処理を行う)
		if( isset($vars['chkck']) ){
			$cookie = $_COOKIE['QHMDUMMY'];
			setcookie('QHMDUMMY', '' ,time()-3600); //del cookie
			
			if( $cookie ){
				header("Location: $auth_url");
				exit;
			}
			else{
				header("Location: {$auth_url}&mobssid=yes");
				exit;
			}
		}
		else{ //はじめてのアクセス
			setcookie('QHMDUMMY', TRUE);
			header('Location: '.$script.'?cmd=read&page='. $en_page.'&chkck');
			exit;
		}
    }
}


function plugin_secret_action(){
    global $vars, $script;
    $qm = get_qm();
    
    $page = isset($vars['page']) ? $vars['page'] : '';
    $errmsg = isset($vars['errmsg']) ? $vars['errmsg'] : '';
    $page_title = urldecode($page);
    
    $action_url = $script . "?" . urlencode($page);
    $title = $qm->m['plg_secret']['hdr'];
    
    if($errmsg == "true"){
        $caution = "<p><strong>" . $qm->m['plg_secret']['err_passwd'] . "</strong></p>";
    }else{ $caution = ''; }
    
    
    $body = <<<EOD
    {$caution}
    
<h2>{$title}</h2>
<form method="post" action="{$action_url}">
<div style="margin:0 auto;width:500px;">
<p>ユーザー名・パスワードを入力し、ログインボタンを押して下さい。</p>
<table style="margin:10px auto;border-collapse: collapse;text-align:center;" >
  <tr>
    <td>{$qm->m['username']}　</td>
    <td><input type="text" name="username"></td>
  </tr>
  <tr>
    <td>{$qm->m['password']}　</td>
    <td><input type="password" name="password"></td>
  </tr>
  <tr>
    <td colspan="2" align="center">
    <input type="submit" name="send" value="ログイン">
    <input type="hidden" name="secretplugin" value="secretplugin">
    </td>
  </tr>
</table>
</div>
</form>
EOD;
    
    return array('msg'=>$qm->replace('plg_secret.title', $page_title), 'body'=>$body);
    
}


function plugin_secret_is_edit_auth($page, $user = '')
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
