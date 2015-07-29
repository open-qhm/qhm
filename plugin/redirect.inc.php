<?php

// ---------------------------------------------
// redirect.inc.php   v. 0.9
//
// 任意のページにリダイレクトする
// URLを設定しない場合は、トップへ
// 管理モードで以外で表示したくないページに使うと便利
// writed by hokuken.com 2007 8/24
// ----------------------------------------------- 

function plugin_redirect_convert()
{
	global $vars, $script;
	$qm = get_qm();
	$qt = get_qt();
	
	//キャッシュしない
	$qt->enable_cache = false;
     
    $page = isset($vars['page']) ? $vars['page'] : '';
    
    $args = func_get_args();
    $url = strip_autolink(array_shift($args));
    
    $status = array_shift($args);
    $status_codes = array(
    	'301' => 'HTTP/1.1 301 Moved Permanently'
    );
    if (isset($status_codes[$status]))
    {
    	$headers[] = $status_codes[$status];
    }
    
    if($url == ''){
    	$url = $script.'?FrontPage';
    }
    else{
       $url = is_url($url) ? $url : $script . '?'.rawurlencode($url);
    }
        
    $editable = ss_admin_check();
    
    
    //自分自身にリダイレクトして、ループする場合は警告する
    if($url===$script.'?'.rawurlencode($page)){
     
        return $qm->m['plg_redirect']['err_self_ref'];
     }
    
    if($editable)
    {
        return $qm->replace('plg_redirect.ntc_admin', $url);
    }
    else
    {
    	$headers[] = 'Location: '. $url;
		foreach ($headers as $header)
		{
			header($header);
		}
        exit;
    }
}


?>
