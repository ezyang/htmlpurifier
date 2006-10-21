<?php

class HTMLPurifier_AttrDefHarness extends UnitTestCase
{
    
    var $def;
    var $context;
    var $config;
    
    function setUp() {
        $this->config = HTMLPurifier_Config::createDefault();
        $this->context = new HTMLPurifier_Context();
    }
    
    // cannot be used for accumulator
    function assertDef($string, $expect = true, $ini = false, $message = '%s') {
        // $expect can be a string or bool
        if ($ini) $this->setUpAssertDef();
        $result = $this->def->validate($string, $this->config, $this->context);
        if ($expect === true) {
            $this->assertIdentical($string, $result, $message);
        } else {
            $this->assertIdentical($expect, $result, $message);
        }
        if ($ini) $this->tearDownAssertDef();
    }
    
    function setUpAssertDef() {}
    function tearDownAssertDef() {}
    
}

?>