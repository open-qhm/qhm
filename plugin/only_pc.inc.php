<?php
/**
 *   Only pc Plugin
 *   -------------------------------------------
 *   /plugin/only_pc.inc.php
 *
 *   Copyright (c) 2015 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2015/06/19
 *   modified : 
 *
 *   Description
 *
 *   Usage :
 *   This plugin can show data when pc access. min-width: 768px from bootstrap
 *
 */

function plugin_only_pc_convert()
{
    global $script;
    static $cnt = 0;
	$cnt++;

    $qt = get_qt();

    $args = func_get_args();
    $body = array_pop($args);

    $body = str_replace("\r", "\n", str_replace("\r\n", "\n", $body));

    // ! make html
    $body = convert_html($body);

    $html = <<< EOH
<div id="plugin_only_pc_{$cnt}" class="plugin-only-pc hidden-xs">
  {$body}
</div>
EOH;

    return $html;
}

