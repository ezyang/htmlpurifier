<?php

if (!defined('HTMLPURIFIER_SCHEMA_STRICT')) define('HTMLPURIFIER_SCHEMA_STRICT', false);

/**
 * Configuration definition, defines directives and their defaults.
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
     * Variable parser.
     */
    protected $parser;
    
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
    
    public function __construct() {
        $this->parser = new HTMLPurifier_VarParser();
    }
    
    /**
     * Unserializes the default ConfigSchema.
     */
    public static function makeFromSerial() {
        return unserialize(file_get_contents(HTMLPURIFIER_PREFIX . '/HTMLPurifier/ConfigSchema/schema.ser'));
    }
    
    /**
     * Retrieves an instance of the application-wide configuration definition.
     */
    public static function &instance($prototype = null) {
        if ($prototype !== null) {
            HTMLPurifier_ConfigSchema::$singleton = $prototype;
        } elseif (HTMLPurifier_ConfigSchema::$singleton === null || $prototype === true) {
            HTMLPurifier_ConfigSchema::$singleton = HTMLPurifier_ConfigSchema::makeFromSerial();
        }
        return HTMLPurifier_ConfigSchema::$singleton;
    }
    
    /**
     * Throws an E_USER_NOTICE stating that a method is deprecated.
     */
    private static function deprecated($method) {
        trigger_error("Static HTMLPurifier_ConfigSchema::$method deprecated, use add*() method instead", E_USER_NOTICE);
    }
    
    /** @see HTMLPurifier_ConfigSchema->set() */
    public static function define($namespace, $name, $default, $type, $description) {
        HTMLPurifier_ConfigSchema::deprecated(__METHOD__);
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
                try {
                    $default = $this->parser->parse($default, $type, $allow_null);
                } catch (HTMLPurifier_VarParserException $e) {
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
        HTMLPurifier_ConfigSchema::deprecated(__METHOD__);
        $def =& HTMLPurifier_ConfigSchema::instance();
        $def->addNamespace($namespace, $description);
    }
    
    /**
     * Defines a namespace for directives to be put into.
     * @param $namespace Namespace's name
     * @param $description Description of the namespace
     */
    public function addNamespace($namespace, $description) {
        $this->info[$namespace] = array();
        $this->info_namespace[$namespace] = new HTMLPurifier_ConfigDef_Namespace();
        $this->info_namespace[$namespace]->description = $description;
        $this->defaults[$namespace] = array();
    }
    
    /** @see HTMLPurifier_ConfigSchema->addValueAliases() */
    public static function defineValueAliases($namespace, $name, $aliases) {
        HTMLPurifier_ConfigSchema::deprecated(__METHOD__);
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
        HTMLPurifier_ConfigSchema::deprecated(__METHOD__);
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
        HTMLPurifier_ConfigSchema::deprecated(__METHOD__);
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
    
    /** @deprecated, use HTMLPurifier_VarParser->parse() */
    public function validate($a, $b, $c = false) {
        trigger_error("HTMLPurifier_ConfigSchema->validate deprecated, use HTMLPurifier_VarParser->parse instead", E_USER_NOTICE);
        return $this->parser->parse($a, $b, $c);
    }
    
}


