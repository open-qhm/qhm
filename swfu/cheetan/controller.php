<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
class CController extends CObject
{
	var	$template		= null;
	var $viewfile		= null;
	var $viewpath		= "ctp";
	var $viewfile_ext	= ".html";
	var $variables		= array();
	var	$db;
	var $sanitize;
	var $s;
	var	$validate;
	var	$v;
	//	Models Array
	var $m				= array();
	//	Components Array
	var $c				= array();
	var	$post			= array();
	var	$get			= array();
	var	$request		= array();
	var	$data			= array();
	var $debug			= false;
	
	
	function CController()
	{
	}
	
	
	function AddModel( $path, $name = "" )
	{
		$cname	= basename( $path, ".php" ); 
		$cname	= strtolower( $cname );
		
		if( !$name )	$name = $cname;
		$cname	= "C" . ucfirst( $name );
		if( !file_exists( $path ) )
		{
			return FALSE;
		}
		else
		{
			require_once( $path );
			$class = &new $cname();
//			if( !$class->table )	$class->table = $name;

			//edited by hokuken 2008 10/20
			$dir = dirname(__FILE__).'/../data/';
			$class->table = $dir.$name.".txt";
			
			$class->SetController( $this );
			$this->m[$name]	= &$class;
			if( empty( $this->{$name} ) )	$this->{$name} = &$this->m[$name];
		}
		return TRUE;
	}
	
	
	function AddComponent( $path, $cname = '', $name = '' )
	{
		if( !$cname )
		{
			$cname	= basename( $path, '.php' );
			$cname	= strtolower( $cname );
			if( !$name )	$name = $cname;
			$cname	= 'C' . ucfirst( $name );
		}
		else
		{
			$name	= basename( $path, '.php' );
			$name	= strtolower( $name );
		}
		if( !file_exists( $path ) )
		{
			print 'Component file $path is not exist.';
			return FALSE;
		}
		else
		{
			require_once( $path );
			$class = &new $cname();
			$this->c[$name]	= $class;
			if( empty( $this->{$name} ) )	$this->{$name} = &$this->c[$name];			
		}
		return TRUE;
	}
	
	
	function SetTemplateFile( $template )
	{
		$this->template	= $template;
	}
	
	
	function SetViewFile( $viewfile )
	{
		$this->viewfile	= $viewfile;
	}
	
	
	function SetViewPath( $viewpath )
	{
		$this->viewpath	= $viewpath;
	}
	
	
	function SetViewExt( $ext )
	{
		if( $ext{0} != '.' )	$ext = '.' . $ext;
		$this->viewfile_ext	= $ext;
	}
	
	
	function GetTemplateFile()
	{
		return $this->template;
	}
	
	
	function GetViewFile()
	{
		if( $this->viewfile )
		{
			return $this->viewfile;
		}
		
		$pos	= strpos( SCRIPTFILE, "." );
		if( $pos === FALSE )	return SCRIPTFILE . $this->viewfile_ext;
		if( !$pos )				return $this->viewfile_ext;
		
		list( $title, $ext )	= explode( ".", SCRIPTFILE );
		if( $this->viewpath )
		{
			$path	= $this->viewpath;
			switch( $this->viewpath[strlen($this->viewpath)-1] )
			{
			case '/';
			case "\\";
				$path	= $this->viewpath . $title . $this->viewfile_ext;
				break;
			default:
				$path	= $this->viewpath . DIRECTORY_SEPARATOR . $title . $this->viewfile_ext;
				break;
			}
		}
		else
		{
			$path	= $title . $this->viewfile_ext;
		}
		return $path;
	}
	
	
	function set( $name, $value )
	{
		$this->variables[$name]	= $value;
	}
	
	
	function setarray( $datas )
	{
		foreach( $datas as $key => $data )
		{
			$this->set( $key, $data );
		}
	}


	function redirect( $url, $is301 = FALSE )
	{
		if( $is301 )
		{
			header( "HTTP/1.1 301 Moved Permanently" );
		}
		header( "Location: " . $url );
		exit();
	}
	
	function flash($url)
	{
		$message = "このページへアクセスするには、認証が必要です<br />ここをクリックして、認証を行って下さい";

		header("Content-Type: text/html; charset=utf-8");
		$body = <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.or
g/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>QHM Commu Redirect</title>
<meta http-equiv="Refresh" content="0;url={$url}"/>
<style><!--
P { text-align:center; font:bold 1.1em sans-serif }
A { color:#444; text-decoration:none }
A:HOVER { text-decoration: underline; color:#44E }
--></style>
</head>
<body>
<p><a href="{$url}">{$message}</a></p>
</body>
</html>
EOD;

		echo $body;
		exit;

	}
	
	function RequestHandle()
	{
		if( count( $_GET ) )		$this->get = $_GET;
		if( count( $_POST ) )		$this->post = $_POST;
		if( count( $_REQUEST ) )	$this->request = $_REQUEST;
		$this->ModelItemHandle( $_GET );
		$this->ModelItemHandle( $_POST );
	}
	
	
	function ModelItemHandle( $requests )
	{
		foreach( $requests as $key => $request )
		{
			if( strpos( $key, "/" ) !== FALSE )
			{
				list( $model, $element )		= explode( "/", $key );
				$this->data[$model][$element]	= $request;
			}
		}
	}
	
	
	function GetVariable()
	{
		return $this->variables;
	}
	
	
	function &GetDatabase()
	{
		return $this->db;
	}
	
	
	function SetDatabase( &$db )
	{
		$this->db	= $db;
	}


	function SetSanitize( &$sanitize )
	{
		$this->sanitize	= $sanitize;
		$this->s		= &$this->sanitize;
	}


	function SetValidate( &$validate )
	{
		$this->validate	= $validate;
		$this->v		= &$this->validate;
	}
	
	
	function SetDebug( $debug )
	{
		$this->debug	= $debug;
	}
	
	
	function GetDebug()
	{
		return $this->debug;
	}
	
	
	function GetSqlLog()
	{
		return $this->db->GetSqlLog();
	}
}
?>
