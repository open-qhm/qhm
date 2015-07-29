<?php

define('PLUGIN_JPLAYER_THEME_DIR', PLUGIN_DIR.'jplayer/themes/');
define('PLUGIN_JPLAYER_THEME_FILE', 'theme.html');
define('PLUGIN_JPLAYER_THEME_CSS', 'theme.css');
define('PLUGIN_JPLAYER_THEME_DEFAULT', 'default');
define('PLUGIN_JPLAYER_THUMBNAIL', 'thumbnail.png');

// $Id$
function plugin_jplayer_convert()
{
	static $s_jplayer_cnt = 0, $theme;

	$qm = get_qm();
	$qt = get_qt();

	//jquery ライブラリの読み込み
	$qt->setv('jquery_include', true);
	
    $args = func_get_args();
    $last = func_num_args() - 1;
	if ($args[$last] == 'auto') {
		return '';
	}
	
	$body = '';
	$body = array_pop($args);

	$options = array();
    foreach ($args as $arg) {
        list($key, $val) = explode('=', $arg, 2);
        $options[$key] = htmlspecialchars($val);
    }
	$options['auto'] = isset($options['auto']) ? 'true' : 'false';
	$options['showlist'] = 'false';
	
	//theme は1ページに付き一つだけ
	if ( ! isset($theme))
	{
		//スタイルを切り替える
		if ( ! isset($options['style']) 
			OR ! file_exists(PLUGIN_JPLAYER_THEME_DIR.$options['style'].'/'.PLUGIN_JPLAYER_THEME_FILE))
		{
			$options['style'] = PLUGIN_JPLAYER_THEME_DEFAULT;
		}
		$theme = $options['style'];
	}
	else
	{
		$options['style'] = $theme;
	}
	$options['style'] = PLUGIN_JPLAYER_THEME_DIR.$options['style'];

	//アートワークがあるかどうか調べる
	if (isset($options['artwork']))
	{
		//なければデフォルト画像を使う
		if ( ! is_url($options['artwork']) && ! file_exists(trim($options['artwork'])))
		{
			$options['artwork'] = $options['style'].'/'.PLUGIN_JPLAYER_THUMBNAIL;
		}
	}
	
	$flist_js = '';
    if (isset($body)) {
	    $body = str_replace("\r", "\n", str_replace("\r\n", "\n", $body));
        $lines = explode("\n", $body);
	    foreach ($lines as $l) {
	    	if ($l != '') {
		    	list($name, $mp3, $poster) = array_pad(explode(',', $l), 3, '');
		    	
				//アートワークがなければデフォルト画像を使う
				if ( ! is_url($poster) &&  ! file_exists(trim($poster)))
				{
					$poster = isset($options['artwork']) ? $options['artwork'] : ($options['style'].'/'.PLUGIN_JPLAYER_THUMBNAIL);
				}
				
		    	$flist_js .= '{title:"'.$name.'", mp3:"'.$mp3.'", poster:"'.$poster.'"},';
	    	}
	    }
	    if ($flist_js != '') $flist_js = substr($flist_js, 0, -1);
    }

    // はじめての定義の場合、jQueryを出力
	$head = '
<link rel="stylesheet" type="text/css" href="'.h($options['style'].'/'.PLUGIN_JPLAYER_THEME_CSS).'" media="all" />
<script type="text/javascript" src="plugin/jplayer/jquery.jplayer.js"></script>
<script type="text/javascript" src="plugin/jplayer/jplayer.playlist.js"></script>
<script type="text/javascript" src="plugin/jplayer/jquery.jplayer.plugin.js"></script>
';
	$qt->appendv_once('plugin_jplayer', 'beforescript', $head);

	$pid = "jquery_jplayer_" . $s_jplayer_cnt++;
	if (strlen($flist_js) > 0)
	{
		$options['showlist'] = 'true';
	}

 	$attributes = 'id="'.h($pid).'" jp-auto="'.h($options['auto']).'" jp-showlist="'.h($options['showlist']).'" jp-artwork="'. h($options['artwork']) .'"';

	ob_start();
	include($options['style'].'/'.PLUGIN_JPLAYER_THEME_FILE);
	$out = ob_get_clean();
	$out = str_replace('#{$attributes}',$attributes, $out);
	$out .= '
<script type="text/javascript">
$("#'. $pid .'").data("playList.jplayer", ['.$flist_js.']);
</script>
';

    return $out;
}

?>