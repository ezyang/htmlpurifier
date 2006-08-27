<?php

require_once 'HTMLPurifier/AttrDef/Border.php';

class HTMLPurifier_AttrDef_BorderTest extends HTMLPurifier_AttrDef_PixelsTest
{
    
    function test() {
        
        $this->def = new HTMLPurifier_AttrDef_Border();
        
        $this->assertDef('thick solid red', 'thick solid #F00');
        $this->assertDef('thick solid');
        $this->assertDef('solid red', 'solid #F00');
        $this->assertDef('1px solid #000');
        
    }
    
}

?>