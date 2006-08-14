<?php

require_once 'HTMLPurifier/StrategyHarness.php';
require_once 'HTMLPurifier/Strategy/Core.php';

class HTMLPurifier_Strategy_CoreTest
    extends HTMLPurifier_StrategyHarness
{
    
    function test() {
        $strategy = new HTMLPurifier_Strategy_Core();
        
        $inputs = array();
        $expect = array();
        $config = array();
        
        $config_escape = HTMLPurifier_Config::createDefault();
        $config_escape->set('Core', 'EscapeInvalidChildren', true);
        
        $inputs[0] = '';
        $expect[0] = '';
        
        $inputs[1] = '<b>Make well formed.';
        $expect[1] = '<b>Make well formed.</b>';
        
        $inputs[2] = '<b><div>Fix nesting.</div></b>';
        $expect[2] = '<b></b>';
        
        // behavior may change
        $inputs[3] = '<asdf>Foreign element removal.</asdf>';
        $expect[3] = '&lt;asdf&gt;Foreign element removal.&lt;/asdf&gt;';
        
        $inputs[4] = '<foo><b><div>All three.</div></b>';
        $expect[4] = '&lt;foo&gt;<b></b>';
        
        $this->assertStrategyWorks($strategy, $inputs, $expect, $config);
    }
    
}

?>