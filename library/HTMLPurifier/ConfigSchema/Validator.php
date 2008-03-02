<?php

/**
 * Base validator for HTMLPurifier_ConfigSchema_Interchange
 */
class HTMLPurifier_ConfigSchema_Validator
{
    
    /**
     * Validates and filters a namespace.
     */
    public function validateNamespace(&$arr, $interchange) {
        $this->validate($arr, $interchange, 'namespace');
    }
    
    /**
     * Validates and filters a directive.
     */
    public function validateDirective(&$arr, $interchange) {
        $this->validate($arr, $interchange, 'directive');
    }
    
    /**
     * Common validator, throwing an exception on error. It can
     * also performing filtering or evaluation functions.
     *
     * @note This is strictly for convenience reasons when subclasing.
     * 
     * @param $arr Array to validate.
     * @param $interchange HTMLPurifier_ConfigSchema_Interchange object
     *      that is being processed.
     * @param $type Type of object being validated, this saves a little work
     *      if only cosmetic changes are being made between namespaces
     *      and directives.
     */
    protected function validate(&$arr, $interchange, $type) {}
    
    
}
