<?php
/**
* ORMcrypt class
*
* Blowfishを使って暗号化するためのクラス。簡単に使える。
* 使い方は以下の通り。
*
* 
<code>

//暗号キーを使ってインスタンスを生成
$mc = new ORMcrypt('hogehoge');

//暗号化すると、配列で暗号データが戻る（両方とも復号に必要）
// $arr[0]は、本文の暗号化データ。$arr[1]は、復号に必要な初期ブロック値
$arr = $mc->encrypt('こんにちは！こんにちは！世界の国から〜♪');
var_dump($arr);

//複合化する。先のセットのデータを渡す
$data = $mc->decrypt($arr[0], $arr[1]);
var_dump($data);

//パスワードのストレッチングをするためのクラスメソッド
var_dump( sha1('passwd'), ORMcrypt::stretching('passwd') );

</code>

*
* @package ORIGAMI
*
*
*/
class ORMcrypt{
	
	// get_key を使って適宜変更
	private $key = 'IK8l6xXw1fYMEHGKjNFC64NoAexzJlkzQJUX4Y';
	
	//この値を変えると、ivデータの暗号化のiv文字列を変えられ、セキュリティーを高められますが、変えなくてもOK
	private static $iviv_seed = '9ijhtr4';
	
	private $_mcrypt_exists;
	
	private $cipher;
	private $mode;
	private $iv_size;
	private $dummy_iv;

	// --------------------------------------------------------------------
	
	/**
	* 暗号、復号を行うオブジェクトを生成
	*
	* @param string 暗号用キー
	*/
	function __construct($key = '')
	{
		if (strlen($key) > 0)
		{
			$this->key = $key;
		}

		$this->_mcrypt_exists = ( ! function_exists('mcrypt_encrypt')) ? FALSE : TRUE;
		
		if( $this->_mcrypt_exists )
		{
			$this->cipher   = MCRYPT_BLOWFISH;
			$this->mode     = MCRYPT_MODE_CBC;
			$this->iv_size  = mcrypt_get_iv_size($this->cipher, $this->mode);
			$this->dummy_iv = str_pad('', $this->iv_size, self::$iviv_seed);
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	* キーのセット
	*
	* @param string 暗号用キー
	*/
	function set_key($key)
	{
		if (strlen($key) > 0)
		{
			$this->key = $key;
		}
	}

	// --------------------------------------------------------------------

	/**
	* データを暗号化し、base64化した暗号データを返す。返される値は、配列になっており、復号には両方必要。
	* 一つ目が$data、二つ目が復号に必要な初期ブロック値の暗号データ。
	* 
	* @param string
	* @return array
	*/
	function encrypt($data)
	{
		if( $this->_mcrypt_exists )
		{
			$iv_size = $this->iv_size;
			
			srand(); //windows ready
			$iv      = mcrypt_create_iv($iv_size, MCRYPT_DEV_RANDOM);
			$crypt_msg = mcrypt_encrypt($this->cipher, $this->key, base64_encode($data),  $this->mode, $iv);
			$crypt_iv  = mcrypt_encrypt($this->cipher, $this->key, base64_encode($iv),  $this->mode, $this->dummy_iv);
			return array( base64_encode($crypt_msg), base64_encode($crypt_iv) );
		}
		else // XOR Encrypt
		{
			return array( base64_encode($this->_xor_encode($data, $this->key)), '');
		}
	}

	// --------------------------------------------------------------------
	
	/**
	* データを複合化する
	*
	* @param string
	* @param string
	* @return string
	*/
	function decrypt($crypt_data, $crypt_iv)
	{
		if( $this->_mcrypt_exists )
		{
			$iv =  $this->_mdecrypt( base64_decode($crypt_iv), $this->dummy_iv);
			$data = $this->_mdecrypt( base64_decode($crypt_data), $iv);
			
			return $data;
		}
		else // XOR Decrypt
		{
			return $this->_xor_decode( base64_decode($crypt_data), $this->key);
		}
	}

	// --------------------------------------------------------------------
	
	function _mdecrypt($data, $iv)
	{
		return base64_decode(
			rtrim( mcrypt_decrypt($this->cipher, $this->key, $data, $this->mode, $iv), "\0" )
		);	
	}
	
	// --------------------------------------------------------------------

	/**
	 * XOR Encode
	 *
	 * Takes a plain-text string and key as input and generates an
	 * encoded bit-string using XOR
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _xor_encode($string, $key)
	{
		$rand = '';
		while (strlen($rand) < 32)
		{
			$rand .= mt_rand(0, mt_getrandmax());
		}

		$rand = sha1($rand);

		$enc = '';
		for ($i = 0; $i < strlen($string); $i++)
		{
			$enc .= substr($rand, ($i % strlen($rand)), 1).(substr($rand, ($i % strlen($rand)), 1) ^ substr($string, $i, 1));
		}

		return $this->_xor_merge($enc, $key);
	}

	// --------------------------------------------------------------------

	/**
	 * XOR Decode
	 *
	 * Takes an encoded string and key as input and generates the
	 * plain-text original message
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _xor_decode($string, $key)
	{
		$string = $this->_xor_merge($string, $key);

		$dec = '';
		for ($i = 0; $i < strlen($string); $i++)
		{
			$dec .= (substr($string, $i++, 1) ^ substr($string, $i, 1));
		}

		return $dec;
	}

	// --------------------------------------------------------------------

	/**
	 * XOR key + string Combiner
	 *
	 * Takes a string and key as input and computes the difference using XOR
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _xor_merge($string, $key)
	{
		$hash = sha1($key);
		$str = '';
		for ($i = 0; $i < strlen($string); $i++)
		{
			$str .= substr($string, $i, 1) ^ substr($hash, ($i % strlen($hash)), 1);
		}

		return $str;
	}

	// --------------------------------------------------------------------
	
	/**
	* パスワードをストレッチングするためのお助け関数
	*/
	static public function stretching($password, $sec_salt = null)
	{
		if($sec_salt == null)
		{
			$seed = self::$iviv_seed;
			$sec_salt = str_pad($seed, 64,  $seed);
		}
		
		$hash = '';
		for($i=0; $i<1000; $i++){
			$hash = sha1($hash.$password.$sec_salt);
		}
		
		return $hash;
	}

	// --------------------------------------------------------------------
	
	/**
	* ランダムなキーを作成するためのお助け関数
	*/
	static public function get_key($num = 40)
	{
		$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$max = strlen($str);

		$key = '';
		for ($i=0; $i < $num; $i++)
		{
			$key .= substr( $str, mt_rand(0, $max) , 1);
		}
		
		return $key;
	}
}

?>