<?php

// ---------------------------------------------
// absplit.inc.php   v. 0.9
//
// 任意のページにリダイレクトする
// URLを設定しない場合は、トップへ
// 管理モードで以外で表示したくないページに使うと便利
// writed by hokuken.com 2007 8/24
// ----------------------------------------------- 


function plugin_absplit_convert()
{
	global $vars, $script;
	$qm = get_qm();
	$qt = get_qt();
    $page = isset($vars['page']) ? $vars['page'] : '';
    
	//--- キャッシュを無効に ---
	$qt->enable_cache = false;
    
    $args = func_get_args();
    $num = func_num_args();
    
    //check
    if($num != 2){
        return "<p>{$qm->m['plg_absplit']['err_param_count']}</p>";
    }
    
    $url = array();
    $url[1] = strip_autolink(array_pop($args));
    $url[0]= strip_autolink(array_pop($args));
    
    if(!is_url($url[0])){
        return "<p> ". $qm->replace('plg_absplit.err_invalid_url', $url[0]). "</p>";
     }
    if(!is_url($url[1])){
        return "<p> ". $qm->replace('plg_absplit.err_invalid_url', $url[1]). "</p>";
     }
    
    //edit auth check
    $editable = edit_auth($page, FALSE, FALSE);
    
    if($editable){
    	return $qm->replace('plg_absplit.ntc_admin', $url[0], $url[1], $page);
    }
    else{
        
        //generate cookie name from $page
        $ckname = encode($page);
        
        
        if(isset($_COOKIE[$ckname])){
            $target = $_COOKIE[$ckname];
            header("Location: " . $url[$target]);
            exit();
        }
        else{
            $target = rand(0,1);
            
            //set cookie for split test
            $result = setcookie($ckname, $target, time() + 60 * 60 * 24 * 30);
            
            header("Location: " . $url[$target]);
            exit();
        }
    }
}


?>
