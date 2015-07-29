<?php
# Output file contents with Content-Type
# This file must be in lib dir

# Function definition
function halt_403()
{
    header('HTTP/1.1 403 Forbidden');
    exit;
}

function halt_404()
{
    header('HTTP/1.1 404 Not Found');
    exit;
}

function halt_304()
{
    header('HTTP/1.1 304 Not Modified');
    exit;
}

# Resolve related path
$basepath = dirname(dirname(__FILE__));
$filepath = $basepath . '/' . trim($_GET['path']);

if (is_dir($filepath))
{
    halt_403();
}

# Disallow refer above path: ../path/to/file
# Allow path contains alphanumerics and dots, slashes, underscores, hyphens
# Allow path extensions of svg

$ok_patterns = array(
    '/[a-z0-9.\/_-]+/i'
);
$ng_patterns = array(
    '/\.\./', # above path
);

$ok_exts = array(
    #'mp4' => 'video/mp4',
    #'webm' => 'video/webm',
    #'ogv' => 'video/ogg',
    'svg' => 'image/svg+xml'
);

define('WEEK_TIME', 86400 * 7);

# Check OK patterns
foreach ($ok_patterns as $pattern)
{
    if ( ! preg_match($pattern, $filepath))
    {
        halt_403();
    }
}


# Check NG patterns
foreach ($ng_patterns as $pattern)
{
    if (preg_match($pattern, $filepath))
    {
        halt_403();
    }
}


# Check extension
$ok = false;
$mimetype = '';
foreach ($ok_exts as $_ext => $_mimetype)
{
    $pattern = '/\.'.$_ext.'$/i';
    if (preg_match($pattern, $filepath))
    {
        $ok = true;
        $mimetype = $_mimetype;
        break;
    }
}

if ( ! $ok)
{
    halt_403();
}

if ( ! file_exists($filepath))
{
    halt_404();
}

# Cache control
$timestamp = filemtime($filepath);
$tsstring = gmdate('D, d M Y H:i:s ', $timestamp) . 'GMT';
$expire_string = gmdate('D, d M Y H:i:s ', time() + WEEK_TIME) . 'GMT';
$etag = md5($filepath . ':' . $timestamp);

$if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ?
    $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
$if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ?
    trim(trim($_SERVER['HTTP_IF_NONE_MATCH']), '"') : false;

if ((($if_none_match && $if_none_match == $etag) || (!$if_none_match)) &&
    ($if_modified_since && $if_modified_since == $tsstring))
{
    halt_304();
}

header("Cache-Control: max-age=".WEEK_TIME.", must-revalidate");
header("Expires: $expire_string");
header("Last-Modified: $tsstring");
header("ETag: \"{$etag}\"");

# Output file contents
$fp = fopen($filepath, 'rb');

header('Content-Type: ' . $mimetype);
header('Content-Length: ' . filesize($filepath));

fpassthru($fp);
exit;
