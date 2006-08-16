<?php

require_once 'HTMLPurifier/AttrDef.php';

class HTMLPurifier_AttrDef_TextDecoration extends HTMLPurifier_AttrDef
{
    
    var $allowed_values = array(
        'line-through' => true,
        'overline' => true,
        'underline' => true
    );
    
    function validate($string, $config, &$context) {
        
        $string = strtolower($this->parseCDATA($string));
        $parts = explode(' ', $string);
        $final = '';
        foreach ($parts as $part) {
            if (isset($this->allowed_values[$part])) {
                $final .= $part . ' ';
            }
        }
        $final = rtrim($final);
        if ($final === '') return false;
        return $final;
        
    }
    
}

?>