<?php

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
        
    }
    
}

?>