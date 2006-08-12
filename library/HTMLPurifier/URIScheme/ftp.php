<?php

require_once 'HTMLPurifier/URIScheme.php';

class HTMLPurifier_URIScheme_ftp extends HTMLPurifier_URIScheme {
    
    var $default_port = 21;
    
    function validateComponents(
        $userinfo, $host, $port, $path, $query, $config
    ) {
        list($userinfo, $host, $port, $path, $query) = 
            parent::validateComponents(
                $userinfo, $host, $port, $path, $query, $config );
        // typecode check needed on path
        return array($userinfo, $host, $port, $path, null);
    }
    
}

?>