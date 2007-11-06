<?php

require_once 'HTMLPurifier/ChildDefHarness.php';
require_once 'HTMLPurifier/ChildDef/Optional.php';

class HTMLPurifier_ChildDef_OptionalTest extends HTMLPurifier_ChildDefHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_ChildDef_Optional('b | i');
    }
    
    function testBasicUsage() {
        $this->assertResult('<b>Bold text</b><img />', '<b>Bold text</b>');
    }
    
    function testRemoveForbiddenText() {
        $this->assertResult('Not allowed text', '');
    }
    
    function testEmpty() {
        $this->assertResult('');
    }
    
}

