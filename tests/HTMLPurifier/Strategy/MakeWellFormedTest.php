<?php

require_once 'HTMLPurifier/StrategyHarness.php';
require_once 'HTMLPurifier/Strategy/MakeWellFormed.php';

class HTMLPurifier_Strategy_MakeWellFormedTest extends HTMLPurifier_StrategyHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_Strategy_MakeWellFormed();
    }
    
    function testEmptyInput() {
        $this->assertResult('');
    }
    
    function testWellFormedInput() {
        $this->assertResult('This is <b>bold text</b>.');
    }
    
    function testUnclosedTagTerminatedByDocumentEnd() {
        $this->assertResult(
            '<b>Unclosed tag, gasp!',
            '<b>Unclosed tag, gasp!</b>'
        );
    }
    
    function testUnclosedTagTerminatedByParentNodeEnd() {
        $this->assertResult(
            '<b><i>Bold and italic?</b>',
            '<b><i>Bold and italic?</i></b>'
        );
    }
    
    function testRemoveStrayClosingTag() {
        $this->assertResult(
            'Unused end tags... recycle!</b>',
            'Unused end tags... recycle!'
        );
    }
    
    function testConvertStartToEmpty() {
        $this->assertResult(
            '<br style="clear:both;">',
            '<br style="clear:both;" />'
        );
    }
    
    function testConvertEmptyToStart() {
        $this->assertResult(
            '<div style="clear:both;" />',
            '<div style="clear:both;"></div>'
        );
    }
    
    function testAutoCloseParagraph() {
        $this->assertResult(
            '<p>Paragraph 1<p>Paragraph 2',
            '<p>Paragraph 1</p><p>Paragraph 2</p>'
        );
    }
    
    function testAutoCloseParagraphInsideDiv() {
        $this->assertResult(
            '<div><p>Paragraphs<p>In<p>A<p>Div</div>',
            '<div><p>Paragraphs</p><p>In</p><p>A</p><p>Div</p></div>'
        );
    }
    
    function testAutoCloseListItem() {
        $this->assertResult(
            '<ol><li>Item 1<li>Item 2</ol>',
            '<ol><li>Item 1</li><li>Item 2</li></ol>'
        );
    }
    
    function testAutoCloseColgroup() {
        $this->assertResult(
            '<table><colgroup><col /><tr></tr></table>',
            '<table><colgroup><col /></colgroup><tr></tr></table>'
        );
    }
    
}

