<?php

/**
 * All-use harness, use this rather than SimpleTest's
 */
class HTMLPurifier_Harness extends UnitTestCase
{
    
    function HTMLPurifier_Harness() {
        parent::UnitTestCase();
    }
    
    function prepareCommon(&$config, &$context) {
        $config = HTMLPurifier_Config::create($config);
        if (!$context) $context = new HTMLPurifier_Context();
    }
    
}

