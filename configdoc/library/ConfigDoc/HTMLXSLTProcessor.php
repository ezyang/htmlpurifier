<?php

/**
 * Special XSLT processor specifically for HTML documents. Loosely
 * based off of XSLTProcessor, but does not inherit from that class
 */
class ConfigDoc_HTMLXSLTProcessor
{
    
    /**
     * Instance of XSLTProcessor
     */
    protected $xsltProcessor;
    
    public function __construct() {
        $this->xsltProcessor = new XSLTProcessor();
    }
    
    /**
     * Imports stylesheet for processor to use
     * @param $xsl XSLT DOM tree, or filename of the XSL transformation
     * @return bool Success?
     */
    public function importStylesheet($xsl) {
        if (is_string($xsl)) {
            $xsl_file = $xsl;
            $xsl = new DOMDocument();
            $xsl->load($xsl_file);
        }
        return $this->xsltProcessor->importStylesheet($xsl);
    }
    
    /**
     * Transforms an XML file into compatible XHTML based on the stylesheet
     * @param $xml XML DOM tree
     * @return string HTML output
     * @todo Rename to transformToXHTML, as transformToHTML is misleading
     */
    public function transformToHTML($xml) {
        $out = $this->xsltProcessor->transformToXML($xml);
        
        // fudges for HTML backwards compatibility
        // assumes that document is XHTML
        $out = str_replace('/>', ' />', $out); // <br /> not <br/>
        $out = str_replace(' xmlns=""', '', $out); // rm unnecessary xmlns
        $out = str_replace(' xmlns="http://www.w3.org/1999/xhtml"', '', $out); // rm unnecessary xmlns
        
        if (class_exists('Tidy')) {
            // cleanup output
            $config = array(
                'indent'        => true,
                'output-xhtml'  => true,
                'wrap'          => 80
            );
            $tidy = new Tidy;
            $tidy->parseString($out, $config, 'utf8');
            $tidy->cleanRepair();
            $out = (string) $tidy;
        }
        
        return $out;
    }
    
    /**
     * Bulk sets parameters for the XSL stylesheet
     * @param array $options Associative array of options to set
     */
    public function setParameters($options) {
        foreach ($options as $name => $value) {
            $this->xsltProcessor->setParameter('', $name, $value);
        }
    }
    
}

