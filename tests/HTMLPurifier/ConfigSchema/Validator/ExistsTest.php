<?php

class HTMLPurifier_ConfigSchema_Validator_ExistsTest extends HTMLPurifier_ConfigSchema_ValidatorHarness
{
    
    public function setup() {
        parent::setup();
        $this->validator = new HTMLPurifier_ConfigSchema_Validator_Exists('ID');
    }
    
    public function testValidate() {
        $this->expectSchemaException('ID must exist');
        $arr = array();
        $this->validator->validate($arr, $this->interchange);
    }
    
}
