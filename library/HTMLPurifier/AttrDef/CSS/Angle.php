<?php

/**
 * Validates the value of angles.
 */
class HTMLPurifier_AttrDef_CSS_Angle extends HTMLPurifier_AttrDef
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

        if (!preg_match('#(-)?\s*(\d+(\.\d+)?)\s*(deg|rad|grad|turn)#', $string, $matches)) {
            return false;
        }

        $value = (float)($matches[1] . $matches[2]);
        $unit = $matches[4];

        return $value . $unit;
    }

}
