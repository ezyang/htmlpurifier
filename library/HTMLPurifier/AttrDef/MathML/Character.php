<?php

/**
 * Validates the MathML attribute character.
 */

class HTMLPurifier_AttrDef_MathML_Character extends HTMLPurifier_AttrDef
{

    /**
     * @param string $char
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool|string
     */
    public function validate($char, $config, $context)
    {
        if (mb_strlen($char) == 1 && strpos(' \t\n\r', $char) === false) {
            return $char;
        } elseif (preg_match('/&((#[0-9]+)|(#x[0-9A-Fa-f]+)|([0-9A-Za-z]+));/', $char)) {
            return $char;
        }
        return false;
    }
}