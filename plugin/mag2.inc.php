<?php
/*
   mag2メルマガ登録フォーム生成プラグイン
*/

function plugin_mag2_convert() {
	$qm = get_qm();
	$args=func_get_args();
	
	$proto=isset($args[0]) ? $args[0] : '';
	if ($proto=="reg") {
		$url = "http://regist.mag2.com/reader/Magrdadd";
		$command = "MagRdAdd";
		$btn = $qm->m['plg_mag2']['btn_reg'];
	} else if ($proto=="del") {
		$url = "http://regist.mag2.com/reader/Magrddel";
		$command = "MagRdDel";
		$btn = $qm->m['plg_mag2']['btn_unsub'];
	} else {
		return $qm->m['plg_mag2']['err_usage_param'];
	}
	
	if(count($args)<2) return $qm->m['plg_mag2']['err_usage_pnum'];
	
	$id = preg_replace("/[^\d]/","",$args[1]);
	
	$target="_top";
	if(count($args)>2) $target=$args[2];
	if($target!="_top" && $target!="_blank") $target="_top";
	
	$message="";
	if(count($args)>3) $message=htmlspecialchars($args[3]);
	
	$body=<<<EOD
<form action="$url" method="post" target="$target">
<input type="hidden" name="MfcISAPICommand" value="$command"/>
$message<input type="text" name="rdemail" size="22" value=""/>
<input type="hidden" name="magid" value="$id"/>
<input type="submit" value="$btn"/>
</form>
EOD;
	return $body;
}



?>