<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: addacc.inc.php,v 0.5 2006/06/26 15:04:08 hokuken Exp $
//
// addacc inline view plugin

// Allow CSS instead of <font> tag
// NOTE: <font> tag become invalid from XHTML 1.1


function plugin_addacc_convert()
{
    global $accesstag, $vars;
	$page = $vars['page'];
    $qm = get_qm();
    $args = func_get_args();
    $num = func_num_args();
    
    if($num != 1){
    	return $qm->m['plg_addacc']['err_usage'];
    }
    
	$addedcode = array_pop($args);
    $accesstag .= "\n\n<!-- added follow code by addacc plugin -->\n" . $addedcode;
    
    
    $editable = edit_auth($page, FALSE, FALSE);
	if($editable){
		return $qm->replace('plg_addacc.ntc_admin', $accesstag);
    }
    else{
		    return '';    
    }
}
?>
