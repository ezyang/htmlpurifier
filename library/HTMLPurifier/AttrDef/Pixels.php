<?php

require_once 'HTMLPurifier/AttrDef.php';

class HTMLPurifier_AttrDef_Pixels extends HTMLPurifier_AttrDef
{
    
    function validate($string) {
        
        $string = trim($string);
        if ($string === '0') return $string;
        if ($string === '')  return false;
        $length = strlen($string);
        if (substr($string, $length - 2) == 'px') {
            $string = substr($string, 0, $length - 2);
        }
        if (!is_numeric($string)) return false;
        $int = (int) $string;
        
        if ($int < 0) return '0';
        
        // could use some upper-bound checking
        
        return (string) $int;
        
    }
    
}

?>