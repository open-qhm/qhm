<?php
// $Id: template.inc.php,v 1.21 2005/02/27 08:06:48 henoheno Exp $
//
// Load template plugin

define('MAX_LEN', 60);

function plugin_template_action()
{
	global $script, $vars;
	$qm = get_qm();

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');
	if (! isset($vars['refer']) || ! is_page($vars['refer']))
		return FALSE;

	$lines = get_source($vars['refer']);

	// Remove '#freeze'
	if (! empty($lines) && strtolower(rtrim($lines[0])) == '#freeze')
		array_shift($lines);

	$begin = (isset($vars['begin']) && is_numeric($vars['begin'])) ? $vars['begin'] : 0;
	$end   = (isset($vars['end'])   && is_numeric($vars['end']))   ? $vars['end'] : count($lines) - 1;
	if ($begin > $end) {
		$temp  = $begin;
		$begin = $end;
		$end   = $temp;
	}
	$page    = isset($vars['page']) ? $vars['page'] : '';
	$is_page = is_page($page);

	// edit
	if ($is_pagename = is_pagename($page) && (! $is_page || ! empty($vars['force']))) {
		$postdata       = join('', array_splice($lines, $begin, $end - $begin + 1));
		$retvar['msg']  = $qm->m['fmt_title_edit'];
		$retvar['body'] = edit_form($vars['page'], $postdata);
		$vars['refer']  = $vars['page'];
		return $retvar;
	}
	$begin_select = $end_select = '';
	for ($i = 0; $i < count($lines); $i++) {
		$line = htmlspecialchars(mb_strimwidth($lines[$i], 0, MAX_LEN, '...'));

		$tag = ($i == $begin) ? ' selected="selected"' : '';
		$begin_select .= "<option value=\"$i\"$tag>$line</option>\n";

		$tag = ($i == $end) ? ' selected="selected"' : '';
		$end_select .= "<option value=\"$i\"$tag>$line</option>\n";
	}

	$_page = htmlspecialchars($page);
	$msg = $tag = '';
	if ($is_page) {
		$msg = $qm->m['plg_template']['err_already'];
		$tag = '<input type="checkbox" name="force" value="1" />'.$qm->m['plg_template']['force'];
	} else if ($page != '' && ! $is_pagename) {
		$msg = str_replace('$1', $_page, $qm->m['plg_template']['err_invalid']);
	}

	$s_refer = htmlspecialchars($vars['refer']);
	$s_page  = ($page == '') ? str_replace('$1', $s_refer, $qm->m['plg_template']['page']) : $_page;
	$ret     = <<<EOD
<form action="$script" method="post">
 <div>
  <input type="hidden" name="plugin" value="template" />
  <input type="hidden" name="refer"  value="$s_refer" />
  {$qm->m['plg_template']['start']} <select name="begin" size="10">$begin_select</select><br /><br />
  {$qm->m['plg_template']['end']}   <select name="end"   size="10">$end_select</select><br /><br />
  <label for="_p_template_refer">{$qm->m['plg_template']['refer']}</label>
  <input type="text" name="page" id="_p_template_refer" value="$s_page" />
  <input type="submit" name="submit" value="{$qm->m['plg_template']['btn_create']}" /> $tag
 </div>
</form>
EOD;

	$retvar['msg']  = ($msg == '') ? $qm->m['plg_template']['title'] : $msg;
	$retvar['body'] = $ret;

	return $retvar;
}
?>
