<?php
/**
* QHM テーマアップローダー
* Zip書庫形式のテーマファイルを受け取り、SKIN_DIR へ格納する
*/

function plugin_theme_uploader_action()
{
	global $script, $vars;

	//管理者チェック
	if ( ! ss_admin_check()) {
		redirect($script, 'この機能には、管理者のみアクセス可能です。');
	}

	if (($errmsg=plugin_theme_uploader_check()) !== '') {
		redirect($script, $errmsg);
	}

	plugin_theme_uploader_assets();

	$mode = isset($vars['mode']) ? $vars['mode'] : 'upload';
	switch ($mode) {
		case 'confirm':
			return plugin_theme_uploader_action_confirm();
		case 'complete':
			return plugin_theme_uploader_action_complete();
		default:
			return plugin_theme_uploader_action_upload();
	}
}

function plugin_theme_uploader_clean()
{
	if (isset($_SESSION['theme_uploader'])) {
		plugin_theme_uploader_rmdir($_SESSION['theme_uploader']['tmp_name']);
		unset($_SESSION['theme_uploader']);
	}
}

function plugin_theme_uploader_rmdir($dir)
{
	if ( ! file_exists($dir)) return;

	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);

	foreach ($files as $file)
	{
		if ($file->isDir() === true)
		{
			rmdir($file->getPathname());
		}
		else
		{
			unlink($file->getPathname());
		}
	}

	rmdir($dir);
}

function plugin_theme_uploader_unzip_strategy()
{
	if (class_exists('ZipArchive')) {
		return 'ZipArchive';
	} else if (shell_exec('which unzip')) {
		return 'shell';
	}
	return '';
}
function plugin_theme_uploader_unzip($zip_file, $extract_to)
{
	if ( ! file_exists($zip_file)) return;

	$strategy = plugin_theme_uploader_unzip_strategy();
	if ($strategy === 'ZipArchive') {
		$zip = new ZipArchive;
		$res = $zip->open($zip_file);
		if ($res === TRUE) {
			$zip->extractTo($extract_to);
			$zip->close();
		} else {
		}
	} else if ($strategy === 'shell') {
		shell_exec("unzip {$zip_file} -d {$extract_to}");
	}
}

/**
* アップロード画面を表示する。
*/
function plugin_theme_uploader_action_upload()
{
	plugin_theme_uploader_clean();

	return array(
		'msg'  => 'アップロード',
		'body' => plugin_theme_uploader_render('upload'),
	);
}

/**
* 確認画面を表示する。
* 存在するデザインであれば警告を表示する。
*/
function plugin_theme_uploader_action_confirm()
{
	$errmsg = '';
	$file = $_FILES['theme'];

	if ($file['error'] === 0
		&& preg_match('/\A(.+)\.zip\z/i', $file['name'], $mts))
	{
		$data['name'] = $mts[1];
	} elseif ($file['error'] > 0) {
		switch($file['error']) {
			case UPLOAD_ERR_INI_SIZE:
				$errmsg = 'ファイル容量がアップロード上限を超えています';
				break;
			default:
				$errmsg = 'アップロードエラー（code: '.$file['error'].'）が発生しました';
		}
	} else {
		$errmsg = 'Zip 形式のファイルではありません';
	}

	if ($errmsg === '') {
		// 展開して内容を確認
		$unzip_to = CACHEQHM_DIR . 'tmp_theme_file_' . md5(session_id());
		plugin_theme_uploader_unzip($file['tmp_name'], $unzip_to);
		if (is_dir($unzip_to) && $dh = opendir($unzip_to)) {
			$files = array('dir'=>array(), 'file'=>array());
			while (false !== ($entry = readdir($dh)))
			{
				if ( ! in_array($entry, array('.', '..'))) {
					if (is_dir($unzip_to . '/' . $entry)) {
						$key = 'dir';
					} else {
						$key = 'file';
					}
					$files[$key][] = $entry;
				}
				$files[] = $entry;
			}
			closedir($dh);

			// フォルダが一つだけならそのフォルダをテーマファイル格納フォルダと見なす
			if (count($files['file']) === 0 && count($files['dir']) === 1) {
				$data['name'] = array_pop($files['dir']);
				$data['files'] = glob("{$unzip_to}/{$data['name']}/*");
				$data['count'] = count($data['files']);
			} else {
				$errmsg = 'Zip ファイルの構成が不正です';
			}
		} else {
			$errmsg = 'Zip ファイルの展開に失敗しました';
			plugin_theme_uploader_rmdir($unzip_to);
		}

		if (isset($data['name']) && file_exists(SKIN_DIR . $data['name'])) {
			$data['overwrite'] = true;
		}
	}

	$data['file']   = $file;
	$data['errmsg'] = $errmsg;

	if ($errmsg !== '') {
		$title = 'アップロードエラー';
		$html = plugin_theme_uploader_render('upload', $data);
	} else {
		$_SESSION['theme_uploader'] = array(
			'phase'      => 'complete',
			'tmp_name'   => $unzip_to,
			'theme_name' => $data['name'],
		);
		$title = 'アップロードの確認';
		$html = plugin_theme_uploader_render('confirm', $data);
	}
	return array(
		'msg'  => '確認',
		'body' => $html,
	);

}

/**
* テーマファイルを設置し、完了画面を表示する。
* 続けてアップロードできるようにする。
*/
function plugin_theme_uploader_action_complete()
{
	$errmsg = '';
	$data = array();

	if ( ! (isset($_SESSION['theme_uploader'])
		&& $_SESSION['theme_uploader']['phase'] === 'complete'))
	{
		$errmsg = 'この操作は不正です。';
	}

	if ( ! is_dir($_SESSION['theme_uploader']['tmp_name'])) {
		$errmsg = 'テーマファイルが存在しません。';
	}

	if ($errmsg === '') {
		// テーマファイルを移動させる
		$theme_name = $_SESSION['theme_uploader']['theme_name'];
		$errmsg = plugin_theme_uploader_move(
			$_SESSION['theme_uploader']['tmp_name'] . '/' . $theme_name,
			SKIN_DIR . $theme_name);

		$data['errmsg'] = $errmsg;
		$data['name']   = $theme_name;
	}

	if ($errmsg !== '') {
		$title = 'エラー';
		$html = plugin_theme_uploader_render('upload', $data);
	} else {
		plugin_theme_uploader_clean();
		$data['success'] = true;
		$title = '完了';
		$html = plugin_theme_uploader_render('upload', $data);
	}
	return array(
		'msg'  => $title,
		'body' => $html,
	);
}

function plugin_theme_uploader_move($source, $dist)
{
	$source = rtrim($source, '/');
	$dist   = rtrim($dist, '/');
	if (file_exists($dist) && ! is_dir($dist)) {
		return "アップロード先 {$dsit} がフォルダではありません。";
	} else {
		if ( ! file_exists($dist)) {
			mkdir($dist);
		}
		foreach (glob("{$source}/*") as $file_path)
		{
			$new_name = "{$dist}/" . basename($file_path);
			rename($file_path, $new_name);
		}

		return '';
	}
}

function plugin_theme_uploader_render($view, $data = array())
{
	global $script;
	$template_dir = PLUGIN_DIR . 'theme_uploader/';
	$view_path = "{$template_dir}{$view}.html";
	if (file_exists($view_path)) {
		$limit = ini_get('upload_max_filesize');
		extract($data);

		ob_start();
		include $view_path;
		$html = ob_get_clean();
		return $html;
	}
	return '';
}

function plugin_theme_uploader_check()
{
	if ( ! is_writable(SKIN_DIR)) {
		return SKIN_DIR . ' に書き込み権限がありません';
	}
	return '';
}

function plugin_theme_uploader_assets()
{
	global $vars, $style_name;
	$qt = get_qt();

	$style_name = '..';
	$vars['disable_toolmenu'] = TRUE;

	$include_bs = '
<link rel="stylesheet" href="skin/bootstrap/css/bootstrap.min.css" />
<style>
.admin.qhm-theme-uploader .description {
	margin: 30px 0;
}
</style>
<script type="text/javascript" src="skin/bootstrap/js/bootstrap.min.js"></script>';
	$qt->appendv_once('include_bootstrap_pub', 'beforescript', $include_bs);
}
