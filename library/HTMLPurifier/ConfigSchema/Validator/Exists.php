<?php

/**
 * Validates that an field exists in the array
 */
class HTMLPurifier_ConfigSchema_Validator_Exists extends HTMLPurifier_ConfigSchema_Validator
{
    
    protected $index;
    
    public function __construct($index) {
        $this->index = $index;
    }
    
    public function validate(&$arr, $interchange) {
        if (empty($arr[$this->index])) {
            $this->error($this->index . ' must exist');
        }
    }
    
}
