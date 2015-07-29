<?php
// Send header
header('Server: X-Powered-By: PHP');
header('Content-Type: text/css');

$onefg   = isset($_GET['onefg'])   ? $_GET['onefg']    : 'ff';
$onebg   = isset($_GET['onebg'])   ? $_GET['onebg']    : 'bb';

//foreground color & font settings
$font = substr($onefg, -2);
$color = urlencode(substr($onefg, 0, -2));
echo '@import url("fg.css.php?font=' .$font. '&color=' .$color. '");';

//background, plugin, boxes css
echo '@import url("plugin.css");';
echo '@import url("boxes.css");';

?>