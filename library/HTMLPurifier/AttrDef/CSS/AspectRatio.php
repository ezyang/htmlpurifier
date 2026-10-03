<?php

/**
 * Validates the aspect-ratio property as defined by the CSS spec:
 * `auto || <ratio>`, allowing auto to be combined with a ratio in
 * either order.
 */
class HTMLPurifier_AttrDef_CSS_AspectRatio extends HTMLPurifier_AttrDef_CSS_Ratio
{
    /**
     * @param   string               $aspect_ratio   Aspect ratio to validate
     * @param   HTMLPurifier_Config  $config  Configuration options
     * @param   HTMLPurifier_Context $context Context
     *
     * @return  string|boolean
     */
    public function validate($aspect_ratio, $config, $context)
    {
        $aspect_ratio = $this->parseCDATA($aspect_ratio);

        if (strtolower($aspect_ratio) === 'auto') {
            return 'auto';
        }

        // Peel auto off before delegating to the strict <ratio>
        // validator, so it isn't confused with the space CSS allows
        // inside the ratio itself (e.g. "16 / 9").
        $len = strlen($aspect_ratio);
        $auto_before = false;
        $auto_after = false;
        if ($len >= 5 && strncasecmp($aspect_ratio, 'auto ', 5) === 0) {
            $auto_before = true;
            $aspect_ratio = ltrim(substr($aspect_ratio, 5));
        } elseif ($len >= 5 && strcasecmp(substr($aspect_ratio, -5), ' auto') === 0) {
            $auto_after = true;
            $aspect_ratio = rtrim(substr($aspect_ratio, 0, -5));
        }

        $result = parent::validate($aspect_ratio, $config, $context);

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
