<?php

class HTMLPurifier_ConfigSchema_ValidatorHarness extends UnitTestCase
{
    
    protected $interchange, $validator;
    
    public function setup() {
        $this->interchange = new HTMLPurifier_ConfigSchema_Interchange();
        if (empty($this->validator)) {
            $class_to_test = substr(get_class($this), 0, -4);
            $this->validator = new $class_to_test;
        }
    }
    
    public function teardown() {
        unset($this->validator, $this->interchange);
    }
    
    protected function expectSchemaException($msg) {
        $this->expectException(new HTMLPurifier_ConfigSchema_Exception($msg));
    }
    
}
