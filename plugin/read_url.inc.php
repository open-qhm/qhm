<?php
function plugin_read_url_convert()
{
	global $vars;
	$qm = get_qm();
	$qt = get_qt();
	
	//---- キャッシュのための処理を登録 -----
	if($qt->create_cache) {
		$args = func_get_args();
		return $qt->get_dynamic_plugin_mark(__FUNCTION__, $args);
	}
	//------------------------------------	
     
	$page = $vars['page'];

	$args = func_get_args();
	$num = func_num_args();
	
	if($num > 0){
		$url   = $args[0];
	}
	
	$from_en = "auto";
	if(isset($args[1]) && $args[1]!=''){
		$from_en = h($args[1]);
	}
	
	if(is_url($url)){
    	$_data = null;
		if( $_http = fopen( $url, "r" ) ) {
			while( !feof( $_http ) ) {
				$_data .= fgets( $_http, 1024 );
			}
			fclose( $_http );
		}
		return( mb_convert_encoding($_data,SOURCE_ENCODING, $from_en) );
	}
	else{
		return "<p>". $qm->replace('plg_read_url.err_invalid_url', $url). "</p>";
	}
}

function plugin_read_url_inline()
{
	$qt = get_qt();
	//---- キャッシュのための処理を登録 -----
	if ($qt->create_cache)
	{
		$args = func_get_args();
		return $qt->get_dynamic_plugin_mark(__FUNCTION__, $args);
	}
	//------------------------------------

	global $vars;
	$qm = get_qm();
	
	$page = $vars['page'];

	$args = func_get_args();
	$num = func_num_args();
	
	if($num > 0){
		$url   = $args[0];
	}
	$from_en = "auto";
	if(isset($args[1]) && $args[1]!=''){
		$from_en = h($args[1]);
	}
	
	if(is_url($url)){
    	$_data = null;
		if( $_http = fopen( $url, "r" ) ) {
			while( !feof( $_http ) ) {
				$_data .= fgets( $_http, 1024 );
			}
			fclose( $_http );
		}
		return( mb_convert_encoding($_data,SOURCE_ENCODING, $from_en) );
	}
	else{
		return "<p>". $qm->replace('plg_read_url.err_invalid_url', $url). "</p>";
	}
}


?>
