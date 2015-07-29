<?php

// ---------------------------------------------
// conversion.inc.php   v. 0.9
//
// 簡易のコンバージョン率を計測するためのプラグイン
// viewerは、?cmd=conversion です
//
// ブロックプラグインは、呼び出されたページのコンバージョンを計る
// インラインプラグインは、クリック（リンク）の計測に使う
//
// 初期ページを通らないと、カウントしない場合は、フラグを挿入すること。
//
//
// writed by hokuken.com 2007 8/24
// ----------------------------------------------- 

define('PLUGIN_CONVERSION_SUFFIX', '.convs');
define('PLUGIN_CONVERSION_PREFIX', 'QHMCONV_');
define('PLUGIN_CONVERSION_FORCE_INIT', true);

function plugin_conversion_convert()
{
	global $vars, $script;
	$qm = get_qm();
	$qt = get_qt();
    $page = isset($vars['page']) ? $vars['page'] : '';
    
    $args = func_get_args();
    $num = func_num_args();
    
	//---- キャッシュのための処理を登録 -----
	if($qt->create_cache) {
		$args = func_get_args();
		return $qt->get_dynamic_plugin_mark(__FUNCTION__, $args);
	}
	//------------------------------------
    
    //check
    if($num != 2 && $num !=4){
    	return $qm->replace('fmt_err_cvt', 'conversion', $qm->m['plg_conversion']['err_usage']);
    }

    //変数を格納
    $step = strip_autolink($args[0]);
    $group  = strip_autolink($args[1]);
    $pattern = isset($args[2]) ? $args[2] : '';
    $name = isset($args[3]) ? $args[3] : $page;
    $en_group = rawurldecode($group);
        
    //edit auth check
    $editable = edit_auth($page, FALSE, FALSE);
        
    //編集モードの場合
    if($editable){
    	$msg = plugin_conversion_msg();
        return <<<EOD
<div style="border:1px dashed #666;background-color:#eee;margin:1em;padding:0px 1em;">
<p><strong>{$qm->m['plg_conversion']['ntc_admin']}</strong></p>
<ul>
  <li>ページ名 : $name</li>
  <li>グループ名 : $group</li>
  <li>ステップ : $step</li>
  <li><a href="$script?cmd=conversion&group=$en_group" target="new">{$qm->m['plg_conversion']['label_result']}</a></li>
  <li>パターン : $pattern</li>
</ul>
<p>$msg</p>
</div>
EOD;
    }
    
    //コンバージョン計測
    return plugin_conversion_count($step, $group, $name, $pattern);
}


function plugin_conversion_inline(){
	global $vars, $script;
	$qm = get_qm();
    $page = isset($vars['page']) ? $vars['page'] : '';
    
    
    $args = func_get_args();
    if( count($args) != 5 ){
    	return $qm->replace('fmt_err_iln', 'conversion', $qm->m['plg_conversion']['err_usage_iln']);
    }
    
    $text = array_pop($args);
    list($step, $group, $name, $url) = $args;
    if( !is_url($url)){
    	return $qm->replace('fmt_err_iln', 'conversion', $qm->m['plg_conversion']['err_url']);
    }
    
    $raw_url = $url;
    
    $step = rawurlencode($step);
    $group = rawurlencode($group);
    $name = rawurlencode($name);
    $url = rawurlencode($url);
        
    
    $dest = $script.'?cmd=conversion&mode=link&step='
    		.$step.'&group='.$group.'&name='.$name.'&url='.$url;
    
    
    //edit auth check
    $editable = edit_auth($page, FALSE, FALSE);
    if($editable){
    	return '<a href="'.$dest.'">'.$text.'</a><span style="font-size:11px;background-color:#fdd">←'. $qm->m['plg_conversion']['ntc_admin']. '</span>';
    }
    else{
    	return '<a href="'.$dest.'">'.$text.'</a>';
    }
}

function plugin_conversion_count($step, $group, $name, $pattern=''){

	$qm = get_qm();
	//loading conversion result
	$file = CACHE_DIR . encode($group) . PLUGIN_CONVERSION_SUFFIX;
	$fp = fopen($file, file_exists($file) ? 'r+' : 'w+')
		or die('conversion.inc.php: '. $qm->replace('fmt_err_open_cachedir', basename($file)));
	set_file_buffer($fp, 0);
	flock($fp, LOCK_EX);
	rewind($fp);
	
	$res = array();
	$res_pattern = array();
	while( !feof($fp) )
	{
		list($r_step, $count, $r_name, $p_log) = array_pad(explode(',', trim(fgets($fp, 1024)), 4), 4, '');
		if($r_step != '')
		{
			$res[$r_step]['count'] = trim($count);
			$res[$r_step]['name'] = trim($r_name);
		}
		
		if($p_log != '')//exist pattern data
		{ 
			$ptrn = explode(',',$p_log);
			for($i=0; $i<count($ptrn); $i+=2)
			{
				$res_pattern[$r_step][ $ptrn[$i] ] = $ptrn[$i+1];
			}
		}
	}
	
	// $res[ステップ番号]--[count] カウント
	//                +-[name] ページ名
	//
	// $res_pattern[ステップ番号]--[パターン名1] カウント
	//                        +-[パターン名2] カウント
	//                        +-[パターン名3] カウント
	
	//read cookie
	$en_group = encode($group);
	$ckname = PLUGIN_CONVERSION_PREFIX.$en_group;
	$path = dirname($_SERVER['PHP_SELF']);
	
	//コンバージョンを初期設定する(つまり、クッキーを設定する)か、チェック
	if( !isset($_COOKIE[$ckname]) ){ //クッキーがセットされていない
		if(PLUGIN_CONVERSION_FORCE_INIT && $step!=1){ //初期ページを強制されていて、stepが1でない
			return ''; //何もせず、スルー
		}
	}

	//the first access on conversion process	
	if( !isset($_COOKIE[$ckname]) ){
	
		setcookie($ckname, $step.','.$pattern, time()+60*60*24*30, $path);
		$res[$step]['count'] ++;
		$res[$step]['name'] = $name;
		ksort($res);
		ksort($res_pattern);
		
		$res_pattern[$step][$pattern]++;
				
		rewind($fp);
		foreach ( $res as $key=>$val){
			if($key != '')
			{
				fputs($fp, $key.','.$val['count'].','.$val['name']);
				if( isset($res_pattern[$key]) )
				{
					foreach($res_pattern[$key] as $key_p=>$val_p)
					{
						fputs($fp, ','.$key_p.','.$val_p);
					}
				}
				
				fputs($fp, "\n");
			}
		}
		
		flock($fp, LOCK_UN);
		fclose($fp);
		return '';
	}
	else{ //conversion count
		$tmp_array = explode(",",$_COOKIE[$ckname]);
		
		$ck_step = array_shift($tmp_array);
		if( $ck_step < $step )
		{
			$pattern = array_shift($tmp_array);
			if($pattern != '')
				$res_pattern[$step][$pattern]++;

			
			$ck_str = ($pattern == '') ? $step : $step.','.$pattern;
			setcookie($ckname, $ck_str, time()+60*60*24*30, $path);
			
			$res[$step]['count'] ++;
			$res[$step]['name'] = $name;
			ksort($res);
			ksort($res_pattern);
			
			rewind($fp);
			foreach ( $res as $key=>$val){
				if($key != '')
				{
					fputs($fp, $key.','.$val['count'].','.$val['name']);
					if( isset($res_pattern[$key]) )
					{
						foreach($res_pattern[$key] as $key_p=>$val_p)
						{
							fputs($fp, ','.$key_p.','.$val_p);
						}
					}
					
					fputs($fp, "\n");
				}
			}
			
			flock($fp, LOCK_UN);
			fclose($fp);	
			return '';
		}
	}


	flock($fp, LOCK_UN);
	fclose($fp);
	
	return '';
}


function plugin_conversion_action()
{
	global $vars, $script;
	$qm = get_qm();
	$msg = plugin_conversion_msg();
	
	//inlineプラグインからの呼び出し
	if( isset($vars['mode']) 
		&& $vars['mode'] == 'link'
		&& isset($vars['step'])
		&& isset($vars['group'])
		&& isset($vars['name'])
		&& isset($vars['url'])
		)
	{
		plugin_conversion_count($vars['step'], $vars['group'], $vars['name']);
		header("Location: ".$vars['url']);
		exit;
	}
	
	
	//認証チェック
	if(!edit_auth($page, FALSE, FALSE)){
        header("Location: $script");
        exit();
    }
	
		
	//表示 or 削除
	if( isset($vars['group']) )
	{

		$group = $vars['group'];
		$en_group = encode( $group );
		$file = CACHE_DIR . $en_group . PLUGIN_CONVERSION_SUFFIX;
		
		//削除
		if(isset($vars['mode']) && $vars['mode'] == 'delete')
		{
			unlink($file);
			return array('msg'=>$qm->m['plg_conversion']['title_delete_result'], 'body'=>$qm->replace('plg_conversion.delete_result', $group));
		}
	
		//表示
		$fp = fopen($file, file_exists($file) ? 'r+' : 'w+')
			or die('conversion.inc.php: '. $qm->replace('fmt_err_open_cachedir', basename($file)));
		set_file_buffer($fp, 0);
		flock($fp, LOCK_EX);
		rewind($fp);

		$cnt = 0;		
		$res = array();
		while( !feof($fp) )
		{
			$tmp_str = trim(fgets($fp, 1024));
			list($r_step, $count, $r_name, $pattern) = explode(',', $tmp_str, 4 );
			if($r_step != '')
			{
				$res[$cnt]['step'] = trim($r_step);
				$res[$cnt]['count'] = trim($count);
				$res[$cnt]['name'] = trim($r_name);
				
				$tmp_arr = explode(',',$pattern);
				if( $tmp_arr[0] != '')
				{
					//echo "----\n";
					//var_dump($tmp_arr);
					
					$p_array = array();
					for($i=0; $i<count($tmp_arr); $i+=2)
					{
						$p_array[ $tmp_arr[$i] ] = $tmp_arr[$i+1];
					}
					$res[$cnt]['pattern'] = $p_array;
				}
				$cnt++;
			}
		}
		
		flock($fp, LOCK_UN);
		fclose($fp);
		
		$qm = get_qm();
		$body = "[[{$qm->m['plg_conversion']['link_result']}>{$script}?cmd=conversion]] > here\n";
		$body .= $qm->replace('plg_conversion.result', $group, $msg);
		
		$body .= $qm->m['plg_conversion']['th_result'];
		
		$before = 0;
		$p_before = NULL;
		foreach($res as $key=>$data)
		{
			$now = $data['count'];
			$conv = ($before != 0 && $now != 0 ) 
				? round( $now / $before * 100, 2).'%' : '--';
			$before = $now;
			
			$p_now = $data['pattern'];
			
			$p_conv = '';
			$p_user = '';
			foreach($p_now as $p_key=>$p_cnt)
			{
				$p_user .= $p_key.': '.$p_cnt;
				if( isset($p_before) && isset($p_before[$p_key]) && $p_before[$p_key] != 0
					&& $p_cnt != 0 )
				{
					$p_user .= " ( ". round($p_cnt/$p_before[$p_key]*100,2) ."% )";
				}
				else
				{
					$p_user .= ' ( -- )';
				}
				$p_user .= "&br;";
			}
			$p_before = $p_now;
				
			$body .= "| {$data['step']} | {$data['name']} |{$now}&br;{$conv} |{$p_user} |\n";
		}
		
		$start = $res[0]['count'];
		$end = $res[ count($res)-1  ]['count'];
		$total_conv = ($start != 0 && $end != 0)
			? round( $end / $start * 100, 2) : '--';
			
		$p_start = $res[0]['pattern'];
		$p_end = $res[ count($res)-1 ]['pattern'];
		$p_total = '';
		foreach($p_start as $key=>$val)
		{
			if( isset($p_end[$key]) && ($p_end[$key] != 0) && ($p_start[$key]) )
			{
				$p_total .= $key.': '.round( $p_end[$key]/$val*100 ,2)."%&br;";
			}
		}
		
		$body .= $qm->replace('plg_conversion.tf_result', $total_conv, $p_total);
		
		
		
		return array('msg'=>$qm->replace('plg_conversion.title_result'), 'body'=>convert_html($body));
	}
	else{ //一覧の表示
	
		$body = $qm->replace('plg_conversion.list_result', $msg);
		
		$handle = opendir(CACHE_DIR);
		while(($entry = readdir($handle) ))
		{
			$matches = array();
			if(preg_match('/(.*)'.PLUGIN_CONVERSION_SUFFIX.'$/',$entry, $matches))
			{
				$group = decode($matches[1]);
				$en_group = rawurlencode($group);
				
				$access = $script.'?cmd=conversion&group='.$en_group;
				$del = $access.'&mode=delete';
				$body .= "- [[{$group}>{$access}]] ([[{$qm->m['plg_conversion']['link_init']}>{$del}]])\n";
			}
		}
	
		return array('msg'=>'hello', 'body'=>convert_html($body));
	}
}

function plugin_conversion_msg(){
	if(PLUGIN_CONVERSION_FORCE_INIT){
		$qm = get_qm();
		return $qm->m['plg_conversion']['force_init'];
	}
	
	return '';
}


?>
