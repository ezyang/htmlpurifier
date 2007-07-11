<?php

require_once 'HTMLPurifier/StrategyHarness.php';
require_once 'HTMLPurifier/Strategy/RemoveForeignElements.php';

class HTMLPurifier_Strategy_RemoveForeignElementsTest
  extends HTMLPurifier_StrategyHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_Strategy_RemoveForeignElements();
    }
    
    function test() {
        
        $this->config = array('HTML.Doctype' => 'XHTML 1.0 Strict');
        
        $this->assertResult('');
        
        $this->assertResult('This is <b>bold text</b>.');
        
        $this->assertResult(
            '<asdf>Bling</asdf><d href="bang">Bong</d><foobar />',
            'BlingBong'
        );
        
        $this->assertResult(
            '<script>alert();</script>',
            ''
        );
        
        $this->assertResult(
            '<style>.foo {blink;}</style>',
            ''
        );
        
        $this->assertResult(
            '<script>alert();</script>',
            'alert();',
            array('Core.RemoveScriptContents' => false)
        );
        
        $this->assertResult(
            '<script>alert();</script>',
            'alert();',
            array('Core.HiddenElements' => array())
        );
        
        $this->assertResult(
            '<menu><li>Item 1</li></menu>',
            '<ul><li>Item 1</li></ul>'
        );
        
        // test center transform
        $this->assertResult(
            '<center>Look I am Centered!</center>',
            '<div style="text-align:center;">Look I am Centered!</div>'
        );
        
        // test font transform
        $this->assertResult(
            '<font color="red" face="Arial" size="6">Big Warning!</font>',
            '<span style="color:red;font-family:Arial;font-size:xx-large;">Big'.
              ' Warning!</span>'
        );
        
        // test removal of invalid img tag
        $this->assertResult(
            '<img />',
            ''
        );
        
        // test preservation of valid img tag
        $this->assertResult('<img src="foobar.gif" alt="foobar.gif" />');
        
        // test preservation of invalid img tag when removal is disabled
        $this->assertResult(
            '<img />',
            true,
            array(
                'Core.RemoveInvalidImg' => false
            )
        );
        
        // test transform to unallowed element
        $this->assertResult(
            '<font color="red" face="Arial" size="6">Big Warning!</font>',
            'Big Warning!',
            array('HTML.Allowed' => 'div')
        );
        
        // text-ify commented script contents ( the trailing comment gets
        // removed during generation )
        $this->assertResult(
'<script type="text/javascript"><!--
alert(<b>bold</b>);
// --></script>',
'<script type="text/javascript">
alert(&lt;b&gt;bold&lt;/b&gt;);
// </script>',
            array('HTML.Trusted' => true, 'Output.CommentScriptContents' => false)
        );
        
    }
    
}

