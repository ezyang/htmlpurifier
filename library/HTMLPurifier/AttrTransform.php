<?php

// AttrTransform = Attribute Transformation, when handling one attribute
//                 isn't enough
class HTMLPurifier_AttrTransform
{
    function HTMLPurifier_AttrTransform() {}
    
    function transform($token, $config = null) {
        trigger_error('Cannot call abstract function', E_USER_ERROR);
    }
}

?>