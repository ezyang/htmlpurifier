<?php

/**
 * Groups several validators together, which can be used with logical validators
 */
class HTMLPurifier_ConfigSchema_Validator_Composite extends HTMLPurifier_ConfigSchema_Validator
{
    
    protected $validators = array();
    
    public function addValidator($validator) {
        $this->validators[] = $validator;
    }
    
    public function validate(&$arr, $interchange) {
        foreach ($this->validators as $validator) {
            $validator->validate($arr, $interchange);
        }
    }
    
}
