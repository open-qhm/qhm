<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: modoru.inc.php,v 0.5 2007/10/30 15:04:08 henoheno Exp $
//
// modoru inline view plugin

function plugin_modoru_inline()
{
	$qm = get_qm();
	$args = func_get_args();
	$args_cnt = count( $args );
	list($label, $selbutton) = array_pad($args, 2, '');
	
	if($label == ""){
		$label = $qm->m['plg_modoru']['label'];
	}
	
	if($selbutton == "button"){
		return '<input type="button" value="'.$label.' "onclick="javascript:history.back();return false;">';
	}
	else{
		return '<a href="#" onclick="javascript:history.back();return false;">'.$label.'</a>';
	}
}
?>
