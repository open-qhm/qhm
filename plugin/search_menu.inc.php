<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: gsearch.inc.php,v 0.5 2007/10/12 19:21:08 henoheno Exp $
//
// gserach convert view plugin

// Allow CSS instead of <font> tag
// NOTE: <font> tag become invalid from XHTML 1.1
// ----

function plugin_search_menu_convert()
{
	global $script;
	$qm = get_qm();
	
	return <<<EOF
<div id="searchmenu">
<h2>{$qm->m['plg_search_menu']['hdr']}</h2>
<form action="{$script}" method="get">
<div style="text-align:center">
  <div class="form-group">
    <div class="input-group">
    	<input type="text" name="word" value="" tabindex="1" accesskey="k" class="form-control input-sm" />
      <span class="input-group-btn">
    	  <input type="submit" value="{$qm->m['plg_search']['btn']}" tabindex="2" accesskey="s" class="btn btn-default btn-sm" />
      </span>
    </div>
  </div>
  <div class="form-group">
    <label for="and_search" class="radio-inline" style="display:inline-block;line-height:normal">
    	<input type="radio" name="type" value="AND" checked="checked" id="and_search" tabindex="3" accesskey="a" />{$qm->m['plg_search']['lbl_and']}
    </label>
    <label for="or_search" class="radio-inline" style="display:inline-block;line-height:normal">
      <input type="radio" name="type" value="OR" id="or_search" tabindex="3" accesskey="o" />{$qm->m['plg_search']['lbl_or']}
    </label>
  </div>

	<input type="hidden" name="cmd" value="search" />
	<input type="hidden" name="encode_hint" value="ぷ" />
</div>
</form>
</div>
EOF;

}
?>