<?php

class TestCase_HTML_Lexer extends UnitTestCase
{
    
    var $HTML_Lexer;
    var $HTML_Lexer_Sax;
    
    function setUp() {
        $this->HTML_Lexer     =& new HTML_Lexer();
        $this->HTML_Lexer_Sax =& new HTML_Lexer_Sax();
    }
    
    function test_nextWhiteSpace() {
        $HP =& $this->HTML_Lexer;
        $this->assertIdentical(false, $HP->nextWhiteSpace('asdf'));
        $this->assertIdentical(0, $HP->nextWhiteSpace(' asdf'));
        $this->assertIdentical(0, $HP->nextWhiteSpace("\nasdf"));
        $this->assertIdentical(1, $HP->nextWhiteSpace("a\tsdf"));
        $this->assertIdentical(4, $HP->nextWhiteSpace("asdf\r"));
        $this->assertIdentical(2, $HP->nextWhiteSpace("as\t\r\nasdf as"));
    }
    
    function test_tokenizeHTML() {
        
        $input[] = '';
        $expect[] = array();
        
        $input[] = 'This is regular text.';
        $expect[] = array(
            new MF_Text('This is regular text.')
            );
        
        $input[] = 'This is <b>bold</b> text';
        $expect[] = array(
            new MF_Text('This is ')
           ,new MF_StartTag('b', array())
           ,new MF_Text('bold')
           ,new MF_EndTag('b')
           ,new MF_Text(' text')
            );
        
        $input[] = '<DIV>Totally rad dude. <b>asdf</b></div>';
        $expect[] = array(
            new MF_StartTag('DIV', array())
           ,new MF_Text('Totally rad dude. ')
           ,new MF_StartTag('b', array())
           ,new MF_Text('asdf')
           ,new MF_EndTag('b')
           ,new MF_EndTag('div')
            );
        
        $input[] = '<asdf></asdf><d></d><poOloka><poolasdf><ds></asdf></ASDF>';
        $expect[] = array(
            new MF_StartTag('asdf')
           ,new MF_EndTag('asdf')
           ,new MF_StartTag('d')
           ,new MF_EndTag('d')
           ,new MF_StartTag('poOloka')
           ,new MF_StartTag('poolasdf')
           ,new MF_StartTag('ds')
           ,new MF_EndTag('asdf')
           ,new MF_EndTag('ASDF')
            );
        
        $input[] = '<a'."\t".'href="foobar.php"'."\n".'title="foo!">Link to <b id="asdf">foobar</b></a>';
        $expect[] = array(
            new MF_StartTag('a',array('href'=>'foobar.php','title'=>'foo!'))
           ,new MF_Text('Link to ')
           ,new MF_StartTag('b',array('id'=>'asdf'))
           ,new MF_Text('foobar')
           ,new MF_EndTag('b')
           ,new MF_EndTag('a')
            );
        
        $input[] = '<br />';
        $expect[] = array(
            new MF_EmptyTag('br')
            );
        
        $input[] = '<!-- Comment --> <!-- not so well formed --->';
        $expect[] = array(
            new MF_Comment(' Comment ')
           ,new MF_Text(' ')
           ,new MF_Comment(' not so well formed -')
            );
        
        $input[] = '<a href=""';
        $expect[] = array(
            new MF_Text('<a href=""')
            );
        
        $size = count($input);
        for($i = 0; $i < $size; $i++) {
            $result = $this->HTML_Lexer->tokenizeHTML($input[$i]);
            $this->assertEqual($expect[$i], $result);
            paintIf($result, $expect[$i] != $result);
            
            // since I didn't write the parser, I can't define its behavior
            // however, make sure that the class runs without any errors
            $exp_result = $this->HTML_Lexer_Sax->tokenizeHTML($input[$i]);
        }
        
    }
    
    function test_tokenizeAttributeString() {
        
        $input[] = 'href="asdf" boom="assdf"';
        $expect[] = array('href'=>'asdf', 'boom'=>'assdf');
        
        $input[] = "href='r'";
        $expect[] = array('href'=>'r');
        
        $input[] = 'onclick="javascript:alert(\'asdf\');"';
        $expect[] = array('onclick' => "javascript:alert('asdf');");
        
        $input[] = 'selected';
        $expect[] = array('selected'=>'selected');
        
        $input[] = '="asdf"';
        $expect[] = array();
        
        $size = count($input);
        for($i = 0; $i < $size; $i++) {
            $result = $this->HTML_Lexer->tokenizeAttributeString($input[$i]);
            $this->assertEqual($expect[$i], $result);
            paintIf($result, $expect[$i] != $result);
        }
        
    }
    
    
}

?>