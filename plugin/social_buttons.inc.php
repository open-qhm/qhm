<?php
/**
 *   Social Buttons Plugin
 *   -------------------------------------------
 *   ./plugin/social_buttons.inc.php
 *
 *   Copyright (c) 2012 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2012-03-12
 *   modified :
 *
 *   Description
 *
 *   Usage :
 *
 */

function plugin_social_buttons_convert()
{
	global $script, $vars;

	$qt = get_qt();
	$qt->setv('jquery_include', true);

	$args = func_get_args();

	$url = '';
	$layout = 'h1';//h1 | h2 | large
	$margin = '3px'; //margin-right per button
	$h_margin = '0'; //horizontal margin of buttons wrapper
	$text = ''; //extra text
	$float = 'right'; //left|right

	$service_list = array('twitter', 'facebook_like');

	$services = array();
	foreach ($args as $arg)
	{
		$arg = trim($arg);

		if (preg_match('/^tw(?:itter)?(?:=([^,\)]*))?$/', $arg, $mts))
		{
			$option_str = isset($mts[1]) ? $mts[1] : '';
			$services['twitter'] = plugin_social_buttons_parse_option($option_str);
		}
		else if (preg_match('/^(?:facebook|fb)(?:=([^,\)]*))?$/', $arg, $mts))
		{
			$option_str = isset($mts[1]) ? $mts[1] : '';
			$services['facebook_like'] = plugin_social_buttons_parse_option($option_str);
		}
		else if (in_array($arg, array('h1', 'h2', 'large')))
		{
			$layout = $arg;
			if ($layout === 'large')
			{
				$margin = '15px';
			}
		}
		else if (is_url($arg))
		{
			$url = $arg;
		}
		else if (preg_match('/^\d+$/', $arg))
		{
			$margin = intval($arg);
			$margin = ($margin > 0) ? $margin . 'px' : 0;
		}
		else if ($arg === 'right' OR $arg === 'left')
		{
			$float = $arg;
		}
		else
		{
			$text = $arg;
		}
	}

	if (count($services) === 0)
	{
		foreach (array_flip($service_list) as $service => $v)
		{
			$services[$service] = array();
		}
	}

	if ($float === 'right')
	{
		$services = array_combine(array_reverse(array_keys($services)), array_reverse(array_values($services)));
	}

	$use_fb = FALSE;
	foreach ($services as $service => $option)
	{
		switch ($service)
		{
			case 'twitter':
				switch ($layout)
				{
					case 'h1':
						$tmp = 'none';
						break;
					case 'h2':
						$tmp = 'horizontal';
						break;
					default: //large
						$tmp = 'vertical';
				}
				$option['button'] = $tmp;
				break;

			case 'facebook_like':
				$use_fb = TRUE;
				$width = 120;
				switch ($layout)
				{
					case 'h1':
						$width = 100;
					case 'h2':
						$tmp = 'button_count';
						break;
					default: //large
						$tmp = 'box_count';
				}
				$option['show_faces'] ='false';
				$option['layout'] = $tmp;
				$option['width'] = isset($option['width']) ? $option['width'] : $width;
				break;
		}

		$services[$service] = $option;
	}

	$tinyurl = $fburl =  $url;
	$page = $vars['page'];
	if ($url === '')
	{
		$fburl = $script.'?'.rawurlencode($page);
		$url = $script.'?'.rawurlencode($page);
		$tinyurl = $script . '?go=' . get_tiny_code($page);
	}

	$addscript = '
<style type="text/css">
.qhm_plugin_social_buttons {
	margin: 3px 0;
}
</style>
<script type="text/javascript" src="./js/jquery.socialbutton-1.9.1.js"></script>
<script type="text/javascript">
$(function(){
	$("div.qhm_plugin_social_button").each(function(){
		var $$ = $(this), service = $$.attr("data-sb-service"), option = {}, attrs = this.attributes;
		var cnt = 0, attr, opt_name;

		while (1)
		{
			attr = attrs.item(cnt);
			cnt++;
			if (attr == null) break;
			if (attr.name == "data-sb-service") continue;

			if (/^data-sb-(.+)$/.test(attr.name))
			{
				opt_name = attr.name.match(/^data-sb-(.+)$/)[1];
				option[opt_name] = /^false$/.test(attr.value) ? false: attr.value;
			}
		}

		$$.socialbutton(service, option);
	});
});
</script>
';
	$body = '
<div class="qhm_plugin_social_buttons">
';
	foreach ($services as $service => $option)
	{
		$option_attr = '';
		foreach ($option as $key => $val)
		{
			$option_attr .= ' data-sb-'. $key .'="'. h($val) .'"';
		}

		switch ($service)
		{
			case 'twitter':
				$service_url = $tinyurl;
				break;
			case 'facebook_like':
				$service_url = $fburl;
				break;
			default :
				$service_url = $url;
		}

		$body .= '<div class="qhm_plugin_social_button" data-sb-service="'. h($service) .'" data-sb-url="'. h($service_url) .'"'. $option_attr.' style="margin-right:'. h($margin) .';float:'. h($float) .';"></div>';
	}
	$body .= '
	<div style="clear:both;"></div>
</div>
';

	$editable = check_editable($page, FALSE, FALSE);

	//Facebook いいね！ボタンが表示されない問題への対処について。
	//原因は不明だが、Fbデバッガへ通すと改善する。
	//管理者にはFacebook のデバッガーへのリンクを表示する。

	if ($editable && $use_fb)
	{
		$debuggerlink = 'http://developers.facebook.com/tools/debug/og/object?q='. rawurlencode($fburl);
		$body .= '
<p style="text-align:'. h($float) .';margin-top: 0;">
<button type="button" class="" style="color:navy;cursor:pointer;border:none;background:none;" data-toggle="collapse" data-target="div.plugin_social_button_alert">
	<i class="icon icon-hand-up"></i>
	いいね！が表示されない
</button>
</p>

<div class="collapse out plugin_social_button_alert">
	<div class="alert alert-info">

	<p>
		以下の手順で復旧してください。
	</p>

	1. <a href="'. h($debuggerlink) .'" class="btn btn-default btn-sm">ここをクリック &gt;&gt;</a><br />
	2. Facebook のページが開いたら、そのまま閉じる。<br />
	3. このページを<a href="#" class="btn btn-default btn-sm" onclick="document.location.reload();return false;">再読み込み</a>する。<br />
	4. 完了
	</div>
</div>
';
	}

	$qt->appendv_once('plugin_social_buttons', 'beforescript', $addscript);

	return $body;
}

function plugin_social_buttons_parse_option($option_str = '')
{
	if (trim($option_str) === '') return array();

	$option = array();
	$options = explode(';', $option_str);
	foreach ($options as $opt)
	{
		list($name, $value) = explode(':', $opt, 2);
		$option[$name] = is_null($value) ? '' : $value;
	}

	return $option;
}
