<?php
/**
*   external_link用 javascriptをはき出す
*   -------------------------------------------
*   external_link.inc.php
*
*   Copyright (c) 2009 hokuken
*   http://hokuken.com/
*
*   created  : 2010年 11月 17日(水)
*   modified :
*
*   Description
*     lib/qhm_init_main.php で利用される。
*     ページ内にあるリンクで「別ドメインへのリンクは、別ウインドウ表示」させる
*     Javascriptを出力する。
*
*   Usage :
*     通常、このプラグインを使うことはありません。どうしても開発側で変更したい場合、
*     オリジナルな制御がしたい場合に改良してください。
*/

/**
*   @param
*     $nowindow 1:表示しない、2:target="newwindow"、3:target="_blank"
*     $reg_exp_host 除外するサイト(ドメイン)
*
*   @return <array> description
*
*/
function plugin_external_link_js($nowindow, $reg_exp_host)
{
    $qt = get_qt();

    $target = null;
    if ($nowindow === 0)
    {
        $target = '_blank';
    }
    else if ($nowindow === 2)
    {
        $target = 'newwindow';
    }

    $qt->setjsv('window_open', $target !== null);
    $qt->setjsv('exclude_host_name_regex', $reg_exp_host);
    $qt->setjsv('default_target', $target);

}
