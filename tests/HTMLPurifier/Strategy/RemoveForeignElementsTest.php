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
        
        
        $this->assertResult('');
        
        $this->assertResult('This is <b>bold text</b>.');
        
        $this->assertResult(
            '<asdf>Bling</asdf><d href="bang">Bong</d><foobar />',
            'BlingBong'
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
        $this->assertResult('<img src="foobar.gif" />');
        
    }
    
}

?>