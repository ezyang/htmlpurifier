<?php

require_once 'HTMLPurifier/Strategy/ErrorsHarness.php';
require_once 'HTMLPurifier/Strategy/RemoveForeignElements.php';

class HTMLPurifier_Strategy_RemoveForeignElements_ErrorsTest extends HTMLPurifier_Strategy_ErrorsHarness
{
    
    function setup() {
        parent::setup();
        $this->config->set('HTML', 'TidyLevel', 'heavy');
    }
    
    function getStrategy() {
        return new HTMLPurifier_Strategy_RemoveForeignElements();
    }
    
    function testTagTransform() {
        // uses $CurrentToken.Serialized
        $this->expectErrorCollection(E_NOTICE, 'Strategy_RemoveForeignElements: Tag transform', 'center');
        $this->invoke('<center>');
    }
    
    function testMissingRequiredAttr() {
        // a little fragile, since img has two required attributes
        $this->expectErrorCollection(E_ERROR, 'Strategy_RemoveForeignElements: Missing required attribute', 'img', 'alt');
        $this->invoke('<img />');
    }
    
    function testForeignElementToText() {
        // uses $CurrentToken.Serialized
        $this->config->set('Core', 'EscapeInvalidTags', true);
        $this->expectErrorCollection(E_WARNING, 'Strategy_RemoveForeignElements: Foreign element to text', 'cannot-possibly-exist-element');
        $this->invoke('<cannot-possibly-exist-element>');
    }
    
    function testForeignElementRemoved() {
        // uses $CurrentToken.Serialized
        $this->expectErrorCollection(E_ERROR, 'Strategy_RemoveForeignElements: Foreign element removed', 'cannot-possibly-exist-element');
        $this->invoke('<cannot-possibly-exist-element>');
    }
    
    function testCommentRemoved() {
        $this->expectErrorCollection(E_NOTICE, 'Strategy_RemoveForeignElements: Comment removed', ' test ');
        $this->invoke('<!-- test -->');
    }
    
    function testScriptRemoved() {
        $this->collector->expectAt(0, 'send', array(E_ERROR, 'Strategy_RemoveForeignElements: Script removed'));
        $this->collector->expectAt(1, 'send', array(E_ERROR, 'Strategy_RemoveForeignElements: Token removed to end', 'script'));
        $this->invoke('<script>asdf');
    }
    
}

?>