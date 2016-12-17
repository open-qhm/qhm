<?php
/**
 *   QHM Show Plugin
 *   -------------------------------------------
 *   show.inc.php
 *
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2010-09-14
 *   modified :
 *
 *   Image showing plugin
 *
 *   Usage :
 *
 */

/////////////////////////////////////////////////
// Default settings

// Horizontal alignment
define('PLUGIN_SHOW_DEFAULT_ALIGN', 'left'); // 'left', 'center', 'right'

// Text wrapping
define('PLUGIN_SHOW_WRAP_TABLE', FALSE); // TRUE, FALSE

// URL指定時に画像サイズを取得するか
define('PLUGIN_SHOW_URL_GET_IMAGE_SIZE', FALSE); // FALSE, TRUE

// UPLOAD_DIR のデータ(画像ファイルのみ)に直接アクセスさせる
define('PLUGIN_SHOW_DIRECT_ACCESS', FALSE); // FALSE or TRUE
// - これは従来のインラインイメージ処理を互換のために残すもので
//   あり、高速化のためのオプションではありません
// - UPLOAD_DIR をWebサーバー上に露出させており、かつ直接アクセス
//   できる(アクセス制限がない)状態である必要があります
// - Apache などでは UPLOAD_DIR/.htaccess を削除する必要があります
// - ブラウザによってはインラインイメージの表示や、「インライン
//   イメージだけを表示」させた時などに不具合が出る場合があります

/////////////////////////////////////////////////

// Image suffixes allowed
define('PLUGIN_SHOW_IMAGE', '/\.(gif|png|jpe?g)$/i');

// Usage (a part of)
define('PLUGIN_SHOW_USAGE', "(attached-file-path[,parameters, ... ][,title])");

// Max file size for upload on script of PukiWikiX_FILESIZE
define('PLUGIN_SHOW_MAX_FILESIZE', (1024 * 1024 * 5)); // default: 5MB

function plugin_show_inline()
{
	global $script,$vars,$digest,$username;
	static $numbers = array();

	$qm = get_qm();
	$args = func_get_args();

	//添付ファイル番号を付ける
	if (!array_key_exists($vars['page'],$numbers))
	{
		$numbers[$vars['page']] = 0;
	}
	$show_no = $numbers[$vars['page']]++;


	$btn_text = $qm->m['plg_attachref']['btn_submit'];

	$editable = edit_auth($vars['page'], FALSE, FALSE);
	if ($editable && has_swfu())
	{
		$btn_text = '('.$btn_text.'-swfu)'; // SWFUを持っている人用
	}

	//添付用のリンクを表示
	if ($args[0]=='')
	{

		if (isset($vars['page_alt']))
		{
			return '<a href="'.$script.'?'.rawurlencode($vars['page_alt']).'" style="font-size:80%;">'.$qm->m['plg_attachref']['btn_submit_alt'].'</a>';
		}

		$s_args = trim( rtrim(join(",", $args),',') );
		$f_page = rawurlencode( $vars['page'] );
		$f_args = rawurlencode($s_args);
		$ret = <<<EOD
<a href="$script?plugin=show&amp;show_no=$show_no&amp;show_opt=$f_args&amp;refer=$f_page&amp;digest=$digest" title="$btn_text" style="display:inline-block;width:300px;height:200px;background-color:#eee;text-align:center;line-height:200px;text-decoration:none;" class="img-rounded text-muted">300 x 200</a>
EOD;
		return $ret;
	}

	$params = plugin_show_body($args);
	//error check
	if (isset($params['_error']) && $params['_error'] != '') {

		//attachフォルダにないか確認
		require_once(PLUGIN_DIR."ref.inc.php");
		$params = plugin_ref_body($args,$vars['page']);

		if (isset($params['_error']) && $params['_error'] != '') {
			// Error
			return '&amp;show(): ' . $params['_error'] . ';';
		}
	}

	return $params['_body'];
}

function plugin_show_convert()
{
	$qm = get_qm();
	$qt = get_qt();

	if (! func_num_args())
		return $qm->m['plg_show']['err_usage'];

	$args = func_get_args();
	$args[] = '_block';
	$params = plugin_show_body($args);

	if (isset($params['_error']) && $params['_error'] != '') {
		return "<p>#show(): {$params['_error']}</p>\n";
	}

	if ((PLUGIN_SHOW_WRAP_TABLE && ! $params['nowrap']) || $params['wrap']) {
		// 枠で包む
		// margin:auto
		//	Mozilla 1.x  = x (wrap,aroundが効かない)
		//	Opera 6      = o
		//	Netscape 6   = x (wrap,aroundが効かない)
		//	IE 6         = x (wrap,aroundが効かない)
		// margin:0px
		//	Mozilla 1.x  = x (wrapで寄せが効かない)
		//	Opera 6      = x (wrapで寄せが効かない)
		//	Netscape 6   = x (wrapで寄せが効かない)
		//	IE6          = o
		$margin = ($params['around'] ? '0px' : 'auto');
		$margin_align = ($params['_align'] == 'center') ? '' : ";margin-{$params['_align']}:0px";
		$params['_body'] = <<<EOD
<table class="style_table" style="margin:$margin$margin_align">
 <tr>
  <td class="style_td">{$params['_body']}</td>
 </tr>
</table>
EOD;
	}

	$style_ard = '';
	$style = '';
	if ($params['around']) {
		$param_ard = ($params['_align'] == 'right') ? 'right' : 'left';
		$style_ard = "_" . $param_ard;

	} else {
		$style = "text-align:{$params['_align']}";
	}

	$add_style = <<< EOD
<style>
.img_margin_left {
  float: left;
  margin: 0 1em 0 0;
}
.img_margin_right {
  float: right;
  margin: 0 0 0 1em;
}
</style>
EOD;
	if ($is_bootstrap_skin)
	{
		$add_style .= <<< EOD
<script>
$(function(){
  if ($("[class^=img_margin_]").length) {
    $("[class^=img_margin_]").each(function(){
      var prevMarginBottom = 0;
      var nextMarginTop = 0;

      if ($(this).prev().length) {
        prevMarginBottom = parseInt($(this).prev().css("margin-bottom").replace("px", ""), 10);
      }
      else {
        $(this).css({marginTop: 0});
      }

      if ($(this).next().length) {
        nextMarginTop = parseInt($(this).next().css("margin-top").replace("px", ""), 10);
      }

      var marginTop = nextMarginTop - prevMarginBottom;
      marginTop = (marginTop < 0) ? 0 : marginTop;

      $(this).css({marginTop: marginTop});
    });
  }
});
</script>
EOD;
	}
	$qt->appendv_once("plugin_show_convert_style", "beforescript", $add_style);

	// divで包む
	return "<div class=\"img_margin$style_ard\" style=\"$style\">{$params['_body']}</div>\n";
}

function plugin_show_body($args)
{
	global $script, $vars;
	global $WikiName, $BracketName; // compat
	$qm = get_qm();
	$qt = get_qt();
	$qt->setv('jquery_include', true);

	// 戻り値
	$params = array(
		'left'      => FALSE, // 左寄せ
		'center'    => FALSE, // 中央寄せ
		'right'     => FALSE, // 右寄せ
		'aroundl'   => FALSE, //回り込み左寄せ
		'aroundc'   => FALSE, //回り込み中央寄せ
		'aroundr'   => FALSE, //回り込み右寄せ
		'wrap'      => FALSE, // TABLEで囲む
		'nowrap'    => FALSE, // TABLEで囲まない
		'around'    => FALSE, // 回り込み
		'noicon'    => FALSE, // アイコンを表示しない
		'nolink'    => TRUE,  // 元ファイルへのリンクを張らない
		'greybox'   => FALSE, //greybox で表示
		'lightbox2' => FALSE, //lightbox2 で表示
		'colorbox'  => FALSE, //colorbox で表示
		'normal'    => FALSE, //画像へのリンクを付ける
		'linkurl'   => FALSE, //greybox, lightbox2, normal のリンク先
		'label'     => FALSE, //labelを指定すると強制的に表示をlabelにする(greybox, lightbox2想定)。画像ファイルを指定するとそれを表示する。
		'noimg'     => FALSE, // 画像を展開しない
		'zoom'      => FALSE, // 縦横比を保持する
		'change'    => FALSE, // マウスオーバーで、画像を切り替える
		'_size'     => FALSE, // サイズ指定あり
		'_w'        => 0,     // 幅
		'_h'        => 0,     // 高さ
		'_%'        => 0,     // 拡大率
		'_ratio'    => FALSE, // 縦横比
		'_args'     => array(),
		'_done'     => FALSE,
		'_error'    => '',
		'_block'    => FALSE, // convert called
	);

	$is_bootstrap_skin = is_bootstrap_skin();
	$bs_image_deco = array (
		'circle'    => FALSE, // .img-circle by bootstrap
		'rounded'   => FALSE, // .img-rounded by bootstrap
		'round'     => FALSE, // .img-rounded by bootstrap
		'thumbnail' => FALSE, // .img-thumbnail by bootstrap
		'pola'      => FALSE, // .img-thumbnail by bootstrap
		'polaroid ' => FALSE, // .img-thumbnail by bootstrap
	);

	if ($is_bootstrap_skin)
	{
		foreach ($bs_image_deco as $key => $val)
		{
			$params[$key] = $val;
		}
	}

	// 添付ファイルのあるページ: defaultは現在のページ名
	$page = isset($vars['page']) ? $vars['page'] : '';

	// 添付ファイルのファイル名
	$name = '';

	// 添付ファイルまでのパスおよび(実際の)ファイル名
	$file = '';

	// 第一引数: "画像ファイル名"、あるいは"画像ファイルパス"、あるいは"画像ファイルURL"を指定
	$name = array_shift($args);
	$is_url = is_url($name, false, true);

	//画像ファイルかどうか
	if ( ! preg_match(PLUGIN_SHOW_IMAGE, $name))
	{
		$params['_error'] = $qm->replace('plg_show.err_noimg', h($name));
	}

	if ( ! $is_url)
	{
		$file = $name;
		if ( ! is_file($file))
		{
			$file = SWFU_IMAGE_DIR . $file;
			if ( ! is_file($file))
			{
				$params['_error'] = $qm->replace('plg_show.err_notfound', h($name));
				return $params;
			}
		}
	}

	// 残りの引数の処理
	if ( ! empty($args))
	{
		foreach ($args as $arg) {
			plugin_show_check_arg($arg, $params);
		}
	}

	if (is_page($params['linkurl']))
	{
		$params['linkurl'] = $script. '?'. rawurlencode($params['linkurl']);
	}

	/*
	$nameをもとに以下の変数を設定
	$url,$url2 : URL
	$title :タイトル
	$info : 画像ファイルのときgetimagesize()の'size'
		画像ファイル以外のファイルの情報
		添付ファイルのとき : ファイルの最終更新日とサイズ
		URLのとき : URLそのもの
	*/

	$title = $url = $url2 = $info = $style = '';
	$width = $height = 0;
	$matches = array();

	if ($is_url) {	// URL
		if (PKWK_DISABLE_INLINE_IMAGE_FROM_URI) {
			$url = h($name);
			$params['_body'] = '<a href="' . $url . '">' . $url . '</a>';
			return $params;
		}

		$url = $url2 = h($name);
		$title = h(preg_match('/\/(.+?)$/', $name, $matches) ? $matches[1] : $url);

		if (PLUGIN_SHOW_URL_GET_IMAGE_SIZE && (bool)ini_get('allow_url_fopen')) {
			$size = @getimagesize($name);
			if (is_array($size)) {
				$width  = $size[0];
				$height = $size[1];
				$info   = $size[3];
			}
		}

	} else { // 添付ファイル

		$title = h($name);

/*
		// Count downloads with attach plugin
		$url = $script . '?plugin=attach' . '&amp;refer=' . rawurlencode($page) .
			'&amp;openfile=' . rawurlencode($name); // Show its filename at the last
*/

		$file = (substr($file, 0, 2)== './') ? substr($file, 2) : $file;
		$url = $url2 = $file;

		$width = $height = 0;
		$size = @getimagesize($file);
		if (is_array($size)) {
			$width  = $size[0];
			$height = $size[1];
		}
	}

	//first Image をセット
	$qt->set_first_image($is_url? $url: (dirname($script). '/'. $url));

	// 拡張パラメータをチェック
	if (! empty($params['_args'])) {
		$_title = array();
		foreach ($params['_args'] as $arg) {
			if (preg_match('/^([0-9]+)x([0-9]+)$/', $arg, $matches)) {
				$params['_size'] = TRUE;
				$params['_w'] = $matches[1];
				$params['_h'] = $matches[2];

			} else if (preg_match('/^([0-9.]+)%$/', $arg, $matches) && $matches[1] > 0) {
				$params['_%'] = $matches[1];

			} else {
				$_title[] = $arg;
			}
		}

		if (! empty($_title)) {
			$title = h(join(',', $_title));
			$title = make_line_rules($title);
		}
	}

	// 画像サイズ調整
	// 指定されたサイズを使用する
	if ($params['_size']) {
		if ($width == 0 && $height == 0) {
			$width  = $params['_w'];
			$height = $params['_h'];
		} else if ($params['zoom']) {
			$_w = $params['_w'] ? $width  / $params['_w'] : 0;
			$_h = $params['_h'] ? $height / $params['_h'] : 0;
			$zoom = max($_w, $_h);
			if ($zoom) {
				$width  = (int)($width  / $zoom);
				$height = (int)($height / $zoom);
			}
		} else {
			$width  = $params['_w'] ? $params['_w'] : $width;
			$height = $params['_h'] ? $params['_h'] : $height;
		}
	}
	if ($params['_%']) {
		$width  = (int)($width  * $params['_%'] / 100);
		$height = (int)($height * $params['_%'] / 100);
	}
	if ($params['_size'] OR $params['_%'])
	{
		if ($width && $height)
		{
			$info = "width=\"$width\" height=\"$height\" ";

			// 表示領域が画像の幅よりも小さい場合
			// 縦横日が崩れるので修正する JavaScript を出力する
			$params['class'] .= ' qhm-plugin-show-keep-ratio';
			$params['class'] .= $params['_size'] ? ' qhm-plugin-show-size-given' : '';
			plugin_show_set_keep_ratio();
		}
	}

	if ($is_bootstrap_skin)
	{
		$params['class'] .= $params['_block'] ? ' img-responsive' : '';
	}
	else
	{
		$style = 'style="max-width:100%;" ';
	}

	// アラインメント判定
	$params['_align'] = PLUGIN_SHOW_DEFAULT_ALIGN;
	foreach (array('right', 'left', 'center', 'aroundr', 'aroundc', 'aroundl') as $align) {
		//aroundx
		if (strpos($align, 'around') === 0 && $params[$align]) {
			$params['around'] = TRUE;
			$align = substr($align, -1, 1);
			switch ($align) {
				case 'r':
					$align = 'right';
					break;
				case 'c':
					$align = 'center';
					break;
				default:
					$align = 'left';
			}
			$params['_align'] = $align;
			break;
		}
		else if ($params[$align])  {
			$params['_align'] = $align;
			break;
		}
	}

	$mouseover = '';
	if ( $params['change'] ){
		$a_path = explode('.', $url);
		$a_path[ (count($a_path)-2) ] .= '_onmouse';
		$mo_url = join('.', $a_path);
		$mouseover = " onmouseover=\"this.src='{$mo_url}'\" onmouseout=\"this.src='{$url}'\" ";
		$mouseover .= 'onload="qhm_preload(\''. $mo_url .'\');"';

		//preload
		$addscript = '
<script type="text/javascript">
var qhm_preloaded = {};
function qhm_preload(src) {
	if (typeof document.images != "undefined" && typeof qhm_preloaded[src] == "undefined") {
		var img = new Image();
		img.src = src;
		qhm_preloaded[src] = 1;
	}
}
</script>
';
		$qt->appendv_once('plugin_show_preload', 'beforescript', $addscript);
	}

	$aclass = $arel = '';
	$url2 = $params['linkurl']? $params['linkurl']: $url2;

	//異なるURLが2つある場合、nolink をFALSEに
	if ($url2 != $url) {
		$params['nolink'] = FALSE;
	}

	//表示設定
	if ( $params['greybox'] ){
		$addscript = '
<script type="text/javascript">
	var GB_ROOT_DIR = "./plugin/greybox/";
</script>
<script type="text/javascript" src="./plugin/greybox/AJS.js"></script>
<script type="text/javascript" src="./plugin/greybox/AJS_fx.js"></script>
<script type="text/javascript" src="./plugin/greybox/gb_scripts.js"></script>
<link href="./plugin/greybox/gb_styles.css" rel="stylesheet" type="text/css" />
';
		$qt->appendv_once('plugin_greybox', 'beforescript', $addscript);

		//文字列の場合：グループ
		if ($params['greybox'] !== TRUE) {
			$gb_type = ($url == $url2)? 'imageset': 'pageset';
			$gb_grp = $params['greybox'];
			$arel = ' rel="gb_'. $gb_type.'['. $gb_grp.']"';
		} else {
			$gb_type = ($url == $url2)? 'image': 'page_fs';
			$arel = ' rel="gb_'. $gb_type.'[]"';
		}
		$params['nolink'] = FALSE;
	}
	else if( $params['lightbox2'] ){

		require_once(PLUGIN_DIR. '/lightbox2.inc.php');

		$addscript = '
<script type="text/javascript" src="js/jquery.dimensions.min.js"></script>
<script type="text/javascript" src="js/jquery.dropshadow.js"></script>
<script type="text/javascript" src="'.LIGHTBOX2_LIB.'/js/jquery.lightbox.js"></script>
<link href="'.LIGHTBOX2_LIB.'/css/lightbox.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
$(document).ready(function(){
	$(".lightbox").lightbox();
});
</script>
';
		$qt->appendv_once('plugin_lightbox2', 'beforescript', $addscript);
		$aclass = ' class="lightbox"';

		if ($params['lightbox2'] !== TRUE) {
			$arel = ' rel="'. $params['lightbox2'].'"';
		}


		//$url2 が画像ファイルでない場合、無効にする|エラーを出す
		if (!preg_match(PLUGIN_SHOW_IMAGE, $url2)) {
			$url2 = $url;
//			$params['_error'] = 'lightbox2 cannot show except images';
		}
		$params['nolink'] = FALSE;
	}
	else if ($params['colorbox'])
	{
		$addscript = '
<script type="text/javascript" src="./plugin/colorbox/jquery.colorbox-min.js"></script>
<link href="./plugin/colorbox/colorbox.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
$(function(){
	var options = {
		opacity:0.5,
		current: "{current}/{total}",
		maxWidth: "100%",
		maxHeight: "90%"
	};
	var slideshowOptions = $.extend({}, options, {
		opacity: 0.8,
		slideshow: true
	});

	$("a.colorbox").colorbox(options);
	$("a.colorbox_slideshow").colorbox(slideshowOptions);
});
</script>
';
		$qt->appendv_once('plugin_show_colorbox', 'beforescript', $addscript);

		//文字列の場合：グループ
		//グループ名がslideshow で始まる場合、スライドショー
		if (substr($params['colorbox'], 0, 9) === 'slideshow')
		{
			$aclass = ' class="colorbox_slideshow"';
			$gb_grp = $params['colorbox'];
			$arel = ' rel="cb_'. $gb_grp.'"';
		}
		else if ($params['colorbox'] !== TRUE) {
			$gb_type = ($url == $url2)? 'imageset': 'pageset';
			$gb_grp = $params['colorbox'];
			$arel = ' rel="cb_'. $gb_grp.'"';
			$aclass = ' class="colorbox"';
		} else {
			$aclass = ' class="colorbox"';
			//
		}
		$params['nolink'] = FALSE;
	}
	else if ($params['normal'])
	{
		$params['nolink'] = FALSE;
	}

	if ($is_bootstrap_skin)
	{
		$img_thumbnail = false;
		$img_rounded = false;
		$img_circle  = false;
		foreach (array_keys($bs_image_deco) as $deco)
		{
			if (isset($params[$deco]) && $params[$deco] !== FALSE)
			{
				switch ($deco)
				{
					case 'pola':
					case 'polaroid':
					case 'thumbnail':
						if ( ! $img_thumbnail)
						{
							$params['class'] .= ' img-thumbnail';
							$img_thumbnail = true;
						}
						break;
					case 'round':
					case 'rounded':
						if ( ! $img_rounded)
						{
							$params['class'] .= ' img-rounded';
							$img_rounded = true;
						}
						break;
					case 'circle':
						if ( ! $img_circle)
						{
							$params['class'] .= ' img-circle';
							$img_circle = true;
						}
						break;
				}
			}
		}
	}
	//指定されたクラスを追加する
	$imgclass = ' class="' . h($params['class']) . '"';


	if($params['label']!==FALSE && $params['label']!=''){
		//画像を指定した場合、画像を表示する
		if (preg_match('/\.(jpe?g|gif|png)$/', $params['label'])) {
			$url = plugin_show_get_filepath($params['label']);
			$size = plugin_show_get_imagesize($params['label']);
			//高さと幅を指定している場合はそちらを利用する
			if ($params['_w'] === 0 && $params['_h'] === 0 && $size !== FALSE)
			{
				$info = '';
				if (is_array($size)) {
					$width  = $size[0];
					$height = $size[1];
					if ($params['_%'] !== 0)
					{
						$width = floor($width * $params['_%'] / 100);
						$height = floor($height * $params['_%'] / 100);
					}
					$info = "width=\"{$width}\" height=\"{$height}\"";
				}
				$params['_body'] = "<img src=\"$url\" alt=\"$title\" title=\"$title\" $info $mouseover $style{$imgclass}{$ratio_attr}>";
			}
			$params['_body'] = "<img src=\"$url\" alt=\"$title\" title=\"$title\" $info $mouseover $style{$imgclass}{$ratio_attr}>";
		}
		else
		{
			$params['_body'] = h($params['label']);
		}
	}
	else{
		$params['_body'] = "<img src=\"$url\" alt=\"$title\" title=\"$title\" $info $mouseover $style{$imgclass}{$ratio_attr}>";
	}

	if (! $params['nolink'] && $url2)
		$params['_body'] = "<a href=\"$url2\" title=\"$title\"$aclass$arel>{$params['_body']}</a>";

	return $params;
}

// オプションを解析する
function plugin_show_check_arg($val, & $params)
{
	global $script;

	if ($val === '_block')
	{
		$params['_block'] = TRUE;
		return;
	}

	if ($val == '') {
		$params['_done'] = TRUE;
		return;
	}


	if (! $params['_done']) {
		foreach (array_keys($params) as $key) {
			if (strpos(strtolower($val), $key) === 0) {
				if (strpos($val, '=')) {
					list($optkey, $optval) = explode('=', $val, 2);
					$params[$key] = $optval;
				} else {
					$params[$key] = TRUE;
				}
				return;
			}
		}
		$params['_done'] = TRUE;
	}

	if (is_url($val)) {
		$params['linkurl'] = $val;
	} else {
		$params['_args'][] = $val;
	}
}


/**
* 画像を添付するためのもの
*/
function plugin_show_action()
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

			//アップロードするファイル名を決める（日本語ダメ、重複もダメ）
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

			$retval = show_insert_ref(SWFU_IMAGE_DIR.$fname);

			return $retval;
		}

		// open qhm用 attachフォルダにファイルを置く
		//すでに存在した場合、 ファイル名に'_0','_1',...を付けて回避(姑息)
		$count = '_0';
		while (file_exists('./attach/'.encode($vars['refer']).'_'.encode($attachname)))
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
			$retval = show_insert_ref($file['name']);
		}
	}
	else
	{
		$retval = show_showform();
		// XHTML 1.0 Transitional
		$html_transitional = TRUE;
	}
	return $retval;

}

//アップロードフォームを表示
function show_showform()
{
	global $vars;
	$qm = get_qm();

	$vars['page'] = $vars['refer'];
	$body = ini_get('file_uploads') ? show_form($vars['page']) : 'file_uploads disabled.';

	return array('msg'=>$qm->m['plg_attach']['upload'],'body'=>$body);
}
//アップロードフォーム
function show_form($page)
{
	global $script,$vars;
	$qm = get_qm();

	$s_page = htmlspecialchars($page);

	$f_digest = array_key_exists('digest',$vars) ? $vars['digest'] : '';
	$f_no = (array_key_exists('show_no',$vars) and is_numeric($vars['show_no'])) ?
		$vars['show_no'] + 0 : 0;


	if (!(bool)ini_get('file_uploads'))
	{
		return "";
	}

	$maxsize = PLUGIN_SHOW_MAX_FILESIZE;
	$msg_maxsize = $qm->replace('plg_attach.maxsize', number_format($maxsize/1024)."KB");

	$pass = '';
	if (ATTACHREF_PASSWORD_REQUIRE or ATTACHREF_UPLOAD_ADMIN_ONLY)
	{
		$title = $qm->m['plg_attach'][ATTACHREF_UPLOAD_ADMIN_ONLY ? 'adminpass' : 'password'];
	}
	return <<<EOD
<form enctype="multipart/form-data" action="$script" method="post">
 <div>
  <input type="hidden" name="plugin" value="show" />
  <input type="hidden" name="pcmd" value="post" />
  <input type="hidden" name="show_no" value="$f_no" />
  <input type="hidden" name="show_opt" value="{$vars['show_opt']}" />
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

function show_insert_ref($filename)
{
	global $script,$vars,$now,$do_backup;
	$qm = get_qm();

	$slen = strlen(SWFU_IMAGE_DIR);
	$filename = substr($filename, 0, $slen)==SWFU_IMAGE_DIR ? substr($filename, $slen) : $filename;

	$ret['msg'] = $qm->m['plg_attachref']['title'];

	$args = split(",", $vars['show_opt']);
	if ( count($args) ){
		$args[0] = $filename;//array_shift,unshiftって要するにこれね
		$s_args = implode(",", $args);
	}
	else {
		$s_args = $filename;
	}
	$msg = "&show($s_args)";

	$refer = $vars['refer'];
	$digest = $vars['digest'];
	$postdata_old = get_source($refer);
	$thedigest = md5(join('',$postdata_old));

	$postdata = '';
	$show_ct = 0; //'#show'の出現回数
	$show_no = $vars['show_no'];
	$skipflag = 0;

	$is_box = false;
	$boxcnt = 0;
	$boxdata = array();

	foreach ($postdata_old as $line)
	{
		if ($is_box == false && ( $skipflag || substr($line,0,1) == ' ' || substr($line,0,2) == '//'))
		{
			$postdata .= $line;
			continue;
			}

		if ($is_box == true && preg_match('/^\}\}/',$line))
		{
			$postdata .= $line;
			$is_box = false;
			continue;
		}

		if ($is_box)
		{
			$boxdata[$boxcnt][] = $line;
			continue;
		}

		if ($is_box == false && preg_match('/^#.+\{\{$/',$line))
		{
			$postdata .= $line;
			$is_box = true;
			$postdata .= '${box'. ++$boxcnt ."}\n";
			$boxdata[$boxcnt] = array();
			continue;
		}

		$ct = preg_match_all('/&show(?=[({;])/',$line, $out);
		if ($ct)
		{
			for ($i=0; $i < $ct; $i++)
			{
				if ($show_ct++ == $show_no)
				{
					$line = preg_replace('/&show(\([^(){};]*\))?(\{[^{}]*\})?;/',$msg.'$2;',$line,1);
					$skipflag = 1;
					break;
				}
				else
				{
					$line = preg_replace('/&show(\([^(){};]*\))?(\{[^{}]*\})?;/','&___show$1$2___;',$line,1);
				}
			}
			$line = preg_replace('/&___show(\([^(){};]*\))?(\{[^{}]*\})?___;/','&show$1$2;',$line);
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

			$ct = preg_match_all('/&show(?=[({;])/',$line, $out);
			if ($ct)
			{
				for ($i=0; $i < $ct; $i++)
				{
					if ($show_ct++ == $show_no)
					{
						$line = preg_replace('/&show(\([^(){};]*\))?(\{[^{}]*\})?;/',$msg.'$2;',$line,1);
						$skipflag = 1;
						break;
					}
					else
					{
						$line = preg_replace('/&show(\([^(){};]*\))?(\{[^{}]*\})?;/','&___show$1$2___;',$line,1);
					}
				}
				$line = preg_replace('/&___show(\([^(){};]*\))?(\{[^{}]*\})?___;/','&show$1$2;',$line);
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

/**
 * Get Image Size
 *
 * @param string $image_path image's path or URL
 * @return array size of image OR FALSE
 */
function plugin_show_get_imagesize($image_path = '')
{
	$is_url = is_url($image_path, false, true);

	if ($is_url) {
		if (PKWK_DISABLE_INLINE_IMAGE_FROM_URI) {
			$url = h($image_path);
			$params['_body'] = '<a href="' . $url . '">' . $url . '</a>';
			return $params;
		}

		$url = h($image_path);
		$title = h(preg_match('/\/(.+?)$/', $image_path, $matches) ? $matches[1] : $url);

		if (PLUGIN_SHOW_URL_GET_IMAGE_SIZE && (bool)ini_get('allow_url_fopen')) {
			$size = @getimagesize($url);
			return $size;
		}

	} else {

		$file = $image_path;
		if ( ! is_file($file))
		{
			$file = SWFU_IMAGE_DIR . $file;
			if ( ! is_file($file))
			{
				return FALSE;
			}
		}

		$file = (substr($file, 0, 2) == './') ? substr($file, 2) : $file;

		$width = $height = 0;
		$size = @getimagesize($file);
		return $size;
	}

}

/**
 * Get File Path considering SWFU
 */
function plugin_show_get_filepath($file_path = '')
{
	$is_url = is_url($file_path, false, true);
	$url = $file_path;

	if ( ! $is_url) {
		$file = $file_path;
		if ( ! is_file($file))
		{
			$file = SWFU_IMAGE_DIR . $file;
			if (is_file($file))
			{
				$url = $file;
			}
		}
	}

	return $url;
}

function plugin_show_set_keep_ratio()
{
	$qt = get_qt();
	$addjs = <<< EOS
<script>
if (typeof QHM === "undefined") QHM = {};
QHM.keepRatio = function(){
	function keepRatio(el) {
		var \$img = $(this);
		if ( ! \$img.is("[width]") || \$img.hasClass("qhm-plugin-show-size-given")) return;

		\$img.css({
			width:  \$img.attr("width"),
			height: "auto"
		});
	}
	$(".qhm-plugin-show-keep-ratio").each(keepRatio);
}
$(document).on("ready", QHM.keepRatio);
</script>
EOS;

	$qt->appendv_once('plugin_show_set_keep_ratio', 'beforescript', $addjs);

}
