<?php

require_once('HTMLPurifier/Token.php');

class HTMLPurifier_TagTransform
{
    
    function transform($tag) {
        trigger_error('Call to abstract function', E_USER_ERROR);
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
    var $transform_to = 'div';
    
    function transform($tag) {
        if ($tag->type == 'end') {
            $new_tag = new HTMLPurifier_Token_End($this->transform_to);
            return $new_tag;
        }
        $attributes = $tag->attributes;
        $prepend_css = 'text-align:center;';
        if (isset($attributes['style'])) {
            $attributes['style'] = $prepend_css . $attributes['style'];
        } else {
            $attributes['style'] = $prepend_css;
        }
        switch ($tag->type) {
            case 'start':
                $new_tag = new HTMLPurifier_Token_Start($this->transform_to, $attributes);
                break;
            case 'empty':
                $new_tag = new HTMLPurifier_Token_Empty($this->transform_to, $attributes);
                break;
            default:
                trigger_error('Failed tag transformation', E_USER_WARNING);
                return;
        }
        return $new_tag;
    }
}

class HTMLPurifier_TagTransform_Font extends HTMLPurifier_TagTransform
{
    
    var $transform_to = 'span';
    
    var $_size_lookup = array(
        '1' => 'xx-small',
        '2' => 'small',
        '3' => 'medium',
        '4' => 'large',
        '5' => 'x-large',
        '6' => 'xx-large',
        '7' => '300%',
        '-1' => 'smaller',
        '+1' => 'larger',
        '-2' => '60%',
        '+2' => '150%',
        '+4' => '300%'
    );
    
    function transform($tag) {
        
        if ($tag->type == 'end') {
            $new_tag = new HTMLPurifier_Token_End($this->transform_to);
            return $new_tag;
        }
        
        // font size lookup table based off of:
        //  http://style.cleverchimp.com/font_size_intervals/altintervals.html
        $attributes = $tag->attributes;
        $prepend_style = '';
        
        // handle color transform
        if (isset($attributes['color'])) {
            $prepend_style .= 'color:' . $attributes['color'] . ';';
            unset($attributes['color']);
        }
        
        // handle face transform
        if (isset($attributes['face'])) {
            $prepend_style .= 'font-family:' . $attributes['face'] . ';';
            unset($attributes['face']);
        }
        
        // handle size transform
        if (isset($attributes['size'])) {
            if (isset($this->_size_lookup[$attributes['size']])) {
                $prepend_style .= 'font-size:' .
                  $this->_size_lookup[$attributes['size']] . ';';
            }
            unset($attributes['size']);
        }
        
        if ($prepend_style) {
            $attributes['style'] = isset($attributes['style']) ?
                $prepend_style . $attributes['style'] :
                $prepend_style;
        }
        
        switch ($tag->type) {
            case 'start':
                $new_tag = new HTMLPurifier_Token_Start($this->transform_to, $attributes);
                break;
            case 'empty':
                $new_tag = new HTMLPurifier_Token_Empty($this->transform_to, $attributes);
                break;
            default:
                trigger_error('Failed tag transformation', E_USER_WARNING);
                return;
        }
        return $new_tag;
        
    }
}

?>