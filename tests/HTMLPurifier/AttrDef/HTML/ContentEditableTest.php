<?php

class HTMLPurifier_AttrDef_HTML_ContentEditableTest extends HTMLPurifier_AttrDefHarness
{
    public function test()
    {
        $this->def = new HTMLPurifier_AttrDef_HTML_ContentEditable();
        $this->assertDef('', 'true');
        $this->assertDef('true');
        $this->assertDef('false', 'false');
        $this->assertDef('caret', false);
    }
}
