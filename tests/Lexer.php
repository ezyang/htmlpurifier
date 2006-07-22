<?php

/* TODO
    * Benchmark the SAX parser with my homemade one
 */

require_once 'HTMLPurifier/Lexer.php';

class Test_HTMLPurifier_Lexer extends UnitTestCase
{
    
    var $HTMLPurifier_Lexer;
    var $HTMLPurifier_Lexer_Sax;
    
    function setUp() {
        $this->HTMLPurifier_Lexer     =& new HTMLPurifier_Lexer();
        $this->HTMLPurifier_Lexer_Sax =& new HTMLPurifier_Lexer_Sax();
    }
    
    function test_nextWhiteSpace() {
        $HP =& $this->HTMLPurifier_Lexer;
        $this->assertIdentical(false, $HP->nextWhiteSpace('asdf'));
        $this->assertIdentical(0, $HP->nextWhiteSpace(' asdf'));
        $this->assertIdentical(0, $HP->nextWhiteSpace("\nasdf"));
        $this->assertIdentical(1, $HP->nextWhiteSpace("a\tsdf"));
        $this->assertIdentical(4, $HP->nextWhiteSpace("asdf\r"));
        $this->assertIdentical(2, $HP->nextWhiteSpace("as\t\r\nasdf as"));
    }
    
    function test_parseData() {
        $HP =& $this->HTMLPurifier_Lexer;
        $this->assertIdentical('asdf', $HP->parseData('asdf'));
        $this->assertIdentical('&', $HP->parseData('&amp;'));
        $this->assertIdentical('"', $HP->parseData('&quot;'));
        $this->assertIdentical("'", $HP->parseData('&#039;'));
        $this->assertIdentical('-', $HP->parseData('&#x2D;'));
        // UTF-8 needed!!!
    }
    
    function test_tokenizeHTML() {
        
        $input = array();
        $expect = array();
        $sax_expect = array();
        
        $input[0] = '';
        $expect[0] = array();
        
        $input[1] = 'This is regular text.';
        $expect[1] = array(
            new HTMLPurifier_Token_Text('This is regular text.')
            );
        
        $input[2] = 'This is <b>bold</b> text';
        $expect[2] = array(
            new HTMLPurifier_Token_Text('This is ')
           ,new HTMLPurifier_Token_Start('b', array())
           ,new HTMLPurifier_Token_Text('bold')
           ,new HTMLPurifier_Token_End('b')
           ,new HTMLPurifier_Token_Text(' text')
            );
        
        $input[3] = '<DIV>Totally rad dude. <b>asdf</b></div>';
        $expect[3] = array(
            new HTMLPurifier_Token_Start('DIV', array())
           ,new HTMLPurifier_Token_Text('Totally rad dude. ')
           ,new HTMLPurifier_Token_Start('b', array())
           ,new HTMLPurifier_Token_Text('asdf')
           ,new HTMLPurifier_Token_End('b')
           ,new HTMLPurifier_Token_End('div')
            );
        
        $input[4] = '<asdf></asdf><d></d><poOloka><poolasdf><ds></asdf></ASDF>';
        $expect[4] = array(
            new HTMLPurifier_Token_Start('asdf')
           ,new HTMLPurifier_Token_End('asdf')
           ,new HTMLPurifier_Token_Start('d')
           ,new HTMLPurifier_Token_End('d')
           ,new HTMLPurifier_Token_Start('poOloka')
           ,new HTMLPurifier_Token_Start('poolasdf')
           ,new HTMLPurifier_Token_Start('ds')
           ,new HTMLPurifier_Token_End('asdf')
           ,new HTMLPurifier_Token_End('ASDF')
            );
        
        $input[5] = '<a'."\t".'href="foobar.php"'."\n".'title="foo!">Link to <b id="asdf">foobar</b></a>';
        $expect[5] = array(
            new HTMLPurifier_Token_Start('a',array('href'=>'foobar.php','title'=>'foo!'))
           ,new HTMLPurifier_Token_Text('Link to ')
           ,new HTMLPurifier_Token_Start('b',array('id'=>'asdf'))
           ,new HTMLPurifier_Token_Text('foobar')
           ,new HTMLPurifier_Token_End('b')
           ,new HTMLPurifier_Token_End('a')
            );
        
        $input[6] = '<br />';
        $expect[6] = array(
            new HTMLPurifier_Token_Empty('br')
            );
        
        // [INVALID] [RECOVERABLE]
        $input[7] = '<!-- Comment --> <!-- not so well formed --->';
        $expect[7] = array(
            new HTMLPurifier_Token_Comment(' Comment ')
           ,new HTMLPurifier_Token_Text(' ')
           ,new HTMLPurifier_Token_Comment(' not so well formed -')
            );
        $sax_expect[7] = false; // we need to figure out proper comment output
        
        // [INVALID]
        $input[8] = '<a href=""';
        $expect[8] = array(
            new HTMLPurifier_Token_Text('<a href=""')
            );
        // SAX parses it into a tag
        $sax_expect[8] = array(
            new HTMLPurifier_Token_Start('a', array('href'=>''))
            ); 
        
        $input[9] = '&lt;b&gt;';
        $expect[9] = array(
            new HTMLPurifier_Token_Text('<b>')
            );
        $sax_expect[9] = array(
            new HTMLPurifier_Token_Text('<')
           ,new HTMLPurifier_Token_Text('b')
           ,new HTMLPurifier_Token_Text('>')
            );
        // note that SAX can clump text nodes together. We won't be
        // too picky though
        
        // [INVALID]
        $input[10] = '<a "=>';
        $expect[10] = array(
            new HTMLPurifier_Token_Start('a', array('"' => ''))
            );
        
        // [INVALID] [RECOVERABLE]
        $input[11] = '"';
        $expect[11] = array( new HTMLPurifier_Token_Text('"') );
        
        // compare with this valid one:
        $input[12] = '&quot;';
        $expect[12] = array( new HTMLPurifier_Token_Text('"') );
        $sax_expect[12] = false;
        // SAX chokes on this? We do have entity parsing on, so it should work!
        
        foreach($input as $i => $discard) {
            $result = $this->HTMLPurifier_Lexer->tokenizeHTML($input[$i]);
            $this->assertEqual($expect[$i], $result);
            paintIf($result, $expect[$i] != $result);
            
            // assert unless I say otherwise
            $sax_result = $this->HTMLPurifier_Lexer_Sax->tokenizeHTML($input[$i]);
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
        
        $input[] = 'missile=launch';
        $expect[] = array('missile' => 'launch');
        
        $input[] = 'href="foo';
        $expect[] = array('href' => 'foo');
        
        $size = count($input);
        for($i = 0; $i < $size; $i++) {
            $result = $this->HTMLPurifier_Lexer->tokenizeAttributeString($input[$i]);
            $this->assertEqual($expect[$i], $result);
            paintIf($result, $expect[$i] != $result);
        }
        
    }
    
    
}

?>