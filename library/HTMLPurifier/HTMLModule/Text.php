<?php

require_once 'HTMLPurifier/HTMLModule.php';

/**
 * XHTML 1.1 Text Module, defines basic text containers. Core Module.
 */
class HTMLPurifier_HTMLModule_Text extends HTMLPurifier_HTMLModule
{
    
    var $elements = array('abbr', 'acronym', 'address', 'blockquote',
        'br', 'cite', 'code', 'dfn', 'div', 'em', 'h1', 'h2', 'h3',
        'h4', 'h5', 'h6', 'kbd', 'p', 'pre', 'q', 'samp', 'span', 'strong',
        'var');
    
    var $info = array();
    
    var $content_sets = array(
        'Heading' => 'h1 | h2 | h3 | h4 | h5 | h6',
        'Block' => 'address | blockquote | div | p | pre',
        'Inline' => 'abbr | acronym | br | cite | code | dfn | em | kbd | q | samp | span | strong | var',
        'Flow' => 'Heading | Block | Inline'
    );
    
    function HTMLPurifier_HTMLModule_Text() {
        foreach ($this->elements as $element) {
            $this->info[$element] = new HTMLPurifier_ElementDef();
            // attributes
            if ($element == 'br') {
                $this->info[$element]->attr = array(0 => array('Core'));
            } elseif ($element == 'blockquote' || $element == 'q') {
                $this->info[$element]->attr = array(0 => array('Common'), 'cite' => 'URI');
            } else {
                $this->info[$element]->attr = array(0 => array('Common'));
            }
            // content models
            if ($element == 'br') {
                $this->info[$element]->content_model_type = 'empty';
            } elseif ($element == 'blockquote') {
                $this->info[$element]->content_model = 'Heading | Block | List';
                $this->info[$element]->content_model_type = 'strictblockquote';
            } elseif ($element == 'div') {
                $this->info[$element]->content_model = '#PCDATA | Flow';
                $this->info[$element]->content_model_type = 'optional';
            } else {
                $this->info[$element]->content_model = '#PCDATA | Inline';
                $this->info[$element]->content_model_type = 'optional';
            }
        }
        $this->info['p']->auto_close = array_flip(array(
                'address', 'blockquote', 'dd', 'dir', 'div', 'dl', 'dt',
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'ol', 'p', 'pre',
                'table', 'ul'
            ));
    }
    
}

?>