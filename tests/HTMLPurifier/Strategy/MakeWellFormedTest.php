<?php

require_once 'HTMLPurifier/StrategyHarness.php';
require_once 'HTMLPurifier/Strategy/MakeWellFormed.php';

class HTMLPurifier_Strategy_MakeWellFormedTest extends HTMLPurifier_StrategyHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_Strategy_MakeWellFormed();
        $this->config = array();
    }
    
    function testNormalIntegration() {
        $this->assertResult('');
        $this->assertResult('This is <b>bold text</b>.');
    }
    
    function testUnclosedTagIntegration() {
        $this->assertResult(
            '<b>Unclosed tag, gasp!',
            '<b>Unclosed tag, gasp!</b>'
        );
        
        $this->assertResult(
            '<b><i>Bold and italic?</b>',
            '<b><i>Bold and italic?</i></b>'
        );
        
        $this->assertResult(
            'Unused end tags... recycle!</b>',
            'Unused end tags... recycle!'
        );
    }
    
    function testEmptyTagDetectionIntegration() {
        $this->assertResult(
            '<br style="clear:both;">',
            '<br style="clear:both;" />'
        );
        
        $this->assertResult(
            '<div style="clear:both;" />',
            '<div style="clear:both;"></div>'
        );
    }
    
    function testAutoClose() {
        // paragraph
        
        $this->assertResult(
            '<p>Paragraph 1<p>Paragraph 2',
            '<p>Paragraph 1</p><p>Paragraph 2</p>'
        );
        
        $this->assertResult(
            '<div><p>Paragraphs<p>In<p>A<p>Div</div>',
            '<div><p>Paragraphs</p><p>In</p><p>A</p><p>Div</p></div>'
        );
        
        // list
        
        $this->assertResult(
            '<ol><li>Item 1<li>Item 2</ol>',
            '<ol><li>Item 1</li><li>Item 2</li></ol>'
        );
        
        // colgroup
        
        $this->assertResult(
            '<table><colgroup><col /><tr></tr></table>',
            '<table><colgroup><col /></colgroup><tr></tr></table>'
        );
        
    }
    
    function testMultipleInjectors() {
        
        $this->config = array('AutoFormat.AutoParagraph' => true, 'AutoFormat.Linkify' => true);
        
        $this->assertResult(
            'Foobar',
            '<p>Foobar</p>'
        );
        
        $this->assertResult(
            'http://example.com',
            '<p><a href="http://example.com">http://example.com</a></p>'
        );
        
        $this->assertResult(
            '<b>http://example.com</b>',
            '<p><b><a href="http://example.com">http://example.com</a></b></p>'
        );
        
        $this->assertResult(
            '<b>http://example.com',
            '<p><b><a href="http://example.com">http://example.com</a></b></p>'
        );
        
        $this->assertResult(
'http://example.com

http://dev.example.com',
            '<p><a href="http://example.com">http://example.com</a></p><p><a href="http://dev.example.com">http://dev.example.com</a></p>'
        );
        
        $this->assertResult(
            'http://example.com <div>http://example.com</div>',
            '<p><a href="http://example.com">http://example.com</a> </p><div><a href="http://example.com">http://example.com</a></div>'
        );
        
        $this->assertResult(
            'This URL http://example.com is what you need',
            '<p>This URL <a href="http://example.com">http://example.com</a> is what you need</p>'
        );
        
    }
    
}

