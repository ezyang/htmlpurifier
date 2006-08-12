<?php

require_once 'HTMLPurifier/URIScheme.php';

class HTMLPurifier_URIScheme_news extends HTMLPurifier_URIScheme {
    
    function validateComponents(
        $userinfo, $host, $port, $path, $query, $config
    ) {
        list($userinfo, $host, $port, $path, $query) = 
            parent::validateComponents(
                $userinfo, $host, $port, $path, $query, $config );
        // typecode check needed on path
        return array(null, null, null, $path, null);
    }
    
}

?>