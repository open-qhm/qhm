<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// pkwkmail plugin ver. 1.0.0 - beta by jidaikobo.
// Modified by Katsumi Saito
// License: Same as PukiWiki.
// thx many ideas from Marijuana's InquirySP (XOOPS Module)
// thx many advice from symly, GIJOE (through his book).
//
// customized by hokuken 2007. 7/10
// -- 管理者宛のメール送信に、Return-Path:ヘッダーを追加 (gmailでspamにならないように)
//
defined('PKWKMAIL_FREEZE_CHECK')          or define('PKWKMAIL_FREEZE_CHECK',			'0');//turn 0 to skip freeze check(default 1) - ページ凍結のチェック
defined('PKWKMAIL_REPLY_MESSAGE_REQUIRE') or define('PKWKMAIL_REPLY_MESSAGE_REQUIRE',	1);	// no need to auto reply, turn 0
defined('PKWKMAIL_ADMIN_RETURN_ALLOWED')  or define('PKWKMAIL_ADMIN_RETURN_ALLOWED',	1);	// no need to add 'From', turn 0
defined('PKWKMAIL_DOMAIN_CHECK')          or define('PKWKMAIL_DOMAIN_CHECK',			1);	// no need to check, turn 0
defined('PKWKMAIL_TABLE_CLASS')           or define('PKWKMAIL_TABLE_CLASS',			'style_table');

// add by hokuken
if( file_exists('lib/qdmail.php') ){
	require_once('lib/qdmail.php');
}
if( file_exists('lib/qdsmtp.php') ){
	require_once('lib/qdsmtp.php');
}


// Not Pukiwiki Plus! I18N
if(! function_exists('_')) {
	function _($x) { return $x; }
}

function plugin_pkwkmail_en_init()
{
	$msg = array(
	'_pkwkmail_msg' => array(
		'contact_title_to_admin'	=> _("There was an inquiry"),
		'contact_title_to_client'	=> _("Automatic reply: The inquiry was gotten"),
		'reply_message'			=> _("Thank you very much for your order.　This is our automatic reply mail.\n　Your order was:"),
		'reply_message_foot'		=> _("Our staff will contact you shortly.\n Monthly fee will be calculated by the actual numbers which were keyed in into system."),
		'default_message'			=> _("Please set default_message"),
		'default_explanation'		=> _("Please fill in a necessary matter on the form about * form \n or less. \n' and 'Input indispensability item''Please prevent me from the omission's there"),
		'confirm_message'			=> _("'''All necessary items are input. '''Please edit the continuing form as follows when you want to correct the content again. "),
		'confirm_message_reply'		=> _("Please remove the following checks and push [Transmit] when mail for the confirmation of the content of the transmission is unnecessary. "),
		'confirm_message_yet'		=> _("Please transmit again after inputting all the following items. "),
		'confirm_message_title'		=> _("order"),
		'confirm_message_fromtitle' => _("Form for * edit and the contents confirmation"),
		'confirm_message_yet_title' => _("Please confirm the input indispensability item"),
		'finish_message_title'		=> _("Mail was transmitted"),
		'attr_err_title'			=> _("Set error"),
		'attr_err_mes'			=> _("The form was not correctly generable. "),
		'attr_err_ary'			=> _("Please confirm the form of the array: %s"),
		'must'					=> _("(must)"),
		'button'					=> _("Confirmation"),
		'button_send'				=> _("Transmit"),
		'invalid_madrs'			=> _("The form of [%s] is illegal"),
		'invalid_mdmain'			=> _("Please confirm the domain of [%s]"),
		'invalid_nosel'			=> _(" [%s] is a unselection "),
		'invalid_noval'			=> _(" [%s] is a uninput "),
		'sendme'					=> _("Transmission..content..copy..mail..send<br /><span style=\"font-size:11px\">※Please remove the check when it is unnecessary. It is not cared not to understand well as it is. </span>"),
		'send_err_admin'			=> _("Because some errors had occurred, mail was not able to be transmitted. "),
		'send_err'				=> _("Because some errors had occurred, the automatic reply was not able to be done. "),
		'finish_message'			=> _("Mail was transmitted as follows.~\n There is a possibility that mail has not reached by the reason like the system trouble etc. when there is no proper reaction. Sorry to trouble you, but please inquire by another means. "),
		'finish_message_return'		=> _("return to top page. "),
		'freeze_notification'		=> _("The page has not been frozen. It is dangerous, and freeze the page, please. "),
		'not_use'					=> _("Please freeze the page to use this plug-in. "),
		'err_msg_notify_to'		=> _("Please set mail address (\$notify_to) to the manager correctly. "),
		'fmt_date'				=> _("Y/m/d"),
		'fmt_time'				=> _(""),
	  )
	);
	set_plugin_messages($msg);
}

function plugin_pkwkmail_en_convert()
{

	//--- キャッシュを無効に ---
	$qt = get_qt();
	$qt->enable_cache = false;

	global $vars,$_pkwkmail_msg, $script;
	$page = isset($vars['page']) ? $vars['page'] : '';

	//freeze check - 凍結していない場合はエラー
	if( PKWKMAIL_FREEZE_CHECK == '1' ){
		if( ! is_freeze( $vars['page'] ) ){
			return '<p>'.$_pkwkmail_msg['not_use'].'</p>';
		}
	}

	//preparing values - return code > return after quotation - 変数の準備と改行コード一時退避
	$lines = array();
	if( isset($vars['refer']) ){
		$r_page = htmlspecialchars($vars['refer']);
		$page = $r_page;
		$lines = PKWKMAIL_read_postdata_old($r_page);
	}
	else{		
		$args = func_get_args();
		$args = str_replace( array( "\r","\r\n" ),"\n",$args );
		$lines = preg_replace( "/\s*=\s*'\n*/", "='", $args[0] );
		$lines = str_replace( "'\n", "'PKWKMAIL_EXPLODE", $lines );
		$lines = str_replace( "\n", "PKWKMAIL_LATER_RETRUN", $lines );
		$lines = explode( "PKWKMAIL_EXPLODE", $lines );
	}

	//decide form action destination
	$a_page = $script . '?' . rawurlencode($page);

	//phase check - 段階のチェック
	$cnfm = 0;
	if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
		if( isset( $vars['cnfm_chk'] ) && $vars['cnfm_chk'] == 1) $cnfm = 1;
		if( isset( $vars['cnfm_snd'] ) && $vars['cnfm_snd'] == 1) $cnfm = 2;
	}
	
	//switch - 段階に応じた対応分岐
	switch( $cnfm ){
		case 1:
			$attr = PKWKMAIL_prepare( $lines );
			$body = PKWKMAIL_confirm( $attr, $a_page  );
	//		$body.= PKWKMAIL_formmaker( $attr,$cnfm );
	//		return $body;
	
			$title = $attr['confirm_message_title'];
			force_output_message($title, $page, "<h2>$title</h2>".$body);

			break;
		case 2:
			$attr = PKWKMAIL_prepare( $lines );
			$body = PKWKMAIL_sent( $attr );
	//		return $body;
	
			$title = $attr['finish_message_title'];
			force_output_message($title, $page, "<h2>$title</h2>".$body);

			break;
		default:
			$attr = PKWKMAIL_prepare( $lines );
			$body = PKWKMAIL_entry( $attr );
			$body.= PKWKMAIL_formmaker( $attr,$cnfm, $a_page );
			return $body;
	}
}

function PKWKMAIL_read_postdata_old( $refer ){

	$postdata_old = get_source( $refer );
	//var_dump($postdata_old);
	
	$go_read = FALSE;
	$rawdata = '';
	foreach($postdata_old as $line) {
		if( preg_match('/^\}\}/',$line) ){
			$go_read = FALSE;
		}
		
		if($go_read){
			$rawdate .= $line;
		}
	
		if( preg_match('/^#pkwkmail{{/',$line) ){
			$go_read = TRUE;
		}	
	}
	
	$rawdate = str_replace( array( "\r","\r\n" ),"\n",$rawdate );
	$lines = preg_replace( "/\s*=\s*'\n*/", "='", $rawdate );
	$lines = str_replace( "'\n", "'PKWKMAIL_EXPLODE", $lines );
	$lines = str_replace( "\n", "PKWKMAIL_LATER_RETRUN", $lines );
	$lines = explode( "PKWKMAIL_EXPLODE", $lines );

	return $lines;
}

function PKWKMAIL_prepare( $lines )
{
	global $notify_to, $_pkwkmail_msg, $defaultpage;

	foreach($lines as $k => $v){
		//correct format - リクエストの記法のパラつきを整頓
		$v = preg_replace( "/'\s*,\s*'/","','",$v );
		$v = preg_replace("/^(PKWKMAIL_LATER_RETRUN)+/",'',$v );
		$v = str_replace("PKWKMAIL_LATER_RETRUN","\n",$v );

		//first explode - 統合と引用府をきっかけにして配列に
		$v = explode( "=", $v );
		$tmp = isset( $v[1] ) ? array_map( 'PKWKMAIL_trim',explode( "','", $v[1] ) ) : NULL;

		//replacement - 項目置換
		$tmp = str_replace( '%DATE%', PKWKMAIL_format_date(UTIME), $tmp );

		for( $i=3;$i<=5;$i++ ) {
			// i=3 - explode arrays - チェックボックスなど多次元配列の準備
			// i=4 - default values of checkbox - チェックボックスの初期値確認
			// i=5 - get size of boxes - input と textarea のサイズ
			if( isset( $tmp[$i] ) && preg_match("/\(.*?\)/",$tmp[$i]) ) {
				$tmp[$i] = ltrim( $tmp[$i],'(' );
				$tmp[$i] = rtrim( $tmp[$i],')' );
				$tmp[$i] = preg_replace( "/\s*,\s*/",',',$tmp[$i] );
				$tmp[$i] = explode(",", $tmp[$i]);
			}
		}

		//create values - 記法によって変数を作成
		switch( $v[0] ){
		case 'attr':
			$attr['attr'][] = $tmp;
			break;
		//htmlspecialchars をかける値
		case 'contact_title_to_admin'://mail subject - メールの件名
		case 'contact_title_to_client':
			$attr[$v[0]] = htmlspecialchars( $tmp[0],ENT_QUOTES );
			break;
		case 'admin_adrs':				// 管理者メールアドレス
		case 'admin_reply_to':			// 管理者返信用アドレス
		case 'reply_message_require':	//reply message for mail - 自動返信メールに関する変数
		case 'admin_return_allowed':
		case 'reply_message':
		case 'reply_message_foot':
		case 'domain_check':			// メールアドレスのドメインの存在チェック
		case 'sendme':				// 通常は、本人に写し、管理者に本文だが、それを逆にする場合のメッセージの設定
		case 'client_signature':		// 宛先に本文、管理者に写しの場合、本文に署名を設定できるようにする
			$attr[$v[0]] = $tmp[0];
			break;
		// 画面に表示されるようなパラメータ
		case 'default_message':			// 初期メッセージ
		case 'default_explanation':
		case 'confirm_message':			// 確認のメッセージ
		case 'confirm_message_yet':
		case 'confirm_message_yet_title':
		case 'confirm_message_title':
		case 'confirm_message_reply':
		case 'confirm_message_fromtitle':
		case 'finish_message':			// appears at the end - 最終段階のメッセージ
		case 'finish_message_title':
		case 'finish_message_return':
			$attr[$v[0]] = convert_html($tmp[0]);
			break;
		}
	}

	// 変数が設定されなかった値については、デフォルト値を設定する
	// 0:$_pkwkmail_msg, 1:convert_html, 2:val
	$format_type = array(
		'contact_title_to_admin'	=> array(0),
		'contact_title_to_client'	=> array(0),
		'admin_adrs'				=> array(2,$notify_to),
		'reply_message_require'		=> array(2,PKWKMAIL_REPLY_MESSAGE_REQUIRE),
		'admin_return_allowed'		=> array(2,PKWKMAIL_ADMIN_RETURN_ALLOWED),
		'reply_message'			=> array(0),
		'reply_message_foot'		=> array(0),
		'domain_check'			=> array(2,PKWKMAIL_DOMAIN_CHECK),
		'default_message'			=> array(0),
		'default_explanation'		=> array(1),
		'confirm_message'			=> array(1),
		'confirm_message_title'		=> array(0),
		'confirm_message_reply'		=> array(1),
		'confirm_message_fromtitle'	=> array(1),
		'confirm_message_yet'		=> array(1),
		'confirm_message_yet_title'	=> array(0),
		'finish_message'			=> array(1),
		'finish_message_title'		=> array(0),
		'finish_message_return'		=> array(3,$defaultpage),
		'sendme'					=> array(0),
	);

	//edit pattern - 編集のパターン
	foreach($format_type as $key => $val) {
		if( ! empty($attr[$key])) continue;//指定値がある場合はそれを使う
		switch($val[0]) {
		case 0:
			$attr[$key] = $_pkwkmail_msg[$key];
			break;
		case 1:
			$attr[$key] = convert_html($_pkwkmail_msg[$key]);
			break;
		case 2:
			$attr[$key] = $val[1];
			break;
		case 3:
			$attr[$key] = convert_html('[['.$_pkwkmail_msg[$key].'>'.$val[1].']]');
			break;
		}
	}

	// 変数妥当性チェック
	if( $attr['admin_adrs'] == 'to@example.com' || $attr['admin_adrs'] == '' ) {
		die_message($_pkwkmail_msg['err_msg_notify_to']);
	}

	foreach(array('admin_adrs','admin_reply_to') as $x) {
		if(empty($attr[$x])) continue;
		$rc = PKWKMAIL_MailAddrCheck($attr[$x],$attr['domain_check']);
		if($rc['err']) die_message(sprintf($_pkwkmail_msg[$rc['msg']],$attr[$x]).' ('.$x.')');
	}

	return $attr;
}

function PKWKMAIL_formmaker($attr,$cnfm, $a_page)
{
	global $vars,$_pkwkmail_msg, $script;

	$render_value = $render_value_arr = array();
	//open $vars - $vars を展開
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		//correct amount num of value - POST された数とあるべき数をあわせる
		foreach($attr['attr'] as $k => $v){
			$processed_post[$k] = isset( $vars['PKWKMAIL_'.$k] ) ? $vars['PKWKMAIL_'.$k] : '';
		}

		foreach($processed_post as $k => $v){
			//set value and sanitize - 値のセットと無害化。チェックボックスだけ特別扱いの変数
			if( is_array( $v ) ) {
				foreach( $v as $kk => $vv ) {
					if(get_magic_quotes_gpc()) { $vv = stripslashes($vv); }
					$render_value_arr[] = htmlspecialchars( $vv, ENT_QUOTES );
				}
			}else{
				if( get_magic_quotes_gpc() ) $v = htmlspecialchars( stripslashes($v),ENT_QUOTES );
			}
			$render_value[] = $v ;
		}
	}

	//rendering error check - 記法のエラーチェック
	$attr_err_flag = false;
	$attr_error = '';

	foreach($attr['attr'] as $k => $v) {
		if(is_array($attr['attr'][$k][3])) continue;
		switch ($attr['attr'][$k][1]) {
		case 'option':
		case 'radio':
		case 'checkbox':
			$attr_err_flag = true;
			$attr_error .= "\t".'<li><strong>'.sprintf($_pkwkmail_msg['attr_err_ary'],$attr['attr'][$k][0]).'</strong></li>'."\n";
			break;
		}
	}

	$n = 0;
	$attr_name = array_keys($attr['attr']);
	$render_title = $render_element = array() ;

	foreach($attr['attr'] as $k){
		//must check - 必須項目チェック
		$render_must = '';
		if($k[2] == 1) $render_must = '<strong>'.$_pkwkmail_msg['must'].'</strong>';

		//title - 各質問項目 - th
		$k[0] = htmlspecialchars($k[0], ENT_QUOTES);
		$render_title[] .= "\t".'<th class="style_th"><label for="PKWKMAIL_'.$attr_name[$n].'">'.str_replace('&amp;br;','<br />',$k[0]).$render_must.'</label></th>'."\n";

		//parts
		$render_element_arr = $value_arr = array() ;

		//default value - 最初の画面であればユーザ設定の初期値を用いる
		$k[4] = isset( $k[4] ) ? $k[4] : NULL ;
		if( $cnfm == 0 ) $render_value[] = $render_value_arr = $k[4] ;

		//single line checkbox and radio button - $breakに改行を代入
		$radio_break    = ( $k[1] == 'radio-br' )    ? '<br />' : '';
		$checkbox_break = ( $k[1] == 'checkbox-br' ) ? '<br />' : '';

		//default size - テキストボックスの初期サイズ
		$k[5] = isset( $k[5] ) && ! empty( $k[5] ) ? $k[5] : array(35,10) ;

		switch ($k[1]) {
		case 'text':
		case 'email':
		case 'password': //text,email,password
			$render_element[] .= "\t".'<td class="style_td"><input id="PKWKMAIL_'.$attr_name[$n].'" name="PKWKMAIL_'.$attr_name[$n].'" type="text" value="'.$render_value[$n].'" size="'.$k[5][0].'" /></td>'."\n";
			break;
		case 'textarea': //textarea
			$render_element[] .= "\t".'<td class="style_td"><textarea id="PKWKMAIL_'.$attr_name[$n].'" name="PKWKMAIL_'.$attr_name[$n].'" cols="'.$k[5][0].'" rows="'.$k[5][1].'">'.$render_value[$n].'</textarea></td>'."\n";
			break;
		case 'option': //option
			$render_element[$n] = array() ;
			$render_element_arr[] .= "\t".'<td class="style_td"><select id="PKWKMAIL_'.$attr_name[$n].'" name="PKWKMAIL_'.$attr_name[$n].'">'."\n";
			foreach($k[3] as $value_arr){
				$value_arr = htmlspecialchars($value_arr, ENT_QUOTES);
				$selected = ($value_arr == $render_value[$n]) ? ' selected="selected"' : '';
				$render_element_arr[] .= "\t\t".'<option value="'.$value_arr.'"'.$selected.'>'.$value_arr."</option>\n";
			}
			$render_element_arr[] .= "\t".'</select></td>'."\n";
			$render_element_arr_num = count($render_element_arr);
			for($i=0;$i<$render_element_arr_num;++$i){
				$render_element[$n][] .= $render_element_arr[$i];
			}
			break;
		case 'radio':
		case 'radio-br':
			//radio
			$render_element[$n] = array() ;
			$render_element_arr[] .= "\t".'<td class="style_td">'."\n";
			foreach($k[3] as $value_arr){
				$value_arr = htmlspecialchars($value_arr, ENT_QUOTES);
				$checked = ($value_arr == $render_value[$n]) ? ' checked="checked"' : '';
				$render_element_arr[] .= "\t\t".'<label><input type="radio" name="PKWKMAIL_'.$attr_name[$n].
						'" value="'.$value_arr.'"'.$checked.'/>'.$value_arr.'</label>'.$radio_break."\n";
			}
			$render_element_arr[] .= "\t".'</td>'."\n";
			$render_element_arr_num = count($render_element_arr);
			for($i=0;$i<$render_element_arr_num;++$i){
				$render_element[$n][] .= $render_element_arr[$i];
			}
			break;
		case 'checkbox':
		case 'checkbox-br':
			//checkbox
			$render_element[$n] = array() ;
			$render_element_arr[] .= "\t".'<td class="style_td">'."\n";
			foreach($k[3] as $value_arr) {
				$value_arr = htmlspecialchars($value_arr, ENT_QUOTES);
				$chked = false;
				if( ! is_null($render_value_arr) ) {
					foreach($render_value_arr as $render_value_arr_v) {
						if($value_arr == $render_value_arr_v) $chked = true;
					}
				}
				$checked = ($chked) ? ' checked="checked"' : '';
				$render_element_arr[] .= "\t\t".'<label><input type="checkbox" name="PKWKMAIL_'.$attr_name[$n].
					'[]" value="'.$value_arr.'"'.$checked.'/>'.$value_arr.'</label>'.$checkbox_break."\n";
			}
			$render_element_arr[] .= "\t".'</td>'."\n";
			$render_element_arr_num = count($render_element_arr);
			for($i=0;$i<$render_element_arr_num;++$i) {
				$render_element[$n][] .= $render_element_arr[$i];
			}
			break;
		}
		$n++ ;
	}

	//rendering sectin - ここから描画
	$result_form = '';

	//rendering error check - エラー描画
	if($attr_err_flag==1){
		$result_form .= '<h2>'.$_pkwkmail_msg['attr_err_title'].'</h2>'."\n";
		$result_form .= '<p>'.$_pkwkmail_msg['attr_err_mes'].'</p>'."\n";
		$result_form .= '<ul>'."\n";
		$result_form .= $attr_error;
		$result_form .= '</ul>'."\n";
	}

	$result_form .= '<div class="ie5"><form action="'.$a_page.'" method="POST">'."\n";
	$result_form .= '<table class="'.PKWKMAIL_TABLE_CLASS.'">'."\n";
	//count attr - 作表のため項目数を数えて繰り返す
	$num_attr = count($render_title);

	for($i=0;$i<$num_attr;++$i) {
		$result_form .= '<tr>'."\n";
		$result_form .= $render_title[$i];
		if(is_array($render_element[$i])) {
			$v = 0;
			foreach($render_element[$i] as $key) {
				$result_form .= $render_element[$i][$v];
				$v = ++$v;
			}
		} else {
			$result_form .= $render_element[$i];
		}
		$result_form .= '</tr>'."\n";
	}
	$result_form .= '</table>'."\n";
	$result_form .= '<p><input name="cnfm_chk" type="hidden" value="1" />'."\n";
	$result_form .= '<input type="submit" value="'.$_pkwkmail_msg['button'].'" name="submit" /></p>'."\n";
	$result_form .= '</form></div>'."\n";

	return $result_form;
}

function PKWKMAIL_entry($attr)
{
	global $vars,$_pkwkmail_msg;

	$entry = '';
	//freeze notification - 凍結確認
	//if(!is_freeze($vars['page'])){
	//	$entry = '<p style="border:1px #aaa solid;padding:10px;background-color:#eee;"><strong>' .$_pkwkmail_msg['freeze_notification'] . '</strong></p>';
	//}
	$entry .= $attr['default_message']."\n";
	$entry .= $attr['default_explanation']."\n";

	return $entry;
}

function PKWKMAIL_confirm( $attr, $a_page )
{
	global $vars,$_pkwkmail_msg, $script;

	unset($vars['submit']);
	unset($vars['cnfm_chk']);

	// generate $digest - ダイジェストの生成
	$digest = md5(join('', get_source($vars['page'])));
	$s_digest = htmlspecialchars($digest,ENT_QUOTES);

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		//correct amount num of values - 送信された総数の確認
		foreach($attr['attr'] as $k => $v){
			$p_key = ($attr['attr'][$k][1] == 'email') ? 'PKWKMAIL_'.$k.'_email':'PKWKMAIL_'.$k;
			$processed_post[$p_key] = isset( $vars['PKWKMAIL_'.$k] ) ? $vars['PKWKMAIL_'.$k] : '';
			//preparing send data - 送信用データ準備
			$send_value_title[$p_key]=$attr['attr'][$k][0];
		}

		$attr_key_n = $render_err_flag = 0;
		$render_err = $send_mail_data = '' ;
		foreach($processed_post as $key => $value){
			if(is_array($value)){
				//set value and sanitize: checkbox - チェックボックスの場合
				foreach($value as $v_key => $v_arr){
					if(get_magic_quotes_gpc())$v_arr = stripslashes($v_arr);
					$v_arr = htmlspecialchars($v_arr, ENT_QUOTES);
					$value_arr[] = $v_arr;
					//preparing send data - 送信用データ準備
					$send_value[$key][] = $v_arr;
				}
			}else{
				//set value and sanitize: non checkbox - 非チェックボックス
				if(get_magic_quotes_gpc())$value = stripslashes($value);
				$value = htmlspecialchars($value, ENT_QUOTES);
				//preparing send data - 送信用データ準備
				if( strpos($key,'_email') ) $send_email_value = $value ;
				$send_value[$key] = $value;
			}

			//remove &br; from error message - エラーから改行記法を除去
			$err_title = str_replace('&br;','',$attr['attr'][$attr_key_n][0]);
			//validation:mail adrs - メールアドレスの妥当性確認
			if(strpos($key,'_email') && $attr['attr'][$attr_key_n][2] == 1) {
				$rc = PKWKMAIL_MailAddrCheck($value,$attr['domain_check']);
				if($rc['err']) {
					$render_err_flag = 1;
					$render_err .= "\t".'<li><strong>'.sprintf($_pkwkmail_msg[$rc['msg']],$err_title).
						'</strong></li>'."\n";
				}
			}

			//validation:must - 必須項目の確認
			if($attr['attr'][$attr_key_n][2] == 1) {
				if(is_array($attr['attr'][$attr_key_n][3]) && !$value | $value == '') {
					$render_err .= "\t".'<li><strong>'.sprintf($_pkwkmail_msg['invalid_nosel'],$err_title).'</strong></li>'."\n";
					$render_err_flag = 1;
				} elseif(!$value) {
					$render_err .= "\t".'<li><strong>'.sprintf($_pkwkmail_msg['invalid_noval'],$err_title).'</strong></li>'."\n";
					$render_err_flag = 1;
				}
			}
		++$attr_key_n;
		}
	}

	if( $render_err_flag == 0 ){
		//formatting sending data TITLE: VALUES - 送信されるデータの生成
		$confirm_msg .= '<table class="style_table">'."\n";
		foreach($send_value as $k => $v) {
			$send_value_title[$k]=str_replace('&br;','',$send_value_title[$k]);
			if(is_array($send_value[$k])) {
				foreach($send_value[$k] as $k_arr => $v_arr) {
					$send_mail_data .= $send_value_title[$k]."\t".$v_arr.'PKWKMAIL_LATER_RETRUN';
					$confirm_msg .= '<tr><th class="style_th">'.$send_value_title[$k].'</th><td class="style_td">' . $v_arr . "</td></tr>\n";
				}
			}else{
				$send_mail_data .= $send_value_title[$k]."\t".$send_value[$k].'PKWKMAIL_LATER_RETRUN';
				$confirm_msg .= '<tr><th class="style_th">'.$send_value_title[$k].'</th><td class="style_td">'. $send_value[$k] . "</td></tr>\n";
			}
		}
		$confirm_msg .= '</table>';

		$send_email_value = isset( $send_email_value ) ? $send_email_value : NULL  ;

		$result_cnfm = $attr['confirm_message'];
		$result_cnfm = $confirm_msg;
		$result_cnfm .=  '<form action="'.$a_page.'" method="POST">'."\n";
		if( $attr['reply_message_require'] == 1 && ! empty( $send_email_value ) ) {
//			$result_cnfm .= $attr['confirm_message_reply'];
//			$result_cnfm .= '<input type="hidden" name="sendmeacopy" value="1" />'."\n";
			$result_cnfm .= '<p><label><input type="checkbox" name="sendmeacopy" checked="checked" value="1" />'.$attr['sendme'].'</label></p>'."\n";
		}
		$result_cnfm .= '<p><input type="hidden" name="mail_data" value="'.$send_mail_data.'" />'."\n";
		$result_cnfm .= '<input name="digest" type="hidden" value="'.$s_digest.'" />'."\n";
		$result_cnfm .= '<input type="hidden" name="mail_adrs" value="'.$send_email_value.'" />'."\n";
		$result_cnfm .= '<input name="cnfm_snd" type="hidden" value="1" />'."\n";
		$result_cnfm .= '<input type="button" name="back" id="back" onclick="history.back();" value="correction" /> ';
		$result_cnfm .= '<input type="submit" value="'.$_pkwkmail_msg['button_send'].'" name="submit" style="font-weight:bold" /></p>'."\n";
		$result_cnfm .= '</form>'."\n";
		//$result_cnfm .= $attr['confirm_message_fromtitle'];

		$vars['page'] = $attr['confirm_message_title'].' - '.htmlspecialchars( $vars['page'],ENT_QUOTES );//遷移画面のh1

		return $result_cnfm;
	}else{
		$result_cnfm = $attr['confirm_message_yet'];
		$result_cnfm .= '<ul>'."\n";
		$result_cnfm .= $render_err;
		$result_cnfm .= '</ul>'."\n";
		$vars['page'] = $attr['confirm_message_yet_title'].' - '.htmlspecialchars( $vars['page'],ENT_QUOTES );//遷移画面のh1
		$result_cnfm .= '<input type="button" name="back" id="back" onclick="history.back();" value="back" /> ';

		return $result_cnfm;
	}
}

function PKWKMAIL_sent($attr)
{
	global $vars,$_pkwkmail_msg,$google_apps, $google_apps_domain, $passwd;

	// digest check - 新規生成するダイジェストとPOST値比較
	$digest = md5(join('', get_source($vars['page'])));
	$s_digest = htmlspecialchars($digest,ENT_QUOTES);
	if($vars['digest'] != $s_digest) die_message('Invalid digest.');

	$mail_content = array();

	//formatting values: do not open $vars by foreach - POST値等から変数生成
	$mail_content['sendmeacopy']    = isset( $vars['sendmeacopy'] ) ? htmlspecialchars($vars['sendmeacopy'],ENT_QUOTES) : NULL ;
	$mail_content['mail_data']      = htmlspecialchars($vars['mail_data'],ENT_QUOTES);
	$mail_content['mail_adrs']      = htmlspecialchars($vars['mail_adrs'],ENT_QUOTES);
	$mail_content['admin_adrs']     = $attr['admin_adrs'];
	$mail_content['admin_reply_to'] = ! empty( $attr['admin_reply_to'] ) ? $attr['admin_reply_to'] : $mail_content['mail_adrs'];

	foreach($mail_content as $key => $value) {
		if(get_magic_quotes_gpc()) $mail_content[$key] = stripslashes($value);
	}

	//preparing rendering data - 画面作成用データ準備
	$mail_content['render_scrn'] = explode('PKWKMAIL_LATER_RETRUN',$mail_content['mail_data']);
	$a = array_pop($mail_content['render_scrn']);

	$scrn_content = '' ;
	foreach($mail_content['render_scrn'] as $key => $value) {
		$value = str_replace("\t",'</th><td class="style_td">', $value);
		$value = '<tr><th class="style_th">'.$value.'</td></tr>'."\n";
		$scrn_content .= $value;
	}

	//preparing sending data - 送信データ準備
	$mail_content['mail_data'] = str_replace("\t",': ',$mail_content['mail_data']);
	$mail_content['mail_data'] = str_replace('PKWKMAIL_LATER_RETRUN',"\n",$mail_content['mail_data']);

	//formatting madrs: return address - Choose one from plural Address
	// メール投稿者宛に送信する際に、管理者アドレスが複数登録されていた場合 From: ヘッダーに複数人登場するため、管理者アドレスを先頭１名にする
	if(!strpos($mail_content['admin_adrs'],',') === false) {
		$admin_adrs_return = explode(',', $mail_content['admin_adrs']);
		$admin_adrs_return = $admin_adrs_return[0];
	} else {
		$admin_adrs_return = $mail_content['admin_adrs'];
	}

	//formatting madrs: client address check
	//クライアント側のメールアドレス確認。第２段階のチェックを通り抜けて、かつここのチェックを通らないとしたら、送信画面への直投稿なので die する
	$rc = PKWKMAIL_MailAddrCheck($mail_content['mail_adrs'],$attr['domain_check']);
	if($rc['err']) die_message(sprintf($_pkwkmail_msg[$rc['msg']],$mail_content['mail_adrs']));

	$mail_to_admin = array();

	//admin side - 管理者への送信内容
	$mail_to_admin = $mail_content['mail_data']."\n\n";
	$mail_to_admin = PKWKMAIL_mailformat($mail_to_admin);
	$mail_to_admin .= '-- '."\n";
	if($mail_content['sendmeacopy'] == 1) {
		$mail_to_admin .= 'Copy has been sent.'."\n";
	} else {
		$mail_to_admin .= 'Copy has not been sent.'."\n";
	}
	$mail_to_admin .= 'Date: '.date('Y-m-d (D) H:i:s', UTIME)."\n";
	$mail_to_admin .= 'Host: '.getHostByAddr(getenv('REMOTE_ADDR'))."\n";
	$mail_to_admin .= isset( $_SERVER['HTTP_USER_AGENT'] ) ? 'UA: '.$_SERVER['HTTP_USER_AGENT']."\n" : NULL ;
	$mail_to_admin .= 'Powered by PKWKMAIL.'."\n";

	$mail_to_admin_header  = 'From:'.$mail_content['mail_adrs']."\n";
	$mail_to_admin_header .= 'Reply-To:'.$mail_content['admin_reply_to']."\n";
	$mail_to_admin_header .= 'Return-Path:'.$mail_content['mail_adrs']."\n";
	$mail_to_admin_header .= 'Content-Type: text/plain;charset=iso-2022-jp'."\n";
	$mail_to_admin_header .= 'X-Mailer: PKWKMAIL / PHP ver.'.phpversion();

//	$mail_to_admin_title = mb_convert_encoding($attr['contact_title_to_admin'], 'ISO-2022-JP', 'auto');
	$mail_to_admin_title = $attr['contact_title_to_admin'];
	$mail_to_admin_title = base64_encode($mail_to_admin_title);
	$mail_to_admin_title = "=?iso-2022-jp?B?".$mail_to_admin_title."?=";

	//client side - クライアント側への送信内容
	$mail_to_client = $attr['reply_message']."\n\n";
	$mail_to_client .= $mail_content['mail_data']."\n\n";
	$mail_to_client .= $attr['reply_message_foot']."\n\n";
	$mail_to_client .= '-- '."\n";
	// 署名が設定されている場合
	if(!empty($attr['client_signature'])) {
		$mail_to_client .= $attr['client_signature']."\n";
	} else {
		$mail_to_client .= 'Date: '.date('Y-m-d (D) H:i:s', UTIME )."\n";
	}
	$mail_to_client = PKWKMAIL_mailformat($mail_to_client);

	if($attr['admin_return_allowed'] == 1) {
		$mail_to_client_header = 'From:'.$admin_adrs_return."\n";
		$mail_to_client_header .= 'Reply-To:'.$admin_adrs_return."\n";
	}
	$mail_to_client_header .= 'Content-Type: text/plain;charset=iso-2022-jp'."\n";
	$mail_to_client_header .= 'X-Mailer: PKWKMAIL';

//	$mail_to_client_title = mb_convert_encoding($attr['contact_title_to_client'], 'ISO-2022-JP', 'auto');
	$mail_to_client_title = $attr['contact_title_to_client'];
	$mail_to_client_title = base64_encode($mail_to_client_title);
	$mail_to_client_title = '=?iso-2022-jp?B?'.$mail_to_client_title.'?=';

	//mail - 送信
	$send_err_admin = $send_err_client = false;

	//mail to admin
	if($google_apps && preg_match('/.*@'.$google_apps_domain.'$/',$mail_content['admin_adrs']))
	{
		$mail = & new Qdmail();
		$mail -> smtp(true);

		$param = array(
			'host'=>'ASPMX.L.GOOGLE.com',
			'port'=> 25, 
			'from'=>$mail_content['mail_adrs'],
			'protocol'=>'SMTP',
			'user'=>'root@'.$google_apps_domain, //SMTPサーバーのユーザーID
			'pass' =>$passwd, //SMTPサーバーの認証パスワード
		);
		$mail -> smtpServer($param);
		
		$mail ->to($mail_content['admin_adrs']);
		$mail ->subject($mail_to_admin_title);
		$mail ->from($mail_content['mail_adrs']);
		$mail ->text($mail_to_admin);
		
		$retval = $mail ->send();
		
	}
	else{
		if(!mail($mail_content['admin_adrs'],$mail_to_admin_title,$mail_to_admin,$mail_to_admin_header)) $send_err_admin = true;
	}

	//mail to client
	if($mail_content['sendmeacopy'] == 1) {
		if(!mail($mail_content['mail_adrs'],$mail_to_client_title,$mail_to_client,$mail_to_client_header)) $send_err_client = true;
	}

	//rendering - 送信結果画面
	$vars['page'] = $attr['finish_message_title'].' - '.htmlspecialchars( $vars['page'],ENT_QUOTES );
	$result_finish ='';
	if(!$send_err_admin && !$send_err_client) {
		$result_finish = $attr['finish_message'];
		$result_finish .= '<table class="'.PKWKMAIL_TABLE_CLASS.'">'."\n";
		$result_finish .= $scrn_content;
		$result_finish .= '</table>'."\n";
		$result_finish .= $attr['finish_message_return'];
	}
	if($send_err_admin)  $result_finish .= '<p>'.$_pkwkmail_msg['send_err_admin'].'</p>'."\n";
	if($send_err_client) $result_finish .= '<p>'.$_pkwkmail_msg['send_err'].'</p>'."\n";

	return $result_finish;
}

function PKWKMAIL_trim( $val )
{
	$retval = trim( $val,"'" );
	return $retval;
}

function PKWKMAIL_mailformat($mail_content)
{
//	$mail_content = mb_convert_kana($mail_content,"KV");
	$mail_content = str_replace('&amp;', '&', $mail_content);
	$mail_content = str_replace("\r", "\n", str_replace("\r\n", "\r", $mail_content));	
//	$mail_content = mb_convert_encoding($mail_content, 'ISO-2022-JP', 'auto');

	return $mail_content;
}

function PKWKMAIL_format_date($val)
{
	global $_pkwkmail_msg, $weeklabels;

	$val += ZONETIME;
	$date = gmdate($_pkwkmail_msg['fmt_date'], $val) .' [' . $weeklabels[gmdate('w', $val)] . '] ' .gmdate($_pkwkmail_msg['fmt_time'], $val);

	return $date;
}

function PKWKMAIL_MailAddrCheck($x,$domain_check)
{
	if( $x == '' ){ return; }
	$retval = array('msg'=>'', 'err'=>false);
	$addrs = explode(',', $x); // 複数指定時対応

	foreach($addrs as $addr) {
		if($addr && !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i",$addr)) {
			$retval['msg'] = 'invalid_madrs';
			$retval['err'] = true;
			return $retval;
		}

		if(! $domain_check) continue;
		if(! function_exists('checkdnsrr')) continue;

		list ($user,$host) = explode('@',$addr);
		if( !checkdnsrr($host,'MX') ) {
			if( !checkdnsrr($host,'A') ) {
				$retval['msg'] = 'invalid_mdmain';
				$retval['err'] = true;
				return $retval;
			}
		}
	}

	return $retval;
}
?>