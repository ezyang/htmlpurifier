<?php

require_once 'HTMLPurifier/StrategyHarness.php';
require_once 'HTMLPurifier/Strategy/MakeWellFormed.php';

class HTMLPurifier_Strategy_MakeWellFormedTest
    extends HTMLPurifier_StrategyHarness
{
    
    function test() {
        
        $strategy = new HTMLPurifier_Strategy_MakeWellFormed();
        
        $inputs = array();
        $expect = array();
        
        $inputs[0] = '';
        $expect[0] = $inputs[0];
        
        $inputs[1] = 'This is <b>bold text</b>.';
        $expect[1] = $inputs[1];
        
        $inputs[2] = '<b>Unclosed tag, gasp!';
        $expect[2] = '<b>Unclosed tag, gasp!</b>';
        
        $inputs[3] = '<b><i>Bold and italic?</b>';
        $expect[3] = '<b><i>Bold and italic?</i></b>';
        
        // CHANGE THIS BEHAVIOR!
        $inputs[4] = 'Unused end tags... recycle!</b>';
        $expect[4] = 'Unused end tags... recycle!&lt;/b&gt;';
        
        $inputs[5] = '<br style="clear:both;">';
        $expect[5] = '<br style="clear:both;" />';
        
        $inputs[6] = '<div style="clear:both;" />';
        $expect[6] = '<div style="clear:both;"></div>';
        
        // test automatic paragraph closing
        
        $inputs[7] = '<p>Paragraph 1<p>Paragraph 2';
        $expect[7] = '<p>Paragraph 1</p><p>Paragraph 2</p>';
        
        $inputs[8] = '<div><p>Paragraphs<p>In<p>A<p>Div</div>';
        $expect[8] = '<div><p>Paragraphs</p><p>In</p><p>A</p><p>Div</p></div>';
        
        // automatic list closing
        
        $inputs[9] = '<ol><li>Item 1<li>Item 2</ol>';
        $expect[9] = '<ol><li>Item 1</li><li>Item 2</li></ol>';
        
        $this->assertStrategyWorks($strategy, $inputs, $expect);
    }
    
}

?>