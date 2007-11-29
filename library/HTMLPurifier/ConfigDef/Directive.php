<?php

require_once 'HTMLPurifier/ConfigDef.php';

/**
 * Structure object containing definition of a directive.
 * @note This structure does not contain default values
 */
class HTMLPurifier_ConfigDef_Directive extends HTMLPurifier_ConfigDef
{
    
    public $class = 'directive';
    
    public function __construct(
        $type = null,
        $descriptions = null,
        $allow_null = null,
        $allowed = null,
        $aliases = null
    ) {
        if (        $type !== null)         $this->type = $type;
        if ($descriptions !== null) $this->descriptions = $descriptions;
        if (  $allow_null !== null)   $this->allow_null = $allow_null;
        if (     $allowed !== null)      $this->allowed = $allowed;
        if (     $aliases !== null)      $this->aliases = $aliases;
    }
    
    /**
     * Allowed type of the directive. Values are:
     *      - string
     *      - istring (case insensitive string)
     *      - int
     *      - float
     *      - bool
     *      - lookup (array of value => true)
     *      - list (regular numbered index array)
     *      - hash (array of key => value)
     *      - mixed (anything goes)
     */
    public $type = 'mixed';
    
    /**
     * Plaintext descriptions of the configuration entity is. Organized by
     * file and line number, so multiple descriptions are allowed.
     */
    public $descriptions = array();
    
    /**
     * Is null allowed? Has no effect for mixed type.
     * @bool
     */
    public $allow_null = false;
    
    /**
     * Lookup table of allowed values of the element, bool true if all allowed.
     */
    public $allowed = true;
    
    /**
     * Hash of value aliases, i.e. values that are equivalent.
     */
    public $aliases = array();
    
    /**
     * Advisory list of directive aliases, i.e. other directives that
     * redirect here
     */
    public $directiveAliases = array();
    
    /**
     * Adds a description to the array
     */
    public function addDescription($file, $line, $description) {
        if (!isset($this->descriptions[$file])) $this->descriptions[$file] = array();
        $this->descriptions[$file][$line] = $description;
    }
    
}

