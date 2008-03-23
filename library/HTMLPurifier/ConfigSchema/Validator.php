<?php

/**
 * Performs validations on HTMLPurifier_ConfigSchema_Interchange
 */
class HTMLPurifier_ConfigSchema_Validator
{
    
    protected $interchange;
    
    /**
     * Context-stack to provide easy to read error messages.
     */
    protected $context = array();
    
    /**
     * HTMLPurifier_VarParser to test variable types.
     */
    protected $parser;
    
    public function __construct() {
        $this->parser = new HTMLPurifier_VarParser();
    }
    
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
        $this->context[] = "namespace '{$n->namespace}'";
        $this->with($n, 'namespace')
            ->assertNotEmpty()
            ->assertAlnum();
        $this->with($n, 'description')
            ->assertNotEmpty()
            ->assertIsString(); // technically redundant
        array_pop($this->context);
    }
    
    public function validateId($id) {
        $this->context[] = "id '$id'";
        if (!isset($this->interchange->namespaces[$id->namespace])) {
            $this->error('namespace', 'does not exist');
        }
        $this->with($id, 'namespace')
            ->assertNotEmpty()
            ->assertAlnum();
        $this->with($id, 'directive')
            ->assertNotEmpty()
            ->assertAlnum();
        array_pop($this->context);
    }
    
    public function validateDirective($d) {
        $this->context[] = "directive '{$d->id}'";
        $this->validateId($d->id);
        $this->with($d, 'description')
            ->assertNotEmpty();
        $this->with($d, 'type')
            ->assertNotEmpty();
        if (!isset(HTMLPurifier_VarParser::$types[$d->type])) {
            $this->error('type', 'is invalid');
        }
        $this->parser->parse($d->default, $d->type, $d->typeAllowsNull);
        
        array_pop($this->context);
    }
    
    // protected helper functions
    
    protected function with($obj, $member) {
        return new HTMLPurifier_ConfigSchema_ValidatorAtom($this->getFormattedContext(), $obj, $member);
    }
    
    protected function error($target, $msg) {
        throw new HTMLPurifier_ConfigSchema_Exception(ucfirst($target) . ' in ' . $this->getFormattedContext() . ' ' . $msg);
    }
    
    protected function getFormattedContext() {
        return implode(' in ', array_reverse($this->context));
    }
    
}
