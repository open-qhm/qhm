<?php
// QHM deco プラグイン
// $Id: deco.inc.php,v 1.22 2005/06/16 15:04:08 henoheno Exp $
//
// Text color plugin

// ----
define('PLUGIN_DECO_USAGE', '&deco(option1[,option2,option3,option4]){text};');
define('PLUGIN_DECO_REGEX', '/^(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z-]+)$/i');

function plugin_deco_inline()
{
	global $pkwk_dtd;
	
	$args = func_get_args();
	$text = strip_autolink(array_pop($args)); // Already htmlspecialchars(text)

	if(! isset($args[0])) //引数が足りない
		return PLUGIN_DECO_USAGE;

	//分解
	$color = array(); $ccnt = 0;
	$size = '';
	$weight = '';
	$underline = '';
	$italic = '';
	$strong = false;
	$strike = false;
	$handline = false;

	$stg_s = '';
	$stg_e = '';
	
	foreach($args as $v){
		if( is_numeric($v) ){
			$size = $v.'px';
		}
		else if (preg_match('/^(\d|\.)/', $v)) {
			$size = $v;
		}
		else if (preg_match('/small|medium|large/', $v)) {
			$size = $v;
		}
		else if( $v=='bold' || $v=='b' ){
			$strong = true;
		}
		else if( $v=='underline' || $v=='u'){
			$underline = 'text-decoration:underline;';
		}
		else if( $v=='italic' || $v=='i'){
			$italic = 'font-style:italic;';
		}
		else if ($v == 'strike' || $v == 's') {
			$strike = true;
		}
		else if ($v == 'handline' || $v == 'h') {
			$handline = true;
		}
		else if( preg_match(PLUGIN_DECO_REGEX, $v) ){
			$color[$ccnt] = $v;
			$ccnt++;
		}
		else if($v==''){
			$color[$ccnt] = 'inherit';
			$ccnt++;
		}
	}
	
	$style = 'style="';
	$style .= $size=='' ? '' : 'font-size:'.$size.';';
	$style .= isset($color[0]) && $color[0]!='' ? 'color:'.$color[0].';' : '';
	$style .= isset($color[1]) && $color[1]!='' ? 'background-color:'.$color[1].';' : '';
	$style .= $underline.$italic;
	$style .= '" ';

	if ($strong) {
		$stg_s = '<strong>';
		$stg_e = '</strong>';
	}
	if ($strike) {
		$stg_s .= '<del>';
		$stg_e = '</del>' . $stg_e;
	}
	if ($handline) {
		$stg_s .= '<span class="handline">';
		$stg_e = '</span>' . $stg_e;
	}
	
	return '<span ' . $style . ' class="qhm-deco">'. $stg_s .$text . $stg_e . '</span>';
}
/* End of file deco.inc.php */
/* Location: /plugin/deco.inc.php */