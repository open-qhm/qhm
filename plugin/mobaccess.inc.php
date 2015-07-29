<?php
/**
*   モバイルアクセスを制御するためのプラグイン
*   -------------------------------------------
*   mobaccess.inc.php
*  
*   Copyright (c) 2009 hokuken
*   http://hokuken.com/
*  
*   created  : 2009 10/14
*   modified : 2009 10/14
*  
*   Description
*  		モバイルからのアクセスを受けて、cookieが有効かチェックを行う。
*		有効であれば、単純に特定のページに転送する
*		無効であれば、session idを使う方式が有効になるようにして転送をする
*		
*   Usage :
*  		{$script}?cmd=mobaccess&page=HogeHoge
*/

function plugin_mobaccess_action(){

	global $vars, $script, $defaultpage;
	
	$page = $vars['page']=='' ? $defaultpage : $vars['page'];
	$r_page = rawurlencode($page);
	
	if( isset($vars['check']) ){
		$cookie = $_COOKIE['QHMDUMMY'];
		setcookie('QHMDUMMY', '' ,time()-3600); //del cookie
		
		if( $cookie ){
			header('Location: '.$script.'?'.$r_page);
			exit;
		}
		else{
			header('Location: '.$script.'?'.$r_page.'&mobssid=yes');
			exit;
		}
	}
	else{ //はじめてのアクセス
		setcookie('QHMDUMMY', TRUE);
		header('Location: '.$script.'?cmd=mobaccess&page='. $r_page.'&check');
		exit;
	}
}

?>
