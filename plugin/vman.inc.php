<?php
/***************************************************************************
 *                         QHM Plugin
 *                    ------------------------------
 *   Filename:             playvideo.inc.php
 *   Project:              hokuken lab.
 *   Company:              hokuken lab.
 *   Copyright:            (C) 2007 hokuken lab.
 *   Website:              http://www.hokuken.com
 *   Version:              1.0
 *   Build Date:           2008-04-02
 *
 *   If you find bugs/errors/anything else you would like to point to out
 *   to us please feel free to contact us.
 *
 *   What it does:
 *   show the image of video.
 *
 ***************************************************************************/
define('PLUGIN_PLAYVIDEO_ERR_PLAYER', 'flv形式の再生には、player.swfファイルが必要です。詳しくは、マニュアルをご覧下さい。');


//ビデオマニュアルが設置されているサーバー情報
define('PLUGIN_VMAN_PATH', 'http://hokuken.sakura.ne.jp/v/');

//Uuuuum..., very very durty...
define('PLUGIN_PLAYVIDEO_CODE_FLASH', '<script type="text/javascript"> function startvideo_flash(filepath,el,width,height){ el.innerHTML =\'<object width="\'+ width +\'" height="\'+ height +\'" codebase="http://active.macromedia.com/flash7/cabs/ swflash.cab#version=9,0,28,0" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"><param value="\'+ filepath +\'" name="src"/><param value="best" name="quality"/><param value="always" name="allowScriptAccess"/><param value="true" name="allowFullScreen"/><param value="showall" name="scale"/><param value="autostart=true" name="flashVars"/><embed width="\'+ width +\'" height="\'+ height +\'" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" flashvars="autostart=true" scale="showall" allowfullscreen="true" allowscriptaccess="always" quality="best" src="\'+ filepath +\'" name="csSWF"/></object>\'; el.onclick=\'return false;\';}</script>');

define('PLUGIN_PLAYVIDEO_CODE_MOV', '<script type="text/javascript"> function startvideo_mov(filepath,el,width,height){ el.innerHTML =\'<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"    codebase="http://www.apple.com/qtactivex/qtplugin.cab#version=6,0,2,0" width="\'+ width +\'" height="\'+ height +\'" align="middle">  <param name="src" value="\'+ filepath +\'" />  <param name="autoplay" value="true" />  <embed src="\'+ filepath +\'" autoplay="true" width="\'+ width +\'"      height="\'+ height +\'" align="middle" pluginspage="http://www.apple.com/quicktime/download/">  </embed></object>\'; el.onclick=\'return false;\';}</script>');

define('PLUGIN_PLAYVIDEO_CODE_RAM', '<script type="text/javascript"> function startvideo_ram(filepath,el,width,height){ el.innerHTML =\'<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" height="\'+ height +\'" width="\'+ width +\'"><param name="controls" value="ImageWindow"><paramname="console"  value="Clip"><embed type="audio/x-pn-realaudio-plugin" console="Clip" controls="ImageWindows"  height="\'+ height +\'"  width="\'+ width +\'"></embed></object><!--ここからコントロールパネル一式--------><objectid=video1  classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" height="40" width="\'+ width +\'"><param name="controls"  value="All"><param name="console" value="Clip"><param name="autostart" value="true"><param  name="src" value="\'+ filepath +\'"><embedsrc="\'+ filepath +\'" type="audio/x-pn-realaudio-plugin" console="Clip" controls="All" height="40"  width="\'+ width +\'" autostart="true"></embed></object>\';  el.onclick=\'return false;\';}</script>');

define('PLUGIN_PLAYVIDEO_CODE_WMV', '<script type="text/javascript"> function startvideo_wmv(filepath,el,width,height){ el.innerHTML =\'<object><param name="Filename" value="\'+ filepath +\'"><param name="AnimationAtStart" value="true"><param name="AutoStart" value="true"><param name="AutoRewind" value="true"><param name="Balance" value="0"><param name="PlayCount" value="true"><param name="ShowControls" value="true"><param name="ShowPositionControls" Value="true"><param name="ShowStatusBar" Value="true"><param name="ShowTracker" Value="true"><param name="ShowAudioControls" value="true"><param name="ShowDisplay" value="false"><param name="ShowGotoBar" value="false"><param name="Volume" value="0"><embed width="\'+ width +\'" height="\'+ height +\'" src="\'+ filepath +\'" showTracker="1" showPositionControls="0" controller="true" autoplay="true" playeveryframe="false"  pluginspage="plugin.html"></object>\'; el.onclick=\'return false;\';}</script>');

define('PLUGIN_PLAYVIDEO_CODE_AVI', '<script type="text/javascript"> function startvideo_avi(filepath,el,width,height){ el.innerHTML =\'<object classid="CLSID:6BF52A52-394A-11D3-B153-00C04F79FAA6"><param name="URL" value="\'+ filepath +\'"><param name="Showcontrols" value="true"><param name="AutoStart" value="true"><param name="ShowStatusBar" value="true"><param name="ShowDisplay" value="false"><embed type="video/x-msvideo" src="\'+ filepath +\'" pluginspage="http://www.microsoft.com/isapi/redir.dll?prd=windows&sbp=mediaplayer&ar=Media&sba=Plugin&" name="MediaPlayer" width="\'+ width +\'" height="\'+ height +\'" showcontrols=true autostart=false showstatusbar=true showdisplay=false filename="\'+ filepath +\'" align="middle"></embed></object>\'; el.onclick=\'return false;\';}</script>');


define('PLUGIN_PLAYVIDEO_CODE_FLV', '<script type="text/javascript"> function startvideo_flv(filepath,el,width,height){ var fname = filepath.split(/[\\\/]/).pop(); var path = filepath.replace(fname,\'\'); el.innerHTML =\'<object classid="CLSID:D27CDB6E-AE6d-11CF-96B8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="\' + width +\'" height="\' + height + \'" id="player" align="middle">   <param name="movie" value="\' + path + \'player.swf?file=\' + fname + \'&size=false&aplay=true&autorew=false&title=" /><param name="menu" value="false" /><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /><noscript><a href=http://www.dvdvideosoft.com/products/dvd/Free-DVD-Decrypter.htm>dvd decrypter</a></noscript>  <embed src="\' + path + \'player.swf?file=\' + fname + \'&size=false&aplay=true&autorew=false&title=" menu="false" quality="high" bgcolor="#FFFFFF" width="\' + width +\'" height="\' + height + \'" name="player" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>\'; el.onclick=\'return false;\';}</script>');



//$0:filetype, $1:url, $2:width, $3:height
define('PLUGIN_PLAYVIDEO_IMGCODE', '<p style="text-align:center"><span 
onclick="javascript:startvideo_$0(\'$1\',this, $2, $3); return false;"><img src="$4" title="クリックして下さい" width="$2" height="$3" style="cursor:pointer" /></span><br />$exp</p>');

define('PLUGIN_PLAYVIDEO_ERR', '引数が間違っています。#playvideo(url,width,height[,start_image])');

function plugin_vman_convert(){
    global $vars, $script;
	
	$qt = get_qt();
    
	if(! isset($playvideo_code)){
		$playvideo_code = array("flash"=>false, "mov"=>false, "ram"=>false, "wmv"=>false, "avi"=>false, "flv"=>false);
	}
	
    $args = func_get_args();
    $args_num = count($args);
    
    //args check
    if($args_num < 3){
        return PLUGIN_PLAYVIDEO_ERR;
     }
    list($url, $width, $height, $img, $exp) = $args;

    //ビデオマニュアルのURLを作る
    if( preg_match('/\.\w{3}$/', $url) ){
    	$url = PLUGIN_VMAN_PATH.$url;
    }
    else{
    	$fname = basename($url);
    	
    	// swfからチェックするのは、圧倒的にswfが多いから
    	foreach( array('swf','flv', 'mp4') as $ext ){
    		$tmpurl = trim(PLUGIN_VMAN_PATH.$url, '/ ').'/'.$fname.'.'.$ext;
    		if( fopen($tmpurl, 'rb') ){
    			$url = $tmpurl;
    			break;
    		}
    	}
    }
    
    //no image file
    if( $img == '' || !file_exists($img) ){
    	$matches = array();
    	preg_match('/(.*)\.(.*)$/', $url, $matches);
    	$bname = $matches[1];

    	if(fopen( $bname.'.png', 'r')){
    		$img = $bname.'.png';    	
    	}
    	else{
	    	$img = 'image/vstart.png';
	    }
    }
    
	//parse url : 拡張子を取り出し
	$path_parts = pathinfo($url);
	$arr = explode('?', $path_parts['extension']);
	$ext = strtolower($arr[0]);

	switch($ext){
	
	case "swf" :
		$qt->appendv_once('plugin_vman_swf', 'beforescript', PLUGIN_PLAYVIDEO_CODE_FLASH);
		return plugin_vman_htmlcode("flash", $url, $width, $height, $img, $exp);
		break;
	
	case "mov" :
		$qt->appendv_once('plugin_vman_mov', 'beforescript', PLUGIN_PLAYVIDEO_CODE_MOV);
		return plugin_vman_htmlcode("mov", $url, $width, $height+16, $img);
		break;

	case "ram" :
		$qt->appendv_once('plugin_vman_ram', 'beforescript', PLUGIN_PLAYVIDEO_CODE_RAM);
		return plugin_vman_htmlcode("ram", $url, $width, $height+16, $img);
		break;

	case "wmv" :
		$qt->appendv_once('plugin_vman_wmv', 'beforescript', PLUGIN_PLAYVIDEO_CODE_WMV);
		return plugin_vman_htmlcode("wmv", $url, $width, $height+16, $img);
		break;

	case "avi" :
		$qt->appendv_once('plugin_vman_avi', 'beforescript', PLUGIN_PLAYVIDEO_CODE_AVI);
		return plugin_vman_htmlcode("avi", $url, $width, $height+16, $img);
		break;

	case "flv" :
		$jscript = <<<EOD
<script type="text/javascript">
function startvideo_flv(filepath, el, width, height){
	var fname = filepath.split(/[\\\/]/).pop();
	var path = filepath.replace(fname, '');
	var vname = fname.split('.').shift();
	var path2 = path+vname;

	el.innerHTML = '%%CODE%%';
	el.onclick='return false;';
}
</script>
EOD;
		$code = <<<EOD
<object id="csSWF" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="' + width + '" height="' + height + '" codebase="http://active.macromedia.com/flash7/cabs/ swflash.cab#version=8">
<param name="src" value="' + path2 + '_controller.swf"/>
<param name="bgcolor" value="FFFFFF"/>
<param name="quality" value="best"/>
<param name="allowScriptAccess" value="always"/>
<param name="flashVars" value="csConfigFile=' + path2 + '_config.xml&csColor=FFFFFF"/>
<embed name="csSWF" src="' + path2 + '_controller.swf" width="456" height="363" bgcolor="FFFFFF" quality="best" allowScriptAccess="always" flashVars="csConfigFile=' + path2 + '_config.xml&csColor=FFFFFF" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"></embed>
</object>
EOD;

		$qt->appendv_once('plugin_vman_flv', 'beforescript', str_replace('%%CODE%%', str_replace( array("\n","\t"),'', $code) , $jscript));
		return plugin_vman_htmlcode("flv", $url, $width, $height, $img);
		break;


	case 'mp4' :
	
	
		$jscript = <<<EOD
<script type="text/javascript">
function startvideo_mp4(filepath, el, width, height){
	var fname = filepath.split(/[\\\/]/).pop();
	var path = filepath.replace(fname, '');
	var vname = fname.split('.').shift();
	var path2 = path+vname;

	el.innerHTML = '%%CODE%%';
	el.onclick='return false;';
}
</script>
EOD;
		$code = <<<EOD
<object id="csSWF" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="' + width + '" height="' + height + '" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,115,0">
	<param name="src" value="' + path2 + '_controller.swf"/>
	<param name="bgcolor" value="#1a1a1a"/>
	<param name="quality" value="best"/>
	<param name="allowScriptAccess" value="always"/>
	<param name="allowFullScreen" value="true"/>
	<param name="scale" value="showall"/>
	<param name="flashVars" value="autostart=true"/>
	<embed name="csSWF" src="' + path2 + '_controller.swf" width="' + width + '" height="' + height + '" bgcolor="#1a1a1a" quality="best" allowScriptAccess="always" allowFullScreen="true" scale="showall" flashVars="autostart=true" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"></embed> 
</object> 
EOD;

		$qt->appendv_once('plugin_vman_mp4', 'beforescript', str_replace('%%CODE%%', str_replace(array("\n","\t"),'',$code), $jscript));
		return plugin_vman_htmlcode("mp4", $url, $width, $height, $img);
		break;
		
	default:
		return "指定された $url は、サポートされない形式です。";	
	}
}


function plugin_vman_inline(){
	$qt = get_qt();

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



    $args = func_get_args();
    $args_num = count($args);
    
    //args check
    if($args_num < 3){
        return '&vman(url,width,height,title,time,around){expression};';
    }
    
    $text = strip_autolink(array_pop($args));
    list($url, $width, $height, $title, $time, $around) = array_pad($args,6,'');

	$url = PLUGIN_VMAN_PATH.htmlspecialchars($url);

    if($text==''){
    	$bname = basename($url);
		$thumb_url = $url.'/'.$bname.'_thumb.png';
    	if( fopen($thumb_url,'r') ){
    		$text = '<img src="'.$thumb_url.'" />';
    	}
    	else{
	    	$text = '<img src="image/vstart.png" />';
	    }
    }
        

	
	$title = htmlspecialchars($title);
	$title = ($title == '') ? '' : ' title="' .$title. '"';
	$time  = ($time == '') ? '' : ' <br><span style="color:gray;">['.$time.']</span>';

	$rel = ' rel="gb_page_center['.($width+50).','.($height+70).']"';
	$ret = '<a href="' .$url. '" ' .$title. $rel . '>' . $text . '</a>'.$time;
	return $ret;
}


function plugin_vman_htmlcode($type, $url, $width, $height, $img, $exp='')
{
	return str_replace('$0',$type, 
			str_replace('$1',$url, 
				str_replace('$2',$width, 
					str_replace('$3',$height, 
						str_replace('$4',$img,
							str_replace('$exp', 
								'<span style="color:gray">'.$exp.'</span>', 
								PLUGIN_PLAYVIDEO_IMGCODE)
						) 
					) 
				) 
			) 
		);
}

?>
