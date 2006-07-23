<?php

class HTMLPurifier_EntityLookup {
    
    var $table;
    
    function HTMLPurifier_EntityLookup($file = false) {
        if (!$file) {
            $file = dirname(__FILE__) . '/EntityLookup/data.txt';
        }
        $this->table = unserialize(file_get_contents($file));
    }
    
    function instance() {
        // no references, since PHP doesn't copy unless modified
        static $instance = null;
        if (!$instance) {
            $instance = new HTMLPurifier_EntityLookup();
        }
        return $instance;
    }
    
}

?>