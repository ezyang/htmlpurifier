<?php

require_once 'HTMLPurifier/AttrDef/Enum.php';
require_once 'HTMLPurifier/AttrDef/Color.php';
require_once 'HTMLPurifier/AttrDef/Composite.php';
require_once 'HTMLPurifier/AttrDef/CSSLength.php';
require_once 'HTMLPurifier/AttrDef/Percentage.php';
require_once 'HTMLPurifier/AttrDef/Multiple.php';
require_once 'HTMLPurifier/AttrDef/TextDecoration.php';
require_once 'HTMLPurifier/AttrDef/FontFamily.php';
require_once 'HTMLPurifier/AttrDef/Font.php';
require_once 'HTMLPurifier/AttrDef/Border.php';
require_once 'HTMLPurifier/AttrDef/ListStyle.php';

/**
 * Defines allowed CSS attributes and what their values are.
 * @see HTMLPurifier_HTMLDefinition
 */
class HTMLPurifier_CSSDefinition
{
    
    /**
     * Assoc array of attribute name to definition object.
     */
    var $info = array();
    
    /**
     * Constructs the info array.  The meat of this class.
     */
    function setup($config) {
        
        $this->info['text-align'] = new HTMLPurifier_AttrDef_Enum(
            array('left', 'right', 'center', 'justify'), false);
        
        $border_style =
        $this->info['border-bottom-style'] = 
        $this->info['border-right-style'] = 
        $this->info['border-left-style'] = 
        $this->info['border-top-style'] =  new HTMLPurifier_AttrDef_Enum(
            array('none', 'hidden', 'dotted', 'dashed', 'solid', 'double',
            'groove', 'ridge', 'inset', 'outset'), false);
        
        $this->info['border-style'] = new HTMLPurifier_AttrDef_Multiple($border_style);
        
        $this->info['clear'] = new HTMLPurifier_AttrDef_Enum(
            array('none', 'left', 'right', 'both'), false);
        $this->info['float'] = new HTMLPurifier_AttrDef_Enum(
            array('none', 'left', 'right'), false);
        $this->info['font-style'] = new HTMLPurifier_AttrDef_Enum(
            array('normal', 'italic', 'oblique'), false);
        $this->info['font-variant'] = new HTMLPurifier_AttrDef_Enum(
            array('normal', 'small-caps'), false);
        
        $this->info['list-style-position'] = new HTMLPurifier_AttrDef_Enum(
            array('inside', 'outside'), false);
        $this->info['list-style-type'] = new HTMLPurifier_AttrDef_Enum(
            array('disc', 'circle', 'square', 'decimal', 'lower-roman',
            'upper-roman', 'lower-alpha', 'upper-alpha'), false);
        
        $this->info['list-style'] = new HTMLPurifier_AttrDef_ListStyle($config);
        
        $this->info['text-transform'] = new HTMLPurifier_AttrDef_Enum(
            array('capitalize', 'uppercase', 'lowercase', 'none'), false);
        $this->info['color'] = new HTMLPurifier_AttrDef_Color();
        
        // technically speaking, this one should get its own validator, but
        // since we don't support background images, it effectively is
        // equivalent to color.  The only trouble is that if the author
        // specifies an image and a color, they'll both end up getting dropped,
        // even though we ought to implement it and just discard the image
        // info.  This will be fixed in a later version (see TODO) when
        // better URI filtering is implemented.
        $this->info['background'] = 
        
        $border_color = 
        $this->info['border-top-color'] = 
        $this->info['border-bottom-color'] = 
        $this->info['border-left-color'] = 
        $this->info['border-right-color'] = 
        $this->info['background-color'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_Enum(array('transparent')),
            new HTMLPurifier_AttrDef_Color()
        ));
        
        $this->info['border-color'] = new HTMLPurifier_AttrDef_Multiple($border_color);
        
        $border_width = 
        $this->info['border-top-width'] = 
        $this->info['border-bottom-width'] = 
        $this->info['border-left-width'] = 
        $this->info['border-right-width'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_Enum(array('thin', 'medium', 'thick')),
            new HTMLPurifier_AttrDef_CSSLength(true) //disallow negative
        ));
        
        $this->info['border-width'] = new HTMLPurifier_AttrDef_Multiple($border_width);
        
        $this->info['letter-spacing'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_Enum(array('normal')),
            new HTMLPurifier_AttrDef_CSSLength()
        ));
        
        $this->info['word-spacing'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_Enum(array('normal')),
            new HTMLPurifier_AttrDef_CSSLength()
        ));
        
        $this->info['font-size'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_Enum(array('xx-small', 'x-small',
                'small', 'medium', 'large', 'x-large', 'xx-large',
                'larger', 'smaller')),
            new HTMLPurifier_AttrDef_Percentage(),
            new HTMLPurifier_AttrDef_CSSLength()
        ));
        
        $this->info['line-height'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_Enum(array('normal')),
            new HTMLPurifier_AttrDef_Number(true), // no negatives
            new HTMLPurifier_AttrDef_CSSLength(true),
            new HTMLPurifier_AttrDef_Percentage(true)
        ));
        
        $margin =
        $this->info['margin-top'] = 
        $this->info['margin-bottom'] = 
        $this->info['margin-left'] = 
        $this->info['margin-right'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_CSSLength(),
            new HTMLPurifier_AttrDef_Percentage(),
            new HTMLPurifier_AttrDef_Enum(array('auto'))
        ));
        
        $this->info['margin'] = new HTMLPurifier_AttrDef_Multiple($margin);
        
        // non-negative
        $padding =
        $this->info['padding-top'] = 
        $this->info['padding-bottom'] = 
        $this->info['padding-left'] = 
        $this->info['padding-right'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_CSSLength(true),
            new HTMLPurifier_AttrDef_Percentage(true)
        ));
        
        $this->info['padding'] = new HTMLPurifier_AttrDef_Multiple($padding);
        
        $this->info['text-indent'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_CSSLength(),
            new HTMLPurifier_AttrDef_Percentage()
        ));
        
        $this->info['width'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_CSSLength(true),
            new HTMLPurifier_AttrDef_Percentage(true),
            new HTMLPurifier_AttrDef_Enum(array('auto'))
        ));
        
        $this->info['text-decoration'] = new HTMLPurifier_AttrDef_TextDecoration();
        
        $this->info['font-family'] = new HTMLPurifier_AttrDef_FontFamily();
        
        // this could use specialized code
        $this->info['font-weight'] = new HTMLPurifier_AttrDef_Enum(
            array('normal', 'bold', 'bolder', 'lighter', '100', '200', '300',
            '400', '500', '600', '700', '800', '900'), false);
        
        // MUST be called after other font properties, as it references
        // a CSSDefinition object
        $this->info['font'] = new HTMLPurifier_AttrDef_Font($config);
        
        // same here
        $this->info['border'] =
        $this->info['border-bottom'] = 
        $this->info['border-top'] = 
        $this->info['border-left'] = 
        $this->info['border-right'] = new HTMLPurifier_AttrDef_Border($config);
        
        $this->info['border-collapse'] = new HTMLPurifier_AttrDef_Enum(array(
            'collapse', 'seperate'));
        
        $this->info['caption-side'] = new HTMLPurifier_AttrDef_Enum(array(
            'top', 'bottom'));
        
        $this->info['table-layout'] = new HTMLPurifier_AttrDef_Enum(array(
            'auto', 'fixed'));
        
        $this->info['vertical-align'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_Enum(array('baseline', 'sub', 'super',
                'top', 'text-top', 'middle', 'bottom', 'text-bottom')),
            new HTMLPurifier_AttrDef_CSSLength(),
            new HTMLPurifier_AttrDef_Percentage()
        ));
        
    }
    
}

?>