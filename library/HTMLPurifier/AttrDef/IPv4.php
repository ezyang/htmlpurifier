<?php

require_once 'HTMLPurifier/AttrDef.php';

// spliced from Feyd's IPv6 function (pd)

class HTMLPurifier_AttrDef_IPv4 extends HTMLPurifier_AttrDef
{
    
    // regex is public so that IPv6 can reuse it
    var $ip4;
    
    function HTMLPurifier_AttrDef_IPv4() {
        $oct = '(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[0-9])'; // 0-255
        $this->ip4 = "(?:{$oct}\\.{$oct}\\.{$oct}\\.{$oct})";
    }
    
    function validate($aIP, $config, &$context) {
        
        if (preg_match('#^' . $this->ip4 . '$#s', $aIP))
        {
                return $aIP;
        }
        
        return false;
        
    }
    
}

?>