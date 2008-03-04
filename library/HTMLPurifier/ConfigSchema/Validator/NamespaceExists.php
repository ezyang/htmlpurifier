<?php

/**
 * Validates that the directive's namespace exists. Expects _NAMESPACE
 * to have been created via HTMLPurifier_ConfigSchema_Validator_ParseId
 */
class HTMLPurifier_ConfigSchema_Validator_NamespaceExists extends HTMLPurifier_ConfigSchema_Validator
{
    
    public function validate(&$arr, $interchange) {
        if (!isset($interchange->namespaces[$arr['_NAMESPACE']])) {
            $this->error('Cannot define directive for undefined namespace ' . $arr['_NAMESPACE']);
        }
    }
    
}
