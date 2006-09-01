<?php

require_once 'HTMLPurifier.php';

// integration test

class HTMLPurifier_Test extends UnitTestCase
{
    var $purifier;
    
    function assertPurification($input, $expect) {
        $result = $this->purifier->purify($input);
        $this->assertIdentical($expect, $result);
    }
    
    function test() {
        $config = HTMLPurifier_Config::createDefault();
        $this->purifier = new HTMLPurifier($config);
        $this->assertPurification("Null byte\0", "Null byte");
    }
}

?>