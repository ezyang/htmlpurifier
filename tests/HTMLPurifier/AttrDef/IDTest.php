<?php

require_once 'HTMLPurifier/AttrDef/ID.php';
require_once 'HTMLPurifier/IDAccumulator.php';

class HTMLPurifier_AttrDef_IDTest extends UnitTestCase
{
    
    function test() {
        
        $acc = new HTMLPurifier_IDAccumulator();
        
        $def = new HTMLPurifier_AttrDef_ID();
        
        // valid ID names
        $this->assertTrue($def->validate('alpha', $acc));
        $this->assertTrue($def->validate('al_ha', $acc));
        $this->assertTrue($def->validate('a0-:.', $acc));
        $this->assertTrue($def->validate('a'    , $acc));
        
        // invalid ID names
        $this->assertFalse($def->validate('<asa', $acc));
        $this->assertFalse($def->validate('0123', $acc));
        $this->assertFalse($def->validate('.asa', $acc));
        
        // test duplicate detection
        $this->assertFalse($def->validate('a'   , $acc));
        
        // valid once whitespace stripped, but needs to be amended
        $this->assertEqual('whee', $def->validate(' whee ', $acc));
        
    }
    
}

?>