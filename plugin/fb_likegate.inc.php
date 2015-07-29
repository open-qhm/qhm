<?php
/**
 *   Facebook Like-Gate Plugin
 *   -------------------------------------------
 *   fb_likegate.inc.php
 *   
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2011-07-26
 *   modified :
 *   
 *   「いいね！」ボタンを押した人だけ見られるページにできる
 *   
 *   Usage :
 *   
 */

function plugin_fb_likegate_init()
{
	if ( ! exist_plugin("fb_root"))
	{
		die('Cannot find fb_root plugin');
	}
	do_plugin_init("fb_root");
}

function plugin_fb_likegate_convert()
{
	global $vars, $script, $nowindow;
	$qm = get_qm();
	$qt = get_qt();

    $editable = edit_auth($page, FALSE, FALSE);

	//本文部分以外のページに設置した場合、無効にする
	if (isset($vars['page_alt']) && is_page($vars['page_alt']))
	{
		if ($editable)
		{
			return 'error: #fb_likegate: 本文に設置してください。';
		}
		else
		{
			return '';
		}
	}

	
    $page = isset($vars['page']) ? $vars['page'] : '';

    //キャッシュ無効
	$qt->enable_cache = false;
    
    $args = func_get_args();
	if (count($args) === 0)
	{
		return $qm->replace('fmt_err_cvt', 'fb_likegate', $qm->m['plg_fb_likegate']['err_usage']);
	}
	list($unlike_page) = $args;

	$fb_apps_url = plugin_fb_root_get_apps_url();

	//Facebook からのアクセス
	if ($signed_request = plugin_fb_root_parse_request())
	{
		$nowindow = 1;
		$edit_url = $script. '?cmd=edit&page='. rawurlencode($page);

		plugin_fb_root_set_page();

		$liked = (isset($signed_request->page->liked) AND ! is_null($signed_request->page->liked))?
			$signed_request->page->liked: $signed_request->page['liked'];
		if ($liked) {
			$src = get_source($page);
			foreach ($src as $i => $line)
			{
				if (strpos($line, '#fb_likegate') === 0)
				{
					unset($src[$i]);
					break;
				}
			}
			$body = convert_html($src);
		}
		else
		{
			
			$src = get_source($unlike_page);
			foreach ($src as $i => $line)
			{
				if (strpos($line, '#fb_page') === 0)
				{
					unset($src[$i]);
				}
			}
			$body = convert_html($src);
		}

		if ($editable)
		{
			$add_body = convert_html($qm->replace('plg_fb_likegate.ntc_admin_fb', $edit_url, $unlike_page, $fb_apps_url));
		}
		force_output_message('', '', $add_body . $body);
	}
	//通常アクセス
	else
	{
		if ($editable)
		{
			return convert_html($qm->replace('plg_fb_likegate.ntc_admin', $unlike_page, $fb_apps_url));
		}
		force_output_message($qm->m['plg_fb_likegate']['ntc_title'], '', $qm->m['plg_fb_likegate']['ntc_msg']);
	}

}

/* End of file fb_likegate.inc.php */
/* Location: ./plugin/fb_likegate.inc.php */