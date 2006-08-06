<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/NumberSpan.php';

class HTMLPurifier_AttrDef_NumberSpanTest extends HTMLPurifier_AttrDefHarness
{
    
    function test() {
        
        $this->def = new HTMLPurifier_AttrDef_NumberSpan();
        
        // this one requires a little explanation. A colspan="1" shouldn't
        // exist at all: it's just a dud, since the default value is already
        // supplied
        $this->assertDef('1', false);
        
        $this->assertDef('4');
        $this->assertDef('4.5', '4'); // round down (truncate)
        $this->assertDef('0', false);
        $this->assertDef('-4', false);
        $this->assertDef('asdf', false);
        
    }
    
}

?>