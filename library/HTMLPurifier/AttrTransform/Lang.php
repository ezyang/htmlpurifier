<?php

require_once 'HTMLPurifier/AttrTransform.php';

class HTMLPurifier_AttrTransform_Lang extends HTMLPurifier_AttrTransform
{
    
    function transform($attr) {
        
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