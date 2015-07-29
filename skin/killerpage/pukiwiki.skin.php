<?php

// Output HTTP headers
pkwk_common_headers();
header('Cache-control: no-cache');
header('Pragma: no-cache');
header('Content-Type: text/html; charset=' . CONTENT_CHARSET);

// Output HTML DTD, <html>, and receive content-type
if (isset($pkwk_dtd)) {
	$_qhm_values['meta_content_type'] = pkwk_output_dtd($pkwk_dtd);
} else {
	$_qhm_values['meta_content_type'] = pkwk_output_dtd();
}


include_template('skin/killerpage2/pukiwiki.skin.php',$_qhm_values, false);
exit(0);

?>
