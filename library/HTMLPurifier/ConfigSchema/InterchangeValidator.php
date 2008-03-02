<?php

/**
 * Decorator for interchange that performs validations
 */
class HTMLPurifier_ConfigSchema_InterchangeValidator
{
    protected $interchange;
    protected $validators = array();
    
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
        $this->validators[] = $validator;
    }
    
    /**
     * Validates and adds a namespace hash
     */
    public function addNamespace($hash) {
        foreach ($this->validators as $validator) {
            $validator->validateNamespace($hash, $this->interchange);
        }
        $this->interchange->addNamespace($hash);
    }
    
    /**
     * Validates and adds a directive hash
     */
    public function addDirective($hash) {
        foreach ($this->validators as $validator) {
            $validator->validateDirective($hash, $this->interchange);
        }
        $this->interchange->addDirective($hash);
    }
}
