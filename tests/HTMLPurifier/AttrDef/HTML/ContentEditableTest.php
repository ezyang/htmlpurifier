<?php

class HTMLPurifier_AttrDef_HTML_ContentEditableTest extends HTMLPurifier_AttrDefHarness
{
    public function setUp()
    {
        parent::setUp();
        $this->def = new HTMLPurifier_AttrDef_HTML_ContentEditable();
    }

    public function test()
    {
        $this->assertDef('', false);
        $this->assertDef('true', false);
        $this->assertDef('caret', false);
        $this->assertDef('false');
    }

    public function testTrustedHtml()
    {
        $this->config->set('HTML.Trusted', true);
        $this->assertDef('');
        $this->assertDef('true');
        $this->assertDef('false');
        $this->assertDef('caret', false);
    }
}
