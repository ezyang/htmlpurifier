<?php

require_once 'HTMLPurifier/URISchemeRegistry.php';

class HTMLPurifier_URISchemeRegistryTest extends UnitTestCase
{
    
    function test() {
        
        generate_mock_once('HTMLPurifier_URIScheme');
        
        $config = HTMLPurifier_Config::createDefault();
        $config->set('URI', 'AllowedSchemes', array('http' => true, 'telnet' => true));
        
        $registry = new HTMLPurifier_URISchemeRegistry();
        $this->assertIsA($registry->getScheme('http'), 'HTMLPurifier_URIScheme_http');
        
        $scheme_http = new HTMLPurifier_URISchemeMock($this);
        $scheme_telnet = new HTMLPurifier_URISchemeMock($this);
        $scheme_foobar = new HTMLPurifier_URISchemeMock($this);
        
        // register a new scheme
        $registry->register('telnet', $scheme_telnet);
        $this->assertIdentical($registry->getScheme('telnet', $config), $scheme_telnet);
        
        // overload a scheme, this is FINAL (forget about defaults)
        $registry->register('http', $scheme_http);
        $this->assertIdentical($registry->getScheme('http', $config), $scheme_http);
        
        // when we register a scheme, it's automatically allowed
        $registry->register('foobar', $scheme_foobar);
        $this->assertIdentical($registry->getScheme('foobar', $config), $scheme_foobar);
        
        // however, don't try to get a scheme that isn't allowed
        $this->assertNull($registry->getScheme('ftp', $config));
        
    }
    
}

?>