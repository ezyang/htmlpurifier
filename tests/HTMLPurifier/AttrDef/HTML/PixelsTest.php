<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/HTML/Pixels.php';

class HTMLPurifier_AttrDef_HTML_PixelsTest extends HTMLPurifier_AttrDefHarness
{
    
    function setup() {
        $this->def = new HTMLPurifier_AttrDef_HTML_Pixels();
    }
    
    function test() {
        
        $this->assertDef('1');
        $this->assertDef('0');
        
        $this->assertDef('2px', '2'); // rm px suffix
        
        $this->assertDef('dfs', false); // totally invalid value
        
        // conceivably we could repair this value, but we won't for now
        $this->assertDef('9in', false);
        
        // test trim
        $this->assertDef(' 45 ', '45');
        
        // no negatives
        $this->assertDef('-2', '0');
        
        // remove empty
        $this->assertDef('', false);
        
        // round down
        $this->assertDef('4.9', '4');
        
    }
    
}

