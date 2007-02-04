<?php

require_once 'HTMLPurifier/HTMLModule.php';

/**
 * XHTML 1.1 Edit Module, defines editing-related elements. Text Extension Module.
 */
class HTMLPurifier_HTMLModule_Edit extends HTMLPurifier_HTMLModule
{
    
    var $elements = array('del', 'ins');
    var $info = array();
    var $content_sets = array('Inline' => 'del | ins');
    
    function HTMLPurifier_HTMLModule_Edit() {
        foreach ($this->elements as $element) {
            $this->info[$element] = new HTMLPurifier_ElementDef();
            $this->info[$element]->attr = array(
                0 => array('Common'),
                'cite' => 'URI',
                // 'datetime' => 'Datetime' // Datetime not implemented
            );
            $this->info[$element]->content_model = '#PCDATA | Inline ! #PCDATA | Flow';
            $this->info[$element]->content_model_type = 'chameleon';
        }
    }
    
}

?>