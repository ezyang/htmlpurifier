<?php

require_once 'HTMLPurifier/AttrTransform.php';

// must be called POST validation

HTMLPurifier_ConfigDef::define(
    'Attr', 'DefaultInvalidImage', '',
    'This is the default image an img tag will be pointed to if it does '.
    'not have a valid src attribute.  In future versions, we may allow the '.
    'image tag to be removed completely, but due to design issues, this is '.
    'not possible right now.'
);

HTMLPurifier_ConfigDef::define(
    'Attr', 'DefaultInvalidImageAlt', 'Invalid image',
    'This is the content of the alt tag of an invalid image if the user '.
    'had not previously specified an alt attribute.  It has no effect when the '.
    'image is valid but there was no alt attribute present.'
);

class HTMLPurifier_AttrTransform_ImgRequired extends HTMLPurifier_AttrTransform
{
    
    function transform($attributes, $config) {
        
        $src = true;
        if (!isset($attributes['src'])) {
            $attributes['src'] = $config->get('Attr', 'DefaultInvalidImage');
            $src = false;
        }
        
        if (!isset($attributes['alt'])) {
            if ($src) {
                $attributes['alt'] = basename($attributes['src']);
            } else {
                $attributes['alt'] = $config->get('Attr', 'DefaultInvalidImageAlt');
            }
        }
        
        return $attributes;
        
    }
    
}

?>