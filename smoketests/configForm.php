<?php

require_once 'common.php';

if (isset($_GET['doc'])) {
    
    if (
        file_exists('testSchema.html') &&
        filemtime('testSchema.php') < filemtime('testSchema.html') &&
        !isset($_GET['purge'])
    ) {
        echo file_get_contents('testSchema.html');
        exit;
    }
    
    if (version_compare('5', PHP_VERSION, '>')) exit('Requires PHP 5 or higher.');
    
    // setup schema for parsing
    require_once 'testSchema.php';
    $new_schema = $custom_schema; // dereference the reference
    HTMLPurifier_ConfigSchema::instance($old); // restore old version
    
    // setup ConfigDoc environment
    require_once '../configdoc/library/ConfigDoc.auto.php';
    
    // perform the ConfigDoc generation
    $configdoc = new ConfigDoc();
    $html = $configdoc->generate($new_schema, 'plain', array(
        'css' => '../configdoc/styles/plain.css',
        'title' => 'Sample Configuration Documentation'
    ));
    $configdoc->cleanup();
    
    file_put_contents('testSchema.html', $html);
    echo $html;
    
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
$config = HTMLPurifier_Config::loadArrayFromForm($_GET, 'config');
$printer = new HTMLPurifier_Printer_ConfigForm('config', '?doc#%s');
echo $printer->render($config);

?>
</form>
<pre>
<?php
echo htmlspecialchars(print_r($config->getAll(), true));
?>
</pre>
</body>
</html>
