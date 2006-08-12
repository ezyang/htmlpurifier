<?php

require_once 'HTMLPurifier/AttrDef/CSS.php';

class HTMLPurifier_AttrDef_CSSTest extends HTMLPurifier_AttrDefHarness
{
    
    function test() {
        
        $this->def = new HTMLPurifier_AttrDef_CSS();
        
        $this->assertDef('text-align:right;');
        
    }
    
}

?>