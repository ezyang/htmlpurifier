<?php

class HTMLPurifier_ConfigSchema_Validator_ParseTypeTest extends HTMLPurifier_ConfigSchema_ValidatorHarness
{
    
    public function testValidatePlain() {
        $arr = array('ID' => 'N.D', 'TYPE' => 'string');
        $this->validator->validate($arr, $this->interchange);
        $this->assertIdentical($arr, array(
            'ID' => 'N.D',
            'TYPE' => 'string',
            '_TYPE' => 'string',
            '_NULL' => false,
        ));
    }
    
    public function testValidateWithNull() {
        $arr = array('ID' => 'N.D', 'TYPE' => 'int/null');
        $this->validator->validate($arr, $this->interchange);
        $this->assertIdentical($arr, array(
            'ID' => 'N.D',
            'TYPE' => 'int/null',
            '_TYPE' => 'int',
            '_NULL' => true,
        ));
    }
    
    public function testValidateInvalidType() {
        $arr = array('ID' => 'N.D', 'TYPE' => 'aint/null');
        $this->expectSchemaException('Invalid type aint for configuration directive N.D');
        $this->validator->validate($arr, $this->interchange);
    }
    
}
