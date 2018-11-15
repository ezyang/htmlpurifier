<?php

/**
 * Validates the MathML type length (not to be confused with either HTML nor
 * CSS's length).
 */

class HTMLPurifier_AttrDef_MathML_Length extends HTMLPurifier_AttrDef
{

    /**
     * @param string $string
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool|string
     */
    public function validate($string, $config, $context)
    {
        $string = $this->parseCDATA($string);

        // Optimizations
        if ($string === '') {
            return false;
        }
        if ($string === '0') {
            return '0';
        }

        $length = HTMLPurifier_MathMLLength::make($string);
        if (!$length->isValid()) {
            return false;
        }

        return $length->toString();
    }
}