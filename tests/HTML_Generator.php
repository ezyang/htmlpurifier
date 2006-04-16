<?php

class UnitTest_HTML_Generator extends UnitTestCase
{
    
    var $gen;
    
    function UnitTest_HTML_Generator() {
        $this->UnitTestCase();
        $this->gen = new HTML_Generator();
    }
    
    function test_generateFromToken() {
        
        $inputs = array();
        $expect = array();
        
        $inputs[0] = new MF_Text('Foobar.<>');
        $expect[0] = 'Foobar.&lt;&gt;';
        
        $inputs[1] = new MF_StartTag('a', array('href' => 'dyn?a=foo&b=bar'));
        $expect[1] = '<a href="dyn?a=foo&amp;b=bar">';
        
        $inputs[2] = new MF_EndTag('b');
        $expect[2] = '</b>';
        
        $inputs[3] = new MF_EmptyTag('br', array('style' => 'font-family:"Courier New";'));
        $expect[3] = '<br style="font-family:&quot;Courier New&quot;;" />';
        
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
    
}

?>