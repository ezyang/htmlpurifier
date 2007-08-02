<?php

require_once 'HTMLPurifier/URIHarness.php';

class HTMLPurifier_URIFilterHarness extends HTMLPurifier_URIHarness
{
    
    function assertFiltering($uri, $expect_uri = true) {
        $this->prepareURI($uri, $expect_uri);
        $this->filter->prepare($this->config, $this->context);
        $result = $this->filter->filter($uri, $this->config, $this->context);
        $this->assertEitherFailOrIdentical($result, $uri, $expect_uri);
    }
    
}
