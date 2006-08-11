<?php

require_once 'HTMLPurifier/AttrDef.php';

// for col and row spans, essentially, a positive integer
class HTMLPurifier_AttrDef_NumberSpan extends HTMLPurifier_AttrDef
{
    
    function validate($string, $config = null) {
        
        $string = trim($string);
        if ($string === '') return false;
        if ($string === '1') return false; // this is the default value
        if (!is_numeric($string)) return false;
        $int = (int) $string;
        if ($int <= 0) return false;
        return (string) $int;
        
    }
    
}

?>