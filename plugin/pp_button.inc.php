<?php
/**
 *   PayPal Shopping Cart Button Plugin
 *   -------------------------------------------
 *   pp_button.inc.php
 *   
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 
 *   modified : 2010-08-27
 *   
 *   PayPal のショッピングカートボタンを設置します
 *   日本語商品名は文字化けが起こることが多いです
 *
 *   USAGE:
 *     &pp_button($paypal_account, product name, price);
 */

function plugin_pp_button_inline()
{
	$qm = get_qm();
	$args = func_get_args();
	$num = func_num_args();
	
	if($num < 3)
	{
		return $qm->m['plg_pp_button']['err_usage'];
	}
	
	list($account, $pname, $price, $pcode) = array_pad($args, 4, '');
	$pname = h($pname);
	$price = h($price);
	$pcode = $pcode=='' ? $pname : h($pcode);
	
	return <<<EOD
<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post" style="display:inline">
<input type="image" src="https://www.paypal.com/ja_JP/JP/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="{$qm->m['plg_pp_button']['title_paypal']}">
<img alt="" border="0" src="https://www.paypal.com/ja_JP/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="add" value="1">
<input type="hidden" name="cmd" value="_cart">
<input type="hidden" name="business" value="{$account}">
<input type="hidden" name="item_name" value="{$pname}">
<input type="hidden" name="item_number" value="{$pcode}">
<input type="hidden" name="amount" value="{$price}">
<input type="hidden" name="no_shipping" value="0">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="JPY">
<input type="hidden" name="lc" value="JP">
<input type="hidden" name="bn" value="PP-ShopCartBF">
</form>	
EOD;

}
?>
