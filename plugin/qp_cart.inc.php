<?php
// quick paypal shopping cart show
// 
// quick paypalのショッピングカートを表示します
// 日本語商品名は、文字化けが起こることが多いです

function plugin_qp_cart_inline()
{
	global $script;
	$qm = get_qm();
		
	$args = func_get_args();
	$num = func_num_args();
		
	$text = array_pop($args);
	$text = ($text == '') ? $qm->m['plg_qp_cart']['link_cart'] : $text;
	
	$url = array_pop($args);
	$url = ($url == '') ? dirname($script).'/pp/cart.php' : $url;

	
	return '<a href="'.$url.'" target="new">'.$text.'</a>';
}
?>
