<?php
// $Id: keywords.inc.php,v 1.1 2007/10/06 00:06:30 hokuken Exp $
// License: The same as PukiWiki
//
// Nowindow plugin

function plugin_keywords_convert()
{
	global $keywords;
	
	$args = func_get_args();
	
	if( count($args) > 0){
		$keywords = '';
		foreach($args as $key)
			$keywords .= html_entity_decode($key).',';
	}
	
	return '';
}
?>
