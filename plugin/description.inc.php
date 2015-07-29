<?php
function plugin_description_convert()
{
	global $description;

	$args   = func_get_args();
	$desc   = isset($args[0]) ? implode(',',$args) : $description;
	
	$description = htmlspecialchars($desc);
}

?>
