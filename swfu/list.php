<?php
require_once( "config.php" );
require_once( "cheetan/cheetan.php" );

function action( &$c )
{
	set_menu($c);

	/** ***********************************************************************
	* 表示のページネーション、クエリの準備
	*
	* pn_sizeを変えると、一頁当たりの表示が変わる。
	*
	***************************************************************************/
	//pagenation setting
	$pagination = new Pagination();
	$pn_size = $c->admin->getListNum();
	$pn_page =  isset($_GET['pn_page']) ? $_GET['pn_page'] : 1;
	$order_tmp = '<a href="list.php?%QUERY%=%VALUE%&amp;order=%ORDER%&amp;sort=%SORT%">%TITLE%</a>';
	$order_que = '';
	
	//表示用のパラメータをセット
	$c->set('cols', $c->admin->getListCols());
	
	//query のテンプレを作る
	$order_link = array();
	$skey = array('/%ORDER%/','/%SORT%/','/%TITLE%/');
	
	$rkey = array('created','desc','新しい順');
	$o1 = preg_replace($skey, $rkey, $order_tmp);
	$rkey = array('created','asc','古い順');
	$o2 = preg_replace($skey, $rkey, $order_tmp);
	$order_link['更新日'] = array($o1, $o2);

	$rkey = array('size','desc','大きい');
	$o1= preg_replace($skey, $rkey, $order_tmp);
	$rkey = array('size','asc','小さい');
	$o2 = preg_replace($skey, $rkey, $order_tmp);
	$order_link['サイズ'] = array($o1, $o2);
	
	$rkey = array('name','asc','昇順');
	$o1 = preg_replace($skey, $rkey, $order_tmp);
	$rkey = array('size','desc','降順');
	$o2 = preg_replace($skey, $rkey, $order_tmp);
	$order_link['名前'] = array($o1, $o2);
	
	
	/** ***********************************************************************
	* セッションから、ページ名を作成、並び替えのクエリをセット
	*
	*
	*
	***************************************************************************/
	//ページ名設定
	if(isset($_SESSION['swfu']['page_name']))
	{
		$page = $_SESSION['swfu']['page_name'];
		$c->set('page_name', $page);

		$images = $c->image->find('$page_name=="'.$page.'"','created desc');
		$c->set('images',$images);
	}
	
	//並び替えクエリ
	if( isset($_GET['order']) && isset($_GET['sort']) )
	{
		$order_que = '&amp;order='.$_GET['order'].'&amp;sort='.$_GET['sort'];
	}
	

	/** ***********************************************************************
	* ページ名の変更
	*
	***************************************************************************/
	if(isset($_POST['change_page_name']))
	{
		$old_page = $_POST['old_page'];
		$new_page = $_POST['new_page'];
		
		$rs = $c->image->find('$page_name=="'.$old_page.'"');
		foreach($rs as $r){
			$r['page_name'] = $new_page;
			$c->image->update($r);
		}
		
		$c->redirect('list.php?page='.rawurlencode($new_page));
	}


	/** ***********************************************************************
	* ページ名の設定
	*
	***************************************************************************/
	if(isset($_POST['set_page_name']))
	{		
		if($_POST['new_page']=='')
		{
			$c->redirect('index.php');
		}
		else
		{
			$_SESSION['swfu']['page_name'] = $_POST['new_page'];
			$c->redirect('list.php?page='.rawurlencode($_POST['new_page']));
		}
	}
	
	
	/** ***********************************************************************
	* pageをもとに、リストを表示
	*
	*
	*
	***************************************************************************/
	if( isset($_GET['page']) )
	{
		$page = $_GET['page'];
		$cond = '$page_name=="'.$page.'"';
		
		$order = $order_que==''? 'created desc' : $_GET['order'].' '.$_GET['sort'];
		
		
		$total_cnt = $c->image->getCount($cond);
		
		
		$pagination->setLink('list.php?page='.rawurlencode($page).'&amp;pn_page=##PN_PAGE##'.$order_que);
		$pagination->setPage($pn_page);
		$pagination->setSize($pn_size); 
		$pagination->setTotalRecords($total_cnt);
		
		$limit = $pagination->getLimit();
		$images = $c->image->find($cond, $order, $limit);
		
		$c->set('images', $images);
		$c->set('pagination_link',$pagination->create_links());
		$disp_pagename = $page=='' ? '未分類' : $page;
		$c->set('h2title', '「'.$disp_pagename.'」のファイル一覧');
		

		$skey = array('/%QUERY%/','/%VALUE%/');
		$rkey = array('page',rawurlencode($page));
		
		$str = '';
		foreach($order_link as $key=>$val){
			$str .= $key.'[';
			foreach($val as $k=>$v)
				$order_link[$key][$k] = preg_replace($skey, $rkey, $v);
			$str .= implode(' , ', $order_link[$key]);
			$str .= '] ';
		}
		
		$page_form = '
<form class="style_form" action="'.$_SERVER['PHP_SELF'].'" method="post" style="text-align:right">
	<input id="change_page_name" type="text" name="new_page" size="14" value="'.$page.'" />
	<input type="submit" value="変更" name="change_page_name" onclick="return confirm_page_chg(\''.$page.'\');" />
	<input type="hidden" name="old_page" value="'.$page.'" />
</form>';

		$page_form_set = '
<form class="style_form" action="'.$_SERVER['PHP_SELF'].'" method="post" style="float:left;">
	<input id="new_page" type="hidden" name="new_page" value="'.$page.'" />
		<input type="submit" value="セット" name="set_page_name" onclick="return confirm_page_set();" />
	<input type="hidden" name="old_page" value="'.$page.'" />
</form>';

		$c->set('page_form', $page_form);
		$c->set('page_form_set', $page_form_set);
		$c->set('order_link', $str);
		return;
	}
	

	/** ***********************************************************************
	* labelをもとに、リストを表示
	*
	*
	*
	***************************************************************************/
	if( isset($_GET['label']) )
	{
		$label = $_GET['label'];
		$cond =	'array_key_exists(
					"'.$label.'",
					array_flip(explode(",",$label))
				)';
		$order = $order_que==''? 'created desc' : $_GET['order'].' '.$_GET['sort'];
		
		$total_cnt = $c->image->getCount($cond);
		
		$pagination->setLink('list.php?label='.rawurlencode($label).'&amp;pn_page=##PN_PAGE##'.$order_que);
		$pagination->setPage($pn_page);
		$pagination->setSize($pn_size); 
		$pagination->setTotalRecords($total_cnt);
		
		$limit = $pagination->getLimit();
		$images = $c->image->find($cond, $order, $limit);
		
		$c->set('images', $images);
		$c->set('pagination_link',$pagination->create_links());
		
		$label = $label=='' ? 'ラベルなし' : $label;
		$c->set('h2title', '「'.$label.'」ラベルのファイル一覧');
		

		$skey = array('/%QUERY%/','/%VALUE%/');
		$rkey = array('label',rawurlencode($label));
		
		$str = '';
		foreach($order_link as $key=>$val){
			$str .= $key.'[';
			foreach($val as $k=>$v)
				$order_link[$key][$k] = preg_replace($skey, $rkey, $v);
			$str .= implode(' , ', $order_link[$key]);
			$str .= '] ';
		}
		
		
		$c->set('order_link', $str);
		return;
	}
	
	
	/** ***********************************************************************
	* searchをもとに、リストを表示
	*
	*
	*
	***************************************************************************/
	if( isset($_GET['search']) )
	{
		$search = $_GET['search'];
		$search_arr = explode(' ',$_GET['search']);
		
		//name , description , page_name , label を検索
		$tmparr = array();
		foreach(array('name', 'description', 'page_name', 'label') as $key )
		{ //OR
			$arr = array();
			foreach($search_arr as $s)
			{ //AND
				$arr[] = '(strpos(strtoupper($'.$key.'),"'.strtoupper($s).'")!==false)';
			}
			$tmparr[] = '('.implode(' && ',$arr).')';
		}
		$cond = implode(' || ', $tmparr);
								
		$order = $order_que==''? 'created desc' : $_GET['order'].' '.$_GET['sort'];
		
		$total_cnt = $c->image->getCount($cond);

		$pagination->setLink('list.php?search='.rawurlencode($search).'&amp;pn_page=##PN_PAGE##'.$order_que);
		$pagination->setPage($pn_page);
		$pagination->setSize($pn_size); 
		$pagination->setTotalRecords($total_cnt);
		
		$limit = $pagination->getLimit();
		$images = $c->image->find($cond, $order, $limit);
		
		$c->set('images', $images);
		$c->set('pagination_link',$pagination->create_links());
		
		$c->set('h2title', '「'.$search.'」検索一覧');
		

		$skey = array('/%QUERY%/','/%VALUE%/');
		$rkey = array('search',rawurlencode($search));
		
		$str = '';
		foreach($order_link as $key=>$val){
			$str .= $key.'[';
			foreach($val as $k=>$v)
				$order_link[$key][$k] = preg_replace($skey, $rkey, $v);
			$str .= implode(' , ', $order_link[$key]);
			$str .= '] ';
		}
		
		
		$c->set('order_link', $str);
		return;
	}

	
	//削除
	if( isset($_GET['delete']) )
	{
		$fname = $_GET['delete'];
		$img = $c->image->findone('$name=="'.$fname.'"');
		$c->image->del('$id=="'.$img['id'].'"');
		
		if(file_exists(SWFU_DATA_DIR.$fname))
			unlink(SWFU_DATA_DIR.$fname);
		
		$c->redirect('index.php');
	}
	
	//ダウンロード
	if( isset($_GET['dl']) ){
		$fname = $_GET['dl'];
		
		$fp = fopen(SWFU_DATA_DIR.$fname, "rb");
	
		header("Cache-Control: public");
		header("Pragma: public");
		header("Accept-Ranges: none");
		header("Content-Transfer-Encoding: binary");
		header("Content-Disposition: attachment; filename={$fname}");
		header("Content-Type: application/octet-stream; name=$fname");
		
		fpassthru($fp);
		fclose($fp);
	
		exit();
	}
}
?>