<?php

require_once 'HTMLPurifier/StrategyAbstractTest.php';
require_once 'HTMLPurifier/Strategy/FixNesting.php';

class HTMLPurifier_Strategy_FixNestingTest
    extends HTMLPurifier_StrategyAbstractTest
{
    
    function test() {
        
        $strategy = new HTMLPurifier_Strategy_FixNesting();
        
        $inputs = array();
        $expect = array();
        
        // next id = 4
        
        // legal inline nesting
        $inputs[0] = '<b>Bold text</b>';
        $expect[0] = $inputs[0];
        
        // legal inline and block
        // as the parent element is considered FLOW
        $inputs[1] = '<a href="about:blank">Blank</a><div>Block</div>';
        $expect[1] = $inputs[1];
        
        // illegal block in inline, element -> text
        $inputs[2] = '<b><div>Illegal div.</div></b>';
        $expect[2] = '<b>&lt;div&gt;Illegal div.&lt;/div&gt;</b>';
        
        // test of empty set that's required, resulting in removal of node
        $inputs[3] = '<ul></ul>';
        $expect[3] = '';
        
        // test illegal text which gets removed
        $inputs[4] = '<ul>Illegal text<li>Legal item</li></ul>';
        $expect[4] = '<ul><li>Legal item</li></ul>';
        
        // test custom table definition
        $inputs[5] = '<table><tr><td>Cell 1</td></tr></table>';
        $expect[5] = '<table><tr><td>Cell 1</td></tr></table>';
        
        $this->assertStrategyWorks($strategy, $inputs, $expect);
    }
    
}

?>