<?php
/**
*
*
*/
function plugin_ssl_convert()
{

	global $script, $script_ssl, $vars, $reg_exp_host;

	//------------ [重要かつ複雑なロジック] ----------------------------------
	// #sslと記述されたページのみ、ssl通信の対象としたいため以下のような処理をする
	// （ナビ、メニュー、ナビ2などは、通常のURLにリンクさせたい）
	//
	//   0. lib/init.php で、$script_ssl が未設定なら生成される
	//   2. 入れ替えた後は、$script_ssl によって、コンテンツ部分の様々なURLが作られる
	//   3. lib/html.php 内で、元に戻す
	//   4. naviや、menuや、pukiwiki.skin.phpで呼び出すところでは、元の$scriptが使われる
	//
	//   なるべく、ドメインを含めないURL指定を心掛けるとよいかも
	//
	
	// lib/html.php でSSL用の処理(HTMLコードの書き換えを実行)をするためのフラグ
	$qt = get_qt();
	$qt->setv('plugin_ssl_flag', TRUE);

	$go_ssl_url = $script_ssl.'?'.rawurlencode($vars['page']);
	
		
	// 移動を促すメッセージ
	$args = func_get_args();
	$msg = isset($args[0]) ? h($args[0]) : '暗号化されたページへ移動してください';
	
	// javascriptで判定して、https:に移動させる（PHPのSERVER変数が信用できないから）
	$qt->setv('jquery_include', true);
	
	$js_co = check_editable($vars['page'], false, false) ? '//' : '';

	$js = <<<EOD
<script type="text/javascript">
if( document.location.protocol != 'https:' ){
	{$js_co}location.href = '{$go_ssl_url}';
	$(function(){
		$('div#plugin_ssl_msg').html('<a href="{$go_ssl_url}" data-target="nowin">{$msg}</a>');
	});
}
</script>
EOD;


	$qt->appendv_once('plugin_ssl', 'beforescript', $js);
	
	
	// 外部ウインドウで開くリストから、通常ページへのURLを除外
	$p_url = parse_url( is_https() ? $script_ssl : $script );
	$reg_exp_host .= ($reg_exp_host=='' ? '' : '|').$p_url['host'];

	return <<<EOD
<div id="plugin_ssl_msg"></div>
EOD;

}
?>
