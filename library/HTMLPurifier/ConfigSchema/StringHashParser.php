<?php

/**
 * Parses string hash files. File format is as such:
 * 
 *      DefaultKeyValue
 *      KEY: Value
 *      KEY2: Value2
 *      --MULTILINE-KEY--
 *      Multiline
 *      value.
 *
 * Which would output something similar to:
 *
 *      array(
 *          'ID' => 'DefaultKeyValue',
 *          'KEY' => 'Value',
 *          'KEY2' => 'Value2',
 *          'MULTILINE-KEY' => "Multiline\nvalue.\n",
 *      )
 *
 * We use this as an easy to use file-format for configuration schema
 * files.
 *
 * @todo
 *      Put this in its own class hierarchy or something; this class
 *      is usage agnostic.
 */
class HTMLPurifier_ConfigSchema_StringHashParser
{
    
    public $default = 'ID';
    
    public function parseFile($file) {
        if (!file_exists($file)) throw new Exception('File does not exist');
        $fh = fopen($file, 'r');
        $state   = false;
        $single  = false;
        $ret     = array();
        while (($line = fgets($fh)) !== false) {
            $line = rtrim($line, "\n\r");
            if (!$state && $line === '') continue;
            if (strncmp('--', $line, 2) === 0) {
                // Multiline declaration
                $state = trim($line, '- ');
                continue;
            } elseif (!$state) {
                $single = true;
                if (strpos($line, ':') !== false) {
                    // Single-line declaration
                    list($state, $line) = explode(': ', $line, 2);
                } else {
                    // Use default declaration
                    $state  = $this->default;
                }
            }
            if ($single) {
                $ret[$state] = $line;
                $single = false;
                $state  = false;
            } else {
                if (!isset($ret[$state])) $ret[$state] = '';
                $ret[$state] .= "$line\n";
            }
        }
        fclose($fh);
        return $ret;
    }
    
}
