<?php

require_once 'HTMLPurifier/AttrDef.php';

/**
 * Validates Color as defined by CSS.
 */
class HTMLPurifier_AttrDef_Color extends HTMLPurifier_AttrDef
{
    
    function validate($color, $config, &$context) {
        
        $color = trim($color);
        if (!$color) return false;
        
        if ($color[0] === '#') {
            // hexadecimal handling
            $hex = substr($color, 1);
            $length = strlen($hex);
            if ($length !== 3 && $length !== 6) return false;
            if (!ctype_xdigit($hex)) return false;
        } else {
            // rgb literal handling
            if (strpos($color, 'rgb(')) return false;
            $length = strlen($color);
            if (strpos($color, ')') !== $length - 1) return false;
            $triad = substr($color, 4, $length - 4 - 1);
            $parts = explode(',', $triad);
            if (count($parts) !== 3) return false;
            $type = false; // to ensure that they're all the same type
            $new_parts = array();
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') return false;
                $length = strlen($part);
                if ($part[$length - 1] === '%') {
                    // handle percents
                    if (!$type) {
                        $type = 'percentage';
                    } elseif ($type !== 'percentage') {
                        return false;
                    }
                    $num = (float) substr($part, 0, $length - 1);
                    if ($num < 0) $num = 0;
                    if ($num > 100) $num = 100;
                    $new_parts[] = "$num%";
                } else {
                    // handle integers
                    if (!$type) {
                        $type = 'integer';
                    } elseif ($type !== 'integer') {
                        return false;
                    }
                    $num = (int) $part;
                    if ($num < 0) $num = 0;
                    if ($num > 255) $num = 255;
                    $new_parts[] = (string) $num;
                }
            }
            $new_triad = implode(',', $new_parts);
            $color = "rgb($new_triad)";
        }
        
        return $color;
        
    }
    
}

?>