<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: update_tinycode.inc.php,v 1.2 2011/02/02 12:01:00 hokuken.com Exp $
//
// Update entities plugin - Update XHTML entities from DTD
// (for admin)

function plugin_update_tinycode_action()
{
	global $script, $vars, $defaultpage;
	global $username;
	
	$qm = get_qm();

	//check admin, setting 
	if($username != $_SESSION['usr']
		&& $vars['phase']!='user2'
		&& $vars['phase'] != 'script'
		&& $vars['phase'] != 'sssavepath'){
		return array(
			'msg'=> $qm->m['plg_update_tinycode']['title_err_auth'],
			'body'=> $qm->m['plg_update_tinycode']['err_auth']
		);
	}

	if (PKWK_READONLY) die_message($qm->m['fmt_err_pkwk_readonly']);
	

// テンプレート指定

$tinycode_title = $qm->m['plg_update_tinycode']['title'];
$body_top = '
* '. $qm->m['plg_update_tinycode']['title'] .' 

#contents
';

$body_page = '
** '. $qm->m['plg_update_tinycode']['hdr_update'] .'

'. $qm->m['plg_update_tinycode']['ntc_update'] .'

- '. $qm->m['plg_update_tinycode']['update_pagename'] .'
%update_tinycode%

%update_form%

&br;

';

$body_list = '
** '. $qm->m['plg_update_tinycode']['hdr_list'] .' [#list]

'. $qm->m['plg_update_tinycode']['ntc_list'] .'

%list_form%

';

$body_clean = '
** '. $qm->m['plg_update_tinycode']['hdr_clean'] .' [#clean]

'. $qm->m['plg_update_tinycode']['ntc_clean'] .'

%clean_form%
&br;

';

$body_reset = '
** '. $qm->m['plg_update_tinycode']['hdr_reset'] .' [#reset]

'. $qm->m['plg_update_tinycode']['ntc_reset'] .'

%reset_form%
&br;

';

$body_verup = '
** '. $qm->m['plg_update_tinycode']['hdr_init'] .' [#verup]

'. $qm->m['plg_update_tinycode']['ntc_init'] .'

%verup_form%
&br;

';

	$pagename = $vars['page'];
	$go_tinycode_top = '<p style="text-align:right"><a href="'.$script.'?cmd=update_tinycode'.'">&gt;&gt;&nbsp;'.$tinycode_title.'</a></p>';

	$msg = $body = '';
	if (empty($vars['action'])) {
		if ($pagename != '') {
			if (!is_page($pagename)) {
				$btn_name = $qm->m['plg_update_tinycode']['btn_delete'];
				$btn_action = 'clean';
				$deletestyle= ' style="color:red;"';
			}
			else {
				$btn_name = $qm->m['plg_update_tinycode']['btn_update'];
				$btn_action = 'update';
			}
			$_go_url = $script.'?go='.get_tiny_code($pagename);
			
			$update_page = '<span'.$deletestyle.'>'.$pagename.'</span>';
			$update_tinycode = '<input type="text" value="'.$_go_url.'" readonly="readonly"  onclick="this.select();" style="width:400px;" />';

			$update_form  = '
<form method="POST" action="'.$script.'">
<input type="hidden" name="plugin" value="update_tinycode" />
<input type="hidden" name="action" value="'.$btn_action.'" />
<input type="hidden" name="page" value="'.h($pagename).'" />
<input type="submit" value="'.$btn_name.'" />
</form>
'.$go_tinycode_top;
			
			
			$body = convert_html($body_top.$body_page);
			$body = str_replace('%update_page%', $update_page, $body);
			$body = str_replace('%update_tinycode%', $update_tinycode, $body);
			$body = str_replace('%update_form%', $update_form, $body);
		}
		else {
			$tiny_table = get_tiny_table(false);
			$list_form = '<div style="overflow:auto;border:1px solid #dcdcdc;padding:5px 10px;margin-left:auto;margin-right:auto;text-align:justify;width:450px;height:300px">';
			if (count($tiny_table) > 0 ) {
				$list_form .= '<ul class="list1">'; 
				ksort($tiny_table);
				foreach($tiny_table as $pname => $code){
					$delstyle = '';
					if (!is_page($pname)) {
						$delstyle = ' style="color:red;"';
					}
					if (!preg_match("/^:[config|RenameLog]|InterWikiName|InterWiki/",$pname)) {
						$_go_url = $script.'?go='.get_tiny_code($pname);
						$list_form .= '<li style="margin-bottom:0.5em;"><a'.$delstyle.' href="'.$script.'?cmd=update_tinycode&page='.h($pname).'">'.h($pname).'</a><br /><input type="text" value="'.$_go_url.'" readonly="readonly"  onclick="this.select();" style="width:400px;" /></li>';
					}
				}
				$list_form .= '</ul>';
			}
			$list_form .= '</div>';


			// クリーニング
			$clean_form = '
<form method="POST" action="'.$script.'">
<input type="hidden" name="plugin" value="update_tinycode" />
<input type="hidden" name="action" value="clean" />
<input type="submit" name="clean" value="'. $qm->m['plg_update_tinycode']['btn_clean'] .'" />
</form>';

			// リセット
			$reset_form = '
<form method="POST" action="'.$script.'">
 <div>
  <input type="hidden" name="plugin" value="update_tinycode" />
  <input type="hidden" name="action" value="reset" />
  <label for="_p_update_entities_adminpass">'. $qm->m['adminpass'] .'</label>
  <input type="password" name="adminpass" id="_p_update_entities_adminpass" size="20" value="" />
  <input type="submit" value="'. $qm->m['plg_update_tinycode']['btn_reset'] .'" />
 </div>
</form>
';

			$body = convert_html($body_top.$body_list.$body_clean.$body_reset);
			$body = str_replace('%list_form%', $list_form, $body);
			$body = str_replace('%clean_form%', $clean_form, $body);
			$body = str_replace('%reset_form%', $reset_form, $body);
		}
	}
	else if ($vars['action'] == 'versionup') {
		// バージョンアップ
		$verup_form = '
<form method="POST" action="'.$script.'">
 <div>
  <input type="hidden" name="plugin" value="update_tinycode" />
  <input type="hidden" name="action" value="reset" />
  <label for="_p_update_entities_adminpass">'. $qm->m['adminpass'] .'</label>
  <input type="password" name="adminpass" id="_p_update_entities_adminpass" size="20" value="" />
  <input type="submit" value="'. $qm->m['plg_update_tinycode']['btn_init'] .'" />
 </div>
</form>
';

		$body = convert_html($body_top.$body_verup);
		$body = str_replace('%verup_form%', $verup_form, $body);
	}
	else if ($vars['action'] == 'update') {
		plugin_update_tinycode_update($pagename);
		$msg  = $tinycode_title;
		$body = $qm->m['plg_update_tinycode']['updated'] . $go_tinycode_top;
	}
	else if ($vars['action'] == 'clean') {
		plugin_update_tinycode_clean($pagename);
		$msg  = $tinycode_title;
		$body = $qm->m['plg_update_tinycode']['updated'] . $go_tinycode_top;
	}
	else if ($vars['action'] == 'reset' && !empty($vars['adminpass']) && pkwk_login($vars['adminpass'])) {
		plugin_update_tinycode_reset();
		$msg  = $tinycode_title;
		$body = $qm->m['plg_update_tinycode']['updated'] . $go_tinycode_top;
	}
	else {
		$msg  = $tinycode_title;
		$body = $qm->m['plg_update_tinycode']['err_invalid_action'] . $go_tinycode_top;
	}

	return array('msg'=>$msg, 'body'=>$body);
}

/**
 *   短縮URLテーブルを再構築する。
 *   すべての短縮URLを作り直す
 */
function plugin_update_tinycode_reset() {
	global $defaultpage;
	
	$qm = get_qm();
	
	//まずファイルサイズを0にする
	$file = CACHE_DIR.QHM_TINYURL_TABLE;
	$fp = fopen($file, 'w') or
		die_message($qm->replace('file.err_cannot_open', h($file)));
	set_file_buffer($fp, 0);
	flock($fp, LOCK_EX);
	
	fputs($fp, '');
	
	flock($fp, LOCK_UN);
	fclose($fp);
	
	//再構築
	$exists = get_existpages();
	
	foreach($exists as $v){
		add_tinycode($v);
	}
	
	return true;

}

/**
 *   短縮URLテーブルを整理する。
 *   ページは存在するが短縮コードが存在しない場合、追加し、
 *   ページが存在しないのに短縮コードが存在する場合、削除する。
 *
 *   @param $pagename <string>: 個別に削除する場合、ページ名を指定する
 */
function plugin_update_tinycode_clean($pagename = ''){

	if ($pagename) {
		if (is_page($pagename)) {
			return false;
		}
		else {
			del_tinycode($pagename);
			return true;
		}
	}

	global $defaultpage;
	
	//dbがなければ、自動的に作られる
	add_tinycode($defaultpage);
	
	//file add
	$exists = get_existpages();
	$tcodes = get_tiny_table();
	$list = array_diff($exists, $tcodes);
	
	foreach($list as $v){
		add_tinycode($v);
	}
	
	//file del
	$tcodes = get_tiny_table();
	$list = array_diff($tcodes, $exists);

	foreach($list as $v){
		del_tinycode($v);
	}
}

/**
 *   指定したページの短縮コードを振り直す
 */
function plugin_update_tinycode_update($pagename = '') {

	if (is_page($pagename)) {
		del_tinycode($pagename);
		add_tinycode($pagename);
		return true;
	}
	//err: ページが存在しない
	else {
		return false;
	}
}

//名残雪
function plugin_update_tinycode_do(){

	global $defaultpage;
	
	//dbがなければ、自動的に作られる
	add_tinycode($defaultpage);
	
	//file add
	$exists = get_existpages();
	$tcodes = get_tiny_table();
	$list = array_diff($exists, $tcodes);
	
	foreach($list as $v){
		add_tinycode($v);
	}
	
	//file del
	$tcodes = get_tiny_table();
	$list = array_diff($tcodes, $exists);

	foreach($list as $v){
		del_tinycode($v);
	}
}

?>