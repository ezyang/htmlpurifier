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
        $this->assertIdentical($v, $this->interchange->getNamespace('Namespace'));
    }
    
    public function testAddDirective() {
        $this->interchange->addDirective($v = array(
            'ID' => 'Namespace.Directive',
            'DESCRIPTION' => 'Bar',
        ));
        $this->assertIdentical($v, $this->interchange->getDirective('Namespace.Directive'));
    }
    
}
