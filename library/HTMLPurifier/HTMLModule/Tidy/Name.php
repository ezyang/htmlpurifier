<?php

/**
 * Name is deprecated, but allowed in strict doctypes, so onl
 */
class HTMLPurifier_HTMLModule_Tidy_Name extends HTMLPurifier_HTMLModule_Tidy
{
    public $name = 'Tidy_Name';
    public $defaultLevel = 'heavy';
    public function makeFixes() {

        $r = array();

        // @name for img, a -----------------------------------------------
        // Technically, it's allowed even on strict, so we allow authors to use
        // it. However, it's deprecated in future versions of XHTML.
        $r['img@name'] = new HTMLPurifier_AttrTransform_Name();

        return $r;
    }
}

// vim: et sw=4 sts=4
