<?php
/**
 *   OpenGraphProtocol Plugin
 *   -------------------------------------------
 *   ogp.inc.php
 *
 *   Copyright (c) 2011 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2011-10-28
 *   modified :
 *
 *   Description
 *
 *   Usage :
 *
 */

function plugin_ogp_convert()
{
	global $script, $vars, $ogp_tag;

	//$ogp_tag を有効にする
	$ogp_tag = 1;

	$qt = get_qt();
	$data = $qt->getv('plugin_ogp_tags');
	if ( ! $data) {
		$data = array();
	}

	//管理者かどうか
    $editable = edit_auth($page, FALSE, FALSE);

	//引数を解析する
	$args = func_get_args();
	$params = explode("\r", array_pop($args));
	$desc = '';

	$aliases = plugin_ogp_get_aliases();
	foreach ($params as $i => $param)
	{
		if (preg_match('/^([\w:]+)=(.*)$/', $param, $mts))
		{
			$key = $mts[1];
			$val = $mts[2];

			if (isset($aliases[$key]))
			{
				$key = $aliases[$key];
			}
			$data[$key] = $val;
		}
		else
		{
			$desc .= $param. "\n";
		}
	}

	if (trim($desc) != '')
	{
		$data['og:description'] = $desc;
	}

	if ( ! $qt->getv('plugin_ogp_tags'))
	{
		$qt->setv('plugin_ogp_tags', $data);
	}

	$html = '';
	if ($editable)
	{
		$editlink = $script. '?cmd=edit&page='. rawurlencode($vars['page']);
		$conflink = $script. '?cmd=qhmsetting&phase=sns&mode=form';
		$fb_debugger_link = 'http://developers.facebook.com/tools/debug/og/object?q='. rawurlencode($script. '?'. rawurlencode($vars['page']));
		$wiki = '
----
\'\'【お知らせ】Open Graph Protocol タグが変更されています。\'\'
変更するには[[このページを編集してください>'. $editlink. ']]

例えばFacebook での表示は以下のようになります。
#style(class=box_gray_ssm){{
#{ogp_preview}
}}
※Facebook のチェックツールは[[こちら>'. $fb_debugger_link.']]
※画像がない場合、OGP設定のサイト画像が表示されます。
サイト画像の変更は[[こちら>'. $conflink. ']]
----
';
		$html .= str_replace('#{ogp_preview}', plugin_ogp_get_preview(), convert_html($wiki));
	}

	return $html;
}

/**
 * Set OGP Tag
 */
function plugin_ogp_set($ogp_tag_name, $value)
{
	$qt = get_qt();
	$data = $qt->getv('plugin_ogp_tags');
	if ( ! $data)
	{
		$data = array();
	}
	$data[$ogp_tag_name] = $value;
	$qt->setv('plugin_ogp_tags', $data);
}

function plugin_ogp_get_defdata()
{
	global $script, $vars, $defaultpage, $page_title, $description;
	global $og_description, $fb_admins, $fb_app_id, $qblog_defaultpage;
	$qt = get_qt();

	$url = $script;
	$page = $vars['page'];
	if ($defaultpage != $page)
	{
		$r_page = rawurlencode($vars['page']);
		$url .= '?'. $r_page;
		$this_page_title = $qt->getv('this_page_title');
		if ($this_page_title === FALSE)
		{
			$this_page_title = $vars['page']. ' - '. $page_title;
		}
	}
	else
	{
		$this_page_title = $page_title;
	}

	$type = ($page !== $qblog_defaultpage && is_qblog())
		? 'article'
		: 'website';

	$defdata = array(
		//Facebook
		'fb:app_id'      => $fb_app_id != ''? $fb_app_id: FALSE,
		'fb:admins'      => $fb_admins != ''? $fb_admins: FALSE,

		//OGP
		'og:locale'      => 'ja_JP',//TODO: conf
		'og:type'        => $type,//website, article, blog
		'og:title'       => $this_page_title,//this_page_title
		'og:url'         => $url,//
		'og:site_name'   => $page_title,//page_title
		'og:description' => $og_description != ''? $og_description: $description,
		'og:image'       => FALSE,
	);

	return $defdata;
}

/**
 * Get Aliases of tag name
 */
function plugin_ogp_get_aliases()
{
	return array(
		'locale'      => 'og:locale',
		'type'        => 'og:type',
		'title'       => 'og:title',
		'url'         => 'og:url',
		'site_name'   => 'og:site_name',
		'description' => 'og:description',
		'image'       => 'og:image',
		'app_id'      => 'fb:app_id',
		'fb_app_id'   => 'fb:app_id',
		'admins'      => 'fb:admins',
		'fb_admins'   => 'fb:admins',
	);
}

/**
 * Set OGP Tags to QHM Template
 */
function plugin_ogp_set_template()
{
	global $ogp_tag, $add_xmlns;

	if ( ! $ogp_tag) {
		return;
	}

	$qt = get_qt();

    $editable = edit_auth($page, FALSE, FALSE);

	$defdata = plugin_ogp_get_defdata();

	//先にセットしたデータを取得
	if ($data = $qt->getv('plugin_ogp_tags'))
	{
		$data = array_merge($defdata, $data);
	}
	else
	{
		$data = $defdata;
	}

	//画像（og:image）がなければ、showプラグインで使った最初の画像を探す。
	if ( ! isset($data['og:image']) OR $data['og:image'] === FALSE)
	{
		if ($fimg = $qt->getv('first_image'))
		{
			$data['og:image'] = $fimg;
		}
		else
		{
			$data['og:image'] = plugin_ogp_get_defaultimage();
		}
	}

	//set ogp tags
	$beforescript = '';
	foreach ($data as $prop => $content)
	{
		if ($content !== FALSE)
			$beforescript .= '<meta property="'. h($prop). '" content="'. h($content). '" />'. "\n";
	}

	$qt->appendv('beforescript', $beforescript);

}

function plugin_ogp_get_preview()
{
	global $ogp_tag, $add_xmlns;

	if ( ! $ogp_tag) {
		return '';
	}

	$qt = get_qt();

	$defdata = plugin_ogp_get_defdata();

	//先にセットしたデータを取得
	if ($data = $qt->getv('plugin_ogp_tags'))
	{
		$data = array_merge($defdata, $data);
	}
	else
	{
		$data = $defdata;
	}

	//デフォルト画像を使うかどうか
	$use_firstimg = TRUE;
	if ($data['og:image'] === FALSE)
	{
		$data['og:image'] = plugin_ogp_get_defaultimage();
	}
	else
	{
		$use_firstimg = FALSE;
	}

	//set preview
	$preview_fmt = '
<div style="width:90px;float:left;margin-right:10px;"><img src="#{$og:image}" style="max-width:90px;max-height:90px;" class="ogp_preview" /></div>
<div style="width:400px;float:left;">
	<a href="#{$og:url}#" style="color:#3B5998;font-size:11px;font-weight:bold;line-height:1.2B;">#{$og:title}</a><br />
	<span style="color:gray;font-size:11px;line-height:1.2B;">#{$og:description}</span>
</div>
<div style="clear:both;"></div>
';
	$ptns = array();
	$rpls = array();

	foreach ($data as $prop => $content)
	{
		$ptns[] = h('#{$'. $prop. '}');
		$rpls[] = nl2br($content);
	}

	$preview = str_replace($ptns, $rpls, $preview_fmt);

	if ($use_firstimg)
	{
		$beforescript = '
<script type="text/javascript">
$(function(){
	var $ogp_thumb = $("img.ogp_preview");
	if ($ogp_thumb.length > 0) {
		if (typeof QHM.first_image != "undefined") {
			$ogp_thumb.attr("src", QHM.first_image);
		}
	}
});
</script>
';
	}
	$qt->appendv_once('plugin_ogp_preview_beforescript', 'beforescript', $beforescript);

	return $preview;
}

function plugin_ogp_get_defaultimage()
{
	global $script, $og_image;
	if ( ! is_null($og_image) && $og_image != '')
	{
		return $og_image;
	}
	else
	{
		return dirname($script). '/image/hokuken/ogp_default.png';
	}
}

/* End of file ogp.inc.php */
/* Location: ./plugin/ogp.inc.php */
