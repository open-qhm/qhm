<?php
# NOTE:ASCII:0x0A:604(or 644)
#####################################################################
#
#	PukiWiki - Yet another WikiWikiWeb clone.
#
#	$Id: atom.inc.php,	v 1.1		2007/05/09 00:09:00 fix Exp $
#	License: GPL v2
#
#	Based on
#		rss10pp.inc.php,	v 1.11	2004/11/10 18:44:54 Logue Exp $
#
#	Atom 1.0 of RecentChanges.
#
#	PHP				>= 5.1.1
#	PukiWiki	>= 1.4.7
#	Apache		>= 1.3.37
#	AnWeb			>= 1.42p
#	MSIE			>= 7.0
#	Firefox		>= 2.0.0.3
#
#####################################################################
#
#	http query
#
#		?cmd=atom[&top=]
#		?plugin=atom[&top=]
#
#####################################################################
#
#	<author><name>
define ( ATR	, SERVER_NAME ) ;
#	max show <entry>
define ( MAX	, 16		) ;	# >= 1
#	max <content> length 
define ( CTT	, 256		) ;	# 0:off
#	delete \t and \n and shorten blanks
define ( DEL	, 0			) ;	# 0:off
#
#####################################################################
function plugin_atom_action ()
{
	global $modifier, $vars;
	$version = isset($vars['ver']) ? $vars['ver'] : '1.0';

	# http header
	pkwk_common_headers () ;
	header ( 'Content-Type: text/xml; charset=UTF-8' ) ;

	# cash check
	$atm	= CACHE_DIR . crc32 ( $_GET[top] ) . ( $version == '1.0' ? '_atom1.0.xml' : '_atom0.3.xml' ) ;
	$dat	= CACHE_DIR . 'recent.dat' ;
	$mod	= filemtime ( $atm ) ;

	# hit!
	if ( $mod > filemtime ( $dat ) )
	{
		header ( 'Last-Modified: ' . gmdate ( 'D, d M Y H:i:s T' , $mod ) ) ;
		readfile ( $atm ) ;
		exit ;
	}

	# parse atom
	global $script , $page_title ;
	$hst	= SERVER_NAME ;
	$pth	= SCRIPT_NAME ;
	( $_GET[top] ) ? $top = $_GET[top] . '/' : 0 ;

	$latest = 0;

	# <entry>
	foreach ( file ( $dat ) as $_ )
	{
		# 0:mtime 1:pagename
		$_		= explode ( "\t" , $_ ) ;

		# fix in path?
		if ( $top && ( strpos ( $_[1] , $top ) !== 0 ) )
			continue ;

		# init - <entry>
		$_[1]	= rtrim ( $_[1] ) ;
		$title  = get_page_title($_[1]);
		$pge	= rawurlencode ( $_[1] ) ;
		# $id		= hash ( crc32 , $script . $pge ) ;
		$id		= crc32 ( $script . $pge ) ;
		if($latest < $_[0]) $latest = $_[0];
		$day	= get_date ( 'Y-m-d' , $_[0] ) ;
		# $mtm	= get_date ( DATE_ATOM , $_[0] ) ;	# PHP >= 5.1.1
		$tzP	= get_date ( 'O' , $_[0] ) ;
		$tzP	= substr ( $tzP , 0, 3 ) . ':' . substr ( $tzP , 3, 2 ) ;
		$mtm	= get_date ( 'Y-m-d\TH:i:s' , $_[0] ) . $tzP ;

		# <content>?
		if ( CTT ) {
			$ctt	= '<![CDATA[' . mb_strimwidth ( strip_htmltag ( convert_html ( get_source ( $_[1] ) ) ) , 0 , CTT , '...' ) . ']]>' ;
			$ctt	= $version == '1.0' ? '<content type="html">' . $ctt . '</content>' :
				'<content type="text/html" mode="escaped">' . $ctt . '</content>' ;
		}
		# <entry> array
		if ( $version == '1.0' )
		{
			$ent[$num++] = <<<_E
  <entry>
    <title type="text">$title</title>
    <link rel="alternate" href="$script?$pge"/>
    <id>tag:$hst,$day:$id</id>
    <updated>$mtm</updated>
    $ctt
  </entry>
_E;
		}
		else
		{
			$ent[$num++] = <<<_E
  <entry>
    <title type="text">$title</title>
    <link rel="alternate" href="$script?$pge"/>
    <id>tag:$hst,$day:$id</id>
    <modified>$mtm</modified>
    $ctt
  </entry>
_E;
		}
		if ( $num == MAX ) break ;
	}

	# <entry> buffer
	$ent = implode ( "\n" , $ent ) ;

	# init - output
	( $_GET[cmd] )
		? $slf	= "$script?cmd=$_GET[cmd]"
		: $slf	= "$script?plugin=$_GET[plugin]" ;
	( $_GET[top] ) ? $slf .= '&amp;top=' . rawurlencode ( $_GET[top] ) : 0 ;
	( $version == '1.0' )
		? 0
		: $slf .= '&amp;ver=0.3' ;
	( $_GET[top] )
		? $alt	= "$script?" . rawurlencode ( $_GET[top] )
		: $alt	= "$script" ;
	$atr			= $modifier ;
	$ctm			= date ( 'Y' , filectime ( $_SERVER[SCRIPT_FILENAME] ) ) ;
	# $mtm			= get_date ( DATE_ATOM ) ;
	$tzP			= get_date ( 'O' , $latest ) ;
	$tzP			= substr ( $tzP , 0, 3 ) . ':' . substr ( $tzP , 3, 2 ) ;
	$mtm			= get_date ( 'Y-m-d\TH:i:s' , $latest ) . $tzP ;

	# output buffer
	$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" ;
	$xml .= $version == '1.0' ? '<feed xmlns="http://www.w3.org/2005/Atom">' . "\n" :
		'<feed version="0.3" xmlns="http://purl.org/atom/ns#">' . "\n" ;
	if ( $version == '1.0' )
	{
		$xml .= <<<_E
  <title>$page_title</title>
  <updated>$mtm</updated>
  <id>tag:$hst,$ctm:$pth</id>
  <author><name>$atr</name></author>
  <link rel="self" type="application/atom+xml" href="$slf"/>
  <link rel="alternate" href="$alt"/>
$ent
</feed>
_E;
	}
	else
	{
		$xml .= <<<_E
  <title>$page_title</title>
  <modified>$mtm</modified>
  <id>tag:$hst,$ctm:$pth</id>
  <author><name>$atr</name></author>
  <link rel="alternate" href="$alt"/>
$ent
</feed>
_E;
	}
	# convert encoding
	$xml = mb_convert_encoding ( $xml , 'UTF-8' , SOURCE_ENCODING ) ;

	# making worst?
	if ( DEL )
	{
		$xml = preg_replace ( "/[\r\n\t]+/" , '' , $xml ) ;
		$xml = preg_replace ( "/[ ]+/" , ' ' , $xml ) ;
	}

	# cash?
	if ( $ent )
	{
		# file_put_contents ( $atm , $xml , 2 ) ;
		$cachehandle = fopen( $atm, 'w' ) ;
		flock( $cachehandle, 2 ) ;
		fwrite( $cachehandle, $xml ) ;
		fclose( $cachehandle ) ;
	}

	# output
	echo $xml ;

	# end
	exit ;
}
#####################################################################
#
#	ChangeLog
#		v 1.1
#			Content-Type: text/xml; charset=UTF-8
#			<content type="html">
#			<id>tag:id</id> md5 -> crc32
#		v 1.0
#			release
#
#####################################################################
?>
