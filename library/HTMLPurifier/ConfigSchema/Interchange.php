<?php

/**
 * Generic schema interchange format that can be converted to a runtime
 * representation (HTMLPurifier_ConfigSchema) or HTML documentation. Members
 * are completely validated.
 */
class HTMLPurifier_ConfigSchema_Interchange
{
    
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
        $namespace = $validator->namespace;
        $directive = $validator->directive;
        
        // ID tests
        $validator->addValidator($this->make('Exists', 'ID'));
        $validator->addValidator($this->make('Unique'));
        
        // ID: Namespace test
        $namespace->addValidator($this->make('Alnum', 'ID'));
        
        // ID: Common tests
        $validator->addValidator($this->make('ParseId'));
        $validator->addValidator($this->make('Exists', '_NAMESPACE'));
        $validator->addValidator($this->make('Alnum', '_NAMESPACE'));
        
        // ID: Directive tests
        $directive->addValidator($this->make('Exists', '_DIRECTIVE'));
        $directive->addValidator($this->make('Alnum', '_DIRECTIVE'));
        $directive->addValidator($this->make('NamespaceExists'));
        
        // Directive: Type tests
        $directive->addValidator($this->make('Exists', 'TYPE'));
        $directive->addValidator($this->make('ParseType'));
        $directive->addValidator($this->make('Exists', '_TYPE'));
        $directive->addValidator($this->make('Exists', '_NULL'));
        $directive->addValidator($this->make('Exists', 'DEFAULT'));
        $directive->addValidator($this->make('ParseDefault'));
        
        // Common tests
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
