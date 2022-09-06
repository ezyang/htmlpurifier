<?php

class HTMLPurifier_AttrDef_HTML_ContentEditable extends HTMLPurifier_AttrDef
{
    public function validate($string, $config, $context)
    {
        if (strtolower($string) === 'false') {
            return $string;
        }

        if ($config->get('HTML.Trusted')) {
            $enum = new HTMLPurifier_AttrDef_Enum(array('', 'true', 'false'));

            return $enum->validate($string, $config, $context);
        }

        return false;
    }
}
