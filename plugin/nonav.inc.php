<?php
/**
 *   Do not show Nav plugin
 *   -------------------------------------------
 *
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2014-08-28
 *   modified :
 *
 */

function plugin_nonav_convert()
{
    $qt = get_qt();
    $qt->setv('no_site_navigator', TRUE);
    plugin_nonav_set_js();
}

function plugin_nonav_set_js()
{
    $qt = get_qt();
    $addscript = <<< EOD
<script>
$(function(){
  $("button.navbar-toggle").hide();
});
</script>
EOD;
    $qt->appendv_once('plugin_nonav_script', 'lastscript', $addscript);
}
