<?php
/**
 *   Ensmall Auth Class
 *   -------------------------------------------
 *   lib/ensmall_auth.php
 *   
 *   Copyright (c) 2012 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 
 *   modified :
 *   
 */

define('ENSMALL_STATUS_SUCCESS', 101);
define('ENSMALL_STATUS_ERROR', 200);
define('ENSMALL_STATUS_ERROR_NO_USER', 201);
define('ENSMALL_STATUS_ERROR_NO_PRODUCT', 202);
define('ENSMALL_STATUS_ERROR_INSTALL_OVER', 203);


class EnsmallAuth {

	/* Product Codename */
	var $codename = '';
	
	/* 送信データの格納 */
	var $query_data = array();

	/* レスポンスデータ */
	var $res;
	
	/* passcode */
	private $passcode = FALSE;
	
	/* バージョン情報 */
	var $version;
	var $revision;
	
	/* インストールURL */
	var $install_url;

	/* インストール回数 */
	var $install_count;

	/* インストール最大回数 */
	var $install_limit;

	/* Ensmall Club 情報 */
	var $user_id;
	var $product_id;

	// ! need change by product ----- 
	var $isOpenQHM = FALSE;
	var $isQHMLite = FALSE;
	var $isACCafe = FALSE;
	// ------------------------------

	/* tryinfo */
	var $tryinfo = array();
	
	/* エラーメッセージ */
	var $errmsg ='';

	/* エラーメッセージ */
	var $errstatus;

	/** getProps() で取得できない非公開プロパティ */
	var $priProps = array(
		'res', 'errmsg'
	);

	/** プロキシー */
	var $use_proxy = FALSE;
	var $proxy_host;
	var $proxy_port;
	var $no_proxy;

	
	/* サイト情報 */
	var $username;
	var $password;
	
	/* メール設定 */
	var $language = 'Japanese';
	var $encoding = 'UTF-8';
	var $from = array('name'=>"北摂情報学研究所", 'email'=>"customer@hokuken.com");
	var $return_path = '';
	var $reply_to = '';
	var $x_mailer = '';
	var $to = array('name'=>'', 'email'=>'');

	/**
	* コンストラクタ
	*
	* @param string email
	* @param string password
	* @param string product_id
	* @return 
	*/
	function EnsmallAuth($url='', $codename='')
	{	
		if (strlen($codename) > 0)
		{
			$this->codename = $codename;
		}
		$this->add_query_data('codename', $this->codename);
		if ($url != '')
		{
			$this->install_url = $url;
		}
	}
	
	// ----------------------------------------------------------------------------
	
	/**
	* Ensmall Clubへ認証
	* 
	* @param string ensmall club url
	* @return integer error code
	*/
	function auth($email, $password)
	{
		$this->add_query_data('email', $email);
		$this->add_query_data('password', sha1(md5($password)));
		$this->to['email'] = $email;

/*
		$req_data = array(
			'method'=>'POST',
			'post'=>$this->query_data,
		);
*/
		if ($this->use_proxy)
		{
			$req_data['proxy_host'] = $this->proxy_host;
		}

		$url = PLUGIN_QHMSETTING_CLUB_URL . 'users/chk_ensmall_auth/';
//		$res = http_req($url, $req_data);
		$res = http_request($url, 'POST', '', $this->query_data);
		if ($data = $this->_search_header($res['header'],'X-ENSMALL-RETURN'))
		{
			$ret = unserialize(base64_decode($data));
			if ($ret['passcode'] !== FALSE) {
				$this->_set_passcode($ret['passcode']);
				$this->install_count     = $ret['install_count'];
				$this->install_limit     = $ret['install_limit'];
				$this->user_id           = $ret['user_id'];
				$this->product_id        = $ret['product_id'];
				$this->tryinfo           = $ret['tryinfo'];
			}

			$this->errmsg = $ret['message'];
			return $this->errstatus = $ret['status'];
		}
		return FALSE;
	}

	// ----------------------------------------------------------------------------
	
	/**
	* 送信データの追加
	* 
	* @param string key
	* @param string value
	* @return
	*/
	function add_query_data($key, $val)
	{
		$this->query_data[$key] = $val;
	}

	// ----------------------------------------------------------------------------
	
	/**
	* データの取得
	* 
	* @param string url
	* @param array query
	* @return mixed response
	*/
	function get_data($url, $query)
	{
		if (count($this->passcode) != 2)
		{
			return FALSE;
		}
		
		$query['code1'] = $this->passcode[0];
		$query['code2'] = $this->passcode[1];
		
		$res = http_request($url, 'POST', '', $query);
		return trim($res['data']);
	}
	
	// ----------------------------------------------------------------------------
	
	/**
	* インストーラーのバージョンチェック
	* 
	* @return boolean TRUE or FALSE(need change)
	*/
	function check_installer()
	{
		$url = PLUGIN_QHMSETTING_UPDATER_URL.'get_installer_version.php';
		$param = array(
		);

		// get upload list
		$res = $this->get_data($url, $param);
		if ($res === FALSE)
		{
			return TRUE;
		}
		
		$data = unserialize($res);
		if ($data !== FALSE)
		{
			if (INSTALLER_VERSION < $data['ver'])
			{
				return FALSE;
			}
		}
		
		return TRUE;
	}

	// ----------------------------------------------------------------------------
	
	/**
	* 更新するファイルリストを取得
	* 
	* @param string mode
	* @param string addon codename
	* @return string tempfilename or FALSE
	*/
	function get_updateFileList($mode, $addonname="")
	{
		$mode = ($addonname == "") ? $mode : 'addon';
		$list_url = PLUGIN_QHMSETTING_UPDATER_URL.'/get_file_list.php';
		$param = array(
			'mode' => $mode,
			'rev'  => $this->revision,
			'addonname'=>$addonname,
		);

		// get upload list
		$res = $this->get_data($list_url, $param);
		if ($res === FALSE)
		{
			return FALSE;
		}
		
		$uplist = unserialize($res);
		return $uplist;
	}
	
	// ----------------------------------------------------------------------------
	
	/**
	* 指定したファイルをダウンロード
	* 
	* @param string url
	* @param string file
	* @return string tempfilename or FALSE
	*/
	function download($file)
	{
		$url = PLUGIN_QHMSETTING_UPDATER_URL.'dl.php';
		$query = array('file' => $file);
		$query['limit'] = $this->getTryLimit();
		$res = $this->get_data($url, $query);
		if ($res === ENSMALL_STATUS_ERROR_INSTALL_OVER)
		{
			$this->errmsg = 'インストール回数が超過しています。';
			return $res;
		}
		else if ($res === ENSMALL_STATUS_ERROR)
		{
			$this->errmsg = 'ファイルをダウンロードできませんでした [file:'.$file.']';
			return FALSE;
		}
		else if (strlen($res) > 0)
		{
			$tmpfile = tempnam("tmp", "hknEns-");
			if  (file_put_contents($tmpfile, $res) !== FALSE)
			{
				return $tmpfile;
			}
		}

		return FALSE;
	}

	// ----------------------------------------------------------------------------
	
	/**
	* パスコードのセット
	* 
	* @param mixed passcode
	* @return
	*/
	function _set_passcode($val)
	{
		$this->passcode = $val;
	}

	// ----------------------------------------------------------------------------
	
	/**
	* プロキシーホストのセット
	* 
	* @param string proxy
	* @return
	*/
	function set_proxy($host, $port)
	{
		if ( ! in_the_net($this->no_proxy, $host))
		{
			if ($host !== '') {
				$this->use_proxy = TRUE;
				$this->proxy_host = $host;
				$this->proxy_port = $port;
			}
		}
	}
	
	// ----------------------------------------------------------------------------
	
	/**
	* Ensmall Club に認証できているか
	* 
	* @param mixed passcode
	* @return
	*/
	function is_connect()
	{
		if ( ! isset($_SESSION['ensmall_info']))
		{
			return FALSE;
		}
		if ($this->passcode !== FALSE)
		{
			return TRUE;
		}
		
		return FALSE;
	}

	// ----------------------------------------------------------------------------
	
	/**
	* ヘッダー情報の取得
	* 
	* @param string header
	* @param string 検索文字列
	* @return ヘッダー情報
	*/
	function _search_header($header, $needle='')
	{
		if ( ! (strlen($header) > 0 && strlen($needle) > 0))
		{
			return FALSE;
		} 
		$hlist = explode("\r\n", $header);
		foreach ($hlist as $h)
		{
			$t = explode(':', $h);
			if (strtolower(trim($t[0])) == strtolower($needle))
			{
				if (($pos = strpos($h, '|')) !== FALSE)
				{
					return substr($h, ($pos+1));
				}
			}
		}
		return FALSE;
	}

	// ----------------------------------------------------------------------------
	
	/**
	* プロパティを取得する
	* 
	* @param
	* @return array this class properties
	*/
	function getProps()
	{
		$priprs = $this->priProps;

		$vars = get_object_vars($this);
		$retvars = array();
		foreach ($vars as $key => $var) {
			if (!in_array($key, $priprs)) {
				$retvars[$key] = $var;
			}
		}
		return $retvars;
	}

	// ----------------------------------------------------------------------------
	
	/**
	* セッションにデータを保存する
	* 
	* @param 
	* @return
	*/
	function saveProps()
	{
		$_SESSION['ensmall_info'] = $this->getProps();
	}	

	// ----------------------------------------------------------------------------
	
	/**
	* プロパティをセッションから読込む
	* 
	* @param 
	* @return
	*/
	function readProps()
	{
		if ( ! isset($_SESSION) || ! isset($_SESSION['ensmall_info']) )
		{
			return FALSE;
		}
		
		$this->setProps($_SESSION['ensmall_info']);
	
	}

	// ----------------------------------------------------------------------------
	
	/**
	* プロパティをセットする
	* 
	* @param array property
	* @return
	*/
	function setProps($props)
	{
		foreach ($props as $key => $prop)
		{
			if ( ! in_array($key, $this->priProps) && property_exists(__CLASS__, $key))
			{
				$this->$key = $prop;
			}
		}
	}


	// ----------------------------------------------------------------------------
	
	/**
	 *   ログの出力
	 *
	 *   @param string $status install or update or restore or uninstall
	 *   @param string $server サーバー情報
	 *   @param string $pid Product ID
	 *
	 *   @return boolean 成功
	 */
	function install_log($status, $server='', $pid='')
	{
		$url = PLUGIN_QHMSETTING_CLUB_URL.'product_installs/install_log/__UID__/__PID__/__SERVER__/__STATUS__/d41da6ce50bdf4e458514e8d4a195e63/';
		$search = array('__UID__', '__PID__', '__SERVER__', '__STATUS__');
		$server = str_replace('/', '\\', $server); //cakephpによって、%2Fが、/ と認識されてうまく動作しないから
		$pid = ($pid == '') ? $this->product_id : $pid;

		$rep = array($this->user_id, $pid, rawurlencode($server), rawurlencode($status));
		$url = str_replace($search, $rep, $url);

		$res = http_request($url, 'POST', '', array('url'=>$this->install_url));
		return TRUE;
	}
	
	
	// ----------------------------------------------------------------------------
	
	/**
	 *   体験版かどうか
	 *
 	 * @param 
	 * @return boolean TRUE:体験版
	 */
	function isTry()
	{
		return (isset($this->tryinfo['isTry'])) ? $this->tryinfo['isTry'] : TRUE;
	}
	
	// ----------------------------------------------------------------------------
	
	/**
	 *   体験版の有効期限の設定
	 *
 	 * @param integer timestamp
	 * @return
	 */
	function setTryLimit($t)
	{
		$this->tryinfo['sys_expire'] = $t;
	}
	
	// ----------------------------------------------------------------------------
	
	/**
	 *   体験版の有効期限の取得
	 *
 	 * @param
	 * @return integer timestamp
	 */
	function getTryLimit()
	{
		if (isset($this->tryinfo['sys_expire']))
		{
			return $this->tryinfo['sys_expire'];
		}
		return '';
	}
	
	
	// ----------------------------------------------------------------------------
	
	/**
	 *   商品のチェック
	 *
 	 * @param
	 * @return boolean
	 */
	function check_product()
	{
		// ! need change by prodcut
		
		// QHMモバイルの場合は、インストール、アップデートをしない
		if (file_exists('./QHMMOBILE.txt'))
		{
			$this->errmsg = 'QHMモバイルはアップデートできません。';
			return FALSE;
		}


		// バージョンチェック
		$this->_checkVersion();

		// 体験版の場合：更新日時をチェックする
		if ($this->isTry())
		{
			$fpath = './lib/qhm_init.php';
			if (file_exists($fpath))
			{
				$qhminit = file_get_contents($fpath);
				//timestamp を取り出す
				if (preg_match('/\$timestamp\s=\s(\d+)?;/', $qhminit, $ms))
				{
					$this->setTryLimit(trim($ms[1]));
				}
			}
		}
		else {
			$hasFwd3 = FALSE;
			$hasSwfu = FALSE;
			
			// swfuチェック				
			if (file_exists('./swfu/config.php'))
			{
				$hasSwfu = TRUE;
			}

			// fwd3チェック
			if (file_exists('./fwd3/sys/config.php'))
			{
				$hasFwd3 = TRUE;
			}
			
			// Open QHMチェック：default.ini.php に other_plugin を持たない
			$fpath = './default.ini.php';
			if (file_exists($fpath))
			{
				$defini = file_get_contents($fpath);
				//$other_plugins の中身があるかどうか調べる
				if (preg_match('/\$other_plugins\s=\sarray\(\);/', $defini)) {
					$this->isOpenQHM = TRUE;
				}
			}
		
			if ($hasSwfu && ! $hasFwd3)
				// QHMLite チェック：other_plugins はもたない かつ swfu はあるけど、fwd3 がない
				if ($this->version >= 4.5 && $this->isOpenQHM) {
				{
					$this->isQHMLite = TRUE;
				}
				// ACCafe チェック
				if (file_exists('./ACCAFE.txt'))
				{
					$this->isACCafe = TRUE;
				}
			}
		}

		$this->saveProps();

		return TRUE;
	}

	// ----------------------------------------------------------------------------
	
	/**
	 *   商品のバージョンチェック
	 *   ! need change by product
	 *
 	 * @param
	 * @return
	 */
	function _checkVersion()
	{
		$ini = './lib/init.php';
		if (file_exists($ini)) {
			$inistr = file_get_contents($ini);
			if( preg_match("/QHM_VERSION', '(.*?)'/", $inistr, $ms))
			{
				$this->version = floatval($ms[1]);
				$this->revision = preg_match("/QHM_REVISION', '(.*)'/", $inistr, $ms2)? intval($ms2[1]): 0;
			} 
			else
			{
				$this->errmsg = 'バージョン情報が取得できません:1';
			}
		}
	}
}
