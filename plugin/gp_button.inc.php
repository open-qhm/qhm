<?php
/**
 *   Google Plusone Button Plugin
 *   -------------------------------------------
 *   ./plugin/gp_button.inc.php
 *   
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2011-08-08
 *   modified :
 *   
 *   Description
 *   
 *   Usage :
 *   
 */

function plugin_gp_button_inline()
{
	$args = func_get_args();
	return plugin_gp_button_body($args);
}

function plugin_gp_button_convert()
{
	$args = func_get_args();
	return plugin_gp_button_body($args);
}

function plugin_gp_button_body($args)
{
	global $script, $vars;
	
	$page = $vars['page'];
	$qm = get_qm();
	$qt = get_qt();
	

	//options
	$attrs = array(
		'href' => '',
		'size' => '',
		'count' => ''
	);
	$opts = array(
		'lang' => 'ja',
		'parsetags' => ''
	);
	
	//設定できるオプションは以下
	//・グローバルオプション（$opts）
	//  lang: 言語。デフォルトでja
	//  parsetags: 表示タイミング。explicit を指定すると、gapi.plusone.go() を呼び出した時に指定した箇所に表示することが可能。
	//
	//・タグ属性（$attrs）
	//  href: URLを指定する。無指定の場合、貼り付けたURLを使う
	//  size: ボタンの大きさ。小・中・標準・大がある
	//  count: +数を表示するかどうか。countoff で表示しない設定
	
	foreach ($args as $arg) {
		//URL
		if (preg_match('/^https?:\/\//', $arg))
		{
			$attrs['href'] = $arg;
		}
		else if (in_array($arg, array('small', 'medium', 'tall')))
		{
			$attrs['size'] = $arg;
		}
		else if ($arg == 'countoff')
		{
			$attrs['count'] = 'false';
		}
		//explicit は任意の表示タイミングをQHMで制御できるようにしていないため非推奨。
		else if ($arg == 'explicit')
		{
			$opts['parsetags'] = 'explicit';
		}
		else
		{
			$opts['lang'] = $arg;
		}
	}
	
	if ($opts['parsetags'] == '')
	{
		unset($opts['parsetags']);
	}
	$json = json_encode($opts);
	$beforescript = '
<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
  '. $json. '
</script>
';
	//表示タイミング（parsetags）がexplicit の場合、onready に関連づける
	if (isset($opts['parsetags']))
	{
		$beforescript .= '<script type="text/javascript">
$(function(){
	gapi.plusone.go();
});
</script>
';
	}
	$qt->appendv_once('plugin_gp_button_convert', 'beforescript', $beforescript);

	$fmt = '<g:plusone%s></g:plusone>';
	$attr = '';
	
	//大サイズの場合、カウントは強制表示
	if ($attrs['size'] == 'tall')
	{
		unset($attrs['count']);
	}
	
	foreach ($attrs as $key => $val)
	{
		if ($val != '')
		{
			$attr .= sprintf(' %s="%s"', $key, $val);
		}
	}
	$body = sprintf($fmt, $attr);
	
	return $body;
}