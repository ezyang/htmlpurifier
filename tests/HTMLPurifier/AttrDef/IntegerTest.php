<?php

require_once 'HTMLPurifier/AttrDef/Integer.php';

class HTMLPurifier_AttrDef_IntegerTest extends HTMLPurifier_AttrDefHarness
{
    
    function test() {
        
        $this->def = new HTMLPurifier_AttrDef_Integer();
        
        $this->assertDef('0');
        $this->assertDef('1');
        $this->assertDef('-1');
        $this->assertDef('-10');
        $this->assertDef('14');
        $this->assertDef('+24', '24');
        $this->assertDef(' 14 ', '14');
        
        $this->assertDef('-1.4', false);
        $this->assertDef('3.4', false);
        $this->assertDef('asdf', false);
        
    }
    
    function testNonNegative() {
        
        $this->def = new HTMLPurifier_AttrDef_Integer(true);
        
        $this->assertDef('0');
        $this->assertDef('1');
        $this->assertDef('-1', false);
        
    }
    
}

?>