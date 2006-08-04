<?php

require_once 'HTMLPurifier/AttrDef/ID.php';
require_once 'HTMLPurifier/IDAccumulator.php';
require_once 'HTMLPurifier/Config.php';

class HTMLPurifier_AttrDef_IDTest extends UnitTestCase
{
    
    function test() {
        
        $acc = new HTMLPurifier_IDAccumulator();
        $def = new HTMLPurifier_AttrDef_ID();
        
        generate_mock_once('HTMLPurifier_Config');
        
        $config = new HTMLPurifier_ConfigMock();
        
        // valid ID names
        $this->assertTrue($def->validate('alpha', $config, $acc));
        $this->assertTrue($def->validate('al_ha', $config, $acc));
        $this->assertTrue($def->validate('a0-:.', $config, $acc));
        $this->assertTrue($def->validate('a'    , $config, $acc));
        
        // invalid ID names
        $this->assertFalse($def->validate('<asa', $config, $acc));
        $this->assertFalse($def->validate('0123', $config, $acc));
        $this->assertFalse($def->validate('.asa', $config, $acc));
        
        // test duplicate detection
        $this->assertFalse($def->validate('a'   , $config, $acc));
        
        // valid once whitespace stripped, but needs to be amended
        $this->assertEqual('whee', $def->validate(' whee ', $config, $acc));
        
    }
    
}

?>