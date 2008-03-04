<?php

/**
 * Base validator for HTMLPurifier_ConfigSchema_Interchange
 */
class HTMLPurifier_ConfigSchema_Validator
{
    
    /**
     * Common validator, throwing an exception on error. It can
     * also performing filtering or evaluation functions.
     *
     * @param $arr Array to validate.
     * @param $interchange HTMLPurifier_ConfigSchema_Interchange object
     *      that is being processed.
     */
    public function validate(&$arr, $interchange) {}
    
    /**
     * Throws a HTMLPurifier_ConfigSchema_Exception
     */
    protected function error($msg) {
        throw new HTMLPurifier_ConfigSchema_Exception($msg);
    }
    
}
