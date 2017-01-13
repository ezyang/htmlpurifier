<?php

class HTMLPurifier_HTMLModule_NoopenerTest extends HTMLPurifier_HTMLModuleHarness
{

    public function setUp()
    {
        parent::setUp();
        $this->config->set('HTML.Noopener', true);
        $this->config->set('Attr.AllowedRel', array("noopener", "blah"));
    }

    public function testNoopener()
    {
        $this->assertResult(
            '<a href="http://google.com">x</a><a href="http://google.com" rel="blah">a</a><a href="/local">b</a><a href="mailto:foo@example.com">c</a>',
            '<a href="http://google.com" rel="noopener">x</a><a href="http://google.com" rel="blah noopener">a</a><a href="/local">b</a><a href="mailto:foo@example.com">c</a>'
        );
    }

    public function testNoopenerDupe()
    {
        $this->assertResult(
            '<a href="http://google.com" rel="noopener">x</a><a href="http://google.com" rel="blah noopener">a</a><a href="/local">b</a><a href="mailto:foo@example.com">c</a>'
        );
    }

}

// vim: et sw=4 sts=4
