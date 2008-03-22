<?php

/**
 * Represents a directive ID in the interchange format.
 */
class HTMLPurifier_ConfigSchema_Interchange_Id
{
    
    public $namespace, $directive;
    
    public function __construct($namespace, $directive) {
        $this->namespace = $namespace;
        $this->directive = $directive;
    }
    
    public function __toString() {
        return $this->namespace . '.' . $this->directive;
    }
    
    public static function make($id) {
        list($namespace, $directive) = explode('.', $id);
        return new HTMLPurifier_ConfigSchema_Interchange_Id($namespace, $directive);
    }
    
}
