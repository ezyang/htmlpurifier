<?php

require_once 'HTMLPurifier/AttrDef.php';
require_once 'HTMLPurifier/AttrDef/Number.php';

/**
 * Validates a Percentage as defined by the HTML spec.
 * @note This also allows integer pixel values.
 */
class HTMLPurifier_AttrDef_Percentage extends HTMLPurifier_AttrDef
{
    
    /**
     * Instance of HTMLPurifier_AttrDef_Number to defer pixel validation
     */
    var $number_def;
    
    /**
     * @param Bool indicating whether to forbid negative values
     */
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