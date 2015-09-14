<?php
/**
 * @author     lunt
 * @license    http://www.gnu.org/licenses/gpl.html GPL 2 or later
 * @version    $Id: secedit.inc.php 406 2008-07-05 12:58:09Z lunt $
 */

/**
 * Define heading style
 *
 * $1 = heading open tag <hx id="content_1_x">
 * $2 = heading string including anchor
 * $3 = link to secedit plugin
 * $4 = heading close tag </hx>
 */

// paraedit patch style
define('PLUGIN_SECEDIT_LINK_STYLE', '$1$2<a class="anchor_super" href="$3" title="Edit">' .
	'<img src="image/paraedit.png" alt="Edit" title="Edit" height="9" width="9" /></a>$4');

// Monobook style
// define('PLUGIN_SECEDIT_LINK_STYLE', '$1<span class="editsection">[<a href="$3">edit</a>]</span><span>$2</span>$4');

define('PLUGIN_SECEDIT_LEVEL', false);

define('PLUGIN_SECEDIT_ENABLE_ON_KEITAI_PROFILE', false);

// Remove #freeze written by hand
define('PLUGIN_SECEDIT_FREEZE_REGEX', '/^(?:#freeze(?!\w)\s*)+/im');
define('PLUGIN_SECEDIT_PAGE', $vars['page']);

function plugin_secedit_action()
{
	global $post;

	switch (true) {
	case isset($post['cancel']):
		$action = 'Cancel'; break;
	case isset($post['preview']):
		$action = 'Preview'; break;
	case isset($post['write']):
		$action = 'Write'; break;
	default:
		$action = 'Edit';
	}

	$action = 'Plugin_Secedit_' . $action;
	$obj    = &new $action();

	return $obj->process();
}

class Plugin_Secedit
{
	var $page;
	var $id;
	var $anchor;
	var $level;
	var $postdata;
	var $original;
	var $digest;
	var $notimestamp;
	var $pass;
	var $help;

	var $s_page;
	var $s_postdata;
	var $s_original;
	var $s_digest;

	var $sections;

	function init()
	{
		global $vars, $post;

		$this->page        = isset($vars['page']) ? $vars['page'] : '';
		$this->s_page      = htmlspecialchars($this->page);
		$this->id          = isset($vars['id']) ? $vars['id'] : 0;
		$this->anchor      = isset($vars['anchor']) ? $vars['anchor'] : '';
		$this->level       = isset($vars['level']) ? true : false;
		$this->postdata    = isset($post['msg']) ?
			 preg_replace(PLUGIN_SECEDIT_FREEZE_REGEX, '', $post['msg']) : '';
		$this->original    = isset($post['original']) ?
			str_replace("\r", '', $post['original']) : '';
		$this->digest      = isset($post['digest']) ? $post['digest'] : '';
		$this->notimestamp = isset($post['notimestamp']) ? true : false;
		$this->pass        = isset($post['pass']) ? $post['pass'] : '';
		$this->help        = isset($vars['help']) ? true : false;
	}

	function check()
	{
		$qm = get_qm();

		if (PKWK_READONLY) die_message($qm->m['fmt_err_pkwk_readonly']);

		check_editable($this->page, true, true);

		if (! is_page($this->page)) die_message($qm->m['plg_secedit']['err_nopage']);
		if (! $this->sections->is_valid_id($this->id)) die_message($qm->m['plg_secedit']['err_invalid_id']);
	}

	function process()
	{
	}

	function redirect($page)
	{
		pkwk_headers_sent();
		header('Location: ' . get_script_uri() . '?' . rawurlencode($page));
		exit;
	}

	function form()
	{
		global $rows, $cols, $notimeupdate, $hr;

		$qm = get_qm();
		$qt = get_qt();

		$script      = get_script_uri();
		$r_page      = rawurlencode($this->page);
		$btn_preview = strpos(get_class($this), 'Preview') ? $qm->m['fmt_btn_repreview'] : $qm->m['fmt_btn_preview'];

    $buttons_align = 'right';
    if ( ! is_bootstrap_skin())
    {
        $buttons_align = 'left';
        $beforescript = <<< EOD
    <script src="skin/bootstrap/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="skin/bootstrap/css/bootstrap-custom.min.css" />
EOD;
        $qt->appendv_once('plugin_secedit_edit', 'beforescript', $beforescript);
    }

		$level = $this->level ? '<input type="hidden" name="level"  value="true" />' : '';

		$add_notimestamp = '';
		if ($notimeupdate) {

			$checked   = $this->notimestamp ? ' checked="checked"' : '';
			$pass_form = ($notimeupdate == 2) ? '   <input type="password" name="pass" size="12" />' : '';
			$add_notimestamp = <<< EOD
	<label for="_edit_form_notimestamp" class="checkbox">
		<input type="checkbox" name="notimestamp" id="_edit_form_notimestamp" value="true"$checked />
		<span class="small">{$qm->m['fmt_btn_notchangetimestamp']}</span>
	</label>
$pass_form
EOD;
		}

		$helpstr = $qm->m['html']['view_help_message'];

		$body = <<< EOD
<div class="edit_form">
  <form action="{$script}" method="post" style="margin-bottom:0px;">
    <input type="hidden" name="cmd"    value="secedit" />
    <input type="hidden" name="page"   value="{$this->s_page}" />
    <input type="hidden" name="id"     value="{$this->id}" />
    {$level}
    <input type="hidden" name="digest" value="{$this->s_digest}" />
    <div class="form-group">
      <textarea name="msg" id="msg" rows="{$rows}" cols="{$cols}" tabindex="2" class="form-control">{$this->s_postdata}</textarea>
    </div>
    <div style="float:{$buttons_align};">
      <input type="submit" name="preview" value="{$btn_preview}" class="qhm-btn-default" />
      <input type="submit" name="write"   value="{$qm->m['fmt_btn_update']}" class="qhm-btn-primary" />
      {$add_notimestamp}
      <textarea name="original" rows="1" cols="1" style="display:none">$this->s_original</textarea>
   </div>
   <div style="margin-top:0px;margin-left:5px;float:{$buttons_align};">
      <input type="submit" name="cancel"  value="{$qm->m['fmt_btn_cancel']}" class="btn-link" />
   </div>
   <div style="clear:both;"></div>
 </form>
</div>
{$help}
EOD;

    $addscript = <<< EOD
<script data-qhm-plugin="secedit">
$(function(){
  if ($("#preview_body").length) {
  }
  else {
    $(".qhm-eyecatch").hide();

    setTimeout(function(){
      $("html, body").animate({scrollTop: $("#msg").offset().top-10}, 300);
      $("#msg").focus();
    }, 25);
  }
});
</script>
EOD;
    $qt->appendv_once("plugin_secedit_script", "lastscript", $addscript);


		// List of attached files to the page by hokuken.com
		$attaches = (exist_plugin_action('attach')) ?
			attach_filelist() : '';

		if ($attaches !== '') {
			$body .= <<< EOD
<script type="text/javascript" src="js/yahoo.js"></script>
<script type="text/javascript" src="js/event.js"></script>
<script type="text/javascript" src="js/dom.js"></script>

<style type="text/css"><!--
.yui-tt {
position: absolute;
padding: 5px;
background-color:#edf;
border:1px solid #aaf;
}
--></style>
<script type="text/javascript" src="js/container.js"></script>
<script type="text/javascript">
function init() {
	var list = document.getElementById('attachlist').getElementsByTagName('a');
	for( var i=0; i<list.length; i++ ) {
		if( list[i].getAttribute("rel") == "attachhref" ){
			var el = 'tooltip'+i;
			var url = list[i].href;
			var title = '<img src="'+list[i].href+'">';
			if ( list[i].title ) title += '<br>'+list[i].innerHTML;
			var tp = new YAHOO.widget.Tooltip( el, { context:list[i], text: title, autodismissdelay: 7500 } );
		}
	}
}
YAHOO.util.Event.addListener(window, "load", init);
</script>
EOD;

			$body .= '<br /><div id="attachlist" style="border: 2px dashed #666;padding:5px 10px;background-color:#eee">' . $attaches . '</div>';
		}

		return $body;
	}
}

class Plugin_Secedit_Edit extends Plugin_Secedit
{
	function init()
	{
		parent::init();

		$source = get_source($this->page, true, true);

		$this->sections = &new Plugin_Secedit_Sections($source);

		if ($this->anchor) {
			$id = $this->sections->anchor2id($this->anchor);
			$this->id = $id ? $id : $this->id;
		}

		$this->s_postdata = htmlspecialchars($this->sections->get_section($this->id, $this->level));
		$this->s_original = htmlspecialchars($source);
		$this->s_digest   = htmlspecialchars(md5($source));
	}

	function process()
	{
		$qm = get_qm();


		$this->init();
		$this->check();

		return array('msg' => $qm->m['fmt_title_edit'], 'body' => $this->form());
	}
}

class Plugin_Secedit_Preview extends Plugin_Secedit
{
	function init()
	{
		parent::init();

		$this->sections   = &new Plugin_Secedit_Sections($this->original);
		$this->s_postdata = htmlspecialchars($this->postdata);
		$this->s_original = htmlspecialchars($this->original);
		$this->s_digest   = htmlspecialchars($this->digest);
	}

	function check()
	{
		$qm = get_qm();

		parent::check();

		if ($this->original === '') die_message($qm->m['plg_secedit']['err_noorg']);
		if ($this->digest === '') die_message($qm->m['plg_secedit']['err_nodigest']);
	}

	function process()
	{
		$qm = get_qm();
		$qt = get_qt();

		$this->init();
		$this->check();

		$this->sections->set_section($this->id, $this->postdata, $this->level);

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
		$qt->appendv_once('plugin_secedit_preview', 'beforescript', $msgstyle);

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
		$qt->appendv_once('plugin_secedit_preview_js', 'beforescript', $addscript);

		$preview_notice = '<div id="preview_notice">'. $qm->m['fmt_msg_preview'] . '</div>' . "\n";
		$qt->appendv_once('plugin_secedit_preview_block', 'lastscript', $preview_notice);
		$body = '';
		if ($this->postdata == '')
		{
			$body .= '<strong>' . $qm->m['fmt_msg_preview_delete'] . '</strong>';
		}
		$body .= '<br />' . "\n";

		if ($this->postdata) {
			$src = make_str_rules($this->postdata);
			$src = explode("\n", $src);
			$src = drop_submit(convert_html($src));
			$body .= $src;
		}
		$body = '<div id="preview_body">'."\n".$body."\n".'</div>';
		$body .= $this->form();

		return array('msg'=>$qm->m['fmt_title_preview'], 'body'=>$body);
	}
}

class Plugin_Secedit_Write extends Plugin_Secedit_Preview
{
	function process()
	{
		global $do_update_diff_table, $notimeupdate;
		$qm = get_qm();

		$this->init();
		$this->check();

		if (substr($this->postdata, -1) !== "\n")
		{
			$this->postdata .= "\n";
		}
		$this->sections->set_section($this->id, $this->postdata, $this->level);
		$postdata = $this->sections->get_source();


		$current_src = get_source($this->page, true, true);
		$current_md5 = md5($current_src);

		if ($this->digest !== $current_md5) {
			list($postdata, $auto) = do_update_diff($current_src, $postdata, $this->original);
			$this->s_postdata = htmlspecialchars($postdata);
			$this->s_digest   = htmlspecialchars($current_md5);
			$body  = ($auto ? $qm->m['fmt_msg_collided_auto'] : $qm->m['fmt_msg_collided']) . "\n";
			$body .= $do_update_diff_table . edit_form($this->page, $postdata, $current_md5, false);
			return array(
				'msg'  => $qm->m['fmt_title_collided'],
				'body' => $body,
			);
		}

		if ($postdata === '') {
			page_write($this->page, $postdata);
			return array(
				'msg'  => $qm->m['fmt_title_deleted'],
				'body' => str_replace('$1', $this->s_page, $qm->m['fmt_title_deleted']),
			);
		}

		if ($notimeupdate > 1 && $this->notimestamp && ! pkwk_login($this->pass)) {
			return array(
				'msg'  => $qm->m['fmt_title_edit'],
				'body' => "<p><strong>{$qm->m['fmt_msg_invalidpass']}</strong></p>\n" . $this->form()
			);
		}

		if (md5($postdata) === $current_md5) {
			$this->redirect($this->page);
		}

		page_write($this->page, $postdata, $notimeupdate != 0 && $this->notimestamp);
		$this->redirect($this->page);
	}
}

class Plugin_Secedit_Cancel extends Plugin_Secedit
{
	function process()
	{
		$this->init();

		if (is_page($this->page)) {
			$this->redirect($this->page);
		}

		return;
	}
}

class Plugin_Secedit_Sections
{
	var $sections;

	function Plugin_Secedit_Sections($text)
	{
		$this->sections = $this->_parse($text);
	}

	function get_source()
	{
		return implode('', $this->sections);
	}

	function &get_section($id, $with_subsection = false)
	{
		if (! $this->is_valid_id($id)) {
			return false;
		}

		if ($with_subsection) {
			return $this->get_section_with_subsection($id);
		} else {
			return $this->sections[$id];
		}
	}

	function &get_section_with_subsection($id)
	{
		$source = '';
		$count  = $id + $this->_count_subsection($id) + 1;

		for ($i = $id; $i < $count; $i++) {
			$source .= $this->sections[$i];
		}
		return $source;
	}

	function set_section($id, $text, $with_subsection = false)
	{
		if (! $this->is_valid_id($id)) {
			return false;
		}

		if ($with_subsection) {
			$this->set_section_with_subsection($id, $text);
		} else {
			$this->sections[$id] = $text;
		}
	}

	function set_section_with_subsection($id, $text)
	{
		array_splice($this->sections, $id, $this->_count_subsection($id) + 1, array($text));
		$this->sections = $this->_parse($this->get_source());
	}

	function is_valid_id($id)
	{
		if (is_string($id) && ($id === '' || ! ctype_digit($id))) {
			return false;
		}
		return isset($this->sections[$id]) && $id > 0;
	}

	function anchor2id($anchor)
	{
		foreach ($this->sections as $id => $section) {
			if (preg_match('/^\*{1,3}.*?(?:\[#([A-Za-z][\w-]*)\]\s*)\n/', $section, $matches) &&
				$anchor === $matches[1])
			{
				return $id;
			}
		}
		return false;
	}

	function _parse($text)
	{
		$id           = 0;
		$sections[0]  = '';
		$in_multiline = false;

		foreach (explode("\n", $text) as $line) {
			if (! PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK && ! $in_multiline &&
				preg_match('/^#[^{]+(\{{2,})\s*$/', $line, $matches))
			{
				$in_multiline    = true;
				$close_multiline = str_repeat('}', strlen($matches[1]));
			} elseif ($in_multiline && $line === $close_multiline) {
				$in_multiline = false;
			}
			if (! $in_multiline && (strpos($line, '*') === 0 OR strpos($line, '!') === 0)) {
				$sections[++$id] = '';
			}
			$sections[$id] .= $line . "\n";
		}
		$sections[count($sections)-1] = substr($sections[count($sections)-1], 0, -1);

		return $sections;
	}

	function _count_subsection($id)
	{
		$count = 0;
		$level = $this->_level($id);

		for ($i = $id + 1; $i < count($this->sections); $i++) {
			if ($this->_level($i) <= $level) {
				break;
			}
			$count++;
		}
		return $count;
	}

	function _level($id)
	{
		return min(3, strspn($this->sections[$id], '*'));
	}
}

function plugin_secedit_wrap(&$string, &$tag, &$param, &$id)
{
	global $vars, $plugin;
	global $qblog_defaultpage;
	static $is_editable;

	$page = isset($vars['page']) ? $vars['page'] : '';

	if ($page !== $qblog_defaultpage && is_qblog())
	{
		return;
	}

	if (! isset($is_editable[$page])) {
		$is_editable[$page] = check_editable($page, false, false);
	}
	list($dummy, $count, $secid) = explode('_', $id);

	if (PKWK_READONLY || $plugin !== 'read' || ! $is_editable[$page] ||
		($count > 1 && PLUGIN_SECEDIT_PAGE === $page) || // for MenuBar
		(! PLUGIN_SECEDIT_ENABLE_ON_KEITAI_PROFILE && UA_PROFILE === 'keitai'))
	{
		return false;
	}

	$secid = '&amp;id=' . strval($secid + 1);
	if ($count > 1 && preg_match('/<a[^>]+id="([A-Za-z][\w-]*)"/', $string, $matches)) {
		$secid = '&amp;anchor=' . $matches[1];
	} elseif ($count > 1) {
		return false;
	}

	$open  = '<' . $tag . $param . '>';
	$close = '</' . $tag . '>';
	$link  = get_script_uri() . '?cmd=secedit&amp;page=' . rawurlencode($page) . $secid;
	$link .= PLUGIN_SECEDIT_LEVEL ? '&amp;level=true' : '';

	return str_replace(
		array('$1', '$2', '$3', '$4'),
		array($open, $string, $link, $close),
		PLUGIN_SECEDIT_LINK_STYLE);
}
