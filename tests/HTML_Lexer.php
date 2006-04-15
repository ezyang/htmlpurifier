<?php

/* TODO
    * Benchmark the SAX parser with my homemade one
 */

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
        
        $input = array();
        $expect = array();
        $sax_expect = array();
        
        $input[0] = '';
        $expect[0] = array();
        
        $input[1] = 'This is regular text.';
        $expect[1] = array(
            new MF_Text('This is regular text.')
            );
        
        $input[2] = 'This is <b>bold</b> text';
        $expect[2] = array(
            new MF_Text('This is ')
           ,new MF_StartTag('b', array())
           ,new MF_Text('bold')
           ,new MF_EndTag('b')
           ,new MF_Text(' text')
            );
        
        $input[3] = '<DIV>Totally rad dude. <b>asdf</b></div>';
        $expect[3] = array(
            new MF_StartTag('DIV', array())
           ,new MF_Text('Totally rad dude. ')
           ,new MF_StartTag('b', array())
           ,new MF_Text('asdf')
           ,new MF_EndTag('b')
           ,new MF_EndTag('div')
            );
        
        $input[4] = '<asdf></asdf><d></d><poOloka><poolasdf><ds></asdf></ASDF>';
        $expect[4] = array(
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
        
        $input[5] = '<a'."\t".'href="foobar.php"'."\n".'title="foo!">Link to <b id="asdf">foobar</b></a>';
        $expect[5] = array(
            new MF_StartTag('a',array('href'=>'foobar.php','title'=>'foo!'))
           ,new MF_Text('Link to ')
           ,new MF_StartTag('b',array('id'=>'asdf'))
           ,new MF_Text('foobar')
           ,new MF_EndTag('b')
           ,new MF_EndTag('a')
            );
        
        $input[6] = '<br />';
        $expect[6] = array(
            new MF_EmptyTag('br')
            );
        
        // [INVALID] [RECOVERABLE]
        $input[7] = '<!-- Comment --> <!-- not so well formed --->';
        $expect[7] = array(
            new MF_Comment(' Comment ')
           ,new MF_Text(' ')
           ,new MF_Comment(' not so well formed -')
            );
        $sax_expect[7] = false; // we need to figure out proper comment output
        
        // [INVALID]
        $input[8] = '<a href=""';
        $expect[8] = array(
            new MF_Text('<a href=""')
            );
        // SAX parses it into a tag
        $sax_expect[8] = array(
            new MF_StartTag('a', array('href'=>''))
            ); 
        
        $input[9] = '&lt;b&gt;';
        $expect[9] = array(
            new MF_Text('&lt;b&gt;')
            );
        // however, we may want to change both styles
        // into parsed: '<b>'. SAX has an option for this
        
        foreach($input as $i => $discard) {
            $result = $this->HTML_Lexer->tokenizeHTML($input[$i]);
            $this->assertEqual($expect[$i], $result);
            paintIf($result, $expect[$i] != $result);
            
            // assert unless I say otherwise
            $sax_result = $this->HTML_Lexer_Sax->tokenizeHTML($input[$i]);
            if (!isset($sax_expect[$i])) {
                // by default, assert with normal result
                $this->assertEqual($expect[$i], $sax_result);
                paintIf($sax_result, $expect[$i] != $sax_result);
            } elseif ($sax_expect[$i] === false) {
                // assertions were turned off, optionally dump
                // paintIf($sax_expect, $i == NUMBER);
            } else {
                // match with a custom SAX result array
                $this->assertEqual($sax_expect[$i], $sax_result);
                paintIf($sax_result, $sax_expect[$i] != $sax_result);
            }
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