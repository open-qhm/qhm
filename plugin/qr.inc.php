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
 *       &qr(string[, width[, height]);
 *     action plugin:
 *       display QR code
 *     display display QR code URL
 *       &qr(url);
 */

define('PLUGIN_QR_URL_FORMAT', '//chart.apis.google.com/chart?chs=%WIDTH%x%HEIGHT%&cht=qr&chl=%STRING%%ENCODING%');
define('PLUGIN_QR_DISP_FORMAT', '<img src="%CHURL%">');
define('PLUGIN_QR_DESP_URL_FORMAT', '<input type="text" value="%URL%" size="10" style="width:10em" onclick="this.focus();this.select();" readonly="readonly" />');
define('PLUGIN_QR_DEFAULT_SIZE', 150);

function plugin_qr_inline() {

	global $script, $vars, $qt;
	$page = $vars['page'];
	$args = func_get_args();
	//first param is string
	$str = array_shift($args);

	$w = $h = PLUGIN_QR_DEFAULT_SIZE;

	$sizecnt = 0;
	foreach ($args as $arg) {
		if ($sizecnt === 0 && preg_match('/^(\d+)$/', trim($arg))) {
			$w = $h = trim($arg);
			$sizecnt++;
		}
		else if ($sizecnt > 0 && preg_match('/^(\d+)$/', trim($arg))) {
			$h = trim($arg);
		}
	}

	$qt->appendv_once('plugin_qr_js', 'beforescript', '<script src="js/qrcode.min.js"></script>
<script type="text/javascript">
$(function() {
	$(".haik-plugin-qr-placeholder").each(function() {
		var qrcode = new QRCode(this, {
			text: $(this).data("url"),
			width: Number($(this).data("width")),
			height: Number($(this).data("height"))
		});
	});
});
</script>');

	return '<span data-url="'. h($str) .'" data-width="'. h($w) .'" data-height="'. h($h) .'" class="haik-plugin-qr-placeholder"></span>';
}
