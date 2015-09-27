<?php
// quick paypal shopping cart button
// 
// quick paypalのショッピングカートボタンを設置します
// 日本語商品名は、文字化けが起こることが多いです

function plugin_qp_button_inline()
{
	global $script;
	$qm = get_qm();
	
	$args = func_get_args();
	$num = func_num_args();
	
	if($num < 2)
	{
		return $qm->m['plg_qp_button']['err_usage'];
	}
	
	list($pname, $price, $url, $disp, $window, $amount) = array_pad($args, 5, '');
	$pname = h($pname);
	$price = h($price);
	
	if( !is_numeric($price) )
	{
		return $qm->m['plg_qp_button']['err_usage'];
	}
	
	$url = ($url=='') ? dirname($script).'/pp/cart.php' : $url;

	$ptrn = '/\.(jpg|jpeg|gif|png)$/i';
	if($disp==''){
		$button = '<input type="submit" value="'. $qm->m['plg_qp_button']['btn_cart_add']. '" name="quickpaypal" />';
	} else if(preg_match($ptrn, $disp)){
		$button = '<input type="image" src="'.$disp.'" border="0" name="submit" alt="'. $qm->m['plg_qp_button']['btn_cart_add']. '" align="top" />';
		$button .= '<input type="hidden" name="quickpaypal" value="'. $qm->m['plg_qp_button']['btn_cart_add']. '" />';
	}
	else{
		$button = '<input type="submit" value="'.h($disp).'" name="quickpaypal" />';
	}

	if($window==''){
		$target = 'target="new"';
	}else if($window=='nowindow') {
		$target = '';
	}
	else {
		$target = 'target="'.$window.'"';
	}
	
	if($amount){
		$amount = is_numeric($amount) ? $amount : 1;
		$button = '個数<input type="text" size="4" name="q_amount" value="'.$amount.'" />'
			.$button;
	}
	
	return <<<EOD
<form action="{$url}"
method="post" {$target} style="display:inline">
{$button}
<input type="hidden" name="q_product" value="{$pname}" />
<input type="hidden" name="q_price" value="{$price}" />
</form>
EOD;

}

function plugin_qp_button_convert()
{
	global $script;
	$qm = get_qm();
	
	$args = func_get_args();
	$num = func_num_args();
	
	if($num < 3)
	{
		return $qm->m['plg_qp_button']['err_usage'];
	}
	
	$text = array_pop($args);
	
	list($pname, $price, $url, $disp, $window, $amount, $align) = array_pad($args, 7, '');
	$pname = htmlspecialchars($pname);
	$price = htmlspecialchars($price);
	
	if( !is_numeric($price) )
	{
		return $qm->m['plg_qp_button']['err_usage'];
	}
	
	$url = ($url=='') ? dirname($script).'/pp/cart.php' : $url;
	$ptrn = '/\.(jpg|jpeg|gif|png)$/i';
	if($disp==''){
		$button = '<input type="submit" value="'. $qm->m['plg_qp_button']['btn_cart_add']. '" name="quickpaypal" />';
	} else if(preg_match($ptrn, $disp)){
		$button = '<input type="image" src="'.$disp.'" border="0" name="submit" alt="'. $qm->m['plg_qp_button']['btn_cart_add']. '" align="top" />';
		$button .= '<input type="hidden" name="quickpaypal" value="'. $qm->m['plg_qp_button']['btn_cart_add']. '" />';
	}
	else{
		$button = '<input type="submit" value="'.htmlspecialchars($disp).'" name="quickpaypal" />';
	}
	
	if($amount){
		$amount = is_numeric($amount) ? $amount : 1;
		$button = '個数<input type="text" size="4" name="q_amount" value="'.$amount.'" />'
			.$button;
	}


	if($window==''){
		$target = 'target="new"';
	}else if($window=='nowindow') {
		$target = '';
	}
	else {
		$target = 'target="'.$window.'"';
	}
	
	if($align==''){
		$align = 'left';
	}
	
	
	//analyze options
	$option_str = '';
	$cnt = 0;
	$matches = array();
	$ops = preg_split("/(\r\n|\n|\r)/", $text);
	
	for($i=0;$i<count($ops);$i++){
		
		if( $ops[$i]!='' ){
			$cnt ++;
			
			$el = explode(',',$ops[$i]);
			$option_str .= '<input type="hidden" name="option['.$cnt.'][name]" value="'
							.$el[0].'" />'."\n";
			$option_str .= ' '.$el[0].' <select name="option['.$cnt.'][value]">'."\n";
			for($ii=1; $ii<count($el); $ii++ )
			{
				$option_str .= '<option>'.$el[$ii].'</option>'."\n";
			}
			$option_str .= '</select>'."\n";
		}
	}
	
	return <<<EOD
<form action="{$url}" method="post" {$target} style="text-align:{$align}">
{$option_str}
$button
<input type="hidden" name="q_product" value="{$pname}" />
<input type="hidden" name="q_price" value="{$price}" />
</form>
EOD;

}
?>
