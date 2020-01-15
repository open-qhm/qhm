<?php
/**
 *   Facebook Inframe Page Plugin for QLG
 *   -------------------------------------------
 *   qlg.inc.php
 *   
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2011-09-16
 *   modified :
 *   
 *   Quick LikeGate 上で表示するためにデザインを調整する
 *   
 *   Usage :
 *   
 */

define('PLUGIN_QLG_APP_URL', 'http://apps.facebook.com/quicklikegate/');
define('PLUGIN_QLG_VERIFY_META_FMT', '<meta name="QLG-Code" content="%s" />');

function plugin_qlg_init()
{
	if ( ! exist_plugin("fb_root"))
	{
		die('Cannot find fb_root plugin');
	}
}


function plugin_qlg_convert()
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
			return 'error: #qlg: 本文に設置してください。';
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
    $code = array_pop($args);
	$meta = FALSE;
    if ($code)
    {
    	$meta = sprintf(PLUGIN_QLG_VERIFY_META_FMT, $code);
		//set verify code
		$qt->appendv_once('plugin_qlg', 'beforescript', "\n". $meta);
    }

	// 現在、QLG からだと管理者かどうかは判別できない
	$fb_apps_url = plugin_fb_root_get_apps_url();

	//Facebook からのアクセス
	if ($signed_request = plugin_fb_root_parse_request())
	{
		$nowindow = 1;
		$edit_url = $script. '?cmd=edit&page='. rawurlencode($page);

		plugin_fb_root_set_page_css();

		$lines = get_source($page);
		foreach ($lines as $k=>$v)
		{
			if (strpos($v, '#qlg') === 0)
			{
				unset($lines[$k]);
			}
		}
	
		$body = convert_html($lines);

		if ($editable)
		{
			$add_body = convert_html($qm->replace('plg_fb_page.ntc_admin_fb', $edit_url, $fb_apps_url));
			
			if ($meta === FALSE)
			{
				$add_body .= convert_html('
&deco(b,red,,18){エラー：確認コードが設定されていません。};
Quick LikeGate のページへ移動して、確認コードをコピペしてください。
');
				// add_body end
			}
			$add_body .= '
Quick LikeGate は<a href="'. PLUGIN_QLG_APP_URL. '" target="_parent">こちら</a>。
';
			// add_body end
		}

		force_output_message('', '', $add_body . $body);
	}
	//通常アクセス
	else
	{
		if ($editable)
		{
			$ntc = '
\'\'【お知らせ】管理者モード以外のアクセスでは表示されません。\'\'
Quick LikeGate から読み込まれている場合のみ表示されます。
';
			// convert_html end
			
			if ($meta === FALSE)
			{
				$ntc .= '
&deco(b,red,,18){エラー：確認コードが設定されていません。};
Quick LikeGate のページへ移動して、確認コードをコピペしてください。
';
				// ntc end
			}
			$add_body = '
Quick LikeGate は<a href="'. PLUGIN_QLG_APP_URL. '" target="_blank" rel="noopener">こちら</a>。
';

			return convert_html($ntc). $add_body;
		}
		force_output_message($qm->m['plg_fb_likegate']['ntc_title'], '', $qm->m['plg_fb_likegate']['ntc_msg']);
	}

}

/* End of file fb_page.inc.php */
/* Location: ./plugin/fb_page.inc.php */