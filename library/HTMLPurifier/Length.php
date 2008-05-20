<?php

/**
 * Represents a measurable length, with a string numeric magnitude
 * and a unit. This object is immutable.
 */
class HTMLPurifier_Length
{
    
    /**
     * String numeric magnitude.
     */
    protected $n;
    
    /**
     * String unit. False is permitted if $n = 0.
     */
    protected $unit;
    
    /**
     * Whether or not this length is valid. Null if not calculated yet.
     */
    protected $isValid;
    
    /**
     * Lookup array of units recognized by CSS 2.1
     */
    protected static $allowedUnits = array(
        'em' => true, 'ex' => true, 'px' => true, 'in' => true,
        'cm' => true, 'mm' => true, 'pt' => true, 'pc' => true
    );
    
    /**
     * @param number $n Magnitude
     * @param string $u Unit
     */
    public function __construct($n = '0', $u = false) {
        $this->n = (string) $n;
        $this->unit = $u !== false ? (string) $u : false;
    }
    
    /**
     * @param string $s Unit string, like '2em' or '3.4in'
     * @warning Does not perform validation.
     */
    static public function make($s) {
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
    protected function validate($non_negative = false) {
        // Special case:
        if ($this->n === '+0' || $this->n === '-0') $this->n = '0';
        if ($this->n === '0' && $this->unit === false) return true;
        if (!ctype_lower($this->unit)) $this->unit = strtolower($this->unit);
        if (!isset(HTMLPurifier_Length::$allowedUnits[$this->unit])) return false;
        // Hack:
        $def = new HTMLPurifier_AttrDef_CSS_Number($non_negative);
        $result = $def->validate($this->n, false, false);
        if ($result === false) return false;
        $this->n = $result;
        return true;
    }
    
    /**
     * Returns string representation of number.
     */
    public function toString() {
        if (!$this->isValid()) return false;
        return $this->n . $this->unit;
    }
    
    /**
     * Retrieves string numeric magnitude.
     */
    public function getN() {return $this->n;}
    
    /**
     * Retrieves string unit.
     */
    public function getUnit() {return $this->unit;}
    
    /**
     * Returns true if this length unit is valid.
     */
    public function isValid($non_negative = false) {
        if ($this->isValid === null) $this->isValid = $this->validate($non_negative);
        return $this->isValid;
    }
    
}
