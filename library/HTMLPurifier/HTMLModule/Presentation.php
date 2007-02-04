<?php

require_once 'HTMLPurifier/HTMLModule.php';

/**
 * XHTML 1.1 Presentation Module, defines hypertext links. Text Extension Module.
 */
class HTMLPurifier_HTMLModule_Presentation extends HTMLPurifier_HTMLModule
{
    
    var $elements = array('b', 'big', 'hr', 'i', 'small', 'sub', 'sup', 'tt');
    var $info = array();
    var $content_sets = array(
        'Block' => 'hr',
        'Inline' => 'b | big | i | small | sub | sup | tt'
    );
    
    function HTMLPurifier_HTMLModule_Presentation() {
        foreach ($this->elements as $element) {
            $this->info[$element] = new HTMLPurifier_ElementDef();
            $this->info[$element]->attr = array(0 => array('Common'));
            if ($element == 'hr') {
                $this->info[$element]->content_model_type = 'empty';
            } else {
                $this->info[$element]->content_model = '#PCDATA | Inline';
                $this->info[$element]->content_model_type = 'optional';
            }
        }
    }
    
}

?>