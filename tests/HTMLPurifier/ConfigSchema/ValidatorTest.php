<?php

class HTMLPurifier_ConfigSchema_ValidatorTest extends UnitTestCase
{
    
    protected $interchange, $validator;
    
    public function setup() {
        $this->interchange = new HTMLPurifier_ConfigSchema_Interchange();
        $this->validator   = new HTMLPurifier_ConfigSchema_Validator();
    }
    
    /**
     * Adds a namespace to our interchange object.
     */
    protected function addNamespace($namespace, $description) {
        $obj = new HTMLPurifier_ConfigSchema_Interchange_Namespace();
        $obj->namespace   = $namespace;
        $obj->description = $description;
        $this->interchange->addNamespace($obj);
    }
    
    /**
     * Invokes a validation, so we can fish for exceptions
     */
    protected function validate() {
        $this->validator->validate($this->interchange);
    }
    
    protected function expectValidationException($msg) {
        $this->expectException(new HTMLPurifier_ConfigSchema_Exception($msg));
    }
    
    public function testNamespace() {
        $this->addNamespace('Namespace', 'This is a generic namespace');
        $this->validate();
    }
    
    public function testNamespaceNamespaceString() {
        $this->addNamespace(3, 'Description');
        $this->expectValidationException("Member variable 'namespace' in namespace '3' must be a string");
        $this->validate();
    }
    
    public function testNamespaceNamespaceNotEmpty() {
        $this->addNamespace('0', 'Description');
        $this->expectValidationException("Member variable 'namespace' in namespace '0' must not be empty");
        $this->validate();
    }
    
    public function testNamespaceNamespaceAlnum() {
        $this->addNamespace('%', 'Description');
        $this->expectValidationException("Member variable 'namespace' in namespace '%' must be alphanumeric");
        $this->validate();
    }
    
    public function testNamespaceDescriptionString() {
        $this->addNamespace('Ns', 3);
        $this->expectValidationException("Member variable 'description' in namespace 'Ns' must be a string");
        $this->validate();
    }
    
    public function testNamespaceDescriptionNotEmpty() {
        $this->addNamespace('Ns', '');
        $this->expectValidationException("Member variable 'description' in namespace 'Ns' must not be empty");
        $this->validate();
    }
    
}
