<?php
/**
 *   Yahoo Bookmark Plugin v1.1
 *   -------------------------------------------
 *   ./plugin/yahoobookmark.inc.php
 *   
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 
 *   modified : 2011-12-05
 *   
 *   Description
 *     v1.1:
 *       タイトルなしでもOKに。
 *       アイコン大小の切り替え、ウィンドウ制御が可能に。
 *   
 *   Usage :
 *     &yahoobookmark([タイトル[,short[,nowindow]]]);
 *   
 */

function plugin_yahoobookmark_inline()
{
	$args = func_get_args();
	$text = $short = $nowindow = FALSE;
	
	foreach ($args as $arg)
	{
		$arg = trim($arg);
		if ($arg === 'short' && $short === FALSE)
		{
			$short = TRUE;
		}
		else if ($arg === 'nowindow' && $nowindow === FALSE)
		{
			$nowindow = TRUE;
		}
		else if (trim($arg) !== '')
		{
			$text = $arg;
		}
	}

	if ($text === FALSE OR trim($text) === '')
	{
		$text = "document.title";
	}
	else
	{
		$text = "'". htmlspecialchars(addcslashes($text, "'")). "'";
	}
	
	if ($short === TRUE)
	{
		$btn_img = 'http://i.yimg.jp/images/sicons/ybm16.gif';
		$btn_width = '16';
		$btn_heigth = '16';
	}
	else
	{
		$btn_img = 'http://i.yimg.jp/images/ybm/blogparts/addmy_btn.gif';
		$btn_width = '125';
		$btn_heigth = '17';
	}
	
	if ($nowindow === TRUE)
	{
		$jscript = <<<EOS
<a href="javascript:location.href='http://bookmarks.yahoo.co.jp/action/bookmark?t='+encodeURIComponent({$text})+'&amp;u='+encodeURIComponent(location.href);"><img src="{$btn_img}" width="{$btn_width}" height="{$btn_height}" alt="Yahoo!ブックマークに登録" style="border:none;"></a>
EOS;
	}
	else
	{
		$jscript = <<<EOS
<a href="javascript:void window.open('http://bookmarks.yahoo.co.jp/bookmarklet/showpopup?t='+encodeURIComponent({$text})+'&amp;u='+encodeURIComponent(location.href)+'&amp;ei=UTF-8','_blank','width=550,height=480,left=100,top=50,scrollbars=1,resizable=1',0);"><img src="{$btn_img}" width="{$btn_width}" height="{$btn_height}" alt="Yahoo!ブックマークに登録" style="border:none;"></a>
EOS;
	}

	return $jscript;
}

/* End of file yahoobookmark.inc.php */
/* Location: ./plugin/yahoobookmark.inc.php */