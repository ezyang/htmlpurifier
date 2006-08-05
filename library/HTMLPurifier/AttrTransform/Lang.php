<?php

require_once 'HTMLPurifier/AttrTransform.php';

class HTMLPurifier_AttrTransform_Lang extends HTMLPurifier_AttrTransform
{
    
    function transform($token) {
        
        $lang     = isset($token->attributes['lang']) ?
                          $token->attributes['lang'] : false;
        $xml_lang = isset($token->attributes['xml:lang']) ?
                          $token->attributes['xml:lang'] : false;
        
        if ($lang === false && $xml_lang == false) return $token;
        
        $new_token = $token->copy();
        
        if ($lang !== false && $xml_lang === false) {
            $new_token->attributes['xml:lang'] = $lang;
        } elseif ($xml_lang !== false) {
            $new_token->attributes['lang'] = $xml_lang;
        }
        
        return $new_token;
        
    }
    
}

?>