<?php
// $Id$

function plugin_checkagree_convert()
{
	static $s_chkagree_cnt = 0;

	//jquery ライブラリの読み込み
	$qt = get_qt();
	$qm = get_qm();
	$qt->setv('jquery_include', true);

    $body = '';
	
    $args = func_get_args();
    $body = array_pop( $args );

	if (count($args) > 2) {
		return $qm->replace('fmt_err_iln', 'size', $qm->m['plg_checkagree']['err_usage']);
	}

	list($label, $align) = array_pad($args, 2, '');
	if (array_key_exists($label, array('left'=>'','center'=>'','right'=>''))) {
		$align = $label;
		$label = $qm->m['plg_checkagree']['label'];


	}
	$label = ($label != '') ? $label : $qm->m['plg_checkagree']['label'];
	$align = ($align != '') ? $align : $qm->m['plg_checkagree']['align'];
	if (!array_key_exists($align, array('left'=>'','center'=>'','right'=>''))) {
		$align = $qm->m['plg_checkagree']['align'];
	}

    //Convert multi-line args to HTML
    if (isset($body)) {
        $body = str_replace("\r", "\n", str_replace("\r\n", "\n", $body));
        $lines = explode("\n", $body);
        $body = convert_html($lines);
        $body = '<div style="text-align:'.$align.';"><label><input type="checkbox" name="agreement" value="1" style="margin-right:3px;" />'.$label.'</label></div><div class="agree_box">'.$body.'</div>';
    } else {
        $body = '';
    }
	

    // はじめての定義の場合、javascriptを出力
	if ($s_chkagree_cnt == 0) {
		$head = '
<script type="text/javascript">
$(document).ready(function(){
	$("div.agree_box").css({display:"none"});
	$("input:checkbox[name=agreement]")
		.click(function(){
			if ($(this).is(":checked")) {
				$(this).parent().parent().next("div.agree_box").fadeIn();
			}
			else {
				$(this).parent().parent().next("div.agree_box").fadeOut();
			}
		});
});
</script>
<style type="text/css">
</style>
';
		$qt->appendv_once('plugin_checkagree', 'beforescript', $head);
	}

    return $body;
}
?>