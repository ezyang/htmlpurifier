<?php

require_once 'common.php';

echo '<?xml version="1.0" encoding="UTF-8" ?>';
?><!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <title>HTML Purifier Preserve YouTube Smoketest</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<h1>HTML Purifier Preserve YouTube Smoketest</h1>
<?php

$string = '<object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/JzqumbhfxRo"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/JzqumbhfxRo" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed></object>';

$regular_purifier = new HTMLPurifier();

$youtube_purifier = new HTMLPurifier();
require_once 'HTMLPurifier/Filter/YouTube.php';
$youtube_purifier->addFilter(new HTMLPurifier_Filter_YouTube());

?>
<h2>Unpurified</h2>
<p><a href="?break">Click here to see the unpurified version (breaks validation).</a></p>
<div><?php
if (isset($_GET['break'])) echo $string;
?></div>

<h2>Without YouTube exception</h2>
<div><?php
echo $regular_purifier->purify($string);
?></div>

<h2>With YouTube exception</h2>
<div><?php
echo $youtube_purifier->purify($string);
?></div>

</body>
</html>
