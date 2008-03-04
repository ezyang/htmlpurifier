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
    public $namespaces;
    
    /**
     * Array of Directive ID => array(directive info)
     */
    public $directives;
    
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
        $validator = new HTMLPurifier_ConfigSchema_InterchangeValidator($this);
        
        // Validators should be defined in the order they are to be called.
        
        // Common validators
        $validator->addValidator(new HTMLPurifier_ConfigSchema_Validator_Exists('ID'));
        $validator->addValidator(new HTMLPurifier_ConfigSchema_Validator_Duplicate());
        $validator->addValidator(new HTMLPurifier_ConfigSchema_Validator_Exists('DESCRIPTION'));
        
        // Namespace validators
        
        // Directive validators
        
        return $validator;
    }
    
}
