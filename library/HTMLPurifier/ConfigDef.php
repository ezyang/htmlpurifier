<?php

/**
 * Configuration definition, defines directives and their defaults.
 * @todo Build documentation generation capabilities.
 */
class HTMLPurifier_ConfigDef {
    
    /**
     * Currently defined directives (and namespaces).
     * @note This shares the exact same structure as HTMLPurifier_Config::$conf
     */
    var $info = array();
    
    /**
     * Initializes the default namespaces.
     */
    function initialize() {
        $this->defineNamespace('Core', 'Core features that are always available.');
        $this->defineNamespace('Attr', 'Features regarding attribute validation.');
        $this->defineNamespace('URI', 'Features regarding Uniform Resource Identifiers.');
    }
    
    /**
     * Retrieves an instance of the application-wide configuration definition.
     */
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
    
    /**
     * Defines a directive for configuration
     * @warning Will fail of directive's namespace is defined
     * @todo Collect information on description and allow redefinition
     *       so that multiple files can register a dependency on a
     *       configuration directive.
     * @param $namespace Namespace the directive is in
     * @param $name Key of directive
     * @param $default Default value of directive
     * @param $description Description of directive for documentation
     */
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
    
    /**
     * Defines a namespace for directives to be put into.
     * @param $namespace Namespace's name
     * @param $description Description of the namespace
     */
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