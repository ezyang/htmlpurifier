<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/Text.php';

class HTMLPurifier_AttrDef_TextTest extends HTMLPurifier_AttrDefHarness
{
    
    function test() {
        
        $this->def = new HTMLPurifier_AttrDef_Text();
        
        $this->assertDef('This is spiffy text!');
        $this->assertDef(" Casual\tCDATA parse\ncheck. ", 'Casual CDATA parsecheck.');
        
    }
    
}

