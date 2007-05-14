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
     * List of aliases to doctype name
     */
    var $aliases = array();
    
    /**
     * List of standard modules (string identifiers or literal objects)
     * that this doctype uses
     */
    var $modules = array();
    
    /**
     * Associative array of mode names to lists of modules; these are
     * the modules added into the standard list if a particular mode
     * is enabled, such as lenient or correctional.
     */
    var $modulesForModes = array();
}

?>