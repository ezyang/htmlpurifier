<?php

require_once 'HTMLPurifier/Lexer/DirectLex.php';

class HTMLPurifier_Lexer_DirectLex_ErrorsTest extends UnitTestCase
{
    
    var $config, $context;
    var $collector;
    
    function setup() {
        $this->config = HTMLPurifier_Config::create(array('Core.CollectErrors' => true));
        $this->context = new HTMLPurifier_Context();
        generate_mock_once('HTMLPurifier_ErrorCollector');
        $this->collector = new HTMLPurifier_ErrorCollectorMock($this);
        $this->context->register('ErrorCollector', $this->collector);
    }
    
    function invoke($input) {
        $lexer = new HTMLPurifier_Lexer_DirectLex();
        $lexer->tokenizeHTML($input, $this->config, $this->context);
    }
    
    function invokeAttr($input) {
        $lexer = new HTMLPurifier_Lexer_DirectLex();
        $lexer->parseAttributeString($input, $this->config, $this->context);
    }
    
    function expectErrorCollection($severity, $msg) {
        $this->collector->expectOnce('send', array($severity, $msg));
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