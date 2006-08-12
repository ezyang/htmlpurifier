<?php

require_once 'HTMLPurifier/URIScheme.php';

class HTMLPurifier_URIScheme_nntp extends HTMLPurifier_URIScheme {
    
    var $default_port = 119;
    
    function validateComponents(
        $userinfo, $host, $port, $path, $query, $config
    ) {
        list($userinfo, $host, $port, $path, $query) = 
            parent::validateComponents(
                $userinfo, $host, $port, $path, $query, $config );
        return array(null, $host, $port, $path, null);
    }
    
}

?>