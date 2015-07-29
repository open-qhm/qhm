<?php
/**
 *   Twitter Tweet Button Plugin
 *   -------------------------------------------
 *   ./plugin/tw_button.inc.php
 *   
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2011-10-06
 *   modified : 2012-01-30 URLを指定しない場合、短縮URLを使ってdata-url属性を設定するように変更
 *   
 *   Description
 *     Twitter に登校するボタン（公式）を設置する。
 *   
 *   Usage :
 *     #tw_button(URL,style=v|h|n,user={user_name},related={related_user},rel_desc={related_user_description},lang=ja|en,{text})
 *     引数を省略すると、設置しているページのURLとタイトルでTweet する
 */

define('PLUGIN_TW_bUTTON_FMT', '<a href="https://twitter.com/share" class="twitter-share-button"%s>Tweet</a>');

function plugin_tw_button_inline()
{
	$args = func_get_args();
	return plugin_tw_button_body($args);
}

function plugin_tw_button_convert()
{
	$args = func_get_args();
	return plugin_tw_button_body($args);
}

function plugin_tw_button_body($args)
{
	global $script, $vars;
	
	$page = $vars['page'];
	$r_page = rawurlencode($page);
	$qm = get_qm();
	$qt = get_qt();
	
	//data-count
	$count_styles = array('vertical', 'horizontal', 'none');
	//data-text
	//data-url
	//data-lang
	$langs = array(
		'Japanese' => 'ja',
		'Dutch' => 'nl',
		'English' => '',
		'French' => 'fr',
		'German' => 'de',
		'Indnesian' => 'id',
		'Italian' => 'it',
		'Korean' => 'ko',
		'Portuguese' => 'pt',
		'Russian' => 'ru',
		'Spanish' => 'es',
		'Turkish' => 'tr'
	);
	//data-via
	//data-related

	$qt->appendv_once('plugin_tw_button', 'lastscript', '<script type="text/javascript" src="//platform.twitter.com/widgets.js" charset="utf-8"></script>');
	
	//attr を構築
	$init_url = FALSE;
	$attrs = array(
		'data-count' => 'horizontal',
		'data-lang' => 'ja'
	);

	foreach ($args as $i => $arg)
	{
		// data-text モード
		if (isset($attrs['data-text']))
		{
			$attrs['data-text'] .= ','. $arg;
			continue;
		}
		$or_arg = $arg;
		$arg = trim($arg);
		
		// url
		if ( ! $init_url && is_url($arg))
		{
			$attrs['data-url'] = $arg;
			$init_url = TRUE;
		}
		//data-count
		else if (strpos($arg, 'style=') === 0)
		{
			list($key, $val) = explode('=', $arg, 2);
			switch ($val)
			{
				case 'v':
				case 'vertical':
					$attrs['data-count'] = 'vertical';
					break;
				case 'h':
				case 'horizontal':
					$attrs['data-count'] = 'horizontal';
					break;
				case 'n':
				case 'none':
				default:
					$attrs['data-count'] = 'none';
			}
		}
		//data-lang
		else if (strpos($arg, 'lang=') === 0)
		{
			list($key, $val) = explode('=', $arg, 2);
			$attrs['data-lang'] = trim($val);
			if ($val == 'en')
			{
				unset($attrs['data-lang']);
			}
		}
		//data-via アカウント名
		else if (strpos($arg, 'user=') === 0 OR strpos($arg, 'via=') === 0)
		{
			list($key, $val) = explode('=', $arg, 2);
			$attrs['data-via'] = trim($val);
		}
		//data-related 関連アカウント名
		else if (strpos($arg, 'related=') === 0)
		{
			list($key, $val) = explode('=', $arg, 2);
			$attrs['data-related'] = $val;
		}
		//data-related 関連アカウントの説明
		else if (strpos($arg, 'rel_desc=') === 0)
		{
			list($key, $val) = explode('=', $arg, 2);
			$attrs['data-related-2'] = $val;
		}
		//data-text
		else
		{
			$attrs['data-text'] = $or_arg;
		}
	}
	
	//URLをセット
	//※日本語ページ名だと二重でURLエンコードされるため、data-url は必ずセットする
	//※index.php?URLENCODE(ページ名) というURLが安全じゃないとエラーが出るので短縮URLを採用
	
	if ($init_url === FALSE)
	{
		$attrs['data-url'] = $script . '?go=' . get_tiny_code($page);
	}
	
	if (isset($attrs['data-related']) && isset($attrs['data-related-2']))
	{
		$attrs['data-related'] = $attrs['data-related']. ':'. $attrs['data-related-2'];
	}
	else if (isset($attrs['data-related-2']) && ! isset($attrs['data-related']))
	{
		unset($attrs['data-related-2']);
	}
	
	$attr_str = '';
	foreach ($attrs as $key => $val)
	{
		$val = h($val);
		$attr_str .= ' '. $key. '="'. $val.'"';
	}
	
	$html = sprintf(PLUGIN_TW_bUTTON_FMT, $attr_str);
	
	return $html;
}

/* End of file tw_button.inc.php */
/* Location: ./plugin/tw_button.inc.php */