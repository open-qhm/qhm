<?php
/*
 flash plugin for pukiwiki
 Author Nekyo.(http://nekyo.hp.infoseek.co.jp/)
 arrange tamac (http://tamac.daa.jp/)
 v1.0 2004/05/26
 1.4.4  2005/01/04 arrange jack(http://f29.aaa.livedoor.jp/~morg/wiki/)

 //----------------------------------------------------------------------
 Usage:
 書式
 #flash(flashムービーファイル,幅x高さ[,オプション=["]値["]]...)
      引数,オプション	        : 意味      : デフォルト
	ファイル名		:必須
        幅x高さ			:必須
	quality = 		:品質        :  high
	bgcolor =	        :背景色      :  なし
	classid =		:クラスID    :D27CDB6E-AE6D-11cf-96B8-444553540000
	version	=		:バージョン  :6,0,0,0
	align	=		:アライン    :center
	flashvars= 		:FlashVars Flashに引数として渡すもの "変数名=値"( "" で囲います): なし
        style  =		:個別スタイル	     : なし


*/
//*****   定数 *******************************************
//
//					ここでデフォルトを設定
//
define("DEFVERSION",	"6,0,0,0");				 //: デフォルトバージョン
define("DEFBGCOLOR",	"");					 //: デフォルト背景
define("DEFQUALITY",	"high");				 //: デフォルトクオリティ
define("DEFALIGN",	    "center");				 //: デフォルトアライン
define("DEFCLASSID",	"D27CDB6E-AE6D-11cf-96B8-444553540000"); //: デフォルトクラスID



//*********************************************************************
//
//		インライン
function plugin_flash_inline()
{
	$args = func_get_args();
	array_pop($args);	// インラインの場合引数の数＋１になる仕様対策
	array_push($args,"inline");
	return call_user_func_array('plugin_flash_convert', $args);
}
//******************************************************************
//
//			flashコンバート
//
//
function plugin_flash_convert()
{
	$argc = func_num_args();
	$qt = get_qt();

	// add ignore class for fitvids
	$ignore_list = $qt->getv('fitvids_ignore_list');
	$ignore_list = $ignore_list ? $ignore_list : array();
	$ignore_list[] = '.qhm-flash';
	$qt->setv('fitvids_ignore_list', $ignore_list);

	if ($argc < 1) {
		return FALSE;
	}
	$binline = 0;
	$argv = func_get_args();
	if($argv[$argc-1] == "inline")
	{ $binline = 1; array_pop($argv);}
	$swf = $argv[0];
//	$swf = &::unescape(&flash::decode($swf));

	//  @ デフォルト値
	$version = DEFVERSION;
	$classid = DEFCLASSID;
	$bgcolor = DEFBGCOLOR;
	$quality = DEFQUALITY;
	$align =   DEFALIGN;
	$id ='swf';
	$wmode = 'opaque';
	$width=" ";
	$height=" ";
	$flashvars="";
	$serverpath  = "http://" . getenv('SERVER_NAME') . "/";

	$wikipath  = str_replace("index.php", "",  "http://" . getenv('SERVER_NAME') . getenv('SCRIPT_NAME'));

        $swf = str_replace("{server}", $serverpath,$swf);
        $swf = str_replace("{wikiroot}", $wikipath,$swf);
	// name取り出し
	if (!strpos($swf,"/")){ $name = str_replace(".swf","",$swf);
 	}else{  preg_match('/\/([^\/]*)\.swf/',$swf,$nmatch);$name = $nmatch[1];}

	//
	$valueStyle = 0;
	$bStyle = 0;
	for ($i = 1; $i < $argc; $i++) {
		//@ 検出正規表現  英数文字=["]任意["]
		$arg_str = isset($argv[$i]) ? $argv[$i] : '';
		if (preg_match('/(\w*)=\"?([^\"]*)\"?/', $arg_str, $match)) {
//		if (preg_match('/(\w*)=\"?([^\"]*)\"?/', $argv[$i], $match)) {
			$prop = strtolower($match[1]);
			$prot = $match[1];
			if ($prop == 'quality'  ) {
				$quality = $match[2];
			} else if($prop== 'bgcolor') {
			    	$bgcolor = $match[2];
			} else if($prop == 'classid' || $prop == 'clsid') {
				$classid = $match[2];
			} else if($prop == 'version'  ) {
				$version = $match[2];
			} else if($prop == 'id'){
				$id = $match[2];
			} else if($prop =='align'  ){
				$align = $match[2];
			} else if($prop =='flashvars'){
				$flashvars = $match[2];
			} else if($prop == 'border'){
				$bStyle = 1;
				$valueStyle = $match[2] . "px";
			} else if($prop == 'wmode'){
				$wmode = $match[2];
			}//if;
			// チェック 数字であり1ケタ以上４桁以下
		} else if (preg_match('/([0-9]{1,4})x([0-9]{1,4})/i', $arg_str, $match)) {
//		} else if (preg_match('/([0-9]{1,4})x([0-9]{1,4})/i', $argv[$i], $match)) {
			$numWidth = $match[1];
			$numHeight = $match[2];
			$width = "width=\"". $numWidth . "\"";
			$height = "height=\"".$numHeight . "\"";
		}//if
	}//for
	if( preg_match('/[0-9]/' ,$valueStyle) ){
		  $numWidth+=$valueStyle; $numHeight+=$valueStyle;
		  $strStyle = "width:$numWidth;height:$numHeight;";
	} else {  $strStyle = "width:$numWidth;height:$numHeight;";}
	$align=strtolower($align);
	// mozilla対策
	if ($align=='center'){
		$mozAlign="text-align:$align;margin-left:auto; margin-right:auto;";
	}else if ($align=='right'){
		$mozAlign="text-align:$align;margin-left:auto; margin-right:0;";
	} else {
		$mozAlign="text-align:$align;margin-left:0; margin-right:auto;";
	}
	//
	//  @ objectタグ IE  embed   mozilla embed用のflashvars
	$embedflashvars = '';
	//  @ flashvarsが存在する場合
	if($flashvars!='')
	{
		// @ UTF-8に変換
		//$flashvars=mb_convert_encoding($flashvars,"UTF-8","EUC-JP");//
		// @ varname=val の形に分解
		$aryVars = preg_split('/&/',$flashvars);
		for($i=0;$i < count($aryVars); $i++)
		{
			// @ 名前と値に分解
			$aryField = preg_split('/=/',$aryVars[$i]);
			if(count($aryField)==2)
			{
				// @ 値の部分だけurlエンコード
				$aryField[1] = urlencode($aryField[1]);
				// @ 繋げて戻す
				$aryVars[$i] = join('=',$aryField);
			}
		}
		// @ 繋げて戻す
		$flashvars = join('&',$aryVars);
		// @ embed対策
		$embedflashvars= '?' . $flashvars;
	}
	$sBorder=0;
	if($bStyle==1){	$sBorder = "border:$valueStyle solid black";}

	//  @ 出力タグ  <div><table><<object>
	$rt = <<<EOD
  <div class="flash qhm-flash" id="$name" style="$mozAlign">
   <table style="background:inherit;$mozAlign">
   <tr><td class="flash" style="background:inherit;margin:0px;padding:0px;$sBorder">
     <object classid="clsid:$classid" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=$version" $width $height id="$id">
       <param name="movie" value="$swf" />
       <param name="quality" value="$quality" />
       <param name="bgcolor" value="$bgcolor" />
       <param name="FlashVars" value="$flashvars" />
       <embed src="$swf$embedflashvars" quality="$quality" bgcolor="$bgcolor" $width  $height name="$name" align="$align" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
     </object>
   </td></tr>
  </table>
 <div>

EOD;

	if ($binline==1)
	{
		// インライン出力
		return "$rt";
	} else {
		// : ブロック出力    配置用テーブルで括る
		return <<<EOD
 <table class="aligntable" style="background:inherit;width:100%;border:0px;" >
 <tr><td class="aligntd" style="text-align:$align;background:inherit;border:0px;" >$rt</td></tr></table>
EOD;

	}

}
