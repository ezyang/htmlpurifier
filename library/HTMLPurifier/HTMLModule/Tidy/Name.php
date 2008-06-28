<?php

/**
 * Name is deprecated, but allowed in strict doctypes, so onl
 */
class HTMLPurifier_HTMLModule_Tidy_Name extends HTMLPurifier_HTMLModule_Tidy
{
    var $name = 'Tidy_Name';
    var $defaultLevel = 'heavy';
    function makeFixes() {
        
        $r = array();
        
        // @name for img, a -----------------------------------------------
        // Technically, it's allowed even on strict, so we allow authors to use
        // it. However, it's deprecated in future versions of XHTML.
        $r['img@name'] = 
        $r['a@name'] = new HTMLPurifier_AttrTransform_Name();
        
        return $r;
    }
}

