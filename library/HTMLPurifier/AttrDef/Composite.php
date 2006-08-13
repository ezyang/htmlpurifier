<?php

class HTMLPurifier_AttrDef_Composite extends HTMLPurifier_AttrDef
{
    
    var $defs;
    
    function HTMLPurifier_AttrDef_Composite(&$defs) {
        $this->defs =& $defs;
    }
    
    function validate($string, $config, &$context) {
        foreach ($this->defs as $i => $def) {
            $result = $this->defs[$i]->validate($string, $config, $context);
            if ($result !== false) return $result;
        }
        return false;
    }
    
}

?>