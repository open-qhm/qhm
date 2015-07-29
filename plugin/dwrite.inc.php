<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: dwrite.inc.php,
//
// dwrite box plugin

/**
*
* 使い方 : 
*   &dwrite(passcode){表示する文言};
*
* インラインプラグイン ： 
*　　通常の場合 -- 単に、return しただけ
*　　編集モード -- 書き換え用フォームへのリンクを出力
*
* アクションプラグイン : 
*   
*   
*/

function plugin_dwrite_action()
{
	global $vars, $script;
	$qm = get_qm();

	//error
	if($vars['page']==''){
		return array('msg'=>$qm->m['plg_dwrite']['title_err'], 'body'=>'<p>'. $qm->m['plg_dwrite']['err_invalid_url']. '</p>');
	}

	if( $vars['mode'] == 'make' ){ //create mode 
		
		$template = $script.'?plugin=dwrite&page='.rawurlencode( $vars['page'] )
			.'&mode=write&code='.$vars['code'];
		$template_s = htmlspecialchars($template);
		
		$title = $qm->m['plg_dwrite']['title'];
		$contents = <<<EOD
<script type="text/javascript">
function gen_dwrite_code(val){
	var el = document.getElementById('dwrite_url');
	el.value = '{$template}&value='+encodeURIComponent(val);
}
</script>
<p style="text-align:left;font-weight:bold;">{$qm->m['plg_dwrite']['note']}<br />
<input type="text" size="40" id="dwrite_data" onkeyup="gen_dwrite_code(this.value);return false;" style="background-color:#ffc;" /></p>
<p style="text-align:left;">{$qm->m['plg_dwrite']['url']}<br />
<input type="text" size="40" id="dwrite_url" readonly="readonly" onclick="this.select();" />
</p>

<p style="text-align:left;">{$qm->m['plg_dwrite']['edit_url']}<br />
<input type="text" size="40" id="dwrite_url" readonly="readonly" value="{$template_s}" onclick="this.select();" />
</p>


EOD;
		auth_catbody($title, $contents);
		exit;
	} //書き込み確認
	else if( $vars['mode'] == 'write' ){
	
		$code = $vars['code'];
		$rep = array(); $cnt=0;
		
		foreach( get_source($vars['page']) as $line){
		
			if( $res = plugin_dwrite_getContent($code, $line) )
			{
				$rep[$cnt] = $res;
				$cnt++;
			}
		}
		
		//error
		if($cnt==0){
			return array('msg'=>$qm->m['plg_dwrite']['title_err'], 'body'=>'<p>'. $qm->m['plg_dwrite']['err_cannot_found']. '</p>');
		}
	

		$title = $qm->m['plg_dwrite']['title_confirm'];
		
		if( ! isset($vars['value']) ){
		
			$s_page = htmlspecialchars($vars['page']);
			$s_code = htmlspecialchars($vars['code']);
			
			$contents = <<<EOD
<p>{$qm->m['plg_dwrite']['note2']}</p>
<form method="post" action="{$script}">
<input type="hidden" name="plugin" value="dwrite" />
<input type="hidden" name="page" value="{$s_page}" />
<input type="hidden" name="mode" value="write" />
<input type="hidden" name="code" value="{$s_code}" />
<input type="text" name="value" size="40" /> <input type="submit" name="ok" value="{$qm->m['plg_dwrite']['btn_confirm']}" />
</form>
EOD;
		
		}
		else{

			$s_page = htmlspecialchars($vars['page']);
			$s_code = htmlspecialchars($vars['code']);
			$s_value = htmlspecialchars( $vars['value'] );
		
			$tmp_str = '';
			foreach($rep as $v){
				$tmp_str .= '「'.$v.'」';
			}
			$contents = $qm->replace('plg_dwrite.ntc_confirm', h($tmp_str), h($vars['value']));

			$contents .= <<<EOD
<form method="post" action="{$script}">
<input type="hidden" name="plugin" value="dwrite" />
<input type="hidden" name="page" value="{$s_page}" />
<input type="hidden" name="mode" value="do_write" />
<input type="hidden" name="code" value="{$s_code}" />
<input type="hidden" name="value" value="{$s_value}" /> <input type="submit" name="ok" value="{$qm->m['plg_dwrite']['btn_exec']}" />
</form>
EOD;

		}
		
		
		auth_catbody($title, $contents);
		exit;
	
	}
	else if( $vars['mode'] == 'do_write' ){

		$code = $vars['code'];
		$ms = array();
		
		$new_data = '';
		foreach( get_source($vars['page']) as $line){
		
			if( $res = plugin_dwrite_getContent($code, $line) )
			{
				$s = '&dwrite('.$code.'){'.$res.'};';
				$r = '&dwrite('.$code.'){'.$vars['value'].'};';
				
				$new_data .= str_replace($s, $r, $line);

			}
			else{
				$new_data .= $line;
			}
		}
						
		page_write($vars['page'], $new_data);

		$title = $qm->replace('plg_dwrite.title_result', $vars['page']);
		$url = $script.'?'.rawurlencode($vars['page']);
		$contents = $qm->replace('plg_dwrite.result', $url);
		auth_catbody($title, $contents);
		exit;

	}

	return array('msg'=>$title, 'body'=>$body);
}

function plugin_dwrite_inline()
{
	global $script, $vars,  $digest;
	static $number = array();
	$qm = get_qm();

	$page = isset($vars['page']) ? $vars['page'] : '';
	
	// dwrite-box-id in the page
	

	if (func_num_args() != 2) return $qm->replace('fmt_err_no_args', '#dwrite'). "\n";

	$args     = func_get_args();
	$s_page   = htmlspecialchars($page);

	$code = $args[0];
	$text = $args[1];
	

    $editable = edit_auth($page, FALSE, FALSE);
	if($editable){
		$href = $script.'?plugin=dwrite&page='.rawurlencode($page).'&code='
			.$code.'&mode=make&KeepThis=true&TB_iframe=true&height=450&width=650';
		$text = $text=='' ? $qm->m['plg_dwrite']['space'] : $text;
		return '<a href="'.$href.'" class="thickbox" style="border-bottom:1px dashed;color:inherit;">'.$text.'</a>';	
	}
	else{
		return $text;
	}
}

function plugin_dwrite_getContent($code, $line){
	$ms = array();
	if( preg_match('/&dwrite\('.$code.'\){(.*)};/', $line , $ms) )
	{
		$str = $ms[1];
		$len = strlen($str);
		$content = '';
		
		$stuck = 1;
		for($i=0; $i<$len; $i++){
						
			//stack trace
			if($str{$i} == '{'){
				$stuck++;
			}
			else if( $i+1<$len && $str{$i}.$str{$i+1} == '};'){
				$stuck--;
			}
			
			if($stuck==0)
				break;
			
			$content .= $str{$i};
		}
		
		if($content == '')
			return false;
		else
			return $content;
	}
	else{
		return false;
	}
}
?>
