<?php

require_once('HTMLPurifier/Token.php');

/**
 * Defines a mutation of an obsolete tag into a valid tag.
 */
class HTMLPurifier_TagTransform
{
    
    /**
     * Tag name to transform the tag to.
     * @public
     */
    var $transform_to;
    
    /**
     * Transforms the obsolete tag into the valid tag.
     * @param $tag Tag to be transformed.
     * @param $config Mandatory HTMLPurifier_Config object
     * @param $context Mandatory HTMLPurifier_Context object
     */
    function transform($tag, $config, &$context) {
        trigger_error('Call to abstract function', E_USER_ERROR);
    }
    
}

/**
 * Simple transformation, just change tag name to something else.
 */
class HTMLPurifier_TagTransform_Simple extends HTMLPurifier_TagTransform
{
    
    /**
     * @param $transform_to Tag name to transform to.
     */
    function HTMLPurifier_TagTransform_Simple($transform_to) {
        $this->transform_to = $transform_to;
    }
    
    function transform($tag, $config, &$context) {
        $new_tag = $tag->copy();
        $new_tag->name = $this->transform_to;
        return $new_tag;
    }
    
}

/**
 * Transforms CENTER tags into proper version (DIV with text-align CSS)
 * 
 * Takes a CENTER tag, parses the align attribute, and then if it's valid
 * assigns it to the CSS property text-align.
 */
class HTMLPurifier_TagTransform_Center extends HTMLPurifier_TagTransform
{
    var $transform_to = 'div';
    
    function transform($tag, $config, &$context) {
        if ($tag->type == 'end') {
            $new_tag = new HTMLPurifier_Token_End($this->transform_to);
            return $new_tag;
        }
        $attr = $tag->attr;
        $prepend_css = 'text-align:center;';
        if (isset($attr['style'])) {
            $attr['style'] = $prepend_css . $attr['style'];
        } else {
            $attr['style'] = $prepend_css;
        }
        $new_tag = $tag->copy();
        $new_tag->name = $this->transform_to;
        $new_tag->attr = $attr;
        return $new_tag;
    }
}

/**
 * Transforms FONT tags to the proper form (SPAN with CSS styling)
 * 
 * This transformation takes the three proprietary attributes of FONT and
 * transforms them into their corresponding CSS attributes.  These are color,
 * face, and size.
 * 
 * @note Size is an interesting case because it doesn't map cleanly to CSS.
 *       Thanks to
 *       http://style.cleverchimp.com/font_size_intervals/altintervals.html
 *       for reasonable mappings.
 */
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
    
    function transform($tag, $config, &$context) {
        
        if ($tag->type == 'end') {
            $new_tag = new HTMLPurifier_Token_End($this->transform_to);
            return $new_tag;
        }
        
        $attr = $tag->attr;
        $prepend_style = '';
        
        // handle color transform
        if (isset($attr['color'])) {
            $prepend_style .= 'color:' . $attr['color'] . ';';
            unset($attr['color']);
        }
        
        // handle face transform
        if (isset($attr['face'])) {
            $prepend_style .= 'font-family:' . $attr['face'] . ';';
            unset($attr['face']);
        }
        
        // handle size transform
        if (isset($attr['size'])) {
            if (isset($this->_size_lookup[$attr['size']])) {
                $prepend_style .= 'font-size:' .
                  $this->_size_lookup[$attr['size']] . ';';
            }
            unset($attr['size']);
        }
        
        if ($prepend_style) {
            $attr['style'] = isset($attr['style']) ?
                $prepend_style . $attr['style'] :
                $prepend_style;
        }
        
        $new_tag = $tag->copy();
        $new_tag->name = $this->transform_to;
        $new_tag->attr = $attr;
        
        return $new_tag;
        
    }
}

?>