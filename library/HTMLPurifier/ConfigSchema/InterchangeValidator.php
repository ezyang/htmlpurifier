<?php

/**
 * Decorator for interchange that performs validations
 */
class HTMLPurifier_ConfigSchema_InterchangeValidator
{
    protected $interchange;
    protected $validators = array();
    protected $namespaceValidators = array();
    protected $directiveVaildators = array();
    protected $index = 0;
    
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
        $this->validators[$this->index++] = $validator;
    }
    
    /**
     * Register validators to be used only on directives
     */
    public function addDirectiveValidator($validator) {
        $this->directiveValidators[$this->index++] = $validator;
    }
    
    /**
     * Register validators to be used only on namespaces
     */
    public function addNamespaceValidator($validator) {
        $this->namespaceValidators[$this->index++] = $validator;
    }
    
    /**
     * Validates and adds a namespace hash
     */
    public function addNamespace($hash) {
        for ($i = 0; $i < $this->index; $i++) {
            if (isset($this->validators[$i])) $validator = $this->validators[$i];
            elseif (isset($this->namespaceValidators[$i])) $validator = $this->namespaceValidators[$i];
            else continue;
            $validator->validate($hash, $this->interchange);
        }
        $this->interchange->addNamespace($hash);
    }
    
    /**
     * Validates and adds a directive hash
     */
    public function addDirective($hash) {
        for ($i = 0; $i < $this->index; $i++) {
            if (isset($this->validators[$i])) $validator = $this->validators[$i];
            elseif (isset($this->directiveValidators[$i])) $validator = $this->directiveValidators[$i];
            else continue;
            $validator->validate($hash, $this->interchange);
        }
        $this->interchange->addDirective($hash);
    }
}
