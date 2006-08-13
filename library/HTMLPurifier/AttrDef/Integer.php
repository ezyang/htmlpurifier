<?php

require_once 'HTMLPurifier/AttrDef.php';

// appears to be a dud class: no currently allowed CSS uses this type
// Uses this: widows, orphans, z-index, counter-increment, counter-reset

class HTMLPurifier_AttrDef_Integer extends HTMLPurifier_AttrDef
{
    
    var $non_negative = false;
    
    function HTMLPurifier_AttrDef_Integer($non_negative = false) {
        $this->non_negative = $non_negative;
    }
    
    function validate($integer, $config, &$context) {
        
        $integer = $this->parseCDATA($integer);
        if ($integer === '') return false;
        
        if ( !$this->non_negative && $integer[0] === '-' ) {
            $digits = substr($integer, 1);
        } elseif( $integer[0] === '+' ) {
            $digits = $integer = substr($integer, 1);
        } else {
            $digits = $integer;
        }
        
        if (!ctype_digit($digits)) return false;
        return $integer;
        
    }
    
}

?>