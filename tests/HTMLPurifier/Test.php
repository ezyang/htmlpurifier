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
    
}

?>