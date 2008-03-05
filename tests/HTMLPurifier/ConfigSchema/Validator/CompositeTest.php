<?php

class HTMLPurifier_ConfigSchema_Validator_CompositeTest extends HTMLPurifier_ConfigSchema_ValidatorHarness
{
    
    public function testValidate() {
        $arr = array('ID' => 'RD');
        
        generate_mock_once('HTMLPurifier_ConfigSchema_Validator');
        $mock1 = new HTMLPurifier_ConfigSchema_ValidatorMock();
        $mock2 = new HTMLPurifier_ConfigSchema_ValidatorMock();
        $mock1->expectOnce('validate', array($arr, $this->interchange));
        $mock2->expectOnce('validate', array($arr, $this->interchange));
        $this->validator->addValidator($mock1);
        $this->validator->addValidator($mock2);
        
        $this->validator->validate($arr, $this->interchange);
    }
    
}
