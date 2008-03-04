<?php

class HTMLPurifier_ConfigSchema_ValidatorHarness extends UnitTestCase
{
    
    protected $interchange, $validator;
    
    public function setup() {
        $this->interchange = new HTMLPurifier_ConfigSchema_Interchange();
    }
    
    protected function expectSchemaException($msg) {
        $this->expectException(new HTMLPurifier_ConfigSchema_Exception($msg));
    }
    
}
