<?php

/**
 * Validates that an ID field exists in the array
 */
class HTMLPurifier_ConfigSchema_Validator_IdExists extends HTMLPurifier_ConfigSchema_Validator
{
    
    public function validate(&$arr, $interchange, $type) {
        if (!isset($arr['ID'])) {
            throw new HTMLPurifier_ConfigSchema_Exception('ID must exist in ' . $type);
        }
    }
    
}
