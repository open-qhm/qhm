<?php
error_reporting(0);
$ini = './init.php';
$sversion = '1.4.7';
$revision = 467;
if (file_exists($ini)) {
	$inistr = file_get_contents($ini);
	if (preg_match("/S_VERSION', '(.*?)'/", $inistr, $ms))
	{
		$sversion = $ms[1];
	}
    if (preg_match("/QHM_REVISION', '(.*?)'/", $inistr, $ms))
    {
        $revision = $ms[1];
    }
}
if ( ! defined('S_VERSION'))
{
	define('S_VERSION', $sversion);
}

require_once('func.php');
require_once('proxy.php');

$_POST   = input_filter($_POST);
$script = $_POST['script'];

if ( ! isset($_COOKIE['QHM_VERSION']))
{
	$get_version_url = 'https://ensmall.net/hkn_p/qhmpro/get_version.php?rev=' . $revision;
	$res = http_request($get_version_url, $method = 'POST');

	if ($res['data'] !==  FALSE)
	{
		$result = unserialize($res['data']);

		$vals = parse_url($script);
		$domain = $vals['host'];
		$dir = str_replace('\\', '', dirname( $vals['path'] ));
		$ckpath = ($dir=='/') ? '/' : $dir.'/';
		setcookie('QHM_VERSION', $result['ver'], time()+3600*24*3, $ckpath, $domain);
	}
}

echo TRUE;

?>