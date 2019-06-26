<?php
//mb_language("UTF-8");

error_reporting(E_ERROR | E_PARSE); // Avoid E_WARNING, E_NOTICE, etc
//error_reporting(E_ALL); // Debug purpose


define('PKWK_DTD_XHTML_1_0_TRANSITIONAL','');
define('DATA_HOME','');
if(file_exists('../pukiwiki.ini.php'))
	require_once('../pukiwiki.ini.php');

define('USER_NAME', $username);
define('SESSION_SAVE_PATH', $session_save_path);

define('SWFU_DATA_DIR','d/');
define('BASE','swfu/');

define('PREVIEW_SIZE', 80);

function config_database( &$db )
{
	$db->add( "", "", "", "", "", DBKIND_TEXTSQL );
}


function config_models( &$controller )
{
	$controller->AddModel( dirname(__FILE__) . "/models/admin.php" );
	$controller->AddModel( dirname(__FILE__) . "/models/image.php" );
}


function config_controller( &$controller )
{
	$controller->SetTemplateFile( "template.html" );

	$controller->set('is_modern_browser', is_modern_browser());
}


function InitTime( $time )
{
	$year	= substr( $time, 0, 4 );
	$month	= substr( $time, 4, 2 );
	$day	= substr( $time, 6, 2 );
	$hour	= substr( $time, 8, 2 );
	$minute	= substr( $time, 10, 2 );
	$second	= substr( $time, 12, 2 );
	return "$year-$month-$day $hour:$minute:$second";
}

if( !function_exists( "is_secure" ) )
{
	function is_secure( &$controller )
	{
   	 return true;
	}
}


function check_secure( &$controller )
{
    if( isset($_SESSION['usr']) && $_SESSION['usr'] == USER_NAME )
    {

    }
    else
    {
        $controller->redirect( "../" );
    }
}

function _getHost($init_url = '') {

		static $script;

		if( $init_url=='' )
		{
			//get
			if (isset($script)) return $script;

			//set automatically
	        foreach (array('SCRIPT_NAME', 'SERVER_ADMIN', 'SERVER_NAME',
	                'SERVER_PORT', 'SERVER_SOFTWARE') as $key) {
	                define($key, isset($_SERVER[$key]) ? $_SERVER[$key] : '');
	                unset(${$key}, $_SERVER[$key]);
	        }

	        $str = (SERVER_PORT == 443 ? 'https://' : 'http://'); // scheme
	        $str .= SERVER_NAME; // host
	        $str .= (SERVER_PORT == 80 ? '' : ':' . SERVER_PORT); // port
	        $str .= $_SERVER['REQUEST_URI'];

	        //親の親
	        $script =  dirname(dirname($str.'dummy'));
	    }
	    else
	    {
	    	$script = dirname($init_url.'dummy');
	    }

        return $script;
}

/**
* セキュアなセッションスタートの方法。
* ただし、QHMと連動しつつ、QHMコミュにも対応するために、ややこしい処理をしている
* ので注意が必要
*/
function secure_session_start()
{
	$vals = parse_url( _getHost().'/index.php' );

	if(TRUE){

		$domain = $vals['host'];

		if($domain != 'localhost' && $domain != '127.0.0.1'){
			if(isset($vals['port']))
			{
				$domain .= ':'.$vals['port'];
			}
			$dir = str_replace('\\', '', dirname( $vals['path'] ));
			$ckpath = ($dir=='/') ? '/' : $dir.'/';

			if( function_exists('ini_set') ){
				ini_set('session.use_trans_sid',0);
				ini_set('session.name', QHM_SESSION_NAME.strlen($ckpath));
				ini_set('session.use_only_cookies', 1);
				ini_set('session.cookie_path', $ckpath);
				ini_set('session.cookie_domain', $domain);
				ini_set('session.cookie_lifetime', 0);
			}
		}
	}
	if (SESSION_SAVE_PATH != '') {
		session_save_path('../'.SESSION_SAVE_PATH);
	}

	session_start();
}

function h($str){
	return htmlspecialchars($str);
}

function el($name, $mtime, $size, $thumb=true, $id='', $desc=''){

	$cmd = '';


	$class = '';
	if(preg_match('/.*(jpeg|png|gif|jpg)$/i',$name))
		$class = 'class="screenshot"';
	else
		$class = 'class="tooltip"';

	$value = "<span class=\"list_file_name\"><a href=\"view.php?id={$id}\" {$class} rel=\"d/{$name}?{$mtime}\" title=\"{$desc}\">{$name}</a></span>";

	$image = "";
	$icon = "";


	//image
	if( preg_match('/.*(jpeg|png|gif|jpg)$/i', $name) )
	{
		$icon = '<img src="images/image.png" alt="'.$desc.'" title="'.$desc.'" />';
		$info = getimagesize(SWFU_DATA_DIR.$name);
		if(!$thumb)
			$value .= '<br />('.$info[0].'x'.$info[1].')';
		$image = '<a href="view.php?id='.$id.'" '.$class.' rel="d/'.$name.'?'.$mtime.'"><img src="'.SWFU_DATA_DIR.$name.'?'.$mtime.'" alt="'.$name.'" style="width:100px" /></a><br />';
		$cmd = '&amp;ref2('.BASE.SWFU_DATA_DIR.$name.',nolink,画像の説明);';
	}
	//video
	else if( preg_match('/.*(swf|mov|ram|wmv|avi|flv)$/i',$name) )
	{
		$icon = '<img src="images/video.png" title="ビデオファイル" />';
		$cmd = '#playvideo('.BASE.SWFU_DATA_DIR.$name.',幅,高さ);';
	}
	//PDF
	else if( preg_match('/.*(pdf)$/i', $name) )
	{
		$icon = '<img src="images/pdf.png" title="PDFファイル" />';
		$cmd = '&amp;dlbutton('.BASE.SWFU_DATA_DIR.$name.');';
	}
	//compressed file
	else if( preg_match('/.*(zip|lzh|tgz|gz|rar|tar|bz2)$/i', $name) )
	{
		$icon = '<img src="images/archive.png" title="圧縮ファイル" />';
		$cmd = '&amp;dlbutton('.BASE.SWFU_DATA_DIR.$name.');';
	}
	//execute file
	else if( preg_match('/.*(exe|dmg)$/i', $name) )
	{
		$icon = '<img src="images/exe.png" title="実行ファイル" />';
		$cmd = '&amp;dlbutton('.BASE.SWFU_DATA_DIR.$name.');';
	}
	//office file
	else if( preg_match('/.*(doc|docx|xls|xlsx|ppt|pptx)$/i', $name) )
	{
		$icon = '<img src="images/office.png" title="オフィスファイル" />';
		$cmd = '&amp;dlbutton('.BASE.SWFU_DATA_DIR.$name.');';
	}
	//text file
	else if( preg_match('/.*(txt|text|html)$/i', $name) )
	{
		$icon = '<img src="images/text.png" title="テキストファイル" />';
		$cmd = '&amp;dlbutton('.BASE.SWFU_DATA_DIR.$name.');';
	}
	//script file
	else if( preg_match('/.*(java|php|cgi|c|cpp|sh|js)$/i', $name) )
	{
		$icon = '<img src="images/script.png" title="スクリプトファイル" />';
		$cmd = '&amp;dlbutton('.BASE.SWFU_DATA_DIR.$name.');';
	}
	else
	{
		$cmd = '&amp;dlbutton('.BASE.SWFU_DATA_DIR.$name.');';
	}




	$value = $icon.' '.$value;
	if(! $thumb)
		$value .= '<br /><font style="font-size:85%">'.date('Y年m月d日 H:i:s',$mtime).'</font><br /><br />';


	if($thumb){
		$value .= '<br />'.$image;
	}
	else{
		$value .= '<a href="view.php?dl='.$name.'"><img src="images/btn_download.png" title="ダウンロード" /></a>&nbsp;';
		$value .= '<a href="view.php?delete='.$name.'" onclick="return disp();"><img src="images/btn_delete.png" title="削除" /></a><br />';
	}

	return $value;
}

function set_menu(&$c)
{
	//recent_file & pae
	$res = $c->image->find('','created desc'); //var_dump($res);

	$rs = $c->admin->findone('$name=="recent_file"');
	$rct_fnum = $rs['value'];

	$rs = $c->admin->findone('$name=="recent_page"');
	$rct_pnum = $rs['value'];

	$recent_file = array();
	$recent_page = array();
	$end = count($res);

	$rcf_cnt = 0; $rcp_cnt = 0;
	for($i=0; $i<$end; $i++){

		//recent file count
		if($rcf_cnt < $rct_fnum){
			$recent_file[$i] = $res[$i];
			$rcf_cnt ++;
		}

		//recent page count
		if($rcp_cnt < $rct_pnum){

			$pname = $res[$i]['page_name'];
			if( $pname!='' && !isset($recent_page[$pname]))
			{
				$recent_page[$pname] = $pname;
				$rcp_cnt ++;
			}
		}
	}

	$c->set('recent_page',$recent_page);
	$c->set('recent_file',$recent_file);

}

function echo_menu($recent_file, $recent_page)
{

	if(isset($_SESSION['swfu']['page_name']))
	{
		$top = 'list.php?page='.rawurlencode($_SESSION['swfu']['page_name']);
		$uplink_query = '?page='.rawurlencode($_SESSION['swfu']['page_name']);
		$uplink_msg = $_SESSION['swfu']['page_name'];
		$uplink_msg_after = 'へ';
	}
	else{
		$top = 'index.php';
		$uplink_query = '';
		$uplink_msg = '';
		$uplink_msg_after = '';
	}
	$uploader = is_modern_browser()? 'up2.php': 'up.php';

	echo <<<EOD
<p style="line-height:1.7em;"><font style="font-size:0.9em;"><strong>{$uplink_msg}</strong>{$uplink_msg_after}</font><br />
<span style="background-color:#eee;border:1px solid #ccc;padding:5px;font-size:12px;font-weight:bold;"><a href="{$uploader}{$uplink_query}" id="upload_link">アップロード</a></span></p>
<br />
<p id="swfutop"><a href="$top">*&nbsp;トップへ&nbsp;*</a></p>
<p id="qhmtop"><a href="../">*&nbsp;HAIKトップへ&nbsp;*</a></p>

<h4>最近のファイル</h4>
<p style="margin-left:0.5em;">
EOD;

	foreach($recent_file as $k=>$v){
		$class = '';
		if(preg_match('/.*(jpeg|png|gif|jpg)$/i',$v['name']))
			$class = 'class="screenshot"';
		else
			$class = 'class="tooltip"';

		echo "<a href=\"view.php?id={$v['id']}\" {$class} rel=\"d/{$v['name']}\" title=\"{$v['description']}\">{$v['name']}</a><br />";
	}

	echo <<<EOD
</p>
<h4>最近のページ</h4>
<p style="margin-left:0.5em;">
EOD;

	foreach($recent_page as $k=>$v){
		$p = rawurlencode($k);
		echo "<a href=\"list.php?page={$p}\">{$v}</a><br />";
	}

	echo <<<EOD
</p>
<h4>一覧</h4>
<p style="margin-left:0.5em;">
<a href="pagelist.php">ページ一覧</a><br />
<a href="imagelist.php">ファイル一覧</a><br />
<a href="labellist.php">ラベル一覧</a><br />
</p>

<h4>検索</h4>
<form action="list.php" method="get">
<input type="text" size="12" name="search" value="" /><br />
<input type="submit" value="検索" />
</form>

<p><a href="check.php" style="font-size:0.8em">*ファイルのチェック*</a><br />
<a href="admin_settings.php" style="font-size:0.8em">*SWFUの設定*</a></p>

EOD;

}

function pr($v){echo '<pre>';var_dump($v);echo '</pre>';}


function format_bytes($size, $round = 1) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, $round) . $units[$i];
}

//function echo_insert_script($image)
/**
 *   Print Tool Buttons
 *
 *   @params
 *     $image <assoc>: a image record
 *     $print <boolean>: print buttons
 *
 */
function get_buttons($image, $br = true)
{
	$name = $image['name'];
	$desc = $image['description'];
	$desc = h(addcslashes($desc, "'"));
	$path = BASE.SWFU_DATA_DIR . $name;

	$btnimg = array(
		'show' => '<img src="images/btn_ref.png" alt="貼り付け" title="貼り付けコマンドを挿入" />',
		'show_ar' => '<img src="images/btn_ref_around.png" alt="回り込み貼り付け" title="回り込み貼り付けコマンドを挿入" />',
		'dlbtn' => '<img src="images/btn_dlbutton.png" alt="ダウンロードボタン" title="ダウンロードボタンコマンドを挿入" />',
		'dllnk' => '<img src="images/btn_dllink.png" alt="ダウンロードリンク" title="ダウンロードリンクコマンドを挿入" />',
		'lbx' => '<img src="images/btn_lightbx.png" alt="lightbox2" title="lightbox2コマンドを挿入" />',
		'gbx' => '<img src="images/btn_greybx.png" alt="greybox" title="greyboxコマンドを挿入" />',
		'video' => '<img src="images/btn_playvideo.png" alt="playvideo" title="playvideoコマンドを挿入" />',
		'music' => '<img src="images/btn_playlist.png" alt="playlist" title="playlistコマンドを挿入" />',
	);

	$buttons = '';
	//Image
	if(preg_match('/\.(jpeg|png|gif|jpg)$/i', $name)){
		$past = "&amp;show({$name},,{$desc});";
		$past_ard = "#show({$name},aroundr,,{$desc})";
		$lightbx2 = "&amp;show({$name},lightbox2=group,50%,{$desc});";
		$greybx = "&amp;show({$name},greybox=group,50%,{$desc});";
		$colorbox = "&amp;show({$name},colorbox=group,{$desc});";

		$buttons .= '<button type="button" onclick="insert_cmd(\''.$past.'\');" class="editmode button-paste">貼り付け</button>'. ($br? ' ': ' ');
		$buttons .= '<button type="button" onclick="insert_cmd(\''.$past_ard.'\');" class="editmode button-paste">回り込み</button>'. ($br? '<br />': ' ');
		$buttons .= '<button type="button" onclick="insert_cmd(\''. $colorbox .'\')" class="editmode button-paste">ポップアップ</button><br />';
	}
	/* ビデオファイル */
	else if( preg_match('/.*(swf|mov|ram|wmv|avi|flv)$/i',$name) ){
		$past = "#playvideo({$path},幅,高さ);";
		$buttons .= '<a href="#" class="editmode" onclick="insert_cmd(\''.$past.'\');">'.$btnimg['video'].'</a>'. ($br? '<br />': ' ');
	}
	/* mp3ファイル */
	else if( preg_match('/.*mp3$/i',$name) ){
		$past = '#playlist(タイトル){{\n'.$path.','.$image['description'].'\n}}\n';
		$buttons .= '<a href="#" class="editmode" onclick="insert_cmd(\''.$past.'\');">'.$btnimg['music'].'</a>'. ($br? '<br />': ' ');
	}

	$past = "&dlbutton({$path});";
	$buttons .= '<button type="button" onclick="insert_cmd(\''.$past.'\');" class="editmode button-paste">DLボタン</button> ';
	$past = "&dllink({$path}){ダウンロード};";
	$buttons .= '<button type="button" onclick="insert_cmd(\''.$past.'\');" class="editmode button-paste">DLリンク</button>';

	$past = basename($path);
	$buttons .= '<br><button type="button" class="editmode button-paste" onclick="insert_cmd(\''.$past.'\');">ファイル名</buton>';

	if ($print) {
		echo $buttons;
	}

	return $buttons;
}


/**
 *   jquery file Upload を利用できるかどうか
 *
 *   Google Chrome - 7.0, 8.0, 9.0, 10.0
 *   Apple Safari - 5.0 *1
 *   Mozilla Firefox - 3.6, 4.0
 *   Opera - 10.6 *2, 11.0 *2
 *   Microsoft Internet Explorer 6.0 *2, 7.0 *2, 8.0 *2, 9.0 *2
 *
 *   *1 Drag & Drop is not supported on the Windows version of Safari.
 *   *2 MSIE and Opera have no support for Drag & Drop, multiple file selection or upload progress indication.
 *
 *   see also http://aquantum-demo.appspot.com/file-upload
 */
function is_modern_browser() {
	$is_modern = true;
	$ua = $_SERVER['HTTP_USER_AGENT'];

	//chrome and firefox
	if (preg_match('/\s(Chrome|Firefox)\/(\d+)/', $ua, $mts)) {
		$browser = $mts[1];
		$version = $mts[2];
	}
	//Safari
	else if(preg_match('/\sVersion\/([\d.]+)\sSafari/', $ua, $mts)) {
		$browser = 'Safari';
		$version = $mts[1];
	}
	else {
		$is_modern = false;
	}

	if ($is_modern) {
		switch ($browser) {

			case 'Chrome':// Chrome v7-
				if ($version < 7) {
					$is_modern = false;
				}
				break;
			case 'Firefox':// Fiefox v3.6-
				if ($version < 3.6) {
					$is_modern = false;
				}
				break;
			case 'Safari':// Safari 5- (Mac)
				if ($version < 5) {
					$is_modern = false;
				} else if (strpos($ua, 'Macintosh') === FALSE) {
					$is_modern = false;
				}
				break;
			default:
				$is_modern = false;

		}
	}
	return $is_modern;
}

function numbering_filename($filename)
{
	$filename = basename($filename);
	while (file_exists(SWFU_DATA_DIR . $filename))
	{
		if (preg_match('/^(.*?)(?:_(\d+))?\.([^.]+)$/', $filename, $mts))
		{
			$num = isset($mts[2]) ? (int)$mts[2] : 1;
			$filename = $mts[1] . '_' . ($num+1) . '.'.$mts[3];
		}
		else if (preg_match('/^(.*_)(\d+)$/', $filename, $mts))
		{
			$num = $mts[2];
			$filename = $mts[1] . ($num+1);
		}
		else
		{
			$filename .= '_2';
		}
	}
	return $filename;
}

if ( ! function_exists('get_qhm_option'))
{
	function get_qhm_option($key = NULL)
	{
		static $options = array();
		if (empty($options))
		{
			$init_str = file_get_contents('../lib/init.php');
			$qhm_options = preg_match("/^define\('QHM_OPTIONS', '(.*?)'/m", $init_str, $mts)
				? $mts[1]
				: '';
			$option_statements = explode(';', $qhm_options);
			foreach ($option_statements as $statement)
			{
				list($_key, $_value) = explode('=', $statement, 2);
				$_key = trim($_key);
				$_value = trim($_value);
				// type cast
				if ($_value === 'true') $_value = true;
				else if ($_value === 'false') $_value = false;
				else if (is_numeric($_value)) $_value = (float)$_value;

				$options[$_key] = $_value;
			}
		}
		if ($key === NULL)
		{
			return $options;
		}
		else if (array_key_exists($key, $options))
		{
			return $options[$key];
		}
		return NULL;
	}
}

if ( ! function_exists('get_extension_blacklist')) {
	/*
	 * PHP, CGI, Pearl スクリプトはアップロード許可しない
	 */
	function get_extension_blacklist()
	{
		return array(
			'php', 'cgi', 'pl'
		);
	}
}

if ( ! function_exists('is_valid_file_for_upload')) {
	/*
	 * アップロードしても良いファイルかどうか判別する
	 */
	function is_valid_file_for_upload($filename)
	{
		return ! in_array(pathinfo($filename, PATHINFO_EXTENSION), get_extension_blacklist());
	}
}

//--------------------------------
//main
if ( isset($script) && $script != '') {
	_getHost($script); // Init matically
}
