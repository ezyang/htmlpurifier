<?php

class HTMLPurifier_ConfigSchema_Interchange_Validator_IdExists extends HTMLPurifier_ConfigSchema_Interchange_Validator
{
    
    public function addNamespace($arr) {
        if (!isset($arr['ID'])) {
            throw new HTMLPurifier_ConfigSchema_Exception('Namespace must have ID');
        }
        parent::addNamespace($arr);
    }
    
    public function addDirective($arr) {
        if (!isset($arr['ID'])) {
            throw new HTMLPurifier_ConfigSchema_Exception('Directive must have ID');
        }
        parent::addDirective($arr);
    }
    
}
