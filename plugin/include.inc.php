<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: include.inc.php,v 1.21 2004/12/30 13:26:43 henoheno Exp $
//
// Include-once plugin

//--------
//	| PageA
//	|
//	| // #include(PageB)
//	---------
//		| PageB
//		|
//		| // #include(PageC)
//		---------
//			| PageC
//			|
//		--------- // PageC end
//		|
//		| // #include(PageD)
//		---------
//			| PageD
//			|
//		--------- // PageD end
//		|
//	--------- // PageB end
//	|
//	| #include(): Included already: PageC
//	|
//	| // #include(PageE)
//	---------
//		| PageE
//		|
//	--------- // PageE end
//	|
//	| #include(): Limit exceeded: PageF
//	| // When PLUGIN_INCLUDE_MAX == 4
//	|
//	|
//-------- // PageA end

// ----

// Default value of 'title|notitle' option
define('PLUGIN_INCLUDE_WITH_TITLE', TRUE);	// Default: TRUE(title)

// Max pages allowed to be included at a time
define('PLUGIN_INCLUDE_MAX', 4);

function plugin_include_convert()
{
	global $script, $vars, $get, $post, $menubar;
	static $included = array();
	static $count = 1;
	$qm = get_qm();
	$qt = get_qt();

	if (func_num_args() == 0) return $qm->m['plg_include']['err_usage']. "\n";;

	// $menubar will already be shown via menu plugin
	if (! isset($included[$menubar])) $included[$menubar] = TRUE;

	// Loop yourself
	$root = isset($vars['page']) ? $vars['page'] : '';
	$included[$root] = TRUE;

	// Get arguments
	$args = func_get_args();
	// strip_bracket() is not necessary but compatible
	$page = isset($args[0]) ? get_fullname(strip_bracket(array_shift($args)), $root) : '';
	
	//キャッシュのために、追加
	if(!in_array($page, $qt->get_rel_pages()))
		$qt->set_rel_page($page);
	
	$with_title = PLUGIN_INCLUDE_WITH_TITLE;
	if (isset($args[0])) {
		switch(strtolower(array_shift($args))) {
		case 'title'  : $with_title = TRUE;  break;
		case 'notitle': $with_title = FALSE; break;
		}
	}

	$s_page = h($page);
	$r_page = rawurlencode($page);
	$link = '<a href="' . $script . '?' . $r_page . '">' . $s_page . '</a>'; // Read link

	// I'm stuffed
	if (isset($included[$page])) {
		return $qm->replace('plg_include.err_already_include', $link) . "\n";
	} if (! is_page($page)) {
		return $qm->replace('plg_include.err_no_page', $s_page) . "\n";
	} if ($count > PLUGIN_INCLUDE_MAX) {
		return $qm->replace('plg_include.err_limit', $link) . "\n";
	} else {
		++$count;
	}

	// One page, only one time, at a time
	$included[$page] = TRUE;

	// Include A page, that probably includes another pages
	$get['page'] = $post['page'] = $vars['page'] = $page;
	if (check_readable($page, false, false)) {
		$body = convert_html(get_source($page));
	} else {
		$body = str_replace('$1', $page, $qm->m['plg_include']['err_restrict']);
	}
	$get['page'] = $post['page'] = $vars['page'] = $root;

	// Put a title-with-edit-link, before including document
	if ($with_title) {
		$link = '<a href="' . $script . '?cmd=read&amp;page=' . $r_page .
			'">' . $s_page . '</a>';
		if ($page == $menubar) {
			$body = '<span align="center"><h5 class="side_label">' .
				$link . '</h5></span><small>' . $body . '</small>';
		} else {
			$body = '<h2>' . $link . '</h2>' . "\n" . $body . "\n";
		}
	}

	//編集状態の場合、hover でメッセージを表示
	if( check_editable($vars['page'], false, false) ){
		$goto_page = $qm->replace('plg_include.goto_include_page', $s_page);
		$addscript = '
<style type="text/css">
	.qhm_include_hover {
		outline: 5px dashed #f99;
		-ms-filter: "alpha( opacity=60 )";/* IE8 */
		filter: alpha( opacity=60 );/* IE6-7 */
		opacity: 0.4;
		background-color: #fff;
	}
	.qhm_include_hover * {
	}
	.qhm_include_wrapper {
		position: relative;
		cursor: pointer;
	}
	.qhm_include {
	}	
	.qhm_include * {
		cursor: pointer;
	}
	.qhm_include_title {
		position: absolute;
		text-align: center;
		color: #f66;
		font-size: 36px;
		width: 100%;
		line-height: 40px;
		top: 50%;
		opacity: 1;
	}
	/* IE7 */
	*+html .qhm_include_hover {
		border: 5px dashed #f99;
	}
	*+html .qhm_include_title {
		background-color: #f66;
		color: #fff;
	}
</style>
<script type="text/javascript">
	$(function(){
		$("div.qhm_include_wrapper").click(function(){
			 location.href = $("> div.qhm_include > span.qhm_include_page", this).text();
		})
		.hover(
			function(){
				var $$ = $(this),
					title = $$.attr("title");
				$$.append("<div class=\"qhm_include_title\">"+ title+ "</div>")
					.children("div.qhm_include").addClass("qhm_include_hover")
			},
			function(){
				var $$ = $(this);
				$$.children("div.qhm_include_title").remove()
					$$.children("div.qhm_include").removeClass("qhm_include_hover")
			}
		);
		//二重を避けるため
		$("div.qhm_include_wrapper div.qhm_include_wrapper").unbind("click").unbind("mouseenter").unbind("mouseleave");
	});
</script>
';
		$qt->appendv_once('plugin_include', 'beforescript', $addscript);	
		$body = '<div class="qhm_include_wrapper" title="'. $goto_page. '"><div class="qhm_include">
	<span class="qhm_include_page" style="display:none;">'. $script.'?'.$r_page.'</span>'. $body. '</div></div>';
			return $body;
	}

	return $body;
}
?>
