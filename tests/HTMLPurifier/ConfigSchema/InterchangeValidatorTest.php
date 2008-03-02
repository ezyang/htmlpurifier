<?php

class HTMLPurifier_ConfigSchema_InterchangeValidatorTest extends UnitTestCase
{
    
    public function setup() {
        generate_mock_once('HTMLPurifier_ConfigSchema_Interchange');
        $this->mock = new HTMLPurifier_ConfigSchema_InterchangeMock();
        $this->validator = new HTMLPurifier_ConfigSchema_InterchangeValidator($this->mock);
    }
    
    protected function makeValidator($expect_params = null) {
        generate_mock_once('HTMLPurifier_ConfigSchema_Validator');
        $validator = new HTMLPurifier_ConfigSchema_ValidatorMock();
        if ($expect_params !== null) $validator->expectOnce('validate', $expect_params);
        else $validator->expectNever('validate');
        return $validator;
    }
    
    public function testAddNamespaceNullValidator() {
        $hash = array('ID' => 'Namespace');
        $this->mock->expectOnce('addNamespace', array($hash));
        $this->validator->addNamespace($hash);
    }
    
    public function testAddNamespaceWithValidators() {
        $hash = array('ID' => 'Namespace');
        $this->validator->addValidator($this->makeValidator(array($hash, $this->mock)));
        $this->validator->addNamespaceValidator($this->makeValidator(array($hash, $this->mock)));
        $this->validator->addDirectiveValidator($this->makeValidator()); // not called
        $this->mock->expectOnce('addNamespace', array($hash));
        $this->validator->addNamespace($hash);
    }
    
    public function testAddDirectiveWithValidators() {
        $hash = array('ID' => 'Namespace.Directive');
        $this->validator->addValidator($this->makeValidator(array($hash, $this->mock)));
        $this->validator->addNamespaceValidator($this->makeValidator()); // not called
        $this->validator->addDirectiveValidator($this->makeValidator(array($hash, $this->mock)));
        $this->mock->expectOnce('addDirective', array($hash));
        $this->validator->addDirective($hash);
    }
    
}
