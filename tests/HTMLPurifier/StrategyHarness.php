<?php

require_once 'HTMLPurifier/Harness.php';

class HTMLPurifier_StrategyHarness extends HTMLPurifier_Harness
{
    
    function setUp() {
        $this->func      = 'execute';
        $this->to_tokens = true;
        $this->to_html   = true;
    }
    
}

?>