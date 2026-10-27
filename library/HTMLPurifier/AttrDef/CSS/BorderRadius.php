<?php

/**
 * Validates the border-radius shorthand: one to four horizontal radii,
 * optionally followed by a slash and one to four vertical radii.
 */
class HTMLPurifier_AttrDef_CSS_BorderRadius extends HTMLPurifier_AttrDef
{
    /**
     * @type HTMLPurifier_AttrDef_CSS_Multiple
     */
    protected $radii;

    /**
     * @param HTMLPurifier_AttrDef $single Validator for a single radius
     */
    public function __construct($single)
    {
        $this->radii = new HTMLPurifier_AttrDef_CSS_Multiple($single, 4);
    }

    /**
     * @param string $string
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool|string
     */
    public function validate($string, $config, $context)
    {
        $parts = explode('/', $this->parseCDATA($string));
        if (count($parts) > 2) {
            return false;
        }
        $result = $this->radii->validate($parts[0], $config, $context);
        if ($result === false || count($parts) === 1) {
            return $result;
        }
        $vertical = $this->radii->validate($parts[1], $config, $context);
        if ($vertical === false) {
            return false;
        }
        return $result . ' / ' . $vertical;
    }
}

// vim: et sw=4 sts=4
