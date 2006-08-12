<?php

class HTMLPurifier_URIScheme
{
    
    function validateComponents($authority, $path, $query, $fragment) {
        return array($authority, $path, $query, $fragment);
    }
    
}

?>