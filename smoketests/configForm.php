<?php

require_once 'common.php';

if (isset($_GET['doc'])) {
    require_once 'testSchema.php';
    $new_schema = $custom_schema;
    HTMLPurifier_ConfigSchema::instance($old);
    define('HTMLPURIFIER_CUSTOM_SCHEMA', 'new_schema');
    define('HTMLPURIFIER_SCRIPT_LOCATION', '../configdoc/');
    require_once '../configdoc/generate.php';
    exit;
}

?><!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <title>HTML Purifier Config Form Smoketest</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="../library/HTMLPurifier/Printer/ConfigForm.css" type="text/css" />
    <script defer="defer" type="text/javascript" src="../library/HTMLPurifier/Printer/ConfigForm.js"></script>
</head>
<body>
<h1>HTML Purifier Config Form Smoketest</h1>
<p>This file outputs the configuration form for every single type
of directive possible.</p>
<form id="htmlpurifier-config" name="htmlpurifier-config" method="get" action=""
style="float:right;">
<?php

require_once 'HTMLPurifier/Printer/ConfigForm.php';

// fictional set, attempts to cover every possible data-type
// see source at ConfigTest.php
require_once 'testSchema.php';

// cleanup ( this should be rolled into Config )
$get = isset($_GET) ? $_GET : array();
$mq = get_magic_quotes_gpc();
foreach ($_GET as $key => $value) {
    if (!strncmp($key, 'Null_', 5) && !empty($value)) {
        unset($get[substr($key, 5)]);
        unset($get[$key]);
    }
    if ($mq) $get[$key] = stripslashes($value);
}
$config = @HTMLPurifier_Config::create($get);

$printer = new HTMLPurifier_Printer_ConfigForm('?doc');
echo $printer->render($config);

?>
<div style="text-align:right;">
    <input type="submit" value="Submit" />
    [<a href="?">Reset</a>]
</div>
</form>
<pre>
<?php
print_r($config->getAll());
?>
</pre>
</body>
</html>