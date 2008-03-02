<?php

class HTMLPurifier_ConfigSchema_Interchange_ValidatorHarness extends UnitTestCase
{
    
    protected $validator;
    protected $mock;
    
    public function setup() {
        generate_mock_once('HTMLPurifier_ConfigSchema_Interchange');
        $this->mock = new HTMLPurifier_ConfigSchema_InterchangeMock();
    }
    
    protected function expectSchemaException($msg) {
        $this->expectException(new HTMLPurifier_ConfigSchema_Exception($msg));
    }
    
}
