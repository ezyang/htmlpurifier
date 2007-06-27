<?php

// this file is encoded in UTF-8, please don't let your editor mangle it

require_once 'common.php';

echo '<?xml version="1.0" encoding="UTF-8" ?>';
?><!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <title>HTML Purifier UTF-8 Smoketest</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<h1>HTML Purifier UTF-8 Smoketest</h1>
<?php

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
<h2>Analysis</h2>
<p>The content in <strong>Raw</strong> should be equivalent to the content
in <strong>Purified</strong>.  If <strong>Purified</strong> is mangled, there
is likely trouble a-brewing in the library. If
both are mangled, check to see that this file was not corrupted.</p>
</body>
</html>
