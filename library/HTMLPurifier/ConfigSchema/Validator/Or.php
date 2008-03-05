<?php

/**
 * Groups several validators together, but as an 'or': if the first
 * one passes, we abort; if it throws an exception, we try the next validator,
 * and the next. If all validators fail, we throw an exception.
 *
 * @note If no validators are registered, this validator automatically
 *       "passes".
 */
class HTMLPurifier_ConfigSchema_Validator_Or extends HTMLPurifier_ConfigSchema_Validator
{
    
    protected $validators = array();
    
    public function addValidator($validator) {
        $this->validators[] = $validator;
    }
    
    public function validate(&$arr, $interchange) {
        $exceptions = array();
        $pass = false;
        foreach ($this->validators as $validator) {
            try {
                $validator->validate($arr, $interchange);
            } catch (HTMLPurifier_ConfigSchema_Exception $e) {
                $exceptions[] = $e;
                continue;
            }
            $exceptions = array();
            break;
        }
        if ($exceptions) {
            // I wonder how we can make the exceptions "lossless"
            throw new HTMLPurifier_ConfigSchema_Exception('All validators failed: ' . implode(";\n", $exceptions));
        }
    }
    
}
