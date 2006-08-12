<?php

require_once 'HTMLPurifier/AttrDef.php';

class HTMLPurifier_AttrDef_Text extends HTMLPurifier_AttrDef
{
    
    function validate($string, $config, &$context) {
        return $this->parseCDATA($string);
    }
    
}

?>