<?php

require_once 'HTMLPurifier/AttrDef.php';

/**
 * Validates an integer.
 * @note While this class was modeled off the CSS definition, no currently
 *       allowed CSS uses this type.  The properties that do are: widows,
 *       orphans, z-index, counter-increment, counter-reset.  Some of the
 *       HTML attributes, however, find use for a non-negative version of this.
 */
class HTMLPurifier_AttrDef_Integer extends HTMLPurifier_AttrDef
{
    
    /**
     * Bool indicating whether or not integers can only be positive.
     */
    var $non_negative = false;
    
    /**
     * @param $non_negative bool indicating whether or not only positive
     */
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