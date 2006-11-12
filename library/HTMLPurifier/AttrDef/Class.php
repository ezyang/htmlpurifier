<?php

require_once 'HTMLPurifier/AttrDef.php';
require_once 'HTMLPurifier/Config.php';

/**
 * Validates the contents of the global HTML attribute class.
 */
class HTMLPurifier_AttrDef_Class extends HTMLPurifier_AttrDef
{
    
    function validate($string, $config, &$context) {
        
        $string = trim($string);
        
        // early abort: '' and '0' (strings that convert to false) are invalid
        if (!$string) return false;
        
        // OPTIMIZABLE!
        // do the preg_match, capture all subpatterns for reformulation
        
        // we don't support U+00A1 and up codepoints or
        // escaping because I don't know how to do that with regexps
        // and plus it would complicate optimization efforts (you never
        // see that anyway).
        $matches = array();
        $pattern = '/(?:(?<=\s)|\A)'. // look behind for space or string start
                   '((?:--|-?[A-Za-z_])[A-Za-z_\-0-9]*)'.
                   '(?:(?=\s)|\z)/'; // look ahead for space or string end
        preg_match_all($pattern, $string, $matches);
        
        if (empty($matches[1])) return false;
        
        // reconstruct class string
        $new_string = '';
        foreach ($matches[1] as $class_names) {
            $new_string .= $class_names . ' ';
        }
        $new_string = rtrim($new_string);
        
        return $new_string;
        
    }
    
}

?>