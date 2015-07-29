<?php
/**
 *   No eyecatch Plugin
 *   -------------------------------------------
 *   noeyecatch.inc.php
 *   
 *   Copyright (c) 2014 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 14/08/7
 *   modified : 
 *   
 *   デフォルトアイキャッチを無効にする
 *   
 *   Usage :
 *   #noeyecatch
 *   
 */
function plugin_noeyecatch_convert()
{
    $qt = get_qt();
    
    $qt->setv('no_eyecatch', TRUE);
}
