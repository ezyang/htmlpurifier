<?php

/**
 * Represents a document type, contains information on which modules
 * need to be loaded.
 */
class HTMLPurifier_Doctype
{
    /**
     * Full name of doctype
     */
    var $name;
    
    /**
     * List of standard modules (string identifiers or literal objects)
     * that this doctype uses
     */
    var $modules = array();
    
    /**
     * List of modules to use for tidying up code
     */
    var $tidyModules = array();
    
    /**
     * Is the language derived from XML (i.e. XHTML)?
     */
    var $xml = true;
    
    /**
     * List of aliases for this doctype
     */
    var $aliases = array();
    
    function HTMLPurifier_Doctype($name = null, $xml = true, $modules = array(),
        $tidyModules = array(), $aliases = array()
    ) {
        $this->name         = $name;
        $this->xml          = $xml;
        $this->modules      = $modules;
        $this->tidyModules  = $tidyModules;
        $this->aliases      = $aliases;
    }
    
    /**
     * Clones the doctype, use before resolving modes and the like
     */
    function copy() {
        return new HTMLPurifier_Doctype(
            $this->name, $this->xml, $this->modules, $this->tidyModules, $this->aliases
        );
    }
}

?>