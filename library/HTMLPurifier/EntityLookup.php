<?php

class HTMLPurifier_EntityLookup {
    
    var $table;
    
    function HTMLPurifier_EntityLookup() {}
    
    // to enforce Singleton-ness
    function setup($file = false) {
        if (!$file) {
            $file = dirname(__FILE__) . '/EntityLookup/data.txt';
        }
        $this->table = unserialize(file_get_contents($file));
    }
    
    function instance($prototype = false) {
        // no references, since PHP doesn't copy unless modified
        static $instance = null;
        if ($prototype) {
            $instance = $prototype;
        } elseif (!$instance) {
            $instance = new HTMLPurifier_EntityLookup();
            $instance->setup();
        }
        return $instance;
    }
    
}

?>