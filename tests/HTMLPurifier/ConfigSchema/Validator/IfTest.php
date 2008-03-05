<?php

class HTMLPurifier_ConfigSchema_Validator_IfTest extends HTMLPurifier_ConfigSchema_ValidatorHarness
{
    
    public function setup() {
        parent::setup();
        generate_mock_once('HTMLPurifier_ConfigSchema_Validator');
    }
    
    public function testValidate() {
        $arr = array('ID' => 'RD');
        $this->validator->setCondition(new HTMLPurifier_ConfigSchema_Validator_Exists('ID'));
        $this->validator->setThen($mock1 = new HTMLPurifier_ConfigSchema_ValidatorMock());
        $mock1->expectOnce('validate', array($arr, $this->interchange));
        $this->validator->setElse($mock2 = new HTMLPurifier_ConfigSchema_ValidatorMock());
        $mock2->expectNever('validate');
        $this->validator->validate($arr, $this->interchange);
    }
    
    public function testValidateConditionIsFalse() {
        $arr = array('ID' => 'RD');
        $this->validator->setCondition(new HTMLPurifier_ConfigSchema_Validator_Exists('ALTID'));
        $this->validator->setThen($mock1 = new HTMLPurifier_ConfigSchema_ValidatorMock());
        $mock1->expectNever('validate');
        $this->validator->setElse($mock2 = new HTMLPurifier_ConfigSchema_ValidatorMock());
        $mock2->expectOnce('validate', array($arr, $this->interchange));
        $this->validator->validate($arr, $this->interchange);
    }
    
}
