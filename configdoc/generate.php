<?php

/*
if (php_sapi_name() != 'cli') {
    header('Content-type:text/plain');
    ini_set('html_errors', '0');
}
*/
if (version_compare('5', PHP_VERSION, '>')) exit('Requires PHP 5 or higher.');

error_reporting(E_ALL);

set_include_path('../library' . PATH_SEPARATOR . get_include_path());
require_once 'HTMLPurifier.php';

$definition = HTMLPurifier_ConfigDef::instance();
// print_r($definition);

$purifier = new HTMLPurifier();

$dom_document = new DOMDocument('1.0', 'UTF-8');
$dom_root = $dom_document->createElement('configdoc');
$dom_document->appendChild($dom_root);
$dom_document->formatOutput = true;

// add the name of the application
$dom_root->appendChild($dom_document->createElement('title', 'HTML Purifier'));

/*
TODO:
- namespace descriptions
- enumerated values
- default values
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
        $dom_directive->appendChild(
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

// save a copy of the raw XML for good measure
$dom_document->save('output/configdoc.xml');

// load the stylesheet
$xsl_stylesheet_name = 'default';
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

// write it to a file (todo: parse into seperate pages)
file_put_contents("output/$xsl_stylesheet_name.html", $html_output);

// output so you can see the fruits of your work!
echo $html_output;

?>