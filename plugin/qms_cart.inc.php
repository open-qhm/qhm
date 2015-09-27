<?php
// quick paypal shopping cart button
// 
// quick paypalのショッピングカートボタンを設置します
// 日本語商品名は、文字化けが起こることが多いです

function plugin_qms_cart_inline()
{
	global $script;
	$qm = get_qm();
	
	$args = func_get_args();
	$num = func_num_args();

	if($num < 2) {
		return $qm->m['plg_qms_cart']['err_usage'];
	}

	$f_view = false;
	$url = '';
	$qms2_id = '';
	$option_str = '';
	$op_start  = '1';
	if (is_url($args[0])) {
		$f_view = true;
		$url = (substr($args[0],-1) == '/') ? $args[0] : ($args[0].'/');
	}
	else if (is_numeric($args[0])) {
		$pid = $args[0];
		$url = (substr($args[1],-1) == '/') ? $args[1] : ($args[1].'/');
		$url = ($url=='') ? dirname($script).'/pp/' : $url;
		$qms2_id = '<input type="hidden" name="qms2_id" value="'.$pid.'" />';
		$op_start  = '2';
	}
	else {
		$pname = h($args[0]);
		$url = (substr($args[1],-1) == '/') ? $args[1] : ($args[1].'/');
		$url = ($url=='') ? dirname($script).'/pp/' : $url;
		$op_start  = '2';
		
		// オプションの問い合わせ
		$option_str  = '<input type="hidden" name="qms2_name" value="'.$pname.'" />';
		$option_str .= file_get_contents($url.'cart_item_query.php?option='.urlencode($pname));
	}
	$url = ($url=='') ? dirname($script).'/pp/' : $url;

	for ($i=$op_start; $i < $num; $i++) {
		list($key, $val) = explode('=', $args[$i], 2);
		$options[$key] = $val;
    }

	// cart view
	if ($f_view) {
		$window = isset($options['win']) ? 'target="'.$options['win'].'"' : 'data-target="nowin"';  
		if (isset($options['button']) && ($options['button'] != '' 
										&&	!preg_match('/\.(jpg|jpeg|gif|png)$/i', $options['button']) )) {
			$button = isset($options['button']) ? h($options['button']) : $qm->m['plg_qms_cart']['btn_cart_view'];
		}
		else {
			if (isset($options['button'])) {
				$button = ($options['button'] != '') ? $options['button'] : $url.'img.php?m=cart&b=view';
				if (!is_url($button)) {
					$button = $url.'img/'.$button;
				}
			}
			else {
				$button = isset($options['skin']) ? $url.'img.php?m=cart&b=view&s='.$options['skin'] : $url.'img.php?m=cart&b=view';
			}
			$button = '<img src="'.$button.'" alt="'.$qm->m['plg_qms_cart']['btn_cart_view'].'" />';
		}
		return '<a href="'.$url.'cart.php" title="'.$qm->m['plg_qms_cart']['btn_cart_view'].'" '.$window.'>'.$button.'</a>';
	}
	// cart add
	else {
		// window
		$window = isset($options['win']) ? ' target="'.$options['win'].'"' : '';
		
		// button
		if (isset($options['button']) && ($options['button'] != '' 
										 &&	!preg_match('/\.(jpg|jpeg|gif|png)$/i', $options['button']) )) {
			$button = isset($options['button']) ? h($options['button']) : $qm->m['plg_qms_cart']['btn_cart_add'];
			$button = '<input type="submit" value="'. $button. '" name="quickpaypal" />';
		}
		else {
			if (isset($options['button'])) {
				$button = ($options['button']!= '') ? $options['button'] : $url.'img.php?m=cart&b=view';
				if (!is_url($button)) {
					$button = $url.'img/'.$button;
				}
			}
			else {
				$button = isset($options['skin']) ? $url.'img.php?m=cart&b=add&s='.$options['skin'] : $url.'img.php?m=cart&b=add';
			}
			$button = '<input type="image" src="'.$button.'" border="0" name="submit" alt="'.$qm->m['plg_qms_cart']['btn_cart_add'].'" style="vertical-align:middle;" />';
			$button .= '<input type="hidden" name="quickpaypal" value="'. $qm->m['plg_qms_cart']['btn_cart_add']. '" />';
		}
		
		// amount
		$amount = '';
		if (isset($options['amount'])) {
			$amount = (isset($options['amount']) && $options['amount'] != '') ? $options['amount'] : $qm->m['plg_qms_cart']['cart_amount_def'];
			$amount = $amount. '&nbsp;<input type="text" name="q_amount" size="4" value="1" />';
		}

		return <<<EOD
<form action="{$url}cart.php" method="post" {$window} style="display:inline" class="myshop-cart-form">
{$qms2_id}
{$option_str}
{$amount}{$button}
</form>
EOD;
	}
}

?>