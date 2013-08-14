<?php

class HTMLPurifier_URIFilter_DisableExternalResourcesTest extends
      HTMLPurifier_URIFilter_DisableExternalTest
{

    public function setUp()
    {
        parent::setUp();
        $this->filter = new HTMLPurifier_URIFilter_DisableExternalResources();
        $var = true;
        $this->context->register('EmbeddedURI', $var);
    }

    public function testPreserveWhenNotEmbedded()
    {
        $this->context->destroy('EmbeddedURI'); // undo setUp
        $this->assertFiltering(
            'http://example.com'
        );
    }

}

// vim: et sw=4 sts=4
