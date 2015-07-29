<?php
/**
 *   QHM Font Size Selector Plugin
 *   -------------------------------------------
 *   plugin/select_fsize.inc.php
 *
 *   Copyright (c) 2015 hokuken
 *   http://hokuken.com/
 *
 *   ファイルサイズを「大」「中」「小」の三種類から選べるようにします。
 *
 *   Usage :
 *     #select_fsize
 *     #select_fsize(notitle)
 *     #select_fsize(タイトル)
 *     #select_fsize(タイトル,center)
 *
 */
function plugin_select_fsize_convert()
{
	global $vars, $script;
	$qm = get_qm();

	//jquery ライブラリの読み込み
	$qt = get_qt();
	$qt->setv('jquery_include', true);
	$qt->setv('jquery_cookie_include', true);

	//param
	$args = func_get_args();
	if (isset($args[0]))
	{
		if ($args[0] === 'notitle')
			$text = '';
		else
			$text = trim($args[0]);
	}
	else
	{
		$text = $qm->m['plg_select_fsize']['lbl']. ':';
	}

	if (isset($args[1]) && preg_match('/^(left|right|center)$/i', $args[1]))
	{
		$align = $args[1];
	}
	else
	{
		$align = 'center';
	}

	//cookie path
	$v = parse_url($script);
	$path = str_replace('\\','', dirname($v['path'].'dummy') ); //windows iis対策
	$path = $path=='/' ? $path : $path.'/';

	plugin_select_fsize_set_assets();

	$ret = <<<EOD
<p class="plg_select_fsize" style="text-align:{$align}">
  {$text}<br>
  <a href="#" onclick="font('12px')">{$qm->m['plg_select_fsize']['s']}</a>
  <a href="#" onclick="font('14px')">{$qm->m['plg_select_fsize']['m']}</a>
  <a href="#" onclick="font('20px')">{$qm->m['plg_select_fsize']['l']}</a>
</p>
EOD;

	return $ret;
}

function plugin_select_fsize_set_assets()
{
	plugin_select_fsize_set_js();
	plugin_select_fsize_set_css();
}

function plugin_select_fsize_set_js()
{
	$qt = get_qt();

	$addscript = <<<EOD
<script>
$(function(){
  $("body").css("font-size", $.cookie('fsize'));
});
function font(size){
  $("body").css("font-size",size);
  $.cookie("fsize",size,{expires:0,path:'$path'});//※1
  return false;
}
</script>
EOD;

	$qt->appendv_once('plugin_select_fsize_js', 'beforescript', $addscript);
}

function plugin_select_fsize_set_css()
{
	$qt = get_qt();

	$addstyle = <<<EOD
<style>
.plg_select_fsize a {
  border: 1px solid #0033CC;
  padding: 3px;
  color: #0033CC;
  text-decoration: none;
}
.plg_select_fsize a:hover {
  color: white;
  background-color: #0033CC;
}
</style>
EOD;

	$qt->appendv_once('plugin_select_fsize_css', 'beforescript', $addstyle);
}
