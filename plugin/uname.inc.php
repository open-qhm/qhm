<?php
// uname plugin
// 
// 目的： Webページ内を個人の名前で置き換え、親近感を与えるために使う
//
// 機能： &uname;と書いたところを置換する
// 置換文字は、GETパラメーターで渡されるuname変数を使う
// もしunameがなければ、「あなた」を使う
//
// オプション： &uname(様);とすると、名前+様を使う
// &uname;だけだと、名前+さん
// &uname(none);だと、名前のみ（使うのか？）
// 
function plugin_uname_inline()
{
	$qt = get_qt();
	//---- キャッシュのための処理を登録 -----
	if($qt->create_cache) {
		$args = func_get_args();
		return $qt->get_dynamic_plugin_mark(__FUNCTION__, $args);
	}
	//------------------------------------


	global $vars;

	$name = isset($vars['uname']) ? $vars['uname'] : '';
	
	//unameパラメータがセットされていない場合
	if($name == ''){
		return 'あなた';
	}
	else{
		$name = htmlspecialchars( mb_convert_encoding($name, SOURCE_ENCODING, 'UTF8,EUC-JP,Shift_JIS') );
	}

	$args = func_get_args();
	$num = count($args);
	$ntitle = "さん";
	
	if($num > 1){
		strip_htmltag(array_pop($args), FALSE);
		$tmpstr = strip_htmltag(array_pop($args), FALSE);
		
		if($tmpstr == "none"){
			$ntitle = '';
		}
		else{
			$ntitle = $tmpstr;
		}
	}
	
	return $name.$ntitle;
}
?>
