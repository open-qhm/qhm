<?php
/*
 * ga_link.inc.php
 * --------------------
 *
 * Google Anayticsで、パラメータを引き付いてリダイレクトするためのもの
 *
 * 注意: 
 * 　新バージョンのGoogle Analyticsのみに対応
 *
 *
*/
function plugin_ga_link_inline()
{
    global $accesstag_moved;
    global $accesstag;
    global $vars;
    $qm = get_qm();
	$qt = get_qt();
    
	$page = isset($vars['page']) ? $vars['page'] : '';

    //parameter check
    $args = func_get_args();
    $num = func_num_args();
    
    if($num != 2){
    	return $qm->replace('fmt_err_iln', 'ga_link', $qm->m['plg_ga_link']['err_usage']);
    }
    
    $text = strip_autolink(array_pop($args));  //テキストデータ
    $url = array_pop($args);
    
    if(!is_url($url)){
        $url = 'index.php?' . rawurlencode($url);
    }
    
    $acc_tag = '';
    
    //初めて呼び出される場合、accesstag情報を表示する
    if(!$accesstag_moved){
        $accesstag_moved = TRUE;  //accesstag_movedを設定
        
		$editable = edit_auth($page, FALSE, FALSE);
		if(!$editable){
        	$qt->setv('toolkit_upper', $accesstag);
        }
    }
    
	//Google Analytics用のjavascriptを先に読み込んでから、関数を呼び出す
	$ret = '<a href="index.php?cmd=ga_link&url=' 
		. rawurlencode( $url ) . '" onClick="javascript:pageTracker._link(this.href);return false;">' . $text . '</a>';
    
    return $ret;	
}


/**
* アクションプラグイン
*
* GoogleAnalyticsのコードを付けたまま、リダイレクトを行う
*/
function plugin_ga_link_action()
{

	global $get;
	$url = $get['url'];
	
	$excluding = array('url', 'page', 'cmd', 'plugin');
	foreach($excluding as $key){
		if( isset($get[$key]) )
			unset($get[$key]);
	}
	
	
	if( strpos($url, '?') ){
		$url .= '&';
	}
	else{
		$url .= '?';
	}
	
	foreach($get as $k=>$v){
		$url .= $k.'='.$v.'&';
	}

	header('Location: '. $url);
	exit;
    
}

?>
