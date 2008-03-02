<?php

class HTMLPurifier_ConfigSchema_InterchangeTest extends UnitTestCase
{
    
    protected $interchange;
    
    public function setup() {
        $this->interchange = new HTMLPurifier_ConfigSchema_Interchange();
    }
    
    public function testAddNamespace() {
        $this->interchange->addNamespace($v = array(
            'ID' => 'Namespace',
            'DESCRIPTION' => 'Bar',
        ));
        $this->assertIdentical($v, $this->interchange->namespaces['Namespace']);
    }
    
    public function testAddDirective() {
        $this->interchange->addDirective($v = array(
            'ID' => 'Namespace.Directive',
            'DESCRIPTION' => 'Bar',
        ));
        $this->assertIdentical($v, $this->interchange->directives['Namespace.Directive']);
    }
    
    public function testValidator() {
        $adapter = $this->interchange->getValidatorAdapter();
        $this->expectException(new HTMLPurifier_ConfigSchema_Exception('ID must exist in directive'));
        $adapter->addDirective(array());
    }
    
}
