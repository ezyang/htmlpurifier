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
        return $obj;
    }
    
    protected function addDirective($namespace, $directive, $type = 'string', $description = 'Description') {
        $obj = new HTMLPurifier_ConfigSchema_Interchange_Directive();
        $obj->id = $this->makeId($namespace, $directive);
        $obj->type = $type;
        $obj->description = $description;
        $this->interchange->addDirective($obj);
        return $obj; // for future editing
    }
    
    protected function makeId($namespace, $directive) {
        return new HTMLPurifier_ConfigSchema_Interchange_Id($namespace, $directive);
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
    
    public function testNamespaceDescriptionNotEmpty() {
        $this->addNamespace('Ns', '');
        $this->expectValidationException("Member variable 'description' in namespace 'Ns' must not be empty");
        $this->validate();
    }
    
    public function testDirectiveIdNamespaceNotEmpty() {
        $this->addDirective('', 'Dir');
    }
    
}
