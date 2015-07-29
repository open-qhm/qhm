<?php
// Google Analyticsのトラッキングを支援するプラグイン
// 新しいトラッキングコードに対応しています。
// 新旧２つのバージョンに自動的に対応します
// ver1.1
// 
function plugin_ganatracker_inline()
{
    global $accesstag_moved;
    global $accesstag;
    global $vars;
    $qm = get_qm();
    
	$page = isset($vars['page']) ? $vars['page'] : '';

    //parameter check
    $args = func_get_args();
    $num = func_num_args();
    
    if($num != 3){
        return $qm->replace('fmt_err_iln', 'ganatracker', $qm->m['plg_ganatracker']['err_usage_iln']);
    }
    
    $text = strip_autolink(array_pop($args));  //テキストデータ
    $tname = array_pop($args);
    $url = array_pop($args);
    
    if(!is_url($url)){
        $url = 'index.php?' . rawurlencode($url);
    }
    
    $acc_tag = '';
    
    //初めて呼び出される場合、accesstag情報を表示する
    if(!$accesstag_moved){
        $acc_tag = $accesstag;
        $accesstag_moved = 1;  //accesstag_movedを設定
    }
    
	
	//Google Analytics Tracking code check ( New or Old)
	//新しいものは、gaJsHost変数が使われている
	//
	$version = $qm->m['plg_ganatracker']['old'];
	if(strstr($accesstag, "gaJsHost") == false){ //Old version
	    $ret = $acc_tag . '<a href="' . $url . '" onClick="javascript:urchinTracker(\'' . $tname . '\');">' . $text . '</a>';
	}
	else{ //New version or none or invalid tag
		$version = $qm->m['plg_ganatracker']['new'];
		$ret = $acc_tag . '<a href="' . $url . '" onClick="javascript:pageTracker._trackPageview(\'' . $tname . '\');">' . $text . '</a>';
	}
    
    $editable = edit_auth($page, FALSE, FALSE);
	if($editable){
		return '<div style="border:2px dashed #f00;background-color:#fee;margin:1em">' . $ret . "<br />". $qm->replace('plg_ganatracker.ntc_admin', $version, $tname). "</div>";
	}
	else{
	    return $ret;	
	}
}

function plugin_ganatracker_convert()
{
    global $accesstag;
    global $vars;
    $qm = get_qm();
    
	$page = isset($vars['page']) ? $vars['page'] : '';
    $args = func_get_args();
    $num = func_num_args();
    
    if($num != 1){
        return $qm->replace('fmt_err_cvt', 'ganatracker', $qm->m['plg_ganatracker']['err_usage_cvt']);
    }
    
    $name = $args[0];
	
	//versionをチェックして、置換
	$version = $qm->m['plg_ganatracker']['old'];
	if(strstr($accesstag, "gaJsHost") == false){ //Old version
		$repstr = 'urchinTracker(\'' . $name . '\')';
    	$accesstag = str_replace('urchinTracker()', $repstr, $accesstag);
	}
	else{
		$version = $qm->m['plg_ganatracker']['old'];
	    $repstr = 'pageTracker._trackPageview(\'' . $name . '\')';
    	$accesstag = str_replace('pageTracker._trackPageview()', $repstr, $accesstag);
    }
	
    $editable = edit_auth($page, FALSE, FALSE);
	if($editable){
		return '<div style="margin:1em;border:dashed 2px #f00;background-color:#fee">'. $qm->replace('plg_ganatracker.ntc_admin_cvt', $version, $name). '</div>';
	}
	else{
	    return '';	
	}
}

?>
