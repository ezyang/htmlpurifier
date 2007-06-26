<?php

require_once 'HTMLPurifier/Strategy/ErrorsHarness.php';
require_once 'HTMLPurifier/Strategy/MakeWellFormed.php';

/*
'Strategy_MakeWellFormed: Tag closed by element end' => '',
'Strategy_MakeWellFormed: Tag closed by document end' => '',
*/

class HTMLPurifier_Strategy_MakeWellFormed_ErrorsTest extends HTMLPurifier_Strategy_ErrorsHarness
{
    
    function getStrategy() {
        return new HTMLPurifier_Strategy_MakeWellFormed();
    }
    
    function testUnnecessaryEndTagRemoved() {
        $this->expectErrorCollection(E_WARNING, 'Strategy_MakeWellFormed: Unnecessary end tag removed', 'b');
        $this->invoke('</b>');
    }
    
    function testUnnecessaryEndTagToText() {
        $this->config->set('Core', 'EscapeInvalidTags', true);
        $this->expectErrorCollection(E_WARNING, 'Strategy_MakeWellFormed: Unnecessary end tag to text', 'b');
        $this->invoke('</b>');
    }
    
    function testStrayEndTagRemoved() {
        $this->expectErrorCollection(E_WARNING, 'Strategy_MakeWellFormed: Stray end tag removed', 'b');
        $this->invoke('<i></b></i>');
    }
    
    function testStrayEndTagToText() {
        $this->config->set('Core', 'EscapeInvalidTags', true);
        $this->expectErrorCollection(E_WARNING, 'Strategy_MakeWellFormed: Stray end tag to text', 'b');
        $this->invoke('<i></b></i>');
    }
    
    function testTagClosedByElementEnd() {
        $this->expectErrorCollection(E_NOTICE, 'Strategy_MakeWellFormed: Tag closed by element end', 'b');
        $this->invoke('<i><b>Foobar</i>');
    }
    
    function testTagClosedByDocumentEnd() {
        $this->expectErrorCollection(E_NOTICE, 'Strategy_MakeWellFormed: Tag closed by document end', 'b');
        $this->invoke('<b>Foobar');
    }
    
}

?>