<?php

require_once 'HTMLPurifier/Error.php';

/**
 * Configuration definition, defines directives and their defaults.
 * @todo The ability to define things multiple times is confusing and should
 *       be factored out to its own function named registerDependency() or 
 *       addNote(), where only the namespace.name and an extra descriptions
 *       documenting the nature of the dependency are needed.  Since it's
 *       possible that the dependency is registered before the configuration
 *       is defined, deferring it to some sort of cache until it actually
 *       gets defined would be wise, keeping it opaque until it does get
 *       defined. We could add a finalize() method which would cause it to
 *       error out if we get a dangling dependency.  It's difficult, however,
 *       to know whether or not it's a dependency, or a codependency, that is
 *       neither of them fully depends on it. Where does the configuration go
 *       then?  This could be partially resolved by allowing blanket definitions
 *       and then splitting them up into finer-grained versions, however, there
 *       might be implementation difficulties in ini files regarding order of
 *       execution.
 */
class HTMLPurifier_ConfigSchema {
    
    /**
     * Defaults of the directives and namespaces.
     * @note This shares the exact same structure as HTMLPurifier_Config::$conf
     */
    var $defaults = array();
    
    /**
     * Definition of the directives.
     */
    var $info = array();
    
    /**
     * Definition of namespaces.
     */
    var $info_namespace = array();
    
    /**
     * Lookup table of allowed types.
     */
    var $types = array(
        'string'    => 'String',
        'istring'   => 'Case-insensitive string',
        'int'       => 'Integer',
        'float'     => 'Float',
        'bool'      => 'Boolean',
        'lookup'    => 'Lookup array',
        'list'      => 'Array list',
        'hash'      => 'Associative array',
        'mixed'     => 'Mixed'
    );
    
    /**
     * Initializes the default namespaces.
     */
    function initialize() {
        $this->defineNamespace('Core', 'Core features that are always available.');
        $this->defineNamespace('Attr', 'Features regarding attribute validation.');
        $this->defineNamespace('URI', 'Features regarding Uniform Resource Identifiers.');
        $this->defineNamespace('HTML', 'Configuration regarding allowed HTML.');
        $this->defineNamespace('CSS', 'Configuration regarding allowed CSS.');
        $this->defineNamespace('Test', 'Developer testing configuration for our unit tests.');
    }
    
    /**
     * Retrieves an instance of the application-wide configuration definition.
     */
    function &instance($prototype = null) {
        static $instance;
        if ($prototype !== null) {
            $instance = $prototype;
        } elseif ($instance === null || $prototype === true) {
            $instance = new HTMLPurifier_ConfigSchema();
            $instance->initialize();
        }
        return $instance;
    }
    
    /**
     * Defines a directive for configuration
     * @warning Will fail of directive's namespace is defined
     * @param $namespace Namespace the directive is in
     * @param $name Key of directive
     * @param $default Default value of directive
     * @param $type Allowed type of the directive. See
     *      HTMLPurifier_DirectiveDef::$type for allowed values
     * @param $description Description of directive for documentation
     */
    function define(
        $namespace, $name, $default, $type, 
        $description
    ) {
        $def =& HTMLPurifier_ConfigSchema::instance();
        if (!isset($def->info[$namespace])) {
            trigger_error('Cannot define directive for undefined namespace',
                E_USER_ERROR);
            return;
        }
        if (!ctype_alnum($name)) {
            trigger_error('Directive name must be alphanumeric',
                E_USER_ERROR);
            return;
        }
        if (isset($def->info[$namespace][$name])) {
            if (
                $def->info[$namespace][$name]->type !== $type ||
                $def->defaults[$namespace][$name]   !== $default
            ) {
                trigger_error('Inconsistent default or type, cannot redefine');
                return;
            }
        } else {
            // process modifiers
            $type_values = explode('/', $type, 2);
            $type = $type_values[0];
            $modifier = isset($type_values[1]) ? $type_values[1] : false;
            $allow_null = ($modifier === 'null');
            
            if (!isset($def->types[$type])) {
                trigger_error('Invalid type for configuration directive',
                    E_USER_ERROR);
                return;
            }
            $default = $def->validate($default, $type, $allow_null);
            if ($def->isError($default)) {
                trigger_error('Default value does not match directive type',
                    E_USER_ERROR);
                return;
            }
            $def->info[$namespace][$name] =
                new HTMLPurifier_ConfigEntity_Directive();
            $def->info[$namespace][$name]->type = $type;
            $def->info[$namespace][$name]->allow_null = $allow_null;
            $def->defaults[$namespace][$name]   = $default;
        }
        $backtrace = debug_backtrace();
        $file = $def->mungeFilename($backtrace[0]['file']);
        $line = $backtrace[0]['line'];
        $def->info[$namespace][$name]->addDescription($file,$line,$description);
    }
    
    /**
     * Defines a namespace for directives to be put into.
     * @param $namespace Namespace's name
     * @param $description Description of the namespace
     */
    function defineNamespace($namespace, $description) {
        $def =& HTMLPurifier_ConfigSchema::instance();
        if (isset($def->info[$namespace])) {
            trigger_error('Cannot redefine namespace', E_USER_ERROR);
            return;
        }
        if (!ctype_alnum($namespace)) {
            trigger_error('Namespace name must be alphanumeric',
                E_USER_ERROR);
            return;
        }
        $def->info[$namespace] = array();
        $def->info_namespace[$namespace] = new HTMLPurifier_ConfigEntity_Namespace();
        $def->info_namespace[$namespace]->description = $description;
        $def->defaults[$namespace] = array();
    }
    
    /**
     * Defines a directive value alias.
     * 
     * Directive value aliases are convenient for developers because it lets
     * them set a directive to several values and get the same result.
     * @param $namespace Directive's namespace
     * @param $name Name of Directive
     * @param $alias Name of aliased value
     * @param $real Value aliased value will be converted into
     */
    function defineValueAliases($namespace, $name, $aliases) {
        $def =& HTMLPurifier_ConfigSchema::instance();
        if (!isset($def->info[$namespace][$name])) {
            trigger_error('Cannot set value alias for non-existant directive',
                E_USER_ERROR);
            return;
        }
        foreach ($aliases as $alias => $real) {
            if (!$def->info[$namespace][$name] !== true &&
                !isset($def->info[$namespace][$name]->allowed[$real])
            ) {
                trigger_error('Cannot define alias to value that is not allowed',
                    E_USER_ERROR);
                return;
            }
            if (isset($def->info[$namespace][$name]->allowed[$alias])) {
                trigger_error('Cannot define alias over allowed value',
                    E_USER_ERROR);
                return;
            }
            $def->info[$namespace][$name]->aliases[$alias] = $real;
        }
    }
    
    /**
     * Defines a set of allowed values for a directive.
     * @param $namespace Namespace of directive
     * @param $name Name of directive
     * @param $allowed_values Arraylist of allowed values
     */
    function defineAllowedValues($namespace, $name, $allowed_values) {
        $def =& HTMLPurifier_ConfigSchema::instance();
        if (!isset($def->info[$namespace][$name])) {
            trigger_error('Cannot define allowed values for undefined directive',
                E_USER_ERROR);
            return;
        }
        if ($def->info[$namespace][$name]->allowed === true) {
            $def->info[$namespace][$name]->allowed = array();
        }
        foreach ($allowed_values as $value) {
            $def->info[$namespace][$name]->allowed[$value] = true;
        }
    }
    
    /**
     * Validate a variable according to type. Return null if invalid.
     */
    function validate($var, $type, $allow_null = false) {
        if (!isset($this->types[$type])) {
            trigger_error('Invalid type', E_USER_ERROR);
            return;
        }
        if ($allow_null && $var === null) return null;
        switch ($type) {
            case 'mixed':
                return $var;
            case 'istring':
            case 'string':
                if (!is_string($var)) break;
                if ($type === 'istring') $var = strtolower($var);
                return $var;
            case 'int':
                if (is_string($var) && ctype_digit($var)) $var = (int) $var;
                elseif (!is_int($var)) break;
                return $var;
            case 'float':
                if (is_string($var) && is_numeric($var)) $var = (float) $var;
                elseif (!is_float($var)) break;
                return $var;
            case 'bool':
                if (is_int($var) && ($var === 0 || $var === 1)) {
                    $var = (bool) $var;
                } elseif (is_string($var)) {
                    if ($var == 'on' || $var == 'true' || $var == '1') {
                        $var = true;
                    } elseif ($var == 'off' || $var == 'false' || $var == '0') {
                        $var = false;
                    } else {
                        break;
                    }
                } elseif (!is_bool($var)) break;
                return $var;
            case 'list':
            case 'hash':
            case 'lookup':
                if (is_string($var)) {
                    // simplistic string to array method that only works
                    // for simple lists of tag names or alphanumeric characters
                    $var = explode(',',$var);
                    // remove spaces
                    foreach ($var as $i => $j) $var[$i] = trim($j);
                }
                if (!is_array($var)) break;
                $keys = array_keys($var);
                if ($keys === array_keys($keys)) {
                    if ($type == 'list') return $var;
                    elseif ($type == 'lookup') {
                        $new = array();
                        foreach ($var as $key) {
                            $new[$key] = true;
                        }
                        return $new;
                    } else break;
                }
                if ($type === 'lookup') {
                    foreach ($var as $key => $value) {
                        $var[$key] = true;
                    }
                }
                return $var;
        }
        $error = new HTMLPurifier_Error();
        return $error;
    }
    
    /**
     * Takes an absolute path and munges it into a more manageable relative path
     */
    function mungeFilename($filename) {
        $offset = strrpos($filename, 'HTMLPurifier');
        $filename = substr($filename, $offset);
        $filename = str_replace('\\', '/', $filename);
        return $filename;
    }
    
    /**
     * Checks if var is an HTMLPurifier_Error object
     */
    function isError($var) {
        if (!is_object($var)) return false;
        if (!is_a($var, 'HTMLPurifier_Error')) return false;
        return true;
    }
}

/**
 * Base class for configuration entity
 */
class HTMLPurifier_ConfigEntity {}

/**
 * Structure object describing of a namespace
 */
class HTMLPurifier_ConfigEntity_Namespace extends HTMLPurifier_ConfigEntity {
    
    /**
     * String description of what kinds of directives go in this namespace.
     */
    var $description;
    
}

/**
 * Structure object containing definition of a directive.
 * @note This structure does not contain default values
 */
class HTMLPurifier_ConfigEntity_Directive extends HTMLPurifier_ConfigEntity
{
    
    /**
     * Hash of value aliases, i.e. values that are equivalent.
     */
    var $aliases = array();
    
    /**
     * Lookup table of allowed values of the element, bool true if all allowed.
     */
    var $allowed = true;
    
    /**
     * Allowed type of the directive. Values are:
     *      - string
     *      - istring (case insensitive string)
     *      - int
     *      - float
     *      - bool
     *      - lookup (array of value => true)
     *      - list (regular numbered index array)
     *      - hash (array of key => value)
     *      - mixed (anything goes)
     */
    var $type = 'mixed';
    
    /**
     * Is null allowed? Has no affect for mixed type.
     * @bool
     */
    var $allow_null = false;
    
    /**
     * Plaintext descriptions of the configuration entity is. Organized by
     * file and line number, so multiple descriptions are allowed.
     */
    var $descriptions = array();
    
    /**
     * Adds a description to the array
     */
    function addDescription($file, $line, $description) {
        if (!isset($this->descriptions[$file])) $this->descriptions[$file] = array();
        $this->descriptions[$file][$line] = $description;
    }
    
}

?>