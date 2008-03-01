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
    public $types = array(
        'string'    => 'String',
        'istring'   => 'Case-insensitive string',
        'text'      => 'Text',
        'itext'      => 'Case-insensitive text',
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
    public $namespaces;
    
    /**
     * Array of Directive ID => array(directive info)
     */
    public $directives;
    
    /**
     * Adds a namespace array to $namespaces
     */
    public function addNamespace($arr) {
        if (!isset($arr['ID'])) throw new HTMLPurifier_ConfigSchema_Exception('Namespace must have ID');
        $this->namespaces[$arr['ID']] = $arr;
    }
    
    /**
     * Adds a directive array to $directives
     */
    public function addDirective($arr) {
        if (!isset($arr['ID'])) throw new HTMLPurifier_ConfigSchema_Exception('Directive must have ID');
        $this->directives[$arr['ID']] = $arr;
    }
    
}
