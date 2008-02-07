<?php

/**
 * Converts HTMLPurifier_ConfigSchema into a StringHash which can be
 * easily saved to a file.
 */
class ConfigSchema_StringHashReverseAdapter
{
    
    protected $schema;
    
    /**
     * @param $schema Instance of HTMLPurifier_ConfigSchema to generate
     *        string hashes from.
     */
    public function __construct($schema) {
        $this->schema = $schema;
    }
    
    /**
     * Retrieves a string hash from a specific ID, could be a directive
     * or a namespace.
     * @param $ns string namespace
     * @param $directive string directive name
     */
    public function get($ns, $directive = null) {
        $ret = array();
        if ($directive === null) {
            if (!isset($this->schema->info_namespace[$ns])) {
                trigger_error("Namespace '$ns' doesn't exist in schema");
                return;
            }
            $def = $this->schema->info_namespace[$ns];
            $ret['ID'] = $ns;
            $ret['DESCRIPTION'] = $def->description;
            return $ret;
        }
        if (!isset($this->schema->info[$ns][$directive])) {
            trigger_error("Directive '$ns.$directive' doesn't exist in schema");
            return;
        }
        $def = $this->schema->info[$ns][$directive];
        $ret['ID'] = "$ns.$directive";
        $ret['TYPE'] = $def->type;
        $ret['DEFAULT'] = $this->export($this->schema->defaults[$ns][$directive]);
        $ret['DESCRIPTION'] = $def->description;
        if ($def->allowed !== null) {
            $ret['ALLOWED'] = $this->exportLookup($def->allowed);
        }
        if (!empty($def->aliases)) {
            $ret['VALUE-ALIASES'] = $this->exportHash($def->aliases);
        }
        if (!empty($def->directiveAliases)) {
            $ret['ALIASES'] = implode(', ', $def->directiveAliases);
        }
        return $ret;
    }
    
    /**
     * Exports a variable into a PHP-readable format
     */
    protected function export($var) {
        return var_export($var, true);
    }
    
    /**
     * Exports a lookup array into the form 'key1', 'key2', ...
     */
    protected function exportLookup($lookup) {
        $keys = array_map(array($this, 'export'), array_keys($lookup));
        return implode(', ', $keys);
    }
    
    /**
     * Exports a hash into the form 'key' => 'val',\n ...
     */
    protected function exportHash($hash) {
        $code = $this->export($hash);
        $lines = explode("\n", $code);
        $ret = '';
        foreach ($lines as $line) {
            if ($line == 'array (') continue;
            if ($line == ')') continue;
            $ret .= substr($line, 2) . "\n";
        }
        return $ret;
    }
    
}
