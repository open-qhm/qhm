<?php

// ---------------------------------------------
// conversion_r.inc.php   v. 0.9
//
// 簡易のコンバージョン率を計測するためのプラグインを
// リモートから実行するためのプラグイン
// viewerは、?cmd=conversion です
// なお、クリックをリモートのQHMで計測する場合は、&conversion(); となります
// writed by hokuken.com 2007 8/24
// ----------------------------------------------- 

function plugin_conversion_r_inline(){
	global $vars, $script;
	$qm = get_qm();
    $page = isset($vars['page']) ? $vars['page'] : '';
    
    
    $args = func_get_args();
    if( count($args) != 6 ){
    	return $qm->replace('fmt_err_iln', 'conversion_r', $qm->m['plg_conversion_r']['err_usage']);
    }
    
    $text = array_pop($args);
    list($step, $group, $name, $url, $site_url) = $args;
    if( !is_url($url)){
    	return $qm->replace('fmt_err_iln', 'conversion_r', $qm->m['plg_conversion_r']['err_url']);
    }
    
    $raw_url = $url;
    
    $step = rawurlencode($step);
    $group = rawurlencode($group);
    $name = rawurlencode($name);
    $url = rawurlencode($url);
    
    $site_url .= ( preg_match('/\/$/', $site_url) ) ? '' : '/';    
    
    $dest = $site_url.'?cmd=conversion&mode=link&step='
    		.$step.'&group='.$group.'&name='.$name.'&url='.$url;
    
    
    //edit auth check
    $editable = edit_auth($page, FALSE, FALSE);
    if($editable){
    	return '<a href="'.$dest.'">'.$text.'</a><span style="font-size:11px;background-color:#fdd">←'. $qm->m['plg_conversion_r']['ntc_admin']. '</span>';
    }
    else{
    	return '<a href="'.$dest.'">'.$text.'</a>';
    }
}

?>
