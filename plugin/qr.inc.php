<?php
/**
 *   QR-Code Generator Plugin
 *   -------------------------------------------
 *   qr.inc.php
 *   
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2010-08-18
 *   modified :
 *   
 *   USAGE:
 *     inline plugin:
 *       &qr(string[, width[, height[,encoding]]);
 *     action plugin:
 *       display QR code
 *     display display QR code URL
 *       &qr(url);
 */

define('PLUGIN_QR_URL_FORMAT', 'http://chart.apis.google.com/chart?chs=%WIDTH%x%HEIGHT%&cht=qr&chl=%STRING%%ENCODING%');
define('PLUGIN_QR_DISP_FORMAT', '<img src="%CHURL%">');
define('PLUGIN_QR_DESP_URL_FORMAT', '<input type="text" value="%URL%" size="10" style="width:10em" onclick="this.focus();this.select();" readonly="readonly" />');
define('PLUGIN_QR_DEFAULT_SIZE', 150);

function plugin_qr_inline() {

	global $script, $vars;
	$page = $vars['page'];
	$args = func_get_args();
	//first param is string
	$str = array_shift($args);
	
	$w = $h = PLUGIN_QR_DEFAULT_SIZE;
	$enc = '';
	$encs = plugin_qr_get_encs();
	
	if ($str == 'url') {
		$url = $script. '?page='. rawurlencode($page). '&plugin=qr';
		
		return str_replace('%URL%', $url, PLUGIN_QR_DESP_URL_FORMAT);
	}
	
	$sizecnt = 0;
	foreach ($args as $arg) {
		if ($sizecnt === 0 && preg_match('/^(\d+)$/', trim($arg))) {
			$w = $h = trim($arg);
			$sizecnt++;
		}
		else if ($sizecnt > 0 && preg_match('/^(\d+)$/', trim($arg))) {
			$h = trim($arg);
		}
		else if (in_array($arg, $encs)) {
			$enc = '&choe='. $arg;
		}
	}

	$churl = plugin_qr_gen_qrcode($str, $w, $h, $enc);
	
	$chimg = str_replace('%CHURL%', $churl, PLUGIN_QR_DISP_FORMAT);
	
	return $chimg;

}

function plugin_qr_action() {

	global $script, $vars;
	$page = $vars['page'];
	$str = $script. '?'. rawurlencode($page);
	$w = isset($vars['w'])? $vars['w']: PLUGIN_QR_DEFAULT_SIZE;
	$h = isset($vars['h'])? $vars['h']: PLUGIN_QR_DEFAULT_SIZE;
	$enc = isset($vars['enc']) && in_array($vars['enc'], plugin_qr_get_encs())? $vars['enc']: '';
	
	$churl = plugin_qr_gen_qrcode($str, $w, $h, $enc);
	
	if ($fp = fopen($churl, 'rb')) {
		$qr = '';
		while (!feof($fp)) {
			$qr .= fread($fp, 8192);
		}
		fclose($fp);
	}
	
	$imgsize = strlen($qr);
	
	//画像を出力
	header('Content-Length: ' . $imgsize);
	header('Content-Type: image/png');
	echo $qr;
	exit;
}

function plugin_qr_gen_qrcode($str, $w=PLUGIN_QR_DEFAULT_SIZE, $h=PLUGIN_QR_DEFAULT_SIZE, $enc='') {

	$str = rawurlencode($str);
	$ptns = array('%STRING%', '%WIDTH%', '%HEIGHT%', '%ENCODING%');
	$rpls = array($str, $w, $h, $enc);
	$churl = str_replace($ptns, $rpls, PLUGIN_QR_URL_FORMAT);
	
	return $churl;


}

function plugin_qr_get_encs() {
	return array('Shift_JIS', 'ISO-8859-1');
}

?>