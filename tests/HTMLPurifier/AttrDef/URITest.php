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
    
    function test_validate_configDisableExternal() {
        
        $this->def = new HTMLPurifier_AttrDef_URI();
        
        $this->config->set('URI', 'DisableExternal', true);
        $this->config->set('URI', 'Host', 'sub.example.com');
        
        $this->assertDef('/foobar.txt');
        $this->assertDef('http://google.com/', false);
        $this->assertDef('http://sub.example.com/alas?foo=asd');
        $this->assertDef('http://example.com/teehee', false);
        $this->assertDef('http://www.example.com/#man', false);
        $this->assertDef('http://go.sub.example.com/perhaps?p=foo');
        
    }
    
    function test_validate_configDisableExternalResources() {
        
        $this->config->set('URI', 'DisableExternalResources', true);
        
        $this->assertDef('http://sub.example.com/alas?foo=asd');
        $this->assertDef('/img.png');
        
        $this->def = new HTMLPurifier_AttrDef_URI(true);
        
        $this->assertDef('http://sub.example.com/alas?foo=asd', false);
        $this->assertDef('/img.png');
        
    }
    
    function test_validate_configBlacklist() {
        
        $this->config->set('URI', 'HostBlacklist', array('example.com', 'moo'));
        
        $this->assertDef('foo.txt');
        $this->assertDef('http://www.google.com/example.com/moo');
        
        $this->assertDef('http://example.com/#23', false);
        $this->assertDef('https://sub.domain.example.com/foobar', false);
        $this->assertDef('http://example.com.example.net/?whoo=foo', false);
        $this->assertDef('ftp://moo-moo.net/foo/foo/', false);
        
    }
    
    /*
    function test_validate_configWhitelist() {
        
        $this->config->set('URI', 'HostPolicy', 'DenyAll');
        $this->config->set('URI', 'HostWhitelist', array(null, 'google.com'));
        
        $this->assertDef('http://example.com/fo/google.com', false);
        $this->assertDef('server.txt');
        $this->assertDef('ftp://www.google.com/?t=a');
        $this->assertDef('http://google.com.tricky.spamsite.net', false);
        
    }
    */
    
}


