<?php
/***
* メール送信を行うためのクラス
*
* 何故？　：　ありとあらゆる方法で、文字化けするので、便利な実装を行った
*
* 機能　：　特定の文字を置き換えて送信することができる
*　　　　　　添付ファイルができる（HTMLメールは無理）
*
* 使い方 ：
*   $sm = new SimpleMail();
*   $sm->set_params('送信者名', 'sender@hoge.com');  //Fromやらがセットされる
*   $sm->set_to('宛先名', 'toadr@example.com');
*   $body = 'hoghoge...';
*
*   $sm->add_attaches('data/profile.jpg');
*
*   $sm->send($body);
*
*/
class SimpleMail{

	/**
	* public
	*/
	var $language = '';
	var $encoding = 'UTF-8';
	var $from = array('name'=>"", 'email'=>"");
	var $return_path = '';
	var $reply_to = '';
	var $x_mailer = '';
	var $to = array('name'=>'', 'email'=>'');
	var $subject = '';
	var $host_path = '';
	var $attaches = array();
	var $boundary = '__QFORM_BOUNDARY__';
	
	// メール送信時に、エンコードした名前を付けないオプション
	var $exclude_to_name = false;
	var $is_qmail = false;

  // メール用エンコード
  var $mail_encode = 'ISO-2022-JP';


	var $searchkey = array (
		'<%id%>',
		'<%lastname%>',
		'<%firstname%>',
		'<%lastname_kana%>',
		'<%firstname_kana%>',
		
		'<%zipcode%>',
		'<%state%>',
		'<%city%>',
		'<%address1%>',
		'<%address2%>',
		
		'<%birthday%>',
		'<%email%>',
		'<%tel%>',
		'<%enc_lastname%>',
		'<%enc_firstname%>',
		
		'<%enc_email%>',
		'<%password%>',
		'<%cancel%>',
	);


	var $searchkey_name;

	function SimpleMail() {
	
		if( function_exists('get_qm') ){
			$qm = get_qm();
			
			$this->language = $qm->m['mb_language'];
			
			$skarr = array(
				$qm->m['simplemail']['sk_id'],           //ID
				$qm->m['simplemail']['sk_lastname'],     //姓
				$qm->m['simplemail']['sk_firstname'],    //名
				$qm->m['simplemail']['sk_ph_lastname'],  //よみ（姓）: phonetic lastname
				$qm->m['simplemail']['sk_ph_firstname'], //よみ（名）
				
				$qm->m['simplemail']['sk_zipcode'],      //郵便番号
				$qm->m['simplemail']['sk_state'],        //都道府県
				$qm->m['simplemail']['sk_city'],         //市町村
				$qm->m['simplemail']['sk_address1'],     //住所1
				$qm->m['simplemail']['sk_address2'],     //住所2
				
				$qm->m['simplemail']['sk_birthday'],     //生年月日
				$qm->m['simplemail']['sk_email'],        //メールアドレス
				$qm->m['simplemail']['sk_tel'],          //電話番号
				$qm->m['simplemail']['sk_enc_lastname'], //エンコード（姓）
				$qm->m['simplemail']['sk_enc_firstname'],//エンコード（名）
				
				$qm->m['simplemail']['sk_enc_email'],    //エンコード（メールアドレス）
				$qm->m['simplemail']['sk_password'],     //パスワード
				$qm->m['simplemail']['sk_cancel'],       //メール配信停止URL
			);
			$this->searchkey_name = $skarr;
		
		}
		else{
			$this->searchkey_name = array(
				'ID', '姓', '名', 'よみ(姓)', 'よみ(名)', 
				'郵便番号', '都道府県', '市町村', '住所1', '住所2', 
				'生年月日', 'メールアドレス', '電話番号', 'エンコード(姓)', 'エンコード(名)',
				'エンコード(メールアドレス)', 'パスワード', 'メール配信停止URL'
			);
		}
		
		$this->boundary .= time();
		
		global $exclude_to_name;
		if ($exclude_to_name)
		{
			$this->exclude_to_name = $exclude_to_name;
		}
		
		global $is_qmail;
		if (isset($is_qmail) && $is_qmail)
		{
			$this->is_qmail = $is_qmail;
		}

    global $mail_encode;
    if (isset($mail_encode) && $mail_encode)
    {
        $this->mail_encode = $mail_encode;
    }

	}
	
	function set_params($name, $email, $x_mailer='QHM Mail Sender'){
		$this->from = array(
			'name'=> $name,
			'email'=> $email
		);
		$this->return_path = $email;
		$this->reply_to = $email;
		$this->x_mailer = $x_mailer;
	}
	
	function set_from($name, $email, $x_mailer='QHM Mail Sender'){
		$this->set_params($name, $email, $x_mailer);
	}
	
	function set_to($name, $email){
		$this->to = array('name'=>$name, 'email'=>$email);
	}
	
	function set_subject($subject){
		$this->subject = $subject;
	}
	
	function add_attaches($fname, $path=''){
	
		if($path == ''){
			$path = $fname;
			$fname = basename($fname);
		}
	
		if(! file_exists($path) ){
			die($path . ' is not exists.');
			exit;
		}
		
		if(isset($this->attaches[$fname]) ){
			$this->attaches[$fname.'-1'] = $path;
		}
		else{
			$this->attaches[$fname] = $path;
		}
	}
		
	function send($body){

		//必ず、多言語設定をUTF-8と設定する
		mb_language($this->language);
		mb_internal_encoding($this->encoding);

		$mail_encode = $this->mail_encode;

		$from_name = $this->from['name'];
		$from_adr = $this->from['email'];
		$rpath = ($this->return_path=='') ? $from_adr : $this->return_path;
		$repto = ($this->reply_to=='') ? $from_adr : $this->reply_to;
		$xmailer = ($this->x_mailer=='') ? "PHP/" . phpversion() : $this->x_mailer;
		$to_name = $this->to['name'];
		$to_adr = $this->to['email'];
		
		$from_name = str_replace("\r", "", $from_name);
		$from_name = mb_convert_encoding($from_name, $mail_encode, $this->encoding);
		if ($this->is_qmail)
		{
			mb_internal_encoding($mail_encode);
			$from_name = mb_encode_mimeheader( $from_name, $mail_encode, 'B', "\n");
			mb_internal_encoding($this->encoding);
			$en_from = $from_name. " <$from_adr>";
		}
		else
		{
			$en_from = $this->mime($from_name, $mail_encode)." <$from_adr>";
		}

		$to_name = str_replace("\r", "", $to_name);
		$to_name = mb_convert_encoding($to_name, $mail_encode, $this->encoding);
		$en_to = $this->mime($to_name, $mail_encode). " <$to_adr>";
		if ($exclude_to_name OR $to_name == '')
		{
			$en_to = $to_adr;
		}
		else
		{
			if ($this->is_qmail)
			{
				mb_internal_encoding($mail_encode);
				$to_name = mb_encode_mimeheader( $to_name, $mail_encode, 'B', "\n");
				mb_internal_encoding($this->encoding);
				$en_to = $to_name. " <$to_adr>";
			}
		}

		$subject = ($this->subject == '') ? '件名なし' : $this->subject;
		$subject = str_replace("\r", "", $subject);
		$subject = mb_convert_encoding($subject, $mail_encode, $this->encoding);
		if ($this->is_qmail)
		{
			mb_internal_encoding($mail_encode);
			$subject = mb_encode_mimeheader($subject, $mail_encode, 'B', "\n");
			mb_internal_encoding($this->encoding);
		}
		else
		{
			$subject = $this->mime($subject, $mail_encode);
		}

		$body = str_replace("\r", "", $body);
		$body = mb_convert_kana($body, "KV");
		$body = mb_convert_encoding($body, $mail_encode, $this->encoding);

		//添付ファイルあり、なし
		$add_msg = '';
		if( count($this->attaches) ){
			$header_content_type = 'Content-Type: multipart/mixed;boundary="'.$this->boundary.'"';
			
			$cnt = 0;
      $encoding = ($mail_encode === 'ISO-2022-JP') ? '7bit' : '8bit';
			
			$body = '--'.$this->boundary.'
Content-Type: text/plain; charset='.$mail_encode.'
Content-Transfer-Encoding: '.$encoding.'

'.$body."\n";

			foreach($this->attaches as $fname=>$path){
				$body .= '--'.$this->boundary."\n";
				$body .= 'Content-Type: '.$this->get_mimetype($fname).'; name="'.$this->mime($fname).'"'."\n";
				$body .= 'Content-Transfer-Encoding: base64'."\n";
				$body .= 'Content-Disposition: attachment; filename="'.$this->mime($fname).'"'."\n";
				$body .= 'Content-ID: <'.$cnt.'>'."\n";
				$body .= "\n";
				$body .= chunk_split(base64_encode( file_get_contents($path) ));
				
				$cnt++;
			}
			$body .= '--'.$this->boundary."--\n";
		}
		else{
			$header_content_type = 'Content-Type: text/plain;charset='.$mail_encode;
		}

		$headers =  "MIME-Version: 1.0 \n".
					"From: {$en_from}\n".
					"Reply-To: {$repto}"."\n".
					"X-Mailer: {$xmailer}\n".
					$header_content_type."\n";

		
		$sendmail_params  = "-f $from_adr";

		// --------------------------------------
		// メール送信(safe_modeによる切り替え)
		if( ini_get('safe_mode') ){
			return mail($en_to, $subject, $body, $headers);
		}
		else{
			return mail($en_to, $subject, $body, $headers, $sendmail_params);
		}
	}
	
	function replace_send($search, $replace, $body){
		if( count($search) == count($replace) ){
			
			$newserach = array();
			$cnt = 0;
			foreach($search as $value){
				$newsearch[$cnt] = '/'.$value.'/';
				$cnt++;
			}
		
			$body = preg_replace($newsearch, $replace, $body);
			$this->subject = preg_replace($newsearch, $replace, $this->subject);
		}
		$this->send($body);
	}
	
	/**
	*
	* 文字の置換を行う
	*/
	function replace_demo($body, $color="red"){
		$replace = array();
		foreach($this->searchkey_name as $value){
			$replace[] = '<span style="color:'.$color.'">'.$value.'</span>';
		}
		
		$body = str_replace($this->searchkey, $replace, $body);
				
		return $body;
	}

	/*****************
	 * mksearch : メール差し込み機能（検索用データ作成）
	 * [IN]  $user : ユーザー情報配列 <%{field name}%>
	 * [OUT] 検索配列
	 */
	function mksearch($user) {
		$ret = array();
		foreach ($user as $key => $val) {
			if (!is_array($val)) {
				$ret[] = "<%$key%>";
			}
		}

		return $ret;
	}

	/*****************
	 * mkreplace : メール差し込み機能（置き換え用データ作成）
	 * [IN]  $user : ユーザー情報配列
	 * [OUT] 置き換え配列
	 */
	function mkreplace($user) {
		$ret = array();
		foreach ($user as $key => $val) {
			if (!is_array($val)) {
				//有効期限は、日付をセット（保存されているのがタイムスタンプのため）
				if ($key == 'expiration') {
					if ($val != '') {
						$val = date('Y-m-d', $val);
					}
				}
				$val = ($val == "") ? "" : $val;
				$ret[] = $val;
			}
		}

		return $ret;
	}

	/**
	 *   内部エンコードを変えて、mb_encode_mimeheader() をかける
	 *   長い差し出し人名などに対応（長すぎると消える）
	 */
	function mime($str = '', $mail_encode = 'ISO-2022-JP') {
		mb_internal_encoding($mail_encode);
		// 濁点付きの文字が2文字扱いにならないよう合成する
		if (class_exists('Normalizer')) {
			$str = Normalizer::normalize($str, Normalizer::FORM_C);
		}
		$str = mb_convert_encoding($str, $mail_encode, $this->encoding);
		$str = mb_encode_mimeheader($str, $mail_encode, 'B');
		mb_internal_encoding($this->encoding);

		return $str;
	}
	
	function get_mimetype($fname){
		$ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
		
		switch($ext){
		
			case 'txt' : return 'text/plain';
			case 'csv' : return 'text/csv';
			case 'html':
			case 'htm' : return 'text/html';

			//
			case 'pdf' : return 'application/pdf';
			case 'css' : return 'text/css';
			case 'js'  : return 'text/javascript';
			
			//image
			case 'jpg' :
			case 'jpeg': return 'image/jpeg';
			case 'png' : return 'image/png';
			case 'gif' : return 'image/gif';
			case 'bmp' : return 'image/bmp';
			
			//av
			case 'mp3' : return 'audio/mpeg';
			case 'm4a' : return 'audio/mp4';
			case 'wav' : return 'audio/x-wav';
			case 'mpg' :
			case 'mpeg': return 'video/mpeg';
			case 'wmv' : return 'video/x-ms-wmv';
			case 'swf' : return 'application/x-shockwave-flash';
			
			//archives
			case 'zip' : return 'application/zip';
			case 'lha' : 
			case 'lzh' : return 'application/x-lzh';
			case 'tar' :
			case 'tgz' :
			case 'gz'  : return 'application/x-tar';
			
			
			//office files
			case 'doc' :
			case 'dot' : return 'application/msword';
			case 'docx': return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
			case 'xls' : 
			case 'xlt' : 
			case 'xla' : return 'application/vnd.ms-excel';
			case 'xlsx': return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
			case 'ppt' : 
			case 'pot' : 
			case 'pps' :
			case 'ppa' : return 'application/vnd.ms-powerpoint';
			case 'pptx': return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
			
		}
		
		return 'application/octet-stream';
		
		
	}
}


?>