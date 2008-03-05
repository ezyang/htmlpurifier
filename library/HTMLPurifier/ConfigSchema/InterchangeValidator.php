<?php

/**
 * Decorator for interchange that performs validations
 */
class HTMLPurifier_ConfigSchema_InterchangeValidator
{
    protected $interchange;
    public $namespace;
    public $directive;
    
    /**
     * @param $interchange Instance of HTMLPurifier_ConfigSchema_Interchange
     *      to save changes to.
     */
    public function __construct($interchange) {
        $this->interchange = $interchange;
        $this->namespace = new HTMLPurifier_ConfigSchema_Validator_Composite();
        $this->directive = new HTMLPurifier_ConfigSchema_Validator_Composite();
    }
    
    /**
     * Registers a HTMLPurifier_ConfigSchema_Validator for both
     * directive and namespace
     */
    public function addValidator($validator) {
        $this->directive->addValidator($validator);
        $this->namespace->addValidator($validator);
    }
    
    /**
     * Validates and adds a namespace hash
     */
    public function addNamespace($hash) {
        $this->namespace->validate($hash, $this->interchange);
        $this->interchange->addNamespace($hash);
    }
    
    /**
     * Validates and adds a directive hash
     */
    public function addDirective($hash) {
        $this->directive->validate($hash, $this->interchange);
        $this->interchange->addDirective($hash);
    }
}
