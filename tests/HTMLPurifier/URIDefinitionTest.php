<?php

require_once 'HTMLPurifier/URIHarness.php';
require_once 'HTMLPurifier/URIDefinition.php';

class HTMLPurifier_URIDefinitionTest extends HTMLPurifier_URIHarness
{
    
    function createFilterMock($expect = true, $result = true) {
        generate_mock_once('HTMLPurifier_URIFilter');
        $mock = new HTMLPurifier_URIFilterMock();
        if ($expect) $mock->expectOnce('filter');
        else $mock->expectNever('filter');
        $mock->setReturnValue('filter', $result);
        return $mock;
    }
    
    function test_filter() {
        $def = new HTMLPurifier_URIDefinition();
        $def->filters[] = $this->createFilterMock();
        $def->filters[] = $this->createFilterMock();
        $uri = $this->createURI('test');
        $this->assertTrue($def->filter($uri, $this->config, $this->context));
    }
    
    function test_filter_earlyAbortIfFail() {
        $def = new HTMLPurifier_URIDefinition();
        $def->filters[] = $this->createFilterMock(true, false);
        $def->filters[] = $this->createFilterMock(false); // never called
        $uri = $this->createURI('test');
        $this->assertFalse($def->filter($uri, $this->config, $this->context));
    }
    
}
