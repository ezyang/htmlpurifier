<?php

require_once 'HTMLPurifier/StrategyHarness.php';
require_once 'HTMLPurifier/Strategy/Core.php';

class HTMLPurifier_Strategy_CoreTest extends HTMLPurifier_StrategyHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_Strategy_Core();
    }
    
    function test() {
        
        $this->assertResult('');
        $this->assertResult(
            '<b>Make well formed.',
            '<b>Make well formed.</b>'
        );
        $this->assertResult(
            '<b><div>Fix nesting.</div></b>',
            '<b></b><div>Fix nesting.</div>'
        );
        $this->assertResult(
            '<asdf>Foreign element removal.</asdf>',
            'Foreign element removal.'
        );
        $this->assertResult(
            '<foo><b><div>All three.</div></b>',
            '<b></b><div>All three.</div>'
        );
        
    }
    
}

