<?php
require_once( "config.php" );
require_once( "cheetan/cheetan.php" );
	
function action( &$c )
{
	$c->setViewFile('ctp/index_.html');
	$c->set('_page_title','トップ');

	//ページ名があれば、
	if(isset($_GET['page']) && $_GET['page']!='')
	{
		$page = $_GET['page'];
		$_SESSION['swfu']['page_name'] = $page;
				
		$c->redirect('list.php?page='.rawurlencode($page));
		exit;
	}
}

?>