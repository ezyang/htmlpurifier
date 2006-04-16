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
            $this->assertEqual($expect[$i], $result);
            paintIf($result, $result != $expect[$i]);
        }
        
    }
    
    function test_makeWellFormed() {
        
        $inputs = array();
        $expect = array();
        
        $inputs[0] = array();
        $expect[0] = $inputs[0];
        
        $inputs[1] = array(
            new MF_Text('This is ')
           ,new MF_StartTag('b')
           ,new MF_Text('bold')
           ,new MF_EndTag('b')
           ,new MF_Text(' text')
            );
        $expect[1] = $inputs[1];
        
        $inputs[2] = array(
            new MF_StartTag('b')
           ,new MF_Text('Unclosed tag, gasp!')
            );
        $expect[2] = array(
            new MF_StartTag('b')
           ,new MF_Text('Unclosed tag, gasp!')
           ,new MF_EndTag('b')
            );
        
        $inputs[3] = array(
            new MF_StartTag('b')
           ,new MF_StartTag('i')
           ,new MF_Text('The b is closed, but the i is not')
           ,new MF_EndTag('b')
            );
        $expect[3] = array(
            new MF_StartTag('b')
           ,new MF_StartTag('i')
           ,new MF_Text('The b is closed, but the i is not')
           ,new MF_EndTag('i')
           ,new MF_EndTag('b')
            );
        
        $inputs[4] = array(
            new MF_Text('Hey, recycle unused end tags!')
           ,new MF_EndTag('b')
            );
        $expect[4] = array(
            new MF_Text('Hey, recycle unused end tags!')
           ,new MF_Text('</b>')
            );
        
        $inputs[5] = array(new MF_StartTag('br'));
        $expect[5] = array(new MF_EmptyTag('br'));
        
        $inputs[6] = array(new MF_EmptyTag('div'));
        $expect[6] = array(
            new MF_StartTag('div')
           ,new MF_EndTag('div')
            );
        
        foreach ($inputs as $i => $input) {
            $result = $this->def->makeWellFormed($input);
            $this->assertEqual($expect[$i], $result);
            paintIf($result, $result != $expect[$i]);
        }
        
    }
    
}

?>