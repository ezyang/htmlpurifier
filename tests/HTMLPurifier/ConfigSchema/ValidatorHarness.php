<?php

class HTMLPurifier_ConfigSchema_ValidatorHarness extends UnitTestCase
{
    
    protected $interchange, $validator;
    
    public function setup() {
        generate_mock_once('HTMLPurifier_ConfigSchema_Interchange');
        $this->interchange = new HTMLPurifier_ConfigSchema_InterchangeMock();
    }
    
    protected function expectSchemaException($msg) {
        $this->expectException(new HTMLPurifier_ConfigSchema_Exception($msg));
    }
    
}
