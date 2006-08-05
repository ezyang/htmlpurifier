<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/Enum.php';

class HTMLPurifier_AttrDef_EnumTest extends HTMLPurifier_AttrDefHarness
{
    
    function testCaseInsensitive() {
        
        $this->def = new HTMLPurifier_AttrDef_Enum(array('one', 'two'));
        
        $this->assertDef('one');
        $this->assertDef('ONE', 'one');
        
    }
    
    function testCaseSensitive() {
        
        $this->def = new HTMLPurifier_AttrDef_Enum(array('one', 'two'), true);
        
        $this->assertDef('one');
        $this->assertDef('ONE', false);
        
    }
    
    function testFixing() {
        
        $this->def = new HTMLPurifier_AttrDef_Enum(array('one'));
        
        $this->assertDef(' one ', 'one');
        
    }
    
}

?>