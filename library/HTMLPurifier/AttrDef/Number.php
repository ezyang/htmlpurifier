<?php

class HTMLPurifier_AttrDef_Number extends HTMLPurifier_AttrDef
{
    
    var $non_negative = false;
    
    function HTMLPurifier_AttrDef_Number($non_negative = false) {
        $this->non_negative = $non_negative;
    }
    
    function validate($number, $config, &$context) {
        
        $number = $this->parseCDATA($number);
        
        if ($number === '') return false;
        
        $sign = '';
        switch ($number[0]) {
            case '-':
                if ($this->non_negative) return false;
                $sign = '-';
            case '+':
                $number = substr($number, 1);
        }
        
        if (ctype_digit($number)) {
            $number = ltrim($number, '0');
            return $number ? $sign . $number : '0';
        }
        if (!strpos($number, '.')) return false;
        
        list($left, $right) = explode('.', $number, 2);
        
        if (!ctype_digit($left)) return false;
        $left = ltrim($left, '0');
        
        $right = rtrim($right, '0');
        
        if ($right === '') {
            return $left ? $sign . $left : '0';
        } elseif (!ctype_digit($right)) {
            return false;
        }
        
        return $sign . $left . '.' . $right;
        
    }
    
}

?>