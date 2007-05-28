<?php

/**
 * Generates XML and HTML documents describing configuration.
 * @note PHP 5 only!
 */

/*
TODO:
- make XML format richer (see below)
- extend XSLT transformation (see the corresponding XSLT file)
- allow generation of packaged docs that can be easily moved
- multipage documentation
- determine how to multilingualize
- factor out code into classes
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

// hack
if (defined('HTMLPURIFIER_CUSTOM_SCHEMA')) {
    // included from somewhere else
    $var = HTMLPURIFIER_CUSTOM_SCHEMA;
    $schema = $$var;
    chdir(dirname(__FILE__));
} else {
    $schema = HTMLPurifier_ConfigSchema::instance();
}
$purifier = new HTMLPurifier();


// ---------------------------------------------------------------------------
// Generate types.xml, a document describing the constraint "type"

$types_serializer = new ConfigDoc_XMLSerializer_Types();
$types_document = $types_serializer->serialize($schema);
$types_document->save('types.xml');


// ---------------------------------------------------------------------------
// Generate configdoc.xml, a document documenting configuration directives

$schema_serializer = new ConfigDoc_XMLSerializer_ConfigSchema();
$dom_document = $schema_serializer->serialize($schema);
$dom_document->save('configdoc.xml');


// ---------------------------------------------------------------------------
// Generate final output using XSLT

// determine stylesheet name
$xsl_stylesheet_name = 'plain';
$xsl_stylesheet = "styles/$xsl_stylesheet_name.xsl";

// transform
$xslt_processor = new ConfigDoc_HTMLXSLTProcessor();
$xslt_processor->importStylesheet($xsl_stylesheet);
$html_output = $xslt_processor->transformToHTML($dom_document);

// hack
if (!defined('HTMLPURIFIER_CUSTOM_SCHEMA')) {
    // write it to a file (todo: parse into seperate pages)
    file_put_contents("$xsl_stylesheet_name.html", $html_output);
} elseif (defined('HTMLPURIFIER_SCRIPT_LOCATION')) {
    $html_output = str_replace('styles/plain.css', HTMLPURIFIER_SCRIPT_LOCATION . 'styles/plain.css', $html_output);
}

// ---------------------------------------------------------------------------
// Output for instant feedback

if (php_sapi_name() != 'cli') {
    echo $html_output;
} else {
    echo 'Files generated successfully.';
}

?>