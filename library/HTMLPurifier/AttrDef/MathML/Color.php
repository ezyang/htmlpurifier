<?php

/**
 * Validates Color as defined by MathML.
 */
class HTMLPurifier_AttrDef_MathML_Color extends HTMLPurifier_AttrDef
{

    // MathML 3 only accepts HTML4 color names + transparent
    static protected $colornames = array(
        'aqua',
        'black',
        'blue',
        'fuchsia',
        'gray',
        'green',
        'lime',
        'maroon',
        'navy',
        'olive',
        'purple',
        'red',
        'silver',
        'teal',
        'white',
        'yellow',
        'transparent'
    );

    public function validate($color, $config, $context)
    {

        $color = trim($color);

        if (preg_match('/(#[0-9A-Fa-f]{3})|(#[0-9A-Fa-f]{6})/', $color) || in_array(strtolower($color), static::$colornames)) {
            return $color;
        }

        return false;
    }

}

// vim: et sw=4 sts=4
