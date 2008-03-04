<?php

/**
 * Decorator for interchange that performs validations
 */
class HTMLPurifier_ConfigSchema_InterchangeValidator
{
    protected $interchange;
    protected $validators = array();
    protected $namespaceValidators = array();
    protected $directiveValidators = array();
    
    /**
     * @param $interchange Instance of HTMLPurifier_ConfigSchema_Interchange
     *      to save changes to.
     */
    public function __construct($interchange) {
        $this->interchange = $interchange;
    }
    
    /**
     * Registers a HTMLPurifier_ConfigSchema_Validator to run when adding.
     */
    public function addValidator($validator) {
        $this->addNamespaceValidator($validator);
        $this->addDirectiveValidator($validator);
    }
    
    /**
     * Register validators to be used only on directives
     */
    public function addDirectiveValidator($validator) {
        $this->directiveValidators[] = $validator;
    }
    
    /**
     * Register validators to be used only on namespaces
     */
    public function addNamespaceValidator($validator) {
        $this->namespaceValidators[] = $validator;
    }
    
    /**
     * Validates and adds a namespace hash
     */
    public function addNamespace($hash) {
        foreach ($this->namespaceValidators as $validator) {
            $validator->validate($hash, $this->interchange);
        }
        $this->interchange->addNamespace($hash);
    }
    
    /**
     * Validates and adds a directive hash
     */
    public function addDirective($hash) {
        foreach ($this->directiveValidators as $validator) {
            $validator->validate($hash, $this->interchange);
        }
        $this->interchange->addDirective($hash);
    }
}
