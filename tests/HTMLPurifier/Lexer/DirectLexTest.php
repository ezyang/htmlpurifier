<?php

require_once 'HTMLPurifier/Lexer/DirectLex.php';
require_once 'HTMLPurifier/Lexer/PEARSax3.php';

class HTMLPurifier_LexerTest extends UnitTestCase
{
    
    var $DirectLex, $PEARSax3, $DOMLex;
    var $_has_dom;
    
    function setUp() {
        $this->DirectLex = new HTMLPurifier_Lexer_DirectLex();
        $this->PEARSax3  = new HTMLPurifier_Lexer_PEARSax3();
        
        $this->_has_dom = version_compare(PHP_VERSION, '5', '>=');
        
        if ($this->_has_dom) {
            require_once 'HTMLPurifier/Lexer/DOMLex.php';
            $this->DOMLex    = new HTMLPurifier_Lexer_DOMLex();
        }
        
    }
    
    function test_nextWhiteSpace() {
        $HP =& $this->DirectLex;
        $this->assertIdentical(false, $HP->nextWhiteSpace('asdf'));
        $this->assertIdentical(0, $HP->nextWhiteSpace(' asdf'));
        $this->assertIdentical(0, $HP->nextWhiteSpace("\nasdf"));
        $this->assertIdentical(1, $HP->nextWhiteSpace("a\tsdf"));
        $this->assertIdentical(4, $HP->nextWhiteSpace("asdf\r"));
        $this->assertIdentical(2, $HP->nextWhiteSpace("as\t\r\nasdf as"));
        $this->assertIdentical(3, $HP->nextWhiteSpace('a a ', 2));
    }
    
    function test_parseData() {
        $HP =& $this->DirectLex;
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
        
        // [XML-INVALID]
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
        // DOM is different because it condenses empty tags into REAL empty ones
        // as well as makes it well-formed
        $dom_expect[4] = array(
            new HTMLPurifier_Token_Empty('asdf')
           ,new HTMLPurifier_Token_Empty('d')
           ,new HTMLPurifier_Token_Start('pooloka')
           ,new HTMLPurifier_Token_Start('poolasdf')
           ,new HTMLPurifier_Token_Empty('ds')
           ,new HTMLPurifier_Token_End('poolasdf')
           ,new HTMLPurifier_Token_End('pooloka')
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
        
        // [SGML-INVALID] [RECOVERABLE]
        $input[7] = '<!-- Comment --> <!-- not so well formed --->';
        $expect[7] = array(
            new HTMLPurifier_Token_Comment(' Comment ')
           ,new HTMLPurifier_Token_Text(' ')
           ,new HTMLPurifier_Token_Comment(' not so well formed -')
            );
        $sax_expect[7] = false; // we need to figure out proper comment output
        
        // [SGML-INVALID]
        $input[8] = '<a href=""';
        $expect[8] = array(
            new HTMLPurifier_Token_Text('<a href=""')
            );
        // SAX parses it into a tag
        $sax_expect[8] = array(
            new HTMLPurifier_Token_Start('a', array('href'=>''))
            ); 
        // DOM parses it into an empty tag
        $dom_expect[8] = array(
            new HTMLPurifier_Token_Empty('a', array('href'=>''))
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
        
        // [SGML-INVALID]
        $input[10] = '<a "=>';
        // We barf on this, aim for no attributes
        $expect[10] = array(
            new HTMLPurifier_Token_Start('a', array('"' => ''))
            );
        // DOM correctly has no attributes, but also closes the tag
        $dom_expect[10] = array(
            new HTMLPurifier_Token_Empty('a')
            );
        // SAX barfs on this
        $sax_expect[10] = array(
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
            $result = $this->DirectLex->tokenizeHTML($input[$i]);
            $this->assertEqual($expect[$i], $result, 'Test '.$i.': %s');
            paintIf($result, $expect[$i] != $result);
            
            // assert unless I say otherwise
            $sax_result = $this->PEARSax3->tokenizeHTML($input[$i]);
            if (!isset($sax_expect[$i])) {
                // by default, assert with normal result
                $this->assertEqual($expect[$i], $sax_result, 'Test '.$i.': %s');
                paintIf($sax_result, $expect[$i] != $sax_result);
            } elseif ($sax_expect[$i] === false) {
                // assertions were turned off, optionally dump
                // paintIf($sax_expect, $i == NUMBER);
            } else {
                // match with a custom SAX result array
                $this->assertEqual($sax_expect[$i], $sax_result, 'Test '.$i.': %s');
                paintIf($sax_result, $sax_expect[$i] != $sax_result);
            }
            if ($this->_has_dom) {
                $dom_result = $this->DOMLex->tokenizeHTML($input[$i]);
                // same structure as SAX
                if (!isset($dom_expect[$i])) {
                    $this->assertEqual($expect[$i], $dom_result, 'Test '.$i.': %s');
                    paintIf($dom_result, $expect[$i] != $dom_result);
                } elseif ($dom_expect[$i] === false) {
                    // paintIf($dom_result, $i == NUMBER);
                } else {
                    $this->assertEqual($dom_expect[$i], $dom_result, 'Test '.$i.': %s');
                    paintIf($dom_result, $dom_expect[$i] != $dom_result);
                }
            }
            
        }
        
    }
    
    // internals testing
    function test_tokenizeAttributeString() {
        
        $input[0] = 'href="asdf" boom="assdf"';
        $expect[0] = array('href'=>'asdf', 'boom'=>'assdf');
        
        $input[1] = "href='r'";
        $expect[1] = array('href'=>'r');
        
        $input[2] = 'onclick="javascript:alert(\'asdf\');"';
        $expect[2] = array('onclick' => "javascript:alert('asdf');");
        
        $input[3] = 'selected';
        $expect[3] = array('selected'=>'selected');
        
        $input[4] = '="asdf"';
        $expect[4] = array();
        
        $input[5] = 'missile=launch';
        $expect[5] = array('missile' => 'launch');
        
        $input[6] = 'href="foo';
        $expect[6] = array('href' => 'foo');
        
        $input[7] = '"=';
        $expect[7] = array('"' => '');
        //           0123456789012345678901234567890123
        $input[8] = 'href ="about:blank"rel ="nofollow"';
        $expect[8] = array('href' => 'about:blank', 'rel' => 'nofollow');
        
        $input[9] = 'foo bar';
        $expect[9] = array('foo' => 'foo', 'bar' => 'bar');
        
        $input[10] = 'foo="bar" blue';
        $expect[10] = array('foo' => 'bar', 'blue' => 'blue');
        
        $size = count($input);
        for($i = 0; $i < $size; $i++) {
            $result = $this->DirectLex->tokenizeAttributeString($input[$i]);
            $this->assertEqual($expect[$i], $result, 'Test ' . $i . ': %s');
            paintIf($result, $expect[$i] != $result);
        }
        
    }
    
    
}

?>