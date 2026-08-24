<?php

/**
 * Validates a ratio as defined by the CSS spec, or the aspect-ratio
 * property's `auto || <ratio>` grammar that allows auto to be combined
 * with a ratio in either order.
 */
class HTMLPurifier_AttrDef_CSS_Ratio extends HTMLPurifier_AttrDef
{
    /**
     * @param   string               $ratio   Ratio to validate
     * @param   HTMLPurifier_Config  $config  Configuration options
     * @param   HTMLPurifier_Context $context Context
     *
     * @return  string|boolean
     *
     * @warning Some contexts do not pass $config, $context. These
     *          variables should not be used without checking HTMLPurifier_Length
     */
    public function validate($ratio, $config, $context)
    {
        $ratio = $this->parseCDATA($ratio);

        if (strtolower($ratio) === 'auto') {
            return 'auto';
        }

        // Peel auto off before splitting on '/', so it isn't confused
        // with the space CSS allows inside the ratio itself (e.g. "16 / 9").
        $len = strlen($ratio);
        $auto_before = false;
        $auto_after = false;
        if ($len >= 5 && strncasecmp($ratio, 'auto ', 5) === 0) {
            $auto_before = true;
            $ratio = ltrim(substr($ratio, 5));
        } elseif ($len >= 5 && strcasecmp(substr($ratio, -5), ' auto') === 0) {
            $auto_after = true;
            $ratio = rtrim(substr($ratio, 0, -5));
        }

        $parts = explode('/', $ratio, 2);
        $length = count($parts);

        if ($length < 1 || $length > 2) {
            return false;
        }

        $num = new \HTMLPurifier_AttrDef_CSS_Number();

        if ($length === 1) {
            $result = $num->validate($parts[0], $config, $context);
        } else {
            $num1 = $num->validate($parts[0], $config, $context);
            $num2 = $num->validate($parts[1], $config, $context);

            $result = ($num1 === false || $num2 === false) ? false : $num1 . '/' . $num2;
        }

        if ($result === false) {
            return false;
        }

        if ($auto_before) {
            return 'auto ' . $result;
        }
        if ($auto_after) {
            return $result . ' auto';
        }
        return $result;
    }
}

// vim: et sw=4 sts=4
