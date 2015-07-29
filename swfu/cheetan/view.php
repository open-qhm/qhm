<?php
/*-----------------------------------------------------------------------------
cheetan is licensed under the MIT license.
copyright (c) 2006 cheetan all right reserved.
http://php.cheetan.net/
-----------------------------------------------------------------------------*/
class CView extends CObject
{
	var	$template;
	var	$viewfile;
	var $variables;
	var $sanitize;
	var $controller;
	var $debug = false;
	
	
	function SetFile( $template, $viewfile )
	{
		$this->template		= $template;
		$this->viewfile		= $viewfile;
	}
	
	
	function SetVariable( &$variable )
	{
		$this->variables		= $variable;
	}


	function SetSanitize( &$sanitize )
	{
		$this->sanitize			= $sanitize;
	}
	
	
	function SetController( &$controller )
	{
		$this->controller	= $controller;
	}
	
	
	function SetSqlLog( $sqllog )
	{
		if( $this->debug )
		{
			$log	= '<table class="cheetan_sql_log">'
					. '<tr>'
					. '<th width="60%">SQL</th>'
					. '<th width="10%">ERROR</th>'
					. '<th width="10%">ROWS</th>'
					. '<th width="10%">TIME</th>'
					. '</tr>'
					;
			foreach( $sqllog as $name => $rows )
			{
				$log	.= '<tr>'
						. '<td colspan="4"><b>' . htmlspecialchars( $name ) . '</b></td>'
						. '</tr>'
						;
				foreach( $rows as $i => $row )
				{
					$log	.= '<tr>'
							. '<td>' . htmlspecialchars( $row['query'] ) . '</td>'
							. '<td>' . htmlspecialchars( $row['error'] ) . '</td>'
							. '<td>' . $row['affected_rows'] . '</td>'
							. '<td>' . sprintf( '%.5f', $row['query_time'] ) . '</td>'
							. '</tr>'
							;
				}
			}
			$log	.= '</table>';
			$this->variables['cheetan_sql_log'] = $log;
		}
	}
	
	
	function SetDebug( $debug )
	{
		$this->debug	= $debug;
	}
	
	
	function display()
	{
		header('Content-Type: text/html; charset=UTF-8');
		if( $this->template )
		{
			$this->_display_template();
		}
		else
		{
			$this->content();
		}
	}
	
	
	function content()
	{
		if( file_exists( $this->viewfile ) )
		{
			$data		= $this->variables;
			$sanitize	= $this->sanitize;
			$s			= $this->sanitize;
			$controller	= $this->controller;
			$c			= $this->controller;
			extract($this->variables, EXTR_SKIP);
			require_once( $this->viewfile );
		}
	}
	
	
	function _display_template()
	{
		if( file_exists( $this->template ) )
		{
			$data		= $this->variables;
			$sanitize	= $this->sanitize;
			$s			= $this->sanitize;
			$controller	= $this->controller;
			$c			= $this->controller;
			extract($this->variables, EXTR_SKIP);
			require_once( $this->template );
		}
		else
		{
			print "Template '$this->template' is not exist.";
		}
	}
	
	
}
?>
