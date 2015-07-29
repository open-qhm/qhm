<?php
// Google Analyticsのトラッキングを支援するプラグイン
// 新しいトラッキングコードに対応しています。
// 以前のものを使う場合は、ganatrackerプラグインを用いてください
//
//
// 
function plugin_ganatracker2_inline()
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
        return $qm->replace('fmt_err_iln', 'ganatracker2', $qm->m['plg_ganatracker2']['err_usage_iln']);
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
    
    $ret = $acc_tag . '<a href="' . $url . '" onClick="javascript:pageTracker._trackPageview(\'' . $tname . '\');">'
    . $text . '</a>';
    
    $editable = edit_auth($page, FALSE, FALSE);
	if($editable){
		return '<div style="border:2px dashed #f00;background-color:#fee;margin:1em">' . $ret . '<br />'. $qm->replace('plg_ganatracker2.ntc_admin', $tname). '</div>';
	}
	else{
	    return $ret;	
	}
}

function plugin_ganatracker2_convert()
{
    global $accesstag;
    global $vars;
    $qm = get_qm();
    
	$page = isset($vars['page']) ? $vars['page'] : '';
    $args = func_get_args();
    $num = func_num_args();
    
    if($num != 1){
        return $qm->replace('fmt_err_cvt', 'ganatracker2', $qm->m['plg_ganatracker2']['err_usage_cvt']);
    }
    
    $name = $args[0];
    $repstr = 'pageTracker._trackPageview(\'' . $name . '\')';
    $accesstag = str_replace('pageTracker._trackPageview()', $repstr, $accesstag);
    
    $editable = edit_auth($page, FALSE, FALSE);
	if($editable){
		return '<div style="margin:1em;border:dashed 2px #f00;background-color:#fee">'. $qm->m['plg_ganatracker2']['ntc_admin_cvt']. '</div>';
	}
	else{
	    return '';	
	}
}

?>
