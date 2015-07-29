<?php
/**
 *   Set Google Search Plugin
 *   -------------------------------------------
 *   plugin/gsearch.inc.php
 *
 *   Copyright (c) 2015 hokuken
 *   http://hokuken.com/
 *
 *   created  :
 *   modified : 2015-06-17 Bootstrap スキンの分岐を追加
 *
 *   Google 検索、サイト内検索フォームを設置する
 *
 *   Usage :
 *     #gsearch([検索対象サイトURL])
 *
 */

function plugin_gsearch_convert()
{
	global $script;
	$qm = get_qm();

	$other_site = false;
	$search_www = false;
	$args = func_get_args();
	if (count($args) > 0) {
		$url = array_pop($args);
		if ($url === 'www')
		{
			$search_www = true;
		}
		else
		{
			$url_info = parse_url($script);
			$other_url_info = parse_url($url);
			if ($url_info['host'] !== $other_url_info['host'] ||
				$url_info['path'] !== $other_url_info['path'])
			{
				$other_site = true;
			}
		}
	} else {
		$url = dirname($script);
	}

	if (is_bootstrap_skin())
	{
		$label = $search_www ? 'Google 検索' : 'Google サイト内検索';
		return '<!-- SiteSearch Google -->
<div class="qhm-plugin-gsearch">
  <form method="get" action="http://www.google.co.jp/search">
    <input type="hidden" name="ie" value="UTF-8">
    <input type="hidden" name="oe" value="UTF-8">
    <input type="hidden" name="hl" value="ja">
    <input type="hidden" name="sitesearch" value="'.($search_www ? '' : h($url)).'">
    <div class="form-group">
      <div class="input-group">
        <input type="text" name="q" size="31" maxlength="255" placeholder="'. h($label) .'" class="form-control input-sm">
        <span class="input-group-btn">
          <input type="submit" value="検索" class="btn btn-default btn-sm">
        </span>
      </div>
      '. ($other_site ? '<span class="help-block">'.h($url).' 内を検索します</span>' : '' ) .'
    </div>
  </form>
</div>
<!-- /SiteSearch Google -->';

	}

	return '<!-- SiteSearch Google -->
<div class="qhm-plugin-gsearch" style="text-align:center">
  <form method="get" action="http://www.google.co.jp/search">
    <table bgcolor="#FFFFFF"><tr valign="top"><td>
      <a href="http://www.google.co.jp/">
      <img src="http://www.google.com/logos/Logo_40wht.gif"
border="0" alt="Google" align="absmiddle" /></a>
    </td>
    <td>
      <input type="text" name="q" size="31" maxlength="255" value="" />
      <input type="hidden" name="ie" value="UTF-8" />
      <input type="hidden" name="oe" value="UTF-8" />
      <input type="hidden" name="hl" value="ja" />
      <input type="submit" name="btnG" value="'. $qm->m['plg_gsearch']['btn_gsearch'] .'" />
      <span style="font-size:smaller">
        <input type="hidden" name="domains" value="'. h($url) .'" /><br />
        <label><input type="radio" name="sitesearch" value="" '. ($search_www ? 'checked' : '') .' /> '. $qm->m['plg_gsearch']['label_wsearch'] .'</label>
        <label><input type="radio" name="sitesearch" value="'. h($url).'" '. ($search_www ? '' : 'checked') .' /> '. $qm->replace('plg_gsearch.label_ssearch', h($url)). '</label>
      </span>
    </td></tr></table>
  </form>
</div>
<!-- /SiteSearch Google -->';

}
