<?php

require_once 'HTMLPurifier/AttrDef/CSS/AlphaValue.php';
require_once 'HTMLPurifier/AttrDefHarness.php';

class HTMLPurifier_AttrDef_CSS_AlphaValueTest extends HTMLPurifier_AttrDefHarness
{
    
    function test() {
        
        $this->def = new HTMLPurifier_AttrDef_CSS_AlphaValue();
        
        $this->assertDef('0');
        $this->assertDef('1');
        $this->assertDef('.2');
        
        // clamping to [0.0, 1,0]
        $this->assertDef('1.2', '1');
        $this->assertDef('-3', '0');
        
        $this->assertDef('0.0', '0');
        $this->assertDef('1.0', '1');
        $this->assertDef('000', '0');
        
        $this->assertDef('asdf', false);
        
    }
    
}

