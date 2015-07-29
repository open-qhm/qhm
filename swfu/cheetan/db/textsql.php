<?php

define( "TEXTDB_CR", "##CR##" );
define( "TEXTDB_LF", "##LF##" );
define( "TEXTDB_SEP", "##SEP##" );
define( "TEXTDB_FB", "##FINDBY##" );

class CTextDB
{
	var $fpath = "";
	var $columns;
	var $column;
	var $columncnt;
	var $i;
	var	$datas;
	var $condition;
	var $key;
	var	$data;
	var $cmpkey;
	var $cmpdesc;
	var $lastid;

	function CTextDB( $fpath = "" )
	{
		$this->fpath = $fpath;
	}


	function SetFileName( $fpath )
	{
		$this->fpath = $fpath;
	}


	function insert( $datas, $fpath = "" )
	{
		if( !$fpath )	$fpath = $this->fpath;
		if( $this->_read_header( $fpath, $maxid ) )
		{
			$fplusmaxid = TRUE;	// added by hokuken
			$pieces	= array();
			foreach( $this->columns as $i => $column )
			{
				if( $i )
				{
					$pieces[$column] = isset($datas[$column]) ? $datas[$column] : "";
				}
				else
				{
					// added by hokuken  -- case by setting id
					if (isset($datas[$column])) {
						$rs = $this->select('$id=="'.$datas[$column].'"');
						if ( count($rs) == 0) {
							$pieces[$column] = $datas[$column];
							$fplusmaxid = FALSE;
						}
						else {
							return FALSE;
						}
					} else {
						$pieces[$column] = $maxid;
					}
				}
			}
			$recode	= implode( TEXTDB_SEP, $pieces );
			$recode	= $this->_initstr( $recode );
			
			$file	= $this->_file_read( $fpath );
			$count	= count( $file );
						
			if( $fp = fopen( $fpath, "a" ) )
			{
				flock( $fp, LOCK_EX );
				ftruncate($fp, 0);
				rewind($fp);
				if ($fplusmaxid == TRUE) $maxid++;
				fwrite( $fp, $file[0] );
				fwrite( $fp, $maxid . "\n" );
				for( $i = 2; $i < $count; $i++ )
				{
					fwrite( $fp, $file[$i] );
				}
				fwrite( $fp, $recode . "\n" );
				fclose( $fp );

				$this->lastid = $maxid -1;
				return TRUE;
			}
		}
		
		return FALSE;
	}

	function update( $datas, $condition, $fpath = "" )
	{
		if( !$fpath )	$fpath = $this->fpath;
		if( $this->_read_header( $fpath, $maxid ) )
		{
			$file	= $this->_file_read( $fpath );
			$count	= count( $file );			
			
			if( $fp = fopen( $fpath, "a" ) )
			{
				flock( $fp, LOCK_EX );
				ftruncate($fp, 0);
				rewind($fp);
				fwrite( $fp, $file[0] );
				fwrite( $fp, $file[1] );
				$this->_strip_findby($condition);
				$this->condition	= $condition;
				for( $i = 2; $i < $count; $i++ )
				{
					$this->datas	= explode( TEXTDB_SEP, trim( $file[$i] ) );
					if( $this->_check_condition() )
					{
						$pieces	= array();
						foreach( $this->columns as $j => $column )
						{
							if( $j )
							{
								if( array_key_exists( $column, $datas ) )
								{
									$pieces[$column] = $datas[$column];
								}
								else
								{
									$pieces[$column] = $this->datas[$j];
								}
							}
							else
							{
								$pieces[$column] = $this->datas[$j];
							}
						}
						$recode	= implode( TEXTDB_SEP, $pieces );
						$recode	= $this->_initstr( $recode );
						fwrite( $fp, $recode . "\n" );
					}
					else
					{
						fwrite( $fp, $file[$i] );
					}
				}
				fclose( $fp );
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	function delete( $condition, $fpath = "" )
	{
		if( !$fpath )	$fpath = $this->fpath;
		if( $this->_read_header( $fpath, $maxid ) )
		{
			$file	= $this->_file_read( $fpath );
			$count	= count( $file );
						
			if( $fp = fopen( $fpath, "a" ) )
			{
				flock( $fp, LOCK_EX );
				ftruncate($fp, 0);
				rewind($fp);
				fwrite( $fp, $file[0] );
				fwrite( $fp, $file[1] );
				if( !strlen( $condition ) )	$condition = "1";
				$this->_strip_findby($condition);
				$this->condition	= $condition;
				for( $i = 2; $i < $count; $i++ )
				{
					$this->datas	= explode( TEXTDB_SEP, trim( $file[$i] ) );
					if( !$this->_check_condition() )
					{
						fwrite( $fp, $file[$i] );
					}
				}
				fclose( $fp );
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	function select( $condition, $order = "", $fpath = "", $limit = '' )
	{
		if( !$fpath )	$fpath = $this->fpath;
		$ret	= array();
		$lstart = "";
		$lnum = "";
		
		
		if( $this->_read_header( $fpath, $maxid ) )
		{
			$file				= $this->_file_read( $fpath );
			$count				= count( $file );
			if( !strlen( $condition ) )	$condition = "1";
			$findby = false;
			if( preg_match( '/^\$([a-zA-Z\d_]+)==([\'"])(.*?)\2' . TEXTDB_FB . '$/', trim($condition), $tmparr) )
			{
				$find_key = $tmparr[1];
				$quote = $tmparr[2];
				$find_value = $tmparr[3];
				$findby = true;
				$find_id = array_search($tmparr[1], $this->columns);
				$condition = "\${$find_key}=={$quote}{$find_value}{$quote}";
			}
			$this->condition	= $condition;
			$limit				= $this->_parse_limit( $limit );
			if( $limit )
			{
				$lstart	= $limit[0];
				$lnum	= $limit[1];
			}
			for( $i = 2; $i < $count; $i++ )
			{
				$tmparr = array();
				$this->datas	= explode( TEXTDB_SEP, trim( $file[$i] ) );
				//selection algorithm
				if($condition==='1'){ //findAll

					$data	= array();
					for( $j = 0; $j < $this->columncnt; $j++ )
					{
						$data[$this->columns[$j]]	= $this->_reversestr( $this->datas[$j] );
					}
					array_push( $ret, $data );

				}
				//find by
				else if( $findby )
				{
					if( $this->datas[$find_id]==$find_value )
					{
						$data	= array();
						for( $j = 0; $j < $this->columncnt; $j++ )
						{
							$data[$this->columns[$j]]	= $this->_reversestr( $this->datas[$j] );
						}
						array_push( $ret, $data );						
					}
				}
				else if( $this->_check_condition() )
				{
					$data	= array();
					for( $j = 0; $j < $this->columncnt; $j++ )
					{
						$data[$this->columns[$j]]	= $this->_reversestr( $this->datas[$j] );
					}
					array_push( $ret, $data );
				}
			}
			
			if( count( $ret ) )
			{
				$this->_sort_records( $ret, $order );
			}
		}
		
		if( $limit )	return array_slice( $ret, $lstart, $lnum );
		
		return $ret;
	}
	
	function count( $condition, $fpath = "" )
	{
		if( !$fpath )	$fpath = $this->fpath;
		$ret	= 0;
		if( $this->_read_header( $fpath, $maxid ) )
		{
			$file				= $this->_file_read( $fpath );
			$count				= count( $file );
			if( !strlen( $condition ) )	$condition = "1";
			$this->condition	= $condition;
			for( $i = 2; $i < $count; $i++ )
			{
				$this->datas	= explode( TEXTDB_SEP, trim( $file[$i] ) );
				if( $this->_check_condition() )
				{
					$ret++;
				}
			}
		}
		
		return $ret;
	}
	
	function _read_header( $fpath, &$maxid )
	{
		$ret	= FALSE;	
		if( $fp = fopen( $fpath, "r" ) )
		{
			flock( $fp, LOCK_SH );

			$line	= trim( fgets( $fp ) );
			if( $line )
			{
				$this->columns		= explode( ",", $line );
				$this->columncnt	= count( $this->columns );
				$line				= trim( fgets( $fp ) );
				if( $line )
				{
					$maxid	= $line;
					$ret	= TRUE;
				}
			}
			fclose( $fp );
		}
		
		return $ret;
	}
	
	function _strip_findby(&$condition)
	{
			if( preg_match( '/^\$([a-zA-Z\d_]+)==([\'"])(.*?)\2' . TEXTDB_FB . '$/', trim($condition), $tmparr) )
			{
				$find_key = $tmparr[1];
				$quote = $tmparr[2];
				$find_value = $tmparr[3];
				$condition = "\${$find_key}=={$quote}{$find_value}{$quote}";
				return true;
			}
			else
				return false;
	}
	
	function _check_condition()
	{
/*
		for( $this->i = 0; $this->i < $this->columncnt; $this->i++ )
		{
			$this->column	= $this->columns[$this->i];
			$this->data		= $this->datas[$this->i];
			$this->datas[$this->i]     = str_replace("'", "\'", $this->data);
			//$str = '$' . $this->column . " = '" . $this->data . "';";
			//eval( $str );
		}
*/
		extract($this->_array_combine($this->columns, $this->datas));
		
		$str	= '
		if( ' . $this->condition . ' )
		{
			$ret = TRUE;	
		}
		else
		{
			$ret = FALSE;
		}
		';
		eval( $str );

		return $ret;
	}
	
	function _initstr( $str )
	{
		$str	= str_replace( "\r", TEXTDB_CR, $str );
		$str	= str_replace( "\n", TEXTDB_LF, $str );
		return $str;
	}
	
	function _reversestr( $str )
	{
		$str	= str_replace( TEXTDB_CR, "\r", $str );
		$str	= str_replace( TEXTDB_LF, "\n", $str );
		return $str;
	}
	
	function _cmpfunc( $a, $b )
	{
	    if( $a[$this->cmpkey] == $b[$this->cmpkey] )
		{
	        return 0;
	    }
		$ret	= ( $a[$this->cmpkey] < $b[$this->cmpkey] ) ? -1 : 1;
		if( $this->cmpdesc )	$ret *= -1;
	    return $ret;
	}
	
	function _sort_records( &$records, $order )
	{
		switch( $order )
		{
		case 'RAND()':
			shuffle( $records );
			break;
		default:
			$order			= trim( $order );
			$pos			= strpos( $order, " " );
			$this->cmpdesc	= FALSE;
			if( $pos === FALSE )
			{
				$this->cmpkey	= $order;
			}
			else
			{
				$this->cmpkey	= substr( $order, 0, $pos );
				if( trim( strtolower( substr( $order, $pos ) ) ) == "desc" )
				{
					$this->cmpdesc	= TRUE;
				}
			}
			if( $this->cmpkey && array_key_exists( $this->cmpkey, $records[0] ) )
			{
				usort( $records, array( &$this, '_cmpfunc' ) );
			}
			break;
		}
	}
	
	function _array_combine($a1, $a2)
	{
		if( function_exists('array_combine'))
		{
			return array_combine($a1, $a2);
		}
		else
		{
			$a1 = array_values($a1);
			$a2 = array_values($a2);
			$c1 = count($a1);
			$c2 = count($a2);
	
			if ($c1 != $c2) {
				echo 'Error : not same count arr1, arr2';
				return false;
			}
			if ($c1 <= 0) {
				echo 'Error : invalid array';
				return false;
			}
			$output = array();
	
			for ($i = 0; $i < $c1; $i++) {
				$output[$a1[$i]] = $a2[$i];
			}
			
			return $output;
		}
	}
	
	function _parse_limit( $limit )
	{
		$limit	= trim( $limit );
		if( !$limit )	return null;
		
		if( strpos( $limit, ',' ) )
		{
			list( $start, $num )	= explode( ',', $limit );
			return array( intval( $start ), intval( $num ) );
		}
		
		return array( 0, intval( $limit ) );
	}
	
	function _file_read( $fpath ){
		
		$fp = fopen( $fpath, "r" );
		flock( $fp, LOCK_SH );
		
		$lines = file($fpath);
		
		fclose($fp);
		
		return $lines;	
	}
}
?>
