<?php

/**
 * Performs validations on HTMLPurifier_ConfigSchema_Interchange
 */
class HTMLPurifier_ConfigSchema_Validator
{
    
    protected $interchange;
    
    /**
     * Volatile context variables to provide a fluent interface.
     */
    protected $context, $obj, $member;
    
    /**
     * Validates a fully-formed interchange object. Throws an
     * HTMLPurifier_ConfigSchema_Exception if there's a problem.
     */
    public function validate($interchange) {
        $this->interchange = $interchange;
        foreach ($interchange->namespaces as $namespace) {
            $this->validateNamespace($namespace);
        }
        foreach ($interchange->directives as $directive) {
            $this->validateDirective($directive);
        }
    }
    
    public function validateNamespace($n) {
        $this->context = "namespace '{$n->namespace}'";
        $this->with($n, 'namespace')
            ->assertNotEmpty()
            ->assertAlnum();
        $this->with($n, 'description')
            ->assertIsString()
            ->assertNotEmpty();
    }
    
    public function validateDirective($d) {
        $this->context = "directive '{$d->id}'";
        $this->validateId($d->id);
    }
    
    public function validateId($id) {
        $this->context = "id '$id'";
        $this->with($id, 'namespace')
            ->assertNotEmpty()
            ->assertAlnum();
        $this->with($id, 'directive')
            ->assertNotEmpty()
            ->assertAlnum();
    }
    
    // protected helper functions
    
    protected function with($obj, $member) {
        return new HTMLPurifier_ConfigSchema_ValidatorAtom($this->context, $obj, $member);
    }
    
    protected function error($msg) {
        throw new HTMLPurifier_ConfigSchema_Exception($msg . ' in ' . $this->context);
    }
    
}
