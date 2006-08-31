<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/ListStyle.php';

class HTMLPurifier_AttrDef_ListStyleTest extends HTMLPurifier_AttrDefHarness
{
    
    function test() {
        
        $this->def = new HTMLPurifier_AttrDef_ListStyle(HTMLPurifier_Config::createDefault());
        
        $this->assertDef('lower-alpha');
        $this->assertDef('upper-roman inside');
        $this->assertDef('circle outside');
        $this->assertDef('inside');
        $this->assertDef('none');
        
        $this->assertDef('outside inside', 'outside');
        $this->assertDef('circle lower-alpha', 'circle');
        
    }
    
}

?>