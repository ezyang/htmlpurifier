<?php

require_once 'HTMLPurifier/Lexer/DirectLex.php';

class HTMLPurifier_Lexer_DirectLexTest extends UnitTestCase
{
    
    var $DirectLex;
    
    function setUp() {
        $this->DirectLex = new HTMLPurifier_Lexer_DirectLex();
    }
    
    function test_parseData() {
        $HP =& $this->DirectLex;
        
        $this->assertIdentical('asdf', $HP->parseData('asdf'));
        $this->assertIdentical('&', $HP->parseData('&amp;'));
        $this->assertIdentical('"', $HP->parseData('&quot;'));
        $this->assertIdentical("'", $HP->parseData('&#039;'));
        $this->assertIdentical("'", $HP->parseData('&#39;'));
        $this->assertIdentical('&&&', $HP->parseData('&amp;&amp;&amp;'));
        $this->assertIdentical('&&', $HP->parseData('&amp;&')); // [INVALID]
        $this->assertIdentical('Procter & Gamble',
                $HP->parseData('Procter & Gamble')); // [INVALID]
        
        // This is not special, thus not converted. Test of fault tolerance,
        // realistically speaking, this should never happen
        $this->assertIdentical('&#x2D;', $HP->parseData('&#x2D;'));
    }
    
    function test_specialEntityCallback() {
        $HP =& $this->DirectLex;
        
        $this->assertIdentical("'",$HP->specialEntityCallback(
            array('&#39;', null, '39', null) ));
    }
    
    // internals testing
    function test_parseAttributeString() {
        
        $input[0] = 'href="about:blank" rel="nofollow"';
        $expect[0] = array('href'=>'about:blank', 'rel'=>'nofollow');
        
        $input[1] = "href='about:blank'";
        $expect[1] = array('href'=>'about:blank');
        
        // note that the single quotes aren't /really/ escaped
        $input[2] = 'onclick="javascript:alert(\'asdf\');"';
        $expect[2] = array('onclick' => "javascript:alert('asdf');");
        
        $input[3] = 'selected';
        $expect[3] = array('selected'=>'selected');
        
        // [INVALID]
        $input[4] = '="nokey"';
        $expect[4] = array();
        
        // [SIMPLE]
        $input[5] = 'color=blue';
        $expect[5] = array('color' => 'blue');
        
        // [INVALID]
        $input[6] = 'href="about:blank';
        $expect[6] = array('href' => 'about:blank');
        
        // [INVALID]
        $input[7] = '"=';
        $expect[7] = array('"' => '');
        // we ought to get array()
        
        $input[8] = 'href ="about:blank"rel ="nofollow"';
        $expect[8] = array('href' => 'about:blank', 'rel' => 'nofollow');
        
        $input[9] = 'two bool';
        $expect[9] = array('two' => 'two', 'bool' => 'bool');
        
        $input[10] = 'name="input" selected';
        $expect[10] = array('name' => 'input', 'selected' => 'selected');
        
        $size = count($input);
        for($i = 0; $i < $size; $i++) {
            $result = $this->DirectLex->parseAttributeString($input[$i]);
            $this->assertEqual($expect[$i], $result, 'Test ' . $i . ': %s');
            paintIf($result, $expect[$i] != $result);
        }
        
    }
    
    
}

?>