<?php

require_once 'HTMLPurifier/StrategyHarness.php';
require_once 'HTMLPurifier/Strategy/FixNesting.php';

class HTMLPurifier_Strategy_FixNestingTest extends HTMLPurifier_StrategyHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_Strategy_FixNesting();
    }
    
    function testBlockAndInlineIntegration() {
        
        // legal inline
        $this->assertResult('<b>Bold text</b>');
        
        // legal inline and block (default parent element is FLOW)
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
        
    }
    
    function testNodeRemovalIntegration() {
        
        // test of empty set that's required, resulting in removal of node
        $this->assertResult('<ul></ul>', '');
        
        // test illegal text which gets removed
        $this->assertResult(
            '<ul>Illegal text<li>Legal item</li></ul>',
            '<ul><li>Legal item</li></ul>'
        );
        
    }
    
    function testTableIntegration() {
        // test custom table definition
        $this->assertResult(
            '<table><tr><td>Cell 1</td></tr></table>'
        );
        $this->assertResult('<table></table>', '');
    }
    
    function testChameleonIntegration() {
        
        // block in inline ins not allowed
        $this->assertResult(
          '<span><ins><div>Not allowed!</div></ins></span>',
          '<span><ins>Not allowed!</ins></span>'
        );
        
        // test block element that has inline content
        $this->assertResult(
          '<h1><ins><div>Not allowed!</div></ins></h1>',
          '<h1><ins>Not allowed!</ins></h1>'
        );
        
        // stacked ins/del
        $this->assertResult(
          '<h1><ins><del><div>Not allowed!</div></del></ins></h1>',
          '<h1><ins><del>Not allowed!</del></ins></h1>'
        );
        $this->assertResult(
          '<div><ins><del><div>Allowed!</div></del></ins></div>'
        );
        
        $this->assertResult( // alt config
          '<span><ins><div>Not allowed!</div></ins></span>',
          '<span><ins>&lt;div&gt;Not allowed!&lt;/div&gt;</ins></span>',
          array('Core.EscapeInvalidChildren' => true)
        );
        
    }
    
    function testExclusionsIntegration() {
        // test exclusions
        $this->assertResult(
          '<a><span><a>Not allowed</a></span></a>',
          '<a><span></span></a>'
        );
    }
   
    function testCustomParentIntegration() {
        // test inline parent
        $this->assertResult(
            '<b>Bold</b>', true, array('HTML.Parent' => 'span')
        );
        $this->assertResult(
            '<div>Reject</div>', 'Reject', array('HTML.Parent' => 'span')
        );
   }
   
   function testError() {
        // test fallback to div
        $this->expectError('Cannot use unrecognized element as parent.');
        $this->assertResult(
            '<div>Accept</div>', true, array('HTML.Parent' => 'obviously-impossible')
        );
        $this->swallowErrors();
        
    }
    
    function testDoubleCheckIntegration() {
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
    }
    
}

