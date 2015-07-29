<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
	require_once( dirname(__FILE__) . "/textsql.php" );
	
class CDBTextsql extends CDBCommon
{
	function connect( $config )
	{
		return true;
	}
	
	
	function findquery( $connect, $query, $condition = "", $order = "", $limit = "", $group = "" )
	{
		return false;
	}
	
	
	function findall( $connect, $table, $condition = "", $order = "", $limit = "", $group = "" )
	{
		$db		= new CTextDB();
		return $db->select( $condition, $order, $table, $limit );
	}
	
	
	function getcount( $connect, $table, $condition = "", $limit = "" )
	{
		$db		= new CTextDB();
		return $db->count( $condition, $table );
	}
	
	
	function insert( $table, $datas, $connect )
	{
		$db		= new CTextDB();
		return $db->insert( $datas, $table );
	}
	
	
	function update( $table, $datas, $condition, $connect )
	{
		$db		= new CTextDB();
		return $db->update( $datas, $condition, $table );
	}
	
	
	function del( $table, $condition, $connect )
	{
		$db		= new CTextDB();
		return $db->delete( $condition, $table );
	}
	
	
	function CreateCondition( $field, $value )
	{
		return '$' . "$field=='$value'";
	}
}
?>