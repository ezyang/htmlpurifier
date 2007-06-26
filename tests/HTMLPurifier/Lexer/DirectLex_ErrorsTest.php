<?php

require_once 'HTMLPurifier/ErrorsHarness.php';
require_once 'HTMLPurifier/Lexer/DirectLex.php';

class HTMLPurifier_Lexer_DirectLex_ErrorsTest extends HTMLPurifier_ErrorsHarness
{
    
    function invoke($input) {
        $lexer = new HTMLPurifier_Lexer_DirectLex();
        $lexer->tokenizeHTML($input, $this->config, $this->context);
    }
    
    function invokeAttr($input) {
        $lexer = new HTMLPurifier_Lexer_DirectLex();
        $lexer->parseAttributeString($input, $this->config, $this->context);
    }
    
    function testUnclosedComment() {
        $this->expectErrorCollection(E_WARNING, 'Lexer: Unclosed comment');
        $this->invoke('<!-- >');
    }
    
    function testUnescapedLt() {
        $this->expectErrorCollection(E_NOTICE, 'Lexer: Unescaped lt');
        $this->invoke('< foo>');
    }
    
    function testMissingGt() {
        $this->expectErrorCollection(E_WARNING, 'Lexer: Missing gt');
        $this->invoke('<a href=""');
    }
    
    function testMissingAttributeKey1() {
        $this->expectErrorCollection(E_ERROR, 'Lexer: Missing attribute key');
        $this->invokeAttr('=""');
    }
    
    function testMissingAttributeKey2() {
        $this->expectErrorCollection(E_ERROR, 'Lexer: Missing attribute key');
        $this->invokeAttr('foo="bar" =""');
    }
    
    function testMissingEndQuote() {
        $this->expectErrorCollection(E_ERROR, 'Lexer: Missing end quote');
        $this->invokeAttr('src="foo');
    }
    
}

?>