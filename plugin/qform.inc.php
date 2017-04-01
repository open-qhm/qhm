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

if( file_exists('lib/qdmail.php') ){
	require_once('lib/qdmail.php');
}
if( file_exists('lib/qdsmtp.php') ){
	require_once('lib/qdsmtp.php');
}

function plugin_qform_parse_csv($line, $delimiter=','){

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
function plugin_qform_convert()
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

	$title = isset($args[0]) ? $args[0] : $qm->m['plg_qform']['title_none'];

	// $style は default, horizontal, vertical の 3 種類
	// bootstrap テンプレートでは default は horizontal のエイリアスとなる

	$style = isset($args[1]) && trim($args[1]) ? trim($args[1]) : 'horizontal'; # default, horizontal, vertical

	$hor_aliases = array('bootstrap', 'table');
	if (is_bootstrap_skin()) $hor_aliases[] = 'default';
	if (in_array($style, $hor_aliases))
	{
		$style = 'horizontal';
	}
	if ( ! in_array($style, array('default', 'horizontal', 'vertical')))
	{
		$style = 'vertical';
	}

	$save  = isset($args[2]) ? $args[2] : 'false';
	//フィッシング対策
	$url_sanitize = isset($args[3]) ? $args[3] : '0';

	//set design
	plugin_qform_set_css();

	$params = plugin_qform_parse($pstr);

	if (isset($vars['qform_condition']))
	{

		//
		// send mail & ...
		//
		if (isset($vars['qform']['qform_finish']))
		{
			if ( ! isset($_SESSION['qform']))
			{
				header("Location: ".$script.'?'.urlencode($page) );
				exit;
			}

			//転送する、postする、完了を出すなどなど
			if ($save === 'true')
			{
				plugin_qform_save_log($params, $page, $title);
			}
			return plugin_qform_do_finish($params, $url_sanitize);
		}


		//
		// 戻る
		//
		if (isset($vars['qform']['qform_back']))
		{
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
		if ($vars['qform_condition']==='confirm')
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


			if ($error==='')
			{
				$body = plugin_qform_confirm($params, $style, $title);
				force_output_message($qm->m['plg_qform']['title_confirm2'], $page, $body);
			}
			else
			{
				$_SESSION['qform']['enable_session_check'] = time();  //セッション有効チェック用

				force_output_message(
					$qm->replace('plg_qform.title_confirm', $title),
					$page,
					'<h2>'.$title.'</h2><p class="' .
						(is_bootstrap_skin() ? 'text-danger' : 'qform-danger') .
						'">'.$error.'</p>' . plugin_qform_mkform($params, $style)
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

	exit;
}

/**
 * 与えられた文字列から、フォームのためのパラメータを解析
 */
function plugin_qform_parse($str)
{
	global $vars, $script;
	$qm = get_qm();

	$lines = preg_split("/\n|\r/", $str);

	$ret = array('element'=>array(), 'conf'=>array());

	$multi = false;

	$end = count($lines);
	for( $index=0; $index<$end; $index++ ){

		$line = $lines[$index];

		if (preg_match('/^(.+?)=(.*)/', $line, $ms))
		{
			$cmd    = $ms[1];
			$params = $ms[2];
			$arr = plugin_qform_parse_csv($params);
		}
		else
		{
			$cmd = '';
			$arr = null;
		}

		switch ($cmd)
		{
			// form要素
			case 'text' :  //テキスト入力ボックス
				list($id, $label, $default, $exp, $validation, $size) = array_pad($arr, 6, '');
				$el = array(
					'type'       => 'text',
					'id'         => $id,
					'label'      => $label,
					'default'    => $default,
					'exp'        => $exp,
					'validation' => $validation,
					'size'       => $size
				);

				$ret['element'][$id] = $el;
				break;

			case 'check' :
			case 'radio' :
				// Determine display type inline or block
				$inline = true;
				foreach ($arr as $i => $v)
				{
					if ($v === 'show:block')
					{
						$inline = false;
						break;
					}
				}
				if ( ! $inline) array_splice($arr, $i, 1);

				list($id, $label, $default, $exp) = array_pad($arr, 4, '');

				$ret['element'][$id] = array(
					'type'       => $cmd,
					'id'         => $id,
					'label'      => $label,
					'default'    => $default,
					'exp'        => $exp,
					'validation' => '',
					'inline'     => $inline,
					'option'     => array_slice($arr, 4)
				);
				break;

			case 'attach' :
				list($id, $label, $exp, $validation, $size) = array_pad($arr, 5, '');
				$size = is_numeric($size) ? $size : QFORM_DEFAULT_ATTACHE_SIZE;

				$el = array(
					'type'       => 'attach',
					'id'         => $id,
					'label'      => $label,
					'exp'        => $exp,
					'validation' => $validation,
					'size'       => $size
				);

				$ret['element'][$id] = $el;
				break;

			case 'select' :
				list($id, $label, $default, $exp) = array_pad($arr, 4, '');
				$el = array(
					'type'    => $cmd,
					'id'      => $id,
					'label'   => $label,
					'default' => $default,
					'exp'     => $exp,
					'option'  => array_slice($arr, 4)
				);

				$ret['element'][$id] = $el;
				break;

			case 'memo' :
				list($id, $label, $default, $exp, $validation, $rows) = array_pad($arr, 6, '');

				$rows = $rows ? $rows : 10;
				$el = array(
					'type'       => 'memo',
					'id'         => $id,
					'label'      => $label,
					'default'    => $default,
					'exp'        => $exp,
					'validation' => $validation,
					'rows'       => $rows
				);

				$ret['element'][$id] = $el;
				break;

			case 'address' :
				list($label, $exp, $validation, $defaultstate) = array_pad($arr, 4, '');

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
					'type'       => 'address_zip',
					'id'         => 'address_zip',
					'label'      => $label,
					'exp'        => $exp,
					'validation' => $vv['zip'],
				);
				$ret['element']['address_zip'] = $el;

				$el = array(
					'type'       => 'address',
					'id'         => 'address_state',
					'label'      => $label,
					'exp'        => $exp,
					'validation' => $vv['state'],
					'default'    => $defaultstate,
				);
				$ret['element']['address_state'] = $el;

				$el = array(
					'type'       => 'address',
					'id'         => 'address_city',
					'label'      => $label,
					'exp'        => $exp,
					'validation' => $vv['city'],
				);
				$ret['element']['address_city'] = $el;

				$el = array(
					'type'       => 'address',
					'id'         => 'address_street',
					'label'      => $label,
					'exp'        => $exp,
					'validation' => $vv['street'],
				);
				$ret['element']['address_street'] = $el;

				break;

			case 'name' :
				list($label, $exp, $validation) = array_pad($arr, 3, '' );

				if ($validation=='lname')
				{
					$vl = 'need';
					$vf = '';
				}
				else if ($validation=='fname')
				{
					$vl = '';
					$vf = 'need';
				}
				else
				{
					$vl = 'need';
					$vf = 'need';
				}

				$ret['element']['lname'] = array(
					'type'       => 'lname',
					'id'         => 'lname',
					'label'      => $label,
					'exp'        => $exp,
					'validation' => $vl,
				);

				$ret['element']['fname'] = array(
					'type'  => 'fname',
					'id'    => 'fname',
					'label' => $label,
					'exp'   => $exp,
					'validation' => $vf,
				);
				break;

			case 'name_kana' :
				list($label, $exp, $validation) = array_pad($arr, 3, '');

				if ($validation == 'lname_kana')
				{
					$vl = 'need';
					$vf = '';
				}
				else if ($validation=='fname_kana')
				{
					$vl = '';
					$vf = 'need';
				}
				else if ($validation=='need')
				{
					$vl = 'need';
					$vf = 'need';
				}
				else
				{
					$vl = '';
					$vf = '';
				}

				$ret['element']['lname_kana'] = array(
					'type'  => 'lname_kana',
					'id'    => 'lname_kana',
					'label' => $label,
					'exp'   => $exp,
					'validation' => $vl,
				);

				$ret['element']['fname_kana'] = array(
					'type'  => 'fname_kana',
					'id'    => 'fname_kana',
					'label' => $label,
					'exp'   => $exp,
					'validation' => $vf,
				);
				break;

			case 'email' :
				$email_match = '/^([a-z0-9_]|\-|\.|\+)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,6}$/i';
				list($label, $exp, $confirm, $email2_msg) = array_pad($arr, 2, '');

				//メッセージ書き換え
				$qm->m['plg_qform']['msg_email2'] = trim($email2_msg) ? $email2_msg: $qm->m['plg_qform']['msg_email2'];

				$id = 'email';

				$ret['element'][$id] = array(
					'type'       => 'email',
					'id'         => $id,
					'label'      => $label,
					'exp'        => $exp,
					'validation' => $email_match,
					'confirm'    => $confirm,
				);
				break;

			case 'state' :
				list($label, $exp, $default) = array_pad($arr, 3, '');
				$ret['element']['state'] = array(
					'type'       => 'state',
					'id'         => $id,
					'label'      => $label,
					'exp'        => $exp,
					'default'    => $default,
					'validation' => '',
				);
				break;

			case 'contract':
				list($label, $cblabel, $pagename) = array_pad($arr, 3, '');
				$ret['element']['contract'] = array(
					'type'       => 'contract',
					'id'         => 'contract',
					'label'      => $label,
					'cblabel'    => $cblabel,
					'pagename'   => $pagename,
					'validation' => 'need',
				);
				break;

			case 'buttons':
				// color: , options: を許容する
				$options = 'btn';
				$options_arr = array('primary');
				$customized_options = false;
				foreach ($arr as $i => $v)
				{
					if (preg_match('/^(?:color|options):([\w\s]+)$/', $v, $mts))
					{
						$customized_options = true;
						$options_arr = preg_split('/\s+/', trim($mts[1]));
						break;
					}
				}
				foreach ($options_arr as $option)
				{
					$options .= ' btn-' . $option;
				}

				// index を振りなおす
				if ($customized_options) array_splice($arr, $i, 1);

				list($confirm, $back, $submit) = array_pad($arr, 3, '');
				$ret['element']['buttons'] = array(
					'type'    => 'buttons',
					'confirm' => trim($confirm)? $confirm: $qm->m['plg_qform']['btn_confirm'],
					'back'    => trim($back)? $back: $qm->m['plg_qform']['btn_back'],
					'submit'  => trim($submit)? $submit: $qm->m['plg_qform']['btn_submit'],
					'options' => $options
				);
				break;

			case 'messages':
				list($confirm, $finished, $email2) = array_pad($arr, 1, '');
				//confirm とconfirm2 を同じもので上書きする
				$qm->m['plg_qform']['title_confirm']  = trim($confirm)  ? $confirm  : $qm->m['plg_qform']['title_confirm'];
				$qm->m['plg_qform']['title_confirm2'] = trim($confirm)  ? $confirm  : $qm->m['plg_qform']['title_confirm2'];
				$qm->m['plg_qform']['title_finished'] = trim($finished) ? $finished : $qm->m['plg_qform']['title_finished'];
				break;

			case 'errors':
				list($invalid_ptn, $invalid_email2, $no_check, $disable_session) = array_pad($arr, 3, '');
				$qm->m['plg_qform']['err_invalid_ptn']    = trim($invalid_ptn)     ? $invalid_ptn     : $qm->m['plg_qform']['err_invalid_ptn'];
				$qm->m['plg_qform']['err_invalid_email2'] = trim($invalid_email2)  ? $invalid_email2  : $qm->m['plg_qform']['err_invalid_email2'];
				$qm->m['plg_qform']['err_no_check']       = trim($no_check)        ? $no_check        : $qm->m['plg_qform']['err_no_check'];
				$qm->m['plg_qform']['err_enable_session'] = trim($disable_session) ? $disable_session : $qm->m['plg_qform']['err_enable_session'];
				break;

			case 'attach_errors':
				list($oversize, $failed, $invalid_ptn) = array_pad($arr, 3, '');
				$qm->m['plg_qform']['err_oversize']         = trim($oversize)    ? $oversize    : $qm->m['plg_qform']['err_oversize'];
				$qm->m['plg_qform']['err_upload']           = trim($failed)      ? $failed      : $qm->m['plg_qform']['err_upload'];
				$qm->m['plg_qform']['err_invalid_ptn_file'] = trim($invalid_ptn) ? $invalid_ptn : $qm->m['plg_qform']['err_invalid_ptn_file'];
				break;

			// param phase
			case 'finish_url' :
				$url = '';
				if (is_page($arr[0]))
				{
					$url = $script . '?' . rawurlencode($arr[0]);
				}
				else if (is_url($arr[0]))
				{
					$url = $arr[0];
				}
				$ret['conf']['finish_url'] = $url;

				break;

			case 'finish_post' :
				list($url, $encode) = array_pad($arr, 2, '');

				$data = array();
				for($i=2; $i<count($arr); $i+=2){
					$data[ $arr[$i] ] = $arr[$i+1];
				}
				$ret['conf']['finish_post'] = array(
					'url'    => $url,
					'encode' => $encode,
					'data'   => $data
				);

				break;

			// 完了メッセージ
			case 'finish_msg' :
				$body = '';
				for( $index++ ; $index<$end; $index++){
					$l = $lines[$index];
					if (trim($l) == "''") break;
					$body .= $l."\n";
				}

				$ret['conf']['finish_msg'] = $body;
				break;

			// 返信メール
			case 'finish_mail' :
				$body    = '';
				$subject = null;
				$from    = null;

				for ($index++ ; $index<$end; $index++)
				{
					$l = $lines[$index];

					if (trim($l) == "''") break;

					if (preg_match('/^::From::(.*?),(.*)$/', $l, $ms))
					{
						$from = array('name' => $ms[1], 'email' => $ms[2]);
					}
					else if (preg_match('/^::Subject::(.*)$/', $l, $ms))
					{
						$subject = $ms[1];
					}
					else
					{
						$body .= $l."\n";
					}
				}

				$ret['conf']['finish_mail'] = array(
					'subject' => $subject,
					'from'    => $from,
					'body'    => $body
				);

				break;

			// 返信メール
			case 'notice_mail' :
				$body    = '';
				$subject = null;
				$from    = null;
				$to      = null;

				for ($index++ ; $index<$end; $index++)
				{
					$l = $lines[$index];

					if (trim($l)=="''") break;

					if (preg_match('/^::Subject::(.*)$/', $l, $ms))
					{
						$subject = $ms[1];
					}
					else if (preg_match('/^::To::(.*?)$/', $l, $ms))
					{
						$to = array('name'=>'', 'email'=>$ms[1]);
					}
					else
					{
						$body .= $l."\n";
					}
				}

				$ret['conf']['notice_mail'] = array(
					'subject' => $subject,
					'from'    => $from,
					'to'      => $to,
					'body'    => $body
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

	return '<p class="'.(is_bootstrap_skin() ? 'text-danger' : 'qform-danger').'">'.$error.'</p>';

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
	$button_options = 'btn btn-primary';

	foreach ($els as $key => $el)
	{
		$template_path = dirname(__FILE__) . '/qform/' . $el['type'] . '.html';

		switch($el['type']){
			// !input[type=text]
			case 'text' :
				$str = isset($posted[ $el['id'] ]) ? $posted[ $el['id'] ] : $el['default'];
				$valid = $el['validation']=='' ? false : true;
				break;

			// !input[type=checkbox]
			case 'check' :
				$checked_values = isset($posted[$el['id']]) ? $posted[$el['id']] : array($el['default']);
				$valid = $el['validation']=='' ? false : true;
				break;

			// !select
			case 'select' :
				$selected_value = isset($posted[$el['id']]) ? $posted[$el['id']] : $el['default'];
				$valid = false;
				break;

			// !input[type=radio]
			case 'radio' :
				$checked_value = isset($posted[$el['id']]) ? $posted[$el['id']] : $el['default'];
				$valid = false;
				break;

			// !textarea(memo)
			case 'memo' :
				if (isset($posted[$el['id']]))
				{
					$memo_value = $posted[$el['id']];
				}
				else
				{
					$memo_value = str_replace('&br;', "\n", $el['default']);
				}
				$valid = $el['validation']=='' ? false : true;
				break;

			// !input[name='qform[email]']
			case 'email' :
				$str = isset($posted['email']) ? $posted['email'] : '';
				$valid = $el['validation']=='' ? false : true;
				break;

			// !input[type='qform[name']
			case 'lname' :
				$fname_el = $els['fname'];
				$lname = isset($posted['lname']) ? $posted['lname'] : '';
				$fname = isset($posted['fname']) ? $posted['fname'] : '';
				$valid = !($el['validation'] == '' && $fname_el['validation'] == '');
				break;

			// !input[type='qform[name_kana]']
			case 'lname_kana' :
				$fname_kana_el = $els['fname_kana'];
				$lname_kana = isset($posted['lname_kana']) ? $posted['lname_kana'] : '';
				$fname_kana = isset($posted['fname_kana']) ? $posted['fname_kana'] : '';
				$valid = !($el['validation'] == '' && $fname_kana_el['validation'] == '');
				break;

			// !address
			case 'address_zip' :
				$zip_el    = $els['address_zip'];
				$city_el   = $els['address_city'];
				$street_el = $els['address_street'];

				$states         = plugin_qform_parse_csv($qm->m['plg_qform']['states']);
				$selected_state = isset($posted['address_state']) ? $posted['address_state'] :
					( ($els['address_state']['default'] != '') ? $els['address_state']['default'] : '東京都' );

				$arr = array();
				foreach(array('zip', 'city', 'street') as $n)
				{
					$arr[$n] = isset($posted['address_'.$n]) ? $posted['address_'.$n] : '';
				}

				$arr['zip']    = $arr['zip'];
				$arr['city']   = $arr['city'];
				$arr['street'] = $arr['street'];

				$valid = $el['validation']=='' ? false : true;
				break;

			// !states
			case 'state' :
				$states         = plugin_qform_parse_csv($qm->m['plg_qform']['states']);
				$selected_state = isset($posted['state']) ? $posted['state'] :
					( ($els['state']['default'] != '') ? $els['state']['default'] : '東京都' );

				$valid = $el['validation']=='' ? false : true;
				break;

			// !contract
			case 'contract' :
				// Err: self include
				if ($vars['page'] == $el['pagename'])
				{
					$err = $qm->m['plg_qform']['err_looppage'];
				}
				// Err: page not found
				else if ( ! is_page($el['pagename']))
				{
					$err = $qm->m['fmt_err_notfoundpage_title'];
				}
				else
				{
					$contract = convert_html(get_source($el['pagename']));
				}
				$cblabel = strlen($el['cblabel'])? $el['cblabel'] : $qm->m['plg_qform']['lbl_agree'];

				$valid = true;
				break;

			// !添付ファイル
			case 'attach' :
				$enctype = ' enctype="multipart/form-data" ';
				$valid   = $el['validation']=='' ? false : true;
				break;

			// !buttons setting
			case 'buttons':
				$qm->m['plg_qform']['btn_confirm'] = $el['confirm'];
				$qm->m['plg_qform']['btn_back']    = $el['back'];
				$qm->m['plg_qform']['btn_submit']  = $el['submit'];

				$button_options = $el['options'];
				$content = '';
			break;

			default:
				$content = '';
		}

		if (file_exists($template_path))
		{
			ob_start();
			include $template_path;
			$content = ob_get_clean();
		}

		if ($content !== '')
		{
			$data_set[] = array('label' => $el['label'], 'content' => $content, 'exp' => $el['exp'], 'valid' => $valid);
		}
	}

	$form_class = "qform_form clearfix qform-style-{$style}";

	if ($style == 'horizontal')
	{
		$form_class .= ' form-horizontal';
	}
	if (is_bootstrap_skin())
	{
		$form_class .= ' qform-on-bootstrap';
	}
	else
	{
		$form_class .= ' qform-on-default';
	}

	$form_content = plugin_qform_format($data_set, $style);

	$template_path = dirname(__FILE__) . '/qform/qform.html';
	ob_start();
	include $template_path;
	$form = ob_get_clean();

	return $form;

}

function plugin_qform_format($data_set, $style)
{
	$astar = '<span class="'.(is_bootstrap_skin() ? 'text-danger' : 'qform-danger').'">*</span>';
	if ($style=='default')
	{
		$str = '<table class="style_table" colspan="0" style="width:90%">';
		foreach($data_set as $d){
			$astr = isset($d['valid']) && $d['valid'] ? $astar : '';
			$str .= '<tr><th class="style_th">'.$d['label'].$astr.'</th><td class="style_td">'.$d['content'].'<br /><span style="font-size:small">'.$d['exp'].'</span></td></tr>';
		}
		$str .= '</table>';
	}
	else if ($style === 'horizontal')
	{
		$template_path = dirname(__FILE__) . '/qform/horizontal.html';
		ob_start();
		include $template_path;
		$str .= ob_get_clean();
	}
	else
	{
		$template_path = dirname(__FILE__) . '/qform/vertical.html';
		ob_start();
		include $template_path;
		$str .= ob_get_clean();
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
				$chk_ptrn = '/^[0-9a-zA-Z]+$/s';
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
				$error .= $qm->m['plg_qform']['err_invalid_email2']. '<br />';
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
				else if($k==='lname_kana'){ $add = $qm->replace('plg_qform.fmt_paren', '姓'); }// TODO
				else if($k==='fname_kana'){ $add = $qm->replace('plg_qform.fmt_paren', '名'); }// TODO
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

function plugin_qform_sanitize_url($str, $sanitize_level)
{
	//リンク、ドメインから始まるURIを全角にする
	if ($sanitize_level === '1' OR $sanitize_level === 'true')
	{
		$ptns = array(
			'/(?:https?|ftp)(?::\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/e',
			'/((?:[a-zA-Z0-9-]+\.)+[a-zA-Z0-9]{2,3})([^a-zA-Z0-9])/e',
		);
		$rpls = array(
			'mb_convert_kana("$0", "A")',
			'mb_convert_kana("$1", "A")."$2"',
		);
		$str = preg_replace($ptns, $rpls, $str);
	}

	return $str;
}

/**
* 確認画面を作り出す
*/
function plugin_qform_confirm(& $params, $style, $title)
{
	global $vars, $script;
	$qm = get_qm();

	$els = $params['element'];
	$button_options = isset($els['buttons']['options'])
		? $els['buttons']['options']
		: 'btn btn-primary';

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

		$value = nl2br( h($value) );
		if($value === '')
			$value = '--';

		//名前フィールドだけ特別扱い
		if($k=='lname'){
			$value = h($_SESSION['qform']['lname'].' '.$_SESSION['qform']['fname']);
		}
		//名前（カナ）フィールドだけ特別扱い
		if($k=='lname_kana'){
			$value = h($_SESSION['qform']['lname_kana'].' '.$_SESSION['qform']['fname_kana']);
		}
		//住所だけ、特別扱い
		else if($k==='address_zip'){
			$k = 'dummy_address'; //下で無視するロジックを回避
			$value = h($_SESSION['qform']['address_zip']).'<br />'
					.h($_SESSION['qform']['address_state']).'<br />'
					.h($_SESSION['qform']['address_city']).'<br />'
					.h($_SESSION['qform']['address_street']);
		}

		//無視
		if($k=='fname' || $k == 'fname_kana' || preg_match('/^address_/', $k)){
			continue;
		}

		if($v['type'] == 'contract'){
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
<form method="post" action="'.h($script . '?' . rawurlencode($vars['page'])).'">
<div style="text-align:center;" class="form-group"><input type="submit" name="qform[qform_back]" value="'.$qm->m['plg_qform']['btn_back'].'" class="btn btn-link" /> <input type="submit" name="qform[qform_finish]" value="'.$qm->m['plg_qform']['btn_submit'].'" class="'. h($button_options) .'" />
<input type="hidden" name="cmd" value="read" />
<input type="hidden" name="page" value="'.h($vars['page']).'" />
<input type="hidden" name="qform_condition" value="hogehoge" />
'.$keitai.'</div>
</form>
';
	return $body;

}

function plugin_qform_do_finish($params, $url_sanitize = '0')
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
	$excludes = array_flip(array(
		'email',
	));

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

		$data = is_array($tmp) ? implode(", ", $tmp) : $tmp;
		$udata[$id]  = array_key_exists($id, $excludes) ? $data : plugin_qform_sanitize_url($data, $url_sanitize);
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
			$mail->smtp(true);

			$param = array(
				'host'=>'ASPMX.L.GOOGLE.com',
				'port'=> 25,
				'from'=>$conf['finish_mail']['from']['email'],
				'protocol'=>'SMTP',
				'user'=>'root@'.$google_apps_domain, //SMTPサーバーのユーザーID
				'pass' =>$passwd, //SMTPサーバーの認証パスワード
			);
			$mail->smtpServer($param);

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
			$mail->smtp(true);

			$param = array(
				'host'=>'ASPMX.L.GOOGLE.com',
				'port'=> 25,
				'from'=>$udata['email'],
				'protocol'=>'SMTP',
				'user'=>'root@'.$google_apps_domain, //SMTPサーバーのユーザーID
				'pass' =>$passwd, //SMTPサーバーの認証パスワード
			);
			$mail->smtpServer($param);

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

function plugin_qform_set_css()
{
	$qt = get_qt();
	$addstyle= "<style>\n" . file_get_contents(dirname(__FILE__) . '/qform/qform.css') . "</style>\n";
	$qt->appendv_once('plugin_qform', 'lastscript', $addstyle);
}
