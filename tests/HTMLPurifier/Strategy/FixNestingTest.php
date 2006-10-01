<?php

require_once 'HTMLPurifier/StrategyHarness.php';
require_once 'HTMLPurifier/Strategy/FixNesting.php';

class HTMLPurifier_Strategy_FixNestingTest extends HTMLPurifier_StrategyHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_Strategy_FixNesting();
    }
    
    function test() {
        
        // legal inline
        $this->assertResult('<b>Bold text</b>');
        
        // legal inline and block
        // as the parent element is considered FLOW
        $this->assertResult('<a href="about:blank">Blank</a><div>Block</div>');
        
        // illegal block in inline
        $this->assertResult(
            '<b><div>Illegal div.</div></b>',
            '<b>Illegal div.</b>'
        );
        
        // same test with different configuration (fragile)
        $this->assertResult(
            '<b><div>Illegal div.</div></b>',
            '<b>&lt;div&gt;Illegal div.&lt;/div&gt;</b>',
            array('Core.EscapeInvalidChildren' => true)
        );
        
        // test of empty set that's required, resulting in removal of node
        $this->assertResult('<ul></ul>', '');
        
        // test illegal text which gets removed
        $this->assertResult(
            '<ul>Illegal text<li>Legal item</li></ul>',
            '<ul><li>Legal item</li></ul>'
        );
        
        // test custom table definition
        $this->assertResult(
            '<table><tr><td>Cell 1</td></tr></table>',
            '<table><tr><td>Cell 1</td></tr></table>'
        );
        $this->assertResult('<table></table>', '');
        
        // breaks without the redundant checking code
        $this->assertResult('<table><tr></tr></table>', '');
        
        // special case, prevents scrolling one back to find parent
        $this->assertResult('<table><tr></tr><tr></tr></table>', '');
        
        // cascading rollbacks
        $this->assertResult(
          '<table><tbody><tr></tr><tr></tr></tbody><tr></tr><tr></tr></table>',
          ''
        );
        
        // rollbacks twice
        $this->assertResult('<table></table><table></table>', '');
        
        // block in inline ins not allowed
        $this->assertResult(
          '<span><ins><div>Not allowed!</div></ins></span>',
          '<span><ins>Not allowed!</ins></span>'
        );
        
        // block in inline ins not allowed
        $this->assertResult(
          '<span><ins><div>Not allowed!</div></ins></span>',
          '<span><ins>&lt;div&gt;Not allowed!&lt;/div&gt;</ins></span>',
          array('Core.EscapeInvalidChildren' => true)
        );
        
        // test exclusions
        $this->assertResult(
          '<a><span><a>Not allowed</a></span></a>',
          '<a><span></span></a>'
        );
        
    }
    
}

?>