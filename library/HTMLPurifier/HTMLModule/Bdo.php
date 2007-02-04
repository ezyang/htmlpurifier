<?php

require_once 'HTMLPurifier/HTMLModule.php';

/**
 * XHTML 1.1 Bi-directional Text Module, defines elements that
 * declare directionality of content. Text Extension Module.
 */
class HTMLPurifier_HTMLModule_Bdo extends HTMLPurifier_HTMLModule
{
    
    var $elements = array('bdo');
    var $info = array();
    var $content_sets = array('Inline' => 'bdo');
    var $attr_collection = array(
        'I18N' => array('dir' => false)
    );
    
    function HTMLPurifier_HTMLModule_Bdo() {
        $dir = new HTMLPurifier_AttrDef_Enum(array('ltr','rtl'), false);
        $this->attr_collection['I18N']['dir'] = $dir;
        $this->info['bdo'] = new HTMLPurifier_ElementDef();
        $this->info['bdo']->attr = array(
            0 => array('Core'),
            'dir' => $dir, // required
            'lang' => 'Lang',
            'xml:lang' => 'Lang'
        );
        $this->info['bdo']->content_model = '#PCDATA | Inline';
        $this->info['bdo']->content_model_type = 'optional';
        $this->info['bdo']->content_model_type = 'optional';
        $this->info['bdo']->attr_transform_post[] = new HTMLPurifier_AttrTransform_BdoDir();
    }
    
}

?>