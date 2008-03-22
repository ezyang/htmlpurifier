<?php

class HTMLPurifier_ConfigSchema_ValidatorAtomTest extends UnitTestCase
{
    
    protected function expectValidationException($msg) {
        $this->expectException(new HTMLPurifier_ConfigSchema_Exception($msg));
    }
    
    public function makeAtom($value) {
        $obj = new stdClass();
        $obj->property = $value;
        // Note that 'property' and 'context' are magic wildcard values
        return new HTMLPurifier_ConfigSchema_ValidatorAtom('context', $obj, 'property');
    }
    
    public function testAssertIsString() {
        $this->makeAtom('foo')->assertIsString();
    }
    
    public function testAssertIsStringFail() {
        $this->expectValidationException("Member variable 'property' in context must be a string");
        $this->makeAtom(3)->assertIsString();
    }
    
    public function testAssertNotNull() {
        $this->makeAtom('foo')->assertNotNull();
    }
    
    public function testAssertNotNullFail() {
        $this->expectValidationException("Member variable 'property' in context must not be null");
        $this->makeAtom(null)->assertNotNull();
    }
    
    public function testAssertAlnum() {
        $this->makeAtom('foo2')->assertAlnum();
    }
    
    public function testAssertAlnumFail() {
        $this->expectValidationException("Member variable 'property' in context must be alphanumeric");
        $this->makeAtom('%a')->assertAlnum();
    }
    
    public function testAssertAlnumFailIsString() {
        $this->expectValidationException("Member variable 'property' in context must be a string");
        $this->makeAtom(3)->assertAlnum();
    }
    
    public function testAssertNotEmpty() {
        $this->makeAtom('foo')->assertNotEmpty();
    }
    
    public function testAssertNotEmptyFail() {
        $this->expectValidationException("Member variable 'property' in context must not be empty");
        $this->makeAtom('')->assertNotEmpty();
    }
    
}
