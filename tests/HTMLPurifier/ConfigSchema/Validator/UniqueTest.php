<?php

class HTMLPurifier_ConfigSchema_Validator_UniqueTest extends HTMLPurifier_ConfigSchema_ValidatorHarness
{
    
    public function setup() {
        parent::setup();
        $this->validator = new HTMLPurifier_ConfigSchema_Validator_Unique();
    }
    
    public function testValidateNamespace() {
        $this->interchange->addNamespace(array('ID' => 'Namespace'));
        $this->expectSchemaException('Cannot redefine namespace');
        $arr = array('ID' => 'Namespace');
        $this->validator->validate($arr, $this->interchange);
    }
    
    public function testValidateDirective() {
        $this->interchange->addDirective(array('ID' => 'Namespace.Directive'));
        $this->expectSchemaException('Cannot redefine directive');
        $arr = array('ID' => 'Namespace.Directive');
        $this->validator->validate($arr, $this->interchange);
    }
    
}
