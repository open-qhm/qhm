<?php
/**
 *   Facebook Inframe Page Plugin
 *   -------------------------------------------
 *   fb_page.inc.php
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

function plugin_fb_page_init()
{
	if ( ! exist_plugin("fb_root"))
	{
		die('Cannot find fb_root plugin');
	}
	do_plugin_init("fb_root");
}


function plugin_fb_page_convert()
{
	global $vars, $script, $nowindow;
	$qm = get_qm();
	$qt = get_qt();
	$qt->setv('jquery_include', true);
	
    $editable = edit_auth($page, FALSE, FALSE);

	//本文部分以外のページに設置した場合、無効にする
	if (isset($vars['page_alt']) && is_page($vars['page_alt']))
	{
		if ($editable)
		{
			return 'error: #fb_page: 本文に設置してください。';
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

	$fb_apps_url = plugin_fb_root_get_apps_url();

	//Facebook からのアクセス
	if ($signed_request = plugin_fb_root_parse_request())
	{
		$nowindow = 1;
		$edit_url = $script. '?cmd=edit&page='. rawurlencode($page);

		plugin_fb_root_set_page();

		$lines = get_source($page);
		foreach ($lines as $k=>$v)
		{
			if (strpos($v, '#fb_page') === 0)
			{
				unset($lines[$k]);
			}
		}
	
		$body = convert_html($lines);

		if ($editable)
		{
			$add_body = convert_html($qm->replace('plg_fb_page.ntc_admin_fb', $edit_url, $fb_apps_url));
		}
		force_output_message('', '', $add_body . $body);
	}
	//通常アクセス
	else
	{
		if ($editable)
		{
			return convert_html($qm->m['plg_fb_page']['ntc_admin']);
		}
		force_output_message($qm->m['plg_fb_likegate']['ntc_title'], '', $qm->m['plg_fb_likegate']['ntc_msg']);
	}

}

/* End of file fb_page.inc.php */
/* Location: ./plugin/fb_page.inc.php */