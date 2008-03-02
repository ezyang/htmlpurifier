<?php

class HTMLPurifier_ConfigSchema_Interchange_Validator_IdExistsTest extends HTMLPurifier_ConfigSchema_Interchange_ValidatorHarness
{
    
    public function setup() {
        parent::setup();
        $this->validator = new HTMLPurifier_ConfigSchema_Interchange_Validator_IdExists($this->mock);
    }
    
    public function testNamespace() {
        $this->mock->expectNever('addNamespace');
        $this->expectSchemaException('Namespace must have ID');
        $this->validator->addNamespace();
    }
    
    public function testDirective() {
        $this->mock->expectNever('addDirective');
        $this->expectSchemaException('Directive must have ID');
        $this->validator->addDirective();
    }
    
}
