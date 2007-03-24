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

// ---------------------------------------------------------------------------
// Check and configure environment

if (version_compare('5', PHP_VERSION, '>')) exit('Requires PHP 5 or higher.');
error_reporting(E_ALL);


// ---------------------------------------------------------------------------
// Include HTML Purifier library

set_include_path('../library' . PATH_SEPARATOR . get_include_path());
require_once 'HTMLPurifier.php';


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

$schema = HTMLPurifier_ConfigSchema::instance();
$purifier = new HTMLPurifier();


// ---------------------------------------------------------------------------
// Generate types.xml, a document describing the constraint "type"

$types_document = new DOMDocument('1.0', 'UTF-8');
$types_root = $types_document->createElement('types');
$types_document->appendChild($types_root);
$types_document->formatOutput = true;
foreach ($schema->types as $name => $expanded_name) {
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
- create a definition (DTD or other) once interface stabilizes
*/

foreach($schema->info as $namespace_name => $namespace_info) {
    
    $dom_namespace = $dom_document->createElement('namespace');
    $dom_root->appendChild($dom_namespace);
    
    $dom_namespace->setAttribute('id', $namespace_name);
    $dom_namespace->appendChild(
        $dom_document->createElement('name', $namespace_name)
    );
    $dom_namespace_description = $dom_document->createElement('description');
    $dom_namespace->appendChild($dom_namespace_description);
    appendHTMLDiv($dom_document, $dom_namespace_description,
        $schema->info_namespace[$namespace_name]->description);
    
    foreach ($namespace_info as $name => $info) {
        
        if ($info->class == 'alias') continue;
        
        $dom_directive = $dom_document->createElement('directive');
        $dom_namespace->appendChild($dom_directive);
        
        $dom_directive->setAttribute('id', $namespace_name . '.' . $name);
        $dom_directive->appendChild(
            $dom_document->createElement('name', $name)
        );
        
        $dom_constraints = $dom_document->createElement('constraints');
        $dom_directive->appendChild($dom_constraints);
        
        $dom_type = $dom_document->createElement('type', $info->type);
        if ($info->allow_null) {
            $dom_type->setAttribute('allow-null', 'yes');
        }
        $dom_constraints->appendChild($dom_type);
        
        if ($info->allowed !== true) {
            $dom_allowed = $dom_document->createElement('allowed');
            $dom_constraints->appendChild($dom_allowed);
            foreach ($info->allowed as $allowed => $bool) {
                $dom_allowed->appendChild(
                    $dom_document->createElement('value', $allowed)
                );
            }
        }
        
        $raw_default = $schema->defaults[$namespace_name][$name];
        if (is_bool($raw_default)) {
            $default = $raw_default ? 'true' : 'false';
        } elseif (is_string($raw_default)) {
            $default = "\"$raw_default\"";
        } elseif (is_null($raw_default)) {
            $default = 'null';
        } else {
            $default = print_r(
                    $schema->defaults[$namespace_name][$name], true
                );
        }
        
        $dom_default = $dom_document->createElement('default', $default);
        
        // remove this once we get a DTD
        $dom_default->setAttribute('xml:space', 'preserve');
        
        $dom_constraints->appendChild($dom_default);
        
        $dom_descriptions = $dom_document->createElement('descriptions');
        $dom_directive->appendChild($dom_descriptions);
        
        foreach ($info->descriptions as $file => $file_descriptions) {
            foreach ($file_descriptions as $line => $description) {
                $dom_description = $dom_document->createElement('description');
                $dom_description->setAttribute('file', $file);
                $dom_description->setAttribute('line', $line);
                appendHTMLDiv($dom_document, $dom_description, $description);
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