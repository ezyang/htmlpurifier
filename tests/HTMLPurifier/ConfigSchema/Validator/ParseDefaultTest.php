<?php

class HTMLPurifier_ConfigSchema_Validator_ParseDefaultTest extends HTMLPurifier_ConfigSchema_ValidatorHarness
{
    
    public function testValidate() {
        $arr = array(
            'ID' => 'N.D',
            'DEFAULT' => 'true',
            '_TYPE' => 'bool',
            '_NULL' => false,
        );
        $this->validator->validate($arr, $this->interchange);
        $this->assertIdentical($arr, array(
            'ID' => 'N.D',
            'DEFAULT' => 'true',
            '_TYPE' => 'bool',
            '_NULL' => false,
            '_DEFAULT' => true,
        ));
    }
    
    public function testValidateFail() {
        $arr = array(
            'ID' => 'N.D',
            'DEFAULT' => '"asdf"',
            '_TYPE' => 'int',
            '_NULL' => true,
        );
        $this->expectSchemaException('Invalid type for default value in N.D: Expected type int, got string');
        $this->validator->validate($arr, $this->interchange);
    }
    
}
