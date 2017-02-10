<?php

/**
 * Validates Gradient as defined by CSS.
 * Works with
 */
class HTMLPurifier_AttrDef_CSS_Gradient extends HTMLPurifier_AttrDef
{

    /**
     * @type HTMLPurifier_AttrDef_CSS_Color
     */
    protected $color;

    public function __construct()
    {
        $this->color = new HTMLPurifier_AttrDef_CSS_Color();
    }

    /**
     * @param string $string
     * @param \HTMLPurifier_Config $config
     * @param \HTMLPurifier_Context $context
     * @return bool|string
     */
    public function validate($string, $config, $context)
    {
        if (strpos($string, 'gradient(') === false) {
            return false;
        }

        // Get gradient function (linear, radial, repeating-linear, repeating-radial)
        $function = explode('gradient', $string);
        $function = reset($function) . 'gradient';

        preg_match('#gradient\((.*)\)#', $string, $values);
        $values = end($values);

        if (substr_count($values, '(') !== substr_count($values, ')')) {
            return false;
        }

        $values = (explode(',', $values));
        $bracket = false;
        $parts = [];

        for ($i = 0; $i < count($values); $i++) {
            if (strpos($values[$i], '(') !== false) {
                $bracket = $values[$i];
            } else if ($bracket !== false) {
                $bracket .= ',' . trim($values[$i]);

                if(strpos($values[$i], ')') !== false) {
                    $parts[] = trim($bracket);
                    $bracket = false;
                }
            } else {
                $parts[] = trim($values[$i]);
            }
        }

        $final = '';
        foreach ($parts as $part) {
            if (ctype_space($part)) {
                continue;
            }

            $result = $this->color->validate($part, $config, $context);

            if ($result !== false) {
                $final .= $result . ',';
            }
        }

        if ($final === '') {
            return false;
        }

        return $function . '(' . rtrim($final, ', ') . ')';
    }
}