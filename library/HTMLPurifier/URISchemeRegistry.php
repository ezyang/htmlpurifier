<?php

class HTMLPurifier_URISchemeRegistry
{
    
    // pass a registry object $prototype with a compatible interface and
    // the function will copy it and return it all further times.
    // pass bool true to reset to the default registry
    function &instance($prototype = null) {
        static $instance = null;
        if ($prototype !== null) {
            $instance = $prototype;
        } elseif ($instance === null || $prototype == true) {
            $instance = new HTMLPurifier_URISchemeRegistry();
        }
        return $instance;
    }
    
    function &getScheme($scheme) {}
    
}

?>