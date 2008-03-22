<?php

/**
 * Fluent interface for validating the contents of member variables.
 * This should be immutable. See HTMLPurifier_ConfigSchema_Validator for
 * use-cases.
 */
class HTMLPurifier_ConfigSchema_ValidatorAtom
{
    
    protected $interchange, $context, $obj, $member, $contents;
    
    public function __construct($interchange, $context, $obj, $member) {
        $this->interchange = $interchange;
        $this->context     = $context;
        $this->obj         = $obj;
        $this->member      = $member;
        $this->contents    =& $obj->$member;
    }
    
    public function assertIsString() {
        if (!is_string($this->contents)) $this->error('must be a string');
        return $this;
    }
    
    public function assertNotNull() {
        if (is_null($this->contents)) $this->error('must not be null');
        return $this;
    }
    
    public function assertAlnum() {
        if (!ctype_alnum($this->contents)) $this->error('must be alphanumeric');
        return $this;
    }
    
    public function assertNotEmpty() {
        if (empty($this->contents)) $this->error('must not be empty');
        return $this;
    }
    
    protected function error($msg) {
        throw new HTMLPurifier_ConfigSchema_Exception('Member variable \'' . $this->member . '\' in ' . $this->context . ' ' . $msg);
    }
    
}


