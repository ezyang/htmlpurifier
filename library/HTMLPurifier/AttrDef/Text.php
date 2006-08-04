<?php

require_once 'HTMLPurifier/AttrDef.php';

class HTMLPurifier_AttrDef_Text extends HTMLPurifier_AttrDef
{
    
    function validate($string) {
        $new_string = $this->parseCDATA($string);
        return ($string == $new_string) ? true : $new_string;
    }
    
}

?>