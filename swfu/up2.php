<?php

require_once( "config.php" );
require_once( "cheetan/cheetan.php" );

function action( &$c )
{
	set_menu($c);
	$c->set('_page_title','トップ');
		
	$page_name = isset($_GET['page']) ? $_GET['page'] : '';
	$c->set('page_name', $page_name);
	
	$config = $c->admin->getConfig();
	if($config['overwrite']){
		$c->set('overwrite_msg','上書き保存');
	}
	else{
		$c->set('overwrite_msg','自動で別名保存');	
	}

	
	$_additional_head = '';
	
	$c->set('_additional_head', $_additional_head);
	
}

?>
