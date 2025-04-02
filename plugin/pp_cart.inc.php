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
<input type="submit" class="btn btn-primary" value="{$qm->m['plg_pp_cart']['btn_title']}">
<input type="hidden" name="display" value="1">
</form>
EOD;
	
}
?>
