<?php

class HTMLPurifier_HTMLModule_NofollowTest extends HTMLPurifier_HTMLModuleHarness
{

    function setUp() {
        parent::setUp();
        $this->config->set('HTML.Nofollow', true);
    }

    function testNofollow() {
        $this->assertResult(
            '<a href="http://google.com">a</a><a href="/local">b</a><a href="mailto:foo@example.com">c</a>',
            '<a href="http://google.com" rel="nofollow">a</a><a href="/local">b</a><a href="mailto:foo@example.com">c</a>'
        );
    }

    function testNofollowDupe() {
        $this->assertResult(
            '<a href="http://google.com" rel="nofollow">a</a><a href="/local">b</a><a href="mailto:foo@example.com">c</a>'
        );
    }

}

// vim: et sw=4 sts=4
