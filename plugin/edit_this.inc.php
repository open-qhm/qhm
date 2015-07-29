<?php

// Inline: Show edit (or unfreeze text) link
function plugin_edit_this_inline()
{
	global $vars, $script;
	$qm = get_qm();
	
	$page = $vars['page'];
	
	if( !edit_auth($page, FALSE, FALSE) ){
		return '';
	}
	
	$args = func_get_args();
	
	if( $args[0] != ''){
		$page = trim($args[0]);
	}
	
	return <<<EOD
<a href="{$script}?cmd=edit&page={$page}">[{$qm->m['plg_edit_this']['label']}]</a>
EOD;

}

?>
