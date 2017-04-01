<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: attach.inc.php,v 1.82 2006/04/14 23:51:12 teanan Exp $
// Copyright (C)
//   2003-2006 PukiWiki Developers Team
//   2002-2003 PANDA <panda@arino.jp> http://home.arino.jp/
//   2002      Y.MASUI <masui@hisec.co.jp> http://masui.net/pukiwiki/
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// File attach plugin

// NOTE (PHP > 4.2.3):
//    This feature is disabled at newer version of PHP.
//    Set this at php.ini if you want.
// Max file size for upload on PHP (PHP default: 2MB)
ini_set('upload_max_filesize', '2M');

// Max file size for upload on script of PukiWikiX_FILESIZE
define('PLUGIN_ATTACH_MAX_FILESIZE', (1024 * 1024 * 5)); // default: 5MB

// 管理者だけが添付ファイルをアップロードできるようにする
define('PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY', FALSE); // FALSE or TRUE

// 管理者だけが添付ファイルを削除できるようにする
define('PLUGIN_ATTACH_DELETE_ADMIN_ONLY', FALSE); // FALSE or TRUE

// 管理者が添付ファイルを削除するときは、バックアップを作らない
// PLUGIN_ATTACH_DELETE_ADMIN_ONLY=TRUEのとき有効
define('PLUGIN_ATTACH_DELETE_ADMIN_NOBACKUP', TRUE); // FALSE or TRUE

// アップロード/削除時にパスワードを要求する(ADMIN_ONLYが優先)
define('PLUGIN_ATTACH_PASSWORD_REQUIRE', FALSE); // FALSE or TRUE

// 添付ファイル名を変更できるようにする
define('PLUGIN_ATTACH_RENAME_ENABLE', TRUE); // FALSE or TRUE

// ファイルのアクセス権
define('PLUGIN_ATTACH_FILE_MODE', 0644);
//define('PLUGIN_ATTACH_FILE_MODE', 0604); // for XREA.COM

// File icon image
define('PLUGIN_ATTACH_FILE_ICON', '<img src="' . IMAGE_DIR .  'file.png"' .
	' width="20" height="20" alt="file"' .
	' style="border-width:0px" />');

// mime-typeを記述したページ
define('PLUGIN_ATTACH_CONFIG_PAGE_MIME', 'plugin/attach/mime-type');

//-------- convert
function plugin_attach_convert()
{
	global $vars;

	$page = isset($vars['page']) ? $vars['page'] : '';

	$nolist = $noform = FALSE;
	if (func_num_args() > 0) {
		foreach (func_get_args() as $arg) {
			$arg = strtolower($arg);
			$nolist |= ($arg == 'nolist');
			$noform |= ($arg == 'noform');
		}
	}

	$ret = '';
	if (! $nolist) {
		$obj  = new AttachPages($page);
		$ret .= $obj->toString($page, TRUE);
	}
	if (! $noform) {
		$ret .= attach_form($page);
	}

	return $ret;
}

//-------- action
function plugin_attach_action()
{
	global $vars;
	$qm = get_qm();

	// Backward compatible
	if (isset($vars['openfile'])) {
		$vars['file'] = $vars['openfile'];
		$vars['pcmd'] = 'open';
	}
	if (isset($vars['delfile'])) {
		$vars['file'] = $vars['delfile'];
		$vars['pcmd'] = 'delete';
	}

	$pcmd  = isset($vars['pcmd'])  ? $vars['pcmd']  : '';
	$refer = isset($vars['refer']) ? $vars['refer'] : '';
	$pass  = isset($vars['pass'])  ? $vars['pass']  : NULL;
	$page  = isset($vars['page'])  ? $vars['page']  : '';

	if ($refer != '' && is_pagename($refer)) {
		if(in_array($pcmd, array('info', 'open', 'list'))) {
			check_readable($refer);
		} else {
			check_editable($refer);
		}
	}

	// Dispatch
	if (isset($_FILES['attach_file'])) {
		// Upload
		return attach_upload($_FILES['attach_file'], $refer, $pass);
	} else {
		switch ($pcmd) {
		case 'delete':	/*FALLTHROUGH*/
		case 'freeze':
		case 'unfreeze':
			if (PKWK_READONLY) die_message($qm->m['fmt_err_pkwk_readonly']);
		}
		switch ($pcmd) {
		case 'info'     : return attach_info();
		case 'delete'   : return attach_delete();
		case 'open'     : return attach_open();
		case 'list'     : return attach_list();
		case 'freeze'   : return attach_freeze(TRUE);
		case 'unfreeze' : return attach_freeze(FALSE);
		case 'rename'   : return attach_rename();
		case 'upload'   : return attach_showform();
		}
		if ($page == '' || ! is_page($page)) {
			return attach_list();
		} else {
			return attach_showform();
		}
	}
}

//-------- call from skin
function attach_filelist($editmode=false)
{
	global $vars;
	$qm = get_qm();

	$page = isset($vars['page']) ? $vars['page'] : '';

	$obj = new AttachPages($page, 0);

	if (! isset($obj->pages[$page])) {
		return '';
	} else {
		return $qm->m['plg_attach']['file'] . ': ' .
		$obj->toString($page, TRUE, $editmode) . "\n";
	}
}

//-------- 実体
// ファイルアップロード
// $pass = NULL : パスワードが指定されていない
// $pass = TRUE : アップロード許可
function attach_upload($file, $page, $pass = NULL)
{
	global $notify, $notify_subject;
	$qm = get_qm();

	if (PKWK_READONLY) die_message($qm->m['fmt_err_pkwk_readonly']);

	// Check query-string
	$query = 'plugin=attach&amp;pcmd=info&amp;refer=' . rawurlencode($page) .
		'&amp;file=' . rawurlencode($file['name']);

	if (PKWK_QUERY_STRING_MAX && strlen($query) > PKWK_QUERY_STRING_MAX) {
		pkwk_common_headers();
		echo('Query string (page name and/or file name) too long');
		exit;
	} else if (! is_page($page)) {
		die_message($qm->m['fmt_err_nopage']);
	} else if ($file['tmp_name'] == '' || ! is_uploaded_file($file['tmp_name'])) {
		return array('result'=>FALSE);
	} else if ($file['size'] > PLUGIN_ATTACH_MAX_FILESIZE) {
		return array(
			'result'=>FALSE,
			'msg'=>$qm->m['plg_attach']['err_exceed']);
	} else if (! is_pagename($page) || ($pass !== TRUE && ! is_editable($page))) {
		return array(
			'result'=>FALSE,'
			msg'=>$qm->m['plg_attach']['err_noperm']);
	} else if (PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY && $pass !== TRUE &&
		  ($pass === NULL || ! pkwk_login($pass))) {
		return array(
			'result'=>FALSE,
			'msg'=>$qm->m['plg_attach']['err_adminpass']);
	}

	$obj = new AttachFile($page, $file['name']);
	if ($obj->exist)
		return array('result'=>FALSE,
			'msg'=>$qm->m['plg_attach']['err_exists']);

	if (move_uploaded_file($file['tmp_name'], $obj->filename))
		chmod($obj->filename, PLUGIN_ATTACH_FILE_MODE);

	if (is_page($page))
		touch(get_filename($page));

	$obj->getstatus();
	$obj->status['pass'] = ($pass !== TRUE && $pass !== NULL) ? md5($pass) : '';
	$obj->putstatus();

	if ($notify) {
		$footer['ACTION']   = 'File attached';
		$footer['FILENAME'] = & $file['name'];
		$footer['FILESIZE'] = & $file['size'];
		$footer['PAGE']     = & $page;

		$footer['URI']      = get_script_uri() .
			//'?' . rawurlencode($page);

			// MD5 may heavy
			'?plugin=attach' .
				'&refer=' . rawurlencode($page) .
				'&file='  . rawurlencode($file['name']) .
				'&pcmd=info';

		$footer['USER_AGENT']  = TRUE;
		$footer['REMOTE_ADDR'] = TRUE;

		pkwk_mail_notify($notify_subject, "\n", $footer) or
			die('pkwk_mail_notify(): Failed');
	}

	return array(
		'result'=>TRUE,
		'msg'=>$qm->m['plg_attach']['uploaded']);
}

// 詳細フォームを表示
function attach_info($err = '')
{
	global $vars;
	$qm = get_qm();
	
	foreach (array('refer', 'file', 'age') as $var)
		${$var} = isset($vars[$var]) ? $vars[$var] : '';

	$obj = new AttachFile($refer, $file, $age);
	return $obj->getstatus() ?
		$obj->info($err) :
		array('msg'=>$qm->m['plg_attach']['err_notfound']);
}

// 削除
function attach_delete()
{
	global $vars;
	$qm = get_qm();

	foreach (array('refer', 'file', 'age', 'pass') as $var)
		${$var} = isset($vars[$var]) ? $vars[$var] : '';

	if (is_freeze($refer) || ! is_editable($refer))
		return array('msg'=>$qm->m['plg_attach']['err_noperm']);

	$obj = new AttachFile($refer, $file, $age);
	if (! $obj->getstatus())
		return array('msg'=>$qm->m['plg_attach']['err_notfound']);
		
	return $obj->delete($pass);
}

// 凍結
function attach_freeze($freeze)
{
	global $vars;
	$qm = get_qm();

	foreach (array('refer', 'file', 'age', 'pass') as $var) {
		${$var} = isset($vars[$var]) ? $vars[$var] : '';
	}

	if (is_freeze($refer) || ! is_editable($refer)) {
		return array('msg'=>$qm->m['plg_attach']['err_noperm']);
	} else {
		$obj = new AttachFile($refer, $file, $age);
		return $obj->getstatus() ?
			$obj->freeze($freeze, $pass) :
			array('msg'=>$qm->m['plg_attach']['err_notfound']);
	}
}

// リネーム
function attach_rename()
{
	global $vars;
	$qm = get_qm();

	foreach (array('refer', 'file', 'age', 'pass', 'newname') as $var) {
		${$var} = isset($vars[$var]) ? $vars[$var] : '';
	}

	if (is_freeze($refer) || ! is_editable($refer)) {
		return array('msg'=>$qm->m['plg_attach']['err_noperm']);
	}
	$obj = new AttachFile($refer, $file, $age);
	if (! $obj->getstatus())
		return array('msg'=>$qm->m['plg_attach']['err_notfound']);

	return $obj->rename($pass, $newname);

}

// ダウンロード
function attach_open()
{
	global $vars;
	$qm = get_qm();

	foreach (array('refer', 'file', 'age') as $var) {
		${$var} = isset($vars[$var]) ? $vars[$var] : '';
	}

	$obj = new AttachFile($refer, $file, $age);
	return $obj->getstatus() ?
		$obj->open() :
		array('msg'=>$qm->m['plg_attach']['err_notfound']);
}

// 一覧取得
function attach_list()
{
	global $vars;
	$qm = get_qm();

	$refer = isset($vars['refer']) ? $vars['refer'] : '';

	$obj = new AttachPages($refer);

	$msg = $qm->m['plg_attach'][($refer == '') ? 'listall' : 'listpage'];
	$body = ($refer == '' || isset($obj->pages[$refer])) ?
		$obj->toString($refer, FALSE) :
		$qm->m['plg_attach']['err_noexist'];

	return array('msg'=>$msg, 'body'=>$body);
}

// アップロードフォームを表示 (action時)
function attach_showform()
{
	global $vars;
	$qm = get_qm();

	$page = isset($vars['page']) ? $vars['page'] : '';
	$vars['refer'] = $page;
	$body = attach_form($page);

	return array('msg'=>$qm->m['plg_attach']['upload'], 'body'=>$body);
}

//-------- サービス
// mime-typeの決定
function attach_mime_content_type($filename)
{
	$type = 'application/octet-stream'; // default

	if (! file_exists($filename)) return $type;

	$size = @getimagesize($filename);
	if (is_array($size)) {
		switch ($size[2]) {
			case 1: return 'image/gif';
			case 2: return 'image/jpeg';
			case 3: return 'image/png';
			case 4: return 'application/x-shockwave-flash';
		}
	}

	$matches = array();
	if (! preg_match('/_((?:[0-9A-F]{2})+)(?:\.\d+)?$/', $filename, $matches))
		return $type;

	$filename = decode($matches[1]);

	// mime-type一覧表を取得
	$config = new Config(PLUGIN_ATTACH_CONFIG_PAGE_MIME);
	$table = $config->read() ? $config->get('mime-type') : array();
	unset($config); // メモリ節約

	foreach ($table as $row) {
		$_type = trim($row[0]);
		$exts = preg_split('/\s+|,/', trim($row[1]), -1, PREG_SPLIT_NO_EMPTY);
		foreach ($exts as $ext) {
			if (preg_match("/\.$ext$/i", $filename)) return $_type;
		}
	}

	return $type;
}

// アップロードフォームの出力
function attach_form($page)
{
	global $script, $vars;
	$qm = get_qm();

	$r_page = rawurlencode($page);
	$s_page = htmlspecialchars($page);
	$navi = <<<EOD
  <span class="small">
   [<a href="$script?plugin=attach&amp;pcmd=list&amp;refer=$r_page">{$qm->m['plg_attach']['list']}</a>]
   [<a href="$script?plugin=attach&amp;pcmd=list">{$qm->m['plg_attach']['listall']}</a>]
  </span><br />
EOD;

	if (! ini_get('file_uploads')) return '#attach(): file_uploads disabled<br />' . $navi;
	if (! is_page($page))          return '#attach(): No such page<br />'          . $navi;

	$maxsize = PLUGIN_ATTACH_MAX_FILESIZE;
	$msg_maxsize = $qm->replace('plg_attach.maxsize', number_format($maxsize/1024) . 'KB');

	$pass = '';
	if (PLUGIN_ATTACH_PASSWORD_REQUIRE || PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY) {
		$title = $qm->m['plg_attach'][PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY ? 'adminpass' : 'password'];
		$pass = '<br />' . $title . ': <input type="password" name="pass" size="8" />';
	}
	return <<<EOD
<form enctype="multipart/form-data" action="$script" method="post">
 <div>
  <input type="hidden" name="plugin" value="attach" />
  <input type="hidden" name="pcmd"   value="post" />
  <input type="hidden" name="refer"  value="$s_page" />
  <input type="hidden" name="max_file_size" value="$maxsize" />
  $navi
  <span class="small">
   $msg_maxsize
  </span><br />
  <label for="_p_attach_file">{$qm->m['plg_attach']['file']}:</label> <input type="file" name="attach_file" id="_p_attach_file" />
  $pass
  <input type="submit" value="{$qm->m['plg_attach']['btn_upload']}" />
 </div>
</form>
EOD;
}

//-------- クラス
// ファイル
class AttachFile
{
	var $page, $file, $age, $basename, $filename, $logname;
	var $time = 0;
	var $size = 0;
	var $time_str = '';
	var $size_str = '';
	var $status = array('count'=>array(0), 'age'=>'', 'pass'=>'', 'freeze'=>FALSE);

	function AttachFile($page, $file, $age = 0)
	{
		$this->page = $page;
		$this->file = preg_replace('#^.*/#','',$file);
		$this->age  = is_numeric($age) ? $age : 0;

		$this->basename = UPLOAD_DIR . encode($page) . '_' . encode($this->file);
		$this->filename = $this->basename . ($age ? '.' . $age : '');
		$this->logname  = $this->basename . '.log';
		$this->exist    = file_exists($this->filename);
		$this->time     = $this->exist ? filemtime($this->filename) - LOCALZONE : 0;
		$this->md5hash  = $this->exist ? md5_file($this->filename) : '';
	}

	// ファイル情報取得
	function getstatus()
	{
		if (! $this->exist) return FALSE;

		// ログファイル取得
		if (file_exists($this->logname)) {
			$data = file($this->logname);
			foreach ($this->status as $key=>$value) {
				$this->status[$key] = chop(array_shift($data));
			}
			$this->status['count'] = explode(',', $this->status['count']);
		}
		$this->time_str = get_date('Y/m/d H:i:s', $this->time);
		$this->size     = filesize($this->filename);
		$this->size_str = sprintf('%01.1f', round($this->size/1024, 1)) . 'KB';
		$this->type     = attach_mime_content_type($this->filename);

		return TRUE;
	}

	// ステータス保存
	function putstatus()
	{
		$qm = get_qm();
		
		$this->status['count'] = join(',', $this->status['count']);
		$fp = fopen($this->logname, 'wb') or
			die_message($qm->replace('plg_attach.err_cannot_write', $this->logname));
		set_file_buffer($fp, 0);
		flock($fp, LOCK_EX);
		rewind($fp);
		foreach ($this->status as $key=>$value) {
			fwrite($fp, $value . "\n");
		}
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	// 日付の比較関数
	function datecomp($a, $b) {
		return ($a->time == $b->time) ? 0 : (($a->time > $b->time) ? -1 : 1);
	}

	function toString($showicon, $showinfo, $editmode=false)
	{
		global $script;
		$qm = get_qm();

		$this->getstatus();
		$param  = '&amp;file=' . rawurlencode($this->file) . '&amp;refer=' . rawurlencode($this->page) .
			($this->age ? '&amp;age=' . $this->age : '');
		$title = $this->time_str . ' ' . $this->size_str;
		$fname = htmlspecialchars($this->file);
		$label = ($showicon ? PLUGIN_ATTACH_FILE_ICON : '') . $fname ;
		if ($this->age) {
			$label .= ' (backup No.' . $this->age . ')';
		}
		$info = $count = '';
		if ($showinfo) {
			$_title = $qm->m['plg_attach']['info'];
			$info = "\n<span class=\"small\">[<a href=\"$script?plugin=attach&amp;pcmd=info$param\" title=\"$_title\">{$qm->m['plg_attach']['btn_info']}</a>]</span>\n";
			$count = ($showicon && ! empty($this->status['count'][$this->age])) ?
				$qm->replace('plg_attach.count', $this->status['count'][$this->age]) : '';
		}
		$imglink = "$script?plugin=attach&amp;pcmd=open$param";
		
		if($editmode){
			$insert_a = "<a href=\"#\" title=\"$fname\" onclick=\"javascript:jQuery.clickpad.cpInsert('&ref($fname,nolink,画像の説明);'); return false;\"><img src=\"image/ins-img.png\" alt=\"挿入\"/></a>";
			$insert_a2 = "<a href=\"#\" title=\"$fname\" onclick=\"javascript:jQuery.clickpad.cpInsert('\\n#ref($fname,nolink,around,left,画像の説明);\\n'); return false;\"><img src=\"image/ins-img2.png\" alt=\"挿入２\"/></a>";
			$sep = " | ";
		}
		else{
			$insert_a = ''; $insert_a2 = '';
			$sep = '';
		}
		return "<a href=\"$imglink\" title=\"$title\" rel=\"attachhref\">$label</a>$insert_a$insert_a2$info";
	}

	// 情報表示
	function info($err)
	{
		global $script;
		$qm = get_qm();

		$r_page = rawurlencode($this->page);
		$s_page = h($this->page);
		$s_file = h($this->file);
		$s_err = ($err == '') ? '' : '<p style="font-weight:bold">' . $qm->m['plg_attach'][$err] . '</p>';

		$msg_rename  = '';
		if ($this->age) {
			$msg_freezed = '';
			$msg_delete  = '<input type="radio" name="pcmd" id="_p_attach_delete" value="delete" />' .
				'<label for="_p_attach_delete">' .  $qm->m['plg_attach']['delete'] .
				$qm->m['plg_attach']['require'] . '</label><br />';
			$msg_freeze  = '';
		} else {
			if ($this->status['freeze']) {
				$msg_freezed = "<dd>{$qm->m['plg_attach']['isfreeze']}</dd>";
				$msg_delete  = '';
				$msg_freeze  = '<input type="radio" name="pcmd" id="_p_attach_unfreeze" value="unfreeze" />' .
					'<label for="_p_attach_unfreeze">' .  $qm->m['plg_attach']['unfreeze'] .
					$qm->m['plg_attach']['require'] . '</label><br />';
			} else {
				$msg_freezed = '';
				$msg_delete = '<input type="radio" name="pcmd" id="_p_attach_delete" value="delete" />' .
					'<label for="_p_attach_delete">' . $qm->m['plg_attach']['delete'];
				if (PLUGIN_ATTACH_DELETE_ADMIN_ONLY || $this->age)
					$msg_delete .= $qm->m['plg_attach']['require'];
				$msg_delete .= '</label><br />';
				$msg_freeze  = '<input type="radio" name="pcmd" id="_p_attach_freeze" value="freeze" />' .
					'<label for="_p_attach_freeze">' .  $qm->m['plg_attach']['freeze'] .
					$qm->m['plg_attach']['require'] . '</label><br />';

				if (PLUGIN_ATTACH_RENAME_ENABLE) {
					$msg_rename  = '<input type="radio" name="pcmd" id="_p_attach_rename" value="rename" />' .
						'<label for="_p_attach_rename">' .  $qm->m['plg_attach']['rename'] .
						$qm->m['plg_attach']['require'] . '</label><br />&nbsp;&nbsp;&nbsp;&nbsp;' .
						'<label for="_p_attach_newname">' . $qm->m['plg_attach']['newname'] .
						':</label> ' .
						'<input type="text" name="newname" id="_p_attach_newname" size="40" value="' .
						$this->file . '" /><br />';
				}
			}
		}
		$info = $this->toString(TRUE, FALSE);

		$retval = array('msg'=>sprintf($qm->m['plg_attach']['info'], htmlspecialchars($this->file)));
		$retval['body'] = <<< EOD
<p class="small">
 [<a href="$script?plugin=attach&amp;pcmd=list&amp;refer=$r_page">{$qm->m['plg_attach']['list']}</a>]
 [<a href="$script?plugin=attach&amp;pcmd=list">{$qm->m['plg_attach']['listall']}</a>]
</p>
<dl>
 <dt>$info</dt>
 <dd>{$qm->m['plg_attach']['page']}:$s_page</dd>
 <dd>{$qm->m['plg_attach']['filename']}:{$this->filename}</dd>
 <dd>{$qm->m['plg_attach']['md5hash']}:{$this->md5hash}</dd>
 <dd>{$qm->m['plg_attach']['filesize']}:{$this->size_str} ({$this->size} bytes)</dd>
 <dd>Content-type:{$this->type}</dd>
 <dd>{$qm->m['plg_attach']['date']}:{$this->time_str}</dd>
 <dd>{$qm->m['plg_attach']['dlcount']}:{$this->status['count'][$this->age]}</dd>
 $msg_freezed
</dl>
<hr />
$s_err
<form action="$script" method="post">
 <div>
  <input type="hidden" name="plugin" value="attach" />
  <input type="hidden" name="refer" value="$s_page" />
  <input type="hidden" name="file" value="$s_file" />
  <input type="hidden" name="age" value="{$this->age}" />
  $msg_delete
  $msg_freeze
  $msg_rename
  <br />
  <label for="_p_attach_password">{$qm->m['plg_attach']['password']}:</label>
  <input type="password" name="pass" id="_p_attach_password" size="8" />
  <input type="submit" value="{$qm->m['plg_attach']['btn_submit']}" />
 </div>
</form>
EOD;
		return $retval;
	}

	function delete($pass)
	{
		global $notify, $notify_subject;
		$qm = get_qm();

		if ($this->status['freeze']) return attach_info('isfreeze');

		if (! pkwk_login($pass)) {
			if (PLUGIN_ATTACH_DELETE_ADMIN_ONLY || $this->age) {
				return attach_info('err_adminpass');
			} else if (PLUGIN_ATTACH_PASSWORD_REQUIRE &&
				md5($pass) != $this->status['pass']) {
				return attach_info('err_password');
			}
		}

		// バックアップ
		if ($this->age ||
			(PLUGIN_ATTACH_DELETE_ADMIN_ONLY && PLUGIN_ATTACH_DELETE_ADMIN_NOBACKUP)) {
			@unlink($this->filename);
		} else {
			do {
				$age = ++$this->status['age'];
			} while (file_exists($this->basename . '.' . $age));

			if (! rename($this->basename,$this->basename . '.' . $age)) {
				// 削除失敗 why?
				return array('msg'=>$qm->m['plg_attach']['err_delete']);
			}

			$this->status['count'][$age] = $this->status['count'][0];
			$this->status['count'][0] = 0;
			$this->putstatus();
		}

		if (is_page($this->page))
			touch(get_filename($this->page));

		if ($notify) {
			$footer['ACTION']   = 'File deleted';
			$footer['FILENAME'] = & $this->file;
			$footer['PAGE']     = & $this->page;
			$footer['URI']      = get_script_uri() .
				'?' . rawurlencode($this->page);
			$footer['USER_AGENT']  = TRUE;
			$footer['REMOTE_ADDR'] = TRUE;
			pkwk_mail_notify($notify_subject, "\n", $footer) or
				die('pkwk_mail_notify(): Failed');
		}

		return array('msg'=>$qm->m['plg_attach']['deleted']);
	}

	function rename($pass, $newname)
	{
		global $notify, $notify_subject;
		$qm = get_qm();

		if ($this->status['freeze']) return attach_info('isfreeze');

		if (! pkwk_login($pass)) {
			if (PLUGIN_ATTACH_DELETE_ADMIN_ONLY || $this->age) {
				return attach_info('err_adminpass');
			} else if (PLUGIN_ATTACH_PASSWORD_REQUIRE &&
				md5($pass) != $this->status['pass']) {
				return attach_info('err_password');
			}
		}
		$newbase = UPLOAD_DIR . encode($this->page) . '_' . encode($newname);
		if (file_exists($newbase)) {
			return array('msg'=>$qm->m['plg_attach']['err_exists']);
		}
		if (! PLUGIN_ATTACH_RENAME_ENABLE || ! rename($this->basename, $newbase)) {
			return array('msg'=>$qm->m['plg_attach']['err_rename']);
		}

		return array('msg'=>$qm->m['plg_attach']['renamed']);
	}

	function freeze($freeze, $pass)
	{
		$qm = get_qm();
		
		if (! pkwk_login($pass)) return attach_info('err_adminpass');

		$this->getstatus();
		$this->status['freeze'] = $freeze;
		$this->putstatus();

		return array('msg'=>$qm->m['plg_attach'][$freeze ? 'freezed' : 'unfreezed']);
	}

	function open()
	{
		$this->getstatus();
		$this->status['count'][$this->age]++;
		$this->putstatus();
		$filename = $this->file;

		// Care for Japanese-character-included file name
		if (LANG == 'ja') {
			switch(UA_NAME . '/' . UA_PROFILE){
			case 'Opera/default':
				// Care for using _auto-encode-detecting_ function
				$filename = mb_convert_encoding($filename, 'UTF-8', 'auto');
				break;
			case 'MSIE/default':
				$filename = mb_convert_encoding($filename, 'SJIS', 'auto');
				break;
			}
		}
		$filename = htmlspecialchars($filename);

		ini_set('default_charset', '');
		mb_http_output('pass');

		pkwk_common_headers();
		header('Content-Disposition: inline; filename="' . $filename . '"');
		header('Content-Length: ' . $this->size);
		header('Content-Type: '   . $this->type);

		@readfile($this->filename);
		exit;
	}
}

// ファイルコンテナ
class AttachFiles
{
	var $page;
	var $files = array();

	function AttachFiles($page)
	{
		$this->page = $page;
	}

	function add($file, $age)
	{
		$this->files[$file][$age] = new AttachFile($this->page, $file, $age);
	}

	// ファイル一覧を取得
	function toString($flat, $editmode=false)
	{
		global $_title_cannotread;

		if (! check_readable($this->page, FALSE, FALSE)) {
			return str_replace('$1', make_pagelink($this->page), $_title_cannotread);
		} else if ($flat) {
			return $this->to_flat($editmode);
		}

		$ret = '';
		$files = array_keys($this->files);
		sort($files);

		foreach ($files as $file) {
			$_files = array();
			foreach (array_keys($this->files[$file]) as $age) {
				$_files[$age] = $this->files[$file][$age]->toString(FALSE, TRUE);
			}
			if (! isset($_files[0])) {
				$_files[0] = htmlspecialchars($file);
			}
			ksort($_files);
			$_file = $_files[0];
			unset($_files[0]);
			$ret .= " <li>$_file\n";
			if (count($_files)) {
				$ret .= "<ul>\n<li>" . join("</li>\n<li>", $_files) . "</li>\n</ul>\n";
			}
			$ret .= " </li>\n";
		}
		return make_pagelink($this->page) . "\n<ul>\n$ret</ul>\n";
	}

	// ファイル一覧を取得(inline)
	function to_flat($editmode=false)
	{
		$ret = '';
		$files = array();
		foreach (array_keys($this->files) as $file) {
			if (isset($this->files[$file][0])) {
				$files[$file] = & $this->files[$file][0];
			}
		}
		uasort($files, array('AttachFile', 'datecomp'));
		foreach (array_keys($files) as $file) {
			$ret .= $files[$file]->toString(TRUE, TRUE, $editmode) . ' ';
		}

		return $ret;
	}
}

// ページコンテナ
class AttachPages
{
	var $pages = array();

	function AttachPages($page = '', $age = NULL)
	{

		$dir = opendir(UPLOAD_DIR) or
			die('directory ' . UPLOAD_DIR . ' is not exist or not readable.');

		$page_pattern = ($page == '') ? '(?:[0-9A-F]{2})+' : preg_quote(encode($page), '/');
		$age_pattern = ($age === NULL) ?
			'(?:\.([0-9]+))?' : ($age ?  "\.($age)" : '');
		$pattern = "/^({$page_pattern})_((?:[0-9A-F]{2})+){$age_pattern}$/";

		$matches = array();
		while ($file = readdir($dir)) {
			if (! preg_match($pattern, $file, $matches))
				continue;

			$_page = decode($matches[1]);
			$_file = decode($matches[2]);
			$_age  = isset($matches[3]) ? $matches[3] : 0;
			if (! isset($this->pages[$_page])) {
				$this->pages[$_page] = new AttachFiles($_page);
			}
			$this->pages[$_page]->add($_file, $_age);
		}
		closedir($dir);
	}

	function toString($page = '', $flat = FALSE, $editmode=false)
	{
		if ($page != '') {
			if (! isset($this->pages[$page])) {
				return '';
			} else {
				return $this->pages[$page]->toString($flat, $editmode);
			}
		}
		$ret = '';

		$pages = array_keys($this->pages);
		sort($pages);

		foreach ($pages as $page) {
			if (check_non_list($page)) continue;
			$ret .= '<li>' . $this->pages[$page]->toString($flat) . '</li>' . "\n";
		}
		return "\n" . '<ul>' . "\n" . $ret . '</ul>' . "\n";
	}
}
?>
