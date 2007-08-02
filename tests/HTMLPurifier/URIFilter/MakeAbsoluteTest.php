<?php

require_once 'HTMLPurifier/URIFilter/MakeAbsolute.php';
require_once 'HTMLPurifier/URIFilterHarness.php';

class HTMLPurifier_URIFilter_MakeAbsoluteTest extends HTMLPurifier_URIFilterHarness
{
    
    function setUp() {
        parent::setUp();
        $this->filter = new HTMLPurifier_URIFilter_MakeAbsolute();
        $this->setBase();
    }
    
    function setBase($base = 'http://example.com/foo/bar.html?q=s#frag') {
        $this->config->set('URI', 'Base', $base);
    }
    
    // corresponding to RFC 2396
    
    function testPreserveAbsolute() {
        $this->assertFiltering('http://example.com/foo.html');
    }
    
    function testFilterBlank() {
        $this->assertFiltering('', 'http://example.com/foo/bar.html?q=s');
    }
    
    function testFilterEmptyPath() {
        $this->assertFiltering('?q=s#frag', 'http://example.com/foo/bar.html?q=s#frag');
    }
    
    function testPreserveAltScheme() {
        $this->assertFiltering('mailto:bob@example.com');
    }
    
    function testFilterIgnoreHTTPSpecialCase() {
        $this->assertFiltering('http:/', 'http://example.com/');
    }
    
    function testFilterAbsolutePath() {
        $this->assertFiltering('/foo.txt', 'http://example.com/foo.txt');
    }
    
    function testFilterRelativePath() {
        $this->assertFiltering('baz.txt', 'http://example.com/foo/baz.txt');
    }
    
    function testFilterRelativePathWithInternalDot() {
        $this->assertFiltering('./baz.txt', 'http://example.com/foo/baz.txt');
    }
    
    function testFilterRelativePathWithEndingDot() {
        $this->assertFiltering('baz/.', 'http://example.com/foo/baz/');
    }
    
    function testFilterRelativePathDot() {
        $this->assertFiltering('.', 'http://example.com/foo/');
    }
    
    function testFilterRelativePathWithInternalDotDot() {
        $this->assertFiltering('../baz.txt', 'http://example.com/baz.txt');
    }
    
    function testFilterRelativePathWithEndingDotDot() {
        $this->assertFiltering('..', 'http://example.com/');
    }
    
    function testFilterRelativePathTooManyDotDots() {
        $this->assertFiltering('../../', 'http://example.com/');
    }
    
    function testFilterAppendingQueryAndFragment() {
        $this->assertFiltering('/foo.php?q=s#frag', 'http://example.com/foo.php?q=s#frag');
    }
    
    // edge cases below
    
    function testFilterAbsolutePathBase() {
        $this->setBase('/foo/baz.txt');
        $this->assertFiltering('test.php', '/foo/test.php');
    }
    
    function testFilterAbsolutePathBaseDirectory() {
        $this->setBase('/foo/');
        $this->assertFiltering('test.php', '/foo/test.php');
    }
    
    function testFilterAbsolutePathBaseBelow() {
        $this->setBase('/foo/baz.txt');
        $this->assertFiltering('../../test.php', '/test.php');
    }
    
    function testFilterRelativePathBase() {
        $this->setBase('foo/baz.html');
        $this->assertFiltering('foo.php', 'foo/foo.php');
    }
    
    function testFilterRelativePathBaseBelow() {
        $this->setBase('../baz.html');
        $this->assertFiltering('test/strike.html', '../test/strike.html');
    }
    
    function testFilterRelativePathBaseWithAbsoluteURI() {
        $this->setBase('../baz.html');
        $this->assertFiltering('/test/strike.html');
    }
    
    function testFilterRelativePathBaseWithDot() {
        $this->setBase('../baz.html');
        $this->assertFiltering('.', '../');
    }
    
    // error case
    
    function testErrorNoBase() {
        $this->setBase(null);
        $this->expectError('URI.MakeAbsolute is being ignored due to lack of value for URI.Base configuration');
        $this->assertFiltering('foo/bar.txt');
    }
    
}
