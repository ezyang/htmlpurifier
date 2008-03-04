<?php

/**
 * Validates that this ID does not exist already in the interchange object.
 * Expects ID to exist.
 * 
 * @note
 *      Although this tests both possible values, in practice the ID
 *      will only be in one or the other. We do this to keep things simple.
 */
class HTMLPurifier_ConfigSchema_Validator_Unique extends HTMLPurifier_ConfigSchema_Validator
{
    
    public function validate(&$arr, $interchange) {
        if (isset($interchange->namespaces[$arr['ID']])) {
            $this->error('Cannot redefine namespace');
        }
        if (isset($interchange->directives[$arr['ID']])) {
            $this->error('Cannot redefine directive');
        }
    }
    
}
