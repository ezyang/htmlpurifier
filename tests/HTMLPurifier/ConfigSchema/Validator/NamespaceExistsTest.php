<?php

class HTMLPurifier_ConfigSchema_Validator_NamespaceExistsTest extends HTMLPurifier_ConfigSchema_ValidatorHarness
{
    
    public function testValidateFail() {
        $arr = array('_NAMESPACE' => 'Namespace');
        $this->expectSchemaException('Cannot define directive for undefined namespace Namespace');
        $this->validator->validate($arr, $this->interchange);
    }
    
    public function testValidatePass() {
        $arr = array('_NAMESPACE' => 'Namespace');
        $this->interchange->addNamespace(array('ID' => 'Namespace'));
        $this->validator->validate($arr, $this->interchange);
    }
    
}
