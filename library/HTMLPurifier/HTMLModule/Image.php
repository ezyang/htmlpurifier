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
    
    function setup($config) {
        $img =& $this->addElement(
            'img', true, 'Inline', 'Empty', 'Common',
            array(
                'alt*' => 'Text',
                // According to the spec, it's Length, but percents can
                // be abused, so we allow only Pixels. A trusted module
                // could overload this with the real value.
                'height' => 'Pixels',
                'width' => 'Pixels',
                'longdesc' => 'URI', 
                'src*' => new HTMLPurifier_AttrDef_URI(true), // embedded
            )
        );
        // kind of strange, but splitting things up would be inefficient
        $img->attr_transform_pre[] =
        $img->attr_transform_post[] =
            new HTMLPurifier_AttrTransform_ImgRequired();
    }
    
}

