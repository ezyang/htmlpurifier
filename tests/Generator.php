<?php

require_once 'HTMLPurifier/Generator.php';

class Test_HTMLPurifier_Generator extends UnitTestCase
{
    
    var $gen;
    
    function Test_HTMLPurifier_Generator() {
        $this->UnitTestCase();
        $this->gen = new HTMLPurifier_Generator();
    }
    
    function test_generateFromToken() {
        
        $inputs = array();
        $expect = array();
        
        $inputs[0] = new HTMLPurifier_Token_Text('Foobar.<>');
        $expect[0] = 'Foobar.&lt;&gt;';
        
        $inputs[1] = new HTMLPurifier_Token_Start('a',
                            array('href' => 'dyn?a=foo&b=bar')
                         );
        $expect[1] = '<a href="dyn?a=foo&amp;b=bar">';
        
        $inputs[2] = new HTMLPurifier_Token_End('b');
        $expect[2] = '</b>';
        
        $inputs[3] = new HTMLPurifier_Token_Empty('br',
                            array('style' => 'font-family:"Courier New";')
                         );
        $expect[3] = '<br style="font-family:&quot;Courier New&quot;;" />';
        
        $inputs[4] = new HTMLPurifier_Token_Start('asdf');
        $expect[4] = '<asdf>';
        
        $inputs[5] = new HTMLPurifier_Token_Empty('br');
        $expect[5] = '<br />';
        
        foreach ($inputs as $i => $input) {
            $result = $this->gen->generateFromToken($input);
            $this->assertEqual($result, $expect[$i]);
            paintIf($result, $result != $expect[$i]);
        }
        
    }
    
    function test_generateAttributes() {
        
        $inputs = array();
        $expect = array();
        
        $inputs[0] = array();
        $expect[0] = '';
        
        $inputs[1] = array('href' => 'dyn?a=foo&b=bar');
        $expect[1] = 'href="dyn?a=foo&amp;b=bar"';
        
        $inputs[2] = array('style' => 'font-family:"Courier New";');
        $expect[2] = 'style="font-family:&quot;Courier New&quot;;"';
        
        $inputs[3] = array('src' => 'picture.jpg', 'alt' => 'Short & interesting');
        $expect[3] = 'src="picture.jpg" alt="Short &amp; interesting"';
        
        foreach ($inputs as $i => $input) {
            $result = $this->gen->generateAttributes($input);
            $this->assertEqual($result, $expect[$i]);
            paintIf($result, $result != $expect[$i]);
        }
        
    }
    
    function test_generateFromTokens() {
        
        $tokens = array(
            new HTMLPurifier_Token_Start('b'),
            new HTMLPurifier_Token_Text('Foobar!'),
            new HTMLPurifier_Token_End('b')
            );
        $expect = '<b>Foobar!</b>';
        $this->assertEqual($expect, $this->gen->generateFromTokens($tokens));
        
    }
    
}

?>