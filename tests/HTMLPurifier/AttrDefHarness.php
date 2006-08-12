<?php

class HTMLPurifier_AttrDefHarness extends UnitTestCase
{
    
    var $def;
    var $context;
    var $config;
    
    // cannot be used for accumulator
    function assertDef($string, $expect = true, $message = '%s') {
        // $expect can be a string or bool
        $this->setUpAssertDef();
        if (!$this->config) $this->config = HTMLPurifier_Config::createDefault();
        if (!$this->context) $this->context = new HTMLPurifier_AttrContext();
        $result = $this->def->validate($string, $this->config, $this->context);
        if ($expect === true) {
            $this->assertIdentical($string, $result, $message);
        } else {
            $this->assertIdentical($expect, $result, $message);
        }
        $this->tearDownAssertDef();
    }
    
    function setUpAssertDef() {}
    function tearDownAssertDef() {}
    
}

?>