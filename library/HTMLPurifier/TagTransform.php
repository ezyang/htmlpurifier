<?php

require_once('HTMLPurifier/Token.php');

class HTMLPurifier_TagTransform
{
    
    function transform($tag) {
        trigger_error('Call to abstract function', E_USER_ERROR);
    }
    
    function normalizeAttributes($attributes) {
        $keys = array_keys($attributes);
        foreach ($keys as $key) {
            // normalization only necessary when key is not lowercase
            if (!ctype_lower($key)) {
                $new_key = strtolower($key);
                if (!isset($attributes[$new_key])) {
                    $attributes[$new_key] = $attributes[$key];
                }
                unset($attributes[$key]);
            }
        }
        return $attributes;
    }
    
}

class HTMLPurifier_TagTransform_Simple extends HTMLPurifier_TagTransform
{
    
    var $transform_to;
    
    function HTMLPurifier_TagTransform_Simple($transform_to) {
        $this->transform_to = $transform_to;
    }
    
    function transform($tag) {
        switch ($tag->type) {
            case 'end':
                $new_tag = new HTMLPurifier_Token_End($this->transform_to);
                break;
            case 'start':
                $new_tag = new HTMLPurifier_Token_Start($this->transform_to,
                                                        $tag->attributes);
                break;
            case 'empty':
                $new_tag = new HTMLPurifier_Token_Empty($this->transform_to,
                                                        $tag->attributes);
                break;
            default:
                trigger_error('Failed tag transformation', E_USER_WARNING);
                return;
        }
        return $new_tag;
    }
    
}

class HTMLPurifier_TagTransform_Center extends HTMLPurifier_TagTransform
{
    function transform($tag) {
        $attributes = $this->normalizeAttributes($tag->attributes);
        $prepend_css = 'text-align:center;';
        if (isset($attributes['style'])) {
            $attributes['style'] = $prepend_css . $attributes['style'];
        } else {
            $attributes['style'] = $prepend_css;
        }
        switch ($tag->type) {
            case 'end':
                $new_tag = new HTMLPurifier_Token_End('div');
                break;
            case 'start':
                $new_tag = new HTMLPurifier_Token_Start('div', $attributes);
                break;
            case 'empty':
                $new_tag = new HTMLPurifier_Token_Empty('div', $attributes);
                break;
            default:
                trigger_error('Failed tag transformation', E_USER_WARNING);
                return;
        }
        return $new_tag;
    }
}

?>