<?php

require_once 'HTMLPurifier/HTMLModule.php';

/**
 * XHTML 1.1 Image Module provides basic image embedding.
 */
class HTMLPurifier_HTMLModule_Image extends HTMLPurifier_HTMLModule
{
    
    var $elements = array('img');
    var $info = array();
    var $content_sets = array('Inline' => 'img');
    
    function HTMLPurifier_HTMLModule_Image() {
        $this->info['img'] = new HTMLPurifier_ElementDef();
        $this->info['img']->attr = array(
            0 => array('Common'),
            'alt' => 'Text',
            'height' => 'Length',
            'longdesc' => 'URI', 
            'src' => new HTMLPurifier_AttrDef_URI(true), // embedded
            'width' => 'Length'
        );
        $this->info['img']->content_model_type = 'empty';
        $this->info['img']->attr_transform_post[] =
            new HTMLPurifier_AttrTransform_ImgRequired();
    }
    
}

?>