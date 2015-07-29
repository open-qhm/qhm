<?php
// $Id$

function plugin_permalink_convert()
{

	global $script, $vars;
	$qm = get_qm();
	
	$code = get_tiny_code($vars['page']);
	$url = $script.'?go='.$code;

	if( func_num_args() ){
		$args = func_get_args();
		$text = $args[0];
		
		if($text === 'nolabel'){
			$text = '';
		}
	}
	else{
		$text = $qm->m['plg_permalink']['label_surl']. ' : ';
	}
	
	$temp1 = '<p style="text-align:center"><span style="color:gray">'.$text.' <input type="text" value="%url%" readonly="readonly" onclick="this.select();" style="width:65%;color:gray" /></span></p>';
	
	return str_replace('%url%', $url, $temp1);
}
?>