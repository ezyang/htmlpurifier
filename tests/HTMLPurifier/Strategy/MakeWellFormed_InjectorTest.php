<?php

class HTMLPurifier_Strategy_MakeWellFormed_InjectorTest extends HTMLPurifier_StrategyHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_Strategy_MakeWellFormed();
        $this->config->set('AutoFormat', 'AutoParagraph', true);
        $this->config->set('AutoFormat', 'Linkify', true);
        $this->config->set('AutoFormat', 'RemoveEmpty', true);
        generate_mock_once('HTMLPurifier_Injector');
    }
    
    function testEndNotification() {
        $mock = new HTMLPurifier_InjectorMock();
        $mock->skip = false;
        $b = new HTMLPurifier_Token_End('b');
        $b->start = new HTMLPurifier_Token_Start('b');
        $mock->expectAt(0, 'notifyEnd', array($b));
        $i = new HTMLPurifier_Token_End('i');
        $i->start = new HTMLPurifier_Token_Start('i');
        $mock->expectAt(1, 'notifyEnd', array($i));
        $mock->expectCallCount('notifyEnd', 2);
        $this->config->set('AutoFormat', 'AutoParagraph', false);
        $this->config->set('AutoFormat', 'Linkify',       false);
        $this->config->set('AutoFormat', 'Custom', array($mock));
        $this->assertResult('<i><b>asdf</b>', '<i><b>asdf</b></i>');
    }
    
    function testErrorRequiredElementNotAllowed() {
        $this->config->set('HTML', 'Allowed', '');
        $this->expectError('Cannot enable AutoParagraph injector because p is not allowed');
        $this->expectError('Cannot enable Linkify injector because a is not allowed');
        $this->assertResult('Foobar');
    }
    
    function testErrorRequiredAttributeNotAllowed() {
        $this->config->set('HTML', 'Allowed', 'a,p');
        $this->expectError('Cannot enable Linkify injector because a.href is not allowed');
        $this->assertResult('<p>http://example.com</p>');
    }
    
    function testOnlyAutoParagraph() {
        $this->assertResult(
            'Foobar',
            '<p>Foobar</p>'
        );
    }
    
    function testParagraphWrappingOnlyLink() {
        $this->assertResult(
            'http://example.com',
            '<p><a href="http://example.com">http://example.com</a></p>'
        );
    }
    
    function testParagraphWrappingNodeContainingLink() {
        $this->assertResult(
            '<b>http://example.com</b>',
            '<p><b><a href="http://example.com">http://example.com</a></b></p>'
        );
    }
    
    function testParagraphWrappingPoorlyFormedNodeContainingLink() {
        $this->assertResult(
            '<b>http://example.com',
            '<p><b><a href="http://example.com">http://example.com</a></b></p>'
        );
    }
    
    function testTwoParagraphsContainingOnlyOneLink() {
        $this->assertResult(
            "http://example.com\n\nhttp://dev.example.com",
            '<p><a href="http://example.com">http://example.com</a></p><p><a href="http://dev.example.com">http://dev.example.com</a></p>'
        );
    }
    
    function testParagraphNextToDivWithLinks() {
        $this->assertResult(
            'http://example.com <div>http://example.com</div>',
            '<p><a href="http://example.com">http://example.com</a> </p><div><a href="http://example.com">http://example.com</a></div>'
        );
    }
    
    function testRealisticLinkInSentence() {
        $this->assertResult(
            'This URL http://example.com is what you need',
            '<p>This URL <a href="http://example.com">http://example.com</a> is what you need</p>'
        );
    }
    
    function testParagraphAfterLinkifiedURL() {
        $this->assertResult(
            "http://google.com\n\n<b>b</b>",
            "<p><a href=\"http://google.com\">http://google.com</a></p><p><b>b</b></p>"
        );
    }
    
    function testEmptyAndParagraph() {
        // This is a fairly degenerate case, but it demonstrates that
        // the two don't error out together, at least.
        $this->assertResult(
            "<p>asdf\n\nasdf<b></b></p>\n\n<p></p><i></i>",
            "<p>asdf</p><p>asdf</p>"
        );
    }
    
    function testRewindAndParagraph() {
        $this->assertResult(
            "bar\n\n<p><i></i>\n\n</p>\n\nfoo",
            "<p>bar</p><p>foo</p>"
        );
    }
    
}
