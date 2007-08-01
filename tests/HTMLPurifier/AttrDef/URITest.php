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
    
    function prepareCommon(&$config, &$context) {
        $config = HTMLPurifier_Config::create($config);
        if (!$context) $context = new HTMLPurifier_Context();
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
    
    function testParsingRegular() {
        $this->assertParsing(
            'http://www.example.com/webhp?q=foo#result2',
            null, 'www.example.com', null, '/webhp', 'q=foo'
        );
    }
    
    function testParsingPortAndUsername() {
        $this->assertParsing(
            'http://user@authority.part:80/now/the/path?query#fragment',
            'user', 'authority.part', 80, '/now/the/path', 'query'
        );
    }
    
    function testParsingPercentEncoding() {
        $this->assertParsing(
            'http://en.wikipedia.org/wiki/Clich%C3%A9',
            null, 'en.wikipedia.org', null, '/wiki/Clich%C3%A9', null
        );
    }
    
    function testParsingEmptyQuery() {
        $this->assertParsing(
            'http://www.example.com/?#',
            null, 'www.example.com', null, '/', ''
        );
    }
    
    function testParsingEmptyPath() {
        $this->assertParsing(
            'http://www.example.com',
            null, 'www.example.com', null, '', null
        );
    }
    
    function testParsingOpaqueURI() {
        $this->assertParsing(
            'mailto:bob@example.com',
            null, null, null, 'bob@example.com', null
        );
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
    
    function testParsingIPv4Address() {
        $this->assertParsing(
            'http://192.0.34.166/',
            null, '192.0.34.166', null, '/', null
        );
    }
    
    function testParsingFakeIPv4Address() {
        $this->assertParsing(
            'http://333.123.32.123/',
            null, '333.123.32.123', null, '/', null
        );
    }
    
    function testParsingIPv6Address() {
        $this->assertParsing(
            'http://[2001:db8::7]/c=GB?objectClass?one',
            null, '[2001:db8::7]', null, '/c=GB', 'objectClass?one'
        );
    }
    
    // We will not implement punycode encoding, that's up to the browsers
    // We also will not implement percent to IDNA encoding transformations:
    // if you need to use an international domain in a link, make sure that
    // you've got it in UTF-8 and send it in raw (no encoding).
    function testParsingInternationalizedDomainName() {
        $this->assertParsing(
            "http://t\xC5\xABdali\xC5\x86.lv",
            null, "t\xC5\xABdali\xC5\x86.lv", null, '', null
        );
    }
    
    function testParsingInvalidHostThatLooksLikeIPv6Address() {
        $this->assertParsing(
            'http://[2001:0db8:85z3:08d3:1319:8a2e:0370:7334]',
            null, null, null, '', null
        );
    }
    
    function testParsingInvalidPort() {
        $this->assertParsing(
            'http://example.com:foobar',
            null, 'example.com', null, '', null
        );
    }
    
    function testParsingOverLargePort() {
        $this->assertParsing(
            'http://example.com:65536',
            null, 'example.com', null, '', null
        );
    }
    
    // scheme munging (i.e. removal when unnecessary) not implemented
    
    function testParsingPathAbsolute() { // note this is different from path-rootless
        $this->assertParsing(
            'http:/this/is/path',
            // do not munge scheme off
            null, null, null, '/this/is/path', null
        );
    }
    
    function testParsingPathRootless() {
        // this should not be used but is allowed
        $this->assertParsing(
            'http:this/is/path',
            null, null, null, 'this/is/path', null
        );
        // TODO: scheme should be munged off
    }
    
    function testParsingPathEmpty() {
        $this->assertParsing(
            'http:',
            null, null, null, '', null
        );
        // TODO: scheme should be munged off
    }
    
    function testParsingRelativeURI() {
        $this->assertParsing(
            '/a/b',
            null, null, null, '/a/b', null
        );
    }
    
    function testParsingMalformedTag() {
        $this->assertParsing(
            'http://www.google.com/\'>"',
            null, 'www.google.com', null, '/', null
        );
    }
    
    function testParsingEmpty() {
        $this->assertParsing(
            '',
            null, null, null, '', null
        );
        // TODO: should be returned unharmed
    }
    
    // OUTPUT RELATED TESTS
    
    function assertOutput($expect_uri, $userinfo, $host, $port, $path, $query, $config = null, $context = null) {
        
        // prepare mock machinery
        $this->prepareCommon($config, $context);
        $scheme =& $this->generateSchemeMock();
        $components = array($userinfo, $host, $port, $path, $query, '*', '*');
        $scheme->setReturnValue('validateComponents', $components);
        
        // dummy URI is passed as input, MUST NOT HAVE FRAGMENT
        $def = new HTMLPurifier_AttrDef_URI();
        $result_uri = $def->validate('http://example.com/', $config, $context);
        $this->assertEqual($result_uri, $expect_uri);
        
    }
    
    function testOutputRegular() {
        $this->assertOutput(
            'http://user@authority.part:8080/now/the/path?query',
            'user', 'authority.part', 8080, '/now/the/path', 'query'
        );
    }
    
    // INTEGRATION TESTS
    
    function testIntegration() {
        $this->assertDef('http://www.google.com/');
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
        /*
        $this->config->set('URI', 'HostPolicy', 'DenyAll');
        $this->config->set('URI', 'HostWhitelist', array(null, 'google.com'));
        
        $this->assertDef('http://example.com/fo/google.com', false);
        $this->assertDef('server.txt');
        $this->assertDef('ftp://www.google.com/?t=a');
        $this->assertDef('http://google.com.tricky.spamsite.net', false);
        */
    }
    
}


