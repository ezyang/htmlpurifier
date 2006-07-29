<?php

require_once 'HTMLPurifier/StrategyAbstractTest.php';
require_once 'HTMLPurifier/Strategy/Core.php';

class HTMLPurifier_Strategy_CoreTest
    extends HTMLPurifier_StrategyAbstractTest
{
    
    function test() {
        $strategy = new HTMLPurifier_Strategy_Core();
        
        $inputs = array();
        $expect = array();
        
        $inputs[0] = '';
        $expect[0] = '';
        
        $inputs[1] = '<b>Make well formed.';
        $expect[1] = '<b>Make well formed.</b>';
        
        // behavior may change
        $inputs[2] = '<b><div>Fix nesting.</div></b>';
        $expect[2] = '<b>&lt;div&gt;Fix nesting.&lt;/div&gt;</b>';
        
        // behavior may change
        $inputs[3] = '<asdf>Foreign element removal.</asdf>';
        $expect[3] = '&lt;asdf&gt;Foreign element removal.&lt;/asdf&gt;';
        
        // behavior may change
        $inputs[4] = '<foo><b><div>All three.</div></b>';
        $expect[4] = '&lt;foo&gt;<b>&lt;div&gt;All three.&lt;/div&gt;</b>';
        
        $this->assertStrategyWorks($strategy, $inputs, $expect);
    }
    
}

?>