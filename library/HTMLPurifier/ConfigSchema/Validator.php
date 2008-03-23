<?php

/**
 * Performs validations on HTMLPurifier_ConfigSchema_Interchange
 *
 * @note If you see '// handled by InterchangeBuilder', that means a
 *       design decision in that class would prevent this validation from
 *       ever being necessary. We have them anyway, however, for
 *       redundancy.
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
        // PHP is a bit lax with integer <=> string conversions in
        // arrays, so we don't use the identical !== comparison
        foreach ($interchange->namespaces as $i => $namespace) {
            if ($i != $namespace->namespace) $this->error(false, "Integrity violation: key '$i' does not match internal id '{$namespace->namespace}'");
            $this->validateNamespace($namespace);
        }
        foreach ($interchange->directives as $i => $directive) {
            $id = $directive->id->toString();
            if ($i != $id) $this->error(false, "Integrity violation: key '$i' does not match internal id '$id'");
            $this->validateDirective($directive);
        }
    }
    
    public function validateNamespace($n) {
        $this->context[] = "namespace '{$n->namespace}'";
        $this->with($n, 'namespace')
            ->assertNotEmpty()
            ->assertAlnum(); // implicit assertIsString handled by InterchangeBuilder
        $this->with($n, 'description')
            ->assertNotEmpty()
            ->assertIsString(); // handled by InterchangeBuilder
        array_pop($this->context);
    }
    
    public function validateId($id) {
        $id_string = $id->toString();
        $this->context[] = "id '$id_string'";
        if (!$id instanceof HTMLPurifier_ConfigSchema_Interchange_Id) {
            // handled by InterchangeBuilder
            $this->error(false, 'is not an instance of HTMLPurifier_ConfigSchema_Interchange_Id');
        }
        if (!isset($this->interchange->namespaces[$id->namespace])) {
            $this->error('namespace', 'does not exist'); // assumes that the namespace was validated already
        }
        $this->with($id, 'directive')
            ->assertNotEmpty()
            ->assertAlnum(); // implicit assertIsString handled by InterchangeBuilder
        array_pop($this->context);
    }
    
    public function validateDirective($d) {
        $id = $d->id->toString();
        $this->context[] = "directive '$id'";
        $this->validateId($d->id);
        $this->with($d, 'description')
            ->assertNotEmpty();
        $this->with($d, 'type')
            ->assertNotEmpty(); // handled by InterchangeBuilder
        // Much stricter default check, since we're using the base implementation.
        // handled by InterchangeBuilder
        try {
            $this->parser->parse($d->default, $d->type, $d->typeAllowsNull);
        } catch (HTMLPurifier_VarParserException $e) {
            $this->error('default', 'had error: ' . $e->getMessage());
        }
        
        array_pop($this->context);
    }
    
    // protected helper functions
    
    protected function with($obj, $member) {
        return new HTMLPurifier_ConfigSchema_ValidatorAtom($this->getFormattedContext(), $obj, $member);
    }
    
    protected function error($target, $msg) {
        if ($target !== false) $prefix = ucfirst($target) . ' in ' .  $this->getFormattedContext();
        else $prefix = ucfirst($this->getFormattedContext());
        throw new HTMLPurifier_ConfigSchema_Exception(trim($prefix . ' ' . $msg));
    }
    
    protected function getFormattedContext() {
        return implode(' in ', array_reverse($this->context));
    }
    
}
