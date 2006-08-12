<?php

class HTMLPurifier_URIScheme
{
    
    var $default_port = null;
    
    function validateComponents(
        $userinfo, $host, $port, $path, $query, $config
    ) {
        if ($this->default_port == $port) $port = null;
        return array($userinfo, $host, $port, $path, $query);
    }
    
}

?>