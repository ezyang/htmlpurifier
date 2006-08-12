<?php

require_once 'HTMLPurifier/URISchemeRegistry.php';

class HTMLPurifier_URISchemeRegistryTest extends UnitTestCase
{
    
    function test() {
        
        $registry =& HTMLPurifier_URISchemeRegistry::instance();
        $this->assertIsA($registry->getScheme('http'), 'HTMLPurifier_URIScheme_http');
        
        // to come: overloading and custom schemes, as well as changing the
        // configuration values used by this class
        
    }
    
}

?>