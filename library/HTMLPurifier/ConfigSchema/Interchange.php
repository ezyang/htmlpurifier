<?php

/**
 * Generic schema interchange format that can be converted to a runtime
 * representation (HTMLPurifier_ConfigSchema) or HTML documentation. Members
 * are completely validated.
 */
class HTMLPurifier_ConfigSchema_Interchange
{
    
    /**
     * Array of Namespace ID => array(namespace info)
     */
    public $namespaces;
    
    /**
     * Array of Directive ID => array(directive info)
     */
    public $directives;
    
    /**
     * Adds a namespace array to $namespaces
     */
    public function addNamespace($namespace) {
        $this->namespaces[$namespace->namespace] = $namespace;
    }
    
    /**
     * Adds a directive array to $directives
     */
    public function addDirective($directive) {
        $this->directives[(string) $directive->id] = $directive;
    }
    
}
