<?php

require_once 'HTMLPurifier/URIFilter/DisableExternalTest.php';
require_once 'HTMLPurifier/URIFilter/DisableExternalResources.php';

class HTMLPurifier_URIFilter_DisableExternalResourcesTest extends
      HTMLPurifier_URIFilter_DisableExternalTest
{
    
    function setUp() {
        parent::setUp();
        $this->filter = new HTMLPurifier_URIFilter_DisableExternalResources();
        $var = true;
        $this->context->register('EmbeddedURI', $var);
    }
    
    function testPreserveWhenNotEmbedded() {
        $this->context->destroy('EmbeddedURI'); // undo setUp
        $this->assertFiltering(
            'http://example.com'
        );
    }
    
}
