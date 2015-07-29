<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: br.inc.php,v 1.4 2005/03/19 01:26:53 henoheno Exp $
//
// Forcing a line break plugin

// Escape using <br> in <blockquote> (BugTrack/583)
define('PLUGIN_BR_ESCAPE_BLOCKQUOTE', 1);

// ----

define('PLUGIN_BR_TAG', '<br class="spacer" />');

function plugin_br_convert()
{
	static $id = 0;
	$id++;

	$args = func_get_args();
	$margin = '';
	foreach ($args as $arg)
	{
		$arg = trim($arg);
		if ($arg === '')
		{
			continue;
		}
		if (preg_match('/\A(-?[\d.]+)(.*)\z/', $arg, $mts))
		{
			$margin = $mts[1] . ($mts[2] ? $mts[2] : 'px');
		}
	}

	if (PLUGIN_BR_ESCAPE_BLOCKQUOTE)
	{
		$style = '';
		$value = '&ensp;';
		if  ($margin !== '')
		{
			$style = ' style="margin-top:'.$margin.'"';
			$value = '';
		}
		return '<div id="plugin_br_'.$id.'" class="spacer"'.$style.'>'.$value.'</div>';
	}
	else
	{
		return PLUGIN_BR_TAG;
	}
}

function plugin_br_inline()
{
	return PLUGIN_BR_TAG;
}
