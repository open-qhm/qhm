<?php
/**
 * PHPUnit bootstrap file
 */

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('LIB_DIR', BASE_PATH . '/lib/');
define('PLUGIN_DIR', BASE_PATH . '/plugin/');
define('DATA_DIR', BASE_PATH . '/tests/data/wiki/');
define('SOURCE_ENCODING', 'UTF-8');
define('LANG', 'ja');

// Global variables required by QHM functions (defined in lib/init.php)
// $BracketName: Valid page name pattern for is_pagename() validation
$BracketName = '(?!\s):?[^\r\n\t\f\[\]<>#&":]+:?(?<!\s)';
// $InterWikiName: InterWiki link pattern (e.g., [[Wikipedia:Tokyo]]) for is_interwiki() detection
$InterWikiName = '(\[\[)?((?:(?!\s|:|\]\]).)+):(.+)(?(1)\]\])';