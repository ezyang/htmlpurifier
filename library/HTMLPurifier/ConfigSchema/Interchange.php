<?php

/**
 * Generic schema interchange format that can be converted to a runtime
 * representation (HTMLPurifier_ConfigSchema) or HTML documentation. Members
 * are completely validated.
 */
class HTMLPurifier_ConfigSchema_Interchange
{
    
    /**
     * Hash table of allowed types.
     */
    protected $types = array(
        'string'    => 'String',
        'istring'   => 'Case-insensitive string',
        'text'      => 'Text',
        'itext'     => 'Case-insensitive text',
        'int'       => 'Integer',
        'float'     => 'Float',
        'bool'      => 'Boolean',
        'lookup'    => 'Lookup array',
        'list'      => 'Array list',
        'hash'      => 'Associative array',
        'mixed'     => 'Mixed'
    );
    
    /**
     * Array of Namespace ID => array(namespace info)
     */
    protected $namespaces;
    
    /**
     * Array of Directive ID => array(directive info)
     */
    protected $directives;
    
    /** Get all namespaces */
    public function getNamespaces() {return $this->namespaces;}
    /** Get a namespace */
    public function getNamespace($id) {return $this->namespaces[$id];}
    /** Check if a namespace exists */
    public function namespaceExists($id) {return isset($this->namespaces[$id]);}
    
    /** Get all directives */
    public function getDirectives() {return $this->directives;}
    /** Get a directive */
    public function getDirective($id) {return $this->directives[$id];}
    /** Check if a directive exists */
    public function directiveExists($id) {return isset($this->directives[$id]);}
    
    /** Get all types */
    public function getTypes() {return $this->types;}
    
    /**
     * Adds a namespace array to $namespaces
     */
    public function addNamespace($arr) {
        $this->namespaces[$arr['ID']] = $arr;
    }
    
    /**
     * Adds a directive array to $directives
     */
    public function addDirective($arr) {
        $this->directives[$arr['ID']] = $arr;
    }
    
    /**
     * Retrieves a version of this object wrapped in the validator adapter
     * to be used for data-input.
     */
    public function getValidatorAdapter() {
        return
            new HTMLPurifier_ConfigSchema_Interchange_Validator_IdExists(
            $this);
    }
    
}
