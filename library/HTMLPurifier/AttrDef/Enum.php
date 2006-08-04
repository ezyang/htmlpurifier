<?php

// Enum = Enumerated
class HTMLPurifier_AttrDef_Enum extends HTMLPurifier_AttrDef
{
    
    var $valid_values   = array();
    var $case_sensitive = false; // values according to W3C spec
    
    function HTMLPurifier_AttrDef_Enum(
        $valid_values = array(), $case_sensitive = false) {
        
        $this->valid_values = array_flip($valid_values);
        $this->case_sensitive = $case_sensitive;
    }
    
    function validate($raw_string) {
        $string = trim($raw_string);
        if (!$this->case_sensitive) {
            $string = ctype_lower($string) ? $string : strtolower($string);
        }
        $result = isset($this->valid_values[$string]);
        
        // if strings equal, return result, otherwise, return
        // the new string on a good result and false on a bad one
        return ($string == $raw_string) ? $result : $result ? $string : false;
    }
    
}

?>