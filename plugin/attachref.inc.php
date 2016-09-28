<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: attachref.inc.php,v 0.14 2003/10/08 04:10:29 sha Exp $
//

/*
*プラグイン attachref
 その場に添付する。attach & ref

*Usage
 &attachref;
 &attachref([<file>][,<ref options>][,button]);

*パラメータ
-<file>: attachすると自動的に追加される。最初から書いておいてもよい。
-<ref options>: &ref;用の引数。
-button: [attach]のようなリンクでなく、<form></form>のボタンにする。

*動作
(1)&attachref;を追加すると、[attach]ボタンが表示される。
(2)[attach]ボタンを押すと、アップロードフォームが現われて、指定したファイル
  を添付できる。
(3)添付されたファイルは&ref(...);で参照したように貼り付けられる。
(4)そのファイルを削除すると、"file not found"と[attach]ボタンが表示される。
(5)(4)のときに、そのファイルが追加されると再び復活する。

*/
// max file size for upload on PHP(PHP default 2MB)
ini_set('upload_max_filesize','2M');

// max file size for upload on script of PukiWiki(default 1MB)
define('MAX_FILESIZE',2000000);

// 管理者だけが添付ファイルをアップロードできるようにする
define('ATTACHREF_UPLOAD_ADMIN_ONLY',TRUE); // FALSE or TRUE
// アップロード/削除時にパスワードを要求する(ADMIN_ONLYが優先)
define('ATTACHREF_PASSWORD_REQUIRE',TRUE); // FALSE or TRUE


// upload dir(must set end of /) attach.inc.phpと合わせる
define('ATTACHREF_UPLOAD_DIR','./attach/');


function plugin_attachref_inline()
{
	global $script,$vars,$digest,$username;
	static $numbers = array();
	static $no_flag = 0;
	$qm = get_qm();

	if (!array_key_exists($vars['page'],$numbers))
	{
		$numbers[$vars['page']] = 0;
	}
	$attachref_no = $numbers[$vars['page']]++;

	//戻り値
	$ret = '';
	$dispattach = 1;
	$button = 0;

	$args = func_get_args();
    $btn_text = array_pop($args);
    $btn_text = $btn_text ? $btn_text : $qm->m['plg_attachref']['btn_submit'];

    //SWFUを持っている人用
    if( $username == $_SESSION['usr'] && has_swfu())
    {
    	$btn_text = '(swfu'.$btn_text.')';
    }

    $options = array();
    foreach ( $args as $opt ){
	    if ( $opt === 'button' ){
	        $button = 1;
	    }
	    else if ( $opt === 'number' ){
		$no_flag = 1;
	    }
	    else if ( $opt === 'nonumber' ){
		$no_flag = 0;
	    }
	    else {
	        array_push($options, $opt);
	    }
	}
    if ( $no_flag == 1 ) $btn_text .= "[$attachref_no]";
	$args = $options;
	if ( count($args) and $args[0]!='' )
	{

//		if( fopen($args[0], 'r') ){ // http:..., swfu/d/hogehoge.png.. などなど
		if( !is_url($args[0]) && file_exists($args[0]) ){ // http:..., swfu/d/hogehoge.png.. などなど
			require_once(PLUGIN_DIR."show.inc.php");
		    $params = plugin_show_body($args,$vars['page']);
		}
		else{
			require_once(PLUGIN_DIR."ref.inc.php");
		    $params = plugin_ref_body($args,$vars['page']);
		}

	    if ($params['_error'] != '') {
			$ret = $params['_error'];
			$dispattach = 1;
	    }
	    else
	    {
			$ret = $params['_body'];
			$dispattach = 0;
	    }

	}
	if ( $dispattach ) {
	    //XSS脆弱性問題 - 外部から来た変数をエスケープ
	    $s_args = trim(join(",", $args));
	    if ( $button ){
			$s_args .= ",button";
			$f_page = htmlspecialchars($vars['page']);
			$f_args = htmlspecialchars($s_args);
			$ret = <<<EOD
  <form action="$script" method="post">
  <div>
  <input type="hidden" name="encode_hint" value="ぷ" />
  <input type="hidden" name="attachref_no" value="$attachref_no" />
  <input type="hidden" name="attachref_opt" value="$f_args" />
  <input type="hidden" name="digest" value="$digest" />
  <input type="hidden" name="plugin" value="attachref" />
  <input type="hidden" name="refer" value="$f_page" />
  $ret
  <input type="submit" value="$btn_text" />
  </div>
  </form>
EOD;
	    }
	    else {
			$f_btn_text = preg_replace('/<[^<>]+>/','',$btn_text);
//		echo '[debug]btn=',$f_btn_text;
			$f_page = rawurlencode($vars['page']);
			$f_args = rawurlencode($s_args);
			$ret = <<<EOD
  $ret<a href="$script?plugin=attachref&amp;attachref_no=$attachref_no&amp;attachref_opt=$f_args&amp;refer=$f_page&amp;digest=$digest" title="$f_btn_text">$btn_text</a>
EOD;
	    }
	}
	return $ret;
}

function plugin_attachref_action()
{
	global $script,$vars,$username;
	global $html_transitional;
	$qm = get_qm();

	//check auth
	$editable = edit_auth($vars['refer'], FALSE, FALSE);
	if(!$editable){
		return array('msg'=>$qm->m['plg_attachref']['title_ntc_admin'],'body'=>'<p>'. $qm->m['plg_attachref']['ntc_admin']. '</p>');
	}

	//戻り値を初期化
	$retval['msg'] = $qm->m['plg_attachref']['title'];
	$retval['body'] = '';

	if (array_key_exists('attach_file',$_FILES)
		and array_key_exists('refer',$vars)
		and is_page($vars['refer']))
	{
		$file = $_FILES['attach_file'];
		$attachname = $file['name'];
		$filename = preg_replace('/\..+$/','', $attachname,1);


		//! swfuを持っていたら (管理者のみ)--------------------------------------------
	    if( $editable && has_swfu())
    	{

    		//アップロードするファイル名を決め（日本語ダメ、重複もダメ）
		 	$upload_name = $file['name'];
			if( preg_match('/^[-_.+a-zA-Z0-9]+$/', $upload_name ) ){
				while(!$overwrite && file_exists(SWFU_IMAGE_DIR.$upload_name)){
					$upload_name = 's_'.$upload_name;
				}
				$upload_file = SWFU_IMAGE_DIR.$upload_name;
				$fname = $upload_name;
				$disp = $qm->m['plg_attachref']['img_desc'];
			}
			else
			{
				$matches = array();

				if( !preg_match('/[^.]+\.(.*)$/', $upload_name, $matches) ){
					echo 'invalid file name : '.$upload_name;
					exit(0);
				}

				$ext = $matches[1];
				$tmp_name = tempnam(SWFU_IMAGE_DIR, 'auto_');
				$upname = $tmp_name.'.'.$ext;
				$disp = $upload_name;

				rename($tmp_name, $upname);
				$upload_file = SWFU_IMAGE_DIR. basename($upname);
				$fname = basename($upname);
			}

			move_uploaded_file($file['tmp_name'], $upload_file);
			chmod($upload_file, 0666);

			//regist db
			$stat = stat($upload_file);

			$data = array(
				'name'			=> $fname,
				'description'	=> $disp,
				'created'		=> $stat['mtime'],
				'size'			=> $stat['size'],
				'page_name'		=> $vars['refer'],
			);

			require_once(SWFU_TEXTSQL_PATH);
			$db = new CTextDB(SWFU_IMAGEDB_PATH);
			$db->insert($data);


    		$retval = attachref_insert_ref(SWFU_IMAGE_DIR.$fname);

    		return $retval;
	    }


		//すでに存在した場合、 ファイル名に'_0','_1',...を付けて回避(姑息)
		$count = '_0';
		while (file_exists(ATTACHREF_UPLOAD_DIR.encode($vars['refer']).'_'.encode($attachname)))
		{
			$attachname = preg_replace('/^[^\.]+/',$filename.$count++,$file['name']);
		}

		$file['name'] = $attachname;

		require_once(PLUGIN_DIR."attach.inc.php");
		if (!exist_plugin('attach') or !function_exists('attach_upload'))
		{
			return array('msg' => $qm->m['plg_attachref']['err_notfound']);
		}
		$pass = array_key_exists('pass',$vars) ? $vars['pass'] : NULL;

	    $retval = attach_upload($file,$vars['refer'],$pass);
		if ($retval['result'] == TRUE)
		{
			$retval = attachref_insert_ref($file['name']);
		}
	}
	else
	{
		$retval = attachref_showform();
		// XHTML 1.0 Transitional
		$html_transitional = TRUE;
	}
	return $retval;
}

function attachref_insert_ref($filename)
{
	global $script,$vars,$now,$do_backup;
	$qm = get_qm();

	$ret['msg'] = $qm->m['plg_attachref']['title'];

	$args = preg_split("/,/", $vars['attachref_opt']);
	if ( count($args) ){
	    $args[0] = $filename;//array_shift,unshiftって要するにこれね
	    $s_args = join(",", $args);
	}
	else {
	    $s_args = $filename;
	}
	$msg = "&attachref($s_args)";

	$refer = $vars['refer'];
	$digest = $vars['digest'];
	$postdata_old = get_source($refer);
	$thedigest = md5(join('',$postdata_old));

	$postdata = '';
	$attachref_ct = 0; //'#attachref'の出現回数
	$attachref_no = $vars['attachref_no'];
    $skipflag = 0;

	$is_box = false;
	$boxcnt = 0;
	$boxdata = array();

	foreach ($postdata_old as $line)
	{
	    if ($is_box == false && ( $skipflag || substr($line,0,1) == ' ' || substr($line,0,2) == '//') ){
			$postdata .= $line;
			continue;
	    }

	    if ($is_box == true && preg_match('/^\}\}/',$line)) {
			$postdata .= $line;
			$is_box = false;
			continue;
	    }

	    if ($is_box) {
	    	$boxdata[$boxcnt][] = $line;
	    	continue;
	    }

	    if ($is_box == false && preg_match('/^#.+\{\{$/',$line)) {
			$postdata .= $line;
			$is_box = true;
			$postdata .= '${box'. ++$boxcnt ."}\n";
			$boxdata[$boxcnt] = array();
			continue;
	    }

	    $ct = preg_match_all('/&attachref(?=[({;])/',$line, $out);
	    if ( $ct ){

		for($i=0; $i < $ct; $i++){
		    if ($attachref_ct++ == $attachref_no ){
				$line = preg_replace('/&attachref(\([^(){};]*\))?(\{[^{}]*\})?;/',$msg.'$2;',$line,1);
				$skipflag = 1;
				break;
		    }
		    else {
				$line = preg_replace('/&attachref(\([^(){};]*\))?(\{[^{}]*\})?;/','&___attachref$1$2___;',$line,1);
		    }
		}
		$line = preg_replace('/&___attachref(\([^(){};]*\))?(\{[^{}]*\})?___;/','&attachref$1$2;',$line);
	    }

	    $postdata .= $line;
	}

	foreach ($boxdata as $bi => $box) {
		$boxstr = '';
		foreach ($box as $line) {
		    if ( $skipflag || substr($line,0,1) == ' ' || substr($line,0,2) == '//' ){
				$boxstr .= $line;
				continue;
		    }

		    $ct = preg_match_all('/&attachref(?=[({;])/',$line, $out);
		    if ( $ct ){

			for($i=0; $i < $ct; $i++){
			    if ($attachref_ct++ == $attachref_no ){
					$line = preg_replace('/&attachref(\([^(){};]*\))?(\{[^{}]*\})?;/',$msg.'$2;',$line,1);
					$skipflag = 1;
					break;
			    }
			    else {
					$line = preg_replace('/&attachref(\([^(){};]*\))?(\{[^{}]*\})?;/','&___attachref$1$2___;',$line,1);
			    }
			}
			$line = preg_replace('/&___attachref(\([^(){};]*\))?(\{[^{}]*\})?___;/','&attachref$1$2;',$line);
		    }
		    $boxstr .= $line;

		}
		$postdata = str_replace('${box'.$bi.'}', trim($boxstr), $postdata);
	}

	// 更新の衝突を検出
	if ( $thedigest != $digest )
	{
		$ret['msg'] = $qm->m['fmt_title_collided'];
		$ret['body'] = $qm->m['plg_attachref']['collided'];
	}
	page_write($vars['refer'],$postdata);

	return $ret;
}
//アップロードフォームを表示
function attachref_showform()
{
	global $vars;
	$qm = get_qm();

	$vars['page'] = $vars['refer'];
	$body = ini_get('file_uploads') ? attachref_form($vars['page']) : 'file_uploads disabled.';

	return array('msg'=>$qm->m['plg_attach']['upload'],'body'=>$body);
}
//アップロードフォーム
function attachref_form($page)
{
	global $script,$vars;
	$qm = get_qm();

	$s_page = htmlspecialchars($page);

	$f_digest = array_key_exists('digest',$vars) ? $vars['digest'] : '';
	$f_no = (array_key_exists('attachref_no',$vars) and is_numeric($vars['attachref_no'])) ?
		$vars['attachref_no'] + 0 : 0;


	if (!(bool)ini_get('file_uploads'))
	{
		return "";
	}

	$maxsize = MAX_FILESIZE;
	$msg_maxsize = $qm->replace('plg_attach.maxsize', number_format($maxsize/1000)."KB");

	$pass = '';
	if (ATTACHREF_PASSWORD_REQUIRE or ATTACHREF_UPLOAD_ADMIN_ONLY)
	{
		$title = $qm->m['plg_attach'][ATTACHREF_UPLOAD_ADMIN_ONLY ? 'adminpass' : 'password'];
	}
	return <<<EOD
<form enctype="multipart/form-data" action="$script" method="post">
 <div>
  <input type="hidden" name="plugin" value="attachref" />
  <input type="hidden" name="pcmd" value="post" />
  <input type="hidden" name="attachref_no" value="$f_no" />
  <input type="hidden" name="attachref_opt" value="{$vars['attachref_opt']}" />
  <input type="hidden" name="digest" value="$f_digest" />
  <input type="hidden" name="refer" value="$s_page" />
  <input type="hidden" name="max_file_size" value="$maxsize" />
  <span class="small">
   $msg_maxsize
  </span><br />
  {$qm->m['plg_attach']['file']}: <input type="file" name="attach_file" />
  $pass
  <input type="submit" value="{$qm->m['plg_attach']['btn_upload']}" />
 </div>
</form>
EOD;
}
?>
