<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: formzu.inc.php,v 0.5 2006/06/26 15:04:08 henoheno Exp $
//
// Formzu inline view plugin

// Allow CSS instead of <font> tag
// NOTE: <font> tag become invalid from XHTML 1.1
define('PLUGIN_IFRAME_ALLOW_CSS', TRUE); // TRUE, FALSE

// ----
define('PLUGIN_IFRAME_FIT_IFRAME_JS', '
<script type="text/javascript">
$(function(){
	$(\'iframe.autofit_iframe\').load(function(){
    if (this.contentWindow.document.documentElement)
		$(this).height(this.contentWindow.document.documentElement.scrollHeight+10);
    });
    $(\'iframe.autofit_iframe\').triggerHandler(\'load\');
});
</script>
'
);

function plugin_iframe_convert()
{
	global $pkwk_dtd;
	$qm = get_qm();

	$qt = get_qt();
	$qt->setv('jquery_include', true);
	
	$args = func_get_args();
	$args_cnt = count( $args );
	list($formurl, $height, $width, $align) = array_pad($args, 4, '');
	
	if (strlen(trim($formurl)) == 0) {
		return $qm->replace('fmt_err_cvt', 'iframe', $qm->m['plg_iframe']['err_usage']);
	}

	$fit = '';
	
	if ($args_cnt < 3) {  //correct args
		$height = '200';
		$width = '100%';
		
		$fit = ' class="autofit_iframe" ';
		
		$qt->appendv_once('plugin_iframe', 'beforescript', PLUGIN_IFRAME_FIT_IFRAME_JS);
	}
	if ($args_cnt < 4){
		$align = 'center';
	}
	

	if (PLUGIN_IFRAME_ALLOW_CSS === TRUE || ! isset($pkwk_dtd) || $pkwk_dtd == PKWK_DTD_XHTML_1_1) {
		return '<div style="text-align:' .$align. '"><iframe src="' . $formurl . '" frameborder="0" height="' . $height . '" width="' . $width . '" style="margin:0px;text-align:' . $align . ';" '.$fit.'><p>'. $qm->replace('plg_iframe.ntc', $formurl). '</p></iframe></div>';
	} else {
		return 'Invalid argument';
	}
}
?>
