<?php

/**
 * Validates Gradient as defined by CSS.
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

    /**
     * @type HTMLPurifier_AttrDef_CSS_Composite
     */
    protected $size;

    public function __construct()
    {
        $this->color = new HTMLPurifier_AttrDef_CSS_Color();
        $this->direction = new HTMLPurifier_AttrDef_CSS_Direction();
        $this->angle = new HTMLPurifier_AttrDef_CSS_Angle();
        $this->size = new HTMLPurifier_AttrDef_CSS_Composite(
            array(
                new HTMLPurifier_AttrDef_CSS_Length(),
                new HTMLPurifier_AttrDef_CSS_Percentage()
            )
        );
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

        $gradientFunctions = array(
            'repeating-linear-gradient',
            'repeating-radial-gradient',
            'linear-gradient',
            'radial-gradient',
        );

        // Get gradient function (linear, radial, repeating-linear, repeating-radial)
        $function = null;
        foreach ($gradientFunctions as $gradientFunction) {
            // If function exists and is in first position
            if (strpos($string, $gradientFunction) === 0) {
                $function = $gradientFunction;
                break;
            }
        }

        if (!$function) {
            return false;
        }

        // Check if function is linear or radial
        $linear = false;
        if (strpos($function, 'linear') !== false) {
            $linear = true;
        }

        preg_match('#gradient\((.*)\)#', $string, $values);
        $values = end($values);

        if (substr_count($values, '(') !== substr_count($values, ')')) {
            return false;
        }

        $values = (explode(',', $values));
        $bracket = false;
        $parts = array();

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

            // test for direction or angle but only for the first parameter of linear function
            if ($i === 1 && $linear === true) {
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

            // if function is radial, the first argument has to be a valid shape (circle or ellipse)
            if ($i === 1 && $linear === false) {
                $shapes = array('circle', 'ellipse');
                $part = trim($part);

                if (in_array($part, $shapes, true)) {
                    $final .= $part.',';
                    continue;
                }
            }

            // test for color
            $size = false;
            if (preg_match('#(.*)\s+(\d+(\.\d+)?(%|px|ex|em|in|cm|mm|pt|pc))\s*$#', $part, $matches)) {
                $color = $matches[1];
                $size = $matches[2];
            } else {
                $color = $part;
            }

            $r = $this->color->validate($color, $config, $context);
            if ($r !== false) {
                $final .= $r;

                if ($size !== false) {
                    $r = $this->size->validate($size, $config, $context);
                    if ($r !== false) {
                        $final .= ' '.$r;
                    }
                }
                $final .= ',';
            }

            // the different size keyword is not yet implemented
            // correct values are: closest-side, farthest-side, closest-corner, farthest-corner

        }

        if ($final === '') {
            return false;
        }

        return $function . '(' . rtrim($final, ', ') . ')';
    }
}