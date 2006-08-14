<?php

require_once 'HTMLPurifier/AttrTransform.php';

// this transformation may be done pre or post validation, but post is
// preferred, since invalid languages then will have been dropped.

class HTMLPurifier_AttrTransform_Lang extends HTMLPurifier_AttrTransform
{
    
    function transform($attr, $config) {
        
        $lang     = isset($attr['lang']) ? $attr['lang'] : false;
        $xml_lang = isset($attr['xml:lang']) ? $attr['xml:lang'] : false;
        
        if ($lang !== false && $xml_lang === false) {
            $attr['xml:lang'] = $lang;
        } elseif ($xml_lang !== false) {
            $attr['lang'] = $xml_lang;
        }
        
        return $attr;
        
    }
    
}

?>