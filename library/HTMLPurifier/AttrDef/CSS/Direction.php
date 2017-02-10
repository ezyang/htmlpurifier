<?php

/**
 * Validates the value of direction.
 */
class HTMLPurifier_AttrDef_CSS_Direction extends HTMLPurifier_AttrDef
{

    /**
     * @type HTMLPurifier_AttrDef_CSS_Position
     */
    protected $position;

    public function __construct()
    {
        $this->position = new HTMLPurifier_AttrDef_CSS_Position();
    }

    /**
     * @param string $string
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool|string
     */
    public function validate($string, $config, $context)
    {
        $string = $this->parseCDATA($string);

        $to = false;
        if (substr($string, 0, 3) === 'to ') {
            $string = str_replace('to ', '', $string);

            $to = true;
        }

        $r = $this->position->validate($string, $config, $context);
        if ($r === false) {
            return false;
        }

        return ($to ? 'to ' : '') . $r;
    }

}