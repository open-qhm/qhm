<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
class CDBPgsql extends CDBCommon
{
	function connect( $config )
	{
		if( empty( $config['port'] ) )	$config['port'] = 5432;
		$connect	= pg_connect( "host={$config['host']} port={$config['port']} dbname={$config['db']} user={$config['user']} password={$config['pswd']}" );
		return $connect;
	}
	
	
	function query( $query, $connect )
	{
		$this->last_query	= $query;
	    list($usec, $sec)	= explode( " ", microtime() ); 
		$time				= (float)$sec + (float)$usec;
		$res				= pg_query( $connect, $query );
	    list($usec, $sec)	= explode( " ", microtime() ); 
		$this->query_time	= ( (float)$sec + (float)$usec ) - $time;
		if( $res )
		{
			if( $affected = pg_affected_rows( $res ) )
			{
				$this->affected_rows	= $affected;
			}
		}
		else
		{
			$this->last_error	= pg_last_error( $connect );
		}
		$this->_push_log();
		return $res;
	}
	
	
	function find( $query, $connect )
	{
		$ret	= array();
		if( $res = $this->query( $query, $connect ) )
		{
			$rownum	= pg_num_rows( $res );
			for( $i = 0; $i < $rownum; $i++ )
			{
				$row	= pg_fetch_array( $res, $i, PGSQL_ASSOC );
				array_push( $ret, $row );
			}
			pg_free_result( $res );
		}
		
		return $ret;
	}
	
	
	function count( $query, $connect )
	{
		if( $res = $this->query( $query, $connect ) )
		{
			$count	= pg_num_rows( $res );
			pg_free_result( $res );
			return $count;
		}
		
		return 0;
	}
	
	function field($field) {
		return $field;
	}
	
	function escape( $str )
	{
		if( function_exists( 'pg_escape_string' ) )
		{
			return pg_escape_string( $str );
		}
		
		return addslashes( $str );
	}
}
?>