<?php

require_once 'HTMLPurifier/HTMLModule.php';

/**
 * XHTML 1.1 Edit Module, defines editing-related elements. Text Extension Module.
 */
class HTMLPurifier_HTMLModule_StyleAttribute extends HTMLPurifier_HTMLModule
{
    var $attr_collection = array(
        'Style' => array('style' => false),
        'Core' => array(0 => array('Style'))
    );
    
    function HTMLPurifier_HTMLModule_StyleAttribute() {
        $this->attr_collection['Style']['style'] = new HTMLPurifier_AttrDef_CSS();
    }
    
}

?>