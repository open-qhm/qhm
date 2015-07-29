<?php
function plugin_enable_jquery_convert()
{
	$qt = get_qt();
	$qt->setv('jquery_include', true);
}