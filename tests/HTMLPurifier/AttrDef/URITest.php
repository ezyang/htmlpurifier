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
    
    function testGenericURI() {
        
        generate_mock_once('HTMLPurifier_URIScheme');
        generate_mock_once('HTMLPurifier_URISchemeRegistry');
        
        $old_registry = HTMLPurifier_URISchemeRegistry::instance();
        
        // finally, lets get a copy of the actual class
        $this->def = new HTMLPurifier_AttrDef_URI();
        
        // initialize test inputs
        $uri = // input URI
        $components = // what components the URI should be parsed to
        $return_components = // return components
        $expect_uri = array(); // what reassembled URI to expect
        
        //////////////////////////////////////////////////////////////////////
        
        // test a regular instance, return identical URI
        $uri[0] = 'http://www.example.com/webhp?q=foo#result2';
        $components[0] = array(
            null,               // userinfo
            'www.example.com',  // host
            null,               // port
            '/webhp',           // path
            'q=foo'             // query
        );
        
        // test an amended URI (the actual logic is irrelevant)
        // test that user and port get parsed correctly (3.2.1 and 3.2.3)
        $uri[1] = 'http://user@authority.part:80/now/the/path?query#fragment';
        $components[1] = array(
            'user', 'authority.part', 80,
            '/now/the/path', 'query'
        );
        $return_components[1] = array( // removed port (it's standard)
            'user', 'authority.part', null, '/now/the/path', 'query'
        );
        $expect_uri[1] = 'http://user@authority.part/now/the/path?query#fragment';
        
        // percent encoded characters are not resolved during generic URI
        // parsing even though RFC 3986 defines this notation
        // also test what happens when query/fragment are missing
        $uri[2] = 'http://en.wikipedia.org/wiki/Clich%C3%A9';
        $components[2] = array(
            null, 'en.wikipedia.org', null, '/wiki/Clich%C3%A9', null
        );
        
        // test distinction between empty query and undefined query (above)
        $uri[3] = 'http://www.example.com/?#';
        $components[3] = array(null, 'www.example.com', null, '/', '');
        
        // path is always defined, even if empty
        $uri[4] = 'http://www.example.com';
        $components[4] = array(null, 'www.example.com', null, '', null);
        
        // test parsing of an opaque URI
        $uri[5] = 'mailto:bob@example.com';
        $components[5] = array(null, null, null, 'bob@example.com', null);
        
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
        $uri[6] = 'http://www.example.com/%56%fc%GJ%5%FC';
        $components[6] = array(null, 'www.example.com', null, '/V%FC%25GJ%255%FC', null);
        $expect_uri[6] = 'http://www.example.com/V%FC%25GJ%255%FC';
        
        // test IPv4 address (behavior may vary with configuration)
        $uri[7] = 'http://192.0.34.166/';
        $components[7] = array(null, '192.0.34.166', null, '/', null);
        
        // while it may look like an IPv4 address, it's really a reg-name.
        // don't destroy it
        $uri[8] = 'http://333.123.32.123/';
        $components[8] = array(null, '333.123.32.123', null, '/', null);
        
        // test IPv6 address, using amended form of RFC's example
        $uri[9] = 'http://[2001:db8::7]/c=GB?objectClass?one';
        $components[9] = array(null, '[2001:db8::7]', null, '/c=GB',
            'objectClass?one');
        
        // We will not implement punycode encoding, that's up to the browsers
        // We also will not implement percent to IDNA encoding transformations:
        // if you need to use an international domain in a link, make sure that
        // you've got it in UTF-8 and send it in raw (no encoding).
        
        // break the RFC a little and allow international characters
        // WARNING: UTF-8 encoded!
        $uri[10] = 'http://tūdaliņ.lv';
        $components[10] = array(null, 'tūdaliņ.lv', null, '', null);
        
        // test invalid IPv6 address and invalid reg-name
        $uri[11] = 'http://[2001:0db8:85z3:08d3:1319:8a2e:0370:7334]';
        $components[11] = array(null, null, null, '', null);
        $expect_uri[11] = 'http:';
        
        // test invalid port
        $uri[12] = 'http://example.com:foobar';
        $components[12] = array(null, 'example.com', null, '', null);
        $expect_uri[12] = 'http://example.com';
        
        // test overlarge port (max is 65535, although this isn't official)
        $uri[13] = 'http://example.com:65536';
        $components[13] = array(null, 'example.com', null, '', null);
        $expect_uri[13] = 'http://example.com';
        
        // some spec abnf tests
        
        // "authority . path-abempty" omitted, it is a trivial case
        
        // "path-absolute", note this is different from path-rootless
        $uri[14] = 'http:/this/is/path';
        $components[14] = array(null, null, null, '/this/is/path', null);
        $expect_uri[14] = 'http:/this/is/path'; // do not munge scheme off
        
        // scheme munging is not being tested yet, it's an extra feature
        
        // "path-rootless" - this should not be used but is allowed
        $uri[15] = 'http:this/is/path';
        $components[15] = array(null, null, null, 'this/is/path', null);
        //$expect_uri[15] = 'this/is/path'; // munge scheme off
        
        // "path-empty" - a rather interesting case, remove the scheme
        $uri[16] = 'http:';
        $components[16] = array(null, null, null, '', null);
        //$expect_uri[16] = ''; // munge scheme off
        
        // test invalid scheme, components shouldn't be passed
        $uri[17] = 'javascript:alert("moo");';
        $expect_uri[17] = false;
        
        // relative URIs - basic case
        $uri[18] = '/a/b';
        $components[18] = array(null, null, null, '/a/b', null);
        
        // result of malformed tag, gracefully handle error
        $uri[19] = 'http://www.google.com/\'>"';
        $components[19] = array(null, 'www.google.com', null, '/', null);
        $expect_uri[19] = 'http://www.google.com/';
        
        // test empty
        $uri[20] = '';
        $components[20] = array(null, null, null, '', null);
        $expect_uri[20] = '';
        
        foreach ($uri as $i => $value) {
            
            // the read in values
            $this->config  = isset($config[$i])  ? $config[$i]  : HTMLPurifier_Config::createDefault();
            $this->context = isset($context[$i]) ? $context[$i] : new HTMLPurifier_Context();
            
            // setUpAssertDef
            if ( isset($components[$i]) ) {
                $this->components = $components[$i];
            } else {
                $this->components = false;
            }
            if ( isset($return_components[$i]) ) {
                $this->return_components = $return_components[$i];
            } else {
                $this->return_components = $this->components;
            }
            
            // parameters
            if (!isset($expect_uri[$i])) {
                $expect_uri[$i] = $value; // untouched
            }
            
            $this->assertDef($value, $expect_uri[$i], true, "Test $i: %s");
            
        }
        
        // reset to regular implementation
        HTMLPurifier_URISchemeRegistry::instance($old_registry);
        
    }
    
    function setUpAssertDef() {
        // $fake_registry isn't the real mock, because due to PHP 4 weirdness
        // I cannot set a default value to function parameters that are passed
        // by reference. So we use the value instance() returns.
        $fake_registry = new HTMLPurifier_URISchemeRegistryMock($this);
        $registry =& HTMLPurifier_URISchemeRegistry::instance($fake_registry);
        
        // now, let's add a pseudo-scheme to the registry
        $this->scheme = new HTMLPurifier_URISchemeMock($this);
        
        // here are the schemes we will support with overloaded mocks
        $registry->setReturnReference('getScheme', $this->scheme, array('http', $this->config, $this->context));
        $registry->setReturnReference('getScheme', $this->scheme, array('mailto', $this->config, $this->context));
        
        // default return value is false (meaning no scheme defined: reject)
        $registry->setReturnValue('getScheme', false, array('*', $this->config, $this->context));
        
        if ($this->components === false) {
            $this->scheme->expectNever('validateComponents');
        } else {
            $this->components[] = $this->config; // append the configuration
            $this->components[] =& $this->context; // append context
            $this->scheme->setReturnValue(
                'validateComponents', $this->return_components, $this->components);
            $this->scheme->expectOnce('validateComponents', $this->components);
        }
    }
    
    function tearDownAssertDef() {
        $this->scheme->tally();
    }
    
    function testIntegration() {
        
        $this->def = new HTMLPurifier_AttrDef_URI();
        
        $this->assertDef('http://www.google.com/');
        $this->assertDef('javascript:bad_stuff();', false);
        $this->assertDef('ftp://www.example.com/');
        $this->assertDef('news:rec.alt');
        $this->assertDef('nntp://news.example.com/324234');
        $this->assertDef('mailto:bob@example.com');
        
    }
    
    function testDisableExternal() {
        
        $this->def = new HTMLPurifier_AttrDef_URI();
        $this->config->set('URI', 'DisableExternal', true);
        
        $this->assertDef('/foobar.txt');
        $this->assertDef('http://google.com/', false);
        $this->assertDef('http://sub.example.com/alas?foo=asd', false);
        
        $this->config->set('URI', 'Host', 'sub.example.com');
        
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
    
    function testDisableExternalResources() {
        
        $this->config->set('URI', 'DisableExternalResources', true);
        
        $this->def = new HTMLPurifier_AttrDef_URI();
        $this->assertDef('http://sub.example.com/alas?foo=asd');
        $this->assertDef('/img.png');
        
        $this->def = new HTMLPurifier_AttrDef_URI(true);
        $this->assertDef('http://sub.example.com/alas?foo=asd', false);
        $this->assertDef('/img.png');
        
    }
    
    function testMunge() {
        
        $this->config->set('URI', 'Munge', 'http://www.google.com/url?q=%s');
        $this->def = new HTMLPurifier_AttrDef_URI();
        
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

?>