<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/URI.php';

// WARNING: INCOMPLETE UNIT TESTS!
// we also need to test all the configuration directives defined by this class

// http: is returned quite often when a URL is invalid. We have to change
// this behavior to just a plain old "FALSE"!

class HTMLPurifier_AttrDef_URITest extends HTMLPurifier_AttrDefHarness
{
    
    var $scheme, $components, $return_components;
    
    var $oldRegistry;
    
    function setUp() {
        // setup ensures that any twiddling around with the registry is reverted
        $this->oldRegistry = HTMLPurifier_URISchemeRegistry::instance();
        $this->def = new HTMLPurifier_AttrDef_URI(); // default
        parent::setUp();
    }
    
    function tearDown() {
        HTMLPurifier_URISchemeRegistry::instance($this->oldRegistry);
    }
    
    function &generateSchemeMock($scheme_names = array('http', 'mailto')) {
        generate_mock_once('HTMLPurifier_URIScheme');
        generate_mock_once('HTMLPurifier_URISchemeRegistry');
        
        // load a scheme registry mock to the singleton
        $registry =& HTMLPurifier_URISchemeRegistry::instance(
          new HTMLPurifier_URISchemeRegistryMock()
        );
        
        // add a pseudo-scheme to the registry for $scheme_names
        $scheme = new HTMLPurifier_URISchemeMock();
        foreach ($scheme_names as $name) {
            $registry->setReturnReference('getScheme', $scheme, array($name, '*', '*'));
        }
        // registry returns false if an invalid scheme is requested
        $registry->setReturnValue('getScheme', false, array('*', '*', '*'));
        
        return $scheme;
    }
    
    // PARSING RELATED TESTS
    
    function assertParsing($uri, $userinfo, $host, $port, $path, $query, $config = null, $context = null) {
        
        $this->prepareCommon($config, $context);
        $scheme =& $this->generateSchemeMock();
        
        // create components parameter list
        // Config and Context are wildcards due to PHP4 reference funkiness
        $components = array($userinfo, $host, $port, $path, $query, '*', '*');
        $scheme->expectOnce('validateComponents', $components);
        
        $def = new HTMLPurifier_AttrDef_URI();
        $def->validate($uri, $config, $context);
        
        $scheme->tally();
        
    }
    
    function testParsingImproperPercentEncoding() {
        // even though we don't resolve percent entities, we have to fix
        // improper percent-encodes. Taken one at a time:
        // %56 - V, which is an unreserved character
        // %fc - u with an umlaut, normalize to uppercase
        // %GJ - invalid characters in entity, encode %
        // %5 - prematurely terminated, encode %
        // %FC - u with umlaut, correct
        // note that Apache doesn't do such fixing, rather, it just claims
        // that the browser sent a "Bad Request".  See PercentEncoder.php
        // for more details
        $this->assertParsing(
            'http://www.example.com/%56%fc%GJ%5%FC',
            null, 'www.example.com', null, '/V%FC%25GJ%255%FC', null
        );
    }
    
    function testParsingInvalidHostThatLooksLikeIPv6Address() {
        $this->assertParsing(
            'http://[2001:0db8:85z3:08d3:1319:8a2e:0370:7334]',
            null, null, null, '', null
        );
    }
    
    function testParsingOverLargePort() {
        $this->assertParsing(
            'http://example.com:65536',
            null, 'example.com', null, '', null
        );
    }
    
    // OUTPUT RELATED TESTS
    // scheme is mocked to ensure only the URI is being tested
    
    function assertOutput($input_uri, $expect_uri, $userinfo, $host, $port, $path, $query, $config = null, $context = null) {
        
        // prepare mock machinery
        $this->prepareCommon($config, $context);
        $scheme =& $this->generateSchemeMock();
        $components = array($userinfo, $host, $port, $path, $query);
        $scheme->setReturnValue('validateComponents', $components);
        
        $def = new HTMLPurifier_AttrDef_URI();
        $result_uri = $def->validate($input_uri, $config, $context);
        if ($expect_uri === true) $expect_uri = $input_uri;
        $this->assertEqual($result_uri, $expect_uri);
        
    }
    
    function testOutputRegular() {
        $this->assertOutput(
            'http://user@authority.part:8080/now/the/path?query#frag', true,
            'user', 'authority.part', 8080, '/now/the/path', 'query'
        );
    }
    
    function testOutputEmpty() {
        $this->assertOutput(
            '', true,
            null, null, null, '', null
        );
    }
    
    function testOutputNullPath() {
        $this->assertOutput(
            '', true,
            null, null, null, null, null // usually shouldn't happen
        );
    }
    
    function testOutputPathAbsolute() { 
        $this->assertOutput(
            'http:/this/is/path', '/this/is/path',
            null, null, null, '/this/is/path', null
        );
    }
    
    function testOutputPathRootless() {
        $this->assertOutput(
            'http:this/is/path', 'this/is/path',
            null, null, null, 'this/is/path', null
        );
    }
    
    function testOutputPathEmpty() {
        $this->assertOutput(
            'http:', '',
            null, null, null, '', null
        );
    }
    
    // INTEGRATION TESTS
    
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
    
    function testConfigDisableExternal() {
        
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
    
    function testEmbeds() {
        
        // embedded URI
        $this->def = new HTMLPurifier_AttrDef_URI(true);
        
        $this->assertDef('http://sub.example.com/alas?foo=asd');
        $this->assertDef('mailto:foo@example.com', false);
        
    }
    
    function testConfigDisableExternalResources() {
        
        $this->config->set('URI', 'DisableExternalResources', true);
        
        $this->def = new HTMLPurifier_AttrDef_URI();
        $this->assertDef('http://sub.example.com/alas?foo=asd');
        $this->assertDef('/img.png');
        
        $this->def = new HTMLPurifier_AttrDef_URI(true);
        $this->assertDef('http://sub.example.com/alas?foo=asd', false);
        $this->assertDef('/img.png');
        
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
    
    function testBlacklist() {
        
        $this->config->set('URI', 'HostBlacklist', array('example.com', 'moo'));
        
        $this->assertDef('foo.txt');
        $this->assertDef('http://www.google.com/example.com/moo');
        
        $this->assertDef('http://example.com/#23', false);
        $this->assertDef('https://sub.domain.example.com/foobar', false);
        $this->assertDef('http://example.com.example.net/?whoo=foo', false);
        $this->assertDef('ftp://moo-moo.net/foo/foo/', false);
        
    }
    
    function testWhitelist() {
        /* unimplemented
        $this->config->set('URI', 'HostPolicy', 'DenyAll');
        $this->config->set('URI', 'HostWhitelist', array(null, 'google.com'));
        
        $this->assertDef('http://example.com/fo/google.com', false);
        $this->assertDef('server.txt');
        $this->assertDef('ftp://www.google.com/?t=a');
        $this->assertDef('http://google.com.tricky.spamsite.net', false);
        */
    }
    
}


