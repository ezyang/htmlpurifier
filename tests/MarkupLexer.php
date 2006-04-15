<?php

class SimpleTest_MarkupLexer extends UnitTestCase
{
    
    var $MarkupLexer;
    
    function setUp() {
        $this->MarkupLexer =& new MarkupLexer();
    }
    
    function test_nextWhiteSpace() {
        $HP =& $this->MarkupLexer;
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
            new HTML_Text('This is regular text.')
            );
        
        $input[] = 'This is <b>bold</b> text';
        $expect[] = array(
            new HTML_Text('This is ')
           ,new HTML_StartTag('b', array())
           ,new HTML_Text('bold')
           ,new HTML_EndTag('b')
           ,new HTML_Text(' text')
            );
        
        $input[] = '<DIV>Totally rad dude. <b>asdf</b></div>';
        $expect[] = array(
            new HTML_StartTag('DIV', array())
           ,new HTML_Text('Totally rad dude. ')
           ,new HTML_StartTag('b', array())
           ,new HTML_Text('asdf')
           ,new HTML_EndTag('b')
           ,new HTML_EndTag('div')
            );
        
        $input[] = '<asdf></asdf><d></d><poOloka><poolasdf><ds></asdf></ASDF>';
        $expect[] = array(
            new HTML_StartTag('asdf')
           ,new HTML_EndTag('asdf')
           ,new HTML_StartTag('d')
           ,new HTML_EndTag('d')
           ,new HTML_StartTag('poOloka')
           ,new HTML_StartTag('poolasdf')
           ,new HTML_StartTag('ds')
           ,new HTML_EndTag('asdf')
           ,new HTML_EndTag('ASDF')
            );
        
        $input[] = '<a'."\t".'href="foobar.php"'."\n".'title="foo!">Link to <b id="asdf">foobar</b></a>';
        $expect[] = array(
            new HTML_StartTag('a',array('href'=>'foobar.php','title'=>'foo!'))
           ,new HTML_Text('Link to ')
           ,new HTML_StartTag('b',array('id'=>'asdf'))
           ,new HTML_Text('foobar')
           ,new HTML_EndTag('b')
           ,new HTML_EndTag('a')
            );
        
        $input[] = '<br />';
        $expect[] = array(
            new HTML_EmptyTag('br')
            );
        
        $input[] = '<!-- Comment --> <!-- not so well formed --->';
        $expect[] = array(
            new HTML_Comment(' Comment ')
           ,new HTML_Text(' ')
           ,new HTML_Comment(' not so well formed -')
            );
        
        $input[] = '<a href=""';
        $expect[] = array(
            new HTML_Text('<a href=""')
            );
        
        $size = count($input);
        for($i = 0; $i < $size; $i++) {
            $result = $this->MarkupLexer->tokenizeHTML($input[$i]);
            $this->assertEqual($expect[$i], $result);
            paintIf($result, $expect[$i] != $result);
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
            $result = $this->MarkupLexer->tokenizeAttributeString($input[$i]);
            $this->assertEqual($expect[$i], $result);
            paintIf($result, $expect[$i] != $result);
        }
        
    }
    
    
}

?>