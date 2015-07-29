<?php
/**
 *   QHM File System Class
 *   -------------------------------------------
 *   ./lib/qhm_fs.php
 *   
 *   Copyright (c) 2012 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2012-05-23
 *   modified :
 *   
 *   FileSystem へアクセスするクラス。
 *   Local_FTPインスタンスを持つ場合はそちらに委譲する。
 */
class QHM_FS {

	var $ftp = NULL;
	
	var $server = 'localhost';
	var $errmsg = '';
	
	function __construct($config = array())
	{
		if (isset($config['ftp_config']))
		{
			if (isset($config['ftp_config']['connect']) && $config['ftp_config']['connect'])
			{
				unset($config['ftp_config']['connect']);
				$ftp_connect = TRUE;
			}
			$this->ftp = new Local_FTP($config['ftp_config']);
			if ($ftp_connect)
			{
				$this->ftp->connect($config['ftp_config']);
			}
		}
	}
	
	
	/**
	* ディレクトリ作成
	* 
	* @param array dirlist
	* @return string file stream
	*/
	function mkdirr($dirs)
	{
		if ( ! is_null($this->ftp))
		{
			return $this->ftp->mkdirr($dirs);
		}
		
		if (count($dirs) == 0)
		{
			return FALSE;
		}

		foreach ($dirs as $path)
		{
			@mkdir($path);
		}
		return TRUE;
	}

	// ----------------------------------------------------------------------------
	
	/**
	* ファイルの設置
	* 
	* @param string localpath
	* @param string Remotepath
	* @return boolean
	*/
	function put($localpath, $rempath)
	{
		if ( ! is_null($this->ftp))
		{
			$ret = $this->ftp->put($localpath, $rempath);
			return $ret;
		}
		if (file_exists($localpath))
		{
			if (copy($localpath, $rempath))
			{
				unlink($localpath);
				return TRUE;
			}
		}
		return FALSE;
	}
	
	// ----------------------------------------------------------------------------
	
	/**
	* ファイル権限変更
	* 
	* @param array filelist
	* @return boolean
	*/
	function chmodr($option = array())
	{
		if ( ! is_null($this->ftp))
		{
			return $this->ftp->chmodr($option);
		}


		if (count($option) == 0) {
			return FALSE;
		}

		$mods= array();
		foreach ($option as $file => $p) {
			if (!isset($mods[$p])) {
				$mods[$p] = array();
			}

			$filelist = $this->_createFileList($file);
			$mods[$p] = array_merge($mods[$p], $filelist);
		}
		//対象ファイルすべてchmodをする
		foreach ($mods as $p => $filelist) {
			foreach ($filelist as $filepath) {
				@chmod($filepath, $p);
			}
		}

		$pwd = getcwd();
		$pnum = fileperms($pwd);
		if ($pnum == 0777)
		{
			@chmod($pwd, 0755);
		}
		else if ($pnum == 0707)
		{
			@chmod($pwddir, 0705);
		}

		return TRUE;
	}
	
	// ----------------------------------------------------------------------------
	
	/**
	* chmodr の書式に従ってファイル一覧を作成して返す
	* 
	* @param array filelist
	* @return array filelist
	*/
	function _createFileList($file) {


		$filelist = array();
		// *／ が入っている場合: ディレクトリすべて
		if (($pos = strpos($file, '*/')) !== FALSE) {
			$prefix = substr($file, 0, $pos);
			$suffix = substr($file, $pos + 1);
			
			//ファイルリストを取得し、ディレクトリ名を入れる
			if (($ls = $this->ls($path)) !== FALSE)
			{
				foreach ($ls as $i => $filename) {
					//ディレクトリであれば格納
					if (is_dir($filename)) {
						$file = $prefix . basename($filename) . $suffix;
						$filelist = array_merge($filelist, $this->_createFileList($file));
					}
				}
			}
		}
		// *.ext が入っている場合: 該当ファイルすべて
		else if (preg_match('/^(.*)\*(\.[a-z0-9]+)$/i', $file, $ms)) {
			$path = $ms[1];
			$ext = $ms[2];
			
			//ファイルリストを取得し、拡張子をチェック
			if (($ls = $this->ls($path)) !== FALSE)
			{
				$extptn = "/\\". $ext. '$/';
				foreach ($ls as $i => $filename) {
					//該当ファイルであれば格納
					if (preg_match($extptn, $filename)) {
						$filelist[] = $filename;
					}
				}
			}
		}
		// * が入っている場合: ファイル全て
		else if (substr($file, -1, 1) === '*') {
			$path = dirname($file);
			//ファイルリストを取得し、すべて追加する
			if (($ls = $this->ls($path)) !== FALSE)
			{
				$filelist = array_merge($filelist, $ls);
			}
		}
		//ただのファイル名: そのまま配列に格納
		else {
			$filelist[] = $file;
		}
	
		return $filelist;
	}
	
	// ----------------------------------------------------------------------------
	
	/**
	* ファイルリストから"." と".." を削除した配列を返す
	* 
	* @param string path
	* @return array filelist
	*/
	function ls($path = '.')
	{
		if ( ! is_null($this->ftp))
		{
			return $this->ftp->ls($path);
		}

		if (strlen($path) == 0){
			return FALSE;
		}
		$path = rtrim($path, "/") . "/";
		if( ! is_dir($path))
		{
			return FALSE;
		}
		
		$retarr = array();
		if( $dh = opendir($path))
		{
			while (false !== ($file = readdir($dh)))
			{
			    // Skip '.' and '..'
			    if ($file == '.' || $file == '..')
			    {
			        continue;
			    }
			    $path = rtrim($path, '/') . '/' . $file;
			    if (is_dir($path))
			    {
			        $retarr[] = $this->ls($path);
			    }
			    else
			    {
			        $retarr[] = $path;
			    }
			}
			closedir($dh);
		}
		return $retarr;
	}

	// ----------------------------------------------------------------------------
	
	function pwd()
	{
		if ( ! is_null($this->ftp))
		{
			return $this->ftp->pwd();
		}

		return getcwd();
	}
	
	function changeidir()
	{
		if ( ! is_null($this->ftp))
		{
			return $this->ftp->changeidir();
		}

		@chdir(dirname(__FILE__));
	}
	
	function serverToString()
	{
		if ( ! is_null($this->ftp))
		{
			return $this->ftp->serverToString();
		}
		
		return $this->server;
	}
	
}



/**
 *   Local FTP class
 *   -------------------------------------------
 *   
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2012-05-23
 *   
 *   
 */
class Local_FTP extends CI_FTP {

	/** インストールフォルダ */
	var $dir;
	/** ログインフォルダ */
	var $loginroot;

	/** 製品バージョン */
	var $version;
	/** 開発リビジョン */
	var $revision;
	
	/** エラーメッセージ */
	var $errmsg = '';
	
	function __construct($config)
	{
		parent::__construct($config);
	}

	/**
	 *   FTP接続し、エラーメッセージを格納する
	 *   @override
	 */
	function connect($config = array())
	{
		if (count($config) > 0)
		{
			$this->initialize($config);
		}
		
		$result = $this->conn_id = @ftp_connect($this->hostname, $this->port);
		if (FALSE === $result)
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_unable_to_connect');
			}
			$this->errmsg = '指定されたFTPサーバーに、接続できません。FTPサーバー名をご確認下さい。<br />もしくは、FTP接続の制限が考えられます。';
			return FALSE;
		}

		if ( ! $this->_login())
		{
			if ($this->debug == TRUE)
			{
				$this->_error('ftp_unable_to_login');
			}
			$this->errmsg = 'ユーザー名、パスワードのいずれかが間違っています。';
			$this->close();
			return FALSE;
		}

		// Set passive mode if needed
		if ($this->passive == TRUE)
		{
			if(! ftp_pasv($this->conn_id, TRUE) ){
				$this->passive = FALSE;
				$this->close();
				$this->connect();
			}
		}
		return TRUE;
	}

	/**
	 *   list_files の結果から"." と".." を削除した配列を返す
	 *
	 *   NOTICE:
	 *     Ftp::list_files() のwrapper
	 */
	function ls($path = '.', $option= '-a ') {
		$list = $this->list_files($path, $option);
		
		$retarr = array();
		foreach ($list as $i => $path) {
			$fname = basename($path);
			if ($fname != '.' && $fname != '..') {
				$retarr[$i] = $path;
			}
		}
		
		return $retarr;
	}
	
	/**
	 *   FTP ログインルートフォルダに移動する
	 */
	function changerdir() {
		return $this->changerdir($this->loginroot);
	}
	/**
	 *   インストールフォルダに移動する
	 *
	 *   Override の必要がある場合もあります
	 */
	function changeidir() {
		return $this->dir === '' || $this->changedir($this->dir);
	}
	
	/**
	 *   インストールシステムを利用できるかチェックする
	 *   チェック項目は
	 *   ・指定されたディレクトリが存在する
	 *   ・指定されたディレクトリに書き込み権限がある
	 *   ・URLが正しいか
	 *   ・サーバー変数は正常に取得できるか
	 *  
	 */
	function serverTest()
	{
		//ログインルートを保存
		$this->loginroot = $this->pwd();
		if ($this->is_exists($this->dir) && $this->changeidir())
		{
			//書き込み権限の確認
			$this->_checkWritable();
		}
		else
		{
			$this->errmsg = '指定されたフォルダは、存在しません。確認してください。';
		}
		
		//ログインルートに戻る
		$this->changedir($this->loginroot);

		return ($this->errmsg == '') ? TRUE : FALSE;
	}

	/**
	 *   チェックファイルを設置する
	 */
	function putChecker() {
		if (!$this->_is_conn()) {
			return FALSE;
		}
		if (!isset($this->r_fname)) {
			$this->r_fname = time() . '_chk.php';
		}

		$tmpfile = tempnam('tmp', 'hkntmp-');
		$stream ='test';
		file_put_contents($tmpfile, $stream);

		return parent::upload($tmpfile, $this->r_fname, FTP_BINARY, 0666);
	}

	/**
	 *   チェックファイルを削除する
	 */
	function removeChecker() {
		if (!$this->_is_conn() || !isset($this->r_fname)) {
			return FALSE;
		}
		$result = $this->delete_file($this->r_fname);
		unset($this->r_fname);
		return $result;
	}
	
	/**
	 *   カレントフォルダに書き込み権限があるかチェック
	 */
	function _checkWritable() {
		if (!$this->_is_conn()) {
			return FALSE;
		}
		
		$r_fname = $this->r_fname = time().'_chk.php';
		$res = $this->putChecker();
		$this->removeChecker();
		if(!$res){
			$this->errmsg = '指定されたフォルダは、書き込み権限がありません。';
			return FALSE;
		}
		
		return TRUE;
	}
	
	/**
	 *   オプション配列を基にchmod をかける
	 *
	 *   USAGE:
	 *     オプション配列は[ファイル名]=>[権限] の連想配列。
	 *     * ですべてのファイル、*／ ですべてのディレクトリ、*.php ですべてのphp ファイル、といった指定が可能
	 *     ※ ファイル名はインストールフォルダからの相対パスで
	 */
	function chmodr($option = array())
	{
		$mods= array();
		
		foreach ($option as $file => $p) {
			if (!isset($mods[$p])) {
				$mods[$p] = array();
			}
			
			//絶対パスに変換して、chmod に渡す配列を作成
			if ($file{0} != '/') {
				$file = $this->dir . '/'. $file;
			}
			$filelist = $this->_createFileList($file);
			$mods[$p] = array_merge($mods[$p], $filelist);
		}

		//対象ファイルすべてchmodをする
		foreach ($mods as $p => $filelist)
		{
			foreach ($filelist as $filepath)
			{
				$this->chmod($filepath, $p);
			}
		}
		
		//インストールディレクトリが777 かどうか判定し、777 の場合、755 に、707 の場合、705 に変更する
		$pardir = dirname($this->dir);
		//ルートディレクトリの場合、諦める
		if ($pardir != $this->dir) {
			$list = ftp_rawlist($this->conn_id, $pardir);
			$dirname = basename($this->dir);
			foreach ($list as $finfo) {
				$finfo = preg_split('/\s+/', $finfo);
				$ficnt = count($finfo);
				if ($finfo[$ficnt-1] == $dirname) {
					$perm = $finfo[0];
					if ($perm[0] == 'd') {
						$ptns = array('d', 'r', 'w', 'x', '-');
						$rpls = array('',  '1', '1', '1', '0');
						$pnum = intval(str_replace($ptns, $rpls, $perm), 2);
	
						if ($pnum == 0777) {
							$this->chmod($this->dir, 0755);
						} else if ($pnum == 0707) {
							$this->chmod($this->dir, 0705);
						}
					}
				}
			}
		}
	}
	
	/**
	 *   chmodr の書式に従ってファイル一覧を作成して返す
	 */
	function _createFileList($file) {
		$filelist = array();
		// *／ が入っている場合: ディレクトリすべて
		if (($pos = strpos($file, '*/')) !== FALSE) {
			$prefix = substr($file, 0, $pos);
			$suffix = substr($file, $pos + 1);
			
			//ファイルリストを取得し、ディレクトリ名を入れる
			$ls = $this->ls($prefix, '');
			foreach ($ls as $i => $filename) {
				//ディレクトリであれば格納
				if ($this->is_dir($filename)) {
					$file = $prefix . basename($filename) . $suffix;
					$filelist = array_merge($filelist, $this->_createFileList($file));
				}
			}
		}
		// *.ext が入っている場合: 該当ファイルすべて
		else if (preg_match('/^(.*)\*(\.[a-z0-9]+)$/i', $file, $ms)) {
			$path = $ms[1];
			$ext = $ms[2];
			
			//ファイルリストを取得し、拡張子をチェック
			$ls = $this->ls($path, '');
			$extptn = "/\\". $ext. '$/';
			foreach ($ls as $i => $filename) {
				//該当ファイルであれば格納
				if (preg_match($extptn, $filename)) {
					$filelist[] = $filename;
				}
			}
		}
		// * が入っている場合: ファイル全て
		else if (substr($file, -1, 1) === '*') {
			$path = dirname($file);
			//ファイルリストを取得し、すべて追加する
			$filelist = array_merge($filelist, $this->ls($path, ''));
		}
		//ただのファイル名: そのまま配列に格納
		else {
			$filelist[] = $file;
		}
	
		return $filelist;
	}

	// ------------------------------------------------------------------------	
		
	/**
	 *   list_files の結果から"." と".." を削除した配列を返す
	 *
	 *   NOTICE:
	 *     Ftp::list_files() のwrapper
	 */
	function pwd_ls() {
		$path = '.';
		$option= '-a ';
		
		$list = $this->list_files($path, $option);

		$retarr = array();
		foreach ($list as $fpath) {
			$fname = basename($fpath);
			if ($fname != '.' && $fname != '..') {
				$retarr[] = ($path=='.') ? $fname : $fpath;
			}
		}
		
		return $retarr;
	}

	// ------------------------------------------------------------------------	
	
	/**
	* 与えられたWeb上でのパスの、FTP接続上のパスを返す
	*
	* @param string:絶対パス
	*/
	function get_ftp_ab_path($file)
	{
		//カレントディレクトリ下にあるディレクトリを取得
		$list = array();
		$ftp_pwd = $this->pwd();
		foreach($this->pwd_ls() as $f)
		{
			if($this->is_dir($ftp_pwd.'/'.$f))
			{
				$list[] = $f;
			}
		}

		//$fileのパスを分割して $dirsに格納
		$fname = basename($file);
		$dirs = explode('/', substr( dirname($file), 1));
		$str_file = file_get_contents($file);
		
		$cnt = count($dirs);
		$stack_dir = '';
		
		//$dirsをさかのぼりながら、
		$retval = FALSE;
		for($i=$cnt-1; $i>=0; $i--)
		{
			if( array_search($dirs[$i], $list) !== FALSE )
			{
				$stack_dir = $dirs[$i] .'/'. $stack_dir;
				$ftp_ab_path = $ftp_pwd.'/'.rtrim($stack_dir, '/').'/'.$fname;
				$str_ftp = $this->file_get_contents($ftp_ab_path);
				if (md5($str_file) === md5($str_ftp))
				{
					$this->dir = $retval = $ftp_pwd.'/'.rtrim($stack_dir, '/').'/';
					break;
				}
			}
			else
			{
				$stack_dir = $dirs[$i] .'/'. $stack_dir;
			}
		}

		return $retval;
	}

	// ----------------------------------------------------------------------------
	
	/**
	* 与えら得たディレクトリが、Web上のディレクトリと合致するかチェックする。
	* 
	* @param string __FILE__
	* @param string FTP Directory
	* @return boolean
	*/
	function check_web_dir($file, $ftp_dir)
	{
		$str_file = file_get_contents($file);

		$bname = basename($file);		
		$str_ftp = $this->file_get_contents(rtrim($ftp_dir,'/').'/'.$bname);
		
		if( $str_file == $str_ftp)
		{
			$this->dir = rtrim($ftp_dir,'/').'/';
			return TRUE;
		}
		return FALSE;
	}

	// ----------------------------------------------------------------------------
	
	/**
	* サーバーのファイルをダウンロードする。
	* 
	* @param string filepath
	* @return string file stream
	*/
	function file_get_contents($file)
	{
		$tmpfile = tempnam("tmp", "hknftp-");
		$this->download($tmpfile, $file, FTP_BINARY);

		$str = file_get_contents($tmpfile);
		unlink($tmpfile);
		
		return $str;
	}

	// ----------------------------------------------------------------------------
	
	/**
	* ディレクトリを作成する（recursive）
	* 
	* @param array dirlist
	* @return boolean
	*/
	function mkdirr($dirs)
	{
		if (count($dirs) == 0)
		{
			return FALSE;
		}
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		foreach ($dirs as $path)
		{
			$this->changeidir();
			$this->mkdir($path);
		}
		return TRUE;
	}

	// ----------------------------------------------------------------------------
	
	/**
	* ファイルの設置
	* 
	* @param string localpath
	* @param string Remotepath
	* @return boolean
	*/
	function put($localpath, $rempath)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}
		$this->changeidir();
		return $this->upload($localpath, $rempath);
	}
	
	function serverToString()
	{
		return join(',', array($this->hostname, $this->username, $this->dir));
	}
}
