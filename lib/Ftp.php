<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		HKNTool
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 *
 *
 *
 *  hokuken.com customizing history
 *  ---------------------------------
 *	
 *  version 1.1  2010 2/3
 *
 *  is_existsメソッドの追加。ファイルのありなしを返す。0なら、存在しない。1ならファイル、それ以上ならディレクトリ。
 *  list_filesメソッドの戻り値（ファイル群のリスト）を、絶対パス付きに変更（ ftp_pwd()の結果を付ける ）
 *    また、list_filesの引数に 絶対パス指定の場合は、そのまま出力するように変更。
 *
 */
 
// ------------------------------------------------------------------------

/**
 * FTP Class
 *
 * @package		HKNTool
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/ftp.html
 */
class CI_FTP {

	var $hostname	= '';
	var $username	= '';
	var $password	= '';
	var $port		= 21;
	var $passive	= TRUE;
	var $debug		= FALSE;
	var $conn_id	= FALSE;
	var $all_binary = TRUE;
	
	var $debug_msg  = array();

	/**
	 * Constructor - Sets Preferences
	 *
	 * The constructor can be passed an array of config values
	 */
	function __construct($config = array())
	{
		if (count($config) > 0)
		{
			$this->initialize($config);
		}

//		log_message('debug', "FTP Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize preferences
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function initialize($config = array())
	{
		foreach ($config as $key => $val)
		{
			if (property_exists($this, $key))
			{
				$this->$key = $val;
			}
		}
		// Prep the hostname
		$this->hostname = preg_replace('|.+?://|', '', $this->hostname);
	}

	// --------------------------------------------------------------------

	/**
	 * FTP Connect
	 *
	 * @access	public
	 * @param	array	 the connection values
	 * @return	bool
	 */
	function connect($config = array())
	{
		if (count($config) > 0)
		{
			$this->initialize($config);
		}

//		if (FALSE === ($this->conn_id = @ftp_connect($this->hostname, $this->port)))
		if (FALSE === ($this->conn_id = @ftp_ssl_connect($this->hostname, $this->port)))
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_unable_to_connect');
			}
			return FALSE;
		}

		if ( ! $this->_login())
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_unable_to_login');
			}
			return FALSE;
		}

		// Set passive mode if needed
		if ($this->passive == TRUE)
		{
			ftp_pasv($this->conn_id, TRUE);
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * FTP Login
	 *
	 * @access	private
	 * @return	bool
	 */
	function _login()
	{
		return @ftp_login($this->conn_id, $this->username, $this->password);
	}

	// --------------------------------------------------------------------

	/**
	 * Validates the connection ID
	 *
	 * @access	private
	 * @return	bool
	 */
	function _is_conn()
	{
		if ( ! is_resource($this->conn_id))
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_no_connection');
			}
			return FALSE;
		}
		return TRUE;
	}

	// --------------------------------------------------------------------


	/**
	 * Change direcotry
	 *
	 * The second parameter lets us momentarily turn off debugging so that
	 * this function can be used to test for the existance of a folder
	 * without throwing an error.  There's no FTP equivalent to is_dir()
	 * so we do it by trying to change to a particular directory.
	 * Internally, this paramter is only used by the "mirror" function below.
	 *
	 * @access	public
	 * @param	string
	 * @param	bool
	 * @return	bool
	 */
	function changedir($path = '', $supress_debug = FALSE)
	{
		if ($path == '' OR ! $this->_is_conn())
		{
			return FALSE;
		}

		$result = @ftp_chdir($this->conn_id, $path);

		if ($result === FALSE)
		{
			if ($this->debug == TRUE AND $supress_debug == FALSE)
			{
				$this->_error('ftp_unable_to_changedir');
			}
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Create a directory
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function mkdir($path = '', $permissions = NULL)
	{
		if ($path == '' OR ! $this->_is_conn())
		{
			return FALSE;
		}

		$result = @ftp_mkdir($this->conn_id, $path);

		if ($result === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_unable_to_makdir : '.$path);
			}
			return FALSE;
		}

		// Set file permissions if needed
		if ( ! is_null($permissions))
		{
			$this->chmod($path, (int)$permissions);
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Upload a file to the server
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function upload($locpath, $rempath, $mode = 'auto', $permissions = NULL)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		if ( ! file_exists($locpath))
		{
			$this->_error('ftp_no_source_file');
			return FALSE;
		}

		// Set the mode if not specified
		if ($mode == 'auto')
		{
			// Get the file extension so we can set the upload type
			$ext = $this->_getext($locpath);
			$mode = $this->_settype($ext);
		}

		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;

		$result = @ftp_put($this->conn_id, $rempath, $locpath, $mode);
		if ($result === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_unable_to_upload : '.$locpath);
			}
			return FALSE;
		}

		// Set file permissions if needed
		if ( ! is_null($permissions))
		{
			$this->chmod($rempath, (int)$permissions);
		}
		return TRUE;
	}
	
	function download($locpath, $rempath, $mode = 'auto')
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}
		
		// Set the mode if not specified
		if ($mode == 'auto')
		{
			// Get the file extension so we can set the upload type
			$ext = $this->_getext($locpath);
			$mode = $this->_settype($ext);
		}

		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;
		$result = @ftp_get($this->conn_id, $locpath, $rempath, $mode);
		if ($result === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_unable_to_download : '.$rempath.' --> '.$locpath);
			}
			return FALSE;
		}
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Rename (or move) a file
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @return	bool
	 */
	function rename($old_file, $new_file, $move = FALSE)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		$result = @ftp_rename($this->conn_id, $old_file, $new_file);

		if ($result === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$msg = ($move == FALSE) ? 'ftp_unable_to_rename' : 'ftp_unable_to_move';

				$this->_error($msg);
			}
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Move a file
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function move($old_file, $new_file)
	{
		return $this->rename($old_file, $new_file, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Rename (or move) a file
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function delete_file($filepath)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		$result = @ftp_delete($this->conn_id, $filepath);

		if ($result === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_unable_to_delete : '.$filepath);
			}
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a folder and recursively delete everything (including sub-folders)
	 * containted within it.
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function delete_dir($filepath)
	{

		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		// Add a trailing slash to the file path if needed
		$filepath = preg_replace("/(.+?)\/*$/", "\\1/",  $filepath);
		$list = $this->list_files($filepath);

		if ($list !== FALSE AND count($list) > 0)
		{
			foreach ($list as $item)
			{
				$bname = basename($item); //for servers likes lolipop
				// If we can't delete the item it's probaly a folder so
				// we'll recursively call delete_dir()	
				if ( !preg_match('/\.$/',$bname) )
				{
					$deleted = @ftp_delete($this->conn_id, $item);
					
					if(! $deleted)
					{
						$this->delete_dir($item);
					}
				}
			}
		}
		
		$result = @ftp_rmdir($this->conn_id, $filepath);

		if ($result === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_unable_to_delete');
			}
			return FALSE;
		}

		return TRUE;
	}


	// --------------------------------------------------------------------

	/**
	 * Set file permissions
	 *
	 * @access	public
	 * @param	string 	the file path
	 * @param	string	the permissions
	 * @return	bool
	 */
	function chmod($path, $perm)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		// Permissions can only be set when running PHP 5
		if ( ! function_exists('ftp_chmod'))
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_unable_to_chmod');
			}
			return FALSE;
		}

		$result = @ftp_chmod($this->conn_id, $perm, $path);

		if ($result === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_unable_to_chmod');
			}
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * FTP List files in the specified directory
	 * 
	 * Caution: nlist option '-a' for hidden files (.htaccess, .htpasswd)
	 *    and @ftp_chdir(connid, path) is for sakura server.
	 *    Because, sakura server returned not NULL but "No such file ..." if 
	 *    path is not exists.
	 * 
	 *  ftp_pwd()の結果を付属したリストを返す（つまり、絶対パスで返す）。
	 *  これは、サーバーによって ftp_nlistの出力がパス付きだったり、そうでなかったりするから。
	 * 
	 * @param string $path
	 * @param string $option ftp_nlist に渡すコマンドのパス
	 * @access	public
	 * @return	array
	 */
	function list_files($path = '.', $option = '-a ')
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		if( $path{0} != '/'){
			$pwd = ftp_pwd($this->conn_id).'/';
		}
		else{
			$pwd = '';
		}

		$path .= preg_match('/\/$/',$path) ? '' : '/';
		
		if( ftp_chdir($this->conn_id, $path) ){
			ftp_chdir($this->conn_id, $pwd) or user_error("Can't return parent director.");
			$list = ftp_nlist($this->conn_id, $option.$path);
		}
		else{
			$path = rtrim($path, '/');
			$list = ftp_nlist($this->conn_id, $option.$path);
		}

		foreach($list as $k=>$v){
			$fname = basename($v);
			$list[$k] = $pwd.$path.$fname;
		}
		return $list;
	}
	
	function is_dir($path)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		$pwd = ftp_pwd($this->conn_id);

		if( ftp_chdir($this->conn_id, $path) ){
			ftp_chdir($this->conn_id, $pwd) or user_error("Can't return parent director.");
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	
	function is_exists($path){
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}
		
		//新バージョン
		
		if( $this->is_dir($path) ){
			return true;
		}
		
		$list = $this->list_files($path, '');
		if (is_array($list) && count($list)) {
			return true;
		} else {
			return false;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Read a directory and recreate it remotely
	 *
	 * This function recursively reads a folder and everything it contains (including
	 * sub-folders) and creates a mirror via FTP based on it.  Whatever the directory structure
	 * of the original file path will be recreated on the server.
	 *
	 * @access	public
	 * @param	string	path to source with trailing slash
	 * @param	string	path to destination - include the base folder with trailing slash
	 * @return	bool
	 */
	function mirror($locpath, $rempath)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		// Open the local file path
		if ($fp = @opendir($locpath))
		{
			// Attempt to open the remote file path.
			if ( ! $this->changedir($rempath, TRUE))
			{
				// If it doesn't exist we'll attempt to create the direcotory
				if ( ! $this->mkdir($rempath) OR ! $this->changedir($rempath))
				{
					return FALSE;
				}
			}

			// Recursively read the local directory
			while (FALSE !== ($file = readdir($fp)))
			{
				if (@is_dir($locpath.$file) && substr($file, 0, 1) != '.')
				{
					$this->mirror($locpath.$file."/", $rempath.$file."/");
				}
				elseif ($file != "." && $file != "..")
				{
					// Get the file extension so we can se the upload type
					$ext = $this->_getext($file);
					$mode = $this->_settype($ext);

					$this->upload($locpath.$file, $rempath.$file, $mode);
				}
			}
			return TRUE;
		}

		return FALSE;
	}


	// --------------------------------------------------------------------

	/**
	 * Extract the file extension
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	function _getext($filename)
	{
		if (FALSE === strpos($filename, '.'))
		{
			return 'txt';
		}

		$x = explode('.', $filename);
		return end($x);
	}


	// --------------------------------------------------------------------

	/**
	 * Set the upload type
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	function _settype($ext)
	{

		if($this->all_binary){
			return 'binary';
		}

		$text_types = array(
							'txt',
							'text',
							'php',
							'phps',
							'php4',
							'js',
							'css',
							'htm',
							'html',
							'phtml',
							'shtml',
							'log',
							'xml',
							);


		return (in_array($ext, $text_types)) ? 'ascii' : 'binary';
	}

	// ------------------------------------------------------------------------

	/**
	 * Close the connection
	 *
	 * @access	public
	 * @param	string	path to source
	 * @param	string	path to destination
	 * @return	bool
	 */
	function close()
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		@ftp_close($this->conn_id);
	}

	// ------------------------------------------------------------------------

	/**
	 * Display error message
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 */
	function _error($line)
	{
		echo $line."\n";
	}
	
	function pwd()
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		return @ftp_pwd($this->conn_id);
	}

}
// END FTP Class

/* End of file Ftp.php */
/* Location: ./system/libraries/Ftp.php */
