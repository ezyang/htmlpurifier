<?php

require_once 'HTMLPurifier/AttrDef/Class.php';
require_once 'HTMLPurifier/Config.php';

class HTMLPurifier_AttrDef_ClassTest extends UnitTestCase
{
    
    function testDefault() {
        
        $def = new HTMLPurifier_AttrDef_Class();
        
        $this->assertTrue($def->validate('valid'));
        $this->assertTrue($def->validate('a0-_'));
        $this->assertTrue($def->validate('-valid'));
        $this->assertTrue($def->validate('_valid'));
        $this->assertTrue($def->validate('double valid'));
        
        $this->assertFalse($def->validate('0invalid'));
        $this->assertFalse($def->validate('-0'));
        
        // test conditional replacement
        $this->assertEqual('validassoc', $def->validate('validassoc 0invalid'));
        
        // test whitespace leniency
        $this->assertTrue('double valid', $def->validate(" double\nvalid\r"));
        
    }
    
}

?>