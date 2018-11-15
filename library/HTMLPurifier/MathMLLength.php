<?php

/**
 * Represents a length in MathML. These admit namedspace values.
 */
class HTMLPurifier_MathMLLength extends HTMLPurifier_Length
{

    /**
     * One of the specified namedspaces.
     * @type string
     */
    protected $namedspace;

    /**
    * Array Lookup array of namedspaces recognized by MathML
    * @type array
    */
    protected static $allowedNamedspaces = array(
        'veryverythinmathspace' => true,
        'verythinmathspace' => true,
        'thinmathspace' => true,
        'mediummathspace' => true,
        'thickmathspace' => true,
        'verythickmathspace' => true,
        'veryverythickmathspace' => true,
        'negativeveryverythinmathspace' => true,
        'negativeverythinmathspace' => true,
        'negativethinmathspace' => true,
        'negativemediummathspace' => true,
        'negativethickmathspace' => true,
        'negativeverythickmathspace' => true,
        'negativeveryverythickmathspace' => true
    );

    /**
     * Array Lookup array of units recognized by MathML.
     * @note This is a restriction of HTMLPurifier_Length's allowed units.
     * @type array
     */
    protected static $allowedUnits = array(
        'em' => true, 'ex' => true, 'px' => true, 'in' => true,
        'cm' => true, 'mm' => true, 'pt' => true, 'pc' => true,
        '%' => true, '' => true
    );

    /**
     * @param string $n Magnitude
     * @param bool|string $u Unit
     */
    public function __construct($n = '0', $u = false, $namedspace = '')
    {
        if ($namedspace) {
            $this->namedspace = strtolower($namedspace);
        } else {
            $this->n = (string) $n;
            $this->unit = $u !== false ? (string) $u : false;
        }
    }

    /**
     * @param string $s Unit string, like '2em' or '3.4in', or namedspace
     * @return HTMLPurifier_MathMLLength
     * @warning Does not perform validation.
     */
    public static function make($s)
    {
        if ($s instanceof HTMLPurifier_MathMLLength) {
            return $s;
        }
        if (isset(HTMLPurifier_MathMLLength::$allowedNamedspaces[trim($s)])) {
            return new HTMLPurifier_MathMLLength('0', false, $s);
        }
        $length = HTMLPurifier_Length::make($s);
        return new HTMLPurifier_MathMLLength($length->n, $length->unit);
    }

    /**
     * Validates the number and unit or namedspace.
     * @return bool
     */
    protected function validate()
    {
        if (isset(HTMLPurifier_MathMLLength::$allowedNamedspaces[$this->namedspace])) {
            return true;
        }
        return parent::validate();
    }

    /**
     * Returns string representation of number.
     * @return string
     */
    public function toString()
    {
        if (!$this->isValid()) {
            return false;
        }
        if ($this->namedspace) {
            return $this->namedspace;
        }
        return parent::toString();
    }

    /**
     * Retrieves the namedspace.
     * @return string
     */
    public function getNamedspace()
    {
        return $this->namedspace;
    }

    /**
     * Compares two lengths, and returns 1 if greater, -1 if less, 0 if equal
     * and null if not comparable.
     * @param HTMLPurifier_Length $l
     * @return int
     * @warning If both values are too large or small, this calculation will
     *          not work properly
     */
    public function compareTo($l)
    {
        if ($l === false) {
            return false;
        }
        if ($this->namedspace || $l->namedspace) {
            if ($this->namedspace === $l->namedspace) {
                return 0;
            } else {
                return null;
            }
        }
        return parent::compareTo($l);
    }
}