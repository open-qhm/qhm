<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: addacc.inc.php,v 0.5 2006/06/26 15:04:08 hokuken Exp $
//
// addacc inline view plugin

// Allow CSS instead of <font> tag
// NOTE: <font> tag become invalid from XHTML 1.1


function plugin_editacc_convert()
{
    global $accesstag, $vars;
    $page = $vars['page'];
    $qm = get_qm();
    $args = func_get_args();
    $num = func_num_args();
    
    if($num != 1){
    	return $qm->m['plg_editacc']['err_usage'];
    }
    
	$addedcode = array_pop($args);
    $accesstag = "\n\n<!-- {$qm->m['plg_editacc']['comment']} -->\n" . $addedcode;
    
    
    $editable = edit_auth($page, FALSE, FALSE);
	if($editable){
        return '<div style="border:2px dashed #f00;background-color:#fee;margin:1em">'. $qm->m['plg_editacc']['ntc_admin']. '<center><textarea rows="5" cols="50" disabled>' . $accesstag . '</textarea></center></div>';
    }
    else{
		    return '';    
    }
}
?>
