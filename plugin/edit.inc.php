<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: edit.inc.php,v 1.40 2006/03/21 14:26:25 henoheno Exp $
// Copyright (C) 2001-2006 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version
//
// Edit plugin (cmd=edit)

// Remove #freeze written by hand
define('PLUGIN_EDIT_FREEZE_REGEX', '/^(?:#freeze(?!\w)\s*)+/im');

function plugin_edit_action()
{
	global $vars, $load_template_func, $style_name, $layout_pages;
	global $qblog_defaultpage;
	$qm = get_qm();
	$qt = get_qt();

	if (PKWK_READONLY) die_message($qm->m['fmt_err_pkwk_readonly']);

	$page = isset($vars['page']) ? $vars['page'] : '';
	$prefix = '';

	//メニューやナビの編集はスタイルを変える
	if (array_key_exists($page, $layout_pages) && ! isset($vars['preview']))
	{
		$prefix = '<h2 class="title">'. h($layout_pages[$page]) .'の編集</h2>';

		if (is_bootstrap_skin()) {
			if (exist_plugin("noeyecatch")) {
				do_plugin_convert("noeyecatch");
			}
		} else {
			$style_name = '../';
			$addscript = <<< EOD
<script type="text/javascript">
\$(function(){

  if (window != parent)
  {
		\$("input:submit").click(function(e){
			window.onbeforeunload = null;
			var name = \$(this).attr("name");
			\$(this).before('<input type="hidden" name="'+name+'" value="'+\$(this).val()+'">').prop("disabled", true);

			var \$form = \$(this).closest("form");
			var \$parent = \$(window.parent.document).find("body");

			var params = \$form.serialize().split('&');
			var \$fm = \$('<form></form>');
			for (var i = 0; i < params.length; i++)
			{
				var param = params[i].split('=');

				\$fm.append(\$('<input type="hidden" name="'+param[0]+'" value="" />').val(decodeURIComponent(param[1].replace(/\+/g, ' '))));
			}
			\$fm.attr('action', \$form.attr('action'));
			\$fm.attr('method', \$form.attr('method'));
			\$parent.append(\$fm).find("form:last").submit();
			return false;
		});
	}
});
</script>
EOD;
			$qt->appendv('beforescript', $addscript);
		}
	} else {
		if (is_bootstrap_skin()) {
			// 編集画面のレイアウトを fullpage にする
			if (exist_plugin("layout")) {
				do_plugin_convert("layout", "nomenu");
			}
		}
	}

	check_editable($page, true, true);


	if (isset($vars['preview']) || ($load_template_func && isset($vars['template']))) {
		return plugin_edit_preview();
	} else if (isset($vars['write'])) {
		return plugin_edit_write();
	} else if (isset($vars['cancel'])) {
		return plugin_edit_cancel();
	}

	$postdata = @join('', get_source($page));
	if ($postdata == '') $postdata = auto_template($page);

	return array('msg'=>$qm->m['fmt_title_edit'], 'body'=> $prefix . edit_form($page, $postdata));
}

// Preview
function plugin_edit_preview()
{
	global $vars, $layout_pages;
	global $qblog_defaultpage;
	$qm = get_qm();
	$qt = get_qt();

	cancel_xss_protection();

	$page = isset($vars['page']) ? $vars['page'] : '';

	$layout_name = '';
	if (array_key_exists($page, $layout_pages))
	{
		$layout_name = $layout_pages[$page];
	}

	// Loading template
	if (isset($vars['template_page']) && is_page($vars['template_page'])) {

		$vars['msg'] = join('', get_source($vars['template_page']));

		// Cut fixed anchors
		$vars['msg'] = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', $vars['msg']);
	}

	$vars['msg'] = preg_replace(PLUGIN_EDIT_FREEZE_REGEX, '', $vars['msg']);
	$postdata = $vars['msg'];

	if (isset($vars['add']) && $vars['add']) {
		if (isset($vars['add_top']) && $vars['add_top']) {
			$postdata  = $postdata . "\n\n" . @join('', get_source($page));
		} else {
			$postdata  = @join('', get_source($page)) . "\n\n" . $postdata;
		}
	}

	$msgstyle = '
<style type="text/css">
	#preview_notice {
		background-color: #ff9;
		padding: 2px 0;
		font-weight: bold;
		text-align: center;
		border-radius: 10px 10px 0 0;
		-moz-border-radius: 10px 10px 0 0;
		-ms-filter: "alpha( opacity=20 )"; /* IE8 */
		filter: alpha( opacity=20 ); /* IE7 */
		opacity: 0.8;
		position: fixed;
		width: 550px;
		bottom: 0;
		left: 50%;
		margin-left: -275px;
		z-index: 30;
	}
	ul.toolbar_menu li.preview_notice, ul.toolbar_menu_min li.preview_notice {
		padding: 0 2px;
		margin: 0 auto;
		background-color: #ff9;
		color: #000;
		font-weight: bold;
		background-image: none;
		cursor: auto;
		text-align: center;
	}
</style>
';

	if ($layout_name !== '')
	{
		$msgstyle .= '
<style type="text/css">
	#preview_body {
		display: none;
	}
</style>
';

	}

	$qt->appendv_once('plugin_edit_preview', 'beforescript', $msgstyle);

	$addscript = '
<script type="text/javascript">
	$(function(){
		$("div.toolbar_upper ul.toolbar_menu, div.toolbar_upper ul.toolbar_menu_min").prepend("<li class=\"preview_notice\">'. $qm->m['plg_edit']['label_preview'].'</li>")
			.children(":nth-child(2)").remove();
		$("#preview_notice")
		.css("cursor", "pointer")
		.click(function(){
			$("#msg").focus();
		});
	});
</script>
';

	if ($layout_name !== '')
	{
		$addscript .= '
<script type="text/javascript">
$(function(){
	var $layout = $("div.preview_highlight").parent(), $div = $("<div></div>"), $div2 = $("<div>'. h($layout_name) .'のプレビュー</div>");
	var paddingWidth = parseInt($layout.css("padding-left").match(/\d+/)[0]) + parseInt($layout.css("padding-right").match(/\d+/)[0]),
		paddingHeight = parseInt($layout.css("padding-top").match(/\d+/)[0]) + parseInt($layout.css("padding-bottom").match(/\d+/)[0]);
	$div.css({
		width: $layout.width() + paddingWidth + 10,
		height: $layout.height() + paddingHeight + 10,
		position: "absolute",
		left: $layout.offset().left - 8,
		top: $layout.offset().top -8,
		border: "3px solid #FF6600",
		zIndex: 999,
		opacity: 0
	});
	$("body").append($div);

	$div2.css({
		width: 220,
		height: 30,
		position: "absolute",
		left: $div.offset().left + $div.width() + 10 - 220,
		top: $div.offset().top -22,
		backgroundColor: "#FF6600",
		color: "white",
		textAlign: "center",
		fontSize: 14,
		lineHeight: "30px",
		zIndex: 1000,
		opacity: 0
	});

	$("body").append($div2);

	$("html, body").animate({scrollTop: $div2.offset().top}, 400, function(){
		$div.animate({opacity: 1}, 600);
		$div2.animate({opacity: 1}, 600);
	});

});
</script>
';
	}
	$qt->appendv_once('plugin_edit_preview_js', 'beforescript', $addscript);

	$preview_notice = '<div id="preview_notice">'. $qm->m['fmt_msg_preview'] . '</div>' . "\n";
	$qt->appendv_once('plugin_edit_preview_block', 'lastscript', $preview_notice);
	$body = '<div id="preview_body">';
	if ($postdata == '')
		$body .= '<strong>' . $qm->m['fmt_msg_preview_delete'] . '</strong>';
	$body .= '<br />' . "\n";

	if ($postdata) {
		if ($page !== $qblog_defaultpage && is_qblog())
		{
			$postdata = "#qblog_head\n" . $postdata;
		}
		$postdata = make_str_rules($postdata);
		$postdata = explode("\n", $postdata);
		$postdata = drop_submit(convert_html($postdata));
		$body .= $postdata;
	}
	$body .= '</div>'. "\n";
	$body .= edit_form($page, $vars['msg'], $vars['digest'], FALSE);

	return array('msg'=>$qm->m['fmt_title_preview'], 'body'=>$body);
}

// Inline: Show edit (or unfreeze text) link
function plugin_edit_inline()
{
	global $script, $vars, $fixed_heading_anchor_edit;
	$qm = get_qm();

	if (PKWK_READONLY) return ''; // Show nothing

	// Arguments
	$args = func_get_args();

	// {label}. Strip anchor tags only
	$s_label = strip_htmltag(array_pop($args), FALSE);

	$page    = array_shift($args);
	if ($page == NULL) $page = '';
	$_noicon = $_nolabel = FALSE;
	foreach($args as $arg){
		switch(strtolower($arg)){
		case ''       :                   break;
		case 'nolabel': $_nolabel = TRUE; break;
		case 'noicon' : $_noicon  = TRUE; break;
		default       : return $qm->m['plg_edit']['err_usage'];
		}
	}

	// Separate a page-name and a fixed anchor
	list($s_page, $id, $editable) = anchor_explode($page, TRUE);

	// Default: This one
	if ($s_page == '') $s_page = isset($vars['page']) ? $vars['page'] : '';

	// $s_page fixed
	$isfreeze = is_freeze($s_page);
	$ispage   = is_page($s_page);

	// Paragraph edit enabled or not
	$short = h($qm->m['plg_edit']['title_short']);
	if ($fixed_heading_anchor_edit && $editable && $ispage && ! $isfreeze) {
		// Paragraph editing
		$id    = rawurlencode($id);
		$title = h($qm->replace('plg_edit.title', $page));
		$icon = '<img src="' . IMAGE_DIR . 'paraedit.png' .
			'" width="9" height="9" alt="' .
			$short . '" title="' . $title . '" /> ';
		$class = ' class="anchor_super"';
	} else {
		// Normal editing / unfreeze
		$id    = '';
		if ($isfreeze) {
			$title = $qm->replace('plg_edit.title_unfreeze', $s_page);
			$icon  = 'unfreeze.png';
		} else {
			$title = $qm->replace('plg_edit.title', $s_page);
			$icon  = 'edit.png';
		}
		$title = h($title);
		$icon = '<img src="' . IMAGE_DIR . $icon .
			'" width="20" height="20" alt="' .
			$short . '" title="' . $title . '" />';
		$class = '';
	}
	if ($_noicon) $icon = ''; // No more icon
	if ($_nolabel) {
		if (!$_noicon) {
			$s_label = '';     // No label with an icon
		} else {
			$s_label = $short; // Short label without an icon
		}
	} else {
		if ($s_label == '') $s_label = $title; // Rich label with an icon
	}

	// URL
	if ($isfreeze) {
		$url   = $script . '?cmd=unfreeze&amp;page=' . rawurlencode($s_page);
	} else {
		$s_id = ($id == '') ? '' : '&amp;id=' . $id;
		$url  = $script . '?cmd=edit&amp;page=' . rawurlencode($s_page) . $s_id;
	}
	$atag  = '<a' . $class . ' href="' . $url . '" title="' . $title . '">';
	static $atags = '</a>';

	if ($ispage) {
		// Normal edit link
		return $atag . $icon . $s_label . $atags;
	} else {
		// Dangling edit link
		return '<span class="noexists">' . $atag . $icon . $atags .
			$s_label . $atag . '?' . $atags . '</span>';
	}
}

// Write, add, or insert new comment
function plugin_edit_write()
{
	global $vars, $trackback, $layout_pages;
	global $notimeupdate, $do_update_diff_table;
	global $qblog_defaultpage, $date_format, $qblog_menubar;
	$qm = get_qm();

	$page   = isset($vars['page'])   ? $vars['page']   : '';
	$add    = isset($vars['add'])    ? $vars['add']    : '';
	$digest = isset($vars['digest']) ? $vars['digest'] : '';

	$vars['msg'] = preg_replace(PLUGIN_EDIT_FREEZE_REGEX, '', $vars['msg']);
	$msg = & $vars['msg']; // Reference


	$retvars = array();

	// Collision Detection
	$oldpagesrc = join('', get_source($page));
	$oldpagemd5 = md5($oldpagesrc);

	if ($digest != $oldpagemd5) {
		$vars['digest'] = $oldpagemd5; // Reset

		$original = isset($vars['original']) ? $vars['original'] : '';
		list($postdata_input, $auto) = do_update_diff($oldpagesrc, $msg, $original);

		$retvars['msg' ] = $qm->m['fmt_title_collided'];
		$retvars['body'] = ($auto ? $qm->m['fmt_msg_collided_auto'] : $qm->m['fmt_msg_collided']) . "\n";
		$retvars['body'] .= $do_update_diff_table;
		$retvars['body'] .= edit_form($page, $postdata_input, $oldpagemd5, FALSE);
		return $retvars;
	}

	// Action?
	if ($add) {
		// Add
		if (isset($vars['add_top']) && $vars['add_top']) {
			$postdata  = $msg . "\n\n" . @join('', get_source($page));
		} else {
			$postdata  = @join('', get_source($page)) . "\n\n" . $msg;
		}
	} else {
		// Edit or Remove
		$postdata = & $msg; // Reference
	}

	//ブログの時は、タイトルを足す
	if ($page !== $qblog_defaultpage && is_qblog())
	{
		global $qblog_default_cat;
		$title = trim($vars['title']);
		$image = trim($vars['image']);
		$cat   = trim($vars['category']);
		$cat   = ($cat === '') ? $qblog_default_cat : $cat;

		if ($postdata !== '')
		{
			$postdata = 'TITLE:'. $title . "\n" . $postdata;
		}
	}

	// NULL POSTING, OR removing existing page
	if ($postdata == '') {
		page_write($page, $postdata);
		$retvars['msg' ] = $qm->m['fmt_title_deleted'];
		$retvars['body'] = str_replace('$1', htmlspecialchars($page), $qm->m['fmt_title_deleted']);

		if ($trackback) tb_delete($page);

		return $retvars;
	}

	// $notimeupdate: Checkbox 'Do not change timestamp'
	$notimestamp = isset($vars['notimestamp']) && $vars['notimestamp'] != '';
	if ($notimeupdate > 1 && $notimestamp && ! pkwk_login($vars['pass'])) {
		// Enable only administrator & password error
		$retvars['body']  = '<p><strong>' . $qm->m['fmt_msg_invalidpass'] . '</strong></p>' . "\n";
		$retvars['body'] .= edit_form($page, $msg, $digest, FALSE);
		return $retvars;
	}

	page_write($page, $postdata, $notimeupdate != 0 && $notimestamp);

	//ブログの場合
	if ($page !== $qblog_defaultpage && is_qblog())
	{
		// 日付の変更があったら、ページ名の変更
		$page_date = get_qblog_date($date_format, $page);
		if ($page_date AND $vars['qblog_date'] != $page_date)
		{
			// ページ名の変更
			if (exist_plugin('rename'))
			{
				// ! renameのために $vasの値を変更
				$vars['page'] = $newpage = qblog_get_newpage($vars['qblog_date']);
				$vars['refer'] = $refer = $page;
				$vars['exist'] = 1;

				$pages = array();
				$pages[encode($refer)] = encode($newpage);
				$files = plugin_rename_get_files($pages);
				$exists = array();
				foreach ($files as $_page => $arr)
				{
					foreach ($arr as $old => $new)
					{
						if (file_exists($new))
						{
							$exists[$_page][$old] = $new;
						}
					}
				}
				plugin_rename_proceed($pages, $files, $exists, FALSE);

				//保留コメントリスト内のページ名を変更
				$datafile = CACHEQBLOG_DIR . 'qblog_pending_comments.dat';
				$pending_comments = unserialize(file_get_contents($datafile));
				foreach ($pending_comments as $i => $comment)
				{
					if ($comment['page'] == $page)
					{
						$pending_comments[$i]['page'] = $newpage;
					}
				}
				file_put_contents($datafile, serialize($pending_comments), LOCK_EX);

				//最新コメントリスト内のページ名を変更
				$datafile = CACHEQBLOG_DIR . 'qblog_recent_comments.dat';
				file_put_contents($datafile, str_replace($page, $newpage, file_get_contents($datafile)), LOCK_EX);

				//変数を格納し直す
				$page = $newpage;
			}
		}

		//ブログの時は、ポストキャッシュを書き換える
		$option = array('category' => $cat, 'image' => $image);
		qblog_update_post($force, $page, $option);

		//Ping送信を行う
		if ( ! $notimestamp)
		{
			send_qblog_ping();
		}
	}

	pkwk_headers_sent();

	//ブログメニューの場合、ブログトップへ移動する
	if ($page === $qblog_menubar)
	{
		header('Location: ' . get_script_uri() . '?' . $qblog_defaultpage);
	}
	//メニューやナビの場合はFrontPage へ
	else if (array_key_exists($page, $layout_pages))
	{
		header('Location: ' . get_script_uri());
	}
	else
	{
		header('Location: ' . get_script_uri() . '?' . rawurlencode($page));
	}

	exit;
}

// Cancel (Back to the page / Escape edit page)
function plugin_edit_cancel()
{
	global $vars, $layout_pages, $qblog_menubar, $qblog_defaultpage;
	pkwk_headers_sent();

	if ($vars['page'] === $qblog_menubar)
	{
		header('Location: ' . get_script_uri() . '?' . $qblog_defaultpage);
	}
	else if (is_qblog($vars['page']) && ! is_page($vars['page']))
	{
		header('Location: ' . get_script_uri() . '?' . $qblog_defaultpage);
	}
	else if (array_key_exists($vars['page'], $layout_pages) OR count(get_source($vars['page'])) == 0)
	{
		header('Location: ' . get_script_uri());
	}
	else
	{
		header('Location: ' . get_script_uri() . '?' . rawurlencode($vars['page']));
	}
	exit;
}
