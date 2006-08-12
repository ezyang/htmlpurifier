<?php

class HTMLPurifier_ConfigDef {
    
    var $info = array();
    
    function initialize() {
        $this->defineNamespace('Core', 'Core features that are always available.');
        $this->defineNamespace('Attr', 'Features regarding attribute validation.');
        $this->defineNamespace('URI', 'Features regarding Uniform Resource Identifiers.');
    }
    
    function &instance($prototype = null) {
        static $instance;
        if ($prototype !== null) {
            $instance = $prototype;
        } elseif ($instance === null || $prototype === true) {
            $instance = new HTMLPurifier_ConfigDef();
            $instance->initialize();
        }
        return $instance;
    }
    
    function define($namespace, $name, $default, $description) {
        $def =& HTMLPurifier_ConfigDef::instance();
        if (!isset($def->info[$namespace])) {
            trigger_error('Cannot define directive for undefined namespace',
                E_USER_ERROR);
            return;
        }
        if (isset($def->info[$namespace][$name])) {
            // this behavior is at risk of change
            trigger_error('Cannot redefine directive', E_USER_ERROR);
            return;
        }
        $def->info[$namespace][$name] = $default;
    }
    
    function defineNamespace($namespace, $description) {
        $def =& HTMLPurifier_ConfigDef::instance();
        if (isset($def->info[$namespace])) {
            trigger_error('Cannot redefine namespace', E_USER_ERROR);
            return;
        }
        $def->info[$namespace] = array();
    }
    
}

?>