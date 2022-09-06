<?php

class HTMLPurifier_AttrDef_HTML_ContentEditable extends HTMLPurifier_AttrDef
{
    /** @var string[] */
    protected static $values = array(
        'true',
        'false',
    );

    public function validate($string, $config, $context)
    {
        if ($string === '') {
            return 'true';
        }

        if (! in_array(strtolower($string), self::$values, true)) {
            return false;
        }

        return $string;
    }
}
