<?php

/**
 * Performs safe variable parsing based on types which can be used by
 * users. This may not be able to represent all possible data inputs,
 * however.
 */
class HTMLPurifier_VarParser_Flexible extends HTMLPurifier_VarParser
{
    
    public function parse($var, $type, $allow_null = false) {
        if (!isset(HTMLPurifier_VarParser::$types[$type])) {
            throw new HTMLPurifier_VarParserException("Invalid type $type");
        }
        if ($allow_null && $var === null) return null;
        switch ($type) {
            // Note: if code "breaks" from the switch, it triggers a generic
            // exception to be thrown. Specific errors can be specifically
            // done here.
            case 'mixed':
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
                        throw new HTMLPurifier_VarParserException("Unrecognized value '$var' for $type");
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
            default:
                // This should not happen!
                throw new HTMLPurifier_Exception("Inconsistency in HTMLPurifier_VarParser_Flexible: $type is not implemented");
        }
        throw new HTMLPurifier_VarParserException("Invalid input for type $type");
    }
    
}
