<?php
require_once( "config.php" );
require_once( "cheetan/cheetan.php" );
	
function action( &$c )
{
	$c->setViewFile('ctp/index_.html');
	$c->set('_page_title','トップ');

	if(isset($_SESSION['swfu']['page_name']))
	{
		unset($_SESSION['swfu']['page_name']);
	}
	
	set_menu($c);
}
?>