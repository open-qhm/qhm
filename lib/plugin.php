<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: plugin.php,v 1.15 2005/07/03 14:16:23 henoheno Exp $
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// Plugin related functions

define('PKWK_PLUGIN_CALL_TIME_LIMIT', 768);
define('PKWK_PLUGIN_NAMESPACE', 'QHM\\Plugin\\');

// Set global variables for plugins
function set_plugin_messages($messages)
{
	foreach ($messages as $name=>$val)
		if (! isset($GLOBALS[$name]))
			$GLOBALS[$name] = $val;
}

// Get plugin prefix with namespace
function get_plugin_prefix($name)
{
	static $prefixes = array();
	if (isset($prefixes[$name])) {
		return $prefixes[$name];
	}
	if (strpos($name, '/') > 0) {
		list($ns, $name) = explode('/', $name);

		// namespace が使えない場合のフォールバックを用意しているが、
		// exist_plugin で真にならないため、実際に呼び出すことはない

		$prefixes[$name] = version_compare(PHP_VERSION, 5.3, '<')
			? "plugin_{$ns}___{$name}_"
			: PKWK_PLUGIN_NAMESPACE . "{$ns}\\plugin_{$name}_";
	} else {
		$prefixes[$name] = "plugin_{$name}_";
	}
	return $prefixes[$name];
}
// Check plugin '$name' is here
function exist_plugin($name)
{
	global $vars;
	static $exist = array(), $count = array();

	$name = strtolower($name);
	if(isset($exist[$name])) {
		if (++$count[$name] > PKWK_PLUGIN_CALL_TIME_LIMIT)
			die('Alert: plugin "' . htmlspecialchars($name) .
			'" was called over ' . PKWK_PLUGIN_CALL_TIME_LIMIT .
			' times. SPAM or someting?<br />' . "\n" .
			'<a href="' . get_script_uri() . '?cmd=edit&amp;page='.
			rawurlencode($vars['page']) . '">Try to edit this page</a><br />' . "\n" .
			'<a href="' . get_script_uri() . '">Return to frontpage</a>');
		return $exist[$name];
	}

	// namespace が使える場合のみ foo/bar を受け付ける
	$regex = '/^\w{1,64}(?:\/\w{1,64})?$/';
	if (version_compare(PHP_VERSION, 5.3, '<')) {
		$regex = '/^\w{1,64}$/';
	}
	if (preg_match($regex, $name) &&
		file_exists(PLUGIN_DIR . $name . '.inc.php')) {
			$exist[$name] = TRUE;
			$count[$name] = 1;
		require_once(PLUGIN_DIR . $name . '.inc.php');
		return TRUE;
	} else {
		$exist[$name] = FALSE;
		$count[$name] = 1;
		return FALSE;
	}
}

// Check if plugin API 'action' exists
function exist_plugin_action($name) {
	$func = get_plugin_prefix($name) . 'action';
	return	function_exists($func) ? TRUE : exist_plugin($name) ?
		function_exists($func) : FALSE;
}

// Check if plugin API 'convert' exists
function exist_plugin_convert($name) {
	$func = get_plugin_prefix($name) . 'convert';
	return	function_exists($func) ? TRUE : exist_plugin($name) ?
		function_exists($func) : FALSE;
}

// Check if plugin API 'inline' exists
function exist_plugin_inline($name) {
	$func = get_plugin_prefix($name) . 'inline';
	return	function_exists($func) ? TRUE : exist_plugin($name) ?
		function_exists($func) : FALSE;
}

// Do init the plugin
function do_plugin_init($name)
{
	static $checked = array();

	if (isset($checked[$name])) return $checked[$name];

	$func = get_plugin_prefix($name) . 'init';
	if (function_exists($func)) {
		// TRUE or FALSE or NULL (return nothing)
		$checked[$name] = call_user_func($func);
	} else {
		$checked[$name] = NULL; // Not exist
	}

	return $checked[$name];
}

// Call API 'action' of the plugin
function do_plugin_action($name)
{
	if (! exist_plugin_action($name)) return array();

	if(do_plugin_init($name) === FALSE)
		die_message('Plugin init failed: ' . $name);

	$retvar = call_user_func(get_plugin_prefix($name) . 'action');

	// Insert a hidden field, supports idenrtifying text enconding
	if (PKWK_ENCODING_HINT != '')
		$retvar =  preg_replace('/(<form[^>]*>)/', '$1' . "\n" .
			'<div><input type="hidden" name="encode_hint" value="' .
			PKWK_ENCODING_HINT . '" /></div>', $retvar);

	return $retvar;
}

// Call API 'convert' of the plugin
function do_plugin_convert($name, $args = '')
{
	global $digest;

	if(do_plugin_init($name) === FALSE)
		return '[Plugin init failed: ' . $name . ']';

	if (! PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK) {
		// Multiline plugin?
		$pos  = strpos($args, "\r"); // "\r" is just a delimiter
		if ($pos !== FALSE) {
			$body = substr($args, $pos + 1);
			$args = substr($args, 0, $pos);
		}
	}

	if ($args === '') {
		$aryargs = array();                 // #plugin()
	} else {
		$aryargs = csv_explode(',', $args); // #plugin(A,B,C,D)
	}
	if (! PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK) {
		if (isset($body)) $aryargs[] = & $body;     // #plugin(){{body}}
	}

	$_digest = $digest;
	$retvar  = call_user_func_array(get_plugin_prefix($name) . 'convert', $aryargs);
	$digest  = $_digest; // Revert

	if ($retvar === FALSE) {
		return htmlspecialchars('#' . $name .
			($args != '' ? '(' . $args . ')' : ''));
	} else if (PKWK_ENCODING_HINT != '') {
		// Insert a hidden field, supports idenrtifying text enconding
		return preg_replace('/(<form[^>]*>)/', '$1 ' . "\n" .
			'<div><input type="hidden" name="encode_hint" value="' .
			PKWK_ENCODING_HINT . '" /></div>', $retvar);
	} else {
		return $retvar;
	}
}

// Call API 'inline' of the plugin
function do_plugin_inline($name, $args, & $body)
{
	global $digest;

	if(do_plugin_init($name) === FALSE)
		return '[Plugin init failed: ' . $name . ']';

	if ($args !== '') {
		$aryargs = csv_explode(',', $args);
	} else {
		$aryargs = array();
	}

	// NOTE: A reference of $body is always the last argument
	$aryargs[] = & $body; // func_num_args() != 0

	$_digest = $digest;
	$retvar  = call_user_func_array(get_plugin_prefix($name) . 'inline', $aryargs);
	$digest  = $_digest; // Revert

	if($retvar === FALSE) {
		// Do nothing
		return htmlspecialchars('&' . $name . ($args ? '(' . $args . ')' : '') . ';');
	} else {
		return $retvar;
	}
}
