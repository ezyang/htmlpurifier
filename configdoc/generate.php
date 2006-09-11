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
- generate string XML file for types
- determine how to multilingualize
*/


// ---------------------------------------------------------------------------
// Check and configure environment

if (version_compare('5', PHP_VERSION, '>')) exit('Requires PHP 5 or higher.');
error_reporting(E_ALL);


// ---------------------------------------------------------------------------
// Include HTML Purifier library

set_include_path('../library' . PATH_SEPARATOR . get_include_path());
require_once 'HTMLPurifier.php';


// ---------------------------------------------------------------------------
// Load copies of HTMLPurifier_ConfigDef and HTMLPurifier

$definition = HTMLPurifier_ConfigDef::instance();
$purifier = new HTMLPurifier();


// ---------------------------------------------------------------------------
// Generate types.xml, a document describing the constraint "type"

$types_document = new DOMDocument('1.0', 'UTF-8');
$types_root = $types_document->createElement('types');
$types_document->appendChild($types_root);
$types_document->formatOutput = true;
foreach ($definition->types as $name => $expanded_name) {
    $types_type = $types_document->createElement('type', $expanded_name);
    $types_type->setAttribute('id', $name);
    $types_root->appendChild($types_type);
}
$types_document->save('types.xml');


// ---------------------------------------------------------------------------
// Generate configdoc.xml, a document documenting configuration directives

$dom_document = new DOMDocument('1.0', 'UTF-8');
$dom_root = $dom_document->createElement('configdoc');
$dom_document->appendChild($dom_root);
$dom_document->formatOutput = true;

// add the name of the application
$dom_root->appendChild($dom_document->createElement('title', 'HTML Purifier'));

/*
TODO for XML format:
- namespace descriptions
- enumerated values
- default values
- create a definition (DTD or other) once interface stabilizes
*/

foreach($definition->info as $namespace_name => $namespace_info) {
    
    $dom_namespace = $dom_document->createElement('namespace');
    $dom_root->appendChild($dom_namespace);
    
    $dom_namespace->setAttribute('id', $namespace_name);
    $dom_namespace->appendChild(
        $dom_document->createElement('name', $namespace_name)
    );
    
    foreach ($namespace_info as $name => $info) {
        
        $dom_directive = $dom_document->createElement('directive');
        $dom_namespace->appendChild($dom_directive);
        
        $dom_directive->setAttribute('id', $namespace_name . '.' . $name);
        $dom_directive->appendChild(
            $dom_document->createElement('name', $name)
        );
        
        $dom_constraints = $dom_document->createElement('constraints');
        $dom_directive->appendChild($dom_constraints);
        $dom_constraints->appendChild(
            $dom_document->createElement('type', $info->type)
        );
        
        $dom_descriptions = $dom_document->createElement('descriptions');
        $dom_directive->appendChild($dom_descriptions);
        
        foreach ($info->descriptions as $file => $file_descriptions) {
            foreach ($file_descriptions as $line => $description) {
                $dom_description = $dom_document->createElement('description');
                $dom_description->setAttribute('file', $file);
                $dom_description->setAttribute('line', $line);
                
                $description = $purifier->purify($description);
                $dom_html = $dom_document->createDocumentFragment();
                $dom_html->appendXML($description);
                
                $dom_div = $dom_document->createElement('div');
                $dom_div->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
                $dom_div->appendChild($dom_html);
                
                $dom_description->appendChild($dom_div);
                $dom_descriptions->appendChild($dom_description);
            }
        }
        
    }
    
}

// print_r($dom_document->saveXML());

// save a copy of the raw XML
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
$html_output = str_replace('/>', ' />', $html_output); // <br /> not <br>
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

// write it to a file (todo: parse into seperate pages)
file_put_contents("$xsl_stylesheet_name.html", $html_output);


// ---------------------------------------------------------------------------
// Output for instant feedback

if (php_sapi_name() != 'cli') {
    echo $html_output;
} else {
    echo 'Files generated successfully.';
}

?>