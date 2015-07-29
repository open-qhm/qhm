<?php
/**
 *   QHM Updater
 *   -------------------------------------------
 *   ./plugin/qhmupdate.inc.php
 *
 *   Copyright (c) 2012 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2012-05-21
 *   modified :
 *
 *   Description
 *
 *   Usage :
 *
 */

//error_reporting(E_ALL);
ini_set('display_errors', 'On');

function plugin_qhmupdate_init()
{
	exist_plugin("qhmsetting");
}

function plugin_qhmupdate_action()
{
	global $script, $vars, $style_name;
	global $no_proxy, $proxy_port;
	global $local_ftp;

	require(LIB_DIR . 'ensmall_auth.php');
	require(LIB_DIR . 'Ftp.php');
	require(LIB_DIR . 'qhm_fs.php');

	$local_script = dirname(dirname(__FILE__)).'/qhm.ini.php';

	//管理者チェック
	if ( ! ss_admin_check())
	{
		redirect($script, 'この機能には、管理者のみアクセス可能です。');
	}

	if ( ! isset($_SESSION['qhmupdate']))
	{
		$_SESSION['qhmupdate'] = array();
	}

	$style_name = '..';
	$vars['disable_toolmenu'] = TRUE;
	$tmpldir = PLUGIN_DIR . 'qhmupdate/';

	// 手動アップデート
	if (get_qhm_option('update') === 'download')
	{
		$download_url = 'https://github.com/open-qhm/qhm/archive/master.zip';
		$retarr = array();
		ob_start();
		include($tmpldir . 'download.php');
		$retarr['body'] = ob_get_clean();
		$retarr['msg']  = '手動アップデートについて';
		return $retarr;
	}

	$auth = new EnsmallAuth($script, PLUGIN_QHMSETTING_CODENAME);

	$mode = isset($vars['mode']) ? $vars['mode'] : '';
	$retarr = array('msg'=>'', 'body'=>'');

	// ! Ensmall クラブログイン
	if ($mode === 'club_login')
	{
		$email      = $vars['email'];
		$password   = $vars['password'];
		$use_proxy  = $vars['use_proxy'];
		$proxy_host = $vars['proxy_host'];
		$proxy_port = (isset($vars['proxy_port']) && ctype_digit($vars['proxy_port'])) ? $vars['proxy_port'] : $proxy_port;

		if ($use_proxy)
		{
			$auth->no_proxy = $no_proxy;
			$auth->set_proxy($proxy_host, $proxy_port);
		}

		$res = $auth->auth($email, $password);

		//Auth Success
		if ($res === ENSMALL_STATUS_SUCCESS && $auth->check_product())
		{
			// need ftp login
			if ( ! $_SESSION['qhmupdate']['is_writable'])
			{
				if (strlen($local_ftp) > 0)
				{
					list($ftp_host, $ftp_username, $dir) = array_pad(unserialize($local_ftp), 3, '');
				}

				// Update or Uninstall or Reset Password
				ob_start();
				include($tmpldir . 'ftp_login.php');
				$retarr['body'] = ob_get_clean();
				$retarr['msg'] = 'FTPログイン';
			}
			else
			{
				// confirm
				ob_start();
				include($tmpldir . 'update.php');
				$retarr['body'] = ob_get_clean();
				$retarr['msg'] = 'システムの更新';
			}
		}
		else {
			$error = $auth->errmsg;
			ob_start();
			include($tmpldir . 'club_login.php');
			$retarr['body'] = ob_get_clean();
			$retarr['msg'] = 'Ensmall Club 認証：エラー';
		}
	}

	// ! 必要ならFTPログイン
	else if ($mode === 'ftp_login')
	{
		$auth->readProps();

		$ftp_config = array(
			'hostname'=>'localhost',
			'dir' => '',
		);

		$ftp_config['username'] = $vars['ftp_username'];
		$ftp_config['password'] = $vars['ftp_password'];

		if (isset($vars['ftp_hostname']))
		{
			$ftp_config['hostname'] = $vars['ftp_hostname'];
		}
		if (isset($vars['install_dir']))
		{
			$ftp_config['dir'] = $vars['install_dir'];
		}

		$config = array(
			'ftp_config' => $ftp_config
		);
		$fs = new QHM_FS($config);

		// FTP login
		if ($fs->ftp->connect($ftp_config))
		{
			if (($abpath = $fs->ftp->get_ftp_ab_path($local_script)) === FALSE)
			{
				// display web dir form
				$fs->ftp->check_web_dir($local_script, $ftp_config['dir']);
			}
			// 設置先フォルダをセット
			$ftp_config['dir'] = $fs->ftp->dir;

			if ($fs->ftp->serverTest())
			{
				//FTP接続情報を保存
				$_SESSION['ftp_config'] = $ftp_config;

				ob_start();
				include($tmpldir . 'update.php');
				$retarr['body'] = ob_get_clean();
				$retarr['msg'] = 'システムの更新';
			}
			else
			{
				// invalid dir
				$error = $fs->ftp->errmsg;
				$ftp_type = 'full';
				ob_start();
				include($tmpldir . 'ftp_login.php');
				$retarr['body'] = ob_get_clean();
				$retarr['msg'] = 'FTP ログイン：エラー';
			}

			$fs->ftp->close();
		}
		else
		{
			// cannot connect
			$error = $fs->ftp->errmsg;
			$ftp_type = 'default';
			ob_start();
			include($tmpldir . 'ftp_login.php');
			$retarr['body'] = ob_get_clean();
			$retarr['msg'] = 'FTP ログイン：エラー';
		}
	}

	// ! アップデートの確認
	else if ($mode === 'update_confirm')
	{
		ob_start();
		include($tmpldir . 'update.php');
		$retarr['body'] = ob_get_clean();
		$retarr['msg'] = 'システムの更新';
	}

	// ! アップデート（システムの更新）
	//バックグラウンド実行
	//ログを取る
	else if ($mode === 'update')
	{
		$auth->readProps();

		$config = array();
		if (isset($_SESSION['ftp_config']))
		{
			$_SESSION['ftp_config']['connect'] = TRUE;
			$config['ftp_config'] = $_SESSION['ftp_config'];
		}

		$fs = new QHM_FS($config);

		$errmsg = plugin_qhmupdate_upload_files($auth, $fs);

		if ( ! is_null($fs->ftp))
		{
			$fs->ftp->close();
		}

		if ($errmsg === '')
		{
			echo '1';
		}
		else
		{
			echo $errmsg;
		}

		exit;

	}

	//完了
	else if ($mode === 'complete')
	{
		redirect($script, 'システムの更新完了');
	}

	// ! EnsmallClub 認証画面
	else
	{
		//Ensmall Club の認証情報を削除
		if (isset($_SESSION['ensmall_info']) OR $_SESSION['ftp_info'])
		{
			unset($_SESSION['ensmall_info'], $_SESSION['ftp_info']);
		}

		//実行ユーザーで書き込みが可能かチェック
		$_SESSION['qhmupdate']['is_writable'] = plugin_qhmupdate_is_writable();

		//Proxy を使う
		$set_proxy = isset($vars['set_proxy']);

		ob_start();
		include($tmpldir . 'club_login.php');
		$retarr['body'] =ob_get_clean();
		$retarr['msg'] = 'Ensmall Club 認証';

	}

	return $retarr;
}

/**
* HTTPの実行ユーザーで書き込み可能かチェックする
*/
function plugin_qhmupdate_is_writable(){

	$dirname = '';
	for($i=0; $i<20; $i++) //20回試行する
	{
		for($j=0; $j<10; $j++) //長さ10のランダムな数字のフォルダを作成
		{
			$dirname .= rand(0, 9);
		}

		if(! file_exists($dirname) )
		{
			break;
		}
		else
		{
			$dirname = '';
		}
	}

	if( $dirname == '')
	{
		die('error: エラーが発生しました。再読み込みをしてください。');
	}

	$is_w = FALSE;
	if( mkdir($dirname) ){
		$fname = $dirname.'/'.$dirname.'.txt';
		if( file_put_contents($fname, 'hoge') )
		{
			unlink($fname);
			$is_w = TRUE;
		}

		rmdir($dirname);
	}

	return $is_w;
}

/**
* ローカル接続かFTP接続かを判定
*
* @param
* @return class HKN_Local or HKN_FTP
*/
function plugin_qhmupdate_get_updater()
{
	if (isset($_SESSION['is_writable']) && $_SESSION['is_writable'])
	{
		$fs = new HKN_Local();
	}
	else
	{
		$fs = new HKN_FTP();
		$fs->readProps();
		$fs->connect();
	}

	return $fs;
}

/**
* ファイルのアップロード
*
* @param class EnsmallAuth
* @param class QHM_FS
* @return string error
*/
function plugin_qhmupdate_upload_files($auth, $fs)
{
	$mode = 'update';
	$error = '';

	// get upload list
	$uplist = $auth->get_updateFileList('update');

	if ($uplist !== FALSE)
	{
		// make directories
		$fs->mkdirr($uplist['dirs']);

		// original data を抽出
		$diff_arr = array();
		foreach ($uplist['originals'] as $regstr)
		{
			$regstr = str_replace("/", "\/", $regstr);
			$regstr = str_replace("*", ".*", $regstr);
			foreach ($uplist['files'] as $file)
			{
				if (preg_match('/^'.$regstr.'$/', $file))
				{
					$diff_arr[$file] = $file;
				}
			}
		}

		$sum = 0;

		// upload files
		$cnt=0;
		$sum += count($uplist['files']);
		foreach ($uplist['files'] as $file)
		{
			// オリジナルデータがなければ作成し、あれば、アップデートしない
			if (array_key_exists($file, $diff_arr) && file_exists($file)) {
				$cnt++;
				continue;
			}

			$res = $auth->download($file);
			if ($res === ENSMALL_STATUS_ERROR_INSTALL_OVER)
			{
				$error = $auth->errmsg;
				break;
			}
			if ($res)
			{
				// save file
				if ( ! $fs->put($res, $file))
				{
					$error .= "cannot save file: {$file}\n";
				}
			}
		}

		// chmod
		$fs->chmodr($uplist['permissions']);

		// インストール回数をカウントアップ
		$auth->install_log($mode, $fs->serverToString());
	}
	else
	{
		$error = 'ファイルリストを取得できませんでした。';
	}

	return $error;
}
