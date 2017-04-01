<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
class CDispatch extends CObject
{
	function dispatch( &$data )
	{
		$db			= new CDatabase();
		if( function_exists( "config_database" ) )
		{
			config_database( $db );
		}
	
		$sanitize	= new CSanitize();
		$validate	= new CValidate();
		if( function_exists( 'config_controller_class' ) )
		{
			$controllername	= config_controller_class();
			$controller		= new $controllername();
		}
		else
		{
			$controller	= new CController();
		}
		$controller->RequestHandle();
		$controller->SetDatabase( $db );
		$controller->SetSanitize( $sanitize );
		$controller->SetValidate( $validate );
		if( function_exists( "config_models" ) )
		{
			config_models( $controller );
		}
		if( function_exists( 'config_components' ) )
		{
			config_components( $controller );
		}
		if( !function_exists( 'is_session' ) || is_session() )
		{
			if( function_exists('secure_session_start') )
			{
				secure_session_start();
			}
			else
			{
				session_start();
			}
		}

		$this->_check_secure( $controller );

		if( function_exists( "config_controller" ) )
		{
			config_controller( $controller );
		}
		if( function_exists( "action" ) )
		{
			action( $controller );
		}
		if( function_exists( 'after_action' ) )
		{
			after_action( $controller );
		}
		
		$template	= $controller->GetTemplateFile();
		$viewfile	= $controller->GetViewFile();
		$variable	= $controller->GetVariable();
		$sqllog		= $controller->GetSqlLog();
		$is_debug	= $controller->GetDebug();
		
		if( function_exists( 'config_view_class' ) )
		{
			$viewname	= config_view_class();
			$view		= new $viewname();
		}
		else
		{
			$view		= new CView();
		}
		$view->SetFile( $template, $viewfile );
		$view->SetVariable( $variable );
		$view->SetSanitize( $sanitize );
		$view->SetController( $controller );
		$view->SetDebug( $is_debug );
		$view->SetSqlLog( $sqllog );
		$view->display();
		
		if( function_exists( 'after_render' ) )
		{
			after_render( $controller );
		}

		$data		= $variable;
		return $controller;
	}


	function _check_secure( $controller )
	{
		if( function_exists( "is_secure" ) )
		{
			if( is_secure( $controller ) )
			{
				if( function_exists( "check_secure" ) )
				{
					check_secure( $controller );
				}
			}
		}
	}
}
?>
