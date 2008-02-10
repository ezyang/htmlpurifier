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
        
        if ($def instanceof HTMLPurifier_ConfigDef_DirectiveAlias) {
            return false;
        }
        
        $ret['ID'] = "$ns.$directive";
        $ret['TYPE'] = $def->type;
        
        // Attempt to extract version information from description.
        $description = $this->normalize($def->description);
        list($description, $version) = $this->extractVersion($description);
        
        if ($version) $ret['VERSION'] = $version;
        $ret['DEFAULT'] = $this->export($this->schema->defaults[$ns][$directive]);
        $ret['DESCRIPTION'] = wordwrap($description, 75, "\n");
        
        if ($def->allowed !== true) {
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
    public function export($var) {
        if ($var === array()) return 'array()'; // single-line format
        return var_export($var, true);
    }
    
    /**
     * Exports a lookup array into the form 'key1', 'key2', ...
     */
    public function exportLookup($lookup) {
        if (!is_array($lookup)) return $this->export($lookup);
        if (empty($lookup)) return '';
        $keys = array_map(array($this, 'export'), array_keys($lookup));
        return implode(', ', $keys);
    }
    
    /**
     * Exports a hash into the form 'key' => 'val',\n ...
     */
    public function exportHash($hash) {
        if (!is_array($hash)) return $this->export($hash);
        if (empty($hash)) return '';
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
    
    /**
     * Normalizes a string to Unix style newlines
     */
    protected function normalize($string) {
        return str_replace(array("\r\n", "\r"), "\n", $string);
    }
    
    public function extractVersion($description) {
        $regex = '/This directive (?:has been|was) available since (\d+\.\d+\.\d+)\./';
        $regex = str_replace(' ', '\s+', $regex); // allow any number of spaces between statements
        $ok = preg_match($regex, $description, $matches);
        if ($ok) {
            $version = $matches[1];
        } else {
            $version = false;
        }
        $description = preg_replace($regex, '', $description, 1);
        return array($description, $version);
    }
    
}
