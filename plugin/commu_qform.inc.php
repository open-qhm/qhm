<?php
/**
* qform.inc.php
*
* 引数 : $title, $style, $save, $labelConfirm, $labelSubmit, $labelBack
*
* 
* Modified:
*  2009 11/9 確認画面で、複数選択項目が表示されない問題を解決
*
*/

define('PLUGIN_QFORM_UNLINK_TIME', 60*60); //1時間前
define('QFORM_DEFAULT_ATTACHE_SIZE', 4);   //添付ファイルのデフォルト制限


require_once(LIB_DIR.'simplemail.php');
if( file_exists('lib/qdmail.php') ){
	require_once('lib/qdmail.php');
}
if( file_exists('lib/qdsmtp.php') ){
	require_once('lib/qdsmtp.php');
}


/*
//definitions
define('QFORM_CONFIRM_TITLE_ADD',' の確認');
define('QFORM_CONFIRM_TITLE','確認画面');
define('QFORM_CONFIRM_BUTTON', '　確　認　');
define('QFORM_BACK_BUTTON', ' 戻る ');
define('QFORM_ERROR_MSG_ADD', ' を正しく入力してください');
define('QFORM_SUBMIT_BUTTON', ' 送信 ');
define('QFORM_FINISH_TITLE', '送信完了');
define('QFORM_FINISH_MSG', '投稿しました');

define('STATE_SELECT', '北海道,青森県,岩手県,宮城県,秋田県,山形県,福島県,茨城県,栃木県,群馬県,埼玉県,千葉県,東京都,神奈川県,新潟県,富山県,石川県,福井県,山梨県,長野県,岐阜県,静岡県,愛知県,三重県,滋賀県,京都府,大阪府,兵庫県,奈良県,和歌山県,鳥取県,島根県,岡山県,広島県,山口県,徳島県,香川県,愛媛県,高知県,福岡県,佐賀県,長崎県,熊本県,大分県,宮崎県,鹿児島県,沖縄県');
*/

if( file_exists('lib/qdmail.php') ){
	require_once('lib/qdmail.php');
}
if( file_exists('lib/qdsmtp.php') ){
	require_once('lib/qdsmtp.php');
}

function parse_csv($line, $delimiter=','){
	
    $expr="/$delimiter(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";
    $fields = preg_split($expr,trim($line)); // added
    $fields = preg_replace("/^\"(.*)\"$/s","$1",$fields); //added
    
    return $fields;
}

/**
 *
 * convert plugins
 *
 */
// qhm	function plugin_qform_convert()
function plugin_commu_qform_convert()
{
	$qm = get_qm();
	$qt = get_qt();

	//--- キャッシュを無効に ---
	$qt->enable_cache = false;

	global $vars, $script, $editable;
	$page = $vars['page'];
    $editable = edit_auth($page, FALSE, FALSE);
	
	$args = func_get_args();
	$pstr = array_pop($args);
	
	$title = isset($args[0]) ? $args[0] : $qm->m['plg_qform']['title_none'];;
	$style = isset($args[1]) ? $args[1] : 'table';
	$save  = isset($args[2]) ? $args[2] : 'false';
		
	//set design
	$addstyle = '
<style type="text/css">
.qform_table{
	width:85%;
	margin:1.5em auto;
}
.qform_table tr th{
	padding: 10px;
/* 	font-weight:normal;	 */
	border-bottom: 1px solid #ccc;
	white-space: nowrap;
}
.qform_table tr td{
	padding: 10px;
	font-weight:normal;	
	border-bottom: 1px solid #ccc;
}
.qform_form textarea,
.qform_form input[type=text]
{
	padding:3px 5px;
	width:97%;
	border:1px solid #ccc;
}
.qform_value{
	border:0;
}
.qform_table tr td.qform_value{
	padding: 10px 10px 20px 10px;
	font-weight:normal;	
	border-bottom: none;
}

</style>
	';
	$qt->appendv_once('plugin_qform', 'beforescript', $addstyle);

	$params = plugin_qform_parse($pstr);

	if( isset($vars['qform_condition']) )
	{
		//
		// send mail & ...
		//
		if( isset($vars['qform']['qform_finish']) )
		{
			if(! isset($_SESSION['qform']) ){
				header("Location: ".$script.'?'.urlencode($page) );
				exit;
			}
					
			//転送する、postする、完了を出すなどなど
			if($save === 'true'){
				plugin_qform_save_log($params, $page, $title);
			}
			return plugin_qform_do_finish($params);	
		}
		
		
		//
		// 戻る
		//
		if( isset($vars['qform']['qform_back']) ){
			$_SESSION['qform']['enable_session_check'] = time();  //セッション有効チェック用
			plugin_qform_unlink_attaches(); //添付ファイル削除(あれば)
			
			force_output_message(
					$qm->replace('plg_qform.title_confirm', $title),
					$page,
					'<h2>'.$title.'</h2>'.plugin_qform_mkform($params, $style)
			);
		}
		
		
		//
		// 確認
		//
		if($vars['qform_condition']==='confirm')
		{

			//sessionチェック
			if(! isset($_SESSION['qform']['enable_session_check']) ){
				$ss_error .= "<br />".$qm->m['plg_qform']['err_enable_session']."\n";
			}else{
				$ss_error = '';
			}

			$_SESSION['qform'] = $vars['qform'];
			$error = plugin_qform_check($params);
			$error .= $ss_error;

		
			if($error===''){
				$body = plugin_qform_confirm($params, $style, $title);
				force_output_message($qm->m['plg_qform']['title_confirm2'], $page, $body);
			}
			else{
				$_SESSION['qform']['enable_session_check'] = time();  //セッション有効チェック用
			
				force_output_message(
					$qm->replace('plg_qform.title_confirm', $title), 
					$page, 
					'<h2>'.$title.'</h2><p style="color:red">'.$error.'</p>' . plugin_qform_mkform($params, $style)
				);
			}
		}
	}
	else{
	
		//デフォルトの動作(フォームの表示)
		plugin_qform_unlink_attaches(); //添付ファイル削除
		
		$_SESSION['qform'] = null; //セッションクリア
		unset($_SESSION['qform']);
		
		$_SESSION['qform']['enable_session_check'] = time();
		
		$body = plugin_qform_mkform($params, $style);
		$error = plugin_qform_conf_check($params); 
		
		$conf_page = ':config/plugin/qform/'.$page;
		
		$tail = '';
		if($editable && is_page(':config/plugin/qform/'.$page) )
			$tail = "\n".'<p style="text-align:right"><a href="'.$script.'?'.rawurlencode($conf_page).'" target="new">'. $qm->m['plg_qform']['link_log_chk'].'</a></p>'."\n";
	
		return $error.$body.$tail;
	}
}

function plugin_qform_action(){
	global $vars, $script;
	
	$id = $vars['id'];
	$path = $_SESSION['qform']['_FILES'][$id]['path'];
	$name = $_SESSION['qform']['_FILES'][$id]['name'];
//var_dump($path, $name);	
	if($path != '' && file_exists($path) ){

		$got = @getimagesize($path);
		if (! isset($got[2])) $got[2] = FALSE;
		switch ($got[2]) {
		case 1: $type = 'image/gif' ; break;
		case 2: $type = 'image/jpeg'; break;
		case 3: $type = 'image/png' ; break;
		case 4: $type = 'application/x-shockwave-flash'; break;
		default:
			$type = get_mimetype($name);
		}
				
		$file = htmlspecialchars($name);
		$size = filesize($path);
		
		pkwk_common_headers();
		header('Content-Disposition: inline; filename="' . $file . '"');
		header('Content-Length: ' . $size);
		header('Content-Type: '   . $type);
		@readfile($path);

	}
	else{
		echo 'No data';
	}
	
	exit;	//exitをシッカリしないと、フォームのデータ(SESSION)が失われるので注意
}

/**
 * 与えられた文字列から、フォームのためのパラメータを解析
 */
function plugin_qform_parse($str)
{

	global $vars;
	$qm = get_qm();

	$lines = preg_split("\n|\r", $str);
	
	$ret = array('element'=>array(), 'conf'=>array());
	
	$multi = false;
	
	$end = count($lines);
	for( $index=0; $index<$end; $index++ ){
	
		$line = $lines[$index];

		if(preg_match('/^(.+?)=(.*)/', $line, $ms)){
			$cmd = $ms[1];
			$params = $ms[2];
			$arr = parse_csv($params);
			$arr = parse_csv($params);
		}
		else{
			$cmd = '';
			$arr = null;
		}
		
		switch($cmd){
			
			// form要素
			case 'text' :  //テキスト入力ボックス
				list($id, $label, $default, $exp, $validation, $size) = array_pad($arr, 6, '');
				$el = array(
					'type'	=> 'text',
					'id'	=> $id,
					'label'	=> $label,
					'default'=> $default,
					'exp'	=> $exp,
					'validation' => $validation,
					'size' => $size
				);
				
				$ret['element'][$id] = $el; 
				break;
			
			case 'check' : 
//				list($id, $label, $default, $exp, $validation) = array_pad($arr, 5, '');
				list($id, $label, $default, $exp) = array_pad($arr, 4, '');
				$el = array(
					'type'	=> $cmd,
					'id'	=> $id,
					'label' => $label,
					'default'	=> $default,
					'exp'	=> $exp,
//					'validation' => $validation,
					'validation' => '',
//					'option' => array_slice($arr, 5)
					'option' => array_slice($arr, 4)
				);
				
				$ret['element'][$id] = $el;
				break;

			case 'attach' :
				list($id, $label, $exp, $validation, $size) = array_pad($arr, 5, '');
				$size = is_numeric($size) ? $size : QFORM_DEFAULT_ATTACHE_SIZE;

				$el = array(
					'type'	=> 'attach',
					'id'	=> $id,
					'label'	=> $label,
					'exp'	=> $exp,
					'validation' => $validation,
					'size' => $size
				);

				$ret['element'][$id] = $el; 
				break;
			
			case 'select' :
			case 'radio' :
				list($id, $label, $default, $exp) = array_pad($arr, 4, '');
				$el = array(
					'type'	=> $cmd,
					'id'	=> $id,
					'label' => $label,
					'default'	=> $default,
					'exp'	=> $exp,
					'option' => array_slice($arr, 4),
				);
				
				$ret['element'][$id] = $el;
				break;
						
			case 'memo' :
				list($id, $label, $default, $exp, $validation, $rows) = array_pad($arr, 6, '');
				
				$rows = $rows ? $rows : 10;
				$el = array(
					'type'	=> 'memo',
					'id'	=> $id,
					'label'	=> $label,
					'default'=> $default,
					'exp'	=> $exp,
					'validation' => $validation,
					'rows'	=> $rows
				);
				
				$ret['element'][$id] = $el;
				break;
								
			case 'address' :
//				list($label, $exp, $validation) = array_pad($arr, 3, '' );
				list($label, $exp, $validation,$defaultstate) = array_pad($arr, 4, '');
				
				$vv = array('zip'=>'', 'state'=>'', 'city'=>'', 'street'=>'');
				if($validation=='') $validation = 4;
				$cnt = 1;
				foreach($vv as $k=>$v){
					if($validation < $cnt)
						break;
					
					$vv[$k] = 'need';
					$cnt++;
				}
				
				$el = array(
					'type'  => 'address_zip',
					'id'    => 'address_zip',
					'label' => $label,
					'exp'   => $exp,
					'validation' => $vv['zip'],
				);
				$ret['element']['address_zip'] = $el;

				$el = array(
					'type'  => 'address',
					'id'    => 'address_state',
					'label' => $label,
					'exp'   => $exp,
					'validation' => $vv['state'],
					'default' => $defaultstate,
				);
				$ret['element']['address_state'] = $el;

				$el = array(
					'type'  => 'address',
					'id'    => 'address_city',
					'label' => $label,
					'exp'   => $exp,
					'validation' => $vv['city'],
				);
				$ret['element']['address_city'] = $el;

				$el = array(
					'type'  => 'address',
					'id'    => 'address_street',
					'label' => $label,
					'exp'   => $exp,
					'validation' => $vv['street'],
				);
				$ret['element']['address_street'] = $el;
			
				break;
				
			case 'name' :
				list($label, $exp, $validation) = array_pad($arr, 3, '' );
				
				if($validation=='lname'){
					$vl = 'need';
					$vf = '';
				}
				else if($validation=='fname'){
					$vl = '';
					$vf = 'need';
				}
				else{
					$vl = 'need';
					$vf = 'need';
				}
				
				$el = array(
					'type'  => 'lname',
					'id'    => 'lname',
					'label' => $label,
					'exp'   => $exp,
					'validation' => $vl,
				);
				$ret['element']['lname'] = $el;				

				$el = array(
					'type'  => 'fname',
					'id'    => 'fname',
					'label' => $label,
					'exp'   => $exp,
					'validation' => $vf,
				);
				$ret['element']['fname'] = $el;
				
				break;
				
			case 'name_kana' :
				list($label, $exp, $validation) = array_pad($arr, 3, '');
				
				if ($validation == 'lname_kana') {
					$vl = 'need';
					$vf = '';
				}
				else if($validation=='fname_kana'){
					$vl = '';
					$vf = 'need';
				}
				else if($validation=='need'){
					$vl = 'need';
					$vf = 'need';
				}
				else{
					$vl = '';
					$vf = '';
				}
				
				$el = array(
					'type'  => 'lname_kana',
					'id'    => 'lname_kana',
					'label' => $label,
					'exp'   => $exp,
					'validation' => $vl,
				);
				$ret['element']['lname_kana'] = $el;				

				$el = array(
					'type'  => 'fname_kana',
					'id'    => 'fname_kana',
					'label' => $label,
					'exp'   => $exp,
					'validation' => $vf,
				);
				$ret['element']['fname_kana'] = $el;
								
				break;
				
			case 'email' :
				$email_match = '/^([a-z0-9_]|\-|\.|\+)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,6}$/i';
				list($label, $exp, $confirm) = array_pad($arr, 3, '');
				$id = 'email';
				
				$el = array(
					'type'	=> 'email',
					'id'	=> $id,
					'label'	=> $label,
					'exp'	=> $exp,
					'validation' => $email_match, 
					'confirm' => $confirm,
				);
				
				$ret['element'][$id] = $el;				
				break;
				
			case 'state' :
				list($label, $exp, $default) = array_pad($arr, 3, '');
				$el = array(
					'type'  => 'state',
					'id'    => $id,
					'label' => $label,
					'exp'   => $exp,
					'default' => $default,
					'validation' => '',
				);
				$ret['element']['state'] = $el;
				break;
				
			case 'contract':
				list($label, $cblabel, $pagename) = array_pad($arr, 3, '');
				$el = array(
					'type' => 'contract',
					'id' => 'contract',
					'label' => $label,
					'cblabel' => $cblabel,
					'pagename' => $pagename,
					'validation' => 'need',
				);
				$ret['element']['contract'] = $el;
				break;

			case 'buttons':
				list($confirm, $back, $submit) = array_pad($arr, 3, '');
				$el = array(
					'type' => 'buttons',
					'confirm' => trim($confirm)? $confirm: $qm->m['plg_qform']['btn_confirm'],
					'back' => trim($back)? $back: $qm->m['plg_qform']['btn_back'],
					'submit' => trim($submit)? $submit: $qm->m['plg_qform']['btn_submit']
				);
				$ret['element']['buttons'] = $el;
				break;
				
			// param phase
			case 'finish_url' :
				$ret['conf']['finish_url'] = is_url($arr[0]) ? $arr[0] : '';
				
				break;
				
			case 'finish_post' :
				list($url, $encode) = array_pad($arr, 2, '');

				$data = array();
				for($i=2; $i<count($arr); $i+=2){
					$data[ $arr[$i] ] = $arr[$i+1];
				}
				$ret['conf']['finish_post'] = array(
					'url' 	=> $url,
					'encode'=> $encode,
					'data'		=> $data
				);
				
				break;
				
			case 'finish_msg' : // 完了メッセージ
				$body = '';
				
				
				for( $index++ ; $index<$end; $index++){
					$l = $lines[$index];
//echo '-- '.$l."<br />\n";
					if( trim($l)=="''" ) break;
					$body .= $l."\n";	
				}
				
				$ret['conf']['finish_msg'] = $body;
				//echo $body;
				
				break;

			case 'finish_mail' : 	// 返信メール				
				$body = '';
				$subject = null;
				$from = null;
				
				for( $index++ ; $index<$end; $index++){
					$l = $lines[$index];
				
					if( trim($l)=="''" ) break;
					
					if(preg_match('/^::From::(.*?),(.*)$/', $l, $ms) ){
						$from = array('name'=>$ms[1], 'email'=>$ms[2]);
					}
					else if(preg_match('/^::Subject::(.*)$/', $l, $ms) ){
						$subject = $ms[1];
					}
					else{
						$body .= $l."\n";
					}
				}
				
				$ret['conf']['finish_mail'] = array(
					'subject' 	=> $subject,
					'from'		=> $from,
					'body'		=> $body
				);
				

				break;

			case 'notice_mail' : 	// 返信メール				
				$body = '';
				$subject = null;
				$from = null;
				$to = null;
				
				for( $index++ ; $index<$end; $index++){
					$l = $lines[$index];
				
					if( trim($l)=="''" ) break;
					
					if(preg_match('/^::Subject::(.*)$/', $l, $ms) ){
						$subject = $ms[1];
					}
					else if(preg_match('/^::To::(.*?)$/', $l, $ms) ){
						$to = array('name'=>'', 'email'=>$ms[1]);
					}
					else{
						$body .= $l."\n";
					}
				}
				
				$ret['conf']['notice_mail'] = array(
					'subject' 	=> $subject,
					'from'		=> $from,
					'to'		=> $to,
					'body'		=> $body
				);

				break;
			

				
			default:
				//do nothing
		
		}
	}	
		
	return $ret;
}

/**
* パラメータのチェック
*/
function plugin_qform_conf_check(&$params)
{
	$qm = get_qm();

	$error = '';
	
	//elementのチェック
	$els = $params['element'];
	if(! isset($els['email']) ){
		$error .= $qm->m['plg_qform']['err_no_email']. '<br />';
	}
	
	//confのチェック
	$conf = $params['conf'];
	if(! isset($conf['notice_mail'])){
		$error .= $qm->m['plg_qform']['err_nm_conf'].'<br />';
	}
	else{
		//メールアドレスチェックなど
		if(! isset($conf['notice_mail']['to']) || !preg_match('/.*@.*/', $conf['notice_mail']['to']['email']) )
			$error .= $qm->m['plg_qform']['err_nm_email']. '<br />';
		if(! isset($conf['notice_mail']['body']))
			$error .= $qm->m['plg_qform']['err_nm_body'].'<br />';
	}
	
	if(! isset($conf['finish_mail'])){
		$error .= $qm->m['plg_qform']['err_fm_conf']. '<br />';
	}
	else{
		//メールアドレスチェックなど
		if(! isset($conf['finish_mail']['from']))
			$error .= $qm->m['plg_qform']['err_fm_email']. '<br />';
		if(! isset($conf['finish_mail']['body']))
			$error .= $qm->m['plg_qform']['err_fm_body'].'<br />';
	}
	
	return '<p style="color:red">'.$error.'</p>';

}

/*
* フォームを生成
*/
function plugin_qform_mkform($data, $style='table')
{
	global $vars, $script;
	$qm = get_qm();

	$page = $vars['page'];

	$posted = isset($_SESSION['qform']) ? $_SESSION['qform'] : false;
	
	$els = $data['element'];
	$data_set = array();
	$enctype = '';
	
	foreach($els as $key=>$el){
	
		switch($el['type']){
			// !input[type=text]
			case 'text' :
				$str = isset($posted[ $el['id'] ]) ? $posted[ $el['id'] ] : $el['default'];
				if ($el['size']) {
					$size = ' size="' .$el['size']. '"';
					$st_size = 'width:'.$el['size'].'em;';
				} else {
					$size = $st_size = '';
				}
				$content = '<input type="text" name="qform['.$el['id'].']" value="'.$str.'"'.$size.' style="'.$st_size.'" />';
				$valid = $el['validation']=='' ? false : true;
				break;
	
			// !input[type=checkbox]
			case 'check' :				
				$content = '';
				foreach($el['option'] as $k=>$v){
					$chked = ($v == $el['default']) ? 'checked="checked"' : '';
					if( isset($posted[ $el['id'] ]) ){
						$chked = isset($posted[ $el['id'] ][$k]) ? 'checked="checked"' : '';
					}

					$content .= <<<EOD
	<label><input type="checkbox" name="qform[{$el['id']}][{$k}]" {$chked} value="{$v}"> {$v} </label>
EOD;
				}
				
				$valid = $el['validation']=='' ? false : true;
				break;	

			// !select
			case 'select' :
				$content = <<<EOD
	<select name="qform[{$el['id']}]">
EOD;
				foreach($el['option'] as $k=>$v){
					$chked = ($v==$el['default']) ? 'selected="selected"' : '';
					if( isset($posted[ $el['id'] ]) ){
						$chked = $v==$posted[ $el['id'] ] ? 'selected="selected"' : '';
					}
					
					$content .= <<<EOD
		<option value="{$v}" {$chked}>{$v}</option>
EOD;
				}
				$content .= '</select>';
				$valid = false;
				break;
	
			// !input[type=radio]
			case 'radio' :
				$content = '';
				foreach($el['option'] as $k=>$v){
					$chked = ($v==$el['default']) ? 'checked="checked"' : '';
					if( isset($posted[ $el['id'] ]) ){
						$chked = $v==$posted[ $el['id'] ] ? 'checked="checked"' : '';
					}

					$content .= <<<EOD
	<label><input type="radio" name="qform[{$el['id']}]" value="{$v}" {$chked}> {$v} </label>
EOD;
				}			
				$valid = false;
				break;
			
			// !textarea(memo)
			case 'memo' :
				if( isset($posted[ $el['id'] ]) ){
					$default = htmlspecialchars($posted[ $el['id'] ]);
				}
				else{
					$default = str_replace('&br;',"\n", $el['default']);
				}
				$content = '<textarea name="qform['.$el['id'].']" rows="'.$el['rows'].'" style="width:97%">'.$default.'</textarea>';
				$valid = $el['validation']=='' ? false : true;
				break;

			// !input[name='qform[email]']
			case 'email' : 
// qhm				$str = isset($posted['email']) ? $posted['email'] : '';
// qhm				$readonly = '';

				$str = isset($posted['email']) ? $posted['email'] : $_SESSION['commu_user']['email'];
				$readonly = isset($_SESSION['commu_user']['email']) ? ' readonly="readonly"' : '';

				$content = '<input type="text" name="qform[email]" value="'.$str.'" style="width:97%"'.$readonly.' />';

				if (trim($el['confirm']) && !$readonly) {
					$val = isset($posted['email2'])? $posted['email2']: '';
					$content .= '<br /><br /><input type="text" name="qform[email2]" value="'. h($val). '" style="width:97%" />';
					$content .= '<br /><span style="font-size:.8em;">※確認のためもう一度入力してください</span>';
				}
				
				$valid = $el['validation']=='' ? false : true;

				break;

			// !input[type='qform[name']
			case 'lname' : 
				$str = '';
// qhm				$lname = isset($posted['lname']) ? $posted['lname'] : '';
// qhm				$fname = isset($posted['fname']) ? $posted['fname'] : '';
// qhm				$l_readonly = '';
// qhm				$f_readonly = '';

				$lname = isset($posted['lname']) ? $posted['lname'] : $_SESSION['commu_user']['lastname'];
				$fname = isset($posted['fname']) ? $posted['fname'] : $_SESSION['commu_user']['firstname'];
				$l_readonly = isset($_SESSION['commu_user']['lastname']) ? ' readonly="readonly"' : '';
				$f_readonly = isset($_SESSION['commu_user']['firstname']) ? ' readonly="readonly"' : '';

				$content = '<input type="text" name="qform[lname]" value="'.$lname.'" size="10"  style="width:auto;"'.$l_readonly.' /> <input type="text" name="qform[fname]" value="'.$fname.'" size="10" style="width:auto;"'.$f_readonly.' />';

				$valid = $el['validation']=='' ? false : true;

				break;

			// !input[type='qform[name_kana]']
			case 'lname_kana' : 
				$str = '';
				$lname_kana = isset($posted['lname_kana']) ? $posted['lname_kana'] : '';
				$fname_kana = isset($posted['fname_kana']) ? $posted['fname_kana'] : '';
				$l_readonly = '';
				$f_readonly = '';

// commu				$lname = isset($posted['lname']) ? $posted['lname'] : $_SESSION['commu_user']['lastname'];
// commu				$fname = isset($posted['fname']) ? $posted['fname'] : $_SESSION['commu_user']['firstname'];
// commu				$l_readonly = isset($_SESSION['commu_user']['lastname']) ? ' readonly="readonly"' : '';
// commu				$f_readonly = isset($_SESSION['commu_user']['firstname']) ? ' readonly="readonly"' : '';

				$content = '<input type="text" name="qform[lname_kana]" value="'.$lname_kana.'" size="10"  style="width:auto;"'.$l_readonly.' /> <input type="text" name="qform[fname_kana]" value="'.$fname_kana.'" size="10" style="width:auto;"'.$f_readonly.' />';

				$valid = $el['validation']=='' ? false : true;

				break;

			//!address
			case 'address_zip' :
//				$selected = isset($posted['address_state']) ? $posted['address_state'] : '東京都';
				$selected = isset($posted['address_state']) ? $posted['address_state'] : 
					( ($els['address_state']['default'] != '') ? $els['address_state']['default'] : '東京都' );

				$options = parse_csv($qm->m['plg_qform']['states']);
				$str = '';
				foreach($options as $op){
					$sld = $op==$selected ? 'selected="selected"' : '';
					$str .= '<option value="'.$op.'" '.$sld.'>'.$op.'</option>'."\n";
				}
				
				$arr = array();
				foreach(array('zip', 'city', 'street') as $n)
					$arr[$n] = isset($posted['address_'.$n]) ? $posted['address_'.$n] : '';

				$content = <<<EOD
<p style="line-height:2em; margin:0 0;">{$qm->m['plg_qform']['zipcode']} : <input type="text" name="qform[address_zip]" value="{$arr['zip']}" style="width:7em;" /><br />
{$qm->m['plg_qform']['state']} : <select name="qform[address_state]">{$str}</select><br />
{$qm->m['plg_qform']['city']} : <input type="text" name="qform[address_city]" value="{$arr['city']}" style="width:70%;" /><br />
{$qm->m['plg_qform']['street']} : <input type="text" name="qform[address_street]" value="{$arr['street']}" style="width:70%;" /></p>
EOD;

				$valid = $el['validation']=='' ? false : true;

				break;
				
			// !states
			case 'state' : 
				$default = $el['default']!='' ? $el['default'] : $qm->m['plg_qform']['def_state']; 
				$selected = isset($posted['state']) ? $posted['state'] : $default;
				$options = parse_csv($qm->m['plg_qform']['states']);
				$str = '';
				foreach($options as $op){
					$sld = $op==$selected ? 'selected="selected"' : '';
					$str .= '<option value="'.$op.'" '.$sld.'>'.$op.'</option>'."\n";
				}
				$content = '<select name="qform[state]">'.$str.'</select>';

				$valid = $el['validation']=='' ? false : true;
				break;
			// !contract
			case 'contract' :
				//self include
				if ($vars['page'] == $el['pagename']) {
					$contract = '<p style="color:red;">'. $qm->m['plg_qform']['err_looppage']. '</p>';
				}
				else if (is_page($el['pagename'])) {
					$contract = convert_html(get_source($el['pagename']));
					$contract = '<div style="width:100%;max-height:150px;overflow-y:scroll;margin:5px auto;border:1px solid #ccc;">'. $contract. '</div>';
//					$contract .= '<div style="text-align:right;"><a href="">利用規約を開く</a></div>';
				}
				//page not found
				else {
					$contract = '<p style="color:red;">'. $qm->m['fmt_err_notfoundpage_title']. '</p>';
				}

				$cblabel = strlen($el['cblabel'])? $el['cblabel']: $qm->m['plg_qform']['lbl_agree'];
				$content = '
'. $contract. '
<div style="margin:5px 0 auto;text-align:center;">
	<label><input type="checkbox" name="qform[contract]" value="'. $qm->m['plg_qform']['agree']. '" />&nbsp;'. $cblabel. '</label>
</div>
';
				$valid = true;
				break;	
			
			// !添付ファイル
			case 'attach' :
				
				$content = '<input type="file" name="qform['.$el['id'].']" />';
				$valid = $el['validation']=='' ? false : true;
				
				$enctype = ' enctype="multipart/form-data" ';

				break;
			
			// !buttons setting
			case 'buttons':
				$qm->m['plg_qform']['btn_confirm'] = $el['confirm'];
				$qm->m['plg_qform']['btn_back'] = $el['back'];
				$qm->m['plg_qform']['btn_submit'] = $el['submit'];
				$content = '';
			break;
			
			default:
				$content = '';

		}
		
		if($content !==''){
			$data_set[] = array( 'label'=>$el['label'], 'content'=>$content, 'exp'=>$el['exp'], 'valid'=>$valid );
		}
	}

	$keitai = '';
	if (UA_PROFILE=='keitai') {
		$keitai = '<input type="hidden" name="mobssid" value="yes" />
<input type="hidden" name="'.session_name().'" value="'.session_id().'" />
';
	}

	$form = '<form action="'.$script.'" method="post" class="qform_form"'.$enctype.'>'."\n";	
	$form .= '<input type="hidden" name="cmd" value="read" /> ';
	$form .= '<input type="hidden" name="page" value="'.$page.'" /> ';
	$form .= '<input type="hidden" name="qform_condition" value="confirm" /> ';
	$form .= $keitai;
	$form .= plugin_qform_format($data_set, $style);
	
	$form .= '<p style="text-align:center"><input type="submit" name="submit" value="'.$qm->m['plg_qform']['btn_confirm'].'" /></p>';
	$form .= '</form>';
	
	return $form;

}

function plugin_qform_format($data_set, $style="table2")
{
	$astar = '<span style="color:red">*</span>';
	if( $style=='default'){
		$str = '<table class="style_table" colspan="0" style="width:90%">';
		foreach($data_set as $d){
			$astr = isset($d['valid']) && $d['valid'] ? $astar : '';
			$str .= '<tr><th class="style_th">'.$d['label'].$astr.'</th><td class="style_td">'.$d['content'].'<br /><span style="font-size:x-small">'.$d['exp'].'</span></td></tr>';
		}
		$str .= '</table>'; 
		
	}
	else if( $style=='table' ){
		$str = '<table class="qform_table" colspan="0">';
		foreach($data_set as $d){
			$astr = isset($d['valid']) && $d['valid'] ? $astar : '';
			$str .= '<tr><th>'.$d['label'].$astr.'</th><td>'.$d['content'].'<br /><span style="font-size:x-small">'.$d['exp'].'</span></td></tr>';
		}
		$str .= '</table>'; 
	}
	else{
		$str = '<table class="qform_table" colspan="0">';
		foreach($data_set as $d){
			$astr = isset($d['valid']) && $d['valid'] ? $astar : '';
			$str .= '<tr><th style="text-align:left;">'.$d['label'].$astr.'</th></tr>
			<tr><td class="qform_value">'.$d['content'].'<br /><span style="font-size:x-small">'.$d['exp'].'</span></td></tr>';
		}
		$str .= '</table>'; 
		
	}

	return $str;

}


/**
* フォームのバリデーション
*/
function plugin_qform_check(& $params)
{
	global $vars;
	$qm = get_qm();
	
	$els = $params['element'];
	$error = '';
	foreach($els as $k=>$el)
	{
		if( isset($el['validation']) && $el['validation']!=='') //何らかのvalidationがあるなら
		{
			$ptrn = $el['validation'];
			$chk_ptrn = '';
			if( $ptrn === 'need' ){ //何かは必要
				$chk_ptrn = '/^(.+)$/s';
			}
			else if( $ptrn === 'num'){ //numberのみ
				$chk_ptrn = '/^[0-9]+$/s';
			}
			else if( $ptrn === 'en'){ //半角英数のみ
				$chk_ptrn = '/^[0-9a-zA-Z]$/s';
			}
			else{ //正規表現なら
				$chk_ptrn = $ptrn;
			}
			
			//checkboxes
			if ($el['type'] === 'check' || $el['type'] === 'contract') {
				if (!isset($vars['qform'][$el['id']]))
					$error .= $qm->replace('plg_qform.err_no_check', $el['label']). '<br />';
			}
			//confirm email
			else if ($el['type'] === 'email' && trim($el['confirm']) && $vars['qform'][$el['id']] != $vars['qform']['email2']) {
				$error .= 'メールアドレスは正しく二度入力してください<br />';
			}
			//添付ファイル
			else if ($el['type'] === 'attach'){
				$size = $el['size']*1000*1000;
				$id = $el['id'];
				$name = $_FILES['qform']['name'][$id];
				
				if(! preg_match($chk_ptrn, $name) ){
					$error .= $qm->replace('plg_qform.err_invalid_ptn_file', $el['label'].'('.$name.')'). '<br />';
				}
				else if( $name == '' ){
					$error .= $qm->replace('plg_qform.err_invalid_ptn', $el['label']). '<br />';
				}
				else{			
					if( $_FILES['qform']['size'][$id] > $size ){ //file size over check
						$error .= $qm->replace('plg_qform.err_oversize', $el['label'].'('.$name.')'). '<br />';
					}
					else if( $_FILES['qform']['error'][$id] ){ //error check
						$error .= $qm->replace('plg_qform.err_upload', $el['label'].$_FILES['qform']['error'][$id]). '<br />';
					}
					else{ //move upload file
						
						$tmpname = tempnam(CACHEQHM_DIR,'qform_');
						
						if( move_uploaded_file($_FILES['qform']['tmp_name'][$id], $tmpname ) ){

							$_SESSION['qform'][$id] = $name;
							$_SESSION['qform']['_FILES'][$id] = array('name'=>$name, 'path'=>$tmpname);
						
						}
						else{
							$error .= $qm->replace('plg_qform.err_upload', $el['label'].'('.$_FILES['qform']['error'][$id].')'). '<br />';
						}
					
					}
				}
							
			}
			else if(! preg_match($chk_ptrn, $vars['qform'][ $el['id'] ] ) ){
				//特別処理
				if($k==='lname'){ $add = $qm->replace('plg_qform.fmt_paren', $qm->m['plg_qform']['lname']); }
				else if($k==='fname'){ $add = $qm->replace('plg_qform.fmt_paren', $qm->m['plg_qform']['fname']); }
				else if($k==='lname_kana'){ $add = $qm->replace('plg_qform.fmt_paren', $qm->m['plg_qform']['lname_kana']); }// TODO
				else if($k==='fname_kana'){ $add = $qm->replace('plg_qform.fmt_paren', $qm->m['plg_qform']['fname_kana']); }// TODO
				else if($k==='address_zip'){ $add = $qm->replace('plg_qform.fmt_paren', $qm->m['plg_qform']['zipcode']); }
				else if($k==='address_city'){ $add = $qm->replace('plg_qform.fmt_paren', $qm->m['plg_qform']['city']); }
				else if($k==='address_street'){ $add = $qm->replace('plg_qform.fmt_paren', $qm->m['plg_qform']['street']); }
				else{ $add = ''; }
			
				$error .= $qm->replace('plg_qform.err_invalid_ptn', $el['label']. $add). '<br />';
			}
		}
		else{
		
			//添付ファイル対応
			if( $el['type'] === 'attach' ){
				$size = $el['size']*1000*1000;
				$id = $el['id'];
				$name = $_FILES['qform']['name'][$id];

				if($name != ''){ //何かアップロードしているなら

					if( $_FILES['qform']['size'][$id] > $size ){ //ファイルサイズチェック
						$error .= $qm->replace('plg_qform.err_oversize', $el['label'].'('.$name.')'). '<br />';
					}
					else if( $_FILES['qform']['error'][$id] ){ //その他のエラー
						$error .= $qm->replace('plg_qform.err_upload', $el['label'].$_FILES['qform']['error'][$id]). '<br />';
					}
					else { //移動
						$tmpname = tempnam(CACHEQHM_DIR,'qform_');
						
						if( move_uploaded_file($_FILES['qform']['tmp_name'][$id], $tmpname ) ){
	
							$_SESSION['qform'][$id] = $name;
							$_SESSION['qform']['_FILES'][$id] = array('name'=>$name, 'path'=>$tmpname);
						
						}
						else{
							$error .= $qm->replace('plg_qform.err_upload', $el['label'].$_FILES['qform']['error'][$id]). '<br />';
						}
					}
				}
			}
			
		}
	}
	
	return $error;
}

/**
* 確認画面を作り出す
*/
function plugin_qform_confirm(& $params, $style, $title)
{
	global $vars, $script;
	$qm = get_qm();

	$els = $params['element'];

	$lclass = 'class="qform_label"';
	$pclass = 'class="qform_form_p"';	

	$data_set = array();
	foreach($els as $k=>$v)
	{
		
		$label = $v['label'];
		
		if( is_array( $_SESSION['qform'][$k] ) ){
			$value = '';
			foreach( $_SESSION['qform'][$k] as $vv ){
				$value .= $vv . "\n";
			}
		}
		else{
			$value = $_SESSION['qform'][$k];
		}
		
		$value = nl2br( htmlspecialchars($value) );
		if($value === '')
			$value = '--';
		
		//名前フィールドだけ特別扱い
		if($k=='lname'){
			$value = $_SESSION['qform']['lname'].' '.$_SESSION['qform']['fname'];
		}
		//名前（カナ）フィールドだけ特別扱い
		if($k=='lname_kana'){
			$value = $_SESSION['qform']['lname_kana'].' '.$_SESSION['qform']['fname_kana'];
		}
		//住所だけ、特別扱い
		else if($k==='address_zip'){
			$k = 'dummy_address'; //下で無視するロジックを回避
			$value = $_SESSION['qform']['address_zip'].'<br />'
					.$_SESSION['qform']['address_state'].'<br />'
					.$_SESSION['qform']['address_city'].'<br />'
					.$_SESSION['qform']['address_street'];
		}
		
		//無視
		if($k=='fname' || $k == 'fname_kana' || preg_match('/^address_/', $k)){
			continue;
		}

		//添付ファイル
		if( $v['type']=='attach' ){
			$path = $_SESSION['qform']['_FILES'][$k]['path'];
			if($path != ''){
				$ref_url = $script.'?plugin=qform&id='.rawurldecode($v['id']);
				
				if(preg_match('/\.(gif|png|jpe?g)$/i', $value)){
					$reconfirm = '<img src="'.$ref_url.'" style="width:100px;" />';
				}
				else{
					$reconfirm = $qm->m['plg_qform']['reconfirm'];
				}
			
				$value = '<a href="'.$ref_url.'" target="new">'.$value.' '.$reconfirm.'</a>';
			}
		}

		//button設定
		else if ($k == 'buttons') {
			$qm->m['plg_qform']['btn_back'] = $v['back'];
			$qm->m['plg_qform']['btn_submit'] = $v['submit'];
			continue;
		}

		$data_set[] = array(
			'label' => 	$label,
			'content' => $value,
			'exp' => ''
		);
	}

	$keitai = '';
	if (UA_PROFILE=='keitai') {
		$keitai = '<input type="hidden" name="mobssid" value="yes" />
<input type="hidden" name="'.session_name().'" value="'.session_id().'" />
';
	}
		
	$body = '<h2>'.$qm->replace('plg_qform.title_confirm', $title).'</h2>'; 
	$body .= plugin_qform_format($data_set, $style);
	
	$body .= '
<form method="post" action="'.$script.'">
<p style="text-align:center;"><input type="submit" name="qform[qform_back]" value="'.$qm->m['plg_qform']['btn_back'].'" /> <input type="submit" name="qform[qform_finish]" value="'.$qm->m['plg_qform']['btn_submit'].'" />
<input type="hidden" name="cmd" value="read" />
<input type="hidden" name="page" value="'.$vars['page'].'" />
<input type="hidden" name="qform_condition" value="hogehoge" />
'.$keitai.'</p>
</form>
';
	return $body;
	
}

function plugin_qform_do_finish($params)
{
	global $vars, $script, $google_apps, $google_apps_domain, $pass;
	$qm = get_qm();

	$page = $vars['page'];
	
	$conf = $params['conf'];
	
	$els = $params['element'];
	$search = array();
	$udata = array();

	//mk data
	$all = '';	
	foreach($els as $id=>$v)
	{
		$tmp = isset($_SESSION['qform'][$id]) ? $_SESSION['qform'][$id] : '';

		//住所だけ、特別扱い
		if($id==='address_zip'){
			$tmp = '';
			foreach (array('address_zip', 'address_state', 'address_city', 'address_street') as $addname) {
				$udata[$addname] = (isset($_SESSION['qform'][$addname])) ? $_SESSION['qform'][$addname] : '';
				$search[$addname] = '<%'.$addname.'%>';
				$tmp .= $_SESSION['qform'][$addname]."\n";
			}
			$tmp = substr($tmp, 0, -1);
			$id = 'address';
		}
		else if(preg_match('/^address_/', $id)){
			continue;
		}

		$udata[$id]  = is_array($tmp) ? implode(", ", $tmp) : $tmp;
		$search[$id] = '<%'.$id.'%>';
		
		//lname, fnameのとき用
		if($id == 'lname'){
			$all .= $v['label'].'  :  ';
			$all .= $udata['lname'].' '.$_SESSION['qform']['fname']."\n";
		}
		//lname_kana, fname_kanaのとき用
		if($id == 'lname_kana'){
			$all .= $v['label'].'  :  ';
			$all .= $udata['lname_kana'].' '.$_SESSION['qform']['fname_kana']."\n";
		}
		if($id != 'fname' && $id != 'lname' && $id != 'fname_kana' && $id != 'lname_kana'){
			$all .= $v['label'].'  :  ';
			//addressの時だけ、ラベルの後に改行を入れる
			if($id == 'address') {
				$all .= "\n";
			}
			$all .= $udata[$id]."\n";
		}
	}
	
	$search['all_post_data'] = '<%all_post_data%>';
	$udata['all_post_data']  = $all;
	$search['form_url'] = '<%form_url%>';
	$udata['form_url']  = $script.'?'.rawurlencode($page);
	
	//mail送信
	$smail = new SimpleMail();

	// --------------------------------
	// Auto Reply Mail (finish mail)
	// --------------------------------
	if( isset($conf['finish_mail']) )
	{
		$subject = str_replace($search, $udata, $conf['finish_mail']['subject']);
		$mailbody = str_replace($search, $udata, $conf['finish_mail']['body']);

		//Google Appsを使って、更に自分宛の場合
		if( $google_apps && preg_match('/.*@'.$google_apps_domain.'$/', $udata['email']) ){
			
			$mail = new Qdmail();
			$mail-> smtp(true);
	
			$param = array(
				'host'=>'ASPMX.L.GOOGLE.com',
				'port'=> 25, 
				'from'=>$conf['finish_mail']['from']['email'],
				'protocol'=>'SMTP',
				'user'=>'root@'.$google_apps_domain, //SMTPサーバーのユーザーID
				'pass' =>$passwd, //SMTPサーバーの認証パスワード
			);
			$mail-> smtpServer($param);
			
			$mail->to($udata['email']);
			$mail->subject($subject);
			$mail->from($conf['finish_mail']['from']['email']);
			$mail->text($mailbody);
			
			$retval = $mail->send();
		}
		else{ // 通常の送信
	
			$smail->set_params($conf['finish_mail']['from']['name'], $conf['finish_mail']['from']['email']);
			$smail->subject = $subject;
			$smail->to = array('name'=>'', 'email'=>$udata['email']);
		
			$smail->send($mailbody);
		}
	}
	
	// -------------------------------
	// notice mail
	// -------------------------------
	if( isset($conf['notice_mail']) )
	{

		$subject = str_replace( $search, $udata, $conf['notice_mail']['subject'] );
		$mailbody = str_replace( $search, $udata, $conf['notice_mail']['body'] );

// commu		if (isset($_SESSION['commu_user'])) {
// commu			$domain = ini_get('session.cookie_domain');
// commu			$path = ini_get('session.cookie_path');
// commu			$url  = (SERVER_PORT == 443) ? 'https://' : 'http://';
// commu			$url .= $domain;
// commu			$url .= (SERVER_PORT == 80) ? '' : ':'.SERVER_PORT;
// commu			$url .= $path.'commu/admin_user_view.php?cid='.$_SESSION['commu_user']['id'];
// commu			$mailbody .= "\n\n----------------------------\n";
// commu			$mailbody .= "ユーザーの詳細\n".$url."\n";
// commu		}

		$to_name = $conf['notice_mail']['to']['name'];
		$to_email = $conf['notice_mail']['to']['email'];

		//Google Appsを使って、更に自分宛の場合
		if( $google_apps && preg_match('/.*@'.$google_apps_domain.'$/', $to_email) ){

			$mail = new Qdmail();
			$mail-> smtp(true);
	
			$param = array(
				'host'=>'ASPMX.L.GOOGLE.com',
				'port'=> 25, 
				'from'=>$udata['email'],
				'protocol'=>'SMTP',
				'user'=>'root@'.$google_apps_domain, //SMTPサーバーのユーザーID
				'pass' =>$passwd, //SMTPサーバーの認証パスワード
			);
			$mail-> smtpServer($param);
			
			$mail->to($to_email);
			$mail->subject($subject);
			$mail->from($udata['email']);
			$mail->text($mailbody);

			//添付ファイル
			foreach($_SESSION['qform']['_FILES'] as $f){
				if( file_exists($f['path']) ){
					$mail->attach( array($f['path'], $f['name']) , true );
				}
			}			
			
			$retval = $mail->send();
		}
		else{
			$name = isset( $udata['lname']) ? $udata['lname'] : '';
			$name .= isset( $udata['fname'] ) ? $udata['fname'] : '';
			
			$smail->set_params($name, $udata['email']);
			$smail->subject = $subject;
			$smail->to = array('name'=>$to_name, 'email'=>$to_email);

			//添付ファイル
			foreach($_SESSION['qform']['_FILES'] as $f){
				if( file_exists($f['path']) ){
					$smail->add_attaches($f['name'], $f['path']);
				}
			}
			$smail->send($mailbody);
		}
	}
	
	// -------------------------------
	// session destroy & tmpfile unlink
	// -------------------------------
	plugin_qform_unlink_attaches();

	$_SESSION['qform'] = null;
	unset($_SESSION['qform']);

	// -------------------------------
	// post
	// -------------------------------
	if( isset($conf['finish_post']) ){
		$dat = $conf['finish_post']['data'];
		$to_enc = $conf['finish_post']['encode'];
		$url = $conf['finish_post']['url'];
		
		foreach($dat as $key=>$val)
		{
			$val = str_replace($search, $udata, $val);
			$dat[$key] = mb_convert_encoding($val, $to_enc, 'UTF-8');
		}
		$pdata = http_build_query($dat, "", "&");
		$res = do_post_request($url, $pdata);
		//echo $res;
	}
	
	
	// --------------------------------
	// redirect
	// --------------------------------
	if( isset($conf['finish_url']) && is_url($conf['finish_url']) ){
		header('Location: '.$conf['finish_url']);
		exit;
	}
	
	
	// ---------------------------------
	// 完了ページの表示
	// ---------------------------------
	if( isset($conf['finish_msg']) ){
		$body = str_replace($search, $udata, $conf['finish_msg']);
		force_output_message($qm->m['plg_qform']['title_finished'], $page, convert_html($body));
	}
	else{
		force_output_message($qm->m['plg_qform']['title_finished'], $page, $qm->m['plg_qform']['finished']);
	}
}

/**
* ログを保存する
*/
function plugin_qform_save_log($params, $page, $title)
{
	$qm = get_qm();
	$write_page = ':config/plugin/qform/'.$page;

	$els = $params['element'];
	//送信日時をセット
	$arr1 = array(
		$qm->m['plg_qform']['datetime']
	);
	$arr2 = array(
		date("Y-m-d H:i:s")
	);
	foreach($els as $k=>$v){
		$arr1[] = $v['label'].'('.$k.')';
		$d = is_array($_SESSION['qform'][$k]) ?
			implode(',',$_SESSION['qform'][$k]) : $_SESSION['qform'][$k];
		
		$arr2[] = str_replace("\n", '<br>',  str_replace("\r",'', $d) );
	}
			
	if(is_page($write_page)){
		$lines = get_source($write_page);
		
		$str = '';
		foreach($lines as $l){	
			if(trim($l)==='}}'){ // }}の直前に、データを書き入れる
				$str .= '"'.implode('","', $arr2).'"'."\n";
			}
				
			$str .= $l;
		}
		
		page_write($write_page, $str);
		
	}
	else{
		//make header
		$str = '#close'."\n";
		$str .= $qm->replace("plg_qform.title_form_log", $page)."\n\n";		
		$str .= '* '.$title."\n\n";
		$str .= '#qform_view(){{'."\n";
		$str .= '"'.implode('","', $arr1).'"'."\n";
		$str .= '"'.implode('","', $arr2).'"'."\n";
		$str .= '}}';
		
		//新規保存
		page_write($write_page, $str);
		
	}
}

/**
* 不要な添付ファイルを削除する
*/
function plugin_qform_unlink_attaches(){

	//現在、セッションにセットされているファイル
	foreach($_SESSION['qform']['_FILES'] as $f){
		if( file_exists($f['path']) ){
			unlink($f['path']);
		}
	}
	
	$limit = time()-PLUGIN_QFORM_UNLINK_TIME;
	chdir(CACHEQHM_DIR);
	foreach( glob('qform_*') as $file ){
		if( filemtime($file) < $limit ){
			unlink($file);
		}
	}
	
	chdir('..');
}

function do_post_request($url, $data, $optional_headers = null)
{
	if(function_exists('stream_get_contents')){
		$params = array('http' => array(
							'method' => 'POST',
							'content' => $data
							)
					);
	
		if ($optional_headers !== null) {
			$params['http']['header'] = $optional_headers;
		}
		$ctx = stream_context_create($params);
		
		$fp = @fopen($url, 'rb', false, $ctx);
		
		if (!$fp) {
//			echo "Problem with $url, $php_errormsg";
		}
		
		$response = @stream_get_contents($fp);
//		echo '<br />';
		if ($response === false) {
//			echo "Problem reading data from $url, $php_errormsg";
		}
		return $response;
	}

	elseif(!function_exists('stream_get_contents')){
	
		$url_parse = parse_url($url);
		$port = "80";

		if ($fp = fsockopen($url_parse['host'], $port)) {
			fputs ($fp, "POST ".$url_parse['path']." HTTP/1.1\r\n");
			fputs ($fp, "User-Agent:PHP/".phpversion()."\r\n");
			fputs ($fp, "Host: ".$_SERVER["HTTP_HOST"]."\r\n");
			fputs ($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
			fputs ($fp, "Content-Length: ".strlen($data)."\r\n\r\n");
			fputs ($fp, $data);
			while (!feof($fp)) {
				$response .= fgets($fp,4096);
			}
			fclose($fp);
		}
		return $response;
	}
}

if (!function_exists('http_build_query')) {
function http_build_query($data, $prefix='', $sep='', $key='')
{
    $ret = array();
    foreach ((array)$data as $k => $v) {
        if (is_int($k) && $prefix != null) $k = urlencode($prefix . $k);
        if (!empty($key)) $k = $key.'['.urlencode($k).']';
        
        if (is_array($v) || is_object($v))
            array_push($ret, http_build_query($v, '', $sep, $k));
        else    array_push($ret, $k.'='.urlencode($v));
    }

    if (empty($sep)) $sep = ini_get('arg_separator.output');
    return implode($sep, $ret);
}
}

?>