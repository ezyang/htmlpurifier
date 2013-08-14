<?php

class HTMLPurifier_AttrDef_HTML_FrameTargetTest extends HTMLPurifier_AttrDefHarness
{

    public function setup()
    {
        parent::setup();
        $this->def = new HTMLPurifier_AttrDef_HTML_FrameTarget();
    }

    public function testNoneAllowed()
    {
        $this->assertDef('', false);
        $this->assertDef('foo', false);
        $this->assertDef('_blank', false);
        $this->assertDef('baz', false);
    }

    public function test()
    {
        $this->config->set('Attr.AllowedFrameTargets', 'foo,_blank');
        $this->assertDef('', false);
        $this->assertDef('foo');
        $this->assertDef('_blank');
        $this->assertDef('baz', false);
    }

}

// vim: et sw=4 sts=4
