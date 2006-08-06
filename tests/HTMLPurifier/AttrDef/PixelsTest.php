<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/Pixels.php';

class HTMLPurifier_AttrDef_PixelsTest extends HTMLPurifier_AttrDefHarness
{
    
    function test() {
        
        $this->def = new HTMLPurifier_AttrDef_Pixels();
        
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

?>