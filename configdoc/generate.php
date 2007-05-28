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
*/

// there are several hacks for the configForm.php smoketest
// - load copies (override default schema)
// - file/line server information hack
// - post-processing, base-name change hack

// ---------------------------------------------------------------------------
// Check and configure environment

if (version_compare('5', PHP_VERSION, '>')) exit('Requires PHP 5 or higher.');
error_reporting(E_ALL); // probably not possible to use E_STRICT


// ---------------------------------------------------------------------------
// Include HTML Purifier library

require_once '../library/HTMLPurifier.auto.php';
require_once 'library/ConfigDoc.auto.php';


// ---------------------------------------------------------------------------
// Load copies of HTMLPurifier_ConfigDef and HTMLPurifier

$schema = HTMLPurifier_ConfigSchema::instance();

// ---------------------------------------------------------------------------
// Generate final output using XSLT

// determine stylesheet name
$xsl_stylesheet_name = 'plain'; // use $_GET in the future
$configdoc = new ConfigDoc();
$html_output = $configdoc->generate($schema, $xsl_stylesheet_name);

file_put_contents("$xsl_stylesheet_name.html", $html_output);

// ---------------------------------------------------------------------------
// Output for instant feedback

if (php_sapi_name() != 'cli') {
    echo $html_output;
} else {
    echo 'Files generated successfully.';
}

?>