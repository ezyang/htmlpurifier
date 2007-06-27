<?php

require_once 'HTMLPurifier/ChildDefHarness.php';
require_once 'HTMLPurifier/ChildDef/Chameleon.php';

class HTMLPurifier_ChildDef_ChameleonTest extends HTMLPurifier_ChildDefHarness
{
    
    function test() {
        
        $this->obj = new HTMLPurifier_ChildDef_Chameleon(
            'b | i',      // allowed only when in inline context
            'b | i | div' // allowed only when in block context
        );
        
        $this->assertResult(
            '<b>Allowed.</b>', true,
            array(), array('IsInline' => true)
        );
        
        $this->assertResult(
            '<div>Not allowed.</div>', '',
            array(), array('IsInline' => true)
        );
        
        $this->assertResult(
            '<div>Allowed.</div>', true,
            array(), array('IsInline' => false)
        );
        
    }
    
}

