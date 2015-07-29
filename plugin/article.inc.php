<?php
// $Id: article.inc.php,v 1.25 2005/09/24 01:12:29 henoheno Exp $
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2002      Originally written by OKAWARA,Satoshi <kawara@dml.co.jp>
//             http://www.dml.co.jp/~kawara/pukiwiki/pukiwiki.php
//
// article: BBS-like plugin

 /*
 メッセージを変更したい場合はLANGUAGEファイルに下記の値を追加してからご使用ください
	$_btn_name    = 'お名前';
	$_btn_article = '記事の投稿';
	$_btn_subject = '題名: ';

 ※$_btn_nameはcommentプラグインで既に設定されている場合があります

 投稿内容の自動メール転送機能をご使用になりたい場合は
 -投稿内容のメール自動配信
 -投稿内容のメール自動配信先
 を設定の上、ご使用ください。

 */

define('PLUGIN_ARTICLE_COLS',	70); // テキストエリアのカラム数
define('PLUGIN_ARTICLE_ROWS',	 5); // テキストエリアの行数
define('PLUGIN_ARTICLE_NAME_COLS',	24); // 名前テキストエリアのカラム数
define('PLUGIN_ARTICLE_SUBJECT_COLS',	60); // 題名テキストエリアのカラム数
define('PLUGIN_ARTICLE_NAME_FORMAT',	'$name'); // 名前の挿入フォーマット
define('PLUGIN_ARTICLE_SUBJECT_FORMAT',	'**$subject'); // 題名の挿入フォーマット

define('PLUGIN_ARTICLE_INS',	0); // 挿入する位置 1:欄の前 0:欄の後
define('PLUGIN_ARTICLE_COMMENT',	1); // 書き込みの下に一行コメントを入れる 1:入れる 0:入れない
define('PLUGIN_ARTICLE_AUTO_BR',	0); // 改行を自動的変換 1:する 0:しない

define('PLUGIN_ARTICLE_MAIL_AUTO_SEND',	0); // 投稿内容のメール自動配信 1:する 0:しない
define('PLUGIN_ARTICLE_MAIL_FROM',	''); // 投稿内容のメール送信時の送信者メールアドレス
define('PLUGIN_ARTICLE_MAIL_SUBJECT_PREFIX', "[someone's PukiWiki]"); // 投稿内容のメール送信時の題名

// 投稿内容のメール自動配信先
global $_plugin_article_mailto;
$_plugin_article_mailto = array (
	''
);

function plugin_article_action()
{
	global $script, $post, $vars, $cols, $rows, $now;
	global $_plugin_article_mailto;
	$qm = get_qm();

	if (PKWK_READONLY) die_message($qm->m['fmt_err_pkwk_readonly']);

	if ($post['msg'] == '')
		return array('msg'=>'','body'=>'');

	$name = ($post['name'] == '') ? $qm->m['fmt_no_name'] : $post['name'];
	$name = ($name == '') ? '' : str_replace('$name', $name, PLUGIN_ARTICLE_NAME_FORMAT);
	$subject = ($post['subject'] == '') ? $qm->m['fmt_no_subject'] : $post['subject'];
	$subject = ($subject == '') ? '' : str_replace('$subject', $subject, PLUGIN_ARTICLE_SUBJECT_FORMAT);
	$article = $subject . "\n" . $qm->replace('plg_article.post_format', $name, $now). "\n\n";

	$msg = rtrim($post['msg']);
	if (PLUGIN_ARTICLE_AUTO_BR) {
		//改行の取り扱いはけっこう厄介。特にURLが絡んだときは…
		//コメント行、整形済み行には~をつけないように arino
//		$msg = join("\n", preg_replace('/^(?!\/\/)(?!\s)(.*)$/', '$1~', explode("\n", $msg))); // 改行の直接入力対応のため
	}
	$article .= $msg . "\n" . $qm->m['plg_article']['note_comment'] . "\n" . "*** ". $qm->m['plg_article']['comment_header'];
	$article = wikiescape($article);

	if (PLUGIN_ARTICLE_COMMENT) $article .= "\n\n" . '#comment2' . "\n";

	$postdata = '';
	$postdata_old  = get_source($post['refer']);
	$article_no = 0;

	foreach($postdata_old as $line) {
		if (! PLUGIN_ARTICLE_INS) $postdata .= $line;
		if (preg_match('/^#article/i', $line)) {
			if ($article_no == $post['article_no'] && $post['msg'] != '')
				$postdata .= $article . "\n";
			++$article_no;
		}
		if (PLUGIN_ARTICLE_INS) $postdata .= $line;
	}

	$postdata_input = $article . "\n";
	$body = '';

	if (md5(@join('', get_source($post['refer']))) != $post['digest']) {
		$title = $qm->m['fmt_title_collided'];

		$body = $qm->m['fmt_collided'] . "\n";

		$s_refer    = htmlspecialchars($post['refer']);
		$s_digest   = htmlspecialchars($post['digest']);
		$s_postdata = htmlspecialchars($postdata_input);
		$body .= <<<EOD
<form action="$script?cmd=preview" method="post">
 <div>
  <input type="hidden" name="refer" value="$s_refer" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea name="msg" rows="$rows" cols="$cols" id="textarea">$s_postdata</textarea><br />
 </div>
</form>
EOD;

	} else {
//		page_write($post['refer'], trim($postdata));

	if($vars['authcode_master'] === $vars['authcode']){
		page_write($vars['refer'], $postdata );
	}
	else{
		$vars['article_error'] = 'error!!!';
	}
	
		// 投稿内容のメール自動送信
		if (PLUGIN_ARTICLE_MAIL_AUTO_SEND) {
			$mailaddress = implode(',', $_plugin_article_mailto);
			$mailsubject = PLUGIN_ARTICLE_MAIL_SUBJECT_PREFIX . ' ' . str_replace('**', '', $subject);
			if ($post['name'])
				$mailsubject .= '/' . $post['name'];
			$mailsubject = mb_encode_mimeheader($mailsubject);

			$mailbody = $post['msg'];
			$mailbody .= "\n\n" . '---' . "\n";
			$mailbody .= $qm->replace('plg_article.sender_format', $post['name'], $now);
			$mailbody .= $qm->m['plg_article']['mail_page'] . $post['refer'] . "\n";
			$mailbody .= '　 URL: ' . $script . '?' . rawurlencode($post['refer']) . "\n";
			$mailbody = mb_convert_encoding($mailbody, 'JIS');

			$mailaddheader = 'From: ' . PLUGIN_ARTICLE_MAIL_FROM;

			mail($mailaddress, $mailsubject, $mailbody, $mailaddheader);
		}

		$title = $qm->m['fmt_title_updated'];
	}
	
	$retvars['msg'] = $title;
	$retvars['body'] = $body;

	$post['page'] = $post['refer'];
	$vars['page'] = $post['refer'];

	return $retvars;
}

function plugin_article_convert()
{
    global $script, $vars, $digest;
    static $numbers = array();
    $qm = get_qm();
	
    $s_msg = $s_subject = $s_name = "";

    if (PKWK_READONLY) return ''; // Show nothing

    if (! isset($numbers[$vars['page']])) $numbers[$vars['page']] = 0;

    $article_no = $numbers[$vars['page']]++;
    $authcode = '' . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9);
	
    $auth_label = '認証コード';
    $auth_error_style = '';
    $auth_error_class = '';
    $auth_error_alert = '';
    if(isset($vars['article_error']))
    {
        $auth_error_alert = '<div class="alert alert-danger">認証コードを入力してください</div>';
        $auth_error_style = 'color:red';
        $auth_error_class = 'has-error';
        $auth_label = '認証コードを入力してください';
    
    		$s_name = htmlspecialchars($vars['name']);
    		$s_msg = htmlspecialchars($vars['msg']);
    		$s_subject = htmlspecialchars($vars['subject']);
    }

    $s_page   = htmlspecialchars($vars['page']);
    $s_digest = htmlspecialchars($digest);
    $name_cols = PLUGIN_ARTICLE_NAME_COLS;
    $subject_cols = PLUGIN_ARTICLE_SUBJECT_COLS;
    $article_rows = PLUGIN_ARTICLE_ROWS;
    $article_cols = PLUGIN_ARTICLE_COLS;


    $article_form = '';  
    if (is_bootstrap_skin())
    {
        $article_form = <<<EOD
  <div class="form-horizontal">
    {$auth_error_alert}
    <div class="form-group">
      <div class="col-sm-6">
        <div class="form-inline">

          <div class="col-sm-12">
            <div class="form-group">
              <label class="control-label" for="_p_article_name_$article_no">お名前:</label>
              <input type="text" name="name" class="form-control input-sm" id="_p_article_name_$article_no" size="" value="{$s_name}" />
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-inline">
          <div class="col-sm-12">
            <div class="form-group">
              <label id="article_auth_msg_no_{$article_no}" class="control-label">認証コード({$authcode}):</label>
              <input type="text" name="authcode" class="form-control input-sm" value="" size="4"  />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="form-group">
      <div class="col-sm-12">
        <input type="text" name="subject" class="form-control" id="_p_article_subject_{$article_no}" size="{$subject_cols}" value="{$s_subject}" placeholder="タイトル" />
      </div>
    </div>  
    <div class="form-group">
      <div class="col-sm-12">
        <textarea name="msg" rows="{$article_rows}" cols="{$article_cols}" class="form-control">{$s_msg}</textarea>
        </div>
    </div>
    <div class="form-group">
      <div class="col-md-12">
        <input type="submit" name="article" class="btn btn-default" value="記事の投稿" />
      </div>
    </div>
  </div>
EOD;
    }
    else
    {

        $article_form = <<<EOD
  <span id="article_auth_msg_no_{$article_no}" style="font-size:11px;{$auth_error_style}">
    {$auth_label}({$authcode}<input type="text" name="authcode" value="" size="4" />
  </span>
  <label for="_p_article_name_$article_no">お名前</label>
  <input type="text" name="name" id="_p_article_name_$article_no" size="$name_cols" value="{$s_name}" /><br />
  <label for="_p_article_subject_$article_no">題名：</label>
  <input type="text" name="subject" id="_p_article_subject_$article_no" size="$subject_cols" value="{$s_subject}" /><br />
  <textarea name="msg" rows="$article_rows" cols="$article_cols">{$s_msg}\n</textarea><br />
  <input type="submit" name="article" value="記事の投稿" />
EOD;
    }

    $string = <<<EOD
<form action="{$script}" method="post">
 <div>
  <input type="hidden" name="article_no" value="{$article_no}" />
  <input type="hidden" name="plugin" value="article" />
  <input type="hidden" name="digest" value="{$s_digest}" />
  <input type="hidden" name="authcode_master" value="{$authcode}" />
  <input type="hidden" name="refer" value="{$s_page}" />
  {$article_form}
 </div>
</form>
EOD;

    return $string;
}
