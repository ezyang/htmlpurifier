<?php

/**
 * Generates XML and HTML documents describing configuration.
 * @note PHP 5.2+ only!
 */

/*
TODO:
- make XML format richer
- extend XSLT transformation (see the corresponding XSLT file)
- allow generation of packaged docs that can be easily moved
- multipage documentation
- determine how to multilingualize
- add blurbs to ToC
*/

if (version_compare(PHP_VERSION, '5.2.0', '<')) exit('PHP 5.2.0 or greater required.');
error_reporting(E_ALL | E_STRICT);

echo 'Currently broken!';
exit;

// load dual-libraries
require_once '../extras/HTMLPurifierExtras.auto.php';
require_once '../library/HTMLPurifier.auto.php';

// setup HTML Purifier singleton
HTMLPurifier::getInstance(array(
    'AutoFormat.PurifierLinkify' => true
));

$schema = HTMLPurifier_ConfigSchema::instance();
$style = 'plain'; // use $_GET in the future
$configdoc = new ConfigDoc();
$output = $configdoc->generate($schema, $style);

if (!$output) {
    echo "Error in generating files\n";
    exit(1);
}

// write out
file_put_contents("$style.html", $output);

if (php_sapi_name() != 'cli') {
    // output (instant feedback if it's a browser)
    echo $output;
} else {
    echo 'Files generated successfully.';
}

