<?php

/**
 * Base decorator class for HTMLPurifier_ConfigSchema_Interchange
 */
class HTMLPurifier_ConfigSchema_Interchange_Validator extends HTMLPurifier_ConfigSchema_Interchange
{
    /**
     * Interchange object this schema is wrapping.
     */
    protected $interchange;
    
    /** @param Object to decorate */
    public function __construct($i = null) {
        $this->decorate($i);
    }
    
    /** Wrap this decorator around an object. */
    public function decorate($i) {
        $this->interchange =  $i;
    }
    
    public function getNamespaces() {
        return $this->interchange->getNamespaces();
    }
    
    public function getDirectives() {
        return $this->interchange->getDirectives();
    }
    
    public function getTypes() {
        return $this->interchange->getTypes();
    }
    
    public function addNamespace($arr) {
        $this->interchange->addNamespace($arr);
    }
    
    public function addDirective($arr) {
        $this->interchange->addNamespace($arr);
    }
    
}
