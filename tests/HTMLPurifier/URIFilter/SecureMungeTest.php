<?php

class HTMLPurifier_URIFilter_SecureMungeTest extends HTMLPurifier_URIFilterHarness
{
    
    function setUp() {
        parent::setUp();
        $this->filter = new HTMLPurifier_URIFilter_SecureMunge();
        $this->setSecureMunge();
        $this->setSecretKey();
    }
    
    function setSecureMunge($uri = '/redirect.php?url=%s&checksum=%t') {
        $this->config->set('URI', 'SecureMunge', $uri);
    }
    
    function setSecretKey($key = 'secret') {
        $this->config->set('URI', 'SecureMungeSecretKey', $key);
    }
    
    function testPreserve() {
        $this->assertFiltering('/local');
    }
    
    function testStandardMunge() {
        $this->assertFiltering('http://google.com', '/redirect.php?url=http%3A%2F%2Fgoogle.com&checksum=0072e2f817fd2844825def74e54443debecf0892');
    }
    
    function testIgnoreUnknownSchemes() {
        // This should be integration tested as well to be false
        $this->assertFiltering('javascript:', true);
    }
    
    function testIgnoreUnbrowsableSchemes() {
        $this->assertFiltering('news:', true);
    }
    
    function testMungeToDirectory() {
        $this->setSecureMunge('/links/%s/%t');
        $this->assertFiltering('http://google.com', '/links/http%3A%2F%2Fgoogle.com/0072e2f817fd2844825def74e54443debecf0892');
    }
    
    function testErrorNoSecretKey() {
        $this->setSecretKey(null);
        $this->expectError('URI.SecureMunge is being ignored due to lack of value for URI.SecureMungeSecretKey');
        $this->assertFiltering('http://google.com');
    }
    
}
