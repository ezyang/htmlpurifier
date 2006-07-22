<?php

class Test_HTMLPurifier_Definition extends UnitTestCase
{
    
    var $def, $lex;
    
    function Test_HTMLPurifier_Definition() {
        $this->UnitTestCase();
        $this->def = new HTMLPurifier_Definition();
        $this->def->loadData();
        $this->lex = new HTMLPurifier_Lexer();
    }
    
    function test_removeForeignElements() {
        
        $inputs = array();
        $expect = array();
        
        $inputs[0] = array();
        $expect[0] = $inputs[0];
        
        $inputs[1] = array(
            new HTMLPurifier_Token_Text('This is ')
           ,new HTMLPurifier_Token_Start('b', array())
           ,new HTMLPurifier_Token_Text('bold')
           ,new HTMLPurifier_Token_End('b')
           ,new HTMLPurifier_Token_Text(' text')
            );
        $expect[1] = $inputs[1];
        
        $inputs[2] = array(
            new HTMLPurifier_Token_Start('asdf')
           ,new HTMLPurifier_Token_End('asdf')
           ,new HTMLPurifier_Token_Start('d', array('href' => 'bang!'))
           ,new HTMLPurifier_Token_End('d')
           ,new HTMLPurifier_Token_Start('pooloka')
           ,new HTMLPurifier_Token_Start('poolasdf')
           ,new HTMLPurifier_Token_Start('ds', array('moogle' => '&'))
           ,new HTMLPurifier_Token_End('asdf')
           ,new HTMLPurifier_Token_End('asdf')
            );
        $expect[2] = array(
            new HTMLPurifier_Token_Text('<asdf>')
           ,new HTMLPurifier_Token_Text('</asdf>')
           ,new HTMLPurifier_Token_Text('<d href="bang!">')
           ,new HTMLPurifier_Token_Text('</d>')
           ,new HTMLPurifier_Token_Text('<pooloka>')
           ,new HTMLPurifier_Token_Text('<poolasdf>')
           ,new HTMLPurifier_Token_Text('<ds moogle="&amp;">')
           ,new HTMLPurifier_Token_Text('</asdf>')
           ,new HTMLPurifier_Token_Text('</asdf>')
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
            new HTMLPurifier_Token_Text('This is ')
           ,new HTMLPurifier_Token_Start('b')
           ,new HTMLPurifier_Token_Text('bold')
           ,new HTMLPurifier_Token_End('b')
           ,new HTMLPurifier_Token_Text(' text')
           ,new HTMLPurifier_Token_Empty('br')
            );
        $expect[1] = $inputs[1];
        
        $inputs[2] = array(
            new HTMLPurifier_Token_Start('b')
           ,new HTMLPurifier_Token_Text('Unclosed tag, gasp!')
            );
        $expect[2] = array(
            new HTMLPurifier_Token_Start('b')
           ,new HTMLPurifier_Token_Text('Unclosed tag, gasp!')
           ,new HTMLPurifier_Token_End('b')
            );
        
        $inputs[3] = array(
            new HTMLPurifier_Token_Start('b')
           ,new HTMLPurifier_Token_Start('i')
           ,new HTMLPurifier_Token_Text('The b is closed, but the i is not')
           ,new HTMLPurifier_Token_End('b')
            );
        $expect[3] = array(
            new HTMLPurifier_Token_Start('b')
           ,new HTMLPurifier_Token_Start('i')
           ,new HTMLPurifier_Token_Text('The b is closed, but the i is not')
           ,new HTMLPurifier_Token_End('i')
           ,new HTMLPurifier_Token_End('b')
            );
        
        $inputs[4] = array(
            new HTMLPurifier_Token_Text('Hey, recycle unused end tags!')
           ,new HTMLPurifier_Token_End('b')
            );
        $expect[4] = array(
            new HTMLPurifier_Token_Text('Hey, recycle unused end tags!')
           ,new HTMLPurifier_Token_Text('</b>')
            );
        
        $inputs[5] = array(new HTMLPurifier_Token_Start('br', array('style' => 'clear:both;')));
        $expect[5] = array(new HTMLPurifier_Token_Empty('br', array('style' => 'clear:both;')));
        
        $inputs[6] = array(new HTMLPurifier_Token_Empty('div', array('style' => 'clear:both;')));
        $expect[6] = array(
            new HTMLPurifier_Token_Start('div', array('style' => 'clear:both;'))
           ,new HTMLPurifier_Token_End('div')
            );
        
        // test automatic paragraph closing
        
        $inputs[7] = array(
            new HTMLPurifier_Token_Start('p')
           ,new HTMLPurifier_Token_Text('Paragraph 1')
           ,new HTMLPurifier_Token_Start('p')
           ,new HTMLPurifier_Token_Text('Paragraph 2')
            );
        $expect[7] = array(
            new HTMLPurifier_Token_Start('p')
           ,new HTMLPurifier_Token_Text('Paragraph 1')
           ,new HTMLPurifier_Token_End('p')
           ,new HTMLPurifier_Token_Start('p')
           ,new HTMLPurifier_Token_Text('Paragraph 2')
           ,new HTMLPurifier_Token_End('p')
            );
        
        $inputs[8] = array(
            new HTMLPurifier_Token_Start('div')
           ,new HTMLPurifier_Token_Start('p')
           ,new HTMLPurifier_Token_Text('Paragraph 1 in a div')
           ,new HTMLPurifier_Token_End('div')
            );
        $expect[8] = array(
            new HTMLPurifier_Token_Start('div')
           ,new HTMLPurifier_Token_Start('p')
           ,new HTMLPurifier_Token_Text('Paragraph 1 in a div')
           ,new HTMLPurifier_Token_End('p')
           ,new HTMLPurifier_Token_End('div')
            );
        
        // automatic list closing
        
        $inputs[9] = array(
            new HTMLPurifier_Token_Start('ol')
            
           ,new HTMLPurifier_Token_Start('li')
           ,new HTMLPurifier_Token_Text('Item 1')
           
           ,new HTMLPurifier_Token_Start('li')
           ,new HTMLPurifier_Token_Text('Item 2')
           
           ,new HTMLPurifier_Token_End('ol')
            );
        $expect[9] = array(
            new HTMLPurifier_Token_Start('ol')
            
           ,new HTMLPurifier_Token_Start('li')
           ,new HTMLPurifier_Token_Text('Item 1')
           ,new HTMLPurifier_Token_End('li')
           
           ,new HTMLPurifier_Token_Start('li')
           ,new HTMLPurifier_Token_Text('Item 2')
           ,new HTMLPurifier_Token_End('li')
           
           ,new HTMLPurifier_Token_End('ol')
            );
        
        foreach ($inputs as $i => $input) {
            $result = $this->def->makeWellFormed($input);
            $this->assertEqual($expect[$i], $result);
            paintIf($result, $result != $expect[$i]);
        }
        
    }
    
    function test_fixNesting() {
        $inputs = array();
        $expect = array();
        
        // next id = 4
        
        // legal inline nesting
        $inputs[0] = array(
            new HTMLPurifier_Token_Start('b'),
                new HTMLPurifier_Token_Text('Bold text'),
            new HTMLPurifier_Token_End('b'),
            );
        $expect[0] = $inputs[0];
        
        // legal inline and block
        // as the parent element is considered FLOW
        $inputs[1] = array(
            new HTMLPurifier_Token_Start('a', array('href' => 'http://www.example.com/')),
                new HTMLPurifier_Token_Text('Linky'),
            new HTMLPurifier_Token_End('a'),
            new HTMLPurifier_Token_Start('div'),
                new HTMLPurifier_Token_Text('Block element'),
            new HTMLPurifier_Token_End('div'),
            );
        $expect[1] = $inputs[1];
        
        // illegal block in inline, element -> text
        $inputs[2] = array(
            new HTMLPurifier_Token_Start('b'),
                new HTMLPurifier_Token_Start('div'),
                    new HTMLPurifier_Token_Text('Illegal Div'),
                new HTMLPurifier_Token_End('div'),
            new HTMLPurifier_Token_End('b'),
            );
        $expect[2] = array(
            new HTMLPurifier_Token_Start('b'),
                new HTMLPurifier_Token_Text('<div>'),
                new HTMLPurifier_Token_Text('Illegal Div'),
                new HTMLPurifier_Token_Text('</div>'),
            new HTMLPurifier_Token_End('b'),
            );
        
        // test of empty set that's required, resulting in removal of node
        $inputs[3] = array(
            new HTMLPurifier_Token_Start('ul'),
            new HTMLPurifier_Token_End('ul')
            );
        $expect[3] = array();
        
        // test illegal text which gets removed
        $inputs[4] = array(
            new HTMLPurifier_Token_Start('ul'),
                new HTMLPurifier_Token_Text('Illegal Text'),
                new HTMLPurifier_Token_Start('li'),
                    new HTMLPurifier_Token_Text('Legal item'),
                new HTMLPurifier_Token_End('li'),
            new HTMLPurifier_Token_End('ul')
            );
        $expect[4] = array(
            new HTMLPurifier_Token_Start('ul'),
                new HTMLPurifier_Token_Start('li'),
                    new HTMLPurifier_Token_Text('Legal item'),
                new HTMLPurifier_Token_End('li'),
            new HTMLPurifier_Token_End('ul')
            );
        
        foreach ($inputs as $i => $input) {
            $result = $this->def->fixNesting($input);
            $this->assertEqual($expect[$i], $result);
            paintIf($result, $result != $expect[$i]);
        }
    }
    
}

?>