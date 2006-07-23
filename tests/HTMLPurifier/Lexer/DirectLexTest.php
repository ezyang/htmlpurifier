<?php

require_once 'HTMLPurifier/Lexer/DirectLex.php';

class HTMLPurifier_Lexer_DirectLexTest extends UnitTestCase
{
    
    var $DirectLex;
    
    function setUp() {
        $this->DirectLex = new HTMLPurifier_Lexer_DirectLex();
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