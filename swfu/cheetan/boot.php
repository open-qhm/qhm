<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
	require_once LIBDIR . DIRECTORY_SEPARATOR . "object.php";
	require_once LIBDIR . DIRECTORY_SEPARATOR . "database.php";
	require_once LIBDIR . DIRECTORY_SEPARATOR . "sanitize.php";
	require_once LIBDIR . DIRECTORY_SEPARATOR . "validate.php";
	require_once LIBDIR . DIRECTORY_SEPARATOR . "controller.php";
	require_once LIBDIR . DIRECTORY_SEPARATOR . "view.php";
	require_once LIBDIR . DIRECTORY_SEPARATOR . "model.php";
	require_once LIBDIR . DIRECTORY_SEPARATOR . "dispatch.php";
	if( file_exists( LIBDIR . DIRECTORY_SEPARATOR . "db" . DIRECTORY_SEPARATOR . "common.php" ) )
	{
		require_once LIBDIR . DIRECTORY_SEPARATOR . "db" . DIRECTORY_SEPARATOR . "common.php";
	}
	if( file_exists( LIBDIR . DIRECTORY_SEPARATOR . "db" . DIRECTORY_SEPARATOR . "mysql.php" ) )
	{
		require_once LIBDIR . DIRECTORY_SEPARATOR . "db" . DIRECTORY_SEPARATOR . "mysql.php";
	}
	if( file_exists( LIBDIR . DIRECTORY_SEPARATOR . "db" . DIRECTORY_SEPARATOR . "pgsql.php" ) )
	{
		require_once LIBDIR . DIRECTORY_SEPARATOR . "db" . DIRECTORY_SEPARATOR . "pgsql.php";
	}
	if( file_exists( LIBDIR . DIRECTORY_SEPARATOR . "db" . DIRECTORY_SEPARATOR . "txtsql.php" ) )
	{
		require_once LIBDIR . DIRECTORY_SEPARATOR . "db" . DIRECTORY_SEPARATOR . "txtsql.php";
	}
	
   if( !defined( "SCRIPTFILE" ) )
   {
	   //define( "SCRIPTFILE", basename( $_SERVER["SCRIPT_FILENAME"] ) );
	   define( "SCRIPTFILE", basename( htmlspecialchars($_SERVER["PHP_SELF"]) ) );
   }
	
	if( !defined( "SCRIPTDIR" ) )
	{
		//define( "SCRIPTDIR", dirname( $_SERVER["SCRIPT_FILENAME"] ) . DIRECTORY_SEPARATOR );
		define( "SCRIPTDIR", dirname( htmlspecialchars($_SERVER["PHP_SELF"]) ) . DIRECTORY_SEPARATOR );
	}
?>
