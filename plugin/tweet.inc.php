<?php
/**
 *   Tweet Plugin
 *   -------------------------------------------
 *   tweet.inc.php
 *   
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2010-05-06
 *   modified :
 *   
 *   USAGE:
 *     &tweet();
 */

define('PLUGIN_TWEET_FORMAT', 'RT @%username% %title% - %url%');
define('PLUGIN_TWEET_DEFAULT_LABEL', 'このページをRTする');
define('PLUGIN_TWEET_DEFAULT_STATUS', PLUGIN_TWEET_FORMAT);

function plugin_tweet_inline() {
	global $script, $vars;
	$page = $vars['page'];
	$args = func_get_args();
	$label = array_shift($args);
	$status = array_shift($args);
	
	$label = $label? trim($label): PLUGIN_TWEET_DEFAULT_LABEL;
	$status = $status? trim($status): PLUGIN_TWEET_DEFAULT_STATUS;
	
	if (preg_match('/^ICON:([mslt])([abc])([ops])?:(.*)$/', $label, $ms)) {
		$isize = $ms[1];
		$icolor = $ms[2];
		$disp = $ms[3];
		$label = $ms[4]? $ms[4]: PLUGIN_TWEET_DEFAULT_LABEL;
		
		$icon = '';
		$ic_w = $ic_h = 16;
		switch ($isize) {
			case 's':
				$icon .= 't_small';
				$ic_w = $ic_h = 22;
				break;
			case 'l':
				$icon .= 't_logo';
				$ic_w = $ic_h = 36;
				break;
			case 't':
				$icon .= 'twitter';
				$ic_w = 61;
				$ic_h = 23;
				break;
			default:
				$icon .= 't_mini';
		}
		$icon .= '-';
		if (in_array($icolor, array('a', 'b', 'c'))) {
			$icon .= $icolor;
		} else {
			$icon .= 'a';
		}
		
		$icon .= '.png';
		$icon = '<img src="http://twitter-badges.s3.amazonaws.com/'. $icon. '" width="'.$ic_w.'" height="'.$ic_h.'" />';
		
		switch($disp) {
			case 'p': //前置
				$icon .= $label;
				break;
			case 's': //後置
				$icon = $label. $icon;
				break;
			default:
		}
	}
	
	$option = array(
		'username' => '',
		'title' => get_page_title($page),
		'url' => $script. '?go='. get_tiny_code($page),
	);
	foreach ($args as $arg) {
		if (strpos($arg, '=')) {
			list($key, $val) = explode('=', $arg, 2);
			$option[$key] = $val;
		}
		//引数がhoge=piyo の形式でない場合、username と見なす
		else if (trim($arg)){
			$option['username'] = $arg;
			break;
		}
	}
	
	$srcs = array_keys($option);
	$rpls = array();
	foreach ($srcs as $i => $key) {
		$srcs[$i] = '%'. $key. '%';
		$rpls[$i] = $option[$key];
	}
	
	$status = str_replace($srcs, $rpls, $status);

	$twurl = 'http://twitter.com/?status=';
	$twurl .= rawurlencode($status);
	
	$ret = '<a href="'.$twurl.'" title="'.$label.'" target="_black">'.(isset($icon)? $icon: $label).'</a>';
	
	return $ret;

}

?>