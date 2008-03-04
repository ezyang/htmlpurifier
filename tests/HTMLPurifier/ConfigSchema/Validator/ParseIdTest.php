<?php

class HTMLPurifier_ConfigSchema_Validator_ParseIdTest extends HTMLPurifier_ConfigSchema_ValidatorHarness
{
    
    public function testValidateNamespace() {
        $arr = array('ID' => 'Namespace');
        $this->validator->validate($arr, $this->interchange);
        $this->assertIdentical($arr, array(
            'ID' => 'Namespace',
            '_NAMESPACE' => 'Namespace'
        ));
    }
    
    public function testValidateDirective() {
        $arr = array('ID' => 'Namespace.Directive');
        $this->validator->validate($arr, $this->interchange);
        $this->assertIdentical($arr, array(
            'ID' => 'Namespace.Directive',
            '_NAMESPACE' => 'Namespace',
            '_DIRECTIVE' => 'Directive'
        ));
    }
    
}
