<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/Class.php';
require_once 'HTMLPurifier/Config.php';

class HTMLPurifier_AttrDef_ClassTest extends HTMLPurifier_AttrDefHarness
{
    
    function testDefault() {
        
        $this->def = new HTMLPurifier_AttrDef_Class();
        
        $this->assertDef('valid');
        $this->assertDef('a0-_');
        $this->assertDef('-valid');
        $this->assertDef('_valid');
        $this->assertDef('double valid');
        
        $this->assertDef('0invalid', false);
        $this->assertDef('-0', false);
        
        // test conditional replacement
        $this->assertDef('validassoc 0invalid', 'validassoc');
        
        // test whitespace leniency
        $this->assertDef(" double\nvalid\r", 'double valid');
        
    }
    
}

?>