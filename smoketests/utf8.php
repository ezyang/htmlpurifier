<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>HTMLPurifier UTF-8 Smoketest</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<h1>HTMLPurifier UTF-8 Smoketest</h1>
<?php

set_include_path('../library' . PATH_SEPARATOR . get_include_path());
require_once 'HTMLPurifier.php';

$purifier = new HTMLPurifier();
$string = '
<ul>
    <li><b>Chinese</b> - 太極拳</li>
    <li><b>Russian</b> - ЊЎЖ</li>
    <li><b>Arabic</b> - لمنس</li>
</ul>
';

?>
<h2>Raw</h2>
<?php echo $string; ?>
<h2>Purified</h2>
<?php echo $purifier->purify($string); ?>
</body>
</html>