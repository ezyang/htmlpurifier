<?php

/**
 * Generates XML and HTML documents describing configuration.
 * @note PHP 5 only!
 */

/*
TODO:
- make XML format richer (see XMLSerializer_ConfigSchema)
- extend XSLT transformation (see the corresponding XSLT file)
- allow generation of packaged docs that can be easily moved
- multipage documentation
- determine how to multilingualize
- add blurbs to ToC
*/

if (version_compare('5', PHP_VERSION, '>')) exit('Requires PHP 5 or higher.');
error_reporting(E_ALL); // probably not possible to use E_STRICT

define('HTMLPURIFIER_SCHEMA_STRICT', true); // description data needs to be collected

// load dual-libraries
require_once '../library/HTMLPurifier.auto.php';
require_once 'library/ConfigDoc.auto.php';

$purifier = HTMLPurifier::getInstance(array(
    'AutoFormat.PurifierLinkify' => true
));

$schema = HTMLPurifier_ConfigSchema::instance();
$style = 'plain'; // use $_GET in the future
$configdoc = new ConfigDoc();
$output = $configdoc->generate($schema, $style);

// write out
file_put_contents("$style.html", $output);

if (php_sapi_name() != 'cli') {
    // output = instant feedback
    echo $output;
} else {
    echo 'Files generated successfully.';
}

