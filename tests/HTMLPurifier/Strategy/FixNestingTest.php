<?php

require_once 'HTMLPurifier/StrategyHarness.php';
require_once 'HTMLPurifier/Strategy/FixNesting.php';

class HTMLPurifier_Strategy_FixNestingTest
    extends HTMLPurifier_StrategyHarness
{
    
    function test() {
        
        $strategy = new HTMLPurifier_Strategy_FixNesting();
        
        $inputs = array();
        $expect = array();
        $config = array();
        
        $config_escape = HTMLPurifier_Config::createDefault();
        $config_escape->set('Core', 'EscapeInvalidChildren', true);
        
        // next id = 4
        
        // legal inline nesting
        $inputs[0] = '<b>Bold text</b>';
        $expect[0] = $inputs[0];
        
        // legal inline and block
        // as the parent element is considered FLOW
        $inputs[1] = '<a href="about:blank">Blank</a><div>Block</div>';
        $expect[1] = $inputs[1];
        
        // illegal block in inline
        $inputs[2] = '<b><div>Illegal div.</div></b>';
        $expect[2] = '<b>Illegal div.</b>';
        
        // same test with different configuration (fragile)
        $inputs[13]  = '<b><div>Illegal div.</div></b>';
        $expect[13] = '<b>&lt;div&gt;Illegal div.&lt;/div&gt;</b>';
        $config[13] = $config_escape;
        
        // test of empty set that's required, resulting in removal of node
        $inputs[3] = '<ul></ul>';
        $expect[3] = '';
        
        // test illegal text which gets removed
        $inputs[4] = '<ul>Illegal text<li>Legal item</li></ul>';
        $expect[4] = '<ul><li>Legal item</li></ul>';
        
        // test custom table definition
        
        $inputs[5] = '<table><tr><td>Cell 1</td></tr></table>';
        $expect[5] = '<table><tr><td>Cell 1</td></tr></table>';
        
        $inputs[6] = '<table></table>';
        $expect[6] = '';
        
        // breaks without the redundant checking code
        $inputs[7] = '<table><tr></tr></table>';
        $expect[7] = '';
        
        // special case, prevents scrolling one back to find parent
        $inputs[8] = '<table><tr></tr><tr></tr></table>';
        $expect[8] = '';
        
        // cascading rollbacks
        $inputs[9] = '<table><tbody><tr></tr><tr></tr></tbody><tr></tr><tr></tr></table>';
        $expect[9] = '';
        
        // rollbacks twice
        $inputs[10] = '<table></table><table></table>';
        $expect[10] = '';
        
        // block in inline ins not allowed
        $inputs[11] = '<span><ins><div>Not allowed!</div></ins></span>';
        $expect[11] = '<span><ins>Not allowed!</ins></span>';
        
        // block in inline ins not allowed
        $inputs[14] = '<span><ins><div>Not allowed!</div></ins></span>';
        $expect[14] = '<span><ins>&lt;div&gt;Not allowed!&lt;/div&gt;</ins></span>';
        $config[14] = $config_escape;
        
        // test exclusions
        $inputs[12] = '<a><span><a>Not allowed</a></span></a>';
        $expect[12] = '<a><span></span></a>';
        
        // next test is *15*
        
        $this->assertStrategyWorks($strategy, $inputs, $expect, $config);
    }
    
}

?>