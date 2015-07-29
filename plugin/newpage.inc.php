<?php
// $Id: newpage.inc.php,v 1.15 2005/02/27 09:35:16 henoheno Exp $
//
// Newpage plugin

function plugin_newpage_convert()
{
	global $script, $vars, $BracketName;
	static $id = 0;
	$qm = get_qm();
	$qt = get_qt();

	if (PKWK_READONLY) return ''; // Show nothing

	$newpage = '';
	if (func_num_args()) list($newpage) = func_get_args();
	if (! preg_match('/^' . $BracketName . '$/', $newpage)) $newpage = '';

	$s_page    = h(isset($vars['refer']) ? $vars['refer'] : $vars['page']);
	$s_newpage = h($newpage);
	++$id;

	$ret = <<<EOD
<form action="$script" method="post">
 <div>
  <input type="hidden" name="plugin" value="newpage" />
  <input type="hidden" name="refer"  value="$s_page" />
  <label for="_p_newpage_$id">{$qm->m['plg_newpage']['label']}:</label>
  <input type="text"   name="page" id="_p_newpage_$id" value="$s_newpage" size="30" />
  <input type="submit" value="{$qm->m['plg_newpage']['btn_create']}" />
 </div>
</form>
EOD;
	
	return $ret;
}

function plugin_newpage_action()
{
	global $vars;
	$qm = get_qm();

	if (PKWK_READONLY) die_message($qm->m['fmt_err_pkwk_readonly']);

	if ($vars['page'] == '') {
		$retvars['msg']  = $qm->m['plg_newpage']['label'];
		$retvars['body'] = plugin_newpage_convert();
		
		if (preg_match('/id="([^"]+)"/', $retvars['body'], $ms)) {
			$domid = $ms[1];

			//jquery ライブラリの読み込み
			$qt = get_qt();
			$qt->setv('jquery_include', true);
			
			$addscript =<<< EOS
<script type="text/javascript">
jQuery(function(){
	jQuery("#$domid").focus().select();
});
</script>
EOS;

			$qt->appendv_once('plugin_select_fsize', 'beforescript', $addscript);

		}
		
		return $retvars;
	} else {
		$page    = strip_bracket($vars['page']);
		$r_page  = rawurlencode(isset($vars['refer']) ?
			get_fullname($page, $vars['refer']) : $page);
		$r_refer = rawurlencode($vars['refer']);

		pkwk_headers_sent();
		header('Location: ' . get_script_uri() .
			'?cmd=read&page=' . $r_page . '&refer=' . $r_refer);
		exit;
	}
}
?>
