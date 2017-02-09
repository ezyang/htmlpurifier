<?php

/**
 * Validates Color as defined by CSS.
 */
class HTMLPurifier_AttrDef_CSS_Color extends HTMLPurifier_AttrDef
{

    /**
     * @param string $color
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool|string
     */
    public function validate($color, $config, $context)
    {
        static $colors = null;
        if ($colors === null) {
            $colors = $config->get('Core.ColorKeywords');
        }

        $color = trim($color);
        if ($color === '') {
            return false;
        }

        $lower = strtolower($color);
        if (isset($colors[$lower])) {
            return $colors[$lower];
        }

        if (preg_match('#(rgb|rgba)\(#', $color, $matches) === 1) {
            // get used function : rgb or rgba
            $function = $matches[1];
            if ($function == 'rgba') {
                $parameters_size = 4;
            } else {
                $parameters_size = 3;
            }

            // rgb literal handling
            $length = strlen($color);
            if (strpos($color, ')') !== $length - 1) {
                return false;
            }

            $values = substr($color, strlen($function) + 1, $length - strlen($function) - 2);

            $parts = explode(',', $values);
            if (count($parts) !== $parameters_size) {
                return false;
            }
            $type = false; // to ensure that they're all the same type
            $new_parts = array();
            $i = 0;
            foreach ($parts as $part) {
                $i++;
                $part = trim($part);
                if ($part === '') {
                    return false;
                }

                // different check for alpha channel
                if ($function === 'rgba' && $i === count($parts)) {
                    $result = (new HTMLPurifier_AttrDef_CSS_AlphaValue())->validate($part, $config, $context);

                    if ($result === false) {
                        return false;
                    }

                    $new_parts[] = (string)$result;
                    continue;
                }

                $length = strlen($part);
                if ($part[$length - 1] === '%') {
                    // handle percents
                    if (!$type) {
                        $type = 'percentage';
                    } elseif ($type !== 'percentage') {
                        return false;
                    }
                    $num = (float)substr($part, 0, $length - 1);
                    if ($num < 0) {
                        $num = 0;
                    }
                    if ($num > 100) {
                        $num = 100;
                    }
                    $new_parts[] = "$num%";
                } else {
                    // handle integers
                    if (!$type) {
                        $type = 'integer';
                    } elseif ($type !== 'integer') {
                        return false;
                    }
                    $num = (int)$part;
                    if ($num < 0) {
                        $num = 0;
                    }
                    if ($num > 255) {
                        $num = 255;
                    }
                    $new_parts[] = (string)$num;
                }
            }
            $new_values = implode(',', $new_parts);
            $color = "$function($new_values)";
        } else {
            // hexadecimal handling
            if ($color[0] === '#') {
                $hex = substr($color, 1);
            } else {
                $hex = $color;
                $color = '#' . $color;
            }
            $length = strlen($hex);
            if ($length !== 3 && $length !== 6) {
                return false;
            }
            if (!ctype_xdigit($hex)) {
                return false;
            }
        }
        return $color;
    }
}

// vim: et sw=4 sts=4
