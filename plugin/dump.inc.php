<?php
// $Id: dump.inc.php,v 1.37 2006/01/12 01:01:35 teanan Exp $
//
// Remote dump / restore plugin
// Originated as tarfile.inc.php by teanan / Interfair Laboratory 2004.

//zip.lib.php を読み込む
require_once(LIB_DIR. 'zip.lib.php');

//zip.lib.php を拡張する
class zipfile2 extends zipfile{

	function addDir($dir, $filter = false, $namedecode = false) {
		//最後のスラッシュを削除
		$dir = rtrim($dir, '/');
//		$dir = ($dir{strlen($dir)-1}=='/') ? substr($dir, 0, strlen($dir)-1) : $dir;
		
		if (!file_exists($dir) || !is_dir($dir)) {
			return;
		}
		
		$count = 0;
		$dhandle = opendir($dir);
		if ($dhandle) {
			while (false !== ($fname = readdir($dhandle))) {
		
				if (is_dir( $dir.'/'.$fname )) {
					if (substr($fname, 0, 1) != '.')
						$count += $this->addDir("$dir/$fname", $filter);
				} else {
					if((!$filter || preg_match("/$filter/", $fname)) && $fname != '.' && $fname != '..')
					{
						$filename = $dir. '/'. $fname;
						$handle = fopen($dir.'/'.$fname, "rb");
						$targetFile = fread($handle, filesize($filename));
						fclose($handle);
						if ($namedecode) {
							$filename = plugin_dump_decodename($filename);
						}
						$this->addFile($targetFile, './'.$filename);
						$count++;
					}
				}
			}
			closedir($dhandle);
		}
		return $count;
	
	}
	
	function download($filename) {
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename={$filename}");
		echo $this->file();
	}
}


/////////////////////////////////////////////////
// User defines

// Allow using resture function
define('PLUGIN_DUMP_ALLOW_RESTORE', TRUE); // FALSE, TRUE

// ページ名をディレクトリ構造に変換する際の文字コード (for mbstring)
define('PLUGIN_DUMP_FILENAME_ENCORDING', 'SJIS');

// 最大アップロードサイズ
define('PLUGIN_DUMP_MAX_FILESIZE', 4024); // Kbyte

/////////////////////////////////////////////////
// Internal defines

// Action
define('PLUGIN_DUMP_DUMP',    'dump');    // Dump & download
define('PLUGIN_DUMP_RESTORE', 'restore'); // Upload & restore
define('PLUGIN_DUMP_FULL',    'full');    // Dump & download

global $_STORAGE;

// DATA_DIR (wiki/*.txt)
$_STORAGE['DATA_DIR']['add_filter']     = '^[0-9A-F]+\.txt';
$_STORAGE['DATA_DIR']['extract_filter'] = '^((?:[0-9A-F])+)(\.txt){0,1}';

// UPLOAD_DIR (attach/*)
$_STORAGE['UPLOAD_DIR']['add_filter']     = '^[0-9A-F_]+';
$_STORAGE['UPLOAD_DIR']['extract_filter'] = '^((?:[0-9A-F]{2})+)_((?:[0-9A-F])+)';

// BACKUP_DIR (backup/*.gz)
$_STORAGE['BACKUP_DIR']['add_filter']     = '^[0-9A-F]+\.gz';
$_STORAGE['BACKUP_DIR']['extract_filter'] =  '^((?:[0-9A-F])+)(\.gz){0,1}';

// SWFU_DIR (swfu/d/*)
$_STORAGE['SWFU_DIR']['add_filter']     = '^[-_.+a-zA-Z0-9]+';
$_STORAGE['SWFU_DIR']['extract_filter'] =  '^([-_.+a-zA-Z0-9]+)';

//logo image
$_STORAGE['CACHE_DIR'] = array(
	'add_filter' => '^qhm_logo\.',
	'extract_filter' => '^qhm_logo\.'
);

//cheetan filter
$ct_filter = array(
	'add_filter' => '^[a-z_]+\.txt',
	'extract_filter' => '^[a-z_]+\.txt'
);
// SWFUDATA_DIR (swfu/data/*.txt), FWD3DATA (fwd3/sys/data/*.txt)
$_STORAGE['FWD3DATA_DIR'] = $_STORAGE['SWFUDATA_DIR'] = $ct_filter;

$_STORAGE['HOME_DIR'] = array(
	'add_fileter'     => '',
	'extract_filter' => '^qhm(?:_access|_users)?\.ini\.(?:php|txt)',
);


/////////////////////////////////////////////////
// プラグイン本体
function plugin_dump_action()
{
	global $style_name, $script;
	$qm = get_qm();
	$qt = get_qt();
	
	$include_bs = '
<link rel="stylesheet" href="skin/bootstrap/css/bootstrap.min.css" />
<script type="text/javascript" src="skin/bootstrap/js/bootstrap.min.js"></script>';
	$qt->appendv_once('include_bootstrap_pub', 'beforescript', $include_bs);

	$head = '
<link rel="stylesheet" href="skin/hokukenstyle/qhm.css" />
<style type="text/css">
body {background-color: #E7E7E7;}
</style>';
	$qt->appendv('beforescript', $head);
	
	$style_name = '..';
	$back_url = '<p><a href="'.$script.'">'. $qm->m['frontpage']. '</a> &gt; <a href="'.$script.'?cmd=qhmsetting">'. $qm->m['preferences']. '</a> &gt; '. $qm->m['here']. '</p>';

    $editable = ss_admin_check();
	if(!$editable){
		return array('msg' => $qm->m['plg_dump']['title'], 'body' => $qm->m['fmt_err_page_only_for_admin']);
	}

	global $vars;

	if (PKWK_READONLY) die_message($qm->m['fmt_err_pkwk_readonly']);

	$pass = isset($_POST['pass']) ? $_POST['pass'] : NULL;
	$act  = isset($vars['act'])   ? $vars['act']   : NULL;

	$body = '';

	if ($pass !== NULL) {
		if (! pkwk_login($pass)) {
			$body = "<p class=\"alert alert-danger\">{$qm->m['fmt_err_invalidpass']}</strong></p>\n";
		} else {
			switch($act){
			case PLUGIN_DUMP_DUMP:
				$body = plugin_dump_download();
				break;
			case PLUGIN_DUMP_RESTORE:
				$retcode = plugin_dump_upload();
				$msg = $retcode['code']? $qm->m['plg_dump']['restore_success']: $qm->m['plg_dump']['restore_failed'];
				$body .= $retcode['msg'];
				return array('msg' => $msg, 'body' => $back_url.$body);
				break;
			case PLUGIN_DUMP_FULL:
				$body = plugin_dump_download_full();
				break;
			}
		}
	}

	// 入力フォームを表示
	$body .= plugin_dump_disp_form();

	$msg = '';
	if (PLUGIN_DUMP_ALLOW_RESTORE) {
		$msg = $qm->m['plg_dump']['title_bk_rstr'];
	} else {
		$msg = $qm->m['plg_dump']['title_bk'];
	}

	return array('msg' => $msg, 'body' => $back_url.$body);
}


function plugin_dump_decodename($name) {

	$dirname  = dirname(trim($name)) . '/';
	$filename = basename(trim($name));
	if (preg_match("/^((?:[0-9A-F]{2})+)_((?:[0-9A-F]{2})+)/", $filename, $matches)) {
		// attachファイル名
		$filename = decode($matches[1]) . '/' . decode($matches[2]);
	} else {
		$pattern = '^((?:[0-9A-F]{2})+)((\.txt|\.gz)*)$';
		if (preg_match("/$pattern/", $filename, $matches)) {
			$filename = decode($matches[1]) . $matches[2];

			// 危ないコードは置換しておく
			$filename = str_replace(':',  '_', $filename);
			$filename = str_replace('\\', '_', $filename);
		}
	}
	$filename = $dirname . $filename;
	// ファイル名の文字コードを変換
	if (function_exists('mb_convert_encoding'))
		$filename = mb_convert_encoding($filename, PLUGIN_DUMP_FILENAME_ENCORDING);


	return $filename;
}


/////////////////////////////////////////////////
// ファイルのダウンロード
function plugin_dump_download()
{
	global $vars, $_STORAGE, $logo_image;
	$qm = get_qm();

	// アーカイブの種類
	$arc_kind = ($vars['pcmd'] == 'tar') ? 'tar' : 'tgz';

	// ページ名に変換する
	$namedecode = isset($vars['namedecode']) ? TRUE : FALSE;

	// バックアップディレクトリ
	$bk_wiki   = isset($vars['bk_wiki'])   ? TRUE : FALSE;
	$bk_attach = isset($vars['bk_attach']) ? TRUE : FALSE;
	$bk_backup = isset($vars['bk_backup']) ? TRUE : FALSE;
	$bk_swfu   = isset($vars['bk_swfu'])   ? TRUE : FALSE;
	$bk_swfudata = isset($vars['bk_swfudata']) ? TRUE: FALSE;
	$bk_fwd3data = isset($vars['bk_fwd3data']) ? TRUE: FALSE;
	
	//ロゴ画像
	$bk_qhmlogo = isset($vars['bk_qhmlogo']) ? TRUE: FALSE;
	
	//設定ファイル
	$bk_qhmini    = isset($vars['bk_qhmini'])    ? TRUE: FALSE;
	$bk_qhmaccess = isset($vars['bk_qhmaccess']) ? TRUE: FALSE;
	$bk_qhmusers  = isset($vars['bk_qhmusers'])  ? TRUE: FALSE;

	$filecount = 0;
	$zip = new zipfile2();
	$zipfile = 'qhmbk_'.date("Ymd"). '.zip';

	//dirs
	if ($bk_wiki)     $filecount += $zip->addDir(DATA_DIR,        $_STORAGE['DATA_DIR']['add_filter'],     $namedecode);
	if ($bk_attach)   $filecount += $zip->addDir(UPLOAD_DIR,      $_STORAGE['UPLOAD_DIR']['add_filter'],   $namedecode);
	if ($bk_backup)   $filecount += $zip->addDir(BACKUP_DIR,      $_STORAGE['BACKUP_DIR']['add_filter'],   $namedecode);
	if ($bk_swfu)     $filecount += $zip->addDir('swfu/d',        $_STORAGE['SWFU_DIR']['add_filter']                 );
	if ($bk_swfudata) $filecount += $zip->addDir('swfu/data',     $_STORAGE['SWFUDATA_DIR']['add_filter']             );
	if ($bk_fwd3data) $filecount += $zip->addDir('fwd3/sys/data', $_STORAGE['FWD3DATA_DIR']['add_filter']             );
	//logo image
	if ($bk_qhmlogo)  $filecount += $zip->addDir(CACHE_DIR,       $_STORAGE['CACHE_DIR']['add_filter']                );
	
	//ini files
	$inifilters = array();
	if ($bk_qhmini) $inifilters[] = 'qhm\.ini\.php';
	if ($bk_qhmaccess) $inifilters[] = 'qhm_access\.ini\.txt';
	if ($bk_qhmusers) $inifilters[] = 'qhm_users\.ini\.txt';
	
	if (count($inifilters) > 0) {
		$inifilter = '(?:'. join('|', $inifilters). ')';
		$filecount += $zip->addDir('.', $inifilter);
	}

	if ($filecount === 0) {
		return '<p class="alert alert-error">'. $qm->m['plg_dump']['err_no_files'].'</strong></p>';
	} else {
		// ダウンロード
		$zip->download($zipfile);
		exit;
	}
}

/////////////////////////////////////////////////
// ファイルのダウンロード
function plugin_dump_download_full()
{

	error_reporting(E_ERROR | E_PARSE);
	
	global $vars;
	$qm = get_qm();

	if( isset($vars['_p_dump_memlimit']) ){
		
		if( is_numeric($vars['_p_dump_memlimit_value']) )
		{
				ini_set("memory_limit", $vars['_p_dump_memlimit_value']."M");
		}
		else{
			return $qm->m['plg_dump']['err_invalid_memory'];
		}
	}


	// バックアップディレクトリ
	$bk_dirs = array(
		UPLOAD_DIR, COUNTER_DIR, CACHE_DIR, CACHEQHM_DIR, CACHEQBLOG_DIR, DIFF_DIR, 
		IMAGE_DIR, BACKUP_DIR, LIB_DIR, PLUGIN_DIR, 
		DATA_DIR, 'js/', 'swfu/', 'fwd3/', 'fwd/', 'fwd2/', 'skin/', 'trackback/'
	);
	
	// バックアップファイル (.txt, .phpすべて)
	$bk_files = array();
	$hd = opendir('./');
	while($f = readdir($hd)){
		if( preg_match('/.*\.(php|txt)/', $f) )
			$bk_files[] = $f;
	}
	
	$bk_fname = 'qhmbk_'.date("Ymd").'.zip';

	//zipファイルの作成　(メモリーオーバーをする危険あり)
	$zip = new zipfile2();
	foreach($bk_dirs as $dir){
		$zip->addDir($dir);
		//zip_add_dir($dir, $zipFile, 'qhmbk_');
	}
	
	foreach($bk_files as $file){
		if( file_exists($file) )
			$zip->addFile(file_get_contents($file), $file);
	}
	
	$dump_buffer = $zip->file();
	
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename={$bk_fname}");
	echo $dump_buffer;
	
	exit;
}



/////////////////////////////////////////////////
// ファイルのアップロード
function plugin_dump_upload()
{
	global $vars, $_STORAGE, $script;
	$qm = get_qm();

	if (! PLUGIN_DUMP_ALLOW_RESTORE)
		return array('code' => FALSE , 'msg' => $qm->m['plg_dump']['err_prohibit_restore']);

	$filename = $_FILES['upload_file']['name'];
	$matches  = array();
	$arc_kind = FALSE;
	if(!preg_match('/\.zip$/', $filename, $matches)){
		die_message($qm->m['plg_dump']['err_invalid_filetype']);
	}

	if ($_FILES['upload_file']['size'] >  PLUGIN_DUMP_MAX_FILESIZE * 1024)
		die_message($qm->replace('plg_dump.err_size_over', PLUGIN_DUMP_MAX_FILESIZE));

	//require unzip
	require_once(LIB_DIR. 'unzip.lib.php');
	
	// Create a temporary tar file
	$uploadfile = tempnam(realpath(CACHEQHM_DIR), 'zip_uploaded_');
	
	if (!move_uploaded_file($_FILES['upload_file']['tmp_name'], $uploadfile)) {
		@unlink($uploadfile);
		die_message($qm->m['plg_dump']['err_upload_failed']);
	}
	$unzip = new SimpleUnzip($uploadfile);
	
	$files = array();
	$len = $unzip->Count();
	for ($i = 0; $i < $len; $i++) {
		$name = $unzip->GetName($i);
		$path = $unzip->GetPath($i);
		//path をディレクトリにする: ./ で始まる場合、それを除去
		$dir = basename($path);
		//swfu/d
		if ($dir == 'd') {
			$dir = strpos($path, 'swfu/d') !== FALSE? 'swfu/d': '';
		}
		if ($dir == 'data') {
			//swfu/data
			if (strpos($path, 'swfu/data') !== FALSE) {
				$dir = 'swfu/data';
			}
			//fwd3/sys/data
			else if (strpos($path, 'fwd3/sys/data') !== FALSE) {
				$dir = 'fwd3/sys/data';
			}
		}
		if (strpos($path, './.') !== FALSE) {
			$path = str_replace('./.', '.', $path);
		}
		
		switch($dir) {
			case 'wiki':
				$stokey = 'DATA_DIR';
				break;
			case 'attach':
				$stokey = 'UPLOAD_DIR';
				break;
			case 'backup':
				$stokey = 'BACKUP_DIR';
				break;
			case 'swfu/d':
				$stokey = 'SWFU_DIR';
				break;
			case 'swfu/data':
				$stokey = 'SWFUDATA_DIR';
				break;
			case 'fwd3/sys/data':
				$stokey = 'FWD3DATA_DIR';
				break;
			case 'cache':
				$stokey = 'CACHE_DIR';
				break;
			default:
				$stokey = 'HOME_DIR';
		}
		
		$filter = isset($_STORAGE[$stokey]['extract_filter'])? $_STORAGE[$stokey]['extract_filter']: '';
		if ($filter && preg_match("/$filter/", $name)) {
			$uzfile = $path. '/'. $name;

			$files[] = $uzfile;

			$data = $unzip->GetData($i);
			$dlen = strlen($data);
			if ($fp = fopen($uzfile, "wb")) {
				fwrite($fp, $data, $dlen);
				fclose($fp);
				chmod($uzfile, 0666);
			} else {
				echo '<error>', $qm->replace('plg_dump.err_open_archive', $name), '</error>';
			}
		}
	}


	if (empty($files)) {
		@unlink($uploadfile);
		return array('code' => FALSE, 'msg' => $qm->m['plg_dump']['err_upload_empty']);
	}

	$msg  = '<p><strong>'. $qm->m['plg_dump']['restore_header']. '</strong><ul>';
	foreach($files as $name) {
		$msg .= "<li>$name</li>\n";
	}
	$msg .= '</ul></p>';
	$msg .= '
<p>
	<a href="'. h($script) .'?cmd=qhmsetting">設定一覧へ戻る</a>
</p>
';

	@unlink($uploadfile);

	return array('code' => TRUE, 'msg' => $msg);
}


/////////////////////////////////////////////////
// 入力フォームを表示
function plugin_dump_disp_form()
{
	global $script, $defaultpage, $logo_image;
	$qm = get_qm();

	$act_down = PLUGIN_DUMP_DUMP;
	$act_up   = PLUGIN_DUMP_RESTORE;
	$maxsize  = PLUGIN_DUMP_MAX_FILESIZE;
	$act_full = PLUGIN_DUMP_FULL;
	
	//ロゴ画像があるかないか
	$logocheck = '';
	if (file_exists($logo_image)) {
		$logocheck = '<label for="_p_dump_d_qhmlogo">
		<input type="checkbox" name="bk_qhmlogo" id="_p_dump_d_qhmlogo" />'. $logo_image .'<span style="color:#999">&nbsp;&nbsp;&nbsp;&nbsp; --- '. $qm->m['plg_dump']['desc_qhmlogo']. '</span></label>';
	}
	


	$data = <<<EOD
<span class="small">
</span>
<h2>{$qm->m['plg_dump']['frm_header']}</h2>
<div style="border:1px #999 solid;background-color:#eee; padding:0px 1em;">
{$qm->m['plg_dump']['frm_ntc']}
</div>

<h3>{$qm->m['plg_dump']['frm_fullbk_header']}</h3>
{$qm->m['plg_dump']['frm_fullbk_ntc']}
<form action="$script" method="post" class="form-inline">
 <div>
  <input type="hidden" name="cmd"  value="dump" />
  <input type="hidden" name="page" value="$defaultpage" />
  <input type="hidden" name="act"  value="$act_full" />

<p><label for="_p_dump_adminpass_dump"><strong>{$qm->m['plg_dump']['label_adminpass']}</strong></label>
  <input type="password" name="pass" id="_p_dump_adminpass_dump" size="12" class="form-control input-sm">
  <input type="submit"   name="ok"   value="{$qm->m['plg_dump']['btn_download']}" class="btn btn-primary">
</p>
<p style="margin-left:2em;"><input type="checkbox" name="_p_dump_memlimit"> {$qm->m['plg_dump']['label_memory_limit']} <input type="text" size="4" name="_p_dump_memlimit_value" value="64" class="form-control input-sm">MB<br />
{$qm->m['plg_dump']['frm_fullbk_ntc_memory_limit']}
</p>

 </div>
</form>
<br />

<h3>{$qm->m['plg_dump']['frm_bk_header']}</h3>
<form action="$script" method="post" class="form-inline">
 <div>
  <input type="hidden" name="cmd"  value="dump" />
  <input type="hidden" name="page" value="$defaultpage" />
  <input type="hidden" name="act"  value="$act_down" />

<p>
	{$qm->m['plg_dump']['frm_bk_ntc']}
</p>

<div style="float:left;width:45%;">
<p><strong>{$qm->m['plg_dump']['frm_bk_dirlist']}</strong></p>
<p>
  <label for="_p_dump_d_wiki">
  	<input type="checkbox" name="bk_wiki" id="_p_dump_d_wiki" checked="checked" />
  	wiki<span style="color:#999">&nbsp;&nbsp;&nbsp;&nbsp; --- {$qm->m['plg_dump']['desc_wiki']}</span>
  </label>
  <label for="_p_dump_d_attach">
  <input type="checkbox" name="bk_attach" id="_p_dump_d_attach" />
  attach<span style="color:#999">&nbsp;&nbsp;&nbsp;&nbsp; --- {$qm->m['plg_dump']['desc_attach']}</span>
  </label>
  <label for="_p_dump_d_backup">
  <input type="checkbox" name="bk_backup" id="_p_dump_d_backup" />
  backup<span style="color:#999">&nbsp;&nbsp;&nbsp;&nbsp; --- {$qm->m['plg_dump']['desc_backup']}</span>
  </label>
  <label for="_p_dump_d_swfu">
  <input type="checkbox" name="bk_swfu" id="_p_dump_d_swfu" />
  swfu/d<span style="color:#999">&nbsp;&nbsp;&nbsp;&nbsp; --- {$qm->m['plg_dump']['desc_swfud']}</span>
  </label>
  <label for="_p_dump_d_swfudata">
  <input type="checkbox" name="bk_swfudata" id="_p_dump_d_swfudata" />
  swfu/data<span style="color:#999">&nbsp;&nbsp;&nbsp;&nbsp; --- {$qm->m['plg_dump']['desc_swfudata']}</span>
  </label>
  <label for="_p_dump_d_fwd3data">
  <input type="checkbox" name="bk_fwd3data" id="_p_dump_d_fwd3data" />
  fwd3/sys/data<span style="color:#999">&nbsp;&nbsp;&nbsp;&nbsp; --- {$qm->m['plg_dump']['desc_fwd3data']}</span>
  </label>
</p>
</div>

<div style="float:left;width:45%;">
<p><strong>{$qm->m['plg_dump']['frm_bk_filelist']}</strong></p>
<p>
  <label for="_p_dump_d_qhmini">
  <input type="checkbox" name="bk_qhmini" id="_p_dump_d_qhmini" />
  qhm.ini.php<span style="color:#999">&nbsp;&nbsp;&nbsp;&nbsp; --- {$qm->m['plg_dump']['desc_qhmini']}</span>
  </label>
  <label for="_p_dump_d_qhmaccess">
  <input type="checkbox" name="bk_qhmaccess" id="_p_dump_d_qhmaccess" />
  qhm_access.ini.txt<span style="color:#999">&nbsp;&nbsp;&nbsp;&nbsp; --- {$qm->m['plg_dump']['desc_qhmaccess']}</span>
  </label>
  <label for="_p_dump_d_qhmusers">
  <input type="checkbox" name="bk_qhmusers" id="_p_dump_d_qhmusers" />
  qhm_users.ini.txt<span style="color:#999">&nbsp;&nbsp;&nbsp;&nbsp; --- {$qm->m['plg_dump']['desc_qhmusers']}</span>
  </label>$logocheck
</p>
</div>
<div style="clear:both;"></div>

<p><strong>{$qm->m['plg_dump']['frm_bk_option']}</strong></p>
<p>
  <label for="_p_dump_namedecode">
  <input type="checkbox" name="namedecode" id="_p_dump_namedecode" />
  {$qm->m['plg_dump']['label_namedecode']}</label>
</p>
<p>
  <label for="_p_dump_adminpass_dump"><strong>{$qm->m['plg_dump']['label_adminpass']}</strong></label>
  <input type="password" name="pass" id="_p_dump_adminpass_dump" size="12" class="form-control input-sm">
  <input type="submit"   name="ok"   value="{$qm->m['plg_dump']['btn_download']}" class="btn btn-primary">
</p>
 </div>
</form>
EOD;

	if(PLUGIN_DUMP_ALLOW_RESTORE) {
		$frm_rstr_ntc_maxsize = $qm->replace('plg_dump.frm_rstr_ntc_maxsize', $maxsize);
		$dump2link = $qm->replace('plg_dump.frm_rstr_ntc', $script);
		$data .= <<<EOD
<h3>{$qm->m['plg_dump']['frm_rstr_header']} (*.zip)</h3>
<p style="color:red;font-size:.8em">
	{$dump2link}
</p>
<form enctype="multipart/form-data" action="$script" method="post" class="form-inline">
 <div>
  <input type="hidden" name="cmd"  value="dump" />
  <input type="hidden" name="page" value="$defaultpage" />
  <input type="hidden" name="act"  value="$act_up" />
<p><strong>{$qm->m['plg_dump']['frm_rstr_ntc2']}</strong></p>
<p><span class="small">
$frm_rstr_ntc_maxsize<br />
</span>
  <label for="_p_dump_upload_file">{$qm->m['plg_dump']['label_file']}:</label>
  <input type="file" name="upload_file" id="_p_dump_upload_file" size="40" />
</p>
<p><label for="_p_dump_adminpass_restore"><strong>{$qm->m['plg_dump']['label_adminpass']}</strong></label>
  <input type="password" name="pass" id="_p_dump_adminpass_restore" size="12" class="form-group input-sm">
  <input type="submit"   name="ok"   value="{$qm->m['plg_dump']['btn_restore']}" class="btn btn-primary" />
</p>
 </div>
</form>
EOD;
	}

	return $data;
}


