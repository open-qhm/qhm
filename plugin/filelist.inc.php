<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: filelist.inc.php,v 1.3 2005/01/09 08:16:28 henoheno Exp $
//
// Filelist plugin: redirect to list plugin
// cmd=filelist

function plugin_filelist_action()
{
    global $script, $vars;
        // check editable
    if(!ss_admin_check()){
		$vars['cmd'] = 'list';
    }
    
	return do_plugin_action('list');
}
?>
