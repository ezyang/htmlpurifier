<?php

require_once 'HTMLPurifier/StrategyAbstractTest.php';
require_once 'HTMLPurifier/Strategy/RemoveForeignElements.php';

class HTMLPurifier_Strategy_RemoveForeignElementsTest
    extends HTMLPurifier_StrategyAbstractTest
{
    
    function test() {
        
        $strategy = new HTMLPurifier_Strategy_RemoveForeignElements();
        
        $inputs = array();
        $expect = array();
        
        $inputs[0] = '';
        $expect[0] = $inputs[0];
        
        $inputs[1] = 'This is <b>bold text</b>.';
        $expect[1] = $inputs[1];
        
        // [INVALID]
        $inputs[2] = '<asdf>Bling</asdf><d href="bang">Bong</d><foobar />';
        $expect[2] = htmlspecialchars($inputs[2]);
        
        $this->assertStrategyWorks($strategy, $inputs, $expect);
    }
    
}

?>