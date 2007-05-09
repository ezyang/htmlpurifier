<?php

require_once 'HTMLPurifier/HTMLModule.php';

require_once 'HTMLPurifier/AttrDef/URI.php';
require_once 'HTMLPurifier/AttrTransform/ImgRequired.php';

/**
 * XHTML 1.1 Image Module provides basic image embedding.
 * @note There is specialized code for removing empty images in
 *       HTMLPurifier_Strategy_RemoveForeignElements
 */
class HTMLPurifier_HTMLModule_Image extends HTMLPurifier_HTMLModule
{
    
    var $name = 'Image';
    var $elements = array('img');
    var $content_sets = array('Inline' => 'img');
    
    function HTMLPurifier_HTMLModule_Image() {
        $this->addElement(
            'img', true, 'Inline', 'Empty', 'Common',
            array(
                'alt' => 'Text',
                'height' => 'Length',
                'longdesc' => 'URI', 
                'src' => new HTMLPurifier_AttrDef_URI(true), // embedded
                'width' => 'Length'
            )
        );
        $this->info['img']->attr_transform_post[] =
            new HTMLPurifier_AttrTransform_ImgRequired();
    }
    
}

?>