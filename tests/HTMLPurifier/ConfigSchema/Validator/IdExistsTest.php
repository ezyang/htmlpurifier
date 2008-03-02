<?php

class HTMLPurifier_ConfigSchema_Validator_IdExistsTest extends HTMLPurifier_ConfigSchema_ValidatorHarness
{
    
    public function setup() {
        parent::setup();
        $this->validator = new HTMLPurifier_ConfigSchema_Validator_IdExists();
    }
    
    public function testValidateNamespace() {
        $this->expectSchemaException('ID must exist in namespace');
        $arr = array();
        $this->validator->validateNamespace($arr, $this->interchange);
    }
    
    public function testValidateDirective() {
        $this->expectSchemaException('ID must exist in directive');
        $arr = array();
        $this->validator->validateDirective($arr, $this->interchange);
    }
    
}
