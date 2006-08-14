<?php

require_once 'HTMLPurifier/AttrDef.php';
require_once 'HTMLPurifier/AttrDef/Number.php';

class HTMLPurifier_AttrDef_Percentage extends HTMLPurifier_AttrDef
{
    
    var $number_def;
    
    function HTMLPurifier_AttrDef_Percentage($non_negative = false) {
        $this->number_def = new HTMLPurifier_AttrDef_Number($non_negative);
    }
    
    function validate($string, $config, &$context) {
        
        $string = $this->parseCDATA($string);
        
        if ($string === '') return false;
        $length = strlen($string);
        if ($length === 1) return false;
        if ($string[$length - 1] !== '%') return false;
        
        $number = substr($string, 0, $length - 1);
        $number = $this->number_def->validate($number, $config, $context);
        
        if ($number === false) return false;
        return "$number%";
        
    }
    
}

?>