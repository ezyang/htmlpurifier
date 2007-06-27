<?php

require_once 'HTMLPurifier/AttrDef/CSS/Color.php';
require_once 'HTMLPurifier/AttrDefHarness.php';

class HTMLPurifier_AttrDef_CSS_ColorTest extends HTMLPurifier_AttrDefHarness
{
    
    function test() {
        
        $this->def = new HTMLPurifier_AttrDef_CSS_Color();
        
        $this->assertDef('#F00');
        $this->assertDef('#808080');
        $this->assertDef('rgb(255, 0, 0)', 'rgb(255,0,0)'); // rm spaces
        $this->assertDef('rgb(100%,0%,0%)');
        $this->assertDef('rgb(50.5%,23.2%,43.9%)'); // decimals okay
        
        $this->assertDef('#G00', false);
        $this->assertDef('cmyk(40, 23, 43, 23)', false);
        $this->assertDef('rgb(0%, 23, 68%)', false);
        
        // clip numbers outside sRGB gamut
        $this->assertDef('rgb(200%, -10%, 0%)', 'rgb(100%,0%,0%)');
        $this->assertDef('rgb(256,-23,34)', 'rgb(255,0,34)');
        
        // color keywords, of course
        $this->assertDef('red', '#FF0000');
        
        // maybe hex transformations would be another nice feature
        // at the very least transform rgb percent to rgb integer
        
    }
    
}

