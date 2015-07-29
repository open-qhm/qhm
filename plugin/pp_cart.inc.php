<?php
// pp_cart plugin
// 2008 8/26
//
// this plugin view the paypal shopping cart button basic.
//

function plugin_pp_cart_inline()
{
	$qm = get_qm();
	
	$args = func_get_args();
	
	if(func_num_args()!= 2)
	{
		return $qm->m['plg_pp_cart']['err_usage'];
	}
	
	$pp_account = $args[0];
	
	return <<<EOD
<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post" style="display:inline">
<input type="hidden" name="cmd" value="_cart">
<input type="hidden" name="business" value="{$pp_account}">
<input type="image" src="https://www.paypal.com/ja_JP/i/btn/view_cart.gif" border="0" name="submit" alt="{$qm->m['plg_pp_button']['title_paypal']}">
<input type="hidden" name="display" value="1">
</form>
EOD;
	
}
?>
