<?php

if (!defined('HTMLPURIFIER_SCHEMA_STRICT')) define('HTMLPURIFIER_SCHEMA_STRICT', false);

// REMOVE THESE LATER:
require_once 'HTMLPurifier/ConfigDef.php';
require_once 'HTMLPurifier/ConfigDef/Directive.php';
require_once 'HTMLPurifier/ConfigDef/DirectiveAlias.php';
require_once 'HTMLPurifier/ConfigDef/Namespace.php';

/**
 * Configuration definition, defines directives and their defaults.
 * @note If you update this, please update Printer_ConfigForm
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
    public $defaults = array();
    
    /**
     * Definition of the directives.
     */
    public $info = array();
    
    /**
     * Definition of namespaces.
     */
    public $info_namespace = array();
    
    /**
     * Application-wide singleton
     */
    static protected $singleton;
    
    /**
     * Lookup table of allowed types.
     */
    public $types = array(
        'string'    => 'String',
        'istring'   => 'Case-insensitive string',
        'text'      => 'Text',
        'itext'      => 'Case-insensitive text',
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
    public function initialize() {
        $this->defineNamespace('Core', 'Core features that are always available.');
        $this->defineNamespace('Attr', 'Features regarding attribute validation.');
        $this->defineNamespace('URI', 'Features regarding Uniform Resource Identifiers.');
        $this->defineNamespace('HTML', 'Configuration regarding allowed HTML.');
        $this->defineNamespace('CSS', 'Configuration regarding allowed CSS.');
        $this->defineNamespace('AutoFormat', 'Configuration for activating auto-formatting functionality (also known as <code>Injector</code>s)');
        $this->defineNamespace('AutoFormatParam', 'Configuration for customizing auto-formatting functionality');
        $this->defineNamespace('Filter', 'Configuration for filters');
        $this->defineNamespace('Output', 'Configuration relating to the generation of (X)HTML.');
        $this->defineNamespace('Cache', 'Configuration for DefinitionCache and related subclasses.');
        $this->defineNamespace('Test', 'Developer testing configuration for our unit tests.');
    }
    
    /**
     * Retrieves an instance of the application-wide configuration definition.
     */
    public static function &instance($prototype = null) {
        if ($prototype !== null) {
            HTMLPurifier_ConfigSchema::$singleton = $prototype;
        } elseif (HTMLPurifier_ConfigSchema::$singleton === null || $prototype === true) {
            HTMLPurifier_ConfigSchema::$singleton = new HTMLPurifier_ConfigSchema();
            HTMLPurifier_ConfigSchema::$singleton->initialize();
        }
        return HTMLPurifier_ConfigSchema::$singleton;
    }
    
    /** @see HTMLPurifier_ConfigSchema->set() */
    public static function define($namespace, $name, $default, $type, $description) {
        $def =& HTMLPurifier_ConfigSchema::instance();
        $def->add($namespace, $name, $default, $type, $description);
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
    public function add($namespace, $name, $default, $type, $description) {
        // basic sanity checks
        if (HTMLPURIFIER_SCHEMA_STRICT) {
            if (!isset($this->info[$namespace])) {
                trigger_error('Cannot define directive for undefined namespace',
                    E_USER_ERROR);
                return;
            }
            if (!ctype_alnum($name)) {
                trigger_error('Directive name must be alphanumeric',
                    E_USER_ERROR);
                return;
            }
            if (empty($description)) {
                trigger_error('Description must be non-empty',
                    E_USER_ERROR);
                return;
            }
        }
        
        if (isset($this->info[$namespace][$name])) {
            // already defined
            trigger_error('Cannot redefine directive');
            return;
        } else {
            // needs defining
            
            // process modifiers (OPTIMIZE!)
            $type_values = explode('/', $type, 2);
            $type = $type_values[0];
            $modifier = isset($type_values[1]) ? $type_values[1] : false;
            $allow_null = ($modifier === 'null');
            
            if (HTMLPURIFIER_SCHEMA_STRICT) {
                if (!isset($this->types[$type])) {
                    trigger_error('Invalid type for configuration directive',
                        E_USER_ERROR);
                    return;
                }
                $default = $this->validate($default, $type, $allow_null);
                if ($this->isError($default)) {
                    trigger_error('Default value does not match directive type',
                        E_USER_ERROR);
                    return;
                }
            }
            
            $this->info[$namespace][$name] =
                new HTMLPurifier_ConfigDef_Directive();
            $this->info[$namespace][$name]->type = $type;
            $this->info[$namespace][$name]->allow_null = $allow_null;
            $this->defaults[$namespace][$name]   = $default;
        }
        if (!HTMLPURIFIER_SCHEMA_STRICT) return;
        $this->info[$namespace][$name]->description = $description;
    }
    
    /** @see HTMLPurifier_ConfigSchema->addNamespace() */
    public static function defineNamespace($namespace, $description) {
        $def =& HTMLPurifier_ConfigSchema::instance();
        $def->addNamespace($namespace, $description);
    }
    
    /**
     * Defines a namespace for directives to be put into.
     * @param $namespace Namespace's name
     * @param $description Description of the namespace
     */
    public function addNamespace($namespace, $description) {
        if (HTMLPURIFIER_SCHEMA_STRICT) {
            if (isset($this->info[$namespace])) {
                trigger_error('Cannot redefine namespace', E_USER_ERROR);
                return;
            }
            if (!ctype_alnum($namespace)) {
                trigger_error('Namespace name must be alphanumeric',
                    E_USER_ERROR);
                return;
            }
            if (empty($description)) {
                trigger_error('Description must be non-empty',
                    E_USER_ERROR);
                return;
            }
        }
        $this->info[$namespace] = array();
        $this->info_namespace[$namespace] = new HTMLPurifier_ConfigDef_Namespace();
        $this->info_namespace[$namespace]->description = $description;
        $this->defaults[$namespace] = array();
    }
    
    /** @see HTMLPurifier_ConfigSchema->addValueAliases() */
    public static function defineValueAliases($namespace, $name, $aliases) {
        $def =& HTMLPurifier_ConfigSchema::instance();
        $def->addValueAliases($namespace, $name, $aliases);
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
    public function addValueAliases($namespace, $name, $aliases) {
        if (HTMLPURIFIER_SCHEMA_STRICT && !isset($this->info[$namespace][$name])) {
            trigger_error('Cannot set value alias for non-existant directive',
                E_USER_ERROR);
            return;
        }
        foreach ($aliases as $alias => $real) {
            if (HTMLPURIFIER_SCHEMA_STRICT) {
                if (!$this->info[$namespace][$name] !== true &&
                    !isset($this->info[$namespace][$name]->allowed[$real])
                ) {
                    trigger_error('Cannot define alias to value that is not allowed',
                        E_USER_ERROR);
                    return;
                }
                if (isset($this->info[$namespace][$name]->allowed[$alias])) {
                    trigger_error('Cannot define alias over allowed value',
                        E_USER_ERROR);
                    return;
                }
            }
            $this->info[$namespace][$name]->aliases[$alias] = $real;
        }
    }
    
    /** @see HTMLPurifier_ConfigSchema->addAllowedValues() */
    public static function defineAllowedValues($namespace, $name, $allowed_values) {
        $def =& HTMLPurifier_ConfigSchema::instance();
        $def->addAllowedValues($namespace, $name, $allowed_values);
    }
    
    /**
     * Defines a set of allowed values for a directive.
     * @param $namespace Namespace of directive
     * @param $name Name of directive
     * @param $allowed_values Arraylist of allowed values
     */
    public function addAllowedValues($namespace, $name, $allowed_values) {
        if (HTMLPURIFIER_SCHEMA_STRICT && !isset($this->info[$namespace][$name])) {
            trigger_error('Cannot define allowed values for undefined directive',
                E_USER_ERROR);
            return;
        }
        $directive =& $this->info[$namespace][$name];
        $type = $directive->type;
        if (HTMLPURIFIER_SCHEMA_STRICT && $type != 'string' && $type != 'istring') {
            trigger_error('Cannot define allowed values for directive whose type is not string',
                E_USER_ERROR);
            return;
        }
        if ($directive->allowed === true) {
            $directive->allowed = array();
        }
        foreach ($allowed_values as $value) {
            $directive->allowed[$value] = true;
        }
        if (
            HTMLPURIFIER_SCHEMA_STRICT &&
            $this->defaults[$namespace][$name] !== null &&
            !isset($directive->allowed[$this->defaults[$namespace][$name]])
        ) {
            trigger_error('Default value must be in allowed range of variables',
                E_USER_ERROR);
            $directive->allowed = true; // undo undo!
            return;
        }
    }
    
    /** @see HTMLPurifier_ConfigSchema->addAlias() */
    public static function defineAlias($namespace, $name, $new_namespace, $new_name) {
        $def =& HTMLPurifier_ConfigSchema::instance();
        $def->addAlias($namespace, $name, $new_namespace, $new_name);
    }
    
    /**
     * Defines a directive alias for backwards compatibility
     * @param $namespace
     * @param $name Directive that will be aliased
     * @param $new_namespace
     * @param $new_name Directive that the alias will be to
     */
    public function addAlias($namespace, $name, $new_namespace, $new_name) {
        if (HTMLPURIFIER_SCHEMA_STRICT) {
            if (!isset($this->info[$namespace])) {
                trigger_error('Cannot define directive alias in undefined namespace',
                    E_USER_ERROR);
                return;
            }
            if (!ctype_alnum($name)) {
                trigger_error('Directive name must be alphanumeric',
                    E_USER_ERROR);
                return;
            }
            if (isset($this->info[$namespace][$name])) {
                trigger_error('Cannot define alias over directive',
                    E_USER_ERROR);
                return;
            }
            if (!isset($this->info[$new_namespace][$new_name])) {
                trigger_error('Cannot define alias to undefined directive',
                    E_USER_ERROR);
                return;
            }
            if ($this->info[$new_namespace][$new_name]->class == 'alias') {
                trigger_error('Cannot define alias to alias',
                    E_USER_ERROR);
                return;
            }
        }
        $this->info[$namespace][$name] =
            new HTMLPurifier_ConfigDef_DirectiveAlias(
                $new_namespace, $new_name);
        $this->info[$new_namespace][$new_name]->directiveAliases[] = "$namespace.$name";
    }
    
    /**
     * Validate a variable according to type. Return null if invalid.
     * @todo Consider making protected
     */
    public function validate($var, $type, $allow_null = false) {
        if (!isset($this->types[$type])) {
            trigger_error('Invalid type', E_USER_ERROR);
            return;
        }
        if ($allow_null && $var === null) return null;
        switch ($type) {
            case 'mixed':
                //if (is_string($var)) $var = unserialize($var);
                return $var;
            case 'istring':
            case 'string':
            case 'text': // no difference, just is longer/multiple line string
            case 'itext':
                if (!is_string($var)) break;
                if ($type === 'istring' || $type === 'itext') $var = strtolower($var);
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
                    // special case: technically, this is an array with
                    // a single empty string item, but having an empty
                    // array is more intuitive
                    if ($var == '') return array();
                    if (strpos($var, "\n") === false && strpos($var, "\r") === false) {
                        // simplistic string to array method that only works
                        // for simple lists of tag names or alphanumeric characters
                        $var = explode(',',$var);
                    } else {
                        $var = preg_split('/(,|[\n\r]+)/', $var);
                    }
                    // remove spaces
                    foreach ($var as $i => $j) $var[$i] = trim($j);
                    if ($type === 'hash') {
                        // key:value,key2:value2
                        $nvar = array();
                        foreach ($var as $keypair) {
                            $c = explode(':', $keypair, 2);
                            if (!isset($c[1])) continue;
                            $nvar[$c[0]] = $c[1];
                        }
                        $var = $nvar;
                    }
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
     * @todo Consider making protected
     * @param $filename Filename to check
     * @return string munged filename
     */
    public function mungeFilename($filename) {
        if (!HTMLPURIFIER_SCHEMA_STRICT) return $filename;
        $offset = strrpos($filename, 'HTMLPurifier');
        $filename = substr($filename, $offset);
        $filename = str_replace('\\', '/', $filename);
        return $filename;
    }
    
    /**
     * Checks if var is an HTMLPurifier_Error object
     * @todo Consider making protected
     */
    public function isError($var) {
        if (!is_object($var)) return false;
        if (!($var instanceof HTMLPurifier_Error)) return false;
        return true;
    }
}


