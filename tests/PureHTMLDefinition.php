<?php

class UnitTest_PureHTMLDefinition extends UnitTestCase
{
    
    var $def;
    
    function UnitTest_PureHTMLDefinition() {
        $this->UnitTestCase();
        $this->def = new PureHTMLDefinition();
        $this->def->loadData();
    }
    
    function test_removeForeignElements() {
        
        $inputs = array();
        $expect = array();
        
        $inputs[0] = array();
        $expect[0] = $inputs[0];
        
        $inputs[1] = array(
            new MF_Text('This is ')
           ,new MF_StartTag('b', array())
           ,new MF_Text('bold')
           ,new MF_EndTag('b')
           ,new MF_Text(' text')
            );
        $expect[1] = $inputs[1];
        
        foreach ($inputs as $i => $input) {
            $result = $this->def->removeForeignElements($input);
            $this->assertEqual($result, $expect[$i]);
            paintIf($result, $result != $expect[$i]);
        }
        
    }
    
}

?>