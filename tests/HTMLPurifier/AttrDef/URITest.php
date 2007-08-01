<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/URI.php';

/**
 * @todo Aim for complete code coverage with mocks
 */
class HTMLPurifier_AttrDef_URITest extends HTMLPurifier_AttrDefHarness
{
    
    function setUp() {
        $this->def = new HTMLPurifier_AttrDef_URI();
        parent::setUp();
    }
    
    function testIntegration() {
        $this->assertDef('http://www.google.com/');
        $this->assertDef('http:', '');
        $this->assertDef('http:/foo', '/foo');
        $this->assertDef('javascript:bad_stuff();', false);
        $this->assertDef('ftp://www.example.com/');
        $this->assertDef('news:rec.alt');
        $this->assertDef('nntp://news.example.com/324234');
        $this->assertDef('mailto:bob@example.com');
    }
    
    function testIntegrationWithPercentEncoder() {
        $this->assertDef(
            'http://www.example.com/%56%fc%GJ%5%FC',
            'http://www.example.com/V%FC%25GJ%255%FC'
        );
    }
    
    function testEmbeds() {
        $this->def = new HTMLPurifier_AttrDef_URI(true);
        $this->assertDef('http://sub.example.com/alas?foo=asd');
        $this->assertDef('mailto:foo@example.com', false);
    }
    
    function testConfigMunge() {
        $this->config->set('URI', 'Munge', 'http://www.google.com/url?q=%s');
        $this->assertDef(
            'http://www.example.com/',
            'http://www.google.com/url?q=http%3A%2F%2Fwww.example.com%2F'
        );
        $this->assertDef('index.html');
        $this->assertDef('javascript:foobar();', false);
    }
    
}


