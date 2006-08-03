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
        
        // test simple transform
        $inputs[3] = '<menu><li>Item 1</li></menu>';
        $expect[3] = '<ul><li>Item 1</li></ul>';
        
        // test center transform
        $inputs[4] = '<center>Look I am Centered!</center>';
        $expect[4] = '<div style="text-align:center;">Look I am Centered!</div>';
        
        // test font transform
        $inputs[5] = '<font color="red" face="Arial" size="6">Big Warning!</font>';
        $expect[5] = '<span style="color:red;font-family:Arial;font-size:xx-large;">Big Warning!</span>';
        
        $this->assertStrategyWorks($strategy, $inputs, $expect);
    }
    
}

?>