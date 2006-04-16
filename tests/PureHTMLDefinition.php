<?php

class Test_PureHTMLDefinition extends UnitTestCase
{
    
    var $def;
    
    function Test_PureHTMLDefinition() {
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
        
        $inputs[2] = array(
            new MF_StartTag('asdf')
           ,new MF_EndTag('asdf')
           ,new MF_StartTag('d', array('href' => 'bang!'))
           ,new MF_EndTag('d')
           ,new MF_StartTag('pooloka')
           ,new MF_StartTag('poolasdf')
           ,new MF_StartTag('ds', array('moogle' => '&'))
           ,new MF_EndTag('asdf')
           ,new MF_EndTag('asdf')
            );
        $expect[2] = array(
            new MF_Text('<asdf>')
           ,new MF_Text('</asdf>')
           ,new MF_Text('<d href="bang!">')
           ,new MF_Text('</d>')
           ,new MF_Text('<pooloka>')
           ,new MF_Text('<poolasdf>')
           ,new MF_Text('<ds moogle="&amp;">')
           ,new MF_Text('</asdf>')
           ,new MF_Text('</asdf>')
            );
        
        foreach ($inputs as $i => $input) {
            $result = $this->def->removeForeignElements($input);
            $this->assertEqual($result, $expect[$i]);
            paintIf($result, $result != $expect[$i]);
        }
        
    }
    
}

?>