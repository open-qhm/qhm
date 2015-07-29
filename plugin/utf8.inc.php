<?php

// ---------------------------------------------
// absplit.inc.php   v. 0.9
//
// 任意のページにリダイレクトする
// URLを設定しない場合は、トップへ
// 管理モードで以外で表示したくないページに使うと便利
// writed by hokuken.com 2007 8/24
// ----------------------------------------------- 


function plugin_utf8_convert()
{
	global $utf8, $vars;

	//edit auth check
    $editable = edit_auth($vars['page'], FALSE, FALSE);
    if($editable){
        return "<p><strong>【お知らせ】</strong><br />このページは、ユーザーモードの際、UTF8化されています<br />
QHM v4からは、UTF-8が標準になったので、このプラグインは不要です</p>";
    }
    else{
		$utf8 = false;
		return "";
	}
}


?>
