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
            'Foo' => 'Bar',
        ));
        $this->assertIdentical($v, $this->interchange->namespaces['Namespace']);
    }
    
    public function testAddNamespaceError() {
        try {
            $this->interchange->addNamespace(array());
        } catch (HTMLPurifier_ConfigSchema_Exception $e) {
            $this->assertIdentical($e->getMessage(), 'Namespace must have ID');
        }
    }
    
    public function testAddDirective() {
        $this->interchange->addDirective($v = array(
            'ID' => 'Namespace.Directive',
            'Foo' => 'Bar',
        ));
        $this->assertIdentical($v, $this->interchange->directives['Namespace.Directive']);
    }
    
    public function testAddDirectiveError() {
        try {
            $this->interchange->addDirective(array());
        } catch (HTMLPurifier_ConfigSchema_Exception $e) {
            $this->assertIdentical($e->getMessage(), 'Directive must have ID');
        }
    }
    
}
