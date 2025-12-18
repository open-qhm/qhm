<?php
/**
 * PHPUnit bootstrap file
 */

define('BASE_PATH', dirname(__DIR__));

// Define constants required by lib/func.php
define('SOURCE_ENCODING', 'UTF-8');
define('LANG', 'ja');

// Define constants required by lib/qhm_template.php
define('CACHE_DIR', BASE_PATH . '/cache/');
define('LIB_DIR', BASE_PATH . '/lib/');
define('PLUGIN_DIR', BASE_PATH . '/plugin/');
define('CONTENT_CHARSET', 'UTF-8');
define('TEMPLATE_ENCODE', 'UTF-8');

// Load the actual source files
require_once BASE_PATH . '/lib/func.php';
require_once BASE_PATH . '/lib/qhm_template.php';