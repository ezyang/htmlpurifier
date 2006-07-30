<?php

// AttrDef = Attribute Definition
class HTMLPurifier_AttrDef
{
    function HTMLPurifier_AttrDef() {}
    
    function validate() {
        trigger_error('Cannot call abstract function', E_USER_ERROR);
    }
}

?>