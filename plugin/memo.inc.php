<?php
// $Id: memo.inc.php,v 1.14 2005/01/23 08:01:29 henoheno Exp $
//
// Memo box plugin

define('MEMO_COLS', 60); // Columns of textarea
define('MEMO_ROWS',  5); // Rows of textarea

function plugin_memo_action()
{
	global $script, $vars, $cols, $rows;
	$qm = get_qm();

	if (PKWK_READONLY) die_message($qm->m['fmt_err_pkwk_readonly']);
	if (! isset($vars['msg']) || $vars['msg'] == '') return;

	$memo_body = preg_replace('/' . "\r" . '/', '', $vars['msg']);
	$memo_body = str_replace("\n", '\n', $memo_body);
	$memo_body = str_replace('"', '&#x22;', $memo_body); // Escape double quotes
	$memo_body = str_replace(',', '&#x2c;', $memo_body); // Escape commas

	$postdata_old  = get_source($vars['refer']);
	$postdata = '';
	$memo_no = 0;
	foreach($postdata_old as $line) {
		if (preg_match("/^#memo\(?.*\)?$/i", $line)) {
			if ($memo_no == $vars['memo_no']) {
				$postdata .= '#memo(' . $memo_body . ')' . "\n";
				$line = '';
			}
			++$memo_no;
		}
		$postdata .= $line;
	}

	$postdata_input = $memo_body . "\n";

	$body = '';
	if (md5(@join('', get_source($vars['refer']))) != $vars['digest']) {
		$title = $qm->m['fmt_title_collided'];
		$body  = $qm->m['fmt_msg_collided'] . "\n";

		$s_refer  = h($vars['refer']);
		$s_digest = h($vars['digest']);
		$s_postdata_input = h($postdata_input);

		$body .= <<<EOD
<form action="$script?cmd=preview" method="post">
 <div>
  <input type="hidden" name="refer"  value="$s_refer" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea name="msg" rows="$rows" cols="$cols" id="textarea">$s_postdata_input</textarea><br />
 </div>
</form>
EOD;
	} else {
		page_write($vars['refer'], $postdata);

		$title = $qm->m['fmt_title_updated'];
	}
	$retvars['msg']  = & $title;
	$retvars['body'] = & $body;

	$vars['page'] = $vars['refer'];

	return $retvars;
}

function plugin_memo_convert()
{
	global $script, $vars, $digest;
	static $numbers = array();
	$qm = get_qm();

	if (! isset($numbers[$vars['page']])) $numbers[$vars['page']] = 0;
	$memo_no = $numbers[$vars['page']]++;

	$data = func_get_args();
	$data = implode(',', $data);	// Care all arguments
	$data = str_replace('&#x2c;', ',', $data); // Unescape commas
	$data = str_replace('&#x22;', '"', $data); // Unescape double quotes
	$data = h(str_replace('\n', "\n", $data));

	if (PKWK_READONLY) {
		$_script = '';
		$_submit = '';	
	} else {
		$_script = & $script;
		$_submit = '<input type="submit" name="memo"    value="' . $qm->m['plg_memo']['btn_update'] . '" />';
	}

	$s_page   = h($vars['page']);
	$s_digest = h($digest);
	$s_cols   = MEMO_COLS;
	$s_rows   = MEMO_ROWS;
	$string   = <<<EOD
<form action="$_script" method="post" class="memo">
 <div>
  <input type="hidden" name="memo_no" value="$memo_no" />
  <input type="hidden" name="refer"   value="$s_page" />
  <input type="hidden" name="plugin"  value="memo" />
  <input type="hidden" name="digest"  value="$s_digest" />
  <textarea name="msg" rows="$s_rows" cols="$s_cols">$data</textarea><br />
  $_submit
 </div>
</form>
EOD;

	return $string;
}
?>
