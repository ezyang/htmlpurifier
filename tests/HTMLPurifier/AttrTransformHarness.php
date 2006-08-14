<?php

class HTMLPurifier_AttrTransformHarness extends UnitTestCase
{
    
    var $transform;
    
    function assertTransform($inputs, $expect, $config = array()) {
        $default_config = HTMLPurifier_Config::createDefault();
        foreach ($inputs as $i => $input) {
            if (!isset($config[$i])) $config[$i] = $default_config;
            $result = $this->transform->transform($input, $config[$i]);
            if ($expect[$i] === true) $expect[$i] = $input;
            $this->assertEqual($expect[$i], $result, "Test $i: %s");
        }
    }
    
}

?>