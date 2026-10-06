<?php

class HTMLPurifier_AttrDef_CSS_ListStyleTest extends HTMLPurifier_AttrDefHarness
{

    public function test()
    {
        $config = HTMLPurifier_Config::createDefault();
        $this->def = new HTMLPurifier_AttrDef_CSS_ListStyle($config);

        $this->assertDef('lower-alpha');
        $this->assertDef('upper-roman inside');
        $this->assertDef('circle outside');
        $this->assertDef('inside');
        $this->assertDef('none');
        $this->assertDef('url("foo.gif")');
        $this->assertDef('circle url("foo.gif") inside');
        $this->assertDef('url("Foo.GIF")');
        $this->assertDef('SQUARE Inside', 'square inside');

        // invalid values
        $this->assertDef('outside inside', 'outside');
        $this->assertDef('circle url("foo.gif") inside bogus', 'circle url("foo.gif") inside');

        // ordering
        $this->assertDef('url(foo.gif) none', 'none url("foo.gif")');
        $this->assertDef('circle lower-alpha', 'circle');
        // the spec is ambiguous about what happens in these
        // cases, so we're going off the W3C CSS validator
        $this->assertDef('disc none', 'disc');
        $this->assertDef('none disc', 'none');


    }

}

// vim: et sw=4 sts=4
