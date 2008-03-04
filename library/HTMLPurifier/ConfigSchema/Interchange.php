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
        $validator->addValidator($this->make('Exists', 'ID'));
        $validator->addValidator($this->make('Unique'));
        $validator->addNamespaceValidator($this->make('Alnum', 'ID'));
        $validator->addValidator($this->make('ParseId'));
        $validator->addValidator($this->make('Exists', '_NAMESPACE'));
        $validator->addValidator($this->make('Alnum', '_NAMESPACE'));
        $validator->addDirectiveValidator($this->make('Exists', '_DIRECTIVE'));
        $validator->addDirectiveValidator($this->make('Alnum', '_DIRECTIVE'));
        $validator->addDirectiveValidator($this->make('Exists', 'TYPE'));
        $validator->addDirectiveValidator($this->make('Exists', 'DEFAULT'));
        $validator->addDirectiveValidator($this->make('NamespaceExists'));
        $validator->addValidator($this->make('Exists', 'DESCRIPTION'));
        
        return $validator;
    }
    
    /**
     * Creates a validator.
     * @warning
     *      Only *one* argument is supported; multiple args shouldn't use
     *      this function.
     */
    protected function make($name, $arg = null) {
        $class = "HTMLPurifier_ConfigSchema_Validator_$name";
        if ($arg === null) return new $class();
        else return new $class($arg);
    }
    
}
