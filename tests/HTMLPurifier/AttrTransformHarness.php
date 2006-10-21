<?php

// todo: change testing harness from accepting arrays to 
// have one call per test

class HTMLPurifier_AttrTransformHarness extends UnitTestCase
{
    
    var $transform;
    
    function assertTransform($inputs, $expect, $config = array(), $context = array()) {
        $default_config = HTMLPurifier_Config::createDefault();
        $default_context = new HTMLPurifier_Context();
        foreach ($inputs as $i => $input) {
            if (!isset($config[$i])) $config[$i] = $default_config;
            if (!isset($context[$i])) $context[$i] = $default_context;
            $result = $this->transform->transform($input, $config[$i], $context[$i]);
            if ($expect[$i] === true) $expect[$i] = $input;
            $this->assertEqual($expect[$i], $result, "Test $i: %s");
        }
    }
    
}

?>