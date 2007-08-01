<?php

/**
 * All-use harness, use this rather than SimpleTest's
 */
class HTMLPurifier_Harness extends UnitTestCase
{
    
    function HTMLPurifier_Harness() {
        parent::UnitTestCase();
    }
    
    var $config, $context;
    
    function setUp() {
        list($this->config, $this->context) = $this->createCommon();
    }
    
    function prepareCommon(&$config, &$context) {
        $config = HTMLPurifier_Config::create($config);
        if (!$context) $context = new HTMLPurifier_Context();
    }
    
    function createCommon() {
        return array(HTMLPurifier_Config::createDefault(), new HTMLPurifier_Context);
    }
    
}

