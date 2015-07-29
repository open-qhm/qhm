<?php
/**
 *   Only mobile Plugin
 *   -------------------------------------------
 *   /plugin/only_mobile.inc.php
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
 *   This plugin can show data when pc access. max-width: 768px from bootstrap
 *
 */

function plugin_only_mobile_convert()
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
<div id="plugin_only_mobile_{$cnt}" class="plugin-only-mobile visible-xs visible-xs-block">
  {$body}
</div>
EOH;

    return $html;
}

