<?php
/**
 *   Output HTML Plugin
 *   -------------------------------------------
 *   plugin/html.inc.php
 *   
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 
 *   modified : 2011-03-03 iframe オプションを追加
 *   
 *   HTMLをそのまま出力します。
 *   
 *   Usage :
 *     #html{{\n
 *     ...HTML...
 *     }}\n
 *   
 */

function plugin_html_action()
{
	global $vars, $script;
	
	$page = $vars['page'];
	$id = $vars['id'];
	
	$lines = get_source($page);
	$line_num = count($lines);
	
	$html_cnt = 0;
	
	$html = '';
	for ($i = 0; $i < $line_num; $i++) {
		$line = $lines[$i];
		if (preg_match('/^#html[^\w]/', $line)) {
			$html_cnt++;
			if ($id == $html_cnt) {
				for($j = $i + 1; $j < $line_num; $j++) {
					$line = $lines[$j];
					if (preg_match('/^}}$/', $line)) {
						break;
					} else {
						$html .= $line;
					}
				}
			}
		}
	}
	
	
	pkwk_common_headers();
	$body = "<html><head></head><body style=\"margin:0;padding:0;\">{$html}</body></html>";
	echo $body;
	exit;

}


function plugin_html_convert()
{
	global $script, $vars;
	$qm = get_qm();
	$qt = get_qt();
	
	$page = isset($vars['page_alt'])? $vars['page_alt']: $vars['page'];

	$ids = $qt->getv('plugin_html_id');
	if (!$ids) {
		$ids = array(
			$page => 0
		);
	}
	$id = ++$ids[$page];
	$qt->setv('plugin_html_id', $ids);

	
	if (! (PKWK_READONLY > 0 or is_freeze($page) or plugin_html_is_edit_auth($page))) {
		return $qm->replace('fmt_msg_not_editable', '#html', $page);
	}

	$args   = func_get_args();
	$body   = array_pop($args);
	
	$size = '';
	$class = 'autofit_iframe';
	foreach ($args as $arg) {
		if ($arg == 'noskin') {
			$noskin = true;
			break;
		}
		else if ($arg == 'iframe') {
			$iframe = true;
		}
		//iframe size
		else if (preg_match('/^(\d+)(?:x(\d+))?$/', $arg, $mts)) {
			$x = "width:{$mts[1]}px;";
			$y = isset($mts[2])? "height:{$mts[2]}px;": '';
			$size = $x . $y;
			$class = '';
		}
	}
	
	if ($noskin) {
		pkwk_common_headers();
		print $body;
		exit;
	}
	else if ($iframe) {
		$qt->setv('jquery_include', true);
		exist_plugin('iframe');
		$qt->appendv_once('plugin_iframe', 'beforescript', PLUGIN_IFRAME_FIT_IFRAME_JS);

		$r_page = rawurlencode($page);
		$body = '<iframe src="'.$script. '?cmd=html&page='.$r_page.'&id='.$id.'" frameborder="0" class="'. $class .'" style="'. $size .'"></iframe>';
	}
	return $body;
}

function plugin_html_is_edit_auth($page, $user = '')
{
	global $edit_auth, $edit_auth_pages, $auth_method_type;
	if (! $edit_auth) {
		return FALSE;
	}
	// Checked by:
	$target_str = '';
	if ($auth_method_type == 'pagename') {
		$target_str = $page; // Page name
	} else if ($auth_method_type == 'contents') {
		$target_str = join('', get_source($page)); // Its contents
	}

	foreach($edit_auth_pages as $regexp => $users) {
		if (preg_match($regexp, $target_str)) {
			if ($user == '' || in_array($user, explode(',', $users))) {
				return TRUE;
			}
		}
	}
	return FALSE;
}
?>
