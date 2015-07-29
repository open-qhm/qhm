<?php
// qcart.inc.php 
//
// This plugin set button & cart on your webpage.
// Ref: QuickCart http://at-factory.com/presents/quickcart_mini.html
//
// Please setup QuickCart library in ./qcart/cgi-bin & ./qcart/shop.
//

function plugin_qcart_convert(){
	global $script;
	$args = func_get_args();
	$cnt = func_num_args();

	$dir = 'qcart';
	$position = 'right';
	
	list($position, $dir) = array_pad($args,2,'');
	$dir = ($dir == '') ? 'qcart' : htmlspecialchars($dir);
	$position = ($position == '') ? 'right' : htmlspecialchars($position);
	$position = ($position == 'right' || $position == 'left') ? $position : 'right';
	
	if($position == 'right'){
		$position .= ';margin-left:1em;';
	}
	else{
		$position .= ';margin-right:1em;';
	}
	
	$addscript = <<<EOD
<script src="./{$dir}/shop/quickcart.js" type="text/javascript" language="javascript"></script>
EOD;
	$qt = get_qt();
	$qt->appendv_once('plugin_qcart', 'beforescript', $addscript);
	
	$myurl = str_replace('index.php','', $script);
	
	return <<<EOD
  <div style="width:200px;float:{$position};">
  <script>cart('{$myurl}{$dir}/cgi-bin/')</script>
  </div>
EOD;
	
}

function plugin_qcart_inline()
{
	$qm = get_qm();
	
	$dir ='qcart';
	
	$args = func_get_args();
	$cnt = func_num_args();
	list($pname, $price) = array_pad($args, 2, '');

	if( $pname == '' || $price == ''){
		return $qm->m['plg_qcart']['err_usage'];
	}
	
	$pname = h($pname);
	$price = h($price);
	
	return <<<EOD
<script type="text/javascript" language="javascript">button('{$pname}','{$price}')</script>
EOD;

}


?>
