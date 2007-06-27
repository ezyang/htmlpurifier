<?php

require_once 'HTMLPurifier/ChildDefHarness.php';
require_once 'HTMLPurifier/ChildDef/Optional.php';

class HTMLPurifier_ChildDef_OptionalTest extends HTMLPurifier_ChildDefHarness
{
    
    function test() {
        
        $this->obj = new HTMLPurifier_ChildDef_Optional('b | i');
        
        $this->assertResult('<b>Bold text</b><img />', '<b>Bold text</b>');
        $this->assertResult('Not allowed text', '');
        
    }
    
}

