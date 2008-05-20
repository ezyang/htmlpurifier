<?php

/**
 * Represents a Length as defined by CSS.
 */
class HTMLPurifier_AttrDef_CSS_Length extends HTMLPurifier_AttrDef
{
    
    protected $nonNegative;
    
    /**
     * @param $non_negative Bool indication whether or not negative values are
     *                      allowed.
     */
    public function __construct($non_negative = false) {
        $this->nonNegative = $non_negative;
    }
    
    public function validate($string, $config, $context) {
        $string = $this->parseCDATA($string);
        
        // Optimizations
        if ($string === '') return false;
        if ($string === '0') return '0';
        if (strlen($string) === 1) return false;
        
        $length = HTMLPurifier_Length::make($string);
        if (!$length->isValid($this->nonNegative)) return false;
        
        $n = $length->getN();
        if ($this->nonNegative && $n < 0) return false;
        
        return $length->toString();
    }
    
}

