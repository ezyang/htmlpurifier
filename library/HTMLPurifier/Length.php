<?php

/**
 * Represents a measurable length, with a string numeric magnitude
 * and a unit.
 */
class HTMLPurifier_Length
{
    
    /**
     * String numeric magnitude.
     */
    var $n;
    
    /**
     * String unit. False is permitted if $n = 0.
     */
    var $unit;
    
    /**
     * @param number $n Magnitude
     * @param string $u Unit
     */
    function HTMLPurifier_Length($n = '0', $u = false) {
        $this->n = $n;
        $this->unit = $u;
    }
    
    /**
     * @param string $s Unit string, like '2em' or '3.4in'
     * @warning Does not perform validation.
     */
    function make($s) {
        $n_length = strspn($s, '1234567890.+-');
        $n = substr($s, 0, $n_length);
        $unit = substr($s, $n_length);
        if ($unit === '') $unit = false;
        return new HTMLPurifier_Length($n, $unit);
    }
    
    /**
     * Validates the number and unit.
     * @param bool $non_negative Whether or not to disable negative values.
     * @note Maybe should be put in another class.
     */
    function validate($non_negative = false, $config, $context) {
        // Special case:
        static $allowedUnits = array(
            'em' => true, 'ex' => true, 'px' => true, 'in' => true,
            'cm' => true, 'mm' => true, 'pt' => true, 'pc' => true
        );
        if ($this->n === '0' && $this->unit === false) return true;
        if (!ctype_lower($this->unit)) $this->unit = strtolower($this->unit);
        if (!isset($allowedUnits[$this->unit])) return false;
        $def = new HTMLPurifier_AttrDef_CSS_Number($non_negative);
        $result = $def->validate($this->n, $config, $context);
        if ($result === false) return false;
        $this->n = $result;
        return true;
    }
    
    /**
     * Returns string representation of number.
     */
    function toString() {
        return $this->n . $this->unit;
    }
    
}
