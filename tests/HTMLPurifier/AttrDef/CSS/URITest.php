<?php

class HTMLPurifier_AttrDef_CSS_URITest extends HTMLPurifier_AttrDefHarness
{

    function test() {

        $this->def = new HTMLPurifier_AttrDef_CSS_URI();

        $this->assertDef('', false);

        // we could be nice but we won't be
        $this->assertDef('http://www.example.com/', false);

        // no quotes are used, since that's the most widely supported
        // syntax
        $this->assertDef('url(', false);
        $this->assertDef('url()', true);
        $result = "url(http://www.example.com/)";
        $this->assertDef('url(http://www.example.com/)', $result);
        $this->assertDef('url("http://www.example.com/")', $result);
        $this->assertDef("url('http://www.example.com/')", $result);
        $this->assertDef(
            '  url(  "http://www.example.com/" )   ', $result);

        // escaping
        $this->assertDef("url(http://www.example.com/foo,bar\))",
            "url(http://www.example.com/foo\,bar\))");
    }

}

// vim: et sw=4 sts=4
