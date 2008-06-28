<?php

class HTMLPurifier_HTMLModule_Name extends HTMLPurifier_HTMLModule
{
    
    var $name = 'Name';
    
    function setup($config) {
        $elements = array('a', 'applet', 'form', 'frame', 'iframe', 'img', 'map');
        foreach ($elements as $name) {
            $element =& $this->addBlankElement($name);
            $element->attr['name'] = 'ID';
            unset($element);
        }
    }
    
}
