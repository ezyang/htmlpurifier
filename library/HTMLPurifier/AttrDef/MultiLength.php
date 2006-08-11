<?php

require_once 'HTMLPurifier/AttrDef.php';
require_once 'HTMLPurifier/AttrDef/Length.php';

class HTMLPurifier_AttrDef_MultiLength extends HTMLPurifier_AttrDef_Length
{
    
    function validate($string, $config = null) {
        
        $string = trim($string);
        if ($string === '') return false;
        
        $parent_result = parent::validate($string);
        if ($parent_result !== false) return $parent_result;
        
        $length = strlen($string);
        $last_char = $string[$length - 1];
        
        if ($last_char !== '*') return false;
        
        $int = substr($string, 0, $length - 1);
        
        if (!is_numeric($int)) return false;
        
        $int = (int) $int;
        
        if ($int < 0) return '0*';
        
        return ((string) $int) . '*';
        
    }
    
}

?>