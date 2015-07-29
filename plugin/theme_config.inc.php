<?php
/**
* QHM_SkinConfig の内容を変更する。
* テーマの config.php に定義していない値は設定できない
*
* 使い方：
*   #theme_config(key,value): 設定する
*   #theme_config(key): リセットする
*/
function plugin_theme_config_convert()
{
	$args = func_get_args();
	if (count($args) === 0)
	{
		return '<p class="text-danger">Usage: #theme_config(key,value)</p>';
	}

	$value = array_pop($args);
	if (strpos($value, "\r") !== FALSE)
	{
		$config = plugin_theme_config_parse($value);
	}
	else if (count($args) === 0)
	{
		plugin_theme_config_reset($value);
		return '';
	}
	else if (count($args) === 1)
	{
		$config = array(
			$args[0] => $value
		);
	}
	else
	{
		return '<p class="text-danger">Usage: #theme_config(key,value)</p>';
	}

	plugin_theme_config_set($config);
	return '';
}

function plugin_theme_config_parse($str)
{
	$config = array();
	foreach (preg_split("/(\r|\n)+/", $str) as $line)
	{
		list($key, $value) = explode('=', $line);
		$config[trim($key)] = is_null($value) ? null : trim($value);
	}
	return $config;
}

function plugin_theme_config_reset($key)
{
	global $style_name;
	QHM_SkinCustomVariables::set($style_name, $key);
}

function plugin_theme_config_set($config)
{
	global $style_name;
	foreach ($config as $key => $value)
	{
		QHM_SkinCustomVariables::set($style_name, $key, $value);
	}
}
