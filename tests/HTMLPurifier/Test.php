<?php

require_once 'HTMLPurifier.php';

// integration test

class HTMLPurifier_Test extends UnitTestCase
{
    var $purifier;
    
    function assertPurification($input, $expect = null) {
        if ($expect === null) $expect = $input;
        $result = $this->purifier->purify($input);
        $this->assertIdentical($expect, $result);
    }
    
    function testNull() {
        $this->purifier = new HTMLPurifier();
        $this->assertPurification("Null byte\0", "Null byte");
    }
    
    function testStrict() {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML', 'Strict', true);
        $this->purifier = new HTMLPurifier($config);
        
        $this->assertPurification(
            '<u>Illegal underline</u>',
            'Illegal underline'
        );
        
        $this->assertPurification(
            '<blockquote>Illegal contents</blockquote>',
            '<blockquote><p>Illegal contents</p></blockquote>'
        );
        
    }
    
    function testDifferentAllowedElements() {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML', 'AllowedElements', array('b', 'i', 'p', 'a'));
        $config->set('HTML', 'AllowedAttributes', array('a.href', '*.id'));
        $this->purifier = new HTMLPurifier($config);
        
        $this->assertPurification(
            '<p>Par.</p><p>Para<a href="http://google.com/">gr</a>aph</p>Text<b>Bol<i>d</i></b>'
        );
        
        $this->assertPurification(
            '<span>Not allowed</span><a class="mef" id="foobar">Foobar</a>',
            'Not allowed<a>Foobar</a>' // no ID!!!
        );
        
    }
    
}

?>