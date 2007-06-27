<?php

require_once 'HTMLPurifier/Strategy/ErrorsHarness.php';
require_once 'HTMLPurifier/Strategy/MakeWellFormed.php';

class HTMLPurifier_Strategy_MakeWellFormed_ErrorsTest extends HTMLPurifier_Strategy_ErrorsHarness
{
    
    function getStrategy() {
        return new HTMLPurifier_Strategy_MakeWellFormed();
    }
    
    function testUnnecessaryEndTagRemoved() {
        $this->expectErrorCollection(E_WARNING, 'Strategy_MakeWellFormed: Unnecessary end tag removed');
        $this->expectContext('CurrentToken', new HTMLPurifier_Token_End('b', array(), 1));
        $this->invoke('</b>');
    }
    
    function testUnnecessaryEndTagToText() {
        $this->config->set('Core', 'EscapeInvalidTags', true);
        $this->expectErrorCollection(E_WARNING, 'Strategy_MakeWellFormed: Unnecessary end tag to text');
        $this->expectContext('CurrentToken', new HTMLPurifier_Token_End('b', array(), 1));
        $this->invoke('</b>');
    }
    
    function testTagAutoClosed() {
        $this->expectErrorCollection(E_NOTICE, 'Strategy_MakeWellFormed: Tag auto closed', new HTMLPurifier_Token_Start('b', array(), 1));
        $this->expectContext('CurrentToken', new HTMLPurifier_Token_Start('div', array(), 1));
        $this->invoke('<b>Foo<div>Bar</div>');
    }
    
    function testStrayEndTagRemoved() {
        $this->expectErrorCollection(E_WARNING, 'Strategy_MakeWellFormed: Stray end tag removed');
        $this->expectContext('CurrentToken', new HTMLPurifier_Token_End('b', array(), 1));
        $this->invoke('<i></b></i>');
    }
    
    function testStrayEndTagToText() {
        $this->config->set('Core', 'EscapeInvalidTags', true);
        $this->expectErrorCollection(E_WARNING, 'Strategy_MakeWellFormed: Stray end tag to text');
        $this->expectContext('CurrentToken', new HTMLPurifier_Token_End('b', array(), 1));
        $this->invoke('<i></b></i>');
    }
    
    function testTagClosedByElementEnd() {
        $this->expectErrorCollection(E_NOTICE, 'Strategy_MakeWellFormed: Tag closed by element end', new HTMLPurifier_Token_Start('b', array(), 1));
        $this->invoke('<i><b>Foobar</i>');
    }
    
    function testTagClosedByDocumentEnd() {
        $this->expectErrorCollection(E_NOTICE, 'Strategy_MakeWellFormed: Tag closed by document end', new HTMLPurifier_Token_Start('b', array(), 1));
        $this->invoke('<b>Foobar');
    }
    
}

