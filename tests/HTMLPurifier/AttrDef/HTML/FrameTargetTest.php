<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/HTML/FrameTarget.php';

class HTMLPurifier_AttrDef_HTML_FrameTargetTest extends HTMLPurifier_AttrDefHarness
{
    
    function setup() {
        parent::setup();
        $this->def = new HTMLPurifier_AttrDef_HTML_FrameTarget();
    }
    
    function testNoneAllowed() {
        $this->assertDef('', false);
        $this->assertDef('foo', false);
        $this->assertDef('_blank', false);
        $this->assertDef('baz', false);
    }
    
    function test() {
        $this->config->set('Attr', 'AllowedFrameTargets', 'foo,_blank');
        $this->assertDef('', false);
        $this->assertDef('foo');
        $this->assertDef('_blank');
        $this->assertDef('baz', false);
    }
    
}

