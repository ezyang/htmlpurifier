<?php

require_once 'ConfigDoc/HTMLXSLTProcessor.php';
require_once 'ConfigDoc/XMLSerializer/Types.php';
require_once 'ConfigDoc/XMLSerializer/ConfigSchema.php';

/**
 * Facade class for configuration documentation system
 */
class ConfigDoc
{
    
    /**
     * Generates configuration documentation based on a HTMLPurifier_ConfigSchema
     * object and styleshet name
     * @param $schema Instance of HTMLPurifier_ConfigSchema to document
     * @param $xsl_stylesheet_name Name of XSL stylesheet in ../styles/ directory to use
     * @param $parameters Extra parameters to pass to the stylesheet
     * @return string HTML output
     */
    public function generate($schema, $xsl_stylesheet_name = 'plain', $parameters = array()) {
        // generate types document, describing type constraints
        $types_serializer = new ConfigDoc_XMLSerializer_Types();
        $types_document = $types_serializer->serialize($schema);
        $types_document->save(dirname(__FILE__) . '/../types.xml'); // only ONE
        
        // generate configdoc.xml, documents configuration directives
        $schema_serializer = new ConfigDoc_XMLSerializer_ConfigSchema();
        $schema_document = $schema_serializer->serialize($schema);
        $schema_document->save('configdoc.xml');
        
        // setup transformation
        $xsl_stylesheet = dirname(__FILE__) . "/../styles/$xsl_stylesheet_name.xsl";
        $xslt_processor = new ConfigDoc_HTMLXSLTProcessor();
        $xslt_processor->setParameters($parameters);
        $xslt_processor->importStylesheet($xsl_stylesheet);
        
        return $xslt_processor->transformToHTML($schema_document);
    }
    
    /**
     * Remove any generated files
     * @return boolean Success?
     */
    public function cleanup() {
        return unlink('configdoc.xml');
    }
    
}

