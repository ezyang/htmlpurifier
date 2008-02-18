<?php

class HTMLPurifier_Strategy_CoreTest extends HTMLPurifier_StrategyHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_Strategy_Core();
    }
    
    function testBlankInput() {
        $this->assertResult('');
    }
    
    function testMakeWellFormed() {
        $this->assertResult(
            '<b>Make well formed.',
            '<b>Make well formed.</b>'
        );
    }
    
    function testFixNesting() {
        $this->assertResult(
            '<b><div>Fix nesting.</div></b>',
            '<b></b><div>Fix nesting.</div>'
        );
    }
    
    function testRemoveForeignElements() {
        $this->assertResult(
            '<asdf>Foreign element removal.</asdf>',
            'Foreign element removal.'
        );
    }
    
    function testFirstThree() {
        $this->assertResult(
            '<foo><b><div>All three.</div></b>',
            '<b></b><div>All three.</div>'
        );
    }
    
}

