<?php

// ---------------------------------------------
// redirect.inc.php   v. 0.9
//
// 任意のページにリダイレクトする
// URLを設定しない場合は、トップへ
// 管理モードで以外で表示したくないページに使うと便利
// writed by hokuken.com 2007 8/24
// ----------------------------------------------- 


function plugin_redirect_iframe_convert()
{
	global $vars, $script;
    $qm = get_qm();
    
    $page = isset($vars['page']) ? $vars['page'] : '';
    
    $args = func_get_args();
    $url = strip_autolink(array_pop($args));
    
    if($url == ''){
    	$url = $script .'?'. rawurlencode($page);
    }
    else{
       $url = is_url($url) ? $url : $script . '?'.rawurlencode($url);
    }
    
    $editable = ss_admin_check();
    
    if($editable){
        return $qm->replace('plg_redirect_iframe.ntc_admin', $url);
    }
    else{
    	
    	return <<<EOD
<span id="redirect_iframe_msg"><p style="color:gray"><a href="$url" target="new">{$qm->m['plg_redirect_iframe']['link']}</a></p></span>
<script type="text/javascript">
<!--
if(parent != self){
parent.location.href="$url";
}
else{
var element = document.getElementById("redirect_iframe_msg");
element.innerHTML = "";
}

//-->
</script>
EOD;

    }
}


?>
