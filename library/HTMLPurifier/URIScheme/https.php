<?php

require_once 'HTMLPurifier/URIScheme/http.php';

class HTMLPurifier_URIScheme_https extends HTMLPurifier_URIScheme_http {
    
    var $default_port = 443;
    
}

?>