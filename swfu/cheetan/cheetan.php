<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
	if( !defined( "LIBDIR" ) )
	{
		define( "LIBDIR", dirname(__FILE__) );
	}
	
	require_once LIBDIR . DIRECTORY_SEPARATOR . "boot.php";
	require_once LIBDIR . DIRECTORY_SEPARATOR . "pagination.php";
	
	$data		= array();
	$sanitize	= new CSanitize();
	$s			= $sanitize;

	//for magic quote gpc
	if( count( $_POST ) ) $_POST = $s->input_filter($_POST);
	if( count( $_GET ) ) $_GET = $s->input_filter($_GET);
	if( count( $_COOKIE ) ) $_COOKIE = $s->input_filter($_COOKIE);
	
	$dispatch	= new CDispatch();
	$controller	= $dispatch->dispatch( $data );
	$c			= &$controller;
	
?>
