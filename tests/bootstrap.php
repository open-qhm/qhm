<?php
/**
 * PHPUnit bootstrap file
 */

define('BASE_PATH', dirname(__DIR__));

// Define constants required by lib/func.php
define('SOURCE_ENCODING', 'UTF-8');
define('LANG', 'ja');

// Load the actual source file
require_once BASE_PATH . '/lib/func.php';