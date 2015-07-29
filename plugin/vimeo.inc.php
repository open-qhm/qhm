<?php
/**
 * Display Vimeo Player Plugin
 * -------------------------------------------
 * ./plugin/vimeo.inc.php
 * 
 * Copyright (c) 2011 hokuken
 * http://hokuken.com/
 * 
 * created  : 2011-09-02
 * modified : 2011-11-28
 * 
 * Vimeo のiframe プレーヤーを貼り付けます。
 * SSLアクセスの場合は自動的にhttps へ切り替えます。
 * 
 * Usage : #vimeo(vimeo_id, [size[, align option[, color]]])
 *   size: dimension format is WIDTHxHEIGHT
 *   align: block alignment [left, center, right, arroundl, arroundr]
 *   color: color format is RRGGBB
 * 
 */

define('PLUGIN_VIMEO_DEFAULT_WIDTH', 512);
define('PLUGIN_VIMEO_DEFAULT_HEIGHT', 384);

function plugin_vimeo_convert()
{
	global $script, $vars;
	
	$args = func_get_args();
	$vimeo_id = array_shift($args);
	
	$wrapper = $color = $width = $height = FALSE;
	$style = '';
	foreach ($args as $arg)
	{
		$arg = trim($arg);
		// size: widthxheight
		if (preg_match('/^(\d+)x(\d+)$/', $arg, $mts))
		{
			$width = $mts[1];
			$height = $mts[2];
		}
		//align: left, center, right
		else if (in_array($arg, array('left', 'center', 'right')))
		{
			$wrapper = '<div style="text-align:'. $arg. '">%s</div>';
		}
		//float option: arround left, arround right
		else if (in_array($arg, array('arroundl', 'arroundr')))
		{
			$align = (substr($arg, -1, 1) === 'r')? 'right': 'left';
			$margin = ($align === 'right')? 'left': 'right';
			$wrapper = '<div style="float:'. $align. ';">%s</div>';
			$style = 'margin-'. $margin. ':10px;';
		}
		//color: RRGGBB
		else if (preg_match('/^[0-9a-f]{6}$/i', $arg))
		{
			$color = $arg;
		}
	
	}
	
	$player_queries = array();
	
	// size: widthxheight
	$width = $width? $width: PLUGIN_VIMEO_DEFAULT_WIDTH;
	$height = $height? $height: PLUGIN_VIMEO_DEFAULT_HEIGHT;
	
	// color setting
	if ($color)
	{
		$player_queries[] = 'color='. rawurlencode($color);
	}
	
	// create player url
	$player = 'http'. (is_ssl()? 's': ''). '://player.vimeo.com/video/'. rawurlencode($vimeo_id);
	if (count($player_queries) > 0)
	{
		$player .= '?'. join('&', $player_queries);
	}
	
	// create iframe
	$body = sprintf('<iframe src="%s" width="%d" height="%d" frameborder="0" style="%s"></iframe>', $player, $width, $height, $style);
	
	if ($wrapper !== FALSE)
	{
		$body = sprintf($wrapper, $body);
	}

	return $body;
}

/* End of file vimeo.inc.php */
/* Location: ./plugin/vimeo.php */
