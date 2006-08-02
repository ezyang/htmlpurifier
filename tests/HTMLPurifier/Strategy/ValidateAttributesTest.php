<?php

require_once('HTMLPurifier/StrategyAbstractTest.php');
require_once('HTMLPurifier/Strategy/ValidateAttributes.php');

class HTMLPurifier_Strategy_ValidateAttributesTest extends
      HTMLPurifier_StrategyAbstractTest
{
    
    function test() {
        
        $strategy = new HTMLPurifier_Strategy_ValidateAttributes();
        
        $inputs = array();
        $expect = array();
        
        $inputs[0] = '';
        $expect[0] = '';
        
        $inputs[1] = '<div id="valid">Preserve the ID.</div>';
        $expect[1] = $inputs[1];
        
        $inputs[2] = '<div id="0invalid">Kill the ID.</div>';
        $expect[2] = '<div>Kill the ID.</div>';
        
        // test accumulator
        $inputs[3] = '<div id="valid">Valid</div><div id="valid">Invalid</div>';
        $expect[3] = '<div id="valid">Valid</div><div>Invalid</div>';
        
        $inputs[4] = '<span dir="up-to-down">Bad dir.</span>';
        $expect[4] = '<span>Bad dir.</span>';
        
        // test case sensitivity
        $inputs[5] = '<div ID="valid">Convert ID to lowercase.</div>';
        $expect[5] = '<div id="valid">Convert ID to lowercase.</div>';
        
        $this->assertStrategyWorks($strategy, $inputs, $expect);
        
    }
    
}

?>