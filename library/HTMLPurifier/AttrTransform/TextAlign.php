<?php

require_once 'HTMLPurifier/AttrTransform.php';

/**
 * Pre-transform that changes deprecated align attribute to text-align.
 */
class HTMLPurifier_AttrTransform_TextAlign
extends HTMLPurifier_AttrTransform {
    
    function transform($attr, $config, &$context) {
        
        if (!isset($attr['align'])) return $attr;
        
        $align = $this->confiscateAttr($attr, 'align');
        $align = strtolower(trim($align));
        
        $values = array('left' => 1,
                        'right' => 1,
                        'center' => 1,
                        'justify' => 1);
        
        if (!isset($values[$align])) {
            return $attr;
        }
        
        $this->prependCSS($attr, "text-align:$align;");
        
        return $attr;
        
    }
    
}

?>