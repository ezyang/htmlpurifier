<?php

require_once 'HTMLPurifier/URIScheme.php';

require_once 'HTMLPurifier/URIScheme/http.php';
require_once 'HTMLPurifier/URIScheme/ftp.php';
require_once 'HTMLPurifier/URIScheme/https.php';
require_once 'HTMLPurifier/URIScheme/mailto.php';
require_once 'HTMLPurifier/URIScheme/news.php';
require_once 'HTMLPurifier/URIScheme/nntp.php';

// WARNING: All the URI schemes are far to relaxed, we need to tighten
// the checks.

class HTMLPurifier_URISchemeTest extends UnitTestCase
{
    
    function test_http() {
        $scheme = new HTMLPurifier_URIScheme_http();
        $config = HTMLPurifier_Config::createDefault();
        $context = new HTMLPurifier_Context();
        
        $this->assertIdentical(
          $scheme->validateComponents(
                null, 'www.example.com', null, '/', 's=foobar', $config, $context),
          array(null, 'www.example.com', null, '/', 's=foobar')
        );
        
        // absorb default port and userinfo
        $this->assertIdentical(
          $scheme->validateComponents(
                'user', 'www.example.com', 80, '/', 's=foobar', $config, $context),
          array(null, 'www.example.com', null, '/', 's=foobar')
        );
        
        // do not absorb non-default port
        $this->assertIdentical(
          $scheme->validateComponents(
                null, 'www.example.com', 8080, '/', 's=foobar', $config, $context),
          array(null, 'www.example.com', 8080, '/', 's=foobar')
        );
        
        // https is basically the same
        
        $scheme = new HTMLPurifier_URIScheme_https();
        $this->assertIdentical(
          $scheme->validateComponents(
                'user', 'www.example.com', 443, '/', 's=foobar', $config, $context),
          array(null, 'www.example.com', null, '/', 's=foobar')
        );
        
    }
    
    function test_ftp() {
        
        $scheme = new HTMLPurifier_URIScheme_ftp();
        $config = HTMLPurifier_Config::createDefault();
        $context = new HTMLPurifier_Context();
        
        $this->assertIdentical(
          $scheme->validateComponents(
                'user', 'www.example.com', 21, '/', 's=foobar', $config, $context),
          array('user', 'www.example.com', null, '/', null)
        );
        
        // valid typecode
        $this->assertIdentical(
          $scheme->validateComponents(
                null, 'www.example.com', null, '/file.txt;type=a', null, $config, $context),
          array(null, 'www.example.com', null, '/file.txt;type=a', null)
        );
        
        // remove invalid typecode
        $this->assertIdentical(
          $scheme->validateComponents(
                null, 'www.example.com', null, '/file.txt;type=z', null, $config, $context),
          array(null, 'www.example.com', null, '/file.txt', null)
        );
        
        // encode errant semicolons
        $this->assertIdentical(
          $scheme->validateComponents(
                null, 'www.example.com', null, '/too;many;semicolons=1', null, $config, $context),
          array(null, 'www.example.com', null, '/too%3Bmany%3Bsemicolons=1', null)
        );
        
    }
    
    function test_news() {
        
        $scheme = new HTMLPurifier_URIScheme_news();
        $config = HTMLPurifier_Config::createDefault();
        $context = new HTMLPurifier_Context();
        
        $this->assertIdentical(
          $scheme->validateComponents(
                null, null, null, 'gmane.science.linguistics', null, $config, $context),
          array(null, null, null, 'gmane.science.linguistics', null)
        );
        
        $this->assertIdentical(
          $scheme->validateComponents(
                null, null, null, '642@eagle.ATT.COM', null, $config, $context),
          array(null, null, null, '642@eagle.ATT.COM', null)
        );
        
        // test invalid field removal
        $this->assertIdentical(
          $scheme->validateComponents(
                'user', 'www.google.com', 80, 'rec.music', 'path=foo', $config, $context),
          array(null, null, null, 'rec.music', null)
        );
        
    }
    
    function test_nntp() {
        
        $scheme = new HTMLPurifier_URIScheme_nntp();
        $config = HTMLPurifier_Config::createDefault();
        $context = new HTMLPurifier_Context();
        
        $this->assertIdentical(
          $scheme->validateComponents(
                null, 'news.example.com', null, '/alt.misc/12345', null, $config, $context),
          array(null, 'news.example.com', null, '/alt.misc/12345', null)
        );
        
        
        $this->assertIdentical(
          $scheme->validateComponents(
                'user', 'news.example.com', 119, '/alt.misc/12345', 'foo=asdf', $config, $context),
          array(null, 'news.example.com', null,  '/alt.misc/12345', null)
        );
    }
    
    function test_mailto() {
        
        $scheme = new HTMLPurifier_URIScheme_mailto();
        $config = HTMLPurifier_Config::createDefault();
        $context = new HTMLPurifier_Context();
        
        $this->assertIdentical(
          $scheme->validateComponents(
                null, null, null, 'bob@example.com', null, $config, $context),
          array(null, null, null, 'bob@example.com', null)
        );
        
        $this->assertIdentical(
          $scheme->validateComponents(
                'user', 'example.com', 80, 'bob@example.com', 'subject=Foo!', $config, $context),
          array(null, null, null, 'bob@example.com', 'subject=Foo!')
        );
        
    }
    
}

?>