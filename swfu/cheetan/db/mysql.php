<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
class CDBMysql extends CDBCommon {

	function connect($config) {
		$host = $config['host'];
		if (!empty($config['port'])) {
			$host .= ':' . $config['port'];
		}
		$connect = mysql_connect($host, $config['user'], $config['pswd']);
		if($connect) {
			mysql_select_db($config['db'], $connect);
		}
		return $connect;
	}
	
	
	function query( $query, $connect )
	{
		$this->last_query	= $query;
	    list($usec, $sec)	= explode( " ", microtime() ); 
		$time				= (float)$sec + (float)$usec;
		$res				= mysql_query( $query, $connect );
	    list($usec, $sec)	= explode( " ", microtime() ); 
		$this->query_time	= ( (float)$sec + (float)$usec ) - $time;
		if( $res )
		{
			if( $last_insert_id = mysql_insert_id( $connect ) )
			{
				$this->last_insert_id	= $last_insert_id;
			}
			if( $affected = mysql_affected_rows( $connect ) )
			{
				$this->affected_rows	= $affected;
			}
		}
		else
		{
			$this->last_error	= mysql_error( $connect );
		}
		$this->_push_log();
		return $res;
	}
	
	
	function find( $query, $connect )
	{
		$ret	= array();
		if( $res = $this->query( $query, $connect ) )
		{
			while( $row = mysql_fetch_assoc( $res ) )
			{
				array_push( $ret, $row );
			}
			mysql_free_result( $res );
		}
		
		return $ret;
	}
	
	
	function count( $query, $connect )
	{
		if( $res = $this->query( $query, $connect ) )
		{
			$count	= mysql_num_rows( $res );
			mysql_free_result( $res );
			return $count;
		}
		
		return 0;
	}
	
	
	function escape( $str )
	{
		if( function_exists( 'mysql_real_escape_string' ) )
		{
			return mysql_real_escape_string( $str );
		}
		
		return mysql_escape_string( $str );
	}
}
?>