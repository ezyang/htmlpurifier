<?php

class HTMLPurifier_ConfigSchema_InterchangeBuilder
{
    
    protected $varParser;
    
    public function __construct($varParser = null) {
        $this->varParser = $varParser ? $varParser : new HTMLPurifier_VarParser_Native();
    }
    
    /**
     * Builds an interchange object based on a hash.
     * @param $interchange HTMLPurifier_ConfigSchema_Interchange object to build
     * @param $hash HTMLPurifier_ConfigSchema_StringHash source data
     */
    public function build($interchange, $hash) {
        if (strpos($hash['ID'], '.') === false) {
            $this->buildNamespace($interchange, $hash);
        } else {
            $this->buildDirective($interchange, $hash);
        }
        $this->_findUnused($hash);
    }
    
    public function buildNamespace($interchange, $hash) {
        $namespace = new HTMLPurifier_ConfigSchema_Interchange_Namespace();
        $namespace->namespace   = $hash->offsetGet('ID');
        $namespace->description = $hash->offsetGet('DESCRIPTION');
        $interchange->addNamespace($namespace);
    }
    
    public function buildDirective($interchange, $hash) {
        $directive = new HTMLPurifier_ConfigSchema_Interchange_Directive();
        
        // These are required elements:
        $directive->id = $this->id($hash->offsetGet('ID'));
        $type = explode('/', $hash->offsetGet('TYPE'));
        if (isset($type[1])) $directive->typeAllowsNull = true;
        $directive->type = $type[0];
        $directive->description = $directive->offsetGet('DESCRIPTION');
        
        // These are extras:
        if (isset($directive['ALLOWED'])) {
            $directive->allowed = $this->lookup($this->evalArray($directive->offsetGet('ALLOWED')));
        }
        if (isset($directive['VALUE-ALIASES'])) {
            $directive->valueAliases = $this->evalArray($directive->offsetGet('VALUE-ALIASES'));
        }
        if (isset($directive['ALIASES'])) {
            $raw_aliases = trim($hash->offsetGet('ALIASES'));
            $aliases = preg_split('/\s*,\s*/', $raw_aliases);
            foreach ($aliases as $alias) {
                $this->aliases[] = $this->id($alias);
            }
        }
        if (isset($directive['VERSION'])) {
            $directive->version = $directive->offsetGet('VERSION');
        }
        if (isset($directive['DEPRECATED-USE'])) {
            $directive->deprecatedUse = $this->id($directive->offsetGet('DEPRECATED-USE'));
        }
        if (isset($directive['DEPRECATED-VERSION'])) {
            $directive->deprecatedVersion = $directive->offsetGet('DEPRECATED-VERSION');
        }
        
        $interchange->addDirective($directive);
    }
    
    /**
     * Evaluates an array PHP code string without array() wrapper
     */
    protected function evalArray($contents) {
        return eval('return array('. $contents .');');
    }
    
    /**
     * Converts an array list into a lookup array.
     */
    protected function lookup($array) {
        $ret = array();
        foreach ($array as $val) $ret[$val] = true;
        return $ret;
    }
    
    /**
     * Convenience function that creates an HTMLPurifier_ConfigSchema_Interchange_Id
     * object based on a string Id.
     */
    protected function id($id) {
        return HTMLPurifier_ConfigSchema_Interchange_Id::make($id);
    }
    
    /**
     * Triggers errors for any unused keys passed in the hash; such keys
     * may indicate typos, missing values, etc.
     * @param $hash Instance of ConfigSchema_StringHash to check.
     */
    protected function _findUnused($hash) {
        $accessed = $hash->getAccessed();
        foreach ($hash as $k => $v) {
            if (!isset($accessed[$k])) {
                trigger_error("String hash key '$k' not used by builder", E_USER_NOTICE);
            }
        }
    }
    
}

