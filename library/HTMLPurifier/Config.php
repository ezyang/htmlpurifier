<?php

require_once 'HTMLPurifier/ConfigSchema.php';

// member variables
require_once 'HTMLPurifier/HTMLDefinition.php';
require_once 'HTMLPurifier/CSSDefinition.php';
require_once 'HTMLPurifier/Doctype.php';
require_once 'HTMLPurifier/DefinitionCache.php';

// accomodations for versions earlier than 4.3.10 and 5.0.2
// borrowed from PHP_Compat, LGPL licensed, by Aidan Lister <aidan@php.net>
if (!defined('PHP_EOL')) {
    switch (strtoupper(substr(PHP_OS, 0, 3))) {
        case 'WIN':
            define('PHP_EOL', "\r\n");
            break;
        case 'DAR':
            define('PHP_EOL', "\r");
            break;
        default:
            define('PHP_EOL', "\n");
    }
}

/**
 * Configuration object that triggers customizable behavior.
 *
 * @warning This class is strongly defined: that means that the class
 *          will fail if an undefined directive is retrieved or set.
 * 
 * @note Many classes that could (although many times don't) use the
 *       configuration object make it a mandatory parameter.  This is
 *       because a configuration object should always be forwarded,
 *       otherwise, you run the risk of missing a parameter and then
 *       being stumped when a configuration directive doesn't work.
 */
class HTMLPurifier_Config
{
    
    /**
     * HTML Purifier's version
     */
    var $version = '1.6.1';
    
    /**
     * Two-level associative array of configuration directives
     */
    var $conf;
    
    /**
     * Reference HTMLPurifier_ConfigSchema for value checking
     */
    var $def;
    
    /**
     * Cached instance of HTMLPurifier_HTMLDefinition
     */
    var $html_definition;
    
    /**
     * Cached instance of HTMLPurifier_CSSDefinition
     */
    var $css_definition;
    
    /**
     * Bool indicator whether or not config is finalized
     */
    var $finalized = false;
    
    /**
     * Bool indicator whether or not to automatically finalize 
     * the object if a read operation is done
     */
    var $autoFinalize = true;
    
    /**
     * @param $definition HTMLPurifier_ConfigSchema that defines what directives
     *                    are allowed.
     */
    function HTMLPurifier_Config(&$definition) {
        $this->conf = $definition->defaults; // set up, copy in defaults
        $this->def  = $definition; // keep a copy around for checking
    }
    
    /**
     * Convenience constructor that creates a config object based on a mixed var
     * @static
     * @param mixed $config Variable that defines the state of the config
     *                      object. Can be: a HTMLPurifier_Config() object,
     *                      an array of directives based on loadArray(),
     *                      or a string filename of an ini file.
     * @return Configured HTMLPurifier_Config object
     */
    function create($config) {
        if (is_a($config, 'HTMLPurifier_Config')) {
            $config = $config->conf; // create a clone
        }
        $ret = HTMLPurifier_Config::createDefault();
        if (is_string($config)) $ret->loadIni($config);
        elseif (is_array($config)) $ret->loadArray($config);
        return $ret;
    }
    
    /**
     * Convenience constructor that creates a default configuration object.
     * @static
     * @return Default HTMLPurifier_Config object.
     */
    function createDefault() {
        $definition =& HTMLPurifier_ConfigSchema::instance();
        $config = new HTMLPurifier_Config($definition);
        return $config;
    }
    
    /**
     * Retreives a value from the configuration.
     * @param $namespace String namespace
     * @param $key String key
     */
    function get($namespace, $key, $from_alias = false) {
        if (!$this->finalized && $this->autoFinalize) $this->finalize();
        if (!isset($this->def->info[$namespace][$key])) {
            trigger_error('Cannot retrieve value of undefined directive',
                E_USER_WARNING);
            return;
        }
        if ($this->def->info[$namespace][$key]->class == 'alias') {
            trigger_error('Cannot get value from aliased directive, use real name',
                E_USER_ERROR);
            return;
        }
        return $this->conf[$namespace][$key];
    }
    
    /**
     * Retreives an array of directives to values from a given namespace
     * @param $namespace String namespace
     */
    function getBatch($namespace) {
        if (!$this->finalized && $this->autoFinalize) $this->finalize();
        if (!isset($this->def->info[$namespace])) {
            trigger_error('Cannot retrieve undefined namespace',
                E_USER_WARNING);
            return;
        }
        return $this->conf[$namespace];
    }
    
    /**
     * Retrieves all directives, organized by namespace
     */
    function getAll() {
        if (!$this->finalized && $this->autoFinalize) $this->finalize();
        return $this->conf;
    }
    
    /**
     * Sets a value to configuration.
     * @param $namespace String namespace
     * @param $key String key
     * @param $value Mixed value
     */
    function set($namespace, $key, $value, $from_alias = false) {
        if ($this->isFinalized('Cannot set directive after finalization')) return;
        if (!isset($this->def->info[$namespace][$key])) {
            trigger_error('Cannot set undefined directive to value',
                E_USER_WARNING);
            return;
        }
        if ($this->def->info[$namespace][$key]->class == 'alias') {
            if ($from_alias) {
                trigger_error('Double-aliases not allowed, please fix '.
                    'ConfigSchema bug');
            }
            $this->set($this->def->info[$namespace][$key]->namespace,
                       $this->def->info[$namespace][$key]->name,
                       $value, true);
            return;
        }
        $value = $this->def->validate(
                    $value,
                    $this->def->info[$namespace][$key]->type,
                    $this->def->info[$namespace][$key]->allow_null
                 );
        if (is_string($value)) {
            // resolve value alias if defined
            if (isset($this->def->info[$namespace][$key]->aliases[$value])) {
                $value = $this->def->info[$namespace][$key]->aliases[$value];
            }
            if ($this->def->info[$namespace][$key]->allowed !== true) {
                // check to see if the value is allowed
                if (!isset($this->def->info[$namespace][$key]->allowed[$value])) {
                    trigger_error('Value not supported', E_USER_WARNING);
                    return;
                }
            }
        }
        if ($this->def->isError($value)) {
            trigger_error('Value is of invalid type', E_USER_WARNING);
            return;
        }
        $this->conf[$namespace][$key] = $value;
        
        // reset definitions if the directives they depend on changed
        // this is a very costly process, so it's discouraged 
        // with finalization
        if ($namespace == 'HTML') {
            $this->html_definition = null;
        } elseif ($namespace == 'CSS') {
            $this->css_definition = null;
        }
    }
    
    /**
     * Retrieves reference to the HTML definition.
     * @param $raw Return a copy that has not been setup yet. Must be
     *             called before it's been setup, otherwise won't work.
     */
    function &getHTMLDefinition($raw = false) {
        if (!$this->finalized && $this->autoFinalize) $this->finalize();
        $cache = HTMLPurifier_DefinitionCache::create('HTML', $this);
        if($this->checkDefinition($this->html_definition, $cache, $raw)) {
            return $this->html_definition;
        }
        return $this->createDefinition(
            $this->html_definition,
            $cache,
            $raw, 
            new HTMLPurifier_HTMLDefinition()
        );
    }
    
    /**
     * Retrieves reference to the CSS definition
     */
    function &getCSSDefinition($raw = false) {
        if (!$this->finalized && $this->autoFinalize) $this->finalize();
        $cache = HTMLPurifier_DefinitionCache::create('CSS', $this);
        if($this->checkDefinition($this->css_definition, $cache, $raw)) {
            return $this->css_definition;
        }
        return $this->createDefinition(
            $this->css_definition,
            $cache,
            $raw, 
            new HTMLPurifier_CSSDefinition()
        );
    }
    
    /**
     * Checks the variable and cache for an easy-access definition,
     * sets def to variable and returns true if available
     */
    function checkDefinition(&$var, $cache, $raw) {
        if ($raw) return false;
        if (!empty($var)) {
            if (!$var->setup) $var->setup($this);
            return true;
        }
        $var = $cache->get($this);
        return (bool) $var;
    }
    
    /**
     * Generates a new definition, possibly returning it raw, returns
     * reference to variable.
     */
    function &createDefinition(&$var, $cache, $raw, $obj) {
        $var = $obj;
        if ($raw) return $var;
        $var->setup($this);
        $cache->set($var, $this);
        return $var;
    }
    
    /**
     * Loads configuration values from an array with the following structure:
     * Namespace.Directive => Value
     * @param $config_array Configuration associative array
     */
    function loadArray($config_array) {
        if ($this->isFinalized('Cannot load directives after finalization')) return;
        foreach ($config_array as $key => $value) {
            $key = str_replace('_', '.', $key);
            if (strpos($key, '.') !== false) {
                // condensed form
                list($namespace, $directive) = explode('.', $key);
                $this->set($namespace, $directive, $value);
            } else {
                $namespace = $key;
                $namespace_values = $value;
                foreach ($namespace_values as $directive => $value) {
                    $this->set($namespace, $directive, $value);
                }
            }
        }
    }
    
    /**
     * Loads configuration values from an ini file
     * @param $filename Name of ini file
     */
    function loadIni($filename) {
        if ($this->isFinalized('Cannot load directives after finalization')) return;
        $array = parse_ini_file($filename, true);
        $this->loadArray($array);
    }
    
    /**
     * Checks whether or not the configuration object is finalized.
     * @param $error String error message, or false for no error
     */
    function isFinalized($error = false) {
        if ($this->finalized && $error) {
            trigger_error($error, E_USER_ERROR);
        }
        return $this->finalized;
    }
    
    /**
     * Finalizes configuration only if auto finalize is on and not
     * already finalized
     */
    function autoFinalize() {
        if (!$this->finalized && $this->autoFinalize) $this->finalize();
    }
    
    /**
     * Finalizes a configuration object, prohibiting further change
     */
    function finalize() {
        $this->finalized = true;
    }
    
}

?>
