<?php
/////////////////////////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: listbox.inc.php,v 1.1 2006/04/30 09:16:10 jjyun Exp $
//   This script is based on listbox2.inc.php by KaWaZ
// -----------------------------------------------------------------
// Copyright (C)
//   2004-2006 written by jjyun ( http://www2.g-com.ne.jp/~jjyun/twilight-breeze/pukiwiki.php )
// License: GPL v2 or (at your option) any later version
//

/**
 *   デフォルトのコンフィグファイル
 */
define('PLUGIN_LISTBOX_CONFIGPAGE', 'plugin/listbox');

function plugin_listbox_init()
{
	$cfg = array(
			'_listbox_cfg' => array (
				'imgEdit'  => 'paraedit.png',
				'imgRefer' => 'close-mini.png'
				)
			);
	set_plugin_messages($cfg);
}

function plugin_listbox_action()
{
	global $script, $vars;

	if( isset($vars['config']) ){
		plugin_listbox_mkconfig();
		header('Location: '.$script.'?'.rawurlencode(':config/'. PLUGIN_LISTBOX_CONFIGPAGE));
		exit;
	}

	$selectval = $vars['select'];

	$number = 0;
	$pagedata = '';
	$pagedata_old  = get_source($vars['refer']);
	foreach($pagedata_old as $line)
	{
		if( ! preg_match('/^(?:\/\/| )/', $line) &&
			preg_match_all('/(?:&listbox\(([^\)]*)\))/',
						   $line, $matches, PREG_SET_ORDER) )
		{
			$paddata = preg_split('/&listbox\([^\)]*\)/', $line);
			$line = $paddata[0];
			foreach($matches as $i => $match)
			{
				$opt = $match[1];
				if($vars['number'] == $number++)
				{
					list($val, $fieldname) = explode(',', $opt, 2);
					//リストからの物かどうか調べる
					if (plugin_listbox_getOptions($selectval, PLUGIN_LISTBOX_CONFIGPAGE, $fieldname, true)) {
						//ターゲットのプラグイン部分
						$opt = "$selectval,$fieldname";
					}
					
				}
				$line .= "&listbox($opt)" . $paddata[$i+1];
			}
		}
		$pagedata .= $line;
	}
	page_write($vars['refer'], $pagedata);

	//結果をテキストで返す
	header("Content-Type: application/text; charset=UTF-8");
	echo 'Listbox Changed';
	exit;
}


function plugin_listbox_inline()
{
	$number = plugin_listbox_getNumber();


	if(func_num_args() > 1)
	{
		$options = func_get_args();
		$value     = array_shift($options);
		$template  = PLUGIN_LISTBOX_CONFIGPAGE;
		$fieldname = array_shift($options);
		
		return plugin_listbox_getBody($number, $value, $template, $fieldname, true);
	}
	return FALSE;
}


function plugin_listbox_getNumber()
{
	global $vars;
	static $numbers = array();
	if( !array_key_exists($vars['page'],$numbers) )
	{
      $numbers[$vars['page']] = 0;
	}
	return $numbers[$vars['page']]++;
}

function plugin_listbox_getBody($number, $value, $template, $fieldname, $inline=false)
{
	global $script, $vars;
	global $_listbox_cfg;
	$qt = get_qt();
	$qt->setv('jquery_include', true);

	$lb_id = "qhm_listbox_$number";
	$options_html = plugin_listbox_getOptions($value, $template, $fieldname);
	$options_html = str_replace("\"", "\\\"", $options_html);

	//HTMLで出力するのはspanで囲ったvalue のみ
	$body = '<span id="'. $lb_id. '" class="qhm_listbox">'.$value.'</span>';

	$imgPath  = IMAGE_DIR;
	$imgEdit  = $_listbox_cfg['imgEdit'];
	$imgRefer = $_listbox_cfg['imgRefer'];

	//number 固有のスクリプト
	$optssetscript = '
<script type="text/javascript">
	qhmListboxConf["'. $lb_id .'"] = "'.$options_html.'";
</script>';
	//一度だけの準備スクリプト
	$readyscript = '
<script type="text/javascript">
<!--
	qhmListboxConf = {};
	//onchange event: ajax value change
	qhmListboxOnChange = function(){
		var $$ = $(this),
			number = $$.attr("id").split("_").pop();
		var data = {
			select: $$.val(),
			number: number,
			plugin: "listbox",
			refer: "'. $vars['page'] .'",
			set: true
		};
		$.post("index.php", data);
		$$.next().click();
	}
	$(function(){
		$("span.qhm_listbox").each(function(){
			var $$ = $(this),
				id = $$.attr("id"),
				number = id.split("_").pop(),
				opts = qhmListboxConf[id];
			$$.after("<select></select>")
				.next()
				.addClass("qhm_listbox_select")
				.append(opts)
				.attr("id", "qhm_listbox_select_" + number)
				.attr("disabled", true)
				.css("vertical-align", "middle")
				.change(qhmListboxOnChange);
			$$.remove();
		});
		
		//add link to config
		$("#body").append("<p><a href=\"'. $script .'?cmd=listbox&config=link\">&gt;&gt;&gt; listbox config</a></p>");
	});
//-->
</script>';

	//編集ボタンを表示する
	if ( LISTBOX3_APPLLY_MODECHANGE) {
		$readyscript .= '
<script type="text/javascript">
	$(function(){
		$("select.qhm_listbox_select").after("<img />")
			.next()
			.attr("name", "editTrigger")
			.attr("src", "'. $imgPath. $imgEdit.'")
			.attr("alt", "edit/refer")
			.css("margin-left", "3px")
			.toggle(
				function(){
					$(this)
					.attr("src", "'. $imgPath. $imgRefer.'")
						.prev().attr("disabled", false);
				},
				function(){
					$(this)
					.attr("src", "'. $imgPath. $imgEdit.'")
						.prev().attr("disabled", true);
				}
			);
	});
</script>';
	
	}
	$qt->appendv_once('plugin_listbox', 'beforescript', $readyscript);
	$qt->appendv('beforescript', $optssetscript);

	$body = $inline ? $body: '<div>'. $body. '</div>';
	
	return $body;
}

/**
 *   Config を読み込み、<option>〜のHTMLを返す
 *
 *   @params
 *     $value <string>: selected string OR search string at $retlist mode
 *     $config_name <string>: ConfigPageName; ex) plugin/listbox -> :config/plugin/listbox
 *     $field_name <string>: ConfigFieldName
 *     $retlist <boolean>: flag of return option list
 */
function plugin_listbox_getOptions($value, $config_name, $field_name, $retlist = false)
{
	$qm = get_qm();
	$options_html = '';

	//config ファイルがなければ作る
 	plugin_listbox_mkconfig();
 	
	$config = new Config($config_name);

	if( ! $config->read() )
	{
		return '<p>'. $qm->replace('plg_listbox.err_cfg_notfound', h($config_name)). '</p>';
	}
	
	$config->name = $config_name;

	$isSelect = 0;
	
	
	$options = $config->get($field_name);
	
	// 2列で指定されていたら、範囲と見なす
	if( is_array($options[0]) ){
	
		$s = $options[0][0];
		$e = $options[0][1];
		
		$options = array();
		for($i=$s; $i<=$e; $i++)
			$options[] = $i;
	}
	
	if ($retlist) {
		return in_array($value, $options)? $options: false;
	}
	
	foreach($options as $option)
	{
	
		if($option{0} == '~') continue;

		if($value == $option)
		{
			$isSelect = 1;
			$options_html .= "<option value='$option' style='' selected='selected'>$option</option>";
		}
		else
		{
			$options_html .= "<option value='$option' style='' >$option</option>";
		}
	}
  
	if( $isSelect == 0 )
	{
		$options_html = "<option value='…' selected='selected'>…</option>" . $options_html;
	}
	
	return $options_html;
}

function plugin_listbox_getStyle($s_format)
{
	if( $s_format == '') return '';
  
	$format_enc = htmlspecialchars($s_format);
	$format_enc = preg_replace("/\%s/", '', $format_enc);
	
	$opt_format='';
	$matches=array();
	while ( preg_match('/^(?:(BG)?COLOR\(([#\w]+)\)):(.*)$/', $format_enc, $matches) )
	{
		if ($matches[0])
		{
			$style_name = $matches[1] ? 'background-color' : 'color';
			$opt_format .= $style_name . ':' . htmlspecialchars($matches[2]) . ';';
			$format_enc = $matches[3];
		}
	}
	return $opt_format;
}

function plugin_listbox_mkconfig(){
	$conffile = ':config/'. PLUGIN_LISTBOX_CONFIGPAGE;
	if(! is_page( $conffile ) ){
		$qm = get_qm();
		$maxyear = date("Y") + 5;
		$contents = '#close

* listbox setting [#v69f5c78]

'. $qm->m['plg_listbox']['cfg_desc']. '

'. $qm->m['plg_listbox']['cfg_ex1']. '

'. $qm->m['plg_listbox']['cfg_ex2']. '

'. $qm->m['plg_listbox']['cfg_ntc']. '


* member [#j18e38d8]
| -- |
|Taro|
|Hanako|
|Ken|
|Michael|

* year [#cad00f59]
|1960|'. $maxyear. '|

* mon [#c1ae4bb0]
|1|12|

* Mon
|Jan.|
|Feb.|
|Mar.|
|Apr.|
|May |
|Jun.|
|Jul.|
|Aug.|
|Sep.|
|Oct.|
|Nov.|
|Dec.|

* Month
|January|
|February|
|March|
|April|
|May|
|June|
|July|
|August|
|September|
|October|
|November|
|December|

* day [#ub609568]
|1|31|

* hour [#l156dc58]
|1|24|

* min [#h6c0ab82]
|1|60|

* sec [#lfb1a875]
|1|60|
';

		page_write($conffile, $contents);
	}

}
?>
