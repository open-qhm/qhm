<?php

// ---------------------------------------------
// absplit2.inc.php   v. 0.9
//
// 任意のページを読み込み、表示する(includeを実行しているだけ)
// 読み込んだページは、COOKIEに保存するので、同じページが読み込まれる
// ことが期待できる。
//
// writed by hokuken.com 2007 8/24
// ----------------------------------------------- 

define('PLUGIN_ABSPLIT2_PREFIX', "qhmabsplit2_");

function plugin_absplit2_convert()
{
	$qt = get_qt();
	$qm = get_qm();

	//--- キャッシュを無効に ---
	$qt->enable_cache = false;

	global $vars, $script;
    $page = isset($vars['page']) ? $vars['page'] : '';
    
    $args = func_get_args();
    $num = func_num_args();
    
    //check
    if($num < 2){
        return "<p>{$qm->m['plg_absplit2']['err_page_count']}</p>";
    }
    
    $tmp_str = '';
    foreach($args as $test_page )
    {
    	if( is_page($test_page) )
    	{
    		$tmp_str .= $test_page . ', ';
    	}
    	else
    	{
    		return $qm->replace('plg_absplit2.err_not_exists', $test_page);
    	}
    }
    
    
    //edit auth check
    $editable = edit_auth($page, FALSE, FALSE);
    
    if($editable){
    	return $qm->replace('plg_absplit2.ntc_admin', $tmp_str, $page);
    }
    else{
        
        //generate cookie name from $page
        $ckname = PLUGIN_ABSPLIT2_PREFIX.encode($page);
        
        
        if(isset($_COOKIE[$ckname])){
            $target = $_COOKIE[$ckname];
        }
        else{
            $index = rand(0, count($args)-1 );
            $target = $args[$index];            
            $result = setcookie($ckname, $target, time() + 60 * 60 * 24 * 30);
        }

        $body = convert_html( get_source($target) );
        return $body;
    }
}


?>
