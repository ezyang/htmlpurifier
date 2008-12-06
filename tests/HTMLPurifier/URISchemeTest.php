<?php

// WARNING: All the URI schemes are far to relaxed, we need to tighten
// the checks.

class HTMLPurifier_URISchemeTest extends HTMLPurifier_URIHarness
{

    protected function assertValidation($uri, $expect_uri = true) {
        $this->prepareURI($uri, $expect_uri);
        // convenience hack: the scheme should be explicitly specified
        $scheme = $uri->getSchemeObj($this->config, $this->context);
        $result = $scheme->validate($uri, $this->config, $this->context);
        $this->assertEitherFailOrIdentical($result, $uri, $expect_uri);
    }

    function test_http_regular() {
        $this->assertValidation(
            'http://example.com/?s=q#fragment'
        );
    }

    function test_http_removeDefaultPort() {
        $this->assertValidation(
            'http://example.com:80',
            'http://example.com'
        );
    }

    function test_http_removeUserInfo() {
        $this->assertValidation(
            'http://bob@example.com',
            'http://example.com'
        );
    }

    function test_http_preserveNonDefaultPort() {
        $this->assertValidation(
            'http://example.com:8080'
        );
    }

    function test_https_regular() {
        $this->assertValidation(
            'https://user@example.com:443/?s=q#frag',
            'https://example.com/?s=q#frag'
        );
    }

    function test_ftp_regular() {
        $this->assertValidation(
            'ftp://user@example.com/path'
        );
    }

    function test_ftp_removeDefaultPort() {
        $this->assertValidation(
            'ftp://example.com:21',
            'ftp://example.com'
        );
    }

    function test_ftp_removeQueryString() {
        $this->assertValidation(
            'ftp://example.com?s=q',
            'ftp://example.com'
        );
    }

    function test_ftp_preserveValidTypecode() {
        $this->assertValidation(
            'ftp://example.com/file.txt;type=a'
        );
    }

    function test_ftp_removeInvalidTypecode() {
        $this->assertValidation(
            'ftp://example.com/file.txt;type=z',
            'ftp://example.com/file.txt'
        );
    }

    function test_ftp_encodeExtraSemicolons() {
        $this->assertValidation(
            'ftp://example.com/too;many;semicolons=1',
            'ftp://example.com/too%3Bmany%3Bsemicolons=1'
        );
    }

    function test_news_regular() {
        $this->assertValidation(
            'news:gmane.science.linguistics'
        );
    }

    function test_news_explicit() {
        $this->assertValidation(
            'news:642@eagle.ATT.COM'
        );
    }

    function test_news_removeNonPathComponents() {
        $this->assertValidation(
            'news://user@example.com:80/rec.music?path=foo#frag',
            'news:/rec.music#frag'
        );
    }

    function test_nntp_regular() {
        $this->assertValidation(
            'nntp://news.example.com/alt.misc/42#frag'
        );
    }

    function test_nntp_removalOfRedundantOrUselessComponents() {
        $this->assertValidation(
            'nntp://user@news.example.com:119/alt.misc/42?s=q#frag',
            'nntp://news.example.com/alt.misc/42#frag'
        );
    }

    function test_mailto_regular() {
        $this->assertValidation(
            'mailto:bob@example.com'
        );
    }

    function test_mailto_removalOfRedundantOrUselessComponents() {
        $this->assertValidation(
            'mailto://user@example.com:80/bob@example.com?subject=Foo#frag',
            'mailto:/bob@example.com?subject=Foo#frag'
        );
    }

}

// vim: et sw=4 sts=4
