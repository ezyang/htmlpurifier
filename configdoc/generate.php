<?php

/**
 * Generates XML and HTML documents describing configuration.
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
error_reporting(E_ALL);


// ---------------------------------------------------------------------------
// Include HTML Purifier library

require_once '../library/HTMLPurifier.auto.php';
require_once 'library/ConfigDoc.auto.php';

// ---------------------------------------------------------------------------
// Setup convenience functions

function appendHTMLDiv($document, $node, $html) {
    global $purifier;
    $html = $purifier->purify($html);
    $dom_html = $document->createDocumentFragment();
    $dom_html->appendXML($html);
    
    $dom_div = $document->createElement('div');
    $dom_div->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
    $dom_div->appendChild($dom_html);
    
    $node->appendChild($dom_div);
}


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

// load the stylesheet
$xsl_stylesheet_name = 'plain';
$xsl_stylesheet = "styles/$xsl_stylesheet_name.xsl";
$xsl_dom_stylesheet = new DOMDocument();
$xsl_dom_stylesheet->load($xsl_stylesheet);

// setup the XSLT processor
$xsl_processor = new XSLTProcessor();

// perform the transformation
$xsl_processor->importStylesheet($xsl_dom_stylesheet);
$html_output = $xsl_processor->transformToXML($dom_document);

// some slight fudges to preserve backwards compatibility
$html_output = str_replace('/>', ' />', $html_output); // <br /> not <br/>
$html_output = str_replace(' xmlns=""', '', $html_output); // rm unnecessary xmlns

if (class_exists('Tidy')) {
    // cleanup output
    $config = array(
        'indent'        => true,
        'output-xhtml'  => true,
        'wrap'          => 80
    );
    $tidy = new Tidy;
    $tidy->parseString($html_output, $config, 'utf8');
    $tidy->cleanRepair();
    $html_output = (string) $tidy;
}

// hack
if (!defined('HTMLPURIFIER_CUSTOM_SCHEMA')) {
    // write it to a file (todo: parse into seperate pages)
    file_put_contents("$xsl_stylesheet_name.html", $html_output);
} else {
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