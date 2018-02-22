<?php
/*
* QHM アップデータ
* GitHub にホスティングした最新版で上書きする
*/

function plugin_system_updater_action()
{
	global $script, $vars, $style_name;

	if ( ! ss_admin_check())
	{
		redirect($script, 'この機能には、管理者のみアクセス可能です。');
	}

	if (($errmsg=plugin_system_updater_check()) !== '') {
		redirect($script, $errmsg);
	}

	plugin_system_updater_assets();

	$mode = isset($vars['mode']) ? $vars['mode'] : 'update';
	switch ($mode) {
		case 'confirm':
			return plugin_system_updater_action_confirm();
		case 'complete':
			return plugin_system_updater_action_complete();
		default:
			return plugin_system_updater_action_upload();
	}
}

function plugin_system_updater_clean()
{
	if (isset($_SESSION['system_updater'])) {
		unlink($_SESSION['system_updater']['save_to']);
		plugin_system_updater_rmdir($_SESSION['system_updater']['extract_to']);
		$_SESSION['system_updater'] = array();
		unset($_SESSION['system_updater']);
	}
}

function plugin_system_updater_rmdir($dir)
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

function plugin_system_updater_unzip_strategy()
{
	if (class_exists('ZipArchive')) {
		return 'ZipArchive';
	} else if (shell_exec('which unzip')) {
		return 'shell';
	}
	return '';
}
function plugin_system_updater_unzip($zip_file, $extract_to)
{
	if ( ! file_exists($zip_file)) return;

	$strategy = plugin_system_updater_unzip_strategy();
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
function plugin_system_updater_action_upload()
{
	plugin_system_updater_clean();

	return array(
		'msg'  => 'アップデート',
		'body' => plugin_system_updater_render('update'),
	);
}

/**
* 確認画面を表示する。
* 最新版を利用できるかどうか表示する。
*/
function plugin_system_updater_action_confirm()
{
	$errmsg = '';

	$version = plugin_system_updater_get_latest_version();

	if ( ! $version) {
		$errmsg = '最新版の情報が取得できません';
	}
	if ( ! version_compare($version, '6.0.0', '>=')) {
		$errmsg = 'QHM v6 が公開されていません';
	}

	$data['errmsg'] = $errmsg;

	if ($errmsg !== '') {
		$title = 'エラー';
		$html = plugin_system_updater_render('update', $data);
	} else {
		$_SESSION['system_updater'] = array(
			'phase'      => 'complete',
		);
		$data['version']    = $version;
		$data['enable_new_version'] = version_compare(QHM_VERSION, $version, '<');
		$title = 'アップデートの確認';
		$html = plugin_system_updater_render('confirm', $data);
	}
	return array(
		'msg'  => 'アップデートの確認',
		'body' => $html,
	);

}

/**
* システムファイルを移動し、完了画面を表示する。
*/
function plugin_system_updater_action_complete()
{
	$errmsg = '';
	$data = array();

	if ( ! (isset($_SESSION['system_updater'])
		&& $_SESSION['system_updater']['phase'] === 'complete'))
	{
		$errmsg = 'この操作は不正です。';
	}

	if ($errmsg === '') {
		// システムファイルをダウンロード
		$download_url = 'https://github.com/open-qhm/qhm/archive/master.zip';
		$save_to = tempnam(CACHEQHM_DIR, 'update-');
		$extract_to = "{$save_to}_extract";
		file_put_contents($save_to, fopen($download_url, 'r'));

		// 展開
		plugin_system_updater_unzip($save_to, $extract_to);

		// 移動させる
		$errmsg = plugin_system_updater_move(
			$extract_to,
			'.');

		$data['errmsg'] = $errmsg;
	}

	if ($errmsg !== '') {
		$title = 'エラー';
		$html = plugin_system_updater_render('update', $data);
	} else {
		$_SESSION['system_updater'] = array(
			'save_to'    => $save_to,
			'extract_to' => $extract_to,
		);
		plugin_system_updater_clean();
		redirect($script, '最新版へ更新しました');
	}
	return array(
		'msg'  => $title,
		'body' => $html,
	);
}

function plugin_system_updater_get_latest_version()
{
	// GitHub へ問い合わせて最新バージョンを確認する
	$api_url = 'https://api.github.com/repos/open-qhm/qhm/contents/lib/init.php';
	$opts = array(
		'http' => array(
			'method' => 'GET',
			'header' => 'User-Agent: QHM Sytem Updater v' . QHM_VERSION,
		)
	);
	$context = stream_context_create($opts);
	$json = file_get_contents($api_url, false, $context);

	if ( ! $json) return false;

	# バージョンを取得
	$init_file_data = json_decode($json, true);
	$init_file = base64_decode($init_file_data['content']);
	$version = preg_match('/^define\(\'QHM_VERSION\', \'(.+)\'\);/m', $init_file, $mts)
		? $mts[1]
		: 0;
	return $version;
}

function plugin_system_updater_move($source, $dist)
{
	$source = rtrim($source, '/');
	$dist   = rtrim($dist, '/');
	if (file_exists($dist) && ! is_dir($dist)) {
		return "アップロード先 {$dist} がフォルダではありません。";
	} else {
		if ( ! file_exists($dist)) {
			mkdir($dist);
		}

		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		);

		$base_path = '';
		$index = 0;

		foreach ($files as $file)
		{
			if ($base_path === '') {
				$base_path = $file->getPathname() . '/';
				$index = strlen($base_path);
			} else {
				$file_path = $file->getPathname();
				$relative_path = substr($file_path, $index);
				$exclude_ptn = '/
					\A
					(?: # 完全一致
						(?:.htaccess|qhm\.ini\.php|\.gitignore|\composer\.(?:json|lock)|\.editorconfig|app\.json)
						|
						(?:qhm_(?:.+)\.ini\.txt)
						\z
					)|(?: # 前方一致
						(?:
							attach|diff|backup|counter|cache|cacheqhm|cacheqblog|
							skin\/hokukenstyle\/(.+)|swfu\/d|swfu\/data|trackback|wiki
						)
						\/
					)
				/x';
				if (preg_match($exclude_ptn, $relative_path)) {
					continue;
				}

				if ($file->isDir()) {
					mkdir($dist . '/' . $relative_path);
				} else {
					copy($file_path, $dist . '/' . $relative_path);
				}
			}
		}

		return '';
	}
}

function plugin_system_updater_render($view, $data = array())
{
	global $script;
	$template_dir = PLUGIN_DIR . 'system_updater/';
	$view_path = "{$template_dir}{$view}.html";
	if (file_exists($view_path)) {
		extract($data);

		ob_start();
		include $view_path;
		$html = ob_get_clean();
		return $html;
	}
	return '';
}

function plugin_system_updater_check()
{
	# TODO: 全てのフォルダの書き込み権限チェック
	if ( ! is_writable(CACHEQHM_DIR)) {
		return CACHEQHM_DIR . ' に書き込み権限がありません';
	}
	return '';
}

function plugin_system_updater_assets()
{
	global $vars, $style_name;
	$qt = get_qt();

	$style_name = '..';
	$vars['disable_toolmenu'] = TRUE;

	$include_bs = '
<link rel="stylesheet" href="skin/bootstrap/css/bootstrap.min.css" />
<style>
.admin.qhm-system-updater .description {
	margin: 30px 0;
}
</style>
<script type="text/javascript" src="skin/bootstrap/js/bootstrap.min.js"></script>';
	$qt->appendv_once('include_bootstrap_pub', 'beforescript', $include_bs);
}
