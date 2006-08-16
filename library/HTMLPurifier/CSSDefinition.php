<?php

require_once 'HTMLPurifier/AttrDef/Enum.php';
require_once 'HTMLPurifier/AttrDef/Color.php';
require_once 'HTMLPurifier/AttrDef/Composite.php';
require_once 'HTMLPurifier/AttrDef/CSSLength.php';
require_once 'HTMLPurifier/AttrDef/Percentage.php';

class HTMLPurifier_CSSDefinition
{
    
    var $info = array();
    
    function &instance($prototype = null) {
        static $instance = null;
        if ($prototype) {
            $instance = $prototype;
        } elseif (!$instance) {
            $instance = new HTMLPurifier_CSSDefinition();
            $instance->setup();
        }
        return $instance;
    }
    
    function HTMLPurifier_CSSDefinition() {}
    
    function setup() {
        
        $this->info['text-align'] = new HTMLPurifier_AttrDef_Enum(
            array('left', 'right', 'center', 'justify'), false);
        $this->info['border-bottom-style'] = 
        $this->info['border-right-style'] = 
        $this->info['border-left-style'] = 
        $this->info['border-top-style'] =  new HTMLPurifier_AttrDef_Enum(
            array('none', 'hidden', 'dotted', 'dashed', 'solid', 'double',
            'groove', 'ridge', 'inset', 'outset'), false);
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
        $this->info['text-transform'] = new HTMLPurifier_AttrDef_Enum(
            array('capitalize', 'uppercase', 'lowercase', 'none'), false);
        $this->info['color'] = new HTMLPurifier_AttrDef_Color();
        
        $this->info['border-top-color'] = 
        $this->info['border-bottom-color'] = 
        $this->info['border-left-color'] = 
        $this->info['border-right-color'] = 
        $this->info['background-color'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_Enum(array('transparent')),
            new HTMLPurifier_AttrDef_Color()
        ));
        
        $this->info['border-top-width'] = 
        $this->info['border-bottom-width'] = 
        $this->info['border-left-width'] = 
        $this->info['border-right-width'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_Enum(array('thin', 'medium', 'thick')),
            new HTMLPurifier_AttrDef_CSSLength(true) //disallow negative
        ));
        
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
        
        $this->info['margin-top'] = 
        $this->info['margin-bottom'] = 
        $this->info['margin-left'] = 
        $this->info['margin-right'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_CSSLength(),
            new HTMLPurifier_AttrDef_Percentage(),
            new HTMLPurifier_AttrDef_Enum(array('auto'))
        ));
        
        // non-negative
        $this->info['padding-top'] = 
        $this->info['padding-bottom'] = 
        $this->info['padding-left'] = 
        $this->info['padding-right'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_CSSLength(true),
            new HTMLPurifier_AttrDef_Percentage(true)
        ));
        
        $this->info['text-indent'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_CSSLength(),
            new HTMLPurifier_AttrDef_Percentage()
        ));
        
        $this->info['width'] = new HTMLPurifier_AttrDef_Composite(array(
            new HTMLPurifier_AttrDef_CSSLength(true),
            new HTMLPurifier_AttrDef_Percentage(true),
            new HTMLPurifier_AttrDef_Enum(array('auto'))
        ));
        
        // this could use specialized code
        $this->info['font-weight'] = new HTMLPurifier_AttrDef_Enum(
            array('normal', 'bold', 'bolder', 'lighter', '100', '200', '300',
            '400', '500', '600', '700', '800', '900'), false);
        
    }
    
}

?>