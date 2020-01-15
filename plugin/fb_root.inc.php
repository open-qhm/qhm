<?php
/**
 *   Facebook Plugins' Init File
 *   -------------------------------------------
 *   ./plugin/fb_root.inc.php
 *
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2011-08-10
 *   modified :
 *
 *   Description
 *
 *   Usage :
 *
 */

function plugin_fb_root_init()
{
	global $add_xmlns;
	static $inited = FALSE;

	if ($inited)
	{
		return;
	}
	$inited = TRUE;

	$qt = get_qt();
	$qt->setv('jquery_include', true);

}

/**
 * Facebook アプリIDを返す。
 * $fb_app_id がなければ、FALSEを返す。
 */
function plugin_fb_root_get_fb_app_id()
{
	global $fb_app_id;
	if (isset($fb_app_id) && strlen(trim($fb_app_id)) > 0)
	{
		return $fb_app_id;
	}
	return FALSE;
}

/**
 * Facebook 上で表示するために、
 * CSS, javascript をbeforescript へセットする
 */
function plugin_fb_root_set_page()
{
	global $vars;
	$qt = get_qt();

	plugin_fb_root_set_jsapi(FALSE);
	plugin_fb_root_set_page_css();
	plugin_fb_root_set_page_js();
}
function plugin_fb_root_set_page_css()
{
	global $vars;
	$qt = get_qt();

	$beforescript = '
<style type="text/css">
body {
	background: none;
	background-color: #fff;
	width: 520px;
	margin: 0;
	padding: 0;
	overflow-x: hidden;
}
#wrapper{
	width: 520px;
	margin-bottom: 30px;
	padding: 0;
	overflow: hidden;
	border: 0;
}
#headcopy,#header,#navigator,#navigator2,#footer,#licence,#wrap_sidebar,#wrap_sidebar2,#toolbar_upper_max,#toolbar_upper_min{
	display: none;
}
#wrap_content {
	width: 100%;
	border: none;
	margin: 0;
	padding: 0;
}
#main {
	width: auto;
	border: none;
	margin: 0;
	padding: 0;
}
#content {
	margin: 0;
	padding: 0;
	border: none;
}
#content h2.title {
	display: none;
}
#body h2, #body h3, #body h4 {
	margin-left: 0;
	margin-right: 0;
}
#body p {
	padding-left: 0;
	padding-right: 0;
}
</style>
';
	$qt->appendv('beforescript', $beforescript);
}
function plugin_fb_root_set_page_js()
{
	global $vars;
	$qt = get_qt();

	$appid = plugin_fb_root_get_fb_app_id();
	if ($appid === FALSE)
	{
		$appid = '0123456789';
	}

	$beforescript = '
<script>
function FB_init_callback() {
	FB.Canvas.setAutoGrow();
	//link mod
	$("#body a:not([href^=#])").attr("target", "_blank").attr("rel", "noopener")
		.filter("[href*=\'facebook.com\']:not([href*=\'developers.facebook.com\'])").attr("target", "_parent");
	$("form").append(\'<input type="hidden" name="signed_request" value="'.h($vars['signed_request']).'" /> \');
}
</script>
';
	$qt->appendv_once('plugin_fb_root_page_js', 'beforescript', $beforescript);
}

function plugin_fb_root_set_jsapi($xfbml = FALSE, $locale = 'ja_JP')
{
	$qt = get_qt();

	$params = array();

	$appid = plugin_fb_root_get_fb_app_id();
	if ($appid !== FALSE)
	{
		$params['appId'] = $appid;
	}

	if ($xfbml !== FALSE)
	{
		$params['xfbml'] = true;
	}

	$params['version'] = 'v2.3';

	$query = http_build_query($params);

	$beforescript = '
<script>
window.fbAsyncInit = function(){
	if ( ! document.getElementById("fb-root")) {
		var fbRoot = document.createElement("div"),
			body = document.getElementsByTagName("body")[0];
		fbRoot.id = "fb-root";
		body.insertBefore(fbRoot, body.firstChild);
	}
	FB.init('. json_encode($params) .');

	if (typeof FB_init_callback !== "undefined") {
		FB_init_callback();
	}
};
(function(d, s, id, callback) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) return;

	js = d.createElement(s);
	js.id = id;
	js.src = "//connect.facebook.net/'. h($locale). '/sdk.js";
	fjs.parentNode.insertBefore(js, fjs);
})(document, "script", "facebook-jssdk");
</script>
';
	$qt->appendv_once('plugin_fb_root_jsapi', 'lastscript', $beforescript);
}

function plugin_fb_root_parse_request()
{
	global $vars;
	if (isset($vars['signed_request']))
	{
		$encoded_sig = null;
		$payload = null;
		list($encoded_sig, $payload) = explode('.', $vars['signed_request'], 2);
		$sig = base64_decode(strtr($encoded_sig, '-_', '+/'));
		$data = json_decode(base64_decode(strtr($payload, '-_', '+/')));
		return $data;
	}
	return FALSE;
}


function plugin_fb_root_get_apps_url()
{
	return 	'https://developers.facebook.com/apps/';
}

function plugin_fb_root_get_fonts()
{
	$fonts = array('', 'arial', 'lucida grande', 'segoe ui', 'tahoma', 'trebuchet ms', 'verdana');

	return $fonts;
}

function plugin_fb_root_get_colorschemes()
{
	$colorschemes = array('light', 'dark');

	return $colorschemes;
}

/**
 * FB系プラグインに来る引数を解釈する
 *
 * @param array $args array of attributes scaffold
 *
 * Scaffold: [{attr_name: DEFAULT_VALUE}, {attr_name: [DEFAULT_VALUE, DATA_SET]}, ...]
 */
function plugin_fb_root_parse_args($args, $tmpl = array())
{
	$ret = $tmpl;

	$init_href = FALSE;

	foreach ($args as $i => $arg)
	{
		$arg = trim($arg);

		// href, site
		if ( ! $init_url && is_url($arg))
		{
			if (isset($ret['href']))
			{
				$ret['href'] = $arg;
			}
			else if (isset($ret['site']))
			{
				$parsed_url = parse_url($arg);
				$ret['site'] = $parsed_url['host'];
			}
			$init_url = TRUE;
		}
		// no send
		else if ($arg == 'nosend' && isset($ret['send']))
		{
			$ret['send'] = 'false';
		}
		// no faces
		else if ($arg == 'noface' && isset($ret['show-faces']))
		{
			$ret['show-faces'] = 'false';
		}
		// no header
		else if ($arg == 'noheader' && isset($ret['header']))
		{
			$ret['header'] = 'false';
		}
		// no stream
		else if ($arg == 'nostream' && isset($ret['stream']))
		{
			$ret['stream'] = 'false';
		}
		// no share button
		else if ($arg === 'noshare' && isset($ret['share']))
		{
    		$ret['share'] = 'false';
		}
		// do not show border
		else if ($arg === 'noborder' && isset($ret['show-border']))
		{
    		$ret['show-border'] = 'false';
		}
		else if ($arg === 'kids' && isset($ret['kid-directed-site']))
		{
    		$ret['kid-directed-site'] = 'true';
		}
		else if ($arg === 'mobile' && isset($ret['mobile']))
		{
    		$ret['mobile'] = 'true';
		}
		// force wall
		else if ($arg == 'force_wall' && isset($ret['force-wall']))
		{
			$ret['force-wall'] = 'true';
		}
		else if ($arg == 'hide_cover' && isset($ret['hide-cover']))
		{
			$ret['hide-cover'] = 'true';
		}
		else if ($arg == 'hide_facepile' && isset($ret['show-facepile']))
		{
			$ret['show-facepile'] = 'false';
		}
		else if ($arg == 'show_posts' && isset($ret['show-posts']))
		{
			$ret['show-posts'] = 'true';
		}
		else if ($arg == 'hide_cta' && isset($ret['hide-cta']))
		{
			$ret['hide-cta'] = 'true';
		}
		// layouts
		else if (strpos($arg, 'layout=') === 0 && isset($ret['layout']))
		{
			list($key, $val) = explode('=', $arg, 2);
			if (is_array($ret[$key]))
			{
				$default = $ret[$key][0];
				$opts = $ret[$key][1];
			}
			else
			{
				$opts = $ret[$key];
			}
			if (in_array($val, $opts))
			{
				$ret[$key] = $val;
			}
		}
		// fonts
		else if (strpos($arg, 'font=') === 0 && isset($ret['font']))
		{
			list($key, $val) = explode('=', $arg, 2);
			if (in_array($val, plugin_fb_root_get_fonts()))
			{
				$ret[$key] = $val;
			}
		}
		// Orders
		else if (strpos($arg, 'order=') === 0 && isset($ret['order-by']))
		{
			list($key, $val) = explode('=', $arg, 2);
			$key = 'order-by';
			if (is_array($ret[$key]))
			{
				$default = $ret[$key][0];
				$opts = $ret[$key][1];
			}
			else
			{
				$opts = $ret[$key];
			}
			if (in_array($val, $opts))
			{
				$ret[$key] = $val;
			}
		}
		// border color
		else if (strpos($arg, 'border_color=') === 0 && isset($ret['border_color']))
		{
			list($key, $val) = explode('=', $arg, 2);
			$ret[$key] = $val;
		}
		// color schemes
		else if (strpos($arg, 'colorscheme=') === 0 && isset($ret['colorscheme']))
		{
			list($key, $val) = explode('=', $arg, 2);
			if (in_array($val, plugin_fb_root_get_colorschemes()))
			{
				$ret[$key] = $val;
			}
		}
		// fb_likebutton:actions
		else if (strpos($arg, 'action=') === 0 && isset($ret['action']))
		{
			list($key, $val) = explode('=', $arg, 2);
			if (is_array($ret[$key]))
			{
				$opts = $ret[$key][1];
			}
			else
			{
				$opts = $ret[$key];
			}
			if (in_array($val, $opts))
			{
				$ret[$key] = $val;
			}
		}
		// fb_recommends:actions
		else if (strpos($arg, 'actions=') === 0 && isset($ret['action']))
		{
			list($key, $val) = explode('=', $arg, 2);
			$ret['action'] = trim(str_replace(' ', ',', $val));
		}
		// app id
		else if (strpos($arg, 'app_id=') === 0 && isset($ret['app-id']))
		{
			list($key, $val) = explode('=', $arg, 2);
			$ret['app-id'] = trim($val);
		}
		// link target
		else if (strpos($arg, 'linktarget=') === 0 && isset($ret['linktarget']))
		{
			list($key, $val) = explode('=', $arg, 2);
			$ret[$key] = trim($val);
		}
		// Max age
		else if (strpos($arg, 'age=') === 0 && isset($ret['max-age']))
		{
			list($key, $val) = explode('=', $arg, 2);
			$val = intval($val);
			if ($val >= 1 && 180 >= $val)
			{
    			$ret['max-age'] = $val;
			}
		}
		// ref
		else if (strpos($arg, 'ref=') === 0 && isset($ret['ref']))
		{
			list($key, $val) = explode('=', $arg, 2);
			if (preg_match('/^[a-zA-Z0-9+\/=.:_-]+$/', $val))
			{
				$ret[$key] = $val;
			}
		}
		// width
		else if (strpos($arg, 'width=') === 0 && isset($ret['width']))
		{
			list($key, $val) = explode('=', $arg, 2);
			if (preg_match('/^\d+$/', trim($val)))
			{
				$ret[$key] = $val;
			}
		}
		// height
		else if (strpos($arg, 'height=') === 0 && isset($ret['height']))
		{
			list($key, $val) = explode('=', $arg, 2);
			if (preg_match('/^\d+$/', trim($val)))
			{
				$ret[$key] = $val;
			}
		}
		// numposts(num)
		else if (strpos($arg, 'num=') === 0 && isset($ret['numposts']))
		{
			list($key, $val) = explode('=', $arg, 2);
			if (preg_match('/^\d+$/', trim($val)))
			{
				$ret['numposts'] = $val;
			}
		}

	}


	foreach ($ret as $key => $val)
	{
		if (is_array($val))
		{
			if ($val[0] !== FALSE)
			{
				$ret[$key] = $val[0];
			}
			else
			{
				unset($ret[$key]);
			}
		}
		else if ($val === FALSE)
		{
			unset($ret[$key]);
		}
	}

	return $ret;
}


function plugin_fb_root_create_tag($social_plugin_type, $attrs = array())
{
	$fmt = "<div class=\"{$social_plugin_type}\"%s></div>";
	$tag = '';
	if (is_array($attrs))
	{
		$attr_strs = array();
		foreach ($attrs as $attr => $val)
		{
			$attr_strs[] = 'data-' . $attr. '="'. h($val). '"';
		}
		if (count($attr_strs))
		{
			$tag = sprintf($fmt, ' '. join(' ', $attr_strs));
		}
	}
	else
	{
		$tag = sprintf($fmt, ' '. trim($attrs));
	}
	return $tag;
}

/**
* Determine Facebook social plugin is deprecated
*
* @param string $fb_plugin_name Facebook Social Plugin Name
* @return false|callable when enable alt plugin
*/
function plugin_fb_root_is_deprecated($fb_plugin_name)
{
	$expired = (strtotime('2015-06-23') < time());
	if ( ! $expired)
	{
		return FALSE;
	}

	$show_deprecated = 'plugin_fb_root_deprecated';

	switch ($fb_plugin_name)
	{
		case 'likebox':
			if (exist_plugin('fb_pagebox'))
				return 'plugin_fb_pagebox_convert';
			else
				return $show_deprecated;
		case 'recommends':
			return $show_deprecated;
	}
	return FALSE;
}

function plugin_fb_root_deprecated()
{
	global $vars;
	if ( ! edit_auth($vars['page'], FALSE, FALSE))
	{
		return '';
	}
	$backtrace = debug_backtrace();
	$plugin_name = str_replace(
		array('plugin_', '_convert', '_inline'),
		'',
		$backtrace[2]['function']
	);
	return <<< EOM
		<div class="alert alert-danger">
			<code>#{$plugin_name}</code> このプラグインは現在利用できません。
		</div>
EOM;
}
