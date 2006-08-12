<?php

require_once 'HTMLPurifier/AttrContext.php';

// AttrDef = Attribute Definition
class HTMLPurifier_AttrDef
{
    function HTMLPurifier_AttrDef() {}
    
    function validate($string, $config, &$context) {
        trigger_error('Cannot call abstract function', E_USER_ERROR);
    }
    
    function parseCDATA($string) {
        $string = trim($string);
        $string = str_replace("\n", '', $string);
        $string = str_replace(array("\r", "\t"), ' ', $string);
        return $string;
    }
}

?>