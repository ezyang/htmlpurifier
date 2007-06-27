<?php

require_once 'HTMLPurifier/AttrDef/CSS/Number.php';
require_once 'HTMLPurifier/AttrDefHarness.php';

class HTMLPurifier_AttrDef_CSS_NumberTest extends HTMLPurifier_AttrDefHarness
{
    
    function test() {
        
        $this->def = new HTMLPurifier_AttrDef_CSS_Number();
        
        $this->assertDef('0');
        $this->assertDef('34');
        $this->assertDef('4.5');
        $this->assertDef('-56.9');
        
        $this->assertDef('000', '0');
        $this->assertDef(' 9', '9');
        $this->assertDef('+5.0000', '5');
        $this->assertDef('02.20', '2.2');
        $this->assertDef('2.', '2');
        
        $this->assertDef('.', false);
        $this->assertDef('asdf', false);
        $this->assertDef('0.5.6', false);
        
    }
    
    function testNonNegative() {
        
        $this->def = new HTMLPurifier_AttrDef_CSS_Number(true);
        $this->assertDef('23');
        $this->assertDef('-12', false);
        
    }
    
}

