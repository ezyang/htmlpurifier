<?php

class HTMLPurifier_ConfigSchema_InterchangeValidatorTest extends UnitTestCase
{
    
    public function setup() {
        generate_mock_once('HTMLPurifier_ConfigSchema_Interchange');
        $this->mock = new HTMLPurifier_ConfigSchema_InterchangeMock();
        $this->validator = new HTMLPurifier_ConfigSchema_InterchangeValidator($this->mock);
    }
    
    protected function makeValidator($expect_method, $expect_params) {
        generate_mock_once('HTMLPurifier_ConfigSchema_Validator');
        $validator = new HTMLPurifier_ConfigSchema_ValidatorMock();
        $validator->expectOnce($expect_method, $expect_params);
        return $validator;
    }
    
    public function testAddNamespaceNullValidator() {
        $hash = array('ID' => 'Namespace');
        $this->mock->expectOnce('addNamespace', array($hash));
        $this->validator->addNamespace($hash);
    }
    
    public function testAddNamespaceWithValidators() {
        $hash = array('ID' => 'Namespace');
        $this->validator->addValidator($this->makeValidator('validateNamespace', array($hash, $this->mock)));
        $this->validator->addValidator($this->makeValidator('validateNamespace', array($hash, $this->mock)));
        $this->mock->expectOnce('addNamespace', array($hash));
        $this->validator->addNamespace($hash);
    }
    
}
