<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/CSS/TextDecoration.php';

class HTMLPurifier_AttrDef_CSS_TextDecorationTest extends HTMLPurifier_AttrDefHarness
{
    
    function testCaseInsensitive() {
        
        $this->def = new HTMLPurifier_AttrDef_CSS_TextDecoration();
        
        $this->assertDef('underline');
        $this->assertDef('overline');
        $this->assertDef('line-through overline underline');
        $this->assertDef('overline line-through');
        $this->assertDef('UNDERLINE', 'underline');
        $this->assertDef('  underline line-through ', 'underline line-through');
        
        $this->assertDef('foobar underline', 'underline');
        $this->assertDef('blink', false);
        
    }
    
}

