<?php

require_once 'HTMLPurifier/AttrDef/CSS/Multiple.php';
require_once 'HTMLPurifier/AttrDefHarness.php';

// borrowed for the sakes of this test
require_once 'HTMLPurifier/AttrDef/Integer.php';

class HTMLPurifier_AttrDef_CSS_MultipleTest extends HTMLPurifier_AttrDefHarness
{
    
    function test() {
        $this->def = new HTMLPurifier_AttrDef_CSS_Multiple(
            new HTMLPurifier_AttrDef_Integer()
        );
        
        $this->assertDef('1 2 3 4');
        $this->assertDef('6');
        $this->assertDef('4 5');
        $this->assertDef('  2  54 2 3', '2 54 2 3');
        $this->assertDef("6\r3", '6 3');
        
        $this->assertDef('asdf', false);
        $this->assertDef('a s d f', false);
        $this->assertDef('1 2 3 4 5', '1 2 3 4');
        $this->assertDef('1 2 invalid 3', '1 2 3');
        
        
    }
    
}

