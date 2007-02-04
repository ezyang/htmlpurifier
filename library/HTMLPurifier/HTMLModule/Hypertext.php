<?php

require_once 'HTMLPurifier/HTMLModule.php';

/**
 * XHTML 1.1 Hypertext Module, defines hypertext links.
 */
class HTMLPurifier_HTMLModule_Hypertext extends HTMLPurifier_HTMLModule
{
    
    var $elements = array('a');
    var $info = array();
    var $content_sets = array('Inline' => 'a');
    
    function HTMLPurifier_HTMLModule_Hypertext() {
        $this->info['a'] = new HTMLPurifier_ElementDef();
        $this->info['a']->attr = array(
            0 => array('Common'),
            // 'accesskey' => 'Character',
            // 'charset' => 'Charset',
            'href' => 'URI',
            //'hreflang' => 'LanguageCode',
            //'rel' => 'LinkTypes',
            //'rev' => 'LinkTypes',
            //'tabindex' => 'Number',
            //'type' => 'ContentType',
        );
        $this->info['a']->content_model = '#PCDATA | Inline';
        $this->info['a']->content_model_type = 'optional';
        $this->info['a']->excludes = array('a' => true);
    }
    
}

?>