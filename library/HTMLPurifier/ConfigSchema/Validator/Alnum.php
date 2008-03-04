<?php

/**
 * Validates that a field is alphanumeric in the array. Expects $index
 * to exist.
 */
class HTMLPurifier_ConfigSchema_Validator_Alnum extends HTMLPurifier_ConfigSchema_Validator
{
    
    protected $index;
    
    public function __construct($index) {
        $this->index = $index;
    }
    
    public function validate(&$arr, $interchange) {
        if (!ctype_alnum($arr[$this->index])) {
            $this->error($arr[$this->index] . ' in '. $this->index .' must be alphanumeric');
        }
    }
    
}
