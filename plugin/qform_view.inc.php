<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: qform.inc.php,v 1.36 2006/01/28 14:54:51 teanan Exp $
// Copyright (C)  hokuken.biz
// GPL

/**
 *
 * convert plugins
 *
 */
function plugin_qform_view_convert(){

	//--- キャッシュを無効に ---
	$qt = get_qt();
	$qt->enable_cache = false;
	$qm = get_qm();


	global $vars, $script;
	$page = $vars['page'];
	
	$args = func_get_args();
	$pstr = array_pop($args);

	
	$body = '<textarea readonly="readonly" rows="20" style="font-size:12px;width:100%" onclick="this.select();">'."\n";
	$body .= htmlspecialchars($pstr);
	$body .= '</textarea>';
	
	$body .= '
<form method="post"	action="'.$script.'">
<input type="submit" name="submit" value="'. $qm->m['download'].'" />
<input type="hidden" name="cmd" value="qform_view" />
<input type="hidden" name="page" value="'.$page.'" />
</form>
';
	
	return $body;

}

function plugin_qform_view_action(){

	global $vars;
	$page = $vars['page'];
	
	$str = ''; $f = false;
	$lines = get_source($page);
	foreach($lines as $l){
		if(trim($l) === '}}')
			$f = false;

		if($f)
			$str .= $l;
	
		if( preg_match('/^#qform_view(.*)/', $l) )
			$f = true;
	}

	header("Cache-Control: public");
	header("Pragma: public");
	header("Accept-Ranges: none");
	header("Content-Transfer-Encoding: binary");
	header("Content-Disposition: attachment; filename=qform.csv");
	header("Content-Type: application/octet-stream; name=qform.csv"); 
		
	echo mb_convert_encoding( $str, 'Shift_JIS' );

	exit();
}


?>
