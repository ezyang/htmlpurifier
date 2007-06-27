<?php

set_include_path('../library/' . PATH_SEPARATOR . get_include_path() );

header('Content-type: text/html; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8" ?>';

function printb($bool) {
    echo '<strong>' . ($bool ? 'Pass' : 'Fail') . '</strong>';
}

function printEval($code) {
    echo '<pre>' . htmlspecialchars($code) . '</pre>';
    eval($code);
}

?><!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>HTML Purifier Function Include Smoketest</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<h1>HTML Purifier Function Include Smoketest</h1>

<p>Tests whether or not the includes are done properly and whether or
not the library is lazy loaded.</p>

<?php printEval("require_once 'HTMLPurifier.func.php';"); ?>

<p>HTMLPurifier class doesn't exist: <?php printb(!class_exists('HTMLPurifier')); ?></li></p>

<?php printEval("HTMLPurifier('foobar');"); ?>

<p>HTMLPurifier class exists: <?php printb(class_exists('HTMLPurifier')); ?></li></p>

</body>
</html>
