<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: search.inc.php,v 1.13 2005/11/29 18:19:51 teanan Exp $
//
// Search plugin

// Allow search via GET method 'index.php?plugin=search&word=keyword'
// NOTE: Also allows DoS to your site more easily by SPAMbot or worm or ...
define('PLUGIN_SEARCH_DISABLE_GET_ACCESS', 0); // 1, 0

define('PLUGIN_SEARCH_MAX_LENGTH', 80);
define('PLUGIN_SEARCH_MAX_BASE',   16); // #search(1,2,3,...,15,16)

// Show a search box on a page
function plugin_search_convert()
{
	static $done;
	$qm = get_qm();

	if (isset($done)) {
		return $qm->replace('fmt_err_already_called', '#search'). "\n";
	} else {
		$done = TRUE;
		$args = func_get_args();
		return plugin_search_search_form('', '', $args);
	}
}

function plugin_search_action()
{
	global $post, $vars;
	$qm = get_qm();

	if (PLUGIN_SEARCH_DISABLE_GET_ACCESS) {
		$s_word = isset($post['word']) ? h($post['word']) : '';
	} else {
		$s_word = isset($vars['word']) ? h($vars['word']) : '';
	}
	if (strlen($s_word) > PLUGIN_SEARCH_MAX_LENGTH) {
		unset($vars['word']); // Stop using $_msg_word at lib/html.php
		die_message($qm->m['plg_search']['err_toolong']);
	}

	$type = isset($vars['type']) ? $vars['type'] : '';
	$base = isset($vars['base']) ? $vars['base'] : '';

	if ($s_word != '') {
		// Search
		$msg  = str_replace('$1', $s_word, $qm->m['plg_search']['title_result']);
		$body = plugin_search_do_search($vars['word'], $type, FALSE, $base);
	} else {
		// Init
		unset($vars['word']); // Stop using $_msg_word at lib/html.php
		$msg  = $qm->m['plg_search']['title_search'];
		$body = '<br />' . "\n" . $qm->m['plg_search']['note'] . "\n";
	}

	

	// Show search form
	$bases = ($base == '') ? array() : array($base);
	$body .= plugin_search_search_form($s_word, $type, $bases);

	return array('msg'=>$msg, 'body'=>$body);
}

function plugin_search_search_form($s_word = '', $type = '', $bases = array())
{
	global $script;
	$qm = get_qm();

	$and_check = $or_check = '';
	if ($type == 'OR') {
		$or_check  = ' checked="checked"';
	} else {
		$and_check = ' checked="checked"';
	}

	$base_option = '';
	if (!empty($bases)) {
		$base_msg = '';
		$_num = 0;
		$check = ' checked="checked"';
		foreach($bases as $base) {
			++$_num;
			if (PLUGIN_SEARCH_MAX_BASE < $_num) break;
			$label_id = '_p_search_base_id_' . $_num;
			$s_base   = htmlspecialchars($base);
			$base_str = '<strong>' . $s_base . '</strong>';
			$base_label = str_replace('$1', $base_str, $qm->m['plg_search']['search_pages']);
			$base_msg  .=<<<EOD
 <div>
  <input type="radio" name="base" id="$label_id" value="$s_base" $check />
  <label for="$label_id">$base_label</label>
 </div>
EOD;
			$check = '';
		}
		$base_msg .=<<<EOD
  <input type="radio" name="base" id="_p_search_base_id_all" value="" />
  <label for="_p_search_base_id_all">{$qm->m['plg_search']['search_all']}</label>
EOD;
		$base_option = '<div class="small">' . $base_msg . '</div>';
	}

	return <<<EOD
<form action="$script" method="get" class="form">
 <div class="qhm_search">
  <div class="form-group">
    <div class="input-group">
      <input type="text" name="word" value="$s_word" size="50" class="form-control" />
      <span class="input-group-btn">
        <input type="submit" class="btn btn-default" value="{$qm->m['plg_search']['btn']}" style="line-height:inherit;" />
      </span>
    </div>
  </div>
  <div class="form-group">
    <label for="_p_search_AND" class="radio-inline" style="display:inline-block;">
      <input type="radio" name="type" id="_p_search_AND" value="AND" $and_check /> {$qm->m['plg_search']['lbl_and']}
    </label>
    <label for="_p_search_OR" class="radio-inline" style="display:inline-block;">
      <input type="radio" name="type" id="_p_search_OR"  value="OR"  $or_check  /> {$qm->m['plg_search']['lbl_or']}
    </label>
  </div>
  <input type="hidden" name="cmd" value="search" />
 </div>
$base_option
</form>
EOD;
}

// 'Search' main function
function plugin_search_do_search($word, $type = 'AND', $non_format = FALSE, $base = '')
{
	global $script, $whatsnew, $non_list, $search_non_list;
	global $search_auth, $show_passage;
	$qm = get_qm();

	$retval = array();

	$b_type = ($type == 'AND'); // AND:TRUE OR:FALSE
	mb_language('Japanese');
	$word = mb_convert_encoding($word,SOURCE_ENCODING,"UTF-8,EUC-JP,SJIS,ASCII,JIS");
	$word = mb_ereg_replace("　", " ", $word);
	$keys = get_search_words(preg_split('/\s+/', $word, -1, PREG_SPLIT_NO_EMPTY));
	foreach ($keys as $key=>$value)
		$keys[$key] = '/' . $value . '/S';

	$pages = get_existpages();

	// Avoid
	if ($base != '') {
		$pages = preg_grep('/^' . preg_quote($base, '/') . '/S', $pages);
	}
	if (! $search_non_list) {
		$pages = array_diff($pages, preg_grep('/' . $non_list . '/S', $pages));
	}
	$pages = array_flip($pages);
	unset($pages[$whatsnew]);

	$count = count($pages);
	foreach (array_keys($pages) as $page) {
		$b_match = FALSE;

		// Search for page name
		if (! $non_format) {
			foreach ($keys as $key) {
				$b_match = preg_match($key, $page);
				if ($b_type xor $b_match) break; // OR
			}
			if ($b_match) continue;
		}

		// Search auth for page contents
		if ($search_auth && ! check_readable($page, false, false)) {
			unset($pages[$page]);
			--$count;
		}

		// Search for page contents
		foreach ($keys as $key) {
			$b_match = preg_match($key, get_source($page, TRUE, TRUE));
			if ($b_type xor $b_match) break; // OR
		}
		if ($b_match) continue;

		unset($pages[$page]); // Miss
	}
	if ($non_format) return array_keys($pages);

	$r_word = rawurlencode($word);
	$s_word = htmlspecialchars($word);
	if (empty($pages))
		return $qm->replace('fmt_msg_notfoundresult', $s_word);

	ksort($pages);

	$retval = '<ul>' . "\n";
	foreach (array_keys($pages) as $page) {
		$s_page  = get_page_title($page);
		
		$r_page  = rawurlencode($page);
		$passage = $show_passage ? ' ' . get_passage(get_filetime($page)) : '';
		
		$tmp_li = ' <li><a href="' . $script . '?cmd=read&amp;page=' .
			$r_page . '&amp;word=' . $r_word . '">' . $s_page .
			'</a>' . $passage . '</li>' . "\n";
		
		$retval .= $tmp_li;
	}
	$retval .= '</ul>' . "\n";

	$retval .= $qm->replace(($b_type? 'fmt_msg_andresult': 'fmt_msg_orresult'), $s_word, count($pages), $count);

	return $retval;
}

?>
