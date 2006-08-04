<?php

require_once 'HTMLPurifier/AttrDef/Enum.php';

class HTMLPurifier_AttrDef_EnumTest extends UnitTestCase
{
    
    function testCaseInsensitive() {
        
        $def = new HTMLPurifier_AttrDef_Enum(array('one', 'two'));
        
        $this->assertTrue($def->validate('one'));
        $this->assertTrue($def->validate('ONE'));
        
    }
    
    function testCaseSensitive() {
        
        $def = new HTMLPurifier_AttrDef_Enum(array('one', 'two'), true);
        
        $this->assertTrue($def->validate('one'));
        $this->assertFalse($def->validate('ONE'));
        
    }
    
    function testFixing() {
        
        $def = new HTMLPurifier_AttrDef_Enum(array('one'));
        
        $this->assertEqual('one', $def->validate(' one '));
        
    }
    
}

?>