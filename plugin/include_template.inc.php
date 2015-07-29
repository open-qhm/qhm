<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: include_template.inc.php,v 1.21 Time-stamp: <2009-09-08 06:40:48 kahata> Exp $
//
// Include-page with parameter plugin

//--------
//	| PageA
//	|
//	| // #include_template(PageB)
//	---------
//		| PageB
//		|
//		| // #include_template(PageC)
//		---------
//			| PageC
//			|
//		--------- // PageC end
//		|
//		| // #include_template(PageD)
//		---------
//			| PageD
//			|
//		--------- // PageD end
//		|
//	--------- // PageB end
//	|
//	| #include_template(): Included already: PageC
//	|
//	| // #include_template(PageE)
//	---------
//		| PageE
//		|
//	--------- // PageE end
//	|
//	| #include_template(): Limit exceeded: PageF
//	| // When PLUGIN_INCLUDE_PGE_MAX == 4
//	|
//	|
//-------- // PageA end

// ----

/*-----------------------------------------------
*プラグイン include_template
指定したページをテンプレートにして、パラメータリストのデータを表示する。~
Validation Check, セキュリティー面のチェックは各自で行うこと。~

*Usage
 #include_template(テンプレートページ名) {{
  キー1 = 値1
  キー2 = 値2
  ........
 }}

*テンプレートのページ
通常のwikiページで、値を表示する場所に{{{キー}}}を記入する。

*使用例
** wikiの記述
 #include_template(template/jusho または html/jusho) {{
 名前 = ○田△夫
 郵便番号 = xxx-yyyy
 住所 = 東京都×××××××××
 電話 = 01-1234-4567
 }}

**テンプレートページ1(template/jusho)
|名前|郵便番号|住所|電話|
|{{{名前}}}|{{{郵便番号}}}|{{{住所}}}|{{{電話}}}|
==noinclude==
この間はtemplate/jushoでのみ表示され、plugin埋め込みページには表示されません。
==/noinclude==

**テンプレートページ2(html/jusho)
 <table>
  <tr><td>名前</td><td>郵便番号</td><td>住所</td><td>電話</td></tr>
  <tr><td>{{{名前}}}</td><td>{{{郵便番号}}}</td><td>{{{住所}}}</td><td>{{{電話}}}</td></tr>
 </table>
==noinclude==
この間はtemplate/jushoでのみ表示され、plugin埋め込みページには表示されません。
==/noinclude==

-----------------------------------------------------*/

/**
 *   PukiWiki Include Template Plugin
 *   -------------------------------------------
 *   include_template.inc.php
 *   
 *   Copyright (c) 2009 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 
 *   modified : 2009-11-30 「戻る」リンクを非表示に
 *   
 */




// 
define('PLUGIN_INCLUDE_TEMPLATE_IS_EDIT_AUTH' , TRUE);     // Default: TRUE

// Default value of 'title|notitle' option
define('PLUGIN_INCLUDE_TEMPLATE_WITH_TITLE', FALSE);	// Default: FALSE(notitle)

// Max pages allowed to be included at a time

// 変更 Time-stamp: <07/02/16(金) 17:20:52 hata>
//define('PLUGIN_INCLUDE_MAX', 4);
define('PLUGIN_INCLUDE_TEMPLATE_MAX', 100);

// インクルードを禁止するページを正規表現でここに定義する
define('PLUGIN_INCLUDE_TEMPLATE_PROTECT' , 'FrontPage');

// パラメーターにhtmlのタグを許可するかどうか。
define('PLUGIN_INCLUDE_TEMPLATE_ALLOW_TAG' , TRUE);        // Default: FALSE(許可しない)

define('PLUGIN_INCLUDE_TEMPLATE_LDELIM' , '{{{');        // 左側デリミタ
define('PLUGIN_INCLUDE_TEMPLATE_RDELIM' , '}}}');        // 右側デリミタ
define('PLUGIN_INCLUDE_TEMPLATE_RAW_KW_DELIM' , '%');    // パラメータリストでの生置換する文字のデリミタ


// コマンド型で使用するとき、置換するデータを格納するページ
define('PLUGIN_INCLUDE_TEMPLATE_DATA_PAGE' , ':config/plugin/include_template/data');   

// 接頭辞がPHP(php)のページをphpコードとして実行を許可するかどうか
// 追加 Time-stamp: <08/07/19(土) 14:51:41 kahata>
define('PLUGIN_INCLUDE_TEMPLATE_ALLOW_EVAL' , FALSE);   // Default: FALSE(許可しない)

function plugin_include_template_action()
{
	global $script, $vars, $get, $post, $menubar, $_msg_include_restrict;
	static $included = array();
	static $count = 1;
	$qm = get_qm();

	$allow_tag = PLUGIN_INCLUDE_TEMPLATE_ALLOW_TAG;
	$data_page = PLUGIN_INCLUDE_TEMPLATE_DATA_PAGE;
	$include_template_is_edit_auth = PLUGIN_INCLUDE_TEMPLATE_IS_EDIT_AUTH;
	$allow_tag  = PLUGIN_INCLUDE_TEMPLATE_ALLOW_TAG;
	$include_template_protect = PLUGIN_INCLUDE_TEMPLATE_PROTECT;
	$allow_eval = PLUGIN_INCLUDE_TEMPLATE_ALLOW_EVAL;

//	$href  = 'javascript:history.go(-1)';
//	$ret = "<a href=\"$href\">[戻る]</a><br><p/>";
//	$body = $ret;
	$body = '';

	$include_template = new include_template();

	// $menubar will already be shown via menu plugin
	if (! isset($included[$menubar])) $included[$menubar] = TRUE;

	// Loop yourself
	$root = isset($vars['page']) ? $vars['page'] : '';
	$included[$root] = TRUE;


	// Get arguments
	// strip_bracket() is not necessary but compatible
	$page = isset($vars['template']) ? get_fullname(strip_bracket($vars['template']), $root) : '';
	if ($page == '') {
		$err_msg = $ret . $qm->m['plg_include_template']['err_usage_cmd']. "\n";
		return array('msg'=>  $qm->m['plg_include_template']['title_err'],'body'=> $err_msg);
	}

	if (! is_page($page)) {
		$err_msg = $ret . $qm->replace('plg_include_template.err_no_page', $page) . "\n";
		return array('msg'=>  $qm->m['plg_include_template']['title_err_read'],'body'=> $err_msg);
	} 
	if ($include_template_is_edit_auth) {
		if (! (PKWK_READONLY > 0 or is_freeze($page) or $include_template->is_edit_auth($page))) {
			$err_msg = $ret . $qm->replace('plg_include_template.err_not_editable', $page) . "\n";
			return array('msg'=> $qm->m['plg_include_template']['title_err'],'body'=> $err_msg);
		}
	}

	$id = isset($vars['id']) ? $vars['id'] : '';

	// I'm stuffed
	if (isset($included[$page])) {
		$err_msg = $ret . $qm->replace('plg_include_template.err_already_included', $page) . "\n";
		return array('msg'=> $qm->m['plg_include_template']['title_err'], 'body'=> $err_msg);
	} if(preg_match("/$include_template_protect/" , $page)){
		$err_msg = $ret . $qm->replace('plg_include_template.err_cannot_include', $page). "\n";
		return array('msg'=> $qm->m['plg_include_template']['title_err'], 'body'=> $err_msg);
	} if (! is_page($page)) {
		$err_msg = $qm->replace('plg_include_template.err_no_page', $page) . "\n";
		return array('msg'=>  $qm->m['plg_include_template']['title_err'], 'body'=> $err_msg);
	} if ($count > PLUGIN_INCLUDE_TEMPLATE_MAX) {
		$err_msg = $qm->replace('plg_include_template.err_limit', $page) . "\n";
		return array('msg'=>  $qm->m['plg_include_template']['title_err'],'body'=> $err_msg);
	} else {
		++$count;
	}

	// Include A page, that probably includes another pages
	$get['page'] = $post['page'] = $vars['page'] = $page;

	if (check_readable($page, false, false)) {
		$output = join('', get_source($page));

		$lines = get_source($data_page); 
		for($i= 0; $i < count($lines); $i++) {
        		$value = $include_template->get_values($lines[$i], $delim1 = "<>", $delim2 = '=');
    			if ($value['id'] == $id ) {
			// キーワード置換前処理　Time-stamp: <07/06/16(土) 10:18:17 kahata>
				foreach ($include_template->kw_replace as $key => $val) {
					$output = str_replace($key,$val, $output);
				}
			$title1  = $value['title'];
			$output = $include_template->param_replace($output, $value);
			}
		}
		if(preg_match("/^:?html.+/" , $page)){
		     $body .= str_replace("#freeze",'', $output);

		// PHP/pageのphpコード評価　Time-stamp: <08/07/15(火) 17:55:30 hata>
		} else if(preg_match("/^:?php.+/" , strtolower($page)) && $allow_eval){
			$output = str_replace("#freeze",'', $output);
			$output = str_replace('<?php','', $output);
			$output = str_replace('?>','', $output);
    		ob_start();
			eval($output);
    		$body = ob_get_contents();
    		ob_end_clean();

		} else {
 	  	$body .= $allow_tag ? convert_html($output) : convert_html(htmlspecialchars($output));
		}
	} else {
		$body = str_replace('$1', $page, $_msg_include_restrict);
	}
	$get['page'] = $post['page'] = $vars['page'] = $root;

	return array(
		'msg'=>  $title1,
		'body'=> $body
	);
}

function plugin_include_template_convert()
{
	global $script, $vars, $get, $post, $menubar, $_msg_include_restrict;
	static $included = array();
	static $count = 1;
	$qm = get_qm();

	$allow_eval = PLUGIN_INCLUDE_TEMPLATE_ALLOW_EVAL;

	$include_template = new include_template();

	$include_template_is_edit_auth = PLUGIN_INCLUDE_TEMPLATE_IS_EDIT_AUTH;

	if (func_num_args() == 0) return $qm->m['plg_include_template']['err_usage']. "\n";;

	// $menubar will already be shown via menu plugin
	if (! isset($included[$menubar])) $included[$menubar] = TRUE;

	// Loop yourself
	$root = isset($vars['page']) ? $vars['page'] : '';
	$included[$root] = TRUE;

	// Get arguments
	$args = func_get_args();

	// strip_bracket() is not necessary but compatible
	$page = isset($args[0]) ? get_fullname(strip_bracket(array_shift($args)), $root) : '';

//	if ($include_template_is_edit_auth || preg_match("/^:?html.+/" , $page)) {
// (仮)
	if ($include_template_is_edit_auth) {
		if (! (PKWK_READONLY > 0 or is_freeze($page) or $include_template->is_edit_auth($page))) {
			return $qm->replace('plg_include_template.err_not_editable', $page);
		}
	}

	$params = array_pop($args);

	$with_title = PLUGIN_INCLUDE_TEMPLATE_WITH_TITLE;
	$allow_tag  = PLUGIN_INCLUDE_TEMPLATE_ALLOW_TAG;

	if ($params != '') {
		switch(strtolower($params)) {
		case 'title'  : $with_title = TRUE;  break;
		case 'notitle': $with_title = FALSE; break;
		default       :
		// 変更 Time-stamp: <09/09/08(火) 06:39:33 kahata>
		    if (substr($params, -1) != "\r") {
        		$value = $include_template->get_values($params, $delim1 = "<>", $delim2 = '=');
				break;
			} else {
				$value = $include_template->get_values($params);
				break;
			}
		}
	}

	if (isset($args[0])) {
		switch(strtolower(array_shift($args))) {
		case 'title'  : $with_title = TRUE;  break;
		case 'notitle': $with_title = FALSE; break;
		}
	}

	$s_page = htmlspecialchars($page);
	$r_page = rawurlencode($page);
	$link = '<a href="' . $script . '?' . $r_page . '">' . $s_page . '</a>'; // Read link

	// I'm stuffed
	$include_template_protect = PLUGIN_INCLUDE_TEMPLATE_PROTECT;

	if (isset($included[$page])) {
		return $qm->replace('plg_include_template.err_already_included', $link) . "\n";
	} if(preg_match("/$include_template_protect/" , $page)){
		return $qm->replace('plg_include_template.err_cannot_include', $page) . "\n";
	} if (! is_page($page)) {
		return $qm->replace('plg_include_template.err_no_page', $s_page) . "\n";
	} if ($count > PLUGIN_INCLUDE_TEMPLATE_MAX) {
		return $qm->replace('plg_include_template.err_limit', $link) . "\n";
	} else {
		++$count;
	}

	// One page, only one time, at a time
//	$included[$page] = TRUE;

	// Include A page, that probably includes another pages
	$get['page'] = $post['page'] = $vars['page'] = $page;

	if (check_readable($page, false, false)) {
		$output = join('', get_source($page));

		// キーワード置換前処理　Time-stamp: <07/06/16(土) 10:18:17 kahata>
		foreach ($include_template->kw_replace as $key => $val) {
			$output = str_replace($key,$val, $output);
		}
		$output = $include_template->param_replace($output, $value);
		if(preg_match("/^:?html.+/" , strtolower($page))){
			$body = str_replace("#freeze",'', $output);

		// PHP/pageのphpコード評価　Time-stamp: <08/07/15(火) 17:55:30 hata>
		} else if(preg_match("/^php.+/" , strtolower($page)) && $allow_eval){
			$output = str_replace("#freeze",'', $output);
			$output = str_replace('<?php','', $output);
			$output = str_replace('?>','', $output);
    		ob_start();
			eval($output);
    		$body = ob_get_contents();
    		ob_end_clean();

		} else {
 			$body = $allow_tag ? convert_html($output) : convert_html(htmlspecialchars($output));
		}
	} else {
		$body = str_replace('$1', $page, $_msg_include_restrict);
	}
	$get['page'] = $post['page'] = $vars['page'] = $root;

	// Put a title-with-edit-link, before including document
	if ($with_title) {
		$link = '<a href="' . $script . '?cmd=edit&amp;page=' . $r_page .
			'">' . $s_page . '</a>';
		if ($page == $menubar) {
			$body = '<span align="center"><h5 class="side_label">' .
				$link . '</h5></span><small>' . $body . '</small>';
		} else {
			$body = '<h1>' . $link . '</h1>' . "\n" . $body . "\n";
		}
	}
	return $body;
}

///////////////////////////////////////
// include_template class
class include_template
{
	// 前処理で置換する文字の連想配列初期値 Time-stamp: <07/06/16(土) 10:14:00 kahata>
	public $kw_replace = array(
				"｛｛｛" => '{{{',
				"｝｝｝" => '}}}',
				"#hide(on)" => '',
				"#hide(off)" => '',
				"//==noinclude==" => '==noinclude==',
				"//==/noinclude==" => '==/noinclude==',
				"//==onlyinclude==" => '==onlyinclude==',
				"//==/onlyinclude==" => '==/onlyinclude==');

	// 連想配列（ハッシュ）を用いた置換処理
	function param_replace($output, $value)
	{	
		reset($value);

		for ($i=0;$i<count($value);$i++){
       			$k = key($value);
//			$pattern = "{{{" . $k . "}}}";
			$pattern = PLUGIN_INCLUDE_TEMPLATE_LDELIM . $k . PLUGIN_INCLUDE_TEMPLATE_RDELIM;


		// bug fix Time-stamp: <07/04/30(月) 08:31:06 kahata>
       			$v = $value[$k];
		//	$v = htmlspecialchars($value[$k]);
			$output = str_replace($pattern , $v , $output); // キーを置換
       			next($value);
		}

		// bug fix Time-stamp: <07/05/20(日) 07:56:18 kahata>
//		$output = ereg_replace("==noinclude==.+==/noinclude==", '' , $output);
		$output = preg_replace("'==noinclude==.+?==\/noinclude=='s","",$output);


		// ==onlyinclude== 〜 ==/onlyinclude== 追加 Time-stamp: <07/06/07(木) 10:34:59 kahata>
		$onlyinclude = preg_match_all("'==onlyinclude==(.+?)==\/onlyinclude=='s",$output,$matches);
		if($onlyinclude){
			$tmp = '';
			for ($i = 0; $i<$onlyinclude; $i++) {
				$tmp = $tmp . $matches[1][$i];
			}
			$output = $tmp;
		}

		return $output;
	}

	// パラメータリストの連想配列への取り込み処理
	function get_values($params, $delim1 = "\r", $delim2 = '=')
	{
		$delim = PLUGIN_INCLUDE_TEMPLATE_RAW_KW_DELIM;
//		$data   = explode("\r", $params);
                $data   = explode($delim1, $params);

		for($i=0;$i<count($data);$i++){
			$temp = explode($delim2, $data[$i] , 2);
			$key = trim(chop($temp[0]));
			$v = trim(chop(isset($temp[1])? $temp[1]: ''));

	//生置換する文字をパラメータリストから連想配列に取り込む
	// Time-stamp: <07/06/16(土) 10:31:50 kahata>
//			if(preg_match("'$delim(.+?)$delim's",$key,$matches)) {
			if(ereg("$delim(.+)$delim",$key,$matches)) {
    				$this->array_push_associative($this->kw_replace, array($matches[1] => $v));
			} else {
    				$value[$key] = $v;
  			}

		}
		return $value;
	}

	// template page の認証処理
	function is_edit_auth($page, $user = '')
	{
		global $edit_auth, $edit_auth_pages, $auth_method_type;
		if (! $edit_auth) {
			return FALSE;
		}
		// Checked by:
		$target_str = '';
		if ($auth_method_type == 'pagename') {
			$target_str = $page; // Page name
		} else if ($auth_method_type == 'contents') {
			$target_str = join('', get_source($page)); // Its contents
		}

		foreach($edit_auth_pages as $regexp => $users) {
			if (preg_match($regexp, $target_str)) {
				if ($user == '' || in_array($user, explode(',', $users))) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	// Append associative array elements　Time-stamp: <07/06/16(土) 10:19:44 kahata>
	function array_push_associative(&$arr) {
		$args = func_get_args();
		$ret = 0;
		foreach ($args as $arg) {
			if (is_array($arg)) {
				foreach ($arg as $key => $value) {
				$arr[$key] = $value;
				$ret++;
			}
			}else{
				$arr[$arg] = "";
			}
		}
		return $ret;
	}

}
?>