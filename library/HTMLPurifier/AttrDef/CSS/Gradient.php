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

    /**
     * @type HTMLPurifier_AttrDef_CSS_Direction
     */
    protected $direction;

    /**
     * @type HTMLPurifier_AttrDef_CSS_Angle
     */
    protected $angle;

    public function __construct()
    {
        $this->color = new HTMLPurifier_AttrDef_CSS_Color();
        $this->direction = new HTMLPurifier_AttrDef_CSS_Direction();
        $this->angle = new HTMLPurifier_AttrDef_CSS_Angle();
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
        $i = 0;
        foreach ($parts as $part) {
            $i++;
            if (ctype_space($part)) {
                continue;
            }

            // test for direction or angle but only for the first parameter
            if ($i === 1) {
                $r = $this->direction->validate($part, $config, $context);
                if ($r !== false) {
                    $final .= $r.',';
                    continue;
                }

                $r = $this->angle->validate($part, $config, $context);
                if ($r !== false) {
                    $final .= $r.',';
                    continue;
                }
            }

            // test for color
            $r = $this->color->validate($part, $config, $context);
            if ($r !== false) {
                $final .= $r.',';
                continue;
            }

            // todo : check color size (size or percent) for repeating gradient : repeating-linear-gradient(red, yellow 10%, green 20%)

        }

        if ($final === '') {
            return false;
        }

        return $function . '(' . rtrim($final, ', ') . ')';
    }
}