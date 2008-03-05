<?php

class HTMLPurifier_ConfigSchema_Validator_OrTest extends HTMLPurifier_ConfigSchema_ValidatorHarness
{
    
    public function testValidatePass() {
        $arr = array('ID' => 'RD');
        $this->validator->addValidator(new HTMLPurifier_ConfigSchema_Validator_Alnum('ID'));
        // Never called:
        $this->validator->addValidator(new HTMLPurifier_ConfigSchema_Validator_Exists('ALT-ID'));
        $this->validator->validate($arr, $this->interchange);
    }
    
    public function testValidatePassLater() {
        $arr = array('ID' => 'RD');
        // This one fails:
        $this->validator->addValidator(new HTMLPurifier_ConfigSchema_Validator_Exists('ALT-ID'));
        // But this one passes:
        $this->validator->addValidator(new HTMLPurifier_ConfigSchema_Validator_Alnum('ID'));
        $this->validator->validate($arr, $this->interchange);
    }
    
    public function testValidateFail() {
        $arr = array('ID' => 'RD');
        $this->validator->addValidator(new HTMLPurifier_ConfigSchema_Validator_Exists('ALT-ID'));
        $this->validator->addValidator(new HTMLPurifier_ConfigSchema_Validator_Exists('FOOBAR'));
        $this->expectException('HTMLPurifier_ConfigSchema_Exception');
        $this->validator->validate($arr, $this->interchange);
    }
    
}
