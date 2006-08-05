<?php

class HTMLPurifier_AttrDefHarness extends UnitTestCase
{
    
    var $def;
    var $id_accumulator;
    var $config;
    
    // cannot be used for accumulator
    function assertDef($string, $expect = true) {
        // $expect can be a string or bool
        if (!$this->config) $this->config = HTMLPurifier_Config::createDefault();
        $result = $this->def->validate($string, $this->config, $this->id_accumulator);
        if ($expect === true) {
            $this->assertIdentical($string, $result);
        } else {
            $this->assertIdentical($expect, $result);
        }
    }
    
}

?>